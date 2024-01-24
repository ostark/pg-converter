<?php

namespace ostark\PgConverter\StatementBuilders;

use ostark\PgConverter\Statement;

class CreateTable implements Statement
{
    public function __construct(protected string $statement)
    {
        // ...
    }

    public function setTable(string $table): Statement
    {
        // TODO: Implement setTable() method.
        return $this;
    }

    public function toSql(): string
    {
        return '';

        return 'MODIFIED:'.$this->statement.PHP_EOL;
    }
}
