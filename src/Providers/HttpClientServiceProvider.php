<?php

namespace AlexQiu\Sdkit\Providers;

use AlexQiu\Sdkit\ServiceContainer;
use DI\Container;
use GuzzleHttp\Client;

/**
 * HttpClientServiceProvider
 *
 * @author  alex
 * @package AlexQiu\Sdkit\Providers\HttpClientServiceProvider
 */
class HttpClientServiceProvider
{
    /**
     * @param ServiceContainer $service
     *
     * @return void
     */
    public function register(ServiceContainer $service)
    {
        $service->getContainer()->set("http_client", function (Container $container) {
            return new Client($container->get("config")->get("http") ?? []);
        });
    }
}