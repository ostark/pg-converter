<?php

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\Statement;
use ostark\PgConverter\StatementBuilder\BuilderResult\Error;
use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Skip;
use ostark\PgConverter\StatementBuilder\BuilderResult\Success;

class CreateIndex implements Statement
{
    use Helper;

    public function __construct(protected string $statement)
    {
        // ...
    }


    public function make(): Result
    {
        $pattern = '/CREATE INDEX (?<index_name>\w+) ON (?<schema_name>\w+).(?<table_name>\w+) USING (?<index_type>\w+) \((?<column_names>.*)\)/';
        preg_match($pattern, $this->statement, $matches);

        $index = $this->prepareIndex($matches['index_name']);
        $table = $this->prepareTable($matches['table_name']);
        $columns = $this->prepareColumns($matches['column_names']);

        if ($matches['index_type'] == 'btree') {

            return new Success(statement: "CREATE INDEX $index ON $table ($columns)");
        }

        if ($matches['index_type'] == 'gin') {

            // CASE 1: gin -> full text index
            // CASE 2: gin -> handle json indexes

            return new Skip("-- GIN is not supported yet");
        }


        $sql =  "-- Skipping index: {$matches['index_name']} of table: {$matches['table_name']}";
        $sql .= "-- because index type: {$matches['index_type']} is not supported.";

        return new Skip($sql);

    }
}





