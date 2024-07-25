<?php

namespace App\Http\Controllers;

use App\Models\CommandType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommandTypeController extends Controller
{
    /**
     * Create a new command type.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $commandType = CommandType::create([
            'name' => $data['name'],
        ]);

        return response()->json(['message' => 'Command type created successfully', 'data' => $commandType], 201);
    }

    /**
     * Get all command types.
     *
     * @return Response
     */
    public function index()
    {
        $commandTypes = CommandType::all();

        return response()->json(['data' => $commandTypes], 200);
    }

    /**
     * Get a specific command type.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $commandType = CommandType::find($id);

        if (! $commandType) {
            return response()->json(['message' => 'Command type not found'], 404);
        }

        return response()->json(['data' => $commandType], 200);
    }

    /**
     * Update a specific command type.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'string|max:255',
        ]);

        $commandType = CommandType::find($id);

        if (! $commandType) {
            return response()->json(['message' => 'Command type not found'], 404);
        }

        $commandType->name = $data['name'];
        $commandType->save();

        return response()->json(['message' => 'Command type updated successfully', 'data' => $commandType], 200);
    }

    /**
     * Delete a specific command type.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $commandType = CommandType::find($id);

        if (! $commandType) {
            return response()->json(['message' => 'Command type not found'], 404);
        }

        $commandType->delete();

        return response()->json(['message' => 'Command type deleted successfully'], 200);
    }
}
