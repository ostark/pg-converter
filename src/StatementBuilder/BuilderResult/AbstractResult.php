<?php

namespace ostark\PgConverter\StatementBuilder\BuilderResult;

abstract class AbstractResult
{
    public function __construct(
        protected string $statement,
        protected string $table = '',
        protected array $errors = []
    ) { }

    public function statement(): string
    {
        return $this->statement . PHP_EOL . PHP_EOL;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
