<?php

namespace ostark\PgConverter\IO;

class FileWriter
{
    public function __construct(readonly public \Iterator $lines, public string $file )
    {
        // ...
    }

    public function store()
    {
        $handle = fopen($this->file, 'w');

        foreach ($this->lines as $line) {
            echo $line;
            fwrite($handle, $line);
        }

        fclose($handle);
    }


}
