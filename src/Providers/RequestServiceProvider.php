<?php

namespace AlexQiu\Sdkit\Providers;

use AlexQiu\Sdkit\ServiceContainer;
use Symfony\Component\HttpFoundation\Request;

/**
 * RequestServiceProvider
 *
 * @author  alex
 * @package AlexQiu\Sdkit\Providers\RequestServiceProvider
 */
class RequestServiceProvider
{
    /**
     * @param ServiceContainer $service
     *
     * @return void
     */
    public function register(ServiceContainer $service): void
    {
        $service->getContainer()->set(Request::class, function () {
            return Request::createFromGlobals();
        });
    }

}