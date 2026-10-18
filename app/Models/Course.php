<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Course extends Model {
 protected $fillable = ['name'];
 public function recruitmentLogo(){return $this->belongsTo(RecruitmentLogo::class,'recruitment_logo_id');}
 public function filters(){return $this->belongsToMany(Filter::class);}
 public function departments(){return $this->belongsToMany(Department::class,'course_department','course_id','department_id');}
 public function semesters(){return $this->belongsToMany(Semester::class);}
 public function subjects(){return $this->belongsToMany(Subject::class);}
 public function chapters(){return $this->belongsToMany(Chapter::class);}
 public function questions(){return $this->belongsToMany(Question::class);}
 public function students(){return $this->belongsToMany(Student::class);}
 public function papers(){return $this->hasMany(Paper::class,'course_id');}
 public function paper(){return $this->hasOne(Paper::class,'course_id');}
 public function syllabus(){return $this->hasOne(Syllabus::class,'course_id');}
 public function syllabuses(){return $this->hasMany(Syllabus::class,'course_id');}
 public function batch(){return $this->hasOne(Batch::class,'course_id');}
 public function batches(){return $this->hasMany(Batch::class,'course_id');}
 public function routines(){return $this->hasMany(Batch::class,'course_id');}
}
