<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
  protected $fillable = ['title', 'details', 'image', 'start_at', 'end_at', 'status'];
}
