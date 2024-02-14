<?php

declare(strict_types=1);

it('should use default values if option is not set', function () {
    $options = [];
    $config = new \ostark\PgConverter\ConverterConfig(
        './tests/examples/create-table-with-index.sql',
        '/tmp/converted.sql',
        $options
    );

    expect($config->getEngine())->toBe('InnoDB');
    expect($config->getCharset())->toBe('utf8');
    expect($config->getAppendString())->toBeNull();
    expect($config->getAppendFile())->toBeNull();
    expect($config->getInputFilter())->toBeNull();

});
