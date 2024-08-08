<?php


namespace App\Jobs;

use App\Models\DailyReport;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalculateDailyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->date = $date ?: Carbon::yesterday()->format('Y-m-d');
//        $this->date = Carbon::yesterday()->format('Y-m-d');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Log::info('handle start');
        $employees = Employee::with('schedule', 'department', 'dayDetails.dayType', 'holidays')->get();

        foreach ($employees as $employee) {
            $dailyReportData = $this->calculateDailyReport($employee, $this->date);
            DailyReport::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $this->date],
                $dailyReportData
            );
        }
    }

    protected function calculateDailyReport($employee, $date)
    {

        Log::info('calculate daily info');
        $weekDayEnglish = date('l', strtotime($date));
        $englishToGeorgianWeekdays = [
            'Monday' => 'ორშაბათი',
            'Tuesday' => 'სამშაბათი',
            'Wednesday' => 'ოთხშაბათი',
            'Thursday' => 'ხუთშაბათი',
            'Friday' => 'პარასკევი',
            'Saturday' => 'შაბათი',
            'Sunday' => 'კვირა',
        ];
        $weekDayGeorgian = $englishToGeorgianWeekdays[$weekDayEnglish];

        $dailyReportData = [
            'employee_id' => $employee->id,
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
        ];

        $dayDetail = $employee->dayDetails->where('date', $date)->first();

        if ($dayDetail) {
            $dailyReportData['day_type'] = $dayDetail->dayType->name;
            $dailyReportData['comment'] = $dayDetail->comment;
            $dailyReportData['forgive_type'] = $dayDetail->forgiveType;
        } else {
            if ($employee->holidays->contains('name', $weekDayGeorgian)) {
                $dailyReportData['day_type'] = 'არა სამუშაო დღე';
            } else {
                $dailyReportData['day_type'] = 'სამუშაო დღე';
            }
        }

        $dailyUsages = $this->getEmployeeDailyUsages($employee->id, $date);

        if (!empty($dailyUsages)) {
            sort($dailyUsages);
            $dailyReportData['come_time'] = substr($dailyUsages[0], 11, 8);
            $dailyReportData['leave_time'] = substr(end($dailyUsages), 11, 8);

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

                $dailyReportData['come_late'] = $comeLateInterval ? $comeLateInterval->format('%H:%I:%S') : null;
                $dailyReportData['come_early'] = $comeEarlyInterval ? $comeEarlyInterval->format('%H:%I:%S') : null;
                $dailyReportData['leave_late'] = $leaveLateInterval ? $leaveLateInterval->format('%H:%I:%S') : null;
                $dailyReportData['leave_early'] = $leaveEarlyInterval ? $leaveEarlyInterval->format('%H:%I:%S') : null;

                $interval = $comeTime->diff($leaveTime);
                $workedHours = $interval->h + ($interval->i / 60) + ($interval->s / 3600);
                $dailyReportData['worked_hours'] = number_format($workedHours, 2);

                $dailyReportData['penalized_time'] = 0;

                if ($employee->holidays->contains('name', $weekDayGeorgian)) {
                    return $dailyReportData;
                }

                if ($comeLateInterval && $employee->group && $employee->group->control) {
                    $comeLateMinutes = $comeLateInterval->i + ($comeLateInterval->h * 60);
                    $dailyReportData['penalized_time'] += $comeLateMinutes;
                }

                if ($leaveEarlyInterval && $employee->group && $employee->group->leave_control) {
                    $leaveEarlyMinutes = $leaveEarlyInterval->i + ($leaveEarlyInterval->h * 60);
                    $dailyReportData['penalized_time'] += $leaveEarlyMinutes;
                }

                $dailyReportData['final_penalized_time'] = $dailyReportData['penalized_time'] - $employee->honorable_minutes_per_day;

                if ($dailyReportData['final_penalized_time'] < 0) {
                    $dailyReportData['final_penalized_time'] = 0;
                }
            }
        }

        return $dailyReportData;
    }

    public function getEmployeeDailyUsages($employeeId, $date)
    {

        $loginUrl = 'https://10.150.20.173/api/login';
        $baseUrl = 'https://10.150.20.173/api/events/search';
        $userData = [
            'User' => [
                'login_id' => env('BIOSTAR_ADMIN_USER'),
                'password' => env('BIOSTAR_ADMIN_PASSWORD'),
            ],
        ];

        $loginResponse = Http::withOptions(['verify' => false])
            ->post($loginUrl, $userData);
        if ($loginResponse->successful()) {
            $bsSessionId = $loginResponse->header('bs-session-id');

            $startDateTime = Carbon::parse($date)->startOfDay()->format('Y-m-d\TH:i:s.000\Z');
            $endDateTime = Carbon::parse($date)->endOfDay()->format('Y-m-d\TH:i:s.999\Z');


            $body = [
                'Query' => [
                    'limit' => 1000,
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
            $eventsResponse = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $bsSessionId,
                ])
                ->post($baseUrl, $body);
            if ($eventsResponse->successful()) {

                $reports = $eventsResponse->json();
                $rows = $reports['EventCollection']['rows'] ?? [];
                $dailyUsages = [];

                if(is_array($rows)){
                    foreach ($rows as $row) {
                        if (isset($row['server_datetime']) && isset($row['user_id']) && $row['user_id']['user_id'] == $employeeId) {
                            $dailyUsages[] = $row['server_datetime'];
                        }
                    }
                }
                return $dailyUsages;
            } else {
                return [
                    'error' => 'Event search request failed',
                    'status' => $eventsResponse->status(),
                    'response' => $eventsResponse->body(),
                ];
            }
        } else {
            return [
                'error' => 'Login request failed',
                'status' => $loginResponse->status(),
                'response' => $loginResponse->body(),
            ];
        }
    }
}
