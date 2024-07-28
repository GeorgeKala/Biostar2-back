<?php

namespace App\Http\Controllers;

use App\Models\DayType;
use Illuminate\Http\Request;

class DayTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dayTypes = DayType::all();

        return response()->json($dayTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $dayType = DayType::create($validatedData);

        return response()->json($dayType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $dayType = DayType::findOrFail($id);

        return response()->json($dayType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $dayType = DayType::findOrFail($id);
        $dayType->update($validatedData);

        return response()->json($dayType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $dayType = DayType::findOrFail($id);
        $dayType->delete();

        return response()->json(['message' => 'Day type deleted successfully']);
    }
}
