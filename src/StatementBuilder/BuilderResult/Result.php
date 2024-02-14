<?php

declare(strict_types=1);

namespace ostark\PgConverter\StatementBuilder\BuilderResult;

interface Result
{
    public function statement(): string;

    public function errors(): array;

    public function isError(): bool;

    public function isSuccess(): bool;
}
