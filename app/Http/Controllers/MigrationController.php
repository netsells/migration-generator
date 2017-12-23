<?php

namespace App\Http\Controllers;

use App\Migration\MigrationBuilder;
use Illuminate\Http\Request;

class MigrationController extends Controller
{
    public function generate(Request $request, MigrationBuilder $migrationBuilder)
    {
        $this->validate($request, [
            'columns.*.name' => 'required',
            'columns.*.type' => 'required',
            'columns.*.nullable' => 'required|boolean',
        ]);

        $code = $migrationBuilder->generate($request->input('columns'));

        return response()->json([
            'code' => $code
        ]);
    }
}