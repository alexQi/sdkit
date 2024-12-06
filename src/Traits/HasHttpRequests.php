<?php

namespace AlexQiu\Sdkit\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Utils;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait HasHttpRequests.
 */
trait HasHttpRequests
{
    use ResponseCastable;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var \GuzzleHttp\HandlerStack
     */
    protected $handlerStack;

    /**
     * @var array
     */
    protected static $defaults = [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ],
    ];

    /**
     * Set guzzle default settings.
     *
     * @param array $defaults
     */
    public static function setDefaultOptions($defaults = [])
    {
        self::$defaults = $defaults;
    }

    /**
     * Return current guzzle default settings.
     *
     * @return array
     */
    public static function getDefaultOptions(): array
    {
        return self::$defaults;
    }

    /**
     * Set GuzzleHttp\Client.
     *
     * @param \GuzzleHttp\ClientInterface $httpClient
     *
     * @return $this
     */
    protected function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Return GuzzleHttp\ClientInterface instance.
     *
     * @return ClientInterface
     */
    protected function getHttpClient(): ClientInterface
    {
        if (!($this->httpClient instanceof ClientInterface)) {
            if (property_exists($this, 'app') && $this->app->getContainer()->has("http_client")) {
                $this->httpClient = $this->app->getContainer()->get("http_client");
            } else {
                $this->httpClient = new Client(
                    [
                        'handler' => HandlerStack::create($this->getGuzzleHandler())
                    ]
                );
            }
        }
        return $this->httpClient;
    }

    /**
     * Add a middleware.
     *
     * @param callable $middleware
     * @param string   $name
     *
     * @return $this
     */
    protected function pushMiddleware(callable $middleware, string $name = null)
    {
        if (!is_null($name)) {
            $this->middlewares[$name] = $middleware;
        } else {
            array_push($this->middlewares, $middleware);
        }

        return $this;
    }

    /**
     * Return all middlewares.
     *
     * @return array
     */
    protected function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Make a request.
     *
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request($url, $method = 'GET', $options = []): ResponseInterface
    {
        $options = array_merge(self::$defaults, $options, [
            'handler' => $this->getHandlerStack()
        ]);

        $options = $this->fixJsonIssue($options);

        if (property_exists($this, 'baseUri') && !is_null($this->baseUri)) {
            $options['base_uri'] = $this->baseUri;
        }

        $response = $this->getHttpClient()->request(
            strtoupper($method),
            $url,
            $options
        );

        $response->getBody()->rewind();

        return $response;
    }

    /**
     * @param \GuzzleHttp\HandlerStack $handlerStack
     *
     * @return $this
     */
    protected function setHandlerStack(HandlerStack $handlerStack)
    {
        $this->handlerStack = $handlerStack;

        return $this;
    }

    /**
     * Build a handler stack.
     *
     * @return \GuzzleHttp\HandlerStack
     */
    protected function getHandlerStack(): HandlerStack
    {
        if ($this->handlerStack) {
            return $this->handlerStack;
        }
        $stack = new HandlerStack($this->getGuzzleHandler());
        $stack->push(Middleware::cookies(), 'cookies');
        $stack->push(Middleware::prepareBody(), 'prepare_body');
        $stack->push(Middleware::redirect(), 'allow_redirects');

        foreach ($this->middlewares as $name => $middleware) {
            $stack->push($middleware, $name);
        }
        $stack->push(Middleware::httpErrors(), 'http_errors');

        return $this->handlerStack = $stack;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function fixJsonIssue(array $options): array
    {
        if (isset($options['json']) && is_array($options['json'])) {
            $options['headers'] = array_merge(
                $options['headers'] ?? [],
                ['Content-Type' => 'application/json']
            );

            if (empty($options['json'])) {
                $options['body'] = Utils::jsonEncode(
                    $options['json'],
                    JSON_FORCE_OBJECT
                );
            } else {
                $options['body'] = Utils::jsonEncode(
                    $options['json'],
                    JSON_UNESCAPED_UNICODE
                );
            }

            unset($options['json']);
        }

        return $options;
    }

    /**
     * Get guzzle handler.
     *
     * @return callable
     */
    protected function getGuzzleHandler()
    {
        if (property_exists($this, 'app') && $this->app->getContainer()->has("guzzle_handler")) {
            return is_string($handler = $this->app->getContainer()->get("guzzle_handler"))
                ? new $handler()
                : $handler;
        }

        return Utils::chooseHandler();
    }
}