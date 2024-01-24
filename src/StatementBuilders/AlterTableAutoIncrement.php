<?php

namespace ostark\PgConverter\StatementBuilders;

use ostark\PgConverter\Statement;

class AlterTableAutoIncrement implements Statement
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
        echo 'MODIFIED:'.$this->statement.PHP_EOL;

        return '';

    }
}
