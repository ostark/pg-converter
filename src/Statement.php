<?php

namespace ostark\PgConverter;

interface Statement
{
    public function setTable(string $table): self;

    public function toSql(): string;
}
