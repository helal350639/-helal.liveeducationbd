<?php
namespace App\Models\PremiumClass;
use Illuminate\Database\Eloquent\Model;
class LebdRecordedSubject extends Model {
 protected $table='lebd_recorded_subjects';
 protected $fillable=['premium_group','premium_topic','title','sort_order','is_active'];
 protected $casts=['is_active'=>'boolean'];
 public function chapters(){return $this->hasMany(LebdRecordedChapter::class,'subject_id');}
 public function videos(){return $this->hasMany(LebdRecordedVideo::class,'subject_id');}
}
