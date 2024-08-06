<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'week_day',
        'come_time',
        'leave_time',
        'come_late',
        'come_early',
        'leave_late',
        'leave_early',
        'worked_hours',
        'penalized_time',
        'final_penalized_time',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    
}
