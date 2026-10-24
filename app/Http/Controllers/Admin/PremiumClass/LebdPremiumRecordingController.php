<?php
namespace App\Http\Controllers\Admin\PremiumClass;
use App\Http\Controllers\Controller;
use App\Models\PremiumClass\LebdRecordedChapter;
use App\Models\PremiumClass\LebdRecordedSubject;
use App\Models\PremiumClass\LebdRecordedVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LebdPremiumRecordingController extends Controller {
 private const TOPICS=[
  'department'=>['civil','electrical','mechanical','computer','electronic','power','rac','architecture','textile','agriculture'],
  'non-department'=>['bangla-grammar','bangla-literature','bangladesh','international','english','math','science','others']
 ];
 // Fix105: the Admin selections must match the public Student Homepage EXACTLY.
 // Never append unrelated rows from departments or from legacy premium data.
 // Existing subject/chapter/video rows remain in database and are not deleted.
 private function topicsFor(string $group):array{
  abort_unless(isset(self::TOPICS[$group]),404);
  return self::TOPICS[$group];
 }
 private function labelsFor(string $group,array $topics):array{
  $labels=[
   'civil'=>'Civil','electrical'=>'Electrical','mechanical'=>'Mechanical',
   'computer'=>'Computer','electronic'=>'Electronic','power'=>'Power',
   'rac'=>'RAC','architecture'=>'Architecture','textile'=>'Textile',
   'agriculture'=>'Agriculture',
   'bangla-grammar'=>'বাংলা ব্যাকরণ',
   'bangla-literature'=>'বাংলা সাহিত্য ও সংস্কৃতি',
   'bangladesh'=>'বাংলাদেশ বিষয়াবলি',
   'international'=>'আন্তর্জাতিক বিষয়াবলি',
   'english'=>'ইংরেজি','math'=>'গণিত',
   'science'=>'বিজ্ঞান ও প্রযুক্তি','others'=>'অন্যান্য'
  ];
  return array_intersect_key($labels,array_flip($topics));
 }
 private function target(Request $r):array{
  $group=(string)$r->query('premium_group','department');$topic=(string)$r->query('premium_topic','civil');
  if(!isset(self::TOPICS[$group])||!in_array($topic,$this->topicsFor($group),true)){abort(404);}
  return [$group,$topic];
 }
 public function index(Request $r){
  // First screen: explicit Department/Non-Department list. No topic selected.
  $group=(string)$r->query('premium_group','department');
  abort_unless(isset(self::TOPICS[$group]),404);
  if(!$r->filled('premium_topic')){
   $topics=$this->topicsFor($group);
   $labels=$this->labelsFor($group,$topics);
   $counts=LebdRecordedSubject::query()->where('premium_group',$group)
    ->select('premium_topic',DB::raw('COUNT(*) as total'))
    ->groupBy('premium_topic')->pluck('total','premium_topic');
   return view('admin.premium-class.departments',compact('group','topics','labels','counts'));
  }
  [$group,$topic]=$this->target($r);
  $topics=$this->topicsFor($group);
  $labels=$this->labelsFor($group,$topics);
  $screen=$r->query('screen','subjects');
  $subjects=LebdRecordedSubject::where('premium_group',$group)->where('premium_topic',$topic)->withCount('chapters')->orderBy('sort_order')->orderBy('id')->get();
  $subject=null;$chapter=null;$chapters=collect();$videos=collect();
  if($r->filled('subject_id')){
   $subject=LebdRecordedSubject::where('premium_group',$group)->where('premium_topic',$topic)->findOrFail((int)$r->query('subject_id'));
   $chapters=$subject->chapters()->withCount('videos')->orderBy('sort_order')->orderBy('id')->get();
  }
  if($r->filled('chapter_id')){
   abort_unless($subject,404);
   $chapter=$subject->chapters()->findOrFail((int)$r->query('chapter_id'));
  }
  if($screen==='videos' && $subject){
   $videos=LebdRecordedVideo::where('subject_id',$subject->id)->where('chapter_id',$chapter?->id)->orderBy('sort_order')->orderBy('id')->get();
  }
  $legacy=LebdRecordedChapter::where('premium_group',$group)->where('premium_topic',$topic)->whereNull('subject_id')->withCount('videos')->orderBy('sort_order')->get();
  return view('admin.premium-class.recordings',compact('group','topic','screen','subjects','subject','chapters','chapter','videos','topics','labels','legacy'));
 }
 private function validatedContext(Request $r):array{
  $group=(string)$r->input('premium_group');$topic=(string)$r->input('premium_topic');
  abort_unless(isset(self::TOPICS[$group])&&in_array($topic,$this->topicsFor($group),true),422);
  return [$group,$topic];
 }
 public function storeSubject(Request $r){
  [$g,$t]=$this->validatedContext($r);
  $d=$r->validate(['title'=>'required|string|max:255','sort_order'=>'nullable|integer|min:0','is_active'=>'required|boolean']);
  LebdRecordedSubject::create($d+['premium_group'=>$g,'premium_topic'=>$t]);
  return back()->with('success','Subject যোগ হয়েছে।');
 }
 public function updateSubject(Request $r,LebdRecordedSubject $subject){
  $d=$r->validate(['title'=>'required|string|max:255','sort_order'=>'nullable|integer|min:0','is_active'=>'required|boolean']);
  $subject->update($d);return back()->with('success','Subject আপডেট হয়েছে।');
 }
 public function destroySubject(LebdRecordedSubject $subject){
  if($subject->chapters()->exists()||$subject->videos()->exists()){return back()->withErrors(['subject'=>'Subject-এ Chapter বা Video আছে। আগে সেগুলো সরান।']);}
  $subject->delete();return back()->with('success','Subject মুছে ফেলা হয়েছে।');
 }
 public function storeChapter(Request $r){
  $d=$r->validate(['subject_id'=>'required|integer|exists:lebd_recorded_subjects,id','title'=>'required|string|max:255','sort_order'=>'nullable|integer|min:0','is_active'=>'required|boolean']);
  $subject=LebdRecordedSubject::findOrFail($d['subject_id']);
  $subject->chapters()->create(['premium_group'=>$subject->premium_group,'premium_topic'=>$subject->premium_topic,'title'=>$d['title'],'sort_order'=>$d['sort_order']??0,'is_active'=>$d['is_active']]);
  return back()->with('success','Chapter যোগ হয়েছে।');
 }
 public function updateChapter(Request $r,LebdRecordedChapter $chapter){
  $d=$r->validate(['title'=>'required|string|max:255','sort_order'=>'nullable|integer|min:0','is_active'=>'required|boolean']);
  $chapter->update($d);return back()->with('success','Chapter আপডেট হয়েছে।');
 }
 public function destroyChapter(LebdRecordedChapter $chapter){
  if($chapter->videos()->exists())return back()->withErrors(['chapter'=>'Chapter-এ Video আছে। আগে Video সরান।']);
  $chapter->delete();return back()->with('success','Chapter মুছে ফেলা হয়েছে।');
 }
 private function videoInput(Request $r):array {
  $d=$r->validate(['title'=>'required|string|max:255','provider'=>['required',Rule::in(['youtube','drive'])],
   'video_url'=>'required|url|max:3000','thumbnail_url'=>'nullable|url|max:2048','thumbnail_file'=>'nullable|file|mimes:jpg,jpeg|max:100|dimensions:width=320,height=180','duration'=>'nullable|string|max:40',
   'sort_order'=>'nullable|integer|min:0|max:999999','is_published'=>'required|boolean']);
  $url=$d['video_url'];$host=strtolower((string)parse_url($url,PHP_URL_HOST));$scheme=strtolower((string)parse_url($url,PHP_URL_SCHEME));
  if($scheme!=='https'){throw ValidationException::withMessages(['video_url'=>'HTTPS লিংক ব্যবহার করুন।']);}
  $allowed=$d['provider']==='youtube'?['youtube.com','www.youtube.com','m.youtube.com','youtu.be','www.youtu.be','youtube-nocookie.com','www.youtube-nocookie.com']:['drive.google.com'];
  if(!in_array($host,$allowed,true)){throw ValidationException::withMessages(['video_url'=>'নির্বাচিত Provider-এর সঠিক লিংক দিন।']);}
  if($d['provider']==='drive'&&!preg_match('~/file/d/[^/]+|[?&]id=[A-Za-z0-9_-]+~',$url)){throw ValidationException::withMessages(['video_url'=>'Google Drive-এর File Share Link দিন।']);}
  if($d['provider']==='youtube'&&!preg_match('~(?:youtu\.be/|[?&]v=|/embed/|/shorts/|/live/)[A-Za-z0-9_-]{6,}~',$url)){throw ValidationException::withMessages(['video_url'=>'YouTube Video Link দিন।']);}
  unset($d['thumbnail_file']);
  return $d;
 }
 private function saveThumbnail(Request $r,array $d):array {
  if (!$r->hasFile('thumbnail_file')) return $d;
  $file=$r->file('thumbnail_file');
  // Protected write path within this application, no user-supplied file names.
  $dir=public_path('web/uploads/premium-thumbnails');
  if (!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir)) {
   throw ValidationException::withMessages(['thumbnail_file'=>'Thumbnail folder তৈরি করা যায়নি।']);
  }
  $name=bin2hex(random_bytes(16)).'.jpg';
  $file->move($dir,$name);
  $d['thumbnail_url']=asset('web/uploads/premium-thumbnails/'.$name);
  return $d;
 }
 public function storeVideo(Request $r){
  $d=$this->saveThumbnail($r,$this->videoInput($r));
  $r->validate(['subject_id'=>'required|integer|exists:lebd_recorded_subjects,id','chapter_id'=>'nullable|integer|exists:lebd_recorded_chapters,id']);
  $subject=LebdRecordedSubject::findOrFail((int)$r->input('subject_id'));
  $chapter=null;
  if($r->filled('chapter_id')){$chapter=$subject->chapters()->findOrFail((int)$r->input('chapter_id'));}
  LebdRecordedVideo::create($d+['subject_id'=>$subject->id,'chapter_id'=>$chapter?->id]);
  return back()->with('success','ভিডিও যোগ হয়েছে।');
 }
 public function updateVideo(Request $r,LebdRecordedVideo $video){$d=$this->saveThumbnail($r,$this->videoInput($r));$video->update($d);return back()->with('success','ভিডিও আপডেট হয়েছে।');}
 public function viewVideo(LebdRecordedVideo $video){
  $url=$video->getRawOriginal('video_url');
  abort_unless(str_starts_with($url,'https://'),404);
  return redirect()->away($url);
 }
 public function destroyVideo(LebdRecordedVideo $video){$video->delete();return back()->with('success','ভিডিও মুছে ফেলা হয়েছে।');}
}