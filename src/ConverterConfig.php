<?php

namespace ostark\PgConverter;

class ConverterConfig
{
    public const OPTION_INPUT_FILTER = 'input-filter';

    public const OPTION_APPEND_STRING = 'append-string';

    public const OPTION_APPEND_FILE = 'append-file';

    public function __construct(public string $inputFile, public string $outputFile, private array $options = [])
    {
        if (! file_exists($inputFile)) {
            throw new \InvalidArgumentException("Input file does not exist: {$inputFile}");
        }

        $outputFile = realpath($outputFile) ?: '';

        if (! is_dir(dirname($outputFile))) {
            throw new \InvalidArgumentException("Output directory does not exist: {$outputFile}");
        }

        if (isset($options[self::OPTION_INPUT_FILTER])) {
            $this->setInputFilter($options[self::OPTION_INPUT_FILTER]);
        }

        if (isset($options[self::OPTION_APPEND_STRING])) {
            $this->setAppendString($options[self::OPTION_APPEND_STRING]);
        }

        if (isset($options[self::OPTION_APPEND_FILE])) {
            $this->setAppendFile($options[self::OPTION_APPEND_FILE]);
        }

    }

    public function getInputFilter(): ?string
    {
        return $this->options[self::OPTION_INPUT_FILTER] ?? null;
    }

    public function getAppendString(): ?string
    {
        return $this->options[self::OPTION_APPEND_STRING] ?? null;
    }

    public function getAppendFile(): ?string
    {
        return $this->options[self::OPTION_APPEND_FILE] ?? null;
    }

    private function setInputFilter(string $regex): void
    {
        // Wrap regex pattern with delimiters
        $regex = sprintf('/%s/', trim($regex, '/'));

        try {
            preg_match($regex, '');
        } catch (\Throwable $exception) {
            throw new \InvalidArgumentException(sprintf(
                '--%s expects a valid regex: %s',
                self::OPTION_INPUT_FILTER,
                $regex
            ));
        }

        $this->options[self::OPTION_INPUT_FILTER] = $regex;
    }

    private function setAppendString(string $statement): void
    {
        $this->options[self::OPTION_APPEND_STRING] = $statement;
    }

    private function setAppendFile(string $file): void
    {
        if (! file_exists($file)) {
            throw new \InvalidArgumentException(sprintf(
                '--%s expects a valid file: %s',
                self::OPTION_APPEND_FILE,
                $file
            ));
        }

        $this->options[self::OPTION_APPEND_FILE] = $file;
    }
}
