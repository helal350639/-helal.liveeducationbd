<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DailyExam extends Model
{


    protected $fillable = [

        'course_id',
        'batch_id',
        'department_id',

        'header',
        'details',
        'banner',

        'exam_title',
        'question_limit',

        'start_time',
        'finish_time',
        'duration',

        'correct_mark',
        'negative_mark',

        'attempt',
        'display_question',
        'random_question',

        'permission',
        'paid_exam',
        'email_result',

        'max_candidate',

        'status',

    ];




    public function questions()
    {
        return $this->hasMany(
            DailyExamQuestion::class,
            'daily_exam_id'
        );
    }





    public function papers()
    {
        return $this->hasMany(
            DailyExamPaper::class,
            'daily_exam_id'
        );
    }



}
