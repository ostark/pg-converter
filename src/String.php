<?php

namespace ostark\PgConverter\String;

/**
 * @param  array<string, string>  $mapping  $mapping
 */
function replace_if_match(array $mapping, string $subject): string
{
    foreach ($mapping as $pattern => $replacement) {
        $result = \preg_replace($pattern, $replacement, $subject);
        if (is_string($result)) {
            $subject = $result;
        }
    }

    return $subject;

}
