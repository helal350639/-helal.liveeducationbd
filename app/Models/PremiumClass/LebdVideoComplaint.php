<?php
namespace App\Models\PremiumClass;
use Illuminate\Database\Eloquent\Model;
class LebdVideoComplaint extends Model {
 protected $table='lebd_premium_video_complaints';
 protected $fillable=['video_id','student_id','premium_group','premium_topic','subject_title','chapter_title','video_title','reason','comment','status','admin_note','reviewed_by','reviewed_at'];
 protected $casts=['reviewed_at'=>'datetime'];
 public function video(){return $this->belongsTo(LebdRecordedVideo::class,'video_id');}
}
