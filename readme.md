# Laravel Migration Generator

This project is a web form for building Laravel migrations.

Set up your columns and types and it will generate the PHP code using nikic's php parser.

## To do:
* Support foreign keys
* Generate the full migration file
* Generate the `down()` for each migration
* Currently only adds columns
* if modifying existing schema, pull in existing tables
* currently project is a full laravel install - would be better as a package that people pull in as a dev dependency