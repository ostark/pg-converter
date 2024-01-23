<?php

namespace ostark\PgConverter\StatementBuilders;

use ostark\PgConverter\Statement;

class AlterTableAddConstraint implements \ostark\PgConverter\Statement
{

    function setTable(string $table): \ostark\PgConverter\Statement
    {
        // TODO: Implement setTable() method.
    }

    function toSql(): string
    {
        return "";
        // TODO: Implement toSql() method.
    }
}
