<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionReaction extends Model
{
  protected $fillable = [
    'question_id',
    'student_id',
    'reaction'
  ];
}
