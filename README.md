# PG Converter

PG Converter is a command-line utility crafted to simplify the process of converting PostgreSQL dumps into dumps compatible with MySQL.

While the SQL grammar of both database engines shares similarities, there are distinctions in certain areas. This tool specifically focuses on converting SQL statements generated by [pg_dump](https://www.postgresql.org/docs/current/app-pgdump.html), which include commands for creating tables, indexes, constraints, and inserting data.


## State of the project (2024-03-05)

If you discover this repository by accident, you will notice the tool is not ready to use. But you can look around and help with bug reports, PRs are appreciated too!
Supported (`pg_dump`) statements so far: 

1. [x] CREATE TABLE
2. [x] CREATE INDEX
3. [x] ALTER SEQUENCE
4. [x] ALTER TABLE ONLY
5. [x] COPY


## Example 

```bash
php bin/pg-convert 
    <src> Postgres source file
    <target> Mysql target file
    --filterInput="^CREATE INDEX searchindex_keywords_idx"
    --appendString="CREATE FULLTEXT INDEX keywords_idx ON searchindex(keywords);"
```




## TODOs

- [ ] e2e tests
- [ ] integration tests



---

This package is inspired by a Perl script by [Tim Sehn](https://github.com/dolthub/pg2mysql), and it is 
based on the wonderful [PHP Skeleton](https://github.com/nunomaduro/skeleton-php/) by Nuno Maduro. 

