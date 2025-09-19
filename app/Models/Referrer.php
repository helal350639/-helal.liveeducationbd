<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referrer extends Model
{
  protected $fillable = ['batch_id', 'referrer_id', 'referrer_bonus', 'student_id'];

  public function batch()
  {
    return $this->belongsTo(Batch::class, 'batch_id');
  }

  public function student()
  {
    return $this->belongsTo(Student::class, 'student_id');
  }

  public function teacher()
  {
    return $this->belongsTo(Teacher::class, 'referrer_id');
  }

  public function referrer()
  {
    return $this->belongsTo(Student::class, 'referrer_id');
  }
}
