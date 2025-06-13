<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineItemRow extends Model
{
  protected $fillable = ['routine_id', 'paper_id', 'exam_at'];

  public function subjects()
  {
    return $this->hasMany(RoutineSubjectRow::class, 'item_id');
  }

  public function departments()
  {
    return $this->hasMany(RoutineDepartmentRow::class, 'item_id');
  }
}
