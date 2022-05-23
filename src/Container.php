<?php

namespace Redot\Container;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;
use Redot\Container\Errors\NotFoundException;
use Redot\Container\Errors\BindingResolutionException;
use Redot\Container\Contracts\Container as ContainerContract;

class Container implements ContainerContract
{
    /**
     * Globally available container instance.
     *
     * @var Container|null
     */
    protected static Container|null $instance = null;

    /**
     * Container bindings.
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * Array of Singletons.
     *
     * @var array
     */
    protected array $instances = [];

    /**
     * Binding aliases.
     *
     * @var array
     */
    protected array $aliases = [];

    /**
     * Resolved instances.
     *
     * @var array
     */
    protected array $resolved = [];

    /**
     * Set current container instance.
     *
     * @param Container $instance
     * @return void
     */
    public static function setInstance(Container $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * Get current container instance.
     *
     * @return Container
     */
    public static function getInstance(): Container
    {
        if (is_null(self::$instance)) self::$instance = new Container();
        return self::$instance;
    }

    /**
     * Bind an abstract to a concrete.
     *
     * @param string $abstract
     * @param string|callable|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bind(string $abstract, string|callable $concrete = null, bool $shared = false): void
    {
        if (is_null($concrete)) $concrete = $abstract;
        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Create a singleton instance of the given class.
     *
     * @param string $abstract
     * @param string|callable|null $concrete
     * @return void
     */
    public function singleton(string $abstract, string|callable $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Set an alias for the given abstract.
     *
     * @param string $abstract
     * @param string $alias
     * @return void
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

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
    public function make(string $abstract, array $params = []): mixed
    {
        $abstract = $this->getAlias($abstract);
        if (isset($this->instances[$abstract])) return $this->instances[$abstract];

        $concrete = $this->getConcrete($abstract);
        if ($this->isBuildable($concrete, $abstract)) $obj = $this->build($concrete, $params);
        else $obj = $this->make($concrete);

        if ($this->isShared($abstract)) $this->instances[$abstract] = $obj;
        $this->resolved[$abstract] = true;

        return $obj;
    }

    /**
     * Get abstract alias.
     *
     * @param string $abstract
     * @return string
     */
    protected function getAlias(string $abstract): string
    {
        return isset($this->aliases[$abstract]) ? $this->getAlias($this->aliases[$abstract]) : $abstract;
    }

    /**
     * Get abstract concrete.
     *
     * @param string $abstract
     * @return mixed
     */
    protected function getConcrete(string $abstract): mixed
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Check if concrete can be built.
     *
     * @param callable|string $concrete
     * @param string $abstract
     * @return bool
     */
    protected function isBuildable(callable|string $concrete, string $abstract): bool
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * Check if abstract is a singleton.
     *
     * @param string $abstract
     * @return bool
     */
    protected function isShared(string $abstract): bool
    {
        return isset($this->instances[$abstract]) || (isset($this->bindings[$abstract]['shared']) &&
            $this->bindings[$abstract]['shared'] === true
        );
    }

    /**
     * Build concrete.
     *
     * @param callable|string $concrete
     * @param array $params
     * @return mixed
     *
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    protected function build(callable|string $concrete, array $params = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, ...array_values($params));
        }

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException) {
            throw new NotFoundException("Unable to resolve class [$concrete]");
        }

        if (!$reflector->isInstantiable()) {
            throw new BindingResolutionException("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) return $reflector->newInstance();

        $dependencies = $constructor->getParameters();
        $args = $this->getDependencies($dependencies, $params);
        return $reflector->newInstanceArgs($args);
    }

    /**
     * Get dependencies for the given constructor.
     *
     * @param array $dependencies
     * @param array $params
     * @return array
     *
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    protected function getDependencies(array $dependencies, array $params = []): array
    {
        return array_map(function ($dependency) use ($params) {
            if (isset($params[$dependency->name])) return $params[$dependency->name];
            if ($dependency->isDefaultValueAvailable()) return $dependency->getDefaultValue();
            return $this->make(Utils::getParameterClassName($dependency));
        }, $dependencies);
    }

    /**
     * Use Container to resolve a method on a class.
     *
     * @param string $class
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function call(string $class, string $method, array $params = []): mixed
    {
        $reflector = new ReflectionMethod($class, $method);
        $dependencies = $reflector->getParameters();
        $args = $this->getDependencies($dependencies, $params);
        return $reflector->invokeArgs($this->make($class), $args);
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param string $abstract
     * @return bool
     */
    protected function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]) ||
            $abstract !== $this->getAlias($abstract);
    }

    /**
     * {@inheritDoc}
     *
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public function get(string $id): mixed
    {
        return $this->make($id);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return $this->bound($id);
    }
}
