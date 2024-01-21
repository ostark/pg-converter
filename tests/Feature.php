<?php

use ostark\PgConverter\Application;

it('foo', function () {
    $example = new Application();

    $result = $example->foo();

    expect($result)->toBe('bar');
});
