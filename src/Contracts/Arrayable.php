<?php

namespace AlexQiu\Sdkit\Contracts;

use ArrayAccess;

interface Arrayable extends ArrayAccess
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}