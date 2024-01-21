<?php

namespace ostark\PgConverter\Statement;

use ostark\PgConverter\Statement;

class InsertInto implements Statement
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
        return "MODIFIED:" . $this->statement . PHP_EOL;
    }
}
