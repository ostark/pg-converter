<?php

use function ostark\PgConverter\String\replace;

it('should convert a create table statement', function () {

    $input = <<<PGSQL
CREATE TABLE public.assetindexdata (
    id integer NOT NULL,
    "volumeId" integer NOT NULL,
    uri text,
    size bigint,
    "timestamp" timestamp(0) without time zone,
    "recordId" integer,
    "inProgress" boolean DEFAULT false,
    completed boolean DEFAULT false,
    "dateCreated" timestamp(0) without time zone NOT NULL,
    "dateUpdated" timestamp(0) without time zone NOT NULL,
    uid character(36) DEFAULT '0'::bpchar NOT NULL,
    "sessionId" integer NOT NULL,
    "isDir" boolean DEFAULT false,
    "isSkipped" boolean DEFAULT false
);
PGSQL;

    $expected = <<<MYSQL
CREATE TABLE users (
    
);   
MYSQL;

    $createTable = new \ostark\PgConverter\StatementBuilder\CreateTable($input);

    expect($createTable->make($input)->statement())->toBe('');

    expect($createTable->make($input)->statement())->toBe($expected);

});
