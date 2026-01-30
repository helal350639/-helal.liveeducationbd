<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionRating extends Model
{
  protected $fillable = [
    'question_id',
    'student_id',
    'rating'
  ];
}
