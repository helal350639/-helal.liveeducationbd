<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Multiple Departments
    public function departments()
    {
        return $this->belongsToMany(
            Department::class,
            'paper_departments',
            'paper_id',
            'department_id'
        );
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'paper_id');
    }

    public function exam()
    {
        return $this->hasOne(Exam::class, 'paper_id');
    }
}