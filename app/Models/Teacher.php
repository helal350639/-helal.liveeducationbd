<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Teacher extends Authenticatable
{
    protected $guard = 'teacher';

    // public function referrer()
    // {
    //   return $this->hasOne(Referrer::class, 'referrer_id');
    // }
}