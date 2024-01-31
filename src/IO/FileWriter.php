<?php

namespace ostark\PgConverter\IO;

class FileWriter
{
    public function __construct(public string $file, readonly public \Iterator $lines)
    {
        // ...
    }

    public function store(): int
    {
        $lineCount = 0;
        if (! $handle = fopen($this->file, 'w')) {
            throw new \Exception("Unable to write file: $this->file");
        }

        /** @var string $line */
        foreach ($this->lines as $line) {
            $lineCount++;
            if (fwrite($handle, $line)) {
                $lineCount++;
            }
        }

        fclose($handle);

        return $lineCount;
    }
}
