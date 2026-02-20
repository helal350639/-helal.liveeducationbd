<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['name'];
    
    public function mcqitems()
    {
        return $this->hasMany(McqItem::class, 'question_id');
    }

    public function filters()
    {
        return $this->belongsToMany(SubFilter::class);
    }

    public function getitems()
    {
        return $this->hasMany(McqItem::class, 'question_id');
    }

    public function getlabels()
    {
        return $this->hasMany(Label::class, 'question_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function semesters()
    {
        return $this->belongsToMany(Semester::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    public function chapters()
    {
        return $this->belongsToMany(Chapter::class);
    }

    public function answerfiles()
    {
        return $this->hasMany(AnswerFile::class, 'question_id');
    }

    public function papers()
    {
        return $this->belongsToMany(Paper::class);
    }

    public function choice()
    {
        return $this->hasOne(Choice::class, 'question_id');
    }

    public function reactions()
    {
      return $this->hasMany(QuestionReaction::class, 'question_id');
    }

    public function comments()
    {
      return $this->hasMany(QuestionComment::class, 'question_id');
    }

    public function ratings()
    {
      return $this->hasMany(QuestionRating::class, 'question_id');
    }

    public function readings()
    {
      return $this->hasMany(QuestionReading::class, 'question_id');
    }
}
