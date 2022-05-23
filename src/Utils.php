<?php

namespace Redot\Container;

use Closure;
use ReflectionNamedType;
use ReflectionParameter;

class Utils
{
    /**
     * Get the class name of the given object / class.
     *
     * @param ReflectionParameter $parameter
     * @return string
     */
    public static function getParameterClassName(ReflectionParameter $parameter): string|null
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) return null;

        $name = $type->getName();

        if (!is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') return $class->getName();
            if ($name === 'parent' && $parent = $class->getParentClass()) return $parent->getName();
        }

        return $name;
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    public static function unwrapIfClosure($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}
