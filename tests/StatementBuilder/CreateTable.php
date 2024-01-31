<?php

it('should convert a create table statement', function () {

    $input = <<<'PGSQL'
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

    $createTable = new \ostark\PgConverter\StatementBuilder\CreateTable($input);

    $expected = <<<'MYSQL'
CREATE TABLE assetindexdata (
  `id` integer NOT NULL,
  `volumeId` integer NOT NULL,
  `uri` text,
  `size` DEFAULT ,
  `timestamp` timestamp,
  `recordId` integer,
  `inProgress` boolean DEFAULT 0,
  `completed` boolean DEFAULT 0,
  `dateCreated` timestamp NOT NULL,
  `dateUpdated` timestamp NOT NULL,
  `uid` varchar(36) DEFAULT '0' NOT NULL,
  `sessionId` integer NOT NULL,
  `isDir` boolean DEFAULT 0,
  `isSkipped` boolean DEFAULT 0
);
MYSQL;

    expect(trim($createTable->make($input)->statement()))->toBe(trim($expected));

});
