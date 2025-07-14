<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CircularType extends Model
{
  public function circulars()
  {
    return $this->belongsToMany(Circular::class);
  }
}
