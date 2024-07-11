<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::all();
        return response()->json($schedules);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:schedules',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'repetition_unit' => 'nullable|integer',
            'interval' => 'nullable|integer',
            'comment' => 'nullable|string',
        ]);

        $schedule = Schedule::create($data);

        return response()->json($schedule, 201);
    }

    public function show(Schedule $schedule)
    {
        return response()->json($schedule);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'name' => 'required|string|unique:schedules,name,' . $schedule->id,
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'repetition_unit' => 'nullable|integer',
            'interval' => 'nullable|integer',
            'comment' => 'nullable|string',
        ]);

        $schedule->update($request->all());
        return response()->json($schedule);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return response()->json(null, 204);
    }
}
