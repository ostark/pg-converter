<?php

afterAll(function () {
    $file = __DIR__ . '/../../fixtures/write-to-file.txt';
    unlink($file);
});


it('should write to file', function () {
    $file = __DIR__ . '/../../fixtures/write-to-file.txt';
    $lines = new ArrayIterator(['first line', 'second line', 'third line']);
    $writer = new \ostark\PgConverter\IO\FileWriter($file, $lines);
    $lineCount = $writer->store();

    expect($lineCount)->toBe(3);
    expect(file_exists($file))->toBeTrue();
    expect(file_get_contents($file))->toBe('first line' . PHP_EOL . 'second line' . PHP_EOL . 'third line' . PHP_EOL);
});

it('should append to file', function () {
    $file = __DIR__ . '/../../fixtures/write-to-file.txt';
    file_put_contents($file, 'hi' . PHP_EOL);

    $writer = new \ostark\PgConverter\IO\FileWriter($file, new ArrayIterator(['bye']));
    $lineCount = $writer->append();

    expect($lineCount)->toBe(1);
    expect(file_get_contents($file))->toBe('hi' . PHP_EOL . 'bye' . PHP_EOL);
});

it('should throw exception if unable to write', function () {
    $lines = new ArrayIterator(['first line', 'second line', 'third line']);
    $writer = new \ostark\PgConverter\IO\FileWriter('not-existing-dir/write-to-file.txt', $lines);
    $writer->store();
})->throws(\Exception::class);
