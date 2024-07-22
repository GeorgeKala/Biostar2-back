<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDayDetail;
use App\Models\ForgiveType;
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
    //         $baseUrl = 'https://10.150.20.173/api/events/search';

    //         $startDate = $request->input('start_date', '2024-07-16T20:00:00.000Z');
    //         $endDate = $request->input('end_date', '2024-07-17T19:59:59.000Z');

    //         $body = [
    //             "Query" => [
    //                 "limit" => 51,
    //                 "conditions" => [
    //                     [
    //                         "column" => "datetime",
    //                         "operator" => 3,
    //                         "values" => [
    //                             $startDate,
    //                             $endDate
    //                         ]
    //                     ],
    //                         [
    //                             "column" => "event_type_id",
    //                             "operator" => 0,
    //                             "values" => [
    //                                 "4102"
    //                             ]
    //                         ]
    //                 ]
    //             ]
    //         ];

    //         $response = Http::withOptions(['verify' => false])
    //                         ->withHeaders([
    //                             'bs-session-id' => $sessionId,
    //                         ])
    //                         ->post($baseUrl, $body);

    //         if ($response->successful()) {
    //             $reports = $response->json();
    //             $rows = $reports['EventCollection']['rows'];

    //             $departmentId = $request->input('department_id');
    //             $employeeId = $request->input('employee_id');

    //             $employeesQuery = \App\Models\Employee::with('schedule');

    //             if ($departmentId) {
    //                 $employeesQuery->where('department_id', $departmentId);
    //             }

    //             if ($employeeId) {
    //                 $employeesQuery->where('id', $employeeId);
    //             }

    //             $employees = $employeesQuery->get();
    //             $data = [];

    //             foreach ($employees as $employee) {
    //                 $userId = $employee->id;
    //                 $employeeData = [
    //                     'user_id' => $userId,
    //                     'user_name' => $employee->fullname,
    //                     'device_id' => null,
    //                     'device_name' => null,
    //                     'usage_count' => 0,
    //                     'daily_usages' => [],
    //                     'employee' => $employee->toArray(),
    //                     'schedule' => $employee->schedule ? $employee->schedule->toArray() : null,
    //                     'come_time' => null,
    //                     'leave_time' => null,
    //                     'come_late' => 0,
    //                     'come_early' => 0,
    //                     'leave_late' => 0,
    //                     'leave_early' => 0,
    //                     'worked_hours' => '0 hours 0 minutes',
    //                     'penalized_time' => 0
    //                 ];

    //                 foreach ($rows as $row) {
    //                     if (isset($row['server_datetime']) && isset($row['user_id']) && $row['user_id']['user_id'] == $userId) {
    //                         $usageDatetime = $row['server_datetime'];
    //                         $date = substr($usageDatetime, 0, 10);
    //                         $employeeData['device_id'] = $row['device_id']['id'];
    //                         $employeeData['device_name'] = $row['device_id']['name'];
    //                         $employeeData['usage_count'] += 1;
    //                         $employeeData['daily_usages'][] = $usageDatetime;
    //                     }
    //                 }

    //                 if (!empty($employeeData['daily_usages'])) {
    //                     sort($employeeData['daily_usages']);
    //                     $employeeData['come_time'] = substr($employeeData['daily_usages'][0], 11, 8);
    //                     $employeeData['leave_time'] = substr(end($employeeData['daily_usages']), 11, 8);

    //                     if ($employeeData['schedule']) {
    //                         $scheduleStart = $employeeData['schedule']['day_start'];
    //                         $scheduleEnd = $employeeData['schedule']['day_end'];

    //                         $comeTime = new \DateTime($employeeData['daily_usages'][0]);
    //                         $leaveTime = new \DateTime(end($employeeData['daily_usages']));
    //                         $scheduleStartTime = new \DateTime($date . ' ' . $scheduleStart);
    //                         $scheduleEndTime = new \DateTime($date . ' ' . $scheduleEnd);

    //                         $employeeData['come_late'] = $comeTime > $scheduleStartTime ? $scheduleStartTime->diff($comeTime)->i + ($scheduleStartTime->diff($comeTime)->h * 60) : 0;
    //                         $employeeData['come_early'] = $comeTime < $scheduleStartTime ? $scheduleStartTime->diff($comeTime)->i + ($scheduleStartTime->diff($comeTime)->h * 60) : 0;

    //                         $employeeData['leave_late'] = $leaveTime > $scheduleEndTime ? $scheduleEndTime->diff($leaveTime)->i + ($scheduleEndTime->diff($leaveTime)->h * 60) : 0;
    //                         $employeeData['leave_early'] = $leaveTime < $scheduleEndTime ? $scheduleEndTime->diff($leaveTime)->i + ($scheduleEndTime->diff($leaveTime)->h * 60) : 0;
    //                         $interval = $comeTime->diff($leaveTime);
    //                         $employeeData['worked_hours'] = $interval->h . ' hours ' . $interval->i . ' minutes';
    //                         $employeeData['penalized_time'] = $employeeData['come_late'] + $employeeData['leave_early'];
    //                     }
    //                 }

    //                 $data[] = $employeeData;
    //             }

    //             return response()->json($data);
    //         } else {
    //             return response()->json(['error' => 'Failed to fetch monthly reports'], $response->status());
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to fetch monthly reports', 'message' => $e->getMessage()], 500);
    //     }
    // }






    
    // public function getMonthlyReports(Request $request)
    // {
    //     try {
    //         $sessionId = $request->header('Bs-Session-Id');
    //         $baseUrl = 'https://10.150.20.173/api/events/search';

    //         $body = [
    //             "Query" => [
    //                 "limit" => 51,
    //                 "conditions" => [
    //                     [
    //                         "column" => "datetime",
    //                         "operator" => 3,
    //                         "values" => [
    //                             "2024-07-16T20:00:00.000Z",
    //                             "2024-07-17T19:59:59.000Z"
    //                         ]
    //                     ],
    //                     [
    //                         "column" => "event_type_id",
    //                         "operator" => 0,
    //                         "values" => [
    //                             "4102"
    //                         ]
    //                     ]
    //                 ]
    //             ]
    //         ];

    //         $response = Http::withOptions(['verify' => false])
    //                         ->withHeaders([
    //                             'bs-session-id' => $sessionId,
    //                         ])
    //                         ->post($baseUrl, $body);

    //         if ($response->successful()) {
    //             $reports = $response->json();
    //             $rows = $reports['EventCollection']['rows'];

    //             $data = [];

    //             foreach ($rows as $row) {
    //                 if (
    //                     isset($row['server_datetime']) &&
    //                     isset($row['user_id']) && isset($row['user_id']['user_id']) &&
    //                     isset($row['user_id_name']) &&
    //                     isset($row['device_id']) && isset($row['device_id']['id']) &&
    //                     isset($row['device_id']['name'])
    //                 ) {
    //                     $date = substr($row['server_datetime'], 0, 10); 
    //                     $userId = $row['user_id']['user_id'];
    //                     $userName = $row['user_id_name'];
    //                     $deviceId = $row['device_id']['id'];
    //                     $deviceName = $row['device_id']['name'];
    //                     $usageDatetime = $row['server_datetime'];

    //                     $employee = \App\Models\Employee::where('id', $userId)->with('schedule')->first();

    //                     if (!isset($data[$date])) {
    //                         $data[$date] = [
    //                             'date' => $date,
    //                             'users' => []
    //                         ];
    //                     }

    //                     $userExists = false;
    //                     foreach ($data[$date]['users'] as &$user) {
    //                         if ($user['user_id'] == $userId) {
    //                             $userExists = true;
    //                             $user['usage_count'] += 1;
    //                             $user['daily_usages'][] = $usageDatetime;
    //                             break;
    //                         }
    //                     }

    //                     if (!$userExists) {
    //                         $data[$date]['users'][] = [
    //                             'user_id' => $userId,
    //                             'user_name' => $userName,
    //                             'device_id' => $deviceId,
    //                             'device_name' => $deviceName,
    //                             'usage_count' => 1,
    //                             'daily_usages' => [$usageDatetime],
    //                             'employee' => $employee ? $employee->toArray() : null,
    //                             'schedule' => $employee && $employee->schedule ? $employee->schedule->toArray() : null
    //                         ];
    //                     }
    //                 }
    //             }

    //             foreach ($data as &$day) {
    //                 foreach ($day['users'] as &$user) {
    //                     sort($user['daily_usages']);
    //                     $user['come_time'] = substr($user['daily_usages'][0], 11, 8);
    //                     $user['leave_time'] = substr(end($user['daily_usages']), 11, 8);
    
    //                     if (isset($user['schedule'])) {
    //                         $scheduleStart = $user['schedule']['day_start'];
    //                         $scheduleEnd = $user['schedule']['day_end'];
    
    //                         $comeTime = new \DateTime($user['daily_usages'][0]);
    //                         $leaveTime = new \DateTime(end($user['daily_usages']));
    //                         $scheduleStartTime = new \DateTime($date . ' ' . $scheduleStart);
    //                         $scheduleEndTime = new \DateTime($date . ' ' . $scheduleEnd);
    
    //                         // Calculate minutes late/early
    //                         $user['come_late'] = $comeTime > $scheduleStartTime ? $scheduleStartTime->diff($comeTime)->i + ($scheduleStartTime->diff($comeTime)->h * 60) : 0;
    //                         $user['come_early'] = $comeTime < $scheduleStartTime ? $scheduleStartTime->diff($comeTime)->i + ($scheduleStartTime->diff($comeTime)->h * 60) : 0;
    
    //                         $user['leave_late'] = $leaveTime > $scheduleEndTime ? $scheduleEndTime->diff($leaveTime)->i + ($scheduleEndTime->diff($leaveTime)->h * 60) : 0;
    //                         $user['leave_early'] = $leaveTime < $scheduleEndTime ? $scheduleEndTime->diff($leaveTime)->i + ($scheduleEndTime->diff($leaveTime)->h * 60) : 0;
    
    //                         // Calculate worked hours
    //                         $interval = $comeTime->diff($leaveTime);
    //                         $user['worked_hours'] = $interval->h + ($interval->i / 60);
    //                         $user['penalized_time'] = $user['come_late'] + $user['leave_early'];
    //                     }
    //                 }
    //             }

    //             return response()->json(array_values($data));
    //         } else {
    //             return response()->json(['error' => 'Failed to fetch monthly reports'], $response->status());
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to fetch monthly reports', 'message' => $e->getMessage()], 500);
    //     }
    // }




    


    public function getMonthlyReports(Request $request)
    {
        try {
            $sessionId = $request->header('Bs-Session-Id');
            $baseUrl = 'https://10.150.20.173/api/events/search';

            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

            $startDateTime = (new \DateTime($startDate))->format('Y-m-d\T00:00:00.000\Z');
            $endDateTime = (new \DateTime($endDate))->format('Y-m-d\T23:59:59.999\Z');

            $departmentId = $request->input('department_id');
            $employeeId = $request->input('employee_id');

            $body = [
                "Query" => [
                    "limit" => 51,
                    "conditions" => [
                        [
                            "column" => "datetime",
                            "operator" => 3,
                            "values" => [
                                $startDateTime,
                                $endDateTime
                            ]
                        ],
                        [
                            "column" => "event_type_id",
                            "operator" => 0,
                            "values" => [
                                "4102"
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::withOptions(['verify' => false])
                            ->withHeaders(['bs-session-id' => $sessionId])
                            ->post($baseUrl, $body);

            if ($response->successful()) {
                $reports = $response->json();
                $rows = $reports['EventCollection']['rows'] ?? [];

                $employeesQuery = \App\Models\Employee::with('schedule', 'department', 'dayDetails.dayType', 'holidays');

                if ($departmentId) {
                    $employeesQuery->where('department_id', $departmentId);
                }

                if ($employeeId) {
                    $employeesQuery->where('id', $employeeId);
                }

                $employees = $employeesQuery->get();
                $data = [];

                $datesRange = $this->createDateRangeArray($startDate, $endDate);

                $englishToGeorgianWeekdays = [
                    'Monday' => 'ორშაბათი',
                    'Tuesday' => 'სამშაბათი',
                    'Wednesday' => 'ოთხშაბათი',
                    'Thursday' => 'ხუთშაბათი',
                    'Friday' => 'პარასკევი',
                    'Saturday' => 'შაბათი',
                    'Sunday' => 'კვირა',
                ];

                foreach ($employees as $employee) {
                    foreach ($datesRange as $date) {
                        $userId = $employee->id;
                        $weekDayEnglish = date('l', strtotime($date));
                        $weekDayGeorgian = $englishToGeorgianWeekdays[$weekDayEnglish];

                        $employeeData = [
                            'user_id' => $userId,
                            'fullname' => $employee->fullname,
                            'department' => $employee->department ? $employee->department->name : null,
                            'position' => $employee->position,
                            'schedule' => $employee->schedule ? $employee->schedule->name : null,
                            'homorable_minutes' => $employee->honorable_minutes_per_day,
                            'date' => $date,
                            'week_day' => $weekDayGeorgian,
                            'come_time' => null,
                            'leave_time' => null,
                            'come_late' => null,
                            'come_early' => null,
                            'leave_late' => null,
                            'leave_early' => null,
                            'worked_hours' => null,
                            'penalized_time' => null,
                            'final_penalized_time' => null,
                            'day_type' => '',
                            'comment' => '',
                            'forgive_type' => ''
                        ];

                        $dayDetail = $employee->dayDetails->where('date', $date)->first();
                        
                        if ($dayDetail) {
                            if ($dayDetail->dayType !== null) {
                                $employeeData['day_type'] = $dayDetail->dayType ? $dayDetail->dayType->name : '';
                            } elseif ($employee->holidays->contains('name', $weekDayGeorgian)) {
                                $employeeData['day_type'] = 'არა სამუშაო დღე';
                            } else {
                                $employeeData['day_type'] = 'სამუშაო დღე';
                            }
                            $employeeData['comment'] = $dayDetail->comment;
                            $employeeData['forgive_type'] = $dayDetail->forgiveType ? $dayDetail->forgiveType->name : '';
                        } else {
                            if ($employee->holidays->contains('name', $weekDayGeorgian)) {
                                $employeeData['day_type'] = 'არა სამუშაო დღე';
                            } else {
                                $employeeData['day_type'] = 'სამუშაო დღე';
                            }
                        }

                        $dailyUsages = [];
                        if (is_array($rows)) {
                            foreach ($rows as $row) {
                                if (isset($row['server_datetime']) && isset($row['user_id']) && $row['user_id']['user_id'] == $userId) {
                                    $usageDatetime = $row['server_datetime'];
                                    $eventDate = substr($usageDatetime, 0, 10);

                                    if ($eventDate == $date) {
                                        $employeeData['device_id'] = $row['device_id']['id'];
                                        $employeeData['device_name'] = $row['device_id']['name'];
                                        $dailyUsages[] = $usageDatetime;
                                    }
                                }
                            }
                        }

                        if (!empty($dailyUsages)) {
                            sort($dailyUsages);
                            $employeeData['come_time'] = substr($dailyUsages[0], 11, 8);
                            $employeeData['leave_time'] = substr(end($dailyUsages), 11, 8);

                            if ($employee->schedule) {
                                $scheduleStart = $employee->schedule->day_start;
                                $scheduleEnd = $employee->schedule->day_end;

                                $comeTime = new \DateTime($dailyUsages[0]);
                                $leaveTime = new \DateTime(end($dailyUsages));
                                $scheduleStartTime = new \DateTime($date . ' ' . $scheduleStart);
                                $scheduleEndTime = new \DateTime($date . ' ' . $scheduleEnd);

                                $comeLateInterval = $comeTime > $scheduleStartTime ? $scheduleStartTime->diff($comeTime) : null;
                                $comeEarlyInterval = $comeTime < $scheduleStartTime ? $scheduleStartTime->diff($comeTime) : null;
                                $leaveLateInterval = $leaveTime > $scheduleEndTime ? $scheduleEndTime->diff($leaveTime) : null;
                                $leaveEarlyInterval = $leaveTime < $scheduleEndTime ? $scheduleEndTime->diff($leaveTime) : null;

                                $employeeData['come_late'] = $comeLateInterval ? $comeLateInterval->format('%H:%I:%S') : null;
                                $employeeData['come_early'] = $comeEarlyInterval ? $comeEarlyInterval->format('%H:%I:%S') : null;
                                $employeeData['leave_late'] = $leaveLateInterval ? $leaveLateInterval->format('%H:%I:%S') : null;
                                $employeeData['leave_early'] = $leaveEarlyInterval ? $leaveEarlyInterval->format('%H:%I:%S') : null;

                                $interval = $comeTime->diff($leaveTime);
                                $employeeData['worked_hours'] = $interval->format('%H:%I:%S');

                                $employeeData['penalized_time'] = 0;

                                if ($comeLateInterval) {
                                    $comeLateMinutes = $comeLateInterval->i + ($comeLateInterval->h * 60);
                                    $employeeData['penalized_time'] += $comeLateMinutes;
                                }

                                if ($leaveEarlyInterval) {
                                    $leaveEarlyMinutes = $leaveEarlyInterval->i + ($leaveEarlyInterval->h * 60);
                                    $employeeData['penalized_time'] += $leaveEarlyMinutes;
                                }

                                $employeeData['final_penalized_time'] = $employeeData['penalized_time'] - $employee->honorable_minutes_per_day;

                                if ($employeeData['final_penalized_time'] < 0) {
                                    $employeeData['final_penalized_time'] = 0;
                                }
                            }
                        }

                        $data[] = $employeeData;
                    }
                }

                return response()->json($data);
            } else {
                return response()->json(['error' => 'Failed to retrieve monthly reports.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




/**
 * Create an array of dates between two dates
 */
    private function createDateRangeArray($start, $end)
    {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);
        $endDate = $endDate->modify('+1 day'); 

        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($startDate, $interval, $endDate);

        $dates = [];
        foreach ($dateRange as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }


    public function updateOrCreateDayDetail(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'day_type_id' => 'nullable|exists:day_types,id',
            'forgive_type_id' => 'nullable|exists:forgive_types,id', 
            'comment' => 'nullable|string'
        ]);

        try {
            $forgiveTypeName = null;
            if (isset($validatedData['forgive_type_id'])) {
                $forgiveType = ForgiveType::find($validatedData['forgive_type_id']);
                $forgiveTypeName = $forgiveType ? $forgiveType->name : null;
            }
            $comment = $validatedData['comment'] ?? '';
            if ($forgiveTypeName) {
                $comment = '(' . $forgiveTypeName . ') ' . $comment;
            }

            $dayDetail = EmployeeDayDetail::updateOrCreate(
                [
                    'employee_id' => $validatedData['employee_id'],
                    'date' => $validatedData['date'],
                ],
                [
                    'day_type_id' => $validatedData['day_type_id'] ?? null,
                    'forgive_type_id' => $validatedData['forgive_type_id'] ?? null, 
                    'comment' => $comment,
                ]
            );

            return response()->json($dayDetail);
                 
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update employee day detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updateDayTypeForDateRange(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'day_type_id' => 'required|exists:day_types,id'
        ]);

        try {
            $datesRange = $this->createDateRangeArray($validatedData['start_date'], $validatedData['end_date']);
            foreach ($datesRange as $date) {
                EmployeeDayDetail::updateOrCreate(
                    [
                        'employee_id' => $validatedData['employee_id'],
                        'date' => $date,
                    ],
                    [
                        'day_type_id' => $validatedData['day_type_id'],
                    ]
                );
            }

            return response()->json(['message' => 'Day type updated successfully for the specified date range.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update day type for the specified date range.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}

