<?php

use ostark\PgConverter\StatementBuilder\AlterTableAutoIncrement;

it('throws an exception if not ALTER SEQUENCE statement', function () {
    new AlterTableAutoIncrement('ALTER TABLE foo');
})->throws(\InvalidArgumentException::class);

it('turns ALTER SEQUENCE into AUTO_INCREMENT statement', function () {
    $pgsql = new AlterTableAutoIncrement('ALTER SEQUENCE public.foo_seq OWNED BY public.foo.some_field;');
    $mysql = 'ALTER TABLE `foo` MODIFY `some_field` int unsigned NOT NULL AUTO_INCREMENT;';

    expect($pgsql->make()->statement())->toBe($mysql);
});
