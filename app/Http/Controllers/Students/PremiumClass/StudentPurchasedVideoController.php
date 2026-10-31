<?php
namespace App\Http\Controllers\Students\PremiumClass;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\PremiumClass\LebdRecordedVideo;

class StudentPurchasedVideoController extends Controller
{
    private function order(int $id)
    {
        abort_unless(auth('student')->check(), 403);
        abort_unless(Schema::hasTable('lebd_premium_class_orders'),404);
        $row=DB::table('lebd_premium_class_orders as o')
          ->leftJoin('lebd_premium_class_packages as p','p.id','=','o.package_id')
          ->where('o.id',$id)->where('o.student_id',auth('student')->id())
          ->where('o.status','paid')->where('o.expires_at','>',now())
          ->select('o.*','p.name as package_name')->first();
        abort_unless($row,403,'Premium package not active.');
        return $row;
    }
    private function allowed($order): array
    {
        $type=str_replace('-','_', (string)$order->package_type);
        $groups=[];
        foreach(['department'=>'department_categories','non-department'=>'non_department_categories'] as $group=>$field){
            if($type!=='combined' && $type!=='combine' && $type!==str_replace('-','_',$group))continue;
            $topics=json_decode((string)$order->{$field},true);
            if(is_array($topics))$groups[$group]=array_values(array_filter($topics,'is_string'));
        }
        return $groups;
    }
    private function belongs($row,$order): void
    {
        $groups=$this->allowed($order);
        abort_unless(isset($groups[$row->premium_group]) && in_array($row->premium_topic,$groups[$row->premium_group],true),403);
    }
    public function index(Request $request)
    {
        abort_unless(auth('student')->check(),403);
        return view('student-panel.purchased-video-classes');
    }
    public function departments(int $order)
    {
        $paid=$this->order($order);$groups=$this->allowed($paid);
        return view('student-panel.purchased-video-navigation',compact('paid','groups')+['stage'=>'departments']);
    }
    public function subjects(int $order,string $group,string $topic)
    {
        $paid=$this->order($order);$this->belongs((object)['premium_group'=>$group,'premium_topic'=>$topic],$paid);
        $subjects=DB::table('lebd_recorded_subjects')->where('premium_group',$group)->where('premium_topic',$topic)->where('is_active',1)->orderBy('sort_order')->orderBy('id')->get();
        return view('student-panel.purchased-video-navigation',compact('paid','group','topic','subjects')+['stage'=>'subjects']);
    }
    public function chapters(int $order,int $subject)
    {
        $paid=$this->order($order);
        $selected=DB::table('lebd_recorded_subjects')->where('id',$subject)->where('is_active',1)->first();abort_unless($selected,404);
        $this->belongs($selected,$paid);
        $chapters=DB::table('lebd_recorded_chapters')->where('premium_group',$selected->premium_group)->where('premium_topic',$selected->premium_topic)->where('is_active',1)
          ->where(function($q)use($subject){$q->where('subject_id',$subject)->orWhereNull('subject_id');})->orderBy('sort_order')->orderBy('id')->get();
        // Match videos by chapter ID even when the legacy video row has NULL subject_id.
        // Never classify chapter-linked videos as standalone.
        $chapterIds=$chapters->pluck('id')->all();
        $chapterVideoGroups=collect();
        if($chapterIds){
            $chapterVideoGroups=DB::table('lebd_recorded_videos')
                ->whereIn('chapter_id',$chapterIds)->where('is_published',1)
                ->orderBy('sort_order')->orderBy('id')->get()->groupBy('chapter_id');
        }
        $standalone=DB::table('lebd_recorded_videos')->where('subject_id',$subject)->whereNull('chapter_id')->where('is_published',1)->orderBy('sort_order')->orderBy('id')->get();
        return view('student-panel.purchased-video-navigation',compact('paid','selected','chapters','standalone','chapterVideoGroups')+['stage'=>'chapters']);
    }
    public function videos(int $order,int $chapter)
    {
        $paid=$this->order($order);
        $selected=DB::table('lebd_recorded_chapters')->where('id',$chapter)->where('is_active',1)->first();abort_unless($selected,404);
        $this->belongs($selected,$paid);
        $videos=DB::table('lebd_recorded_videos')->where('chapter_id',$chapter)->where('is_published',1)->orderBy('sort_order')->orderBy('id')->get();
        return view('student-panel.purchased-video-navigation',compact('paid','selected','videos')+['stage'=>'videos']);
    }
    public function watch(int $order,int $video)
    {
        $paid=$this->order($order);
        $row=DB::table('lebd_recorded_videos')->where('id',$video)->where('is_published',1)->first();abort_unless($row,404);
        $ancestor=null;
        if($row->chapter_id)$ancestor=DB::table('lebd_recorded_chapters')->where('id',$row->chapter_id)->where('is_active',1)->first();
        if(!$ancestor && $row->subject_id)$ancestor=DB::table('lebd_recorded_subjects')->where('id',$row->subject_id)->where('is_active',1)->first();
        abort_unless($ancestor,403);
        $this->belongs($ancestor,$paid);
        if($row->chapter_id && $row->subject_id && isset($ancestor->subject_id) && $ancestor->subject_id && (int)$ancestor->subject_id !== (int)$row->subject_id)abort(403);
        $videoModel=LebdRecordedVideo::findOrFail($video);
        $url=(string)$videoModel->video_url;
        $host=strtolower((string)parse_url($url,PHP_URL_HOST));$path=(string)parse_url($url,PHP_URL_PATH);
        $embed=null;
        if(in_array($host,['youtube.com','www.youtube.com','m.youtube.com'],true)){
            parse_str((string)parse_url($url,PHP_URL_QUERY),$q);
            $id=$q['v']??(preg_match('~^/(?:embed|shorts)/([a-zA-Z0-9_-]{11})~',$path,$m)?$m[1]:null);
            if(is_string($id)&&preg_match('/^[A-Za-z0-9_-]{11}$/',$id))$embed='https://www.youtube-nocookie.com/embed/'.$id;
        }elseif($host==='youtu.be'){
            $id=trim($path,'/');if(preg_match('/^[A-Za-z0-9_-]{11}$/',$id))$embed='https://www.youtube-nocookie.com/embed/'.$id;
        }elseif(in_array($host,['drive.google.com','www.drive.google.com'],true)){
            if(preg_match('~/file/d/([a-zA-Z0-9_-]+)~',$path,$m))$embed='https://drive.google.com/file/d/'.$m[1].'/preview';
        }
        abort_unless($embed,422,'Video provider or URL is invalid.');
        $isDrive=str_contains($embed,'drive.google.com');
        if(!$isDrive) $embed .= '?rel=0&playsinline=1&fs=0&iv_load_policy=3&disablekb=1';

        return view('student-panel.purchased-video-player',compact('paid','videoModel','embed','isDrive'));
    }
}
