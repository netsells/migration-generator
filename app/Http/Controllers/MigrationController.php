<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Netsells\MigrationGenerator\MigrationBuilder;

class MigrationController extends Controller
{
    public function generate(Request $request)
    {
        $this->validate($request, [
            'migration_name' => 'required',
            'migration_type' => ['required', Rule::in(['create', 'modify'])],
            'table_name' => 'required',
            'columns.*.name' => 'required',
            'columns.*.type' => 'required',
            'columns.*.nullable' => 'required|boolean',
        ]);

        $migrationBuilder = new MigrationBuilder(
            $request->input('columns'),
            $request->input('migration_name'),
            $request->input('table_name'),
            $request->input('migration_type') === 'create'
        );
        $code = $migrationBuilder->generate();

        return response()->json([
            'file_name' => $migrationBuilder->fileName(),
            'code' => $code
        ]);
    }
}