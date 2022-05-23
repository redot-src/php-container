<?php

namespace Redot\Container\Contracts;

use ReflectionParameter;

interface Utils
{
    /**
     * Get the class name of the given object / class.
     *
     * @param ReflectionParameter $parameter
     * @return string
     */
    public static function getParameterClassName(ReflectionParameter $parameter): string|null;
}
