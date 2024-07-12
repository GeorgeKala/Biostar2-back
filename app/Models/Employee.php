<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullname',
        'personal_id',
        'phone_number',
        'department_id',
        'start_datetime',
        'expiry_datetime',
        'position',
        'group_id',
        'schedule_id',
        'honorable_minutes_per_day',
        'device',
        'card_number',
        'checksum',
    ];


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

 
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

 
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
    

    public function holidays()
    {
        return $this->belongsToMany(Holiday::class);
    }
}
