<?php

namespace ostark\PgConverter\StatementBuilders;

use ostark\PgConverter\Statement;

class CreateIndex implements Statement
{
    public function __construct(protected string $statement)
    {
        // ...
    }

    public function setTable(string $table): Statement
    {
        // TODO: Implement setTable() method.
    }

    public function toSql(): string
    {
        return '';

        return 'MODIFIED:'.$this->statement.PHP_EOL;
    }
}
