<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineItem extends Model
{

    protected $guarded = [];


    public function subjects()
    {
        return $this->hasMany(RoutineItemSubject::class,'routine_item_id');
    }


    public function departments()
    {
        return $this->hasMany(RoutineItemDepartment::class,'routine_item_id');
    }

}