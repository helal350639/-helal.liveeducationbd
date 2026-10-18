<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

/** Server-side MCQ entitlement and quota accounting (Bangladesh timezone). */
class McqAccessService
{
    public function studentId(): ?int { $id=Auth::guard('student')->id(); return $id ? (int)$id : null; }
    public function settings(): object {
        return DB::table('lebd_mcq_access_settings')->where('id',1)->first() ?: (object)['free_daily_reading'=>20,'free_monthly_quiz'=>3];
    }
    public function access(int $categoryId): array {
        $studentId=$this->studentId();
        $free=['paid'=>false,'package_id'=>null,'reading_limit'=>(int)$this->settings()->free_daily_reading,
            'quiz_limit'=>(int)$this->settings()->free_monthly_quiz];
        if (!$studentId) return $free;
        $now=Carbon::now('Asia/Dhaka')->toDateTimeString();
        $subs=DB::table('lebd_mcq_subscriptions as s')
            ->join('mcq_subscription_packages as p','p.id','=','s.package_id')
            ->join('mcq_subscription_package_categories as pc',function($j)use($categoryId){$j->on('pc.package_id','=','p.id')->where('pc.category_id',$categoryId);})
            ->where('s.student_id',$studentId)->where('s.starts_at','<=',$now)->where('s.ends_at','>',$now)
            ->orderByDesc('s.ends_at')
            ->select('s.package_id','p.reading_limit','p.quiz_limit')->get();
        if($subs->isEmpty()) return $free;
        // Select a single subscribed package for a category, favoring unlimited quotas.
        $chosen=$subs->sort(function($a,$b){$ar=is_null($a->reading_limit)?PHP_INT_MAX:(int)$a->reading_limit;
            $br=is_null($b->reading_limit)?PHP_INT_MAX:(int)$b->reading_limit;
            return $br <=> $ar;})->first();
        return ['paid'=>true,'package_id'=>(int)$chosen->package_id,'reading_limit'=>$chosen->reading_limit===null?null:(int)$chosen->reading_limit,
            'quiz_limit'=>$chosen->quiz_limit===null?null:(int)$chosen->quiz_limit];
    }
    private function date(): string {return Carbon::now('Asia/Dhaka')->format('Y-m-d');}
    private function month(): string {return Carbon::now('Asia/Dhaka')->format('Y-m');}
    public function remainingReading(int $studentId,array $access): ?int {
        if($access['reading_limit']===null) return null;
        $q=DB::table('lebd_mcq_read_events')->where('student_id',$studentId)->where('read_date',$this->date());
        if($access['paid']) $q->where('package_id',$access['package_id']); else $q->whereNull('package_id');
        return max(0,$access['reading_limit']-$q->count());
    }
    public function remainingQuizzes(int $studentId,array $access): ?int {
        if($access['quiz_limit']===null)return null;
        $q=DB::table('lebd_mcq_quiz_attempts')->where('student_id',$studentId)->where('quiz_month',$this->month());
        if($access['paid'])$q->where('package_id',$access['package_id']);else $q->whereNull('package_id');
        return max(0,$access['quiz_limit']-$q->count());
    }
    /** Reserve up to remaining unique, active linked MCQs on each reading GET. */
    public function reserveReading(int $categoryId,array $questionIds,array $access): array {
        $studentId=$this->studentId(); if(!$studentId) return [];
        $day=$this->date();$now=now();$allowed=[];
        DB::transaction(function()use($studentId,$questionIds,$categoryId,$access,$day,$now,&$allowed){
            // Serialize simultaneous tabs for this student; quota cannot be bypassed by parallel loads.
            DB::table('students')->where('id',$studentId)->lockForUpdate()->first();
            $query=DB::table('lebd_mcq_read_events')->where('student_id',$studentId)->where('read_date',$day);
            if($access['paid'])$query->where('package_id',$access['package_id']);else $query->whereNull('package_id');
            $already=$query->pluck('question_id')->map(fn($id)=>(int)$id)->all();
            $remaining=$access['reading_limit']===null?PHP_INT_MAX:max(0,$access['reading_limit']-count($already));
            foreach($questionIds as $id){$id=(int)$id;
                if(in_array($id,$already,true)){ $allowed[]=$id; continue; }
                if($remaining<=0)continue;
                DB::table('lebd_mcq_read_events')->insertOrIgnore(['student_id'=>$studentId,'question_id'=>$id,'category_id'=>$categoryId,
                    'package_id'=>$access['paid']?$access['package_id']:null,'read_date'=>$day,'created_at'=>$now]);
                $allowed[]=$id;$already[]=$id;$remaining--;
            }
        });
        return $allowed;
    }
    public function reserveQuiz(int $categoryId,string $token,array $access): bool {
        $studentId=$this->studentId();if(!$studentId)return false;
        $result=false;
        DB::transaction(function()use($studentId,$categoryId,$token,$access,&$result){
            DB::table('students')->where('id',$studentId)->lockForUpdate()->first();
            if(DB::table('lebd_mcq_quiz_attempts')->where('token',$token)->exists()){$result=true;return;}
            $remain=$this->remainingQuizzes($studentId,$access);
            if($remain!==null && $remain<=0)return;
            DB::table('lebd_mcq_quiz_attempts')->insert(['student_id'=>$studentId,'category_id'=>$categoryId,
                'package_id'=>$access['paid']?$access['package_id']:null,'quiz_month'=>$this->month(),'token'=>$token,'created_at'=>now()]);
            $result=true;
        });
        return $result;
    }
}
