<?php
 namespace ostark\PgConverter\String;


 function replace_all(array $mapping, string $subject): string
 {
     foreach ($mapping as $pattern => $replacement) {
         $result = \preg_replace($pattern, $replacement, $subject);
         if (is_string($result)) {
             $subject = $result;
         }
     }

     return $subject;

 }
