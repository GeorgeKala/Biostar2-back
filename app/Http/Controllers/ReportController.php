<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDayDetail;
use App\Models\ForgiveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    public function login(Request $request)
    {
        try {
            $sessionId = $request->header('bs-session-id');
            $biostarUrl = 'https://10.150.20.173:3002/tna/login/sso';
            $userData = [
                'user_id' => (string) $request->user()->id,
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

    public function getMonthlyReports(Request $request)
    {
        try {
            $sessionId = $request->header('Bs-Session-Id');
            $baseUrl = 'https://10.150.20.173/api/events/search';

            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

            $today = now()->format('Y-m-d');
            if ($endDate > $today) {
                $endDate = $today;
            }

            $startDateTime = (new \DateTime($startDate))->format('Y-m-d\T00:00:00.000\Z');
            $endDateTime = (new \DateTime($endDate))->format('Y-m-d\T23:59:59.999\Z');

            $departmentId = $request->input('department_id');
            $employeeId = $request->input('employee_id');

            $body = [
                'Query' => [
                    'limit' => 51,
                    'conditions' => [
                        [
                            'column' => 'datetime',
                            'operator' => 3,
                            'values' => [
                                $startDateTime,
                                $endDateTime,
                            ],
                        ],
                        [
                            'column' => 'event_type_id',
                            'operator' => 0,
                            'values' => [
                                '4102',
                            ],
                        ],
                    ],
                ],
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
                            'forgive_type' => '',
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

                        if (! empty($dailyUsages)) {
                            sort($dailyUsages);
                            $employeeData['come_time'] = substr($dailyUsages[0], 11, 8);
                            $employeeData['leave_time'] = substr(end($dailyUsages), 11, 8);

                            if ($employee->schedule) {
                                $scheduleStart = $employee->schedule->day_start;
                                $scheduleEnd = $employee->schedule->day_end;

                                $comeTime = new \DateTime($dailyUsages[0]);
                                $leaveTime = new \DateTime(end($dailyUsages));
                                $scheduleStartTime = new \DateTime($date.' '.$scheduleStart);
                                $scheduleEndTime = new \DateTime($date.' '.$scheduleEnd);

                                $comeLateInterval = $comeTime > $scheduleStartTime ? $scheduleStartTime->diff($comeTime) : null;
                                $comeEarlyInterval = $comeTime < $scheduleStartTime ? $scheduleStartTime->diff($comeTime) : null;
                                $leaveLateInterval = $leaveTime > $scheduleEndTime ? $scheduleEndTime->diff($leaveTime) : null;
                                $leaveEarlyInterval = $leaveTime < $scheduleEndTime ? $scheduleEndTime->diff($leaveTime) : null;

                                $employeeData['come_late'] = $comeLateInterval ? $comeLateInterval->format('%H:%I:%S') : null;
                                $employeeData['come_early'] = $comeEarlyInterval ? $comeEarlyInterval->format('%H:%I:%S') : null;
                                $employeeData['leave_late'] = $leaveLateInterval ? $leaveLateInterval->format('%H:%I:%S') : null;
                                $employeeData['leave_early'] = $leaveEarlyInterval ? $leaveEarlyInterval->format('%H:%I:%S') : null;

                                $interval = $comeTime->diff($leaveTime);
                                $workedHours = $interval->h + ($interval->i / 60) + ($interval->s / 3600);
                                $employeeData['worked_hours'] += number_format($workedHours, 2);

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

    //***** Make Comment on day detail of user *****//
    public function updateOrCreateDayDetail(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'day_type_id' => 'nullable|exists:day_types,id',
            'forgive_type_id' => 'nullable|exists:forgive_types,id',
            'comment' => 'nullable|string',
            'final_penalized_time' => 'nullable|numeric',
            'comment_datetime' => 'nullable|date',
        ]);

        try {
            $forgiveTypeName = null;
            if (isset($validatedData['forgive_type_id'])) {
                $forgiveType = ForgiveType::find($validatedData['forgive_type_id']);
                $forgiveTypeName = $forgiveType ? $forgiveType->name : null;
            }
            $comment = $validatedData['comment'] ?? '';
            if ($forgiveTypeName) {
                $comment = '('.$forgiveTypeName.') '.$comment;
            }

            $userId = auth()->user()->id;

            $dayDetail = EmployeeDayDetail::updateOrCreate(
                [
                    'employee_id' => $validatedData['employee_id'],
                    'date' => $validatedData['date'],
                ],
                [
                    'day_type_id' => $validatedData['day_type_id'] ?? null,
                    'forgive_type_id' => $validatedData['forgive_type_id'] ?? null,
                    'comment' => $comment,
                    'user_id' => $userId,
                    'final_penalized_time' => $validatedData['final_penalized_time'] ?? null,
                    'comment_datetime' => $validatedData['comment_datetime'] ?? now(),
                ]
            );

            return response()->json($dayDetail);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update employee day detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //make order on day detail
    public function updateDayTypeForDateRange(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'day_type_id' => 'required|exists:day_types,id',
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
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteDayTypeForDateRange(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $datesRange = $this->createDateRangeArray($validatedData['start_date'], $validatedData['end_date']);
            foreach ($datesRange as $date) {
                EmployeeDayDetail::where([
                    'employee_id' => $validatedData['employee_id'],
                    'date' => $date,
                ])->delete();
            }

            return response()->json(['message' => 'Day details deleted successfully for the specified date range.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete day details for the specified date range.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteDayDetail($id)
    {
        try {
            $dayDetail = EmployeeDayDetail::find($id);

            if ($dayDetail) {
                $dayDetail->delete();

                return response()->json(['message' => 'Day detail deleted successfully.']);
            } else {
                return response()->json(['error' => 'Day detail not found.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete day detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchReport(Request $request)
    {
        try {
            $url = 'https://10.150.20.173:3002/tna/report.json';

            $sessionId = $request->header('Bs-Session-Id');
            $sessionId = "bs-ta-session-id={$sessionId}";

            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

            // $today = now()->format('Y-m-d');
            // if ($endDate > $today) {
            //     $endDate = $today;
            // }

            $departmentId = $request->input('department_id');
            $employeeId = $request->input('employee_id');

            $body = [
                'type' => 'CUSTOM',
                'report_type' => 'REPORT_DAILY',
                'limit' => 100,
                'offset' => 0,
                'rebuild_time_card' => true,
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
                'group_id_list' => ['1'],
                'columns' => [
                    ['field' => 'userName'],
                    ['field' => 'datetime'],
                ],
            ];

            $response = Http::withOptions(['verify' => false])
                ->withHeaders(['Cookie' => $sessionId])
                ->post($url, $body);

            if ($response->successful()) {
                $reportData = $response->json();

                $employeesQuery = \App\Models\Employee::with('schedule', 'department', 'dayDetails.dayType', 'holidays', 'group');

                if ($departmentId) {
                    $employeesQuery->where('department_id', $departmentId);
                }

                if ($employeeId) {
                    $employeesQuery->where('id', $employeeId);
                }

                $employees = $employeesQuery->get();
                $englishToGeorgianWeekdays = [
                    'Monday' => 'ორშაბათი',
                    'Tuesday' => 'სამშაბათი',
                    'Wednesday' => 'ოთხშაბათი',
                    'Thursday' => 'ხუთშაბათი',
                    'Friday' => 'პარასკევი',
                    'Saturday' => 'შაბათი',
                    'Sunday' => 'კვირა',
                ];

                $combinedData = [];

                foreach ($reportData['records'] as $report) {
                    $employee = $employees->firstWhere('id', $report['userId']);
                    if ($employee) {
                        $group = $employee->group;
                        $control = $group->control;
                        $breakControl = $group->break_control;
                        $leaveControl = $group->leave_control;
                        $weekDayEnglish = date('l', strtotime($report['datetime']));
                        $weekDayGeorgian = $englishToGeorgianWeekdays[$weekDayEnglish];
                        $shiftStart = null;
                        $shiftEnd = null;
                        $shiftTimes = explode('-', $report['shift']);
                        if (count($shiftTimes) === 2 && ($shiftTimes[0]) && ($shiftTimes[1])) {
                            $shiftStart = \Carbon\Carbon::createFromFormat('H:i', trim($shiftTimes[0].':00'));
                            $shiftEnd = \Carbon\Carbon::createFromFormat('H:i', trim($shiftTimes[1].':00'));
                        }

                        $comeTime = null;
                        $leaveTime = null;
                        try {
                            $comeTime = \Carbon\Carbon::createFromFormat('H:i:s', $report['inTime']);
                            $leaveTime = \Carbon\Carbon::createFromFormat('H:i:s', $report['outTime']);
                        } catch (\Exception $e) {

                        }

                        $comeLate = $comeTime && $shiftStart && $comeTime->greaterThan($shiftStart) ? $comeTime->diffInMinutes($shiftStart) : null;
                        $comeEarly = $comeTime && $shiftStart && $comeTime->lessThan($shiftStart) ? $shiftStart->diffInMinutes($comeTime) : null;
                        $leaveLate = $leaveTime && $shiftEnd && $leaveTime->greaterThan($shiftEnd) ? $leaveTime->diffInMinutes($shiftEnd) : null;
                        $leaveEarly = $leaveTime && $shiftEnd && $leaveTime->lessThan($shiftEnd) ? $shiftEnd->diffInMinutes($leaveTime) : null;

                        $workedHours = $comeTime && $leaveTime ? $leaveTime->diffInMinutes($comeTime) / 60 : null;
                        $workedHours = $workedHours !== null ? number_format($workedHours, 2) : null;

                        $penalizedTime = 0;
                        if ($control) {
                            $penalizedTime += ($comeLate ?? 0);
                            $penalizedTime += ($comeEarly ?? 0);
                        }

                        $employeeData = [
                            'user_id' => $employee->id,
                            'fullname' => $employee->fullname,
                            'department' => $employee->department ? $employee->department->name : null,
                            'position' => $employee->position,
                            'schedule' => $report['shift'],
                            'homorable_minutes' => $employee->honorable_minutes_per_day,
                            'date' => $report['datetime'],
                            'week_day' => $weekDayGeorgian,
                            'come_time' => $report['inTime'],
                            'leave_time' => $report['outTime'],
                            'come_late' => $comeLate,
                            'come_early' => $comeEarly,
                            'leave_late' => $leaveLate,
                            'leave_early' => $leaveEarly,
                            'worked_hours' => $workedHours,
                            'penalized_time' => $penalizedTime,
                            'final_penalized_time' => max(0, $penalizedTime - $employee->honorable_minutes_per_day),
                            'day_type' => '',
                            'comment' => '',
                            'forgive_type' => '',
                        ];

                        $dayDetail = $employee->dayDetails->where('date', $report['datetime'])->first();

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

                        $combinedData[] = $employeeData;
                    }
                }

                usort($combinedData, function ($a, $b) {
                    if ($a['fullname'] === $b['fullname']) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    }

                    return strcmp($a['fullname'], $b['fullname']);
                });

                return response()->json([
                    'message' => 'Processed Successfully',
                    'message_key' => 'SUCCESSFUL',
                    'language' => 'en',
                    'status_code' => 'SUCCESSFUL',
                    'records' => $combinedData,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch report',
                    'error' => $response->body(),
                ], $response->status());
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
