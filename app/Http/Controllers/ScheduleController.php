<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
            'day_start' => 'nullable|date_format:H:i',
            'day_end' => 'nullable|date_format:H:i',
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
        $data = $request->validate([
            'name' => 'required|string|unique:schedules,name,'.$schedule->id,
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'repetition_unit' => 'nullable|integer',
            'interval' => 'nullable|integer',
            'comment' => 'nullable|string',
            'day_start' => 'nullable|date_format:H:i',
            'day_end' => 'nullable|date_format:H:i',
        ]);

        $schedule->update($data);

        return response()->json($schedule);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json(null, 204);
    }

    public function getSchedules(Request $request)
    {
        try {
            $url = 'https://10.150.20.173:3002/tna/schedules';
            $sessionId = $request->header('Bs-Session-Id');

            $sessionId = "bs-ta-session-id={$sessionId}";

            $response = Http::withOptions(['verify' => false])
                ->withHeaders(['Cookie' => $sessionId])
                ->get($url);

            if ($response->successful()) {
                $schedules = $response->json();

                return response()->json($schedules);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch schedules',
                    'error' => $response->body(),
                ], $response->status());
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching schedules',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
