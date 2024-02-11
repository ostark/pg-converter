<?php

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Success;

class AlterTableAddConstraint implements Statement
{
    public function __construct(protected string $statement)
    {
        if (! str_contains($statement, 'ALTER TABLE')) {
            throw new \InvalidArgumentException('Invalid statement. Expected ALTER TABLE ...');
        }
    }

    public function make(): Result
    {
        return new Success('-- TODO: ALTER TABLE ... ADD CONSTRAINT ...');
    }
}
