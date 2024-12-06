<?php

namespace AlexQiu\Sdkit\Contracts;

interface EventHandlerInterface
{
    /**
     * @param mixed $payload
     */
    public function handle($payload = null);
}