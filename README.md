
Supported statements


* CREATE TABLE
* CREATE INDEX
* ALTER SEQUENCE
* ALTER TABLE ONLY



pg-convert 
    <src> Postgres source file
    <target> Mysql target file
    --excludeTables=
    --excludeIndexes="searchindex_keywords_idx
    --appendString="CREATE FULLTEXT INDEX keywords_idx ON searchindex(keywords);"
    --appendFile="file_with_mysql_statements.sql"
    






---

This package is based the wonderful [PHP Skeleton](https://github.com/nunomaduro/skeleton-php/) by Nuno Maduro.

