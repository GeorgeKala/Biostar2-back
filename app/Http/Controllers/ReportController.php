<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function login(Request $request)
    {
        try {
            $sessionId = $request->header('bs-session-id');
            $biostarUrl = 'https://10.150.20.173:3002/tna/login/sso';
            $userData = [
                'user_id' =>(string) $request->user()->id,
                'biostar_session_id' => $sessionId,
            ];
    
            $response = Http::withOptions(['verify' => false])
                            ->post($biostarUrl, $userData);
    
            if ($response->successful()) {
                $responseData = $response->cookies()->toArray();
    
                return $responseData;
            } else {
                return response()->json(['error' => 'Failed to login'], $response->status());
            }
        } catch (\Exception $e) {
    
            return response()->json(['error' => 'Failed to login'], 500);
        }
    }



    // public function getMonthlyReports(Request $request)
    // {
    //     try {
    //         $sessionId = $request->header('Bs-Session-Id');

    //         $reportsUrl = 'https://10.150.20.173:3002/tna/report_filters/17';

    //         $response = Http::withOptions(['verify' => false])
    //                         ->withHeaders([
    //                             'biostar_session_id' => $sessionId,
    //                         ])
    //                         ->get($reportsUrl);

    //         if ($response->successful()) {
    //             $reports = $response->json();

    //             return response()->json($reports);
    //         } else {
    //             return response()->json(['error' => 'Failed to fetch monthly reports'], $response->status());
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to fetch monthly reports'], 500);
    //     }
    // }


    
    public function getMonthlyReports(Request $request)
    {
        try {
            $sessionId = $request->header('Bs-Session-Id');
            $baseUrl = 'https://10.150.20.173/api/events/search';

            $body = [
                "Query" => [
                    "limit" => 51,
                    "conditions" => [
                        [
                            "column" => "datetime",
                            "operator" => 3,
                            "values" => [
                                "2024-07-16T20:00:00.000Z",
                                "2024-07-17T19:59:59.000Z"
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::withOptions(['verify' => false])
                            ->withHeaders([
                                'bs-session-id' => $sessionId,
                            ])
                            ->post($baseUrl, $body);

            if ($response->successful()) {
                $reports = $response->json();
                $rows = $reports['EventCollection']['rows'];

                $data = [];

                foreach ($rows as $row) {
                    if (
                        isset($row['server_datetime']) &&
                        isset($row['user_id']) && isset($row['user_id']['user_id']) &&
                        isset($row['user_id_name']) &&
                        isset($row['device_id']) && isset($row['device_id']['id']) &&
                        isset($row['device_id']['name'])
                    ) {
                        $date = substr($row['server_datetime'], 0, 10); 
                        $userId = $row['user_id']['user_id'];
                        $userName = $row['user_id_name'];
                        $deviceId = $row['device_id']['id'];
                        $deviceName = $row['device_id']['name'];
                        $usageDatetime = $row['server_datetime'];

                        $employee = \App\Models\Employee::where('id', $userId)->with('schedule')->first();

                        if (!isset($data[$date])) {
                            $data[$date] = [
                                'date' => $date,
                                'users' => []
                            ];
                        }

                        $userExists = false;
                        foreach ($data[$date]['users'] as &$user) {
                            if ($user['user_id'] == $userId) {
                                $userExists = true;
                                $user['usage_count'] += 1;
                                $user['daily_usages'][] = $usageDatetime;
                                break;
                            }
                        }

                        if (!$userExists) {
                            $data[$date]['users'][] = [
                                'user_id' => $userId,
                                'user_name' => $userName,
                                'device_id' => $deviceId,
                                'device_name' => $deviceName,
                                'usage_count' => 1,
                                'daily_usages' => [$usageDatetime],
                                'employee' => $employee ? $employee->toArray() : null,
                                'schedule' => $employee && $employee->schedule ? $employee->schedule->toArray() : null
                            ];
                        }
                    }
                }

                foreach ($data as &$day) {
                    foreach ($day['users'] as &$user) {
                        sort($user['daily_usages']);
                        $user['come_time'] = substr($user['daily_usages'][0], 11, 8);
                        $user['leave_time'] = substr(end($user['daily_usages']), 11, 8);
    
                        if (isset($user['schedule'])) {
                            $scheduleStart = $user['schedule']['day_start'];
                            $scheduleEnd = $user['schedule']['day_end'];
    
                            $comeTime = new \DateTime($user['daily_usages'][0]);
                            $leaveTime = new \DateTime(end($user['daily_usages']));
                            $scheduleStartTime = new \DateTime($date . ' ' . $scheduleStart);
                            $scheduleEndTime = new \DateTime($date . ' ' . $scheduleEnd);
    
                            // Calculate minutes late/early
                            $user['come_late'] = $comeTime > $scheduleStartTime ? $scheduleStartTime->diff($comeTime)->i + ($scheduleStartTime->diff($comeTime)->h * 60) : 0;
                            $user['come_early'] = $comeTime < $scheduleStartTime ? $scheduleStartTime->diff($comeTime)->i + ($scheduleStartTime->diff($comeTime)->h * 60) : 0;
    
                            $user['leave_late'] = $leaveTime > $scheduleEndTime ? $scheduleEndTime->diff($leaveTime)->i + ($scheduleEndTime->diff($leaveTime)->h * 60) : 0;
                            $user['leave_early'] = $leaveTime < $scheduleEndTime ? $scheduleEndTime->diff($leaveTime)->i + ($scheduleEndTime->diff($leaveTime)->h * 60) : 0;
    
                            // Calculate worked hours
                            $interval = $comeTime->diff($leaveTime);
                            $user['worked_hours'] = $interval->h + ($interval->i / 60);
                            $user['penalized_time'] = $user['come_late'] + $user['leave_early'];
                        }
                    }
                }

                return response()->json(array_values($data));
            } else {
                return response()->json(['error' => 'Failed to fetch monthly reports'], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch monthly reports', 'message' => $e->getMessage()], 500);
        }
    }




    


    // private function formatReportsData($rows)
    // {

    //     $dateWiseUsage = [];

    //     foreach ($rows as $row) {
    //         $userId = $row['user_id']['user_id'];
    //         $userName = $row['user_id_name'];
    //         $date = substr($row['datetime'], 0, 10); // Extract date (YYYY-MM-DD)
    //         $deviceId = $row['device_id']['id'];
    //         $deviceName = $row['device_id']['name'];
    //         $datetime = $row['datetime'];

    //         if (!isset($dateWiseUsage[$date])) {
    //             $dateWiseUsage[$date] = [
    //                 'date' => $date,
    //                 'users' => []
    //             ];
    //         }

    //         if (!isset($dateWiseUsage[$date]['users'][$userId])) {
    //             $dateWiseUsage[$date]['users'][$userId] = [
    //                 'user_id' => $userId,
    //                 'user_name' => $userName,
    //                 'device_id' => $deviceId,
    //                 'device_name' => $deviceName,
    //                 'usage_count' => 0,
    //                 'daily_usages' => []
    //             ];
    //         }

    //         $dateWiseUsage[$date]['users'][$userId]['usage_count'] += 1;
    //         $dateWiseUsage[$date]['users'][$userId]['daily_usages'][] = $datetime;
    //     }

    //     // Format the output
    //     $formattedUsage = [];
    //     foreach ($dateWiseUsage as $date => $usage) {
    //         $usage['users'] = array_values($usage['users']);
    //         $formattedUsage[] = $usage;
    //     }

    //     return response()->json($formattedUsage);
    // }


}

