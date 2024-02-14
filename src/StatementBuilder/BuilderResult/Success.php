<?php

declare(strict_types=1);

namespace ostark\PgConverter\StatementBuilder\BuilderResult;

class Success extends AbstractResult implements Result
{
    public function isError(): bool
    {
        return false;
    }

    public function isSuccess(): bool
    {
        return true;
    }
}
