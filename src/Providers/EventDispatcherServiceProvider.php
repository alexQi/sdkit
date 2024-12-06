<?php

namespace AlexQiu\Sdkit\Providers;

use AlexQiu\Sdkit\ServiceContainer;
use DI\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * EventDispatcherServiceProvider
 *
 * @author  alex
 * @package AlexQiu\Sdkit\Providers\EventDispatcherServiceProvider
 */
class EventDispatcherServiceProvider
{
    /**
     * @param Container $pimple
     *
     * @return void
     */
    public function register(ServiceContainer $service): void
    {
        $service->getContainer()->set("events", function (Container $container) {
            $dispatcher = new EventDispatcher();
            $listens    = $container->get("config")["events"]["listeners"] ?? [];
            foreach ($listens as $event => $listeners) {
                foreach ($listeners as $listener) {
                    $dispatcher->addListener($event, $listener);
                }
            }

            return $dispatcher;
        });
    }
}