<?php

declare(strict_types=1);

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\StatementBuilder\BuilderResult\Error;
use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Success;

class DropTableIfExists implements Statement
{
    public function __construct(
        private readonly string $statement
    ) {}

    public function make(): Result
    {
        $pattern = '/DROP TABLE IF EXISTS (?<schema>\w+).(?<table>\w+);/';

        if (!preg_match($pattern, $this->statement, $matches)) {
            return new Error($this->statement, ['Could not parse statement.']);
        }

        return new Success("DROP TABLE IF EXISTS {$matches['table']};");
    }
}
