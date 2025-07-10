<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
  protected $fillable = ['student_id'];

  public function courses()
  {
      return $this->belongsToMany(Course::class);
  }
  public function course()
  {
      return $this->belongsTo(Course::class);
  }

  public function departments()
  {
      return $this->belongsToMany(Department::class);
  }

  public function papers()
  {
      return $this->belongsToMany(Paper::class);
  }

  public function paper()
  {
      return $this->belongsTo(Paper::class);
  }

  public function students()
  {
      return $this->belongsToMany(Student::class);
  }

  public function student()
  {
      return $this->belongsTo(Student::class, 'student_id');
  }

  public function choices()
  {
      return $this->hasMany(Choice::class, 'exam_id');
  }
}