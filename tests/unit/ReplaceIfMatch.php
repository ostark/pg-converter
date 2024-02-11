<?php

use function ostark\PgConverter\String\replace_if_match;

it('should replace multi line input', function () {

    // short poem
    $multi_line_input = <<<'POEM'
        I have eaten
        the plums
        that were in
        the icebox
    POEM;

    $converted = replace_if_match([
        '/the plums/' => 'the fish bones',
        '/no match/' => 'not found',
        '/the icebox/' => 'the trash bin',

    ], $multi_line_input);

    expect($converted)->toBe(<<<'POEM'
        I have eaten
        the fish bones
        that were in
        the trash bin
    POEM);

});

it('should capture an expression', function () {

    $input = 'MyClass::fooo(11111,true)';

    $converted = replace_if_match([
        '/fooo\(([0-9]*),([true|false]*)\)/' => 'bar($1,$2)',
    ], $input);

    expect($converted)->toBe('MyClass::bar(11111,true)');

});

it('should return the given input if no match', function () {

    $input = 'this the input';

    $converted = replace_if_match([
        '/no match/' => 'not found',
        '/no match either/' => 'not found either',
    ], $input);

    expect($converted)->toBe($input);

});
