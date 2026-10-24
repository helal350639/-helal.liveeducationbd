<?php
namespace App\Http\Controllers\Students\PremiumClass;
use App\Http\Controllers\Controller;
use App\Models\PremiumClass\LebdRecordedVideo;
use App\Models\PremiumClass\PremiumClassPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
class RecordedClassController extends Controller {
 private const TOPICS=['department'=>['civil','electrical','mechanical','computer','electronic','power','rac','architecture','textile','agriculture'], 'non-department'=>['bangla-grammar','bangla-literature','bangladesh','international','english','math','science','others']];
 public function packages(Request $request){
  $group=(string)$request->query('premium_group','department');
  $topic=(string)$request->query('premium_topic','');
  $type=(string)$request->query('type','all');
  abort_unless(in_array($group,['department','non-department'],true),404);
  abort_unless(in_array($type,['all','combined','department','non_department'],true),404);
  if($topic!=='' && !in_array($topic,self::TOPICS[$group]??[],true))$topic='';
  $packages=PremiumClassPackage::query()->where('is_active',1)->orderBy('sort_order')->orderByDesc('id')->get()
   ->filter(function($p)use($type){
    $kind=str_replace(['-',' '],'_',(string)$p->package_type);
    if($kind==='combine')$kind='combined';
    return in_array($kind,['combined','department','non_department'],true) && ($type==='all'||$type===$kind);
   });
  return view('premium-class.packages',compact('packages','group','topic','type'));
 }
 public function watch(LebdRecordedVideo $video){
  abort_unless($video->is_published,404);
  $isFree=false;
  if($video->chapter_id){
    $first=LebdRecordedVideo::where('chapter_id',$video->chapter_id)->where('is_published',1)->orderBy('sort_order')->orderBy('id')->value('id');
    $isFree=(int)$first===(int)$video->id;
  }elseif($video->subject_id){
    $first=LebdRecordedVideo::where('subject_id',$video->subject_id)->whereNull('chapter_id')->where('is_published',1)->orderBy('sort_order')->orderBy('id')->value('id');
    $isFree=(int)$first===(int)$video->id;
  }
  if (!$isFree) {
    $student=\Illuminate\Support\Facades\Auth::guard('student')->user();
    if (!$student || !\App\Services\PremiumClass\PremiumAccess::hasAccess((int)$student->id,(string)$video->premium_group,(string)$video->premium_topic)) {
      return redirect()->route('premium.recordings.packages',['premium_group'=>$video->premium_group,'premium_topic'=>$video->premium_topic]);
    }
  }
  $url=(string)$video->video_url;
  $host=strtolower((string)parse_url($url,PHP_URL_HOST));$path=(string)parse_url($url,PHP_URL_PATH);
  $embed=null;
  if(in_array($host,['youtube.com','www.youtube.com','m.youtube.com'],true)){
    parse_str((string)parse_url($url,PHP_URL_QUERY),$qs);
    $id=$qs['v']??(preg_match('~^/(?:embed|shorts)/([a-zA-Z0-9_-]{11})~',$path,$m)?$m[1]:null);
    if(is_string($id)&&preg_match('/^[A-Za-z0-9_-]{11}$/',$id))$embed='https://www.youtube-nocookie.com/embed/'.$id;
  }elseif($host==='youtu.be'){
    $id=trim($path,'/');if(preg_match('/^[A-Za-z0-9_-]{11}$/',$id))$embed='https://www.youtube-nocookie.com/embed/'.$id;
  }elseif(in_array($host,['drive.google.com','www.drive.google.com'],true)){
    if(preg_match('~/file/d/([a-zA-Z0-9_-]+)~',$path,$m))$embed='https://drive.google.com/file/d/'.$m[1].'/preview';
  }
  return view('premium-class.watch',compact('video','embed','isFree'));
 }
}
