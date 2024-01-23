<?php

namespace ostark\PgConverter\StatementBuilders;

use ostark\PgConverter\Statement;

class AlterTableAutoIncrement implements Statement
{

    public function __construct(protected string $statement)
    {
        // ...
    }

    function setTable(string $table): Statement
    {
        // TODO: Implement setTable() method.
    }

    function toSql(): string
    {
        echo "MODIFIED:" . $this->statement . PHP_EOL;
        return "";


    }
}
