<?php

namespace ostark\PgConverter\StatementBuilder\BuilderResult;

interface Result
{
    function statement(): string;

    function table(): string;

    function errors(): array;

    function isError(): bool;
    
    function isSuccess(): bool;
}
