<?php

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\StatementBuilder\BuilderResult\Result;

interface Statement
{
    public const UNSUPPORTED = [
        'ALTER TABLE IF EXISTS',
        'DROP INDEX IF EXISTS',
        'DROP SEQUENCE IF EXISTS',
        'ALTER SEQUENCE',
        'DROP TABLE IF EXISTS',
        'CREATE EXTENSION',
        'SELECT pg_catalog',
        'SET ', // <-- space is intentional
        'CREATE SCHEMA',
        'DROP SCHEMA',
        'COMMENT ON SCHEMA',
    ];

    public function make(): Result;
}
