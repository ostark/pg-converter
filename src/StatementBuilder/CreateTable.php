<?php

namespace ostark\PgConverter\StatementBuilder;

use Composer\Pcre\PcreException;
use Composer\Pcre\Preg;
use ostark\PgConverter\Statement;
use ostark\PgConverter\StatementBuilder\BuilderResult\Error;
use ostark\PgConverter\StatementBuilder\BuilderResult\Result;
use ostark\PgConverter\StatementBuilder\BuilderResult\Success;
use function ostark\PgConverter\String\replace_if_match;

/*
 * Example statement
 *
 * CREATE TABLE public.plugins (
 *   id integer NOT NULL,
 *   handle character varying(255) NOT NULL,
 *   version character varying(255) NOT NULL,
 *   "schemaVersion" character varying(255) NOT NULL,
 *   "licenseKeyStatus" character varying(255) DEFAULT 'unknown'::character varying NOT NULL,
 *   "licensedEdition" character varying(255),
 *   "installDate" timestamp(0) without time zone NOT NULL,
 *   "dateCreated" timestamp(0) without time zone NOT NULL,
 *   "dateUpdated" timestamp(0) without time zone NOT NULL,
 *   uid character(36) DEFAULT '0'::bpchar NOT NULL,
 *   CONSTRAINT "plugins_licenseKeyStatus_check" CHECK ((("licenseKeyStatus")::text = ANY (ARRAY[('valid'::character varying)::text, ('trial'::character varying)::text, ('invalid'::character varying)::text, ('mismatched'::character varying)::text, ('astray'::character varying)::text, ('unknown'::character varying)::text])))
 *);
 */

class CreateTable implements Statement
{

    private array $lines = [];

    private string $table;

    public function __construct(protected string $statement)
    {
        $this->lines = explode(PHP_EOL, $statement);

        if (count($this->lines) <= 3) {
            throw new \InvalidArgumentException('Invalid statement. Expected at least 3 lines');
        }

        $this->table = $this->extractTableName($this->lines[0]);

        // Remove first and last line
        array_shift($this->lines);
        array_pop($this->lines);
    }


    public function make(): Result
    {
        $lines = array_map(fn($line) => $this->convertLine($line), $this->lines);
        $lines = array_filter($lines);

        if (count($lines) === 0) {
            return new Error("-- ERROR: No fields after conversion for CREATE TABLE {$this->table} ... ", $this->table);
        }

        // Indent lines
        $lines = array_map(fn($line) => "  {$line}", $lines);

        $start = "CREATE TABLE {$this->table} (\n";
        $fieldDefinitions = rtrim(implode(PHP_EOL, $lines), ',');
        $end = "\n);";

        return new Success($start . $fieldDefinitions . $end, $this->table);
    }


    /**
     * Example lines
     * - name character varying(255) NOT NULL,
     * - "licenseKeyStatus" character varying(255) DEFAULT 'unknown'::character varying NOT NULL,
     * - "anotherField" integer,
     */
    private function convertLine(string $line): ?string
    {
        $parts = explode(' ', trim($line), 2);
        $field = trim($parts[0], '"');
        $def = $parts[1];

        // Handle CONSTRAINTs, most we just skip
        // Exception: CONSTRAINT users_pkey PRIMARY KEY (id));
        if ('CONSTRAINT' === $field) {
            if ($def = $this->handleConstraint($def)) {
                return $def;
            }
            return null;
        }

        // Handle character varying and other text types
        if (preg_match("/character varying\((?<length>\d+)\)/", $def, $matches)) {
            $length = $matches['length'];
            $def = ($length > 255)
                ? str_replace("character varying({$length})", "text", $def)
                : str_replace("character varying({$length})", "varchar({$length})", $def);
        }
        if (str_contains($def, "character varying")) {
            $def = str_replace("character varying", "varchar(255)", $def);
        }
        foreach (['character', 'bpchar', 'char'] as $type) {
            if (str_starts_with($def, "$type(")) {
                $def = str_replace("$type(", "varchar(", $def);
            }
        }

        // Special types
        $def = strtr($def, [
            'serial' => 'integer auto_increment',
            'uuid' => 'varchar(36)',
            'character' => 'varchar(255)'
        ]);

        // Common types, but slightly different syntax
        $def = strtr($def, [
            'int_unsigned' => 'integer UNSIGNED',
            'smallint_unsigned' => 'smallint UNSIGNED',
            'bigint_unsigned' => 'bigint UNSIGNED',
            'bytea' => 'BLOB',
            'boolean' => 'bool',
            'jsonb' => 'json',
        ]);

        // Array types
        $def = str_replace(['text[]', 'character varying[]'], 'longtext', $def);

        // Replace integer default values
        if (preg_match("/DEFAULT \'(?<default_int>\d+)\'::int|smallint|bigint[^ ,]", $def, $matches)) {
            $default = $matches['default_int'];
        }


            // DEFAULT \('([0-9]*)'::bigint/

        // Strip  sequence defaults
        $def = str_replace('DEFAULT nextval\(', '', $def);

        // Strip extra type info
        $def=preg_replace("/::.*,/",",",$def);
        $def=preg_replace("/::.*$/","\n",$def);

        // Convert time and timestamptypes
        $def = replace_if_match([
            'time(\([0-6]\))? with time zone' => 'time',
            'time(\([0-6]\))? without time zone' => 'time',
            'timestamp(\([0-6]\))? with time zone' => 'timestamp',
            '/timestamp(\([0-6]\))? without time zone/' => 'timestamp',
            '/timestamp(\([0-6]\))? DEFAULT now()/' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            '/timestamp DEFAULT now()/' => 'timestamp DEFAULT CURRENT_TIMESTAMP'
        ], $def);


        // Convert other exotic types to varchar
        $def = str_replace(['cidr', 'inet', 'macaddr', 'money', 'interval', 'longtext DEFAULT [^,]*( NOT NULL)?'], ['varchar(32)', 'varchar(32)', 'varchar(32)', 'varchar(32)', 'varchar(64)', 'longtext'], $def);

        // Strip function defaults
        $def = str_replace(['DEFAULT .*\(\)'], '', $def);

        // Handle json_build_object defaults
        $def = str_replace('DEFAULT json_build_object\((.*)\)', 'DEFAULT json_object($1)', $def);

        // Handle text types with defaults
        $def = str_replace('longtext DEFAULT [^,]*', 'longtext', $def);


        // Add backticks to field names and concat with definition
        return "`{$field}` {$def}";
    }

    private function extractTableName(string $sql): string
    {
        $pattern = '/CREATE TABLE (?<schema_name>\w+).(?<table_name>\w+) \(/';
        preg_match($pattern, $sql, $matches);

        return $matches['table_name'];
    }

    private function handleConstraint(string $def): ?string
    {
        if (str_contains($def, 'PRIMARY KEY')) {
            if (preg_match('/PRIMARY KEY \((?<columns>.*)\)/', $def, $matches)) {
                $cols = $matches['columns'];
                $cols = str_replace('"', '`', $cols);
                return "PRIMARY KEY ({$cols}),";
            }
        }

        return null;

    }
}
