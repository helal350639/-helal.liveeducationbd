<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    protected $fillable = [
        'student_id',
        'amount',
        'method',
        'account_name',
        'account_number',
        'status'
    ];


    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}