<?php

namespace ostark\PgConverter\StatementBuilder\BuilderResult;

class Skip extends AbstractResult implements Result
{

    function isError(): bool
    {
        return count($this->errors()) > 0;
    }

    function isSuccess(): bool
    {
        return count($this->errors()) === 0;
    }
}
