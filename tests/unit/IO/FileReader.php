<?php

it('should read file line by line', function () {
    $file = __DIR__ . '/../../fixtures/create-table.sql';
    $reader = new \ostark\PgConverter\IO\FileReader($file);
    $lines = $reader->getLines();

    expect($lines)->toBeIterable();
    expect($lines->current())->toBeString();
    expect($lines->current())->toContain('CREATE TABLE public.users');
});

it('should throw exception if file does not exist', function () {
    $file = '.not-existing';
    $reader = new \ostark\PgConverter\IO\FileReader($file);
    $reader->getLines();
})->throws(\InvalidArgumentException::class);
