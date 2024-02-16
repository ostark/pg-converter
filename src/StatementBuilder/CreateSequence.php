<?php

declare(strict_types=1);

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Skip;

class CreateSequence implements Statement
{
    public function __construct(
        private readonly string $statement
    ) {}

    public function make(): Result
    {
        return new Skip($this->statement, ['Sequences are not supported.']);
    }
}
