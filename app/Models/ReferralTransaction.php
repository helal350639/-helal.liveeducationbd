<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralTransaction extends Model
{
    protected $fillable = [
    'student_id',
    'referred_student_id',
    'batch_id',
    'amount',
    'type',
    'status',
    'description'
    ];
}