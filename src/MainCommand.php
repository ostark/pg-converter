<?php

declare(strict_types=1);

namespace ostark\PgConverter;

use ostark\PgConverter\IO\FileReader;
use ostark\PgConverter\IO\FileWriter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MainCommand
{
    public const DEFAULT_SCHEMA = 'public';

    public function __construct(public InputInterface $input, public OutputInterface $output)
    {
    }

    public function run(): void
    {
        // Read lines from source file
        $file = __DIR__.'/../tests/examples/craft-demo.sql';
        $lines = (new FileReader($file))->getLines();

        // convert
        $skippedTables = ['fooo'];

        $converter = new Converter($lines, $skippedTables);
        $lines = $converter->convert();

        // Store line by line
        $target = tempnam('/tmp', 'pg-converter-').'.sql';
        $lineCount = (new FileWriter($lines, $target))->store();

        print_r([
            'ERRORS' => $converter->getErrors(),
            'UNKNOWN' => array_slice($converter->getUnknownStatements(), 0, 50),
            'LINES_WRITTEN' => $lineCount,
        ]);
    }
}
