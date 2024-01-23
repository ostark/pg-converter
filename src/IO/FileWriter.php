<?php

namespace ostark\PgConverter\IO;

class FileWriter
{
    public function __construct(readonly public \Iterator $lines, public string $file )
    {
        // ...
    }

    public function store(): int
    {
        $lineCount = 0;
        $handle = fopen($this->file, 'w');

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
