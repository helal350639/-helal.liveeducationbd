<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineSubjectRow extends Model
{

    protected $table = 'routine_subject_rows';


    public $timestamps = false;



    protected $fillable = [
    'item_id',
    'subject_id',
    'chapter'
];



    public function subject()
    {

        return $this->belongsTo(

            Subject::class,

            'subject_id',

            'id'

        );

    }

}