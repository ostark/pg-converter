<?php

declare(strict_types=1);

namespace ostark\PgConverter\StatementBuilder;

use InvalidArgumentException;
use ostark\PgConverter\StatementBuilder\BuilderResult\Error;
use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Success;

class AlterTableAutoIncrement implements Statement
{
    public function __construct(protected string $statement)
    {
        if (! str_contains($statement, 'ALTER SEQUENCE')) {
            throw new InvalidArgumentException('Invalid statement. Expected ALTER SEQUENCE ...');
        }
    }

    public function make(): Result
    {
        $pattern = '/ALTER SEQUENCE public.(?<sequence>\w+) OWNED BY public.(?<table_name>\w+).(?<column_name>\w+);/';

        if (preg_match($pattern, $this->statement, $matches)) {
            $table = $matches['table_name'];
            $column = $matches['column_name'];

            return new Success("ALTER TABLE `{$table }` MODIFY `{$column}` int unsigned NOT NULL AUTO_INCREMENT;");
        }

        return new Error($this->statement, ['Could not parse statement.']);

    }
}
