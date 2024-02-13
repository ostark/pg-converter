<?php

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Success;
use function PHPUnit\Framework\matches;

class InsertInto implements Statement
{
    private const COPY_COLUMN_DELIMITER = "\t";

    use Helper;

    public function __construct(protected string $statement)
    {
        if (! str_starts_with($statement, 'COPY')) {
            throw new \InvalidArgumentException('Invalid statement. Expected ALTER TABLE ...');
        }
    }

    public function make(): Result
    {
        // parse copy statement, count columns, create insert statement
        $lines = explode(PHP_EOL, $this->statement);
        $head = array_shift($lines);

        // remove last line (\.)
        array_pop($lines);

        preg_match('/COPY (?<schema>\w+).(?<table>\w+) \((?<columns>.*)\) FROM stdin;/', $head, $matches);
        $table = $matches['table'];
        $columns = $this->prepareColumns($matches['columns']);
        $insert = "INSERT IGNORE INTO `{$table}` ({$columns})";

        foreach ($lines as $key => $valueLine) {
            $values = explode(self::COPY_COLUMN_DELIMITER, $valueLine);
            $values = $this->prepareValues($values);

            $line = array_map(fn ($v) => implode(', ', $v), $values);
            $line = "({$line})";
            $lines[$key] = $line;
        }

        $valueLines = implode(", \n", $lines);

        return new Success("{$insert} \n VALUES \n {$valueLines};");
    }

    private function prepareValues(array $values): array
    {
        $values = array_map(fn ($v) =>  trim($v, '"'), $values);

        // fix special values
        foreach ($values as $key => $value) {
            $values[$key] = match ($value) {
                '\N' => 'NULL',
                'true' => 1,
                'false' => 0,
                default => (is_string($value)) ? "'{$value}'" : $value
            };
        }

        return $values;
    }
}
