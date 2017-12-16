<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MigrationController extends Controller
{
    public function generate(Request $request)
    {
        $this->validate($request, [
            'columns.*.name' => 'required',
            'columns.*.type' => 'required',
            'columns.*.nullable' => 'required|boolean',
        ]);

        return response()->json($request->input('columns'));
    }
}