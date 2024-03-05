<?php

declare(strict_types=1);

namespace ostark\PgConverter\IO;

class FileWriter
{
    public function __construct(public string $file, readonly public \Iterator $lines)
    {
        // ...
    }

    public function store(string $mode = 'w'): int
    {
        $lineCount = 0;
        if (! $handle = fopen($this->file, $mode)) {
            throw new \Exception("Unable to write file: $this->file");
        }

        /** @var string $line */
        foreach ($this->lines as $line) {
            if (fwrite($handle, $line . PHP_EOL)) {
                $lineCount++;
            }
        }

        fclose($handle);

        return $lineCount;
    }

    public function append(): int
    {
        return $this->store('a');
    }
}
