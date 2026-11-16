<?php

namespace App\Http\Controllers\Students\ExamSection;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StudentWrongUnansweredQuizController extends Controller
{
    private function scope(int $categoryId, ?int $subcategoryId): array
    {
        $category = DB::table('lebd_exam_section_categories')->where('id',$categoryId)
            ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])->first(['id','name']);
        abort_unless($category,404);
        $subcategory = null;
        if ($subcategoryId) {
            $subcategory = DB::table('lebd_exam_section_subcategories')->where('id',$subcategoryId)
                ->where('category_id',$categoryId)->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
                ->first(['id','category_id','name']);
            abort_unless($subcategory,404);
        }
        return [$category,$subcategory];
    }

    private function settings(int $categoryId, ?int $subcategoryId): array
    {
        $key = $subcategoryId ? 'sub:'.$subcategoryId : 'cat:'.$categoryId;
        $json = Schema::hasTable('lebd_exam_section_paper_settings')
            ? DB::table('lebd_exam_section_paper_settings')->where('scope_key',$key)->value('settings_json') : null;
        return $json ? (json_decode($json,true) ?: []) : [];
    }

    /** Return the unique wrong/unanswered question IDs from submitted exams in this exact scope. */
    private function pool(int $categoryId, ?int $subcategoryId, int $studentId): array
    {
        $attempts = DB::table('lebd_exam_section_student_attempts as a')
            ->join('lebd_exam_section_routine_items as i','i.id','=','a.routine_item_id')
            ->join('lebd_exam_section_routines as r','r.id','=','i.routine_id')
            ->where('a.student_id',$studentId)->where('a.status','submitted')
            ->where('r.category_id',$categoryId)
            ->when($subcategoryId,fn($q)=>$q->where('r.subcategory_id',$subcategoryId))
            ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
            ->select(['a.answers_json','a.submitted_at','i.routine_id','i.exam_name','i.exam_end_at','r.category_id','r.subcategory_id'])
            ->get();

        $wrong=[]; $unanswered=[];
        $settings=$this->settings($categoryId,$subcategoryId);
        $limit=max(1,min(1000,(int)($settings['question_limit']??100)));
        foreach($attempts as $attempt){
            $end=$attempt->exam_end_at ? Carbon::parse($attempt->exam_end_at,'Asia/Dhaka') : null;
            $visible=in_array(strtolower((string)($settings['student_view_result']??'no')),['yes','1','true'],true)
                || ($end && $end->lte(now('Asia/Dhaka')));
            if(!$visible) continue;
            $ids=DB::table('lebd_exam_section_generated_questions')->where('routine_id',$attempt->routine_id)
                ->where('exam_name',$attempt->exam_name)->orderBy('position')->orderBy('id')
                ->limit($limit)->pluck('question_id')->map(fn($v)=>(int)$v)->unique()->all();
            if(!$ids) continue;
            $answers=json_decode((string)($attempt->answers_json??'{}'),true); if(!is_array($answers)) $answers=[];
            $options=DB::table('mcq_items')->whereIn('question_id',$ids)->get(['id','question_id','correct_answer'])->groupBy('question_id');
            foreach($ids as $qid){
                $selected=$answers[(string)$qid]??null;
                $chosen=$selected ? ($options->get($qid,collect())->first(fn($o)=>(string)$o->id===(string)$selected)) : null;
                if(!$chosen){$unanswered[]=$qid;continue;}
                if(!in_array(strtolower(trim((string)$chosen->correct_answer)),['1','yes','true','correct'],true)) $wrong[]=$qid;
            }
        }
        return ['wrong'=>array_values(array_unique($wrong)),'unanswered'=>array_values(array_unique($unanswered))];
    }

    public function setup(Request $request, int $category)
    {
        $student=Auth::guard('student')->user(); abort_unless($student,403);
        $subId=$request->filled('subcategory_id')?(int)$request->query('subcategory_id'):null;
        [$categoryRow,$subcategoryRow]=$this->scope($category,$subId);
        $pool=$this->pool($category,$subId,(int)$student->id);
        $mode=in_array($request->query('mode'),['wrong','unanswered','both'],true)?$request->query('mode'):'wrong';
        $ids=$mode==='wrong'?$pool['wrong']:($mode==='unanswered'?$pool['unanswered']:array_values(array_unique(array_merge($pool['wrong'],$pool['unanswered']))));
        $available=count($ids);
        return view('student.exam-section.quiz-setup',[
            'category'=>$categoryRow,'subcategory'=>$subcategoryRow,'scopeTitle'=>$subcategoryRow->name??$categoryRow->name,
            'mode'=>$mode,'counts'=>['wrong'=>count($pool['wrong']),'unanswered'=>count($pool['unanswered']),'both'=>count(array_unique(array_merge($pool['wrong'],$pool['unanswered'])))],
            'available'=>$available,'settings'=>$this->settings($category,$subId),
        ]);
    }

    public function start(Request $request, int $category)
    {
        $student=Auth::guard('student')->user(); abort_unless($student,403);
        $subId=$request->filled('subcategory_id')?(int)$request->input('subcategory_id'):null;
        [$categoryRow,$subcategoryRow]=$this->scope($category,$subId);
        $validated=$request->validate([
            'mode'=>'required|in:wrong,unanswered,both',
            'question_count'=>'required|integer|min:1|max:200',
            'duration_minutes'=>'required|integer|min:1|max:180',
            'show_result'=>'nullable|boolean',
        ]);
        $pool=$this->pool($category,$subId,(int)$student->id);
        $ids=$validated['mode']==='wrong'?$pool['wrong']:($validated['mode']==='unanswered'?$pool['unanswered']:array_values(array_unique(array_merge($pool['wrong'],$pool['unanswered']))));
        abort_if(!$ids,422,'এই ফিল্টারে কোনো প্রশ্ন পাওয়া যায়নি।');
        abort_if((int)$validated['question_count']>count($ids),422,'প্রশ্নের সংখ্যা উপলব্ধ প্রশ্নের চেয়ে বেশি।');
        if(!Schema::hasTable('lebd_exam_section_practice_quizzes')) abort(503,'Quiz table is not installed. Please run the supplied SQL script first.');
        shuffle($ids); $ids=array_slice($ids,0,(int)$validated['question_count']);
        $settings=$this->settings($category,$subId);
        $mark=(float)($settings['correct_mark']??1); $minus=(float)($settings['negative_mark']??0);
        $now=now('Asia/Dhaka'); $token=Str::random(48);
        $quizId=DB::table('lebd_exam_section_practice_quizzes')->insertGetId([
            'token'=>$token,'student_id'=>$student->id,'category_id'=>$category,'subcategory_id'=>$subId,
            'source_mode'=>$validated['mode'],'question_ids_json'=>json_encode($ids),'answers_json'=>'{}',
            'question_count'=>count($ids),'duration_minutes'=>(int)$validated['duration_minutes'],
            'correct_mark'=>$mark,'negative_mark'=>$minus,'show_result'=>!empty($validated['show_result'])?1:0,
            'started_at'=>$now->format('Y-m-d H:i:s'),'status'=>'started','created_at'=>now(),'updated_at'=>now(),
        ]);
        return redirect()->route('student.exam-section.wrong-unanswered.quiz.take',['category'=>$category,'quiz'=>$quizId,'subcategory_id'=>$subId]);
    }

    public function take(Request $request, int $category, int $quiz)
    {
        $student=Auth::guard('student')->user(); abort_unless($student,403);
        $subId=$request->filled('subcategory_id')?(int)$request->query('subcategory_id'):null;
        [$categoryRow,$subcategoryRow]=$this->scope($category,$subId);
        $attempt=DB::table('lebd_exam_section_practice_quizzes')->where('id',$quiz)->where('student_id',$student->id)
            ->where('category_id',$category)->where('subcategory_id',$subId)->first();
        abort_unless($attempt,404);
        if($attempt->status==='submitted') return redirect()->route('student.exam-section.wrong-unanswered.quiz.result',['category'=>$category,'quiz'=>$quiz,'subcategory_id'=>$subId]);
        $deadline=Carbon::parse($attempt->started_at,'Asia/Dhaka')->addMinutes((int)$attempt->duration_minutes);
        if(now('Asia/Dhaka')->gte($deadline)){
            $this->finish($attempt,[]);
            return redirect()->route('student.exam-section.wrong-unanswered.quiz.result',['category'=>$category,'quiz'=>$quiz,'subcategory_id'=>$subId]);
        }
        $ids=json_decode($attempt->question_ids_json,true)?:[];
        $questions=DB::table('questions')->whereIn('id',$ids)->get(['id','title','image'])->keyBy('id');
        $options=DB::table('mcq_items')->whereIn('question_id',$ids)->orderBy('id')->get(['id','question_id','item','image'])->groupBy('question_id');
        $ordered=collect($ids)->map(fn($id)=>$questions->get($id))->filter()->values();
        return view('student.exam-section.quiz-take',compact('categoryRow','subcategoryRow','attempt','ordered','options','deadline'));
    }

    private function finish(object $attempt, array $sent): void
    {
        $ids=json_decode($attempt->question_ids_json,true)?:[];
        $valid=DB::table('mcq_items')->whereIn('question_id',$ids)->get(['id','question_id','correct_answer'])->groupBy('question_id');
        $stored=[];$correct=0;$wrong=0;
        foreach($ids as $qid){
            $choice=$sent[$qid]??null; if($choice===null||$choice==='') continue;
            $chosen=($valid->get($qid)??collect())->first(fn($o)=>(string)$o->id===(string)$choice);
            if(!$chosen) continue;
            $stored[(string)$qid]=(int)$chosen->id;
            if(in_array(strtolower(trim((string)$chosen->correct_answer)),['1','yes','true','correct'],true))$correct++;else $wrong++;
        }
        $unanswered=max(0,count($ids)-$correct-$wrong);
        $score=round($correct*(float)$attempt->correct_mark-$wrong*(float)$attempt->negative_mark,2);
        DB::table('lebd_exam_section_practice_quizzes')->where('id',$attempt->id)->where('status','started')->update([
            'answers_json'=>json_encode($stored),'correct_count'=>$correct,'wrong_count'=>$wrong,'unanswered_count'=>$unanswered,
            'score'=>$score,'status'=>'submitted','submitted_at'=>now('Asia/Dhaka')->format('Y-m-d H:i:s'),'updated_at'=>now(),
        ]);
    }

    public function submit(Request $request, int $category, int $quiz)
    {
        $student=Auth::guard('student')->user(); abort_unless($student,403);
        $subId=$request->filled('subcategory_id')?(int)$request->input('subcategory_id'):null;
        $attempt=DB::table('lebd_exam_section_practice_quizzes')->where('id',$quiz)->where('student_id',$student->id)
            ->where('category_id',$category)->where('subcategory_id',$subId)->first(); abort_unless($attempt,404);
        if($attempt->status==='started'){
            $deadline=Carbon::parse($attempt->started_at,'Asia/Dhaka')->addMinutes((int)$attempt->duration_minutes);
            abort_if(now('Asia/Dhaka')->gt($deadline->copy()->addSeconds(5)),403,'Quiz submission deadline has passed.');
            $sent=$request->input('answers',[]); abort_unless(is_array($sent),422);
            $this->finish($attempt,$sent);
        }
        return redirect()->route('student.exam-section.wrong-unanswered.quiz.result',['category'=>$category,'quiz'=>$quiz,'subcategory_id'=>$subId]);
    }

    public function result(Request $request, int $category, int $quiz)
    {
        $student=Auth::guard('student')->user(); abort_unless($student,403);
        $subId=$request->filled('subcategory_id')?(int)$request->query('subcategory_id'):null;
        [$categoryRow,$subcategoryRow]=$this->scope($category,$subId);
        $attempt=DB::table('lebd_exam_section_practice_quizzes')->where('id',$quiz)->where('student_id',$student->id)
            ->where('category_id',$category)->where('subcategory_id',$subId)->first(); abort_unless($attempt&&$attempt->status==='submitted',404);
        $ids=json_decode($attempt->question_ids_json,true)?:[];
        $answers=json_decode($attempt->answers_json,true)?:[];
        $questions=DB::table('questions')->whereIn('id',$ids)->get(['id','title'])->keyBy('id');
        $options=DB::table('mcq_items')->whereIn('question_id',$ids)->orderBy('id')->get(['id','question_id','item','correct_answer'])->groupBy('question_id');
        $review=[];
        foreach($ids as $qid){
            $opts=$options->get($qid,collect());$selected=$answers[(string)$qid]??null;
            $chosen=$selected?$opts->first(fn($o)=>(string)$o->id===(string)$selected):null;
            $right=$opts->first(fn($o)=>in_array(strtolower(trim((string)$o->correct_answer)),['1','yes','true','correct'],true));
            $review[]=['question'=>$questions->get($qid),'options'=>$opts,'chosen'=>$chosen,'right'=>$right];
        }
        return view('student.exam-section.quiz-result',compact('categoryRow','subcategoryRow','attempt','review'));
    }
}
