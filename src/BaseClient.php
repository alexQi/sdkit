<?php

namespace AlexQiu\Sdkit;

use AlexQiu\Sdkit\Exceptions\InvalidConfigException;
use AlexQiu\Sdkit\Support\Collection;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use AlexQiu\Sdkit\Contracts\AccessTokenInterface;
use AlexQiu\Sdkit\Exceptions\NotEligibleResponseException;
use AlexQiu\Sdkit\Traits\HasHttpRequests;
use AlexQiu\Sdkit\Http\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Class BaseClient.
 */
class BaseClient
{
    use HasHttpRequests;

    /**
     * @var ServiceContainer
     */
    protected $app;

    /**
     * @var AccessTokenInterface
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * BaseClient constructor.
     *
     * @param ServiceContainer          $app
     * @param AccessTokenInterface|null $accessToken
     */
    public function __construct(ServiceContainer $app, AccessTokenInterface $accessToken = null)
    {
        $this->app         = $app;
        $this->accessToken = $accessToken ? : null;
        if ($this->app->getContainer()->has("access_token")) {
            $this->accessToken = $this->app->access_token;
        }
    }

    /**
     * GET request.
     *
     * @param string $url
     * @param array  $query
     *
     * @return ResponseInterface|Collection|array|object|string
     */
    protected function httpGet(string $url, array $query = [])
    {
        return $this->fetch($url, 'GET', [
            'query' => $query
        ]);
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array  $data
     *
     * @return ResponseInterface|Collection|array|object|string
     */
    protected function httpPost(string $url, array $data = [])
    {
        return $this->fetch($url, 'POST', [
            'form_params' => $data
        ]);
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array  $data
     *
     * @return ResponseInterface|Collection|array|object|string
     */
    protected function httpPut(string $url, array $data = [])
    {
        return $this->fetch($url, 'PUT', [
            'json' => $data
        ]);
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array  $data
     *
     * @return ResponseInterface|Collection|array|object|string
     */
    protected function httpDelete(string $url, array $data = [])
    {
        return $this->fetch($url, 'DELETE', [
            'json' => $data
        ]);
    }

    /**
     * JSON request.
     *
     * @param string $url
     * @param array  $data
     * @param array  $query
     *
     * @return ResponseInterface|Collection|array|object|string
     */
    protected function httpPostJson(string $url, array $data = [], array $query = [])
    {
        return $this->fetch($url, 'POST', [
            'query' => $query,
            'json'  => $data
        ]);
    }

    /**
     * Upload file.
     *
     * @param string $url
     * @param array  $files
     * @param array  $form
     * @param array  $query
     *
     * @return ResponseInterface|Collection|array|object|string
     */
    protected function httpUpload(string $url, array $files = [], array $form = [], array $query = [])
    {
        $multipart = [];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name'     => $name,
                'contents' => fopen($path, 'r'),
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        return $this->fetch($url, 'POST', [
            'query'           => $query,
            'multipart'       => $multipart,
            'connect_timeout' => 30,
            'timeout'         => 30,
            'read_timeout'    => 30
        ]);
    }

    /**
     * @return AccessTokenInterface
     */
    protected function getAccessToken(): AccessTokenInterface
    {
        return $this->accessToken;
    }

    /**
     * @param AccessTokenInterface $accessToken
     *
     * @return $this
     */
    protected function setAccessToken(AccessTokenInterface $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $options
     * @param bool   $returnRaw
     *
     * @return ResponseInterface|Collection|array|object|string
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    protected function fetch(
        string $url,
        string $method = 'GET',
        array  $options = [],
               $returnRaw = false
    ) {
        $this->registerHttpMiddlewares();

        $response = $this->request($url, $method, $options);

        return $returnRaw ? $response : $this->unwrapResponse($response);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array|object|ResponseInterface|string|Support\Collection
     * @throws Exceptions\InvalidConfigException
     */
    protected function unwrapResponse(ResponseInterface $response)
    {
        return $this->castResponseToType(
            $response,
            $this->app->getContainer()->get("config")->get('http.response_type')
        );
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return Response
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    protected function requestRaw(string $url, string $method = 'GET', array $options = [])
    {
        return Response::buildFromPsrResponse(
            $this->fetch($url, $method, $options, true)
        );
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // access token
        $this->pushMiddleware($this->accessTokenMiddleware(), 'access_token');

        // logger
        if ($this->app->getContainer()->has(LoggerInterface::class)) {
            $this->pushMiddleware($this->logMiddleware(), 'logger');
        }

        // not eligible response
        if (method_exists($this, 'isNotEligibleResponse')) {
            $this->pushMiddleware(
                $this->notEligibleResponseMiddleware(),
                'not_eligible_response'
            );
        }
    }

    /**
     * Attache access token to request query.
     *
     * @return callable
     */
    protected function accessTokenMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if ($this->accessToken) {
                    $request = $this->accessToken->applyToRequest($request, $options);
                }

                return $handler($request, $options);
            };
        };
    }

    /**
     * Check the response.
     *
     * @return callable
     */
    protected function notEligibleResponseMiddleware()
    {
        return function (callable $handler) {
            return function ($request, array $options) use ($handler) {
                return $handler($request, $options)->then(function ($response) use ($request) {
                    if ($message = $this->isNotEligibleResponse($response, $request)) {
                        if (!is_string($message)) {
                            $message = 'Unsuccessful request';
                        }
                        throw new NotEligibleResponseException(
                            $message,
                            $request,
                            $response
                        );
                    }

                    return $response;
                });
            };
        };
    }

    /**
     * @param ResponseInterface $response
     * @param RequestInterface  $request
     *
     * @return false|string
     */
    protected function isNotEligibleResponse(ResponseInterface $response, RequestInterface $request)
    {
        // 示例逻辑：判断 HTTP 状态码是否是 200
        if ($response->getStatusCode() !== 200) {
            return 'Response status code is not 200.';
        }

        // 如果响应符合条件，返回 false
        return false;
    }

    /**
     * Log the request.
     *
     * @return callable
     */
    protected function logMiddleware()
    {
        return Middleware::log(
            $this->app->getContainer()->get(LoggerInterface::class),
            new MessageFormatter(
                $this->app->config->get(
                    'http.log_template',
                    MessageFormatter::DEBUG
                )
            ),
            LogLevel::DEBUG
        );
    }
}