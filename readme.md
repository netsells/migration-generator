# Laravel Migration Generator

This project is a web form for building Laravel migrations.

Set up your columns and types and it will generate the PHP code using nikic's php parser.

## To do:
* ~~Support foreign keys~~
* ~~Generate the full migration file~~
* Generate the `down()` for each migration, we have all the information to generate the columns, the reverse 
is usually just dropping the column
* Currently only adds columns
* If modifying existing schema, pull in existing tables
* Currently project is a full laravel install - would be better as a package that people pull in as a dev dependency
* Implement validation errors