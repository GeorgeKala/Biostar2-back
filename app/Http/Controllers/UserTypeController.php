<?php

namespace App\Http\Controllers;

use App\Models\UserTypes;
use Illuminate\Http\Request;

class UserTypeController extends Controller
{
    public function index()
    {
        $userTypes = UserTypes::all();

        return response()->json([
            'data' => $userTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:user_types',
        ]);

        $userType = UserTypes::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'data' => $userType,
            'message' => 'User type created successfully.',
        ], 201);
    }

    public function show(UserTypes $userType)
    {
        return response()->json([
            'data' => $userType,
        ]);
    }

    public function update(Request $request, UserTypes $userType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:user_types,name,'.$userType->id,
        ]);

        $userType->name = $request->name;
        $userType->save();

        return response()->json([
            'data' => $userType,
            'message' => 'User type updated successfully.',
        ]);
    }

    public function destroy(UserTypes $userType)
    {
        $userType->delete();

        return response()->json([
            'message' => 'User type deleted successfully.',
        ]);
    }
}
