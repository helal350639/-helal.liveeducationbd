<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['category_id', 'department_id', 'name', 'front_cover', 'back_cover', 'status', 'created_by', 'details'];

    public function category()
    {
      return $this->belongsTo(BookCategory::class);
    }

    public function department()
    {
      return $this->belongsTo(Department::class);
    }

    public function chapters()
    {
        return $this->belongsToMany(Chapter::class);
    }
    
    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }
}
