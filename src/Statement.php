<?php

namespace ostark\PgConverter;

use ostark\PgConverter\StatementBuilder\BuilderResult\Result;

interface Statement
{
    public function make(): Result;
}
