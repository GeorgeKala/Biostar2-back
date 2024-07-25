<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'access_groups'
    ];

    protected $casts = [
        'access_groups' => 'array',
    ];

    public function buildings()
    {
        return $this->belongsToMany(Building::class, 'building_department')
                    ->withPivot('id');
    }

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id')->with('children');
    }

}
