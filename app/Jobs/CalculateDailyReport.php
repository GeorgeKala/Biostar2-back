<?php

// app/Jobs/CalculateDailyReport.php

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

class CalculateDailyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;
    protected $token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date, $token)
    {
        $this->date = $date ?: Carbon::yesterday()->format('Y-m-d');
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $employees = Employee::with('schedule', 'department', 'dayDetails.dayType', 'holidays')->get();
        
        foreach ($employees as $employee) {
            // Logic to calculate daily report for each employee
            $dailyReportData = $this->calculateDailyReport($employee, $this->date);

            // Save daily report
            DailyReport::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $this->date],
                $dailyReportData
            );
        }
    }

    protected function calculateDailyReport($employee, $date)
    {
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

        $dailyUsages = $this->getEmployeeDailyUsages($employee->id, $date, $this->token);

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

    protected function getEmployeeDailyUsages($employeeId, $date, $token)
    {
        $sessionId = 'your-session-id'; 
        $baseUrl = 'https://your-api-endpoint/api/events/search';

        $startDateTime = (new \DateTime($date))->format('Y-m-d\T00:00:00.000\Z');
        $endDateTime = (new \DateTime($date))->format('Y-m-d\T23:59:59.999\Z');

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
            ->withHeaders([
                'bs-session-id' => $sessionId,
               
            ])
            ->post($baseUrl, $body);

        if ($response->successful()) {
            $reports = $response->json();
            $rows = $reports['EventCollection']['rows'] ?? [];

            $dailyUsages = [];
            foreach ($rows as $row) {
                if (isset($row['server_datetime']) && isset($row['user_id']) && $row['user_id']['user_id'] == $employeeId) {
                    $dailyUsages[] = $row['server_datetime'];
                }
            }

            return $dailyUsages;
        }

        return [];
    }
}
