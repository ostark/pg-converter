<?php

declare(strict_types=1);

namespace ostark\PgConverter\StatementBuilder\BuilderResult;

class Error extends AbstractResult implements Result
{
    public function isError(): bool
    {
        return true;
    }

    public function isSuccess(): bool
    {
        return false;
    }
}
