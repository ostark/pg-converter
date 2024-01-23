<?php

namespace ostark\PgConverter;

use ostark\PgConverter\StatementBuilders\CreateIndex;
use ostark\PgConverter\StatementBuilders\CreateTable;
use ostark\PgConverter\StatementBuilders\GenericMultiLine;
use ostark\PgConverter\StatementBuilders\InsertInto;

class Converter
{
    private array $errors = [];
    private array $unknownStatements = [];

    public function __construct(
        private \Iterator $lines,
        private string $schema,
        private array $skippedTables = [])
    {

    }


    public function convert(): \Iterator
    {
        $builder = new GenericMultiLine();

        foreach ($this->lines as $line) {

            if ($builder->isCollectingMultilines()) {
                $builder->add($line);
            }

            if ($builder->isLastMultiline($line)) {

                $statement = $builder->toString();
                $name = $builder->getName();

                yield $this->convertStatement($name, $statement);

                $builder->reset();
                continue;
            }

            // Below we detect different the sql statements
            // Most of them contain of multiple lines

            if (str_starts_with($line, "CREATE TABLE")) {

                $builder = new GenericMultiLine("CREATE_TABLE");
                $builder->setStopCharacter(");");
                $builder->add($line);

                continue;
            }

            if (str_starts_with($line, "COPY") && str_ends_with($line, "FROM stdin;")) {
                $builder = new GenericMultiLine("COPY");
                $builder->setStopCharacter("\.");
                $builder->add($line);

                continue;
            }
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


    private function convertStatement(?string $type, string $sql): string
    {
        return match($type) {
            'CREATE_TABLE' => (new CreateTable($sql))->toSql(),
            'CREATE_INDEX' => (new CreateIndex($sql))->toSql(),
            'COPY' => (new InsertInto($sql))->toSql(),
            default => throw new \Exception("Unsupported Statement: $type"),
        };



    }

}
