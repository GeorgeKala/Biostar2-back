<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Building;
use Illuminate\Support\Facades\Validator;

class BuildingController extends Controller
{
    /**
     * Display a listing of the buildings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $buildings = Building::all();
        return response()->json(['data' => $buildings], 200);
    }

    /**
     * Store a newly created building in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $building = Building::create($request->all());

        return response()->json(['data' => $building], 201);
    }

    /**
     * Display the specified building.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    
    public function show($id)
    {
        $building = Building::find($id);

        if (!$building) {
            return response()->json(['error' => 'Building not found'], 404);
        }

        return response()->json(['data' => $building], 200);
    }

    /**
     * Update the specified building in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $building = Building::find($id);

        if (!$building) {
            return response()->json(['error' => 'Building not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'address' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $building->update($request->all());

        return response()->json(['data' => $building], 200);
    }

    /**
     * Remove the specified building from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $building = Building::find($id);

        if (!$building) {
            return response()->json(['error' => 'Building not found'], 404);
        }

        $building->delete();

        return response()->json(['message' => 'Building deleted successfully'], 200);
    }


    public function attachDepartments(Request $request, Building $building)
    {
        $departmentId = $request->department_id;

        $building->departments()->attach($departmentId);

        return response()->json(['message' => 'Department attached successfully']);
    }

    /**
     * Detach a department from a building.
     *
     * @param Request $request
     * @param Building $building
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachDepartments(Request $request, Building $building)
    {
        $departmentId = $request->department_id;

        // Check if the department is already attached
        $isAttached = $building->departments()->where('department_id', $departmentId)->exists();

        if ($isAttached) {
            // Delete existing pivot entry
            $building->departments()->detach($departmentId);
        }

        // Attach department (new pivot entry)
        $building->departments()->attach($departmentId);

        return response()->json(['message' => 'Department updated successfully']);
    }


    public function updateAttachedDepartments(Request $request, Building $building)
    {
        $departmentId = $request->department_id;

        $isAttached = $building->departments()->where('department_id', $departmentId)->exists();

        if ($isAttached) {
            // Update existing pivot entry
            $building->departments()->updateExistingPivot($departmentId, []);

            return response()->json(['message' => 'Department updated successfully']);
        } else {
            return response()->json(['error' => 'Department is not attached to this building.']);
        }
    }


    public function getBuildingsWithDepartments()
    {
        $buildings = Building::with('departments')->get();

        $formattedData = [];

        foreach ($buildings as $building) {
            foreach ($building->departments as $department) {
                $formattedData[] = [
                    'id' => $department->pivot->id,
                    'department_id' => $department->id,
                    'building_id' => $building->id,
                    'department_name' => $department->name,
                    'building_name' => $building->name,
                ];
            }
        }
        return response()->json($formattedData);
    }
}
