<?php

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Success;

class InsertInto implements Statement
{
    public function __construct(protected string $statement)
    {
        // ...
    }

    public function make(): Result
    {
        return new Success('-- TODO: INSERT INTO ...');
    }
}
