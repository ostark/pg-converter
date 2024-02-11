<?php

namespace ostark\PgConverter\StatementBuilder;

use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Success;

class InsertInto implements Statement
{
    private const COPY_COLUMN_DELIMITER = "\t";

    use Helper;

    public function __construct(protected string $statement)
    {
        if (! str_starts_with($statement, 'COPY')) {
            throw new \InvalidArgumentException('Invalid statement. Expected ALTER TABLE ...');
        }
    }

    public function make(): Result
    {
        $copyStatement = 'COPY public.usergroups (id, name, handle, "dateCreated", "dateUpdated", uid, description) FROM stdin;
1	Site Administrators	siteAdministrators	2020-01-18 00:39:17	2020-01-18 00:39:17	2baf1b70-dad1-4c27-b485-a94b23a3e89f	\N
2	Content Managers	contentManagers	2020-01-18 00:39:17	2020-01-18 00:39:17	823ac85e-6b3c-499a-bbdc-99a4e2432138	\N
\.
';

        // \N or \\N is a NULL in COPY statements
        // other special characters are escaped with a backslash: \t, \r, \n, \b, \f, \v, \a, \, and \
        // other special columns that differ in mysql: dateCreated, dateUpdated, uid
        // what about the "E" in the first line?

        // parse copy statement, count columns, create insert statement
        $lines = explode(PHP_EOL, $copyStatement);
        $head = array_shift($lines);
        $end = array_pop($lines);

        preg_match('/COPY (?<schema>\w+).(?<table>\w+) \((?<columns>.*)\) FROM stdin;/', $head, $matches);
        $table = $matches['table'];
        $columns = $this->prepareColumns($matches['columns']);
        $head = "INSERT INTO `{$table}` ({$columns})\n VALUES";

        foreach ($lines as $valueLine) {

        }

        $columns = explode(self::COPY_COLUMN_DELIMITER, $lines[0]);
        $columns = array_map(fn ($column) => trim($column, '"'), $columns);
        $columns = array_map(fn ($column) => "`{$column}`", $columns);
        $columns = implode(', ', $columns);

        $values = array_slice($lines, 1, -1);
        $values = array_map(fn ($value) => explode(self::COPY_COLUMN_DELIMITER, $value), $values);
        $values = array_map(fn ($value) => array_map(fn ($value) => trim($value, '"'), $value), $values);
        $values = array_map(fn ($value) => array_map(fn ($value) => "'{$value}'", $value), $values);
        $values = array_map(fn ($value) => implode(', ', $value), $values);
        $values = array_map(fn ($value) => "({$value})", $values);
        $values = implode(', ', $values);

        $insertStatement = "INSERT INTO `usergroups` ({$columns}) VALUES {$values};";

        return new Success('-- TODO: INSERT INTO ...');
    }
}
