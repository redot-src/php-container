<?php

namespace Redot\Container;

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
    public static function getParameterClassName(ReflectionParameter $parameter): string
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return '';
        }

        $name = $type->getName();

        if (!is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') return $class->getName();
            if ($name === 'parent' && $parent = $class->getParentClass()) return $parent->getName();
        }

        return $name;
    }
}
