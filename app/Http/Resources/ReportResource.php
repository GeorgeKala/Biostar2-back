<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dayDetail = $this->employee->dayDetails ? $this->employee->dayDetails->where('date', $this->date)->first() : null;
//        return [
//            'user_id' => $this->employee_id,
//            'fullname' => $this->employee->fullname,
//            'department' => $this->employee->department ? $this->employee->department->name : null,
//            'position' => $this->employee->position,
//            'schedule' => $this->employee->schedule ? $this->employee->schedule->name : null,
//            'honorable_minutes' => $this->employee->honorable_minutes_per_day,
//            'date' => $this->date,
//            'week_day' => $this->week_day,
//            'come_time' => $this->come_time,
//            'leave_time' => $this->leave_time,
//            'come_late' => $this->come_late,
//            'come_early' => $this->come_early,
//            'leave_late' => $this->leave_late,
//            'leave_early' => $this->leave_early,
//            'worked_hours' => $this->worked_hours,
//            'penalized_time' => $this->penalized_time,
//            'final_penalized_time' => $this->final_penalized_time,
//            'day_type' => $dayDetail ? $dayDetail->day_type : null,
//            'comment' => $dayDetail ? $dayDetail->comment : null,
//            'forgive_type' => $dayDetail ? $dayDetail->forgiveType : null,
//            'day_type_id' => $dayDetail ? $dayDetail->day_type_id : null,
//        ];

        return [
            'user_id' => $this->employee_id,
            'fullname' => $this->employee->fullname,
            'department' => $this->employee->department ? $this->employee->department->name : null,
            'position' => $this->employee->position,
            'schedule' => $this->employee->schedule ? $this->employee->schedule->name : null,
            'honorable_minutes' => $this->employee->honorable_minutes_per_day,
            'date' => $this->date,
            'week_day' => $this->week_day,
            'come_time' => $this->come_time,
            'leave_time' => $this->leave_time,
            'come_late' => $this->come_late,
            'come_early' => $this->come_early,
            'leave_late' => $this->leave_late,
            'leave_early' => $this->leave_early,
            'worked_hours' => $this->worked_hours,
            'penalized_time' => $this->penalized_time,
            'final_penalized_time' => $this->final_penalized_time,
            'day_type' => $this->day_type,
            'comment' => $this->comment,
            'forgive_type' => $this->forgive_type,
            'day_type_id' => $this->day_type_id,
        ];
    }
}
