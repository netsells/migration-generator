<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class MigrationController extends Controller
{
    public function generate(Request $request)
    {
        $this->validate($request, [
            'columns.*.name' => 'required',
            'columns.*.type' => 'required',
            'columns.*.nullable' => 'required|boolean',
        ]);

        $columns = $request->input('columns');

        $columnStatements = [];
        $tableVariable = new Variable('table');
        foreach ($columns as $column) {
            $columnNameParameter = new String_($column['name']);
            $columnStatement = new MethodCall($tableVariable, $column['type'], [$columnNameParameter]);

            if ($column['nullable']) {
                // for chained method calls the expression is the input / variable to the next method call
                $columnStatement = new MethodCall($columnStatement, 'nullable');
            }

            if ($column['unsigned']) {
                $columnStatement = new MethodCall($columnStatement, 'unsigned');
            }

            if ($column['is_foreign_key']) {
                $columnStatements[] = $columnStatement;
                $columnStatements[] = $this->foreignKeyStatement($tableVariable, $column['name']);
                continue;
            }

            $columnStatements[] = $columnStatement;
        }
        $prettyPrinter = new Standard();
        return response()->json([
            'code' => $prettyPrinter->prettyPrint($columnStatements)
        ]);
    }

    public function parse()
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $migrationFile = file_get_contents(base_path('database/migrations/2014_10_12_000000_create_users_table.php'));
        $stmts = $parser->parse($migrationFile);
        dd($stmts);
    }

    private function foreignKeyStatement($tableVariable, $localColumnName, $foreignColumnName = 'id')
    {
        $foreign = new MethodCall($tableVariable, 'foreign', [new String_($localColumnName)]);
        $references = new MethodCall($foreign, 'references', [new String_($foreignColumnName)]);
        $foreignTable = new MethodCall($references, 'on', [new String_('foreign_table_name')]);
        return $foreignTable;
    }
}