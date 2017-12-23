<?php

namespace App\Migration;

use PhpParser\Builder\Class_;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\PrettyPrinter\Standard;

/**
 * Takes an array of columns with their name, type and options
 * and generates PHP code for a Laravel migration
 *
 * Class MigrationBuilder
 * @package App\Migration
 */
class MigrationBuilder
{
    /**
     * This represents the $table variable in the AST for the migration
     */
    protected $tableVariable;

    public function generate(array $columns)
    {
        $this->tableVariable = new Variable('table');

        $upStatements = $this->upStatements($columns);
        $downStatements = $this->downStatements($columns);

        return (new Standard())->prettyPrintFile(
            $this->migrationStatements($upStatements, $downStatements)
        );
    }

    private function foreignKeyStatement(array $column)
    {
        $foreignKey = $column['foreign_key'];
        $foreign = new MethodCall($this->tableVariable, 'foreign', [new String_($column['name'])]);
        $references = new MethodCall($foreign, 'references', [new String_($foreignKey['column_name'] ?? 'id')]);
        $foreignTable = new MethodCall($references, 'on', [new String_($foreignKey['references'])]);
        $onUpdate = new MethodCall($foreignTable, 'onUpdate', [new String_($foreignKey['on_update'])]);
        $onDelete = new MethodCall($onUpdate, 'onDelete', [new String_($foreignKey['on_delete'])]);
        return $onDelete;
    }

    private function migrationStatements(array $upStatements, array $downStatements)
    {
        $tableName = 'users';
        $className = 'CreateUsersTable';

        $factory = new BuilderFactory();

        $class = $factory->class($className)->extend('Migration')
            ->addStmt($factory->method('up')->makePublic()
                ->addStmt($this->schemaStatement($upStatements, $tableName)))
            ->addStmt($factory->method('down')->makePublic()
                ->addStmt($this->schemaStatement($downStatements, $tableName)));

        $builders = [
            $factory->use('Illuminate\Support\Facades\Schema'),
            $factory->use('Illuminate\Database\Schema\Blueprint'),
            $factory->use('Illuminate\Database\Migrations\Migration'),
            $class
        ];

        return collect($builders)->map(function ($builder) {
            return $builder->getNode();
        })->all();
    }

    private function schemaStatement(array $statements, $tableName)
    {
        $closure = new Closure([
            'params' => [new Param('table', null, new Name('Blueprint'))],
            'stmts' => $statements
        ]);
        return new StaticCall(new Name('Schema'), 'table', [new String_($tableName), $closure]);
    }

    private function upStatements($columns)
    {
        foreach ($columns as $column) {
            $columnNameParameter = new String_($column['name']);
            $columnStatement = new MethodCall($this->tableVariable, $column['type'], [$columnNameParameter]);

            if ($column['nullable']) {
                // for chained method calls the expression is the input / variable to the next method call
                $columnStatement = new MethodCall($columnStatement, 'nullable');
            }

            if ($column['unsigned']) {
                $columnStatement = new MethodCall($columnStatement, 'unsigned');
            }

            if ($column['is_foreign_key']) {
                $columnStatements[] = $columnStatement;
                $columnStatements[] = $this->foreignKeyStatement($column);
                continue;
            }

            $columnStatements[] = $columnStatement;
        }

        return $columnStatements;
    }

    private function downStatements($columns)
    {
        $tableNames = collect($columns)->map(function ($column) {
            return new String_($column['name']);
        })->all();
        $dropColumnStmt = new MethodCall($this->tableVariable, 'dropColumn', [new Array_($tableNames)]);
        return [$dropColumnStmt];
    }
}