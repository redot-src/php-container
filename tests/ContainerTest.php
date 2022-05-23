<?php

use Redot\Container\Container;
use Redot\Container\Errors\NotFoundException;
use Redot\Container\Errors\BindingResolutionException;

test('Container::bind creates a new binding', function () {
    $container = new Container();
    $container->bind('foo', function () {
        return 'bar';
    });

    expect($container->get('foo'))->toBe('bar');
});

test('Container::bind with dependencies', function () {
    $container = new Container();
    $container->bind('foo', function ($container, $bar) {
        return $bar;
    });

    expect($container->make('foo', ['bar' => true]))->toBe(true);
});

test('Container::bind with default values for dependencies', function () {
    $container = new Container();
    $container->bind('foo', function ($container, $bar = false) {
        return $bar;
    });

    expect($container->make('foo'))->toBe(false);
});

test('Container::singleton creates a new singleton', function () {
    $container = new Container();
    $container->singleton('class', function () {
        return new stdClass;
    });

    expect($container->get('class'))->toBe($container->get('class'));
});

test('Container::alias creates a new alias', function () {
    $container = new Container();
    $container->bind('foo', function () {
        return 'bar';
    });

    $container->alias('foo', 'foo_alias');
    expect($container->get('foo_alias'))->toBe('bar');
});

test('Container::get returns the correct value', function () {
    $container = new Container();
    $container->bind('foo', function () {
        return 'bar';
    });

    expect($container->get('foo'))->toBe('bar');
});

test('Container::make returns the correct value', function () {
    $container = new Container();
    $container->bind('foo', function () {
        return 'bar';
    });

    expect($container->make('foo'))->toBe('bar');
});

test('Container::call resolves a method on a class.', function () {
    class Foo {
        public function bar() {
            return 'bar';
        }
    }

    $container = new Container();
    expect($container->call(Foo::class, 'bar'))->toBe('bar');
});

test('Container::has returns true if the given key exists', function () {
    $container = new Container();
    $container->bind('foo', function () {
        return 'bar';
    });

    expect($container->has('foo'))->toBe(true);
    expect($container->has('bar'))->toBe(false);
});

test('Container throws an exception if the given key does not exist', function () {
    $container = new Container();
    expect(fn() => $container->get('test'))->toThrow(NotFoundException::class);
});

test('Container throws an expection if the given key is not instantiable', function () {
    $container = new Container();

    abstract class classA {
        public function __construct() {}
    };

    expect(fn() => $container->make(classA::class))->toThrow(BindingResolutionException::class);
});
