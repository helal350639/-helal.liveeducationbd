<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Circular extends Model
{
  protected $fillable = [
    'type', 
    'title', 
    'published_at',
    'start_at',
    'end_at',
    'admit_to',
    'position',
    'fee',
    'url',
    'details',
    'status',
    'image',
    'pdf'
  ];
}
