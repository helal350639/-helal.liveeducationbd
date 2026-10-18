<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineExam extends Model
{
    protected $table = 'routine_exams';

    protected $fillable = [
        'routine_id',
        'name',
        'sort_order',
    ];

    public function routine()
    {
        return $this->belongsTo(
            Routine::class,
            'routine_id'
        );
    }
}