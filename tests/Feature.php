<?php

use ostark\PgConverter\MainCommand;

it('foo', function () {
    $example = new MainCommand();

    $result = $example->foo();

    expect($result)->toBe('bar');
});
