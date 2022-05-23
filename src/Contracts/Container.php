<?php

namespace Redot\Container\Contracts;

use ReflectionException;
use Psr\Container\ContainerInterface;
use Redot\Container\Errors\NotFoundException;
use Redot\Container\Errors\BindingResolutionException;

interface Container extends ContainerInterface
{
    /**
     * Bind an abstract to a concrete.
     *
     * @param string $abstract
     * @param string|callable|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bind(string $abstract, string|callable $concrete = null, bool $shared = false): void;

    /**
     * Create a singleton instance of the given class.
     *
     * @param string $abstract
     * @param string|callable|null $concrete
     * @return void
     */
    public function singleton(string $abstract, string|callable $concrete = null): void;

    /**
     * Set an alias for the given abstract.
     *
     * @param string $abstract
     * @param string $alias
     * @return void
     */
    public function alias(string $abstract, string $alias): void;

    /**
     * Make a concrete instance of the given abstract.
     *
     * @param string $abstract
     * @param array $params
     * @return mixed
     *
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public function make(string $abstract, array $params = []): mixed;

    /**
     * Use Container to resolve a method on a class.
     *
     * @param string $class
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function call(string $class, string $method, array $params = []): mixed;
}
