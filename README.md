# PHP Dependency Injection Container

PHP Minimal Dependency Injection Container

## Installation

```sh
composer require redot/container
```

## Testing

```sh
composer test
```

## Usage

- Bind an abstract class to a concrete

    ```php
    $container = new Container();
    $container->bind(ClassA::class, fn () => new ClassA());

    /* Or simply */
    $container->bind(ClassA::class);
    ```

- Create a singleton

    ```php
    $container = new Container();
    $container->singleton(ClassB::class);
    ```

- Set alias

    ```php
    $container = new Container();
    $container->alias(ClassA::class, 'ClassA');
    ```

- Dynamic resolve constructor properties

    ```php
    class ClassC
    {
        public function __construct(ClassA $classA)
        {
            // your awesome code
        }
    }

    $container->make(ClassC::class);
    ```

That's it! Enjoy ✌
