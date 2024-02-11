<?php

namespace ostark\PgConverter\Command;

use ostark\PgConverter\Converter;
use ostark\PgConverter\ConverterConfig;
use ostark\PgConverter\IO\FileReader;
use ostark\PgConverter\IO\FileWriter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertCommand extends \Symfony\Component\Console\Command\Command
{
    const NAME = 'convert';

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('input-file', InputArgument::REQUIRED, 'Postgres dump'),
                    new InputArgument('output-file', InputArgument::REQUIRED, 'Mysql dump'),
                    new InputOption(ConverterConfig::OPTION_INPUT_FILTER, 'i', InputOption::VALUE_OPTIONAL, 'Regex pattern to filter input lines'),
                    new InputOption(ConverterConfig::OPTION_APPEND_STRING, 's', InputOption::VALUE_OPTIONAL, 'Append string to output file'),
                    new InputOption(ConverterConfig::OPTION_APPEND_FILE, 'f', InputOption::VALUE_OPTIONAL, 'Append file to output file'),
                    new InputOption(ConverterConfig::OPTION_ENGINE, 'e', InputOption::VALUE_OPTIONAL, 'MySQL Engine', ConverterConfig::DEFAULT_ENGINE),
                    new InputOption(ConverterConfig::OPTION_CHARSET, 'c', InputOption::VALUE_OPTIONAL, 'MySQL Charset', ConverterConfig::DEFAULT_CHARSET),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = new ConverterConfig(
            $input->getArgument('input-file'),
            $input->getArgument('output-file'),
            $input->getOptions()
        );

        // Read lines from source file
        $lines = (new FileReader($config->inputFile))->getLines();

        // Convert
        $converter = new Converter($lines, $config);
        $lines = $converter->convert();

        // Store the converted sql statements
        $lineCount = (new FileWriter($config->outputFile, $lines))->store();

        // Add extra sql statements to the output file
        if ($config->getAppendString()) {
            $lines = new \ArrayIterator([$config->getAppendString()]);
            $lineCount += (new FileWriter($config->outputFile, $lines))->append();
        }

        // Add extra sql statements from a file to the output file
        if ($config->getAppendFile()) {
            $lines = (new FileReader($config->getAppendFile()))->getLines();
            $lineCount += (new FileWriter($config->outputFile, $lines))->append();
        }

        // Output some stats
        print_r([
            'ERRORS' => $converter->getErrors(),
            'UNKNOWN' => array_slice($converter->getUnknownStatements(), 0, 50),
            'UNSUPPORTED' => array_slice($converter->getUnsupportedStatements(), 0, 50),
            'LINES_WRITTEN' => $lineCount,
        ]);

        return 0;
    }
}
