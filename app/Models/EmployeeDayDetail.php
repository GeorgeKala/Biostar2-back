<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDayDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'day_type_id',
        'comment',
        'forgive_type_id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function dayType()
    {
        return $this->belongsTo(DayType::class);
    }

    public function forgiveType()
    {
        return $this->belongsTo(ForgiveType::class);
    }
}
