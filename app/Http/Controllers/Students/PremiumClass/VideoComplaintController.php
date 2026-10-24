<?php
namespace App\Http\Controllers\Students\PremiumClass;
use App\Http\Controllers\Controller;
use App\Models\PremiumClass\LebdRecordedVideo;
use App\Models\PremiumClass\LebdVideoComplaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class VideoComplaintController extends Controller {
 public function store(Request $request){
  abort_unless(Schema::hasTable('lebd_premium_video_complaints'),503,'Video complaint table is not installed.');
  $data=$request->validate([
   'video_id'=>['required','integer','min:1','exists:lebd_recorded_videos,id'],
   'reason'=>['required','in:unclear_video,poor_explanation,other'],
   'comment'=>['nullable','string','max:2000'],
  ]);
  if($data['reason']==='other' && blank($data['comment']??null)) return back()->withErrors(['comment'=>'Others হলে অভিযোগের বিস্তারিত লিখুন।'])->withInput();
  $video=LebdRecordedVideo::query()->whereKey($data['video_id'])->where('is_published',1)->firstOrFail();
  $chapter=$video->chapter;
  $subject=$video->subject ?: ($chapter ? $chapter->subject : null);
  $group=$subject?->premium_group ?: $chapter?->premium_group ?: '';
  $topic=$subject?->premium_topic ?: $chapter?->premium_topic ?: '';
  $studentId=Auth::guard('student')->id();abort_unless($studentId,401);
  DB::transaction(function()use($video,$studentId,$group,$topic,$subject,$chapter,$data){
   LebdVideoComplaint::create([
    'video_id'=>$video->id,'student_id'=>$studentId,'premium_group'=>$group,'premium_topic'=>$topic,
    'subject_title'=>$subject?->title,'chapter_title'=>$chapter?->title,
    'video_title'=>$video->title,'reason'=>$data['reason'],
    'comment'=>$data['comment']??null,'status'=>'pending',
   ]);
  });
  return back()->with('lebd_complaint_success','আপনার ভিডিও অভিযোগ জমা হয়েছে। ধন্যবাদ।');
 }
}
