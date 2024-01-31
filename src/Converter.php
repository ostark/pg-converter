<?php

namespace ostark\PgConverter;

use ostark\PgConverter\StatementBuilder\AlterTableAddConstraint;
use ostark\PgConverter\StatementBuilder\AlterTableAutoIncrement;
use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Skip;
use ostark\PgConverter\StatementBuilder\CreateIndex;
use ostark\PgConverter\StatementBuilder\CreateTable;
use ostark\PgConverter\StatementBuilder\InsertInto;

class Converter
{
    private array $errors = [];

    private array $unknownStatements = [];

    public function __construct(
        private \Iterator $lines,
        private ConverterConfig $config)
    {
        //
    }

    public function convert(): \Iterator
    {
        $builder = new GenericMultiLine();

        /** @var string $line */
        foreach ($this->lines as $line) {

            if ($builder->isLastMultiline($line)) {
                $builder->add($line);
                $statement = $builder->toString();
                $name = $builder->getName();

                yield $this->convertStatement($name, $statement);

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
                $builder = new GenericMultiLine('COPY');
                $builder->setStopCharacter("\.");
                $builder->add($line);

                continue;
            }

            if (str_starts_with($line, 'CREATE TABLE')) {
                $builder = new GenericMultiLine('CREATE_TABLE');
                $builder->setStopCharacter(');');
                $builder->add($line);

                continue;
            }

            if (str_starts_with($line, 'CREATE SEQUENCE')) {
                $builder = new GenericMultiLine('CREATE_SEQUENCE');
                $builder->setStopCharacter('CACHE 1;');
                $builder->add($line);

                continue;
            }

            // CREATE UNIQUE INDEX "revisions_sourceId_num_unq_idx" ON public.revisions USING btree ("canonicalId", num);
            if (str_starts_with($line, 'CREATE UNIQUE INDEX') || str_starts_with($line, 'CREATE INDEX')) {
                yield $this->convertStatement('CREATE_INDEX', $line);

                continue;
            }

            if (str_starts_with($line, 'ALTER TABLE ONLY')) {

                $one = $line;
                $this->lines->next();
                $two = trim($this->lines->current());

                if (strstr($one, 'ALTER COLUMN')) {
                    yield $this->convertStatement('ALTER_COLUMN', $one);

                    continue;
                }

                if (str_starts_with($two, 'ADD CONSTRAINT')) {
                    // PRIMARY KEY, UNIQUE, FOREIGN KEY,
                    yield $this->convertStatement('ADD_CONSTRAINT', "$one $two");
                }

                continue;
            }

            if (str_starts_with($line, 'ALTER TABLE IF EXISTS')) {
                continue;
            }
            if (str_starts_with($line, 'DROP INDEX IF EXISTS')) {
                continue;
            }

            if (str_starts_with($line, 'DROP SEQUENCE IF EXISTS')) {
                continue;
            }

            if (str_starts_with($line, 'ALTER SEQUENCE')) {
                // Deferred? (auto increment)
                continue;
            }
            if (str_starts_with($line, 'DROP TABLE IF EXISTS')) {
                continue;
            }
            //if (str_starts_with($line, 'ALTER TABLE ONLY') && strstr($line, '::regclass)')) {
            //    continue;
            //}
            if (str_starts_with($line, 'SELECT pg_catalog')) {
                continue;
            }
            if (str_starts_with($line, 'SET ')) {
                continue;
            }
            if (str_starts_with($line, 'CREATE SCHEMA')) {
                continue;
            }
            if (str_starts_with($line, 'DROP SCHEMA')) {
                continue;
            }
            if (str_starts_with($line, 'COMMENT ON SCHEMA')) {
                continue;
            }

            // Lines to ignore
            if (str_starts_with($line, '--') || trim($line) === '') {
                // Skip, just a sql comment
                continue;
            }

            // No condition matches
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

    private function convertStatement(?string $type, string $sql): Result
    {
        if ($this->shouldSkip($sql)) {
            return new Skip($sql);
        }

        return match ($type) {
            'CREATE_TABLE' => (new CreateTable($sql))->make(),
            'CREATE_INDEX' => (new CreateIndex($sql))->make(),
            'CREATE_SEQUENCE' => (new AlterTableAutoIncrement($sql))->make(),
            'ADD_CONSTRAINT' => (new AlterTableAddConstraint($sql))->make(),
            'COPY' => (new InsertInto($sql))->make(),
            default => throw new \Exception("Unsupported Statement: $type"),
        };
    }

    private function shouldSkip(string $sql): bool
    {
        if ($pattern = $this->config->getInputFilter()) {
            return preg_match($pattern, $sql) !== false;
        }

        return false;
    }
}
