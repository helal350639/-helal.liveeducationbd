<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function papers()
    {
        return $this->hasMany(Paper::class, 'batch_id');
    }

    public function paper()
    {
        return $this->hasOne(Paper::class, 'batch_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class);
    }

    public function syllabus()
    {
        return $this->hasOne(Syllabus::class, 'batch_id');
    }

    public function syllabuses()
    {
        return $this->hasMany(Syllabus::class, 'batch_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class);
    }

    public function complain()
    {
      return $this->hasOne(Complain::class, 'batch_id');
    }

    public function routines()
    {
        return $this->hasMany(Routine::class, 'batch_id');
    }
}