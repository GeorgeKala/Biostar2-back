<?php

namespace App\Http\Controllers;

use App\Models\ForgiveTypes;
use Illuminate\Http\Request;

class ForgiveTypesController extends Controller
{
    public function index()
    {
        return ForgiveTypes::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $forgiveType = ForgiveTypes::create($request->all());

        return response()->json($forgiveType, 201);
    }

    public function show(ForgiveTypes $forgiveType)
    {
        return $forgiveType;
    }

    public function update(Request $request, ForgiveTypes $forgiveType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $forgiveType->update($request->all());

        return response()->json($forgiveType, 200);
    }

    public function destroy(ForgiveTypes $forgiveType)
    {
        $forgiveType->delete();

        return response()->json(null, 204);
    }
}
