<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    protected $guard = 'student';

    protected $fillable = [
        'name',
        'contact',
        'email',
        'password',
        'image',
        'status',
        'github_id',
        'google_id',
        'facebook_id',
        'referral_id',
        'referred_by',
        'referral_balance'
        
    ];
     protected static function boot()
{
    parent::boot();

    static::creating(function ($student) {

        $student->referral_id = 'LEBD' . str_pad(
        (self::max('id') + 1),
        6,
        '0',
        STR_PAD_LEFT
        );

    });
}

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function batches()
    {
        return $this->belongsToMany(Batch::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
    
    public function referrer()
    {
    return $this->belongsTo(Student::class, 'referred_by');
    }
    }