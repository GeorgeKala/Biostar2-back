<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'parent_id', 'access_group'];

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'building_department')
            ->withPivot('id');
    }

    public function parent()
    {
        return $this->belongsTo(Building::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Building::class, 'parent_id')->with('children');
    }
}
