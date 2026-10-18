<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineItemRow extends Model
{
    protected $table = 'routine_item_rows';


    protected $fillable = [
    'routine_id',
    'paper_name',
    'exam_at',
    'exam_end_at'
];


    public function subjects()
    {
        return $this->hasMany(
            RoutineSubjectRow::class,
            'item_id',
            'id'
        );
    }


    public function departments()
    {
        return $this->hasMany(
            RoutineDepartmentRow::class,
            'item_id',
            'id'
        );
    }
}