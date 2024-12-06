<?php

namespace AlexQiu\Sdkit;

use AlexQiu\Sdkit\Contracts\AccessTokenInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Class AccessToken.
 */
abstract class AccessToken implements AccessTokenInterface
{
    /**
     * @var ServiceContainer
     */
    protected $app;

    /**
     * AccessToken constructor.
     *
     * @param ServiceContainer $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }

    /**
     * @param RequestInterface $request
     * @param array            $requestOptions
     *
     * @return RequestInterface
     */
    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface
    {
        if (!method_exists($this, 'appendQuery')) {
            return $request;
        }

        parse_str($request->getUri()->getQuery(), $query);

        $query = http_build_query(
            array_merge(
                $this->appendQuery($query, $request, $requestOptions),
                $query
            )
        );

        return $request->withUri($request->getUri()->withQuery($query));
    }
}