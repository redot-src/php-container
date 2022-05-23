<?php

use Redot\Container\Utils;

test('Method: unwrapIfClosure', function () {
    $value = function () {
        return 'Hello';
    };

    expect(Utils::unwrapIfClosure($value))->toBe('Hello');
    expect(Utils::unwrapIfClosure('World'))->toBe('World');
});
