<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyExamQuestion extends Model
{


    protected $fillable = [

        'daily_exam_id',

        'paper_id',

        'question',

        'option_a',
        'option_b',
        'option_c',
        'option_d',

        'correct_answer',

        'mark',

        'explanation',

    ];



    public function dailyExam()
    {
        return $this->belongsTo(
            DailyExam::class,
            'daily_exam_id'
        );
    }

    public function paper()
{
    return $this->belongsTo(
        DailyExamPaper::class,
        'paper_id'
    );
}


}