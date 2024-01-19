<?php

use ostark\PgConverter\Converter;

it('foo', function () {
    $example = new Converter();

    $result = $example->foo();

    expect($result)->toBe('bar');
});
