<?php

namespace ostark\PgConverter\StatementBuilders;

use ostark\PgConverter\Statement;
use ostark\PgConverter\StatementBuilders;

class CreateTable implements Statement
{
    public function __construct(protected string $statement)
    {
        // ...
    }

    function setTable(string $table): Statement
    {
        // TODO: Implement setTable() method.
        return $this;
    }

    function toSql(): string
    {
        return "MODIFIED:" . $this->statement . PHP_EOL;
    }
}
