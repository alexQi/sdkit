<?php

namespace AlexQiu\Sdkit\Providers;

use AlexQiu\Sdkit\Config;
use AlexQiu\Sdkit\ServiceContainer;

class ConfigServiceProvider
{
    public function register(ServiceContainer $service): void
    {
        $service->getContainer()->set("config", function () use ($service) {
            return new Config($service->getConfig());
        });
    }
}