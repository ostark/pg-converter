<?php

namespace ostark\PgConverter;

use ostark\PgConverter\StatementBuilder\AlterTableAddConstraint;
use ostark\PgConverter\StatementBuilder\AlterTableAutoIncrement;
use ostark\PgConverter\StatementBuilder\AlterTableSetDefault;
use ostark\PgConverter\StatementBuilder\BuilderResult\Error;
use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Skip;
use ostark\PgConverter\StatementBuilder\BuilderResult\Success;
use ostark\PgConverter\StatementBuilder\CreateIndex;
use ostark\PgConverter\StatementBuilder\CreateTable;
use ostark\PgConverter\StatementBuilder\InsertInto;
use ostark\PgConverter\StatementBuilder\Statement;

class Converter
{
    private array $errors = [];

    private array $unsupportedStatements = [];

    private array $unknownStatements = [];

    public function __construct(
        private \Iterator       $lines,
        private ConverterConfig $config)
    {
        //
    }

    public function convert(): \Iterator
    {
        $builder = new MultilineStatement();

        /** @var string $line */
        foreach ($this->lines as $line) {

            if ($this->shouldSkip($line)) {
                continue;
            }

            if ($this->isUnsupported($line)) {
                $this->unsupportedStatements[] = $line;
                continue;
            }

            // Multi-line handling
            if ($builder->isLastMultiline($line)) {
                $builder->add($line);
                yield $this->handleResult($builder->next());
                $builder->reset();

                continue;
            }

            if ($builder->isCollectingMultilines()) {
                $builder->add($line);

                continue;
            }

            // Below we detect different the sql statements
            // that spawn across multiple lines
            if (str_starts_with($line, 'COPY') && str_ends_with(rtrim($line), 'FROM stdin;')) {
                $builder = new MultilineStatement();
                $builder->setStopCharacter("\.");
                $builder->setNextHandler( fn($sql): Result => (new InsertInto($sql))->make() );

                $builder->add($line);

                continue;
            }

            // when using pg_dump with --column-inserts we get
            // INSERT INTO ... (col1, col2) VALUES (val1, val2)

            // when using pg_dump with --inserts we get


            if (str_starts_with($line, 'CREATE TABLE')) {
                $builder = new MultilineStatement();
                $builder->setStopCharacter(');');
                $builder->setNextHandler( fn($sql): Result => (new CreateTable($sql))->make() );

                $builder->add($line);

                continue;
            }

            if (str_starts_with($line, 'CREATE SEQUENCE')) {
                $builder = new MultilineStatement();
                $builder->setStopCharacter('CACHE 1;');
                $builder->setNextHandler( fn($sql): Result => (new AlterTableAutoIncrement($sql))->make() );

                $builder->add($line);

                continue;
            }

            // ALTER TABLE ONLY can contain one or two lines
            if (str_starts_with($line, 'ALTER TABLE ONLY')) {

                $one = $line;
                $this->lines->next();
                $two = trim($this->lines->current());

                // ALTER COLUMN SET DEFAULT nextval('')
                if (str_contains($one, 'SET DEFAULT')) {
                    yield $this->handleResult((new AlterTableSetDefault($one))->make());
                }

                // PRIMARY KEY, UNIQUE, FOREIGN KEY ...
                if (str_starts_with($two, 'ADD CONSTRAINT')) {
                    yield $this->handleResult((new AlterTableAddConstraint("$one $two"))->make());
                }

                continue;
            }

            if (str_starts_with($line, 'CREATE UNIQUE INDEX') || str_starts_with($line, 'CREATE INDEX')) {
                yield $this->handleResult((new CreateIndex($line))->make());

                continue;
            }

            // Collect unknown statements for debugging
            $this->unknownStatements[] = $line;
        }

    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getUnknownStatements(): array
    {
        return $this->unknownStatements;
    }


    private function shouldSkip(string $sql): bool
    {
        if ($pattern = $this->config->getInputFilter()) {
            return preg_match($pattern, $sql) !== false;
        }

        return false;
    }

    private function isUnsupported(string $line)
    {
        foreach (Statement::UNSUPPORTED as $unsupported) {
            if (str_starts_with($line, $unsupported)) {
                return true;
            }
        }

        // Lines to ignore
        if (str_starts_with($line, '--') || trim($line) === '') {
            // Skip, just a sql comment
            return true;
        }

        return false;
    }


    private function handleResult(Result $result): ?string
    {
        // Transformed statement
        $statement = $result->statement();

        // Happy path
        if ($result instanceof Success) {
            return $statement . PHP_EOL;
        }

        // Collect info about non-successful results
        $this->unsupportedStatements[] = $statement;

        // Return sql comment
        if ($this->config->verboseComments()) {
            $comment = "-- Skipped: $statement\n";
            $comment .= array_map(fn($e) => "-- $e\n", $result->errors());
            return $comment;
        }

        return '-- \n';

    }
}
