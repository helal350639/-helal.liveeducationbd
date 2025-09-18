<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referrer extends Model
{
  protected $fillable = ['batch_id', 'referrer_id', 'referrer_bonus', 'student_id'];

  public function batch()
  {
    return $this->belongsTo(Batch::class);
  }

  public function student()
  {
    return $this->belongsTo(Student::class);
  }

  // public function teacher()
  // {
  //   return $this->belongsTo(Teacher::class);
  // }
}
