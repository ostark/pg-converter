<?php

namespace ostark\PgConverter\StatementBuilder\BuilderResult;

abstract class AbstractResult
{
    public function __construct(
        protected string $statement,
        protected array $errors = []
    ) {
    }

    public function statement(): string
    {
        return $this->statement;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
