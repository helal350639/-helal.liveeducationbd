<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RoutineExam;

class Routine extends Model
{
    protected $fillable = [
        'name',
        'course_id',
        'department_id'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Routine Exam Names
    |--------------------------------------------------------------------------
    */

    public function exams()
    {
        return $this->hasMany(
            RoutineExam::class,
            'routine_id'
        )->orderBy('sort_order');
    }
}