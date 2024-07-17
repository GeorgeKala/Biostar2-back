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
        'day_type',
        'comment'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
