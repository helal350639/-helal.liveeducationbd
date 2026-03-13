<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    //
    public function subfilter()
    {
        return $this->hasMany(SubFilter::class, 'filter_id');
    }

    public function parents()
    {
        return $this->hasMany(SubFilter::class, 'filter_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }
}
