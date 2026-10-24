<?php
namespace App\Models\PremiumClass;
use Illuminate\Database\Eloquent\Model;
class LebdRecordedVideo extends Model {
 protected $table='lebd_recorded_videos';
 protected $fillable=['chapter_id','subject_id','title','provider','video_url','thumbnail_url','duration','sort_order','is_published'];
 protected $hidden=['video_url'];
 protected $casts=['is_published'=>'boolean'];
 public function subject(){return $this->belongsTo(LebdRecordedSubject::class,'subject_id');}
 public function chapter(){return $this->belongsTo(LebdRecordedChapter::class,'chapter_id');}
}