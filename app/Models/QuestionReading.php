<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionReading extends Model
{
  protected $fillable = [
    'question_id',
    'student_id',
    'reading'
  ];
}
