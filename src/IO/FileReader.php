<?php

namespace ostark\PgConverter\IO;

/**
 * Here we try to implement a memory efficient way of reading the content of the given file name.
 * Instead of loading the whole string in memory we read it line-by-line.
 * From outside, we can iterate over the result that implements \Iterator
 *
 */
class FileReader
{
    private string $pathToFile;

    public function __construct(string $pathToFile)
    {
        if (!file_exists($pathToFile)) {
            throw new \InvalidArgumentException("Unable to locate file: $pathToFile");
        }

        $this->pathToFile = $pathToFile;
    }


    public function getLines(): \Iterator
    {
        $handle = fopen($this->pathToFile, 'r');

        if ($handle === false) {
            throw new \InvalidArgumentException("Unable to read file: {$this->pathToFile}");
        }

        while (false !== $line = fgets(stream: $handle)) {
            yield $line;
        }

        fclose($handle);
    }
}
