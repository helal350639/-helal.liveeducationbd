<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineDepartmentRow extends Model
{

    protected $table = 'routine_department_rows';


    public $timestamps = false;



    protected $fillable = [

        'item_id',

        'department_id',

        'details'

    ];



    public function department()
    {

        return $this->belongsTo(

            Department::class,

            'department_id',

            'id'

        );

    }

}