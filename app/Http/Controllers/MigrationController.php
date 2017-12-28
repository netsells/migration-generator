<?php

namespace App\Http\Controllers;

use App\Migration\MigrationBuilder;
use Illuminate\Http\Request;

class MigrationController extends Controller
{
    public function generate(Request $request)
    {
        $this->validate($request, [
            'migration_name' => 'required',
            'table_name' => 'required',
            'columns.*.name' => 'required',
            'columns.*.type' => 'required',
            'columns.*.nullable' => 'required|boolean',
        ]);

        $migrationBuilder = new MigrationBuilder(
            $request->input('columns'),
            $request->input('migration_name'),
            $request->input('table_name')
        );
        $code = $migrationBuilder->generate();

        return response()->json([
            'file_name' => $migrationBuilder->fileName(),
            'code' => $code
        ]);
    }
}