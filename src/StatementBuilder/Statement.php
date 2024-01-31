<?php

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\StatementBuilder\BuilderResult\Result;

interface Statement
{
    public function make(): Result;
}
