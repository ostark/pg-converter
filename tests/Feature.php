<?php

use ostark\PgConverter\MainCommand;

it('tmp', function () {


    $line = "\"licenseKeyStatus\" character varying(255) DEFAULT 'unknown'::character varying NOT NULL,";
    $parts = explode(' ', $line, 2);

    $field = trim($parts[0], '"');
    $def = rtrim($parts[1], ',');



    expect([])->toBe([]);
});
