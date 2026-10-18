<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RecruitmentLogo extends Model {
 protected $table='lebd_recruitment_logos';
 protected $fillable=['name','short_name','logo_path','website','status','sort_order'];
 public function courses(){return $this->hasMany(Course::class,'recruitment_logo_id');}
}
