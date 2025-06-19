<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Syllabus extends Model
{
    protected $fillable = ['name', 'course_id', 'batch_id', 'department_id', 'status'];
    
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
        return $this->belongsToMany(Subject::class, 'department_subject', 'department_id', 'subject_id');
    }
    
    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }
}