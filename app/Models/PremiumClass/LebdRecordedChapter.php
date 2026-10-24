<?php
namespace App\Models\PremiumClass;
use Illuminate\Database\Eloquent\Model;
class LebdRecordedChapter extends Model {
 protected $table='lebd_recorded_chapters';
 protected $fillable=['premium_group','premium_topic','subject_id','title','sort_order','is_active'];
 protected $casts=['is_active'=>'boolean'];
 public function subject(){return $this->belongsTo(LebdRecordedSubject::class,'subject_id');}
 public function videos(){return $this->hasMany(LebdRecordedVideo::class,'chapter_id')->orderBy('sort_order')->orderBy('id');}
}