<?php

it('should convert CREATE TABLE statement with indexes', function () {
    $this->assertTrue(true);

    $config = new \ostark\PgConverter\ConverterConfig(
        './tests/examples/create-table-with-index.sql',
        '/tmp/converted.sql',
        ['engine' => 'InnoDB']
    );

    $converter = new \ostark\PgConverter\Converter(
        (new \ostark\PgConverter\IO\FileReader($config->inputFile))->getLines(),
        $config
    );

    $convertedLines = $converter->convert();

    foreach ($convertedLines as $line) {
        echo $line.PHP_EOL;
    }

});
