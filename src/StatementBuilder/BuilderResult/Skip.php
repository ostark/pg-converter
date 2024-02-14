<?php

declare(strict_types=1);

namespace ostark\PgConverter\StatementBuilder\BuilderResult;

class Skip extends AbstractResult implements Result
{
    public function __construct(string $statement, array $errors = [])
    {
        // Limit sql to two lines
        $this->statement = implode(PHP_EOL, array_slice(explode(PHP_EOL, $statement), 0, 2));

        $this->errors = $errors;
    }

    public function isError(): bool
    {
        return count($this->errors()) > 0;
    }

    public function isSuccess(): bool
    {
        return count($this->errors()) === 0;
    }
}
