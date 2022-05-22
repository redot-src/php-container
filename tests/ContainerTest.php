<?php

use Redot\Container\Container;
use Redot\Container\Tests\ClassA;
use Redot\Container\Tests\ClassB;

test('Method: bind', function () {
    Container::getInstance()->bind(ClassA::class);
    expect(Container::getInstance()->make(ClassA::class))->toBeInstanceOf(ClassA::class);
});

test('Method: alias', function () {
    Container::getInstance()->alias(ClassA::class, 'ClassA');
    expect(Container::getInstance()->make('ClassA'))->toBeInstanceOf(ClassA::class);
});

test('Method: singleton', function () {
    Container::getInstance()->singleton(ClassB::class);
    expect(Container::getInstance()->make(ClassB::class))->toBeInstanceOf(ClassB::class);
});
