<?php

namespace ostark\PgConverter\StatementBuilder;

use function ostark\PgConverter\String\replace_if_match;

trait Helper
{
    protected function prepareIndex(string $raw): string
    {
        return $raw;
    }

    protected function prepareTable(string $raw): string
    {
        return $raw;
    }

    // lower((email)::text), "quotedColumn", unquotedColumn
    // >> `email`, `quotedColumn`, `unquotedColumn`
    protected function prepareColumns(string $raw): string
    {
        $list = explode(',', $raw);
        $list = array_map(function ($column) {
            $column = trim($column);
            $column = str_replace('"', '', $column);
            $column = replace_if_match(['/\((.*)\)/' => '$1'], $column);

            return "`$column`";
        }, $list);

        return implode(', ', $list);
    }
}
