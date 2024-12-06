<?php

namespace AlexQiu\Sdkit\Providers;

use AlexQiu\Sdkit\Log\LogManager;
use AlexQiu\Sdkit\ServiceContainer;
use Psr\Log\LoggerInterface;

/**
 * LogServiceProvider
 *
 * @author  alex
 * @package AlexQiu\Sdkit\Providers\LogServiceProvider
 */
class LogServiceProvider
{
    public function register(ServiceContainer $service): void
    {
        $container = $service->getContainer();
        // 注册 LogManager
        $container->set(LoggerInterface::class, function () use ($service) {
            return new LogManager($service);
        });
    }
}