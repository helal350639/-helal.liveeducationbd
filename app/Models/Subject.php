<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name'];

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }
    
    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function semesters()
    {
        return $this->belongsToMany(Semester::class);
    }

    public function chapters()
    {
        return $this->belongsToMany(Chapter::class, 'chapter_subject', 'subject_id', 'chapter_id');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_subject', 'subject_id', 'question_id');
    }

    public function routine()
    {
        return $this->belongsToMany(Routine::class);
    }
}
