<?php

namespace App\Http\Controllers;

use App\Models\ForgiveType;
use Illuminate\Http\Request;

class ForgiveTypesController extends Controller
{
    public function index()
    {
        return ForgiveType::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $forgiveType = ForgiveType::create($request->all());

        return response()->json($forgiveType, 201);
    }

    public function show(ForgiveType $forgiveType)
    {
        return $forgiveType;
    }

    public function update(Request $request, ForgiveType $forgiveType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $forgiveType->update($request->all());

        return response()->json($forgiveType, 200);
    }

    public function destroy(ForgiveType $forgiveType)
    {
        $forgiveType->delete();

        return response()->json(null, 204);
    }
}
