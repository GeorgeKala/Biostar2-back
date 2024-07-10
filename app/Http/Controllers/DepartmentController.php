<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        return response()->json(['departments' => $departments], 200);
    }

    public function nestedDepartments()
    {
        $departments = Department::with('children.children')->whereNull('parent_id')->get();
        return response()->json(['departments' => $departments], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'parent_id' => 'nullable|exists:departments,id'
        ]);

        $department = Department::create($request->all());

        return response()->json(['department' => $department], 201);
    }

    public function show(Department $department)
    {
        return response()->json(['department' => $department], 200);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string',
            'parent_id' => 'nullable|exists:departments,id'
        ]);

        $department->update($request->all());

        return response()->json(['department' => $department], 200);
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return response()->json(null, 204);
    }

    
}
