# Laravel Migration Generator
This project is a web form for building Laravel migrations.

Set up your columns and types and it will generate the PHP code using nikic's php parser.

## To do:
* set up hosted version of project
* Currently project is a full laravel install - would be better as a package that people pull in as a dev dependency
* (FE) validation errors
    * show errors on front end
    * highlight relevant field
* If modifying existing schema, pull in existing tables
* (FE) add syntax highlighting package, to be re-applied whenever the code is updated from the server:
    * https://highlightjs.org/
    * When the highlighting is applied, it is currently breaking Vue's binding
* (FE) add multi-input option for enum columns, select2?
* (FE) add copy to clipboard for generated migration
* add support for float/decimal
* add support for default values
* add command to pipe into file `pbpaste >> migration_name.php`
* ~~Support creating and modifying tables - this has a knock-on effect for how the down side of the migration takes place~~
* ~~Support generating foreign keys~~
* ~~Generate the full migration file~~
* ~~create migration should add timestamps~~
* ~~passing table name to server~~
* ~~pass migration name to server~~
    * ~~use migration name for class name~~

### Generate down for migration
* drops foreign keys
* ~~drops columns~~
* ~~drops created tables~~ 
