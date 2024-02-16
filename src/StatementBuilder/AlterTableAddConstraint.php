<?php

declare(strict_types=1);

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\StatementBuilder\BuilderResult\Error;
use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Success;

class AlterTableAddConstraint implements Statement
{
    private string $alterTable;

    private string $addConstraint;

    public function __construct(protected string $statement)
    {
        if (! str_contains($statement, 'ALTER TABLE')) {
            throw new \InvalidArgumentException('Invalid statement. Expected ALTER TABLE ...');
        }

        [$this->alterTable, $this->addConstraint] = explode(PHP_EOL, $this->statement, 2);

        if(! str_contains($this->addConstraint, 'ADD CONSTRAINT')) {
            throw new \InvalidArgumentException('Invalid statement. Expected ADD CONSTRAINT ...');
        }

    }

    public function make(): Result
    {

        $pattern = '/ALTER TABLE ONLY (?<schema>\w+).(?<table>\w+)(?<rest>.*)/';
        if (!preg_match($pattern, $this->alterTable, $matches)) {
            return new Error($this->statement, ['Could not parse statement.']);
        }

        $table =  $matches['table'];
        $schema = $matches['schema'];

        $statement = "ALTER TABLE {$table}" . PHP_EOL;
        $statement .= str_replace("{$schema}.", "", $this->addConstraint);

        return new Success($statement);
    }
}
