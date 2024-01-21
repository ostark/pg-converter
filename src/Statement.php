<?php

namespace ostark\PgConverter;

interface Statement
{
    function setTable(string $table): self;
    function toSql(): string;
}
