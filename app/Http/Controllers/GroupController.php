<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::all();

        return response()->json($groups);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:groups',
            'control' => 'nullable|boolean',
            'break_control' => 'nullable|boolean',
            'leave_control' => 'nullable|boolean',
        ]);

        $group = Group::create($request->all());

        return response()->json($group, 201);
    }

    public function show(Group $group)
    {
        return response()->json($group);
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string|unique:groups,name,'.$group->id,
            'control' => 'nullable|boolean',
            'break_control' => 'nullable|boolean',
            'leave_control' => 'nullable|boolean',
        ]);

        $group->update($request->all());

        return response()->json($group);
    }

    public function destroy(Group $group)
    {
        $group->delete();

        return response()->json(null, 204);
    }
}
