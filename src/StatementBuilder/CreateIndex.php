<?php

namespace ostark\PgConverter\StatementBuilder;

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
        // CREATE UNIQUE INDEX "revisions_sourceId_num_unq_idx" ON public.revisions USING btree ("canonicalId", num);
        // CREATE INDEX "fooo" ON public.sometable USING btree (id);

        $pattern = '/(?<statement>CREATE UNIQUE INDEX|CREATE INDEX) (?<index_name>\w+) ON (?<schema_name>\w+).(?<table_name>\w+) USING (?<index_type>\w+) \((?<column_names>.*)\)/';

        if (! preg_match($pattern, $this->statement, $matches)) {
            return new Error($this->statement, ['Could not parse statement.']);

        }

        $statement = $matches['statement'];
        $index = $this->prepareIndex($matches['index_name']);
        $type = $matches['index_type'];
        $table = $this->prepareTable($matches['table_name']);
        $columns = $this->prepareColumns($matches['column_names']);

        if ($type !== 'btree') {
            return new Skip($this->statement, ["Index type: {$type} is not supported."]);
        }

        return new Success(statement: "$statement $index ON $table ($columns);");

    }
}
