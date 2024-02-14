<?php

declare(strict_types=1);

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Skip;

class AlterTableSetDefault implements Statement
{
    public function __construct(protected string $statement)
    {
        // Nothing to do,
    }

    public function make(): Result
    {
        return new Skip($this->statement);
    }
}
