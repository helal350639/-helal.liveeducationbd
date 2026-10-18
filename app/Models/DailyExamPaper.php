<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyExamPaper extends Model
{

    protected $fillable = [

        'daily_exam_id',

        'paper_name',

        'description',

        'status',

    ];



    public function dailyExam()
    {
        return $this->belongsTo(
            DailyExam::class,
            'daily_exam_id'
        );
    }



    public function questions()
    {
        return $this->hasMany(
            DailyExamQuestion::class,
            'paper_id'
        );
    }


}