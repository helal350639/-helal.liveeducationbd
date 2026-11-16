<?php

namespace App\Http\Controllers\Students\ExamSection;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentWrongUnansweredController extends Controller
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

    private function resultVisible(object $attempt, array $settings): bool
    {
        $immediate = in_array(strtolower((string)($settings['student_view_result'] ?? 'no')),['yes','1','true'],true);
        $ended = !empty($attempt->exam_end_at)
            && Carbon::parse($attempt->exam_end_at,'Asia/Dhaka')->lte(now('Asia/Dhaka'));
        return $immediate || $ended;
    }

    private function questionIds(object $attempt, array $settings): array
    {
        $ids = DB::table('lebd_exam_section_generated_questions')
            ->where('routine_id',$attempt->routine_id)->where('exam_name',$attempt->exam_name)
            ->orderBy('position')->orderBy('id')->pluck('question_id')
            ->map(fn($v)=>(int)$v)->unique()->values()->all();
        $limit = max(1,min(1000,(int)($settings['question_limit'] ?? 100)));
        return array_slice($ids,0,$limit);
    }

    public function index(Request $request, int $category)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student,403);
        foreach (['lebd_exam_section_student_attempts','lebd_exam_section_routine_items','lebd_exam_section_routines','lebd_exam_section_categories','lebd_exam_section_generated_questions','questions','mcq_items'] as $table) {
            abort_unless(Schema::hasTable($table),503,'Exam review data is not available yet.');
        }
        $subcategoryId = $request->filled('subcategory_id') ? (int)$request->query('subcategory_id') : null;
        [$categoryRow,$subcategoryRow] = $this->scope($category,$subcategoryId);

        $attempts = DB::table('lebd_exam_section_student_attempts as a')
            ->join('lebd_exam_section_routine_items as i','i.id','=','a.routine_item_id')
            ->join('lebd_exam_section_routines as r','r.id','=','i.routine_id')
            ->join('lebd_exam_section_categories as c','c.id','=','r.category_id')
            ->leftJoin('lebd_exam_section_subcategories as s','s.id','=','r.subcategory_id')
            ->where('a.student_id',(int)$student->id)->where('a.status','submitted')
            ->where('r.category_id',$category)
            ->when($subcategoryId,fn($q)=>$q->where('r.subcategory_id',$subcategoryId))
            ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
            ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
            ->whereRaw('LOWER(TRIM(c.status)) = ?', ['active'])
            ->where(function($q){$q->whereNull('r.subcategory_id')->orWhereRaw('LOWER(TRIM(s.status)) = ?', ['active']);})
            ->select(['a.id as attempt_id','a.routine_item_id','a.score','a.correct_count','a.wrong_count','a.answers_json','a.submitted_at',
                'i.exam_name','i.exam_end_at','i.routine_id','r.category_id','r.subcategory_id','r.name as routine_name','c.name as category_name','s.name as subcategory_name'])
            ->orderByDesc('a.submitted_at')->get();

        $settingsByScope=[];
        $cards=[];
        foreach ($attempts as $attempt) {
            $scopeKey = $attempt->subcategory_id ? 'sub:'.(int)$attempt->subcategory_id : 'cat:'.(int)$attempt->category_id;
            if (!array_key_exists($scopeKey,$settingsByScope)) {
                $json = Schema::hasTable('lebd_exam_section_paper_settings')
                    ? DB::table('lebd_exam_section_paper_settings')->where('scope_key',$scopeKey)->value('settings_json') : null;
                $settingsByScope[$scopeKey] = $json ? (json_decode($json,true) ?: []) : [];
            }
            $settings=$settingsByScope[$scopeKey];
            if (!$this->resultVisible($attempt,$settings)) continue;
            $ids=$this->questionIds($attempt,$settings);
            $answers=json_decode((string)($attempt->answers_json ?? '{}'),true);
            if (!is_array($answers)) $answers=[];
            $validOptions = $ids ? DB::table('mcq_items')->whereIn('question_id',$ids)->get(['id','question_id','correct_answer'])->groupBy('question_id') : collect();
            $correct=0; $wrong=0; $unanswered=0;
            foreach ($ids as $qid) {
                $selected=$answers[(string)$qid] ?? null;
                $chosen=$selected ? ($validOptions->get($qid,collect())->first(fn($o)=>(string)$o->id===(string)$selected)) : null;
                if (!$chosen) {$unanswered++; continue;}
                if (in_array(strtolower(trim((string)$chosen->correct_answer)),['1','yes','true','correct'],true)) $correct++; else $wrong++;
            }
            $cards[]=[
                'id'=>(int)$attempt->routine_item_id,'name'=>(string)$attempt->exam_name,
                'routine'=>(string)$attempt->routine_name,'date'=>$attempt->submitted_at ? Carbon::parse($attempt->submitted_at,'Asia/Dhaka')->format('d M Y, h:i A') : '—',
                'score'=>(float)$attempt->score,'correct'=>$correct,'wrong'=>$wrong,'unanswered'=>$unanswered,'total'=>count($ids),
            ];
        }
        return view('student.exam-section.wrong-unanswered',[
            'category'=>$categoryRow,'subcategory'=>$subcategoryRow,'scopeTitle'=>$subcategoryRow->name ?? $categoryRow->name,'cards'=>$cards,
        ]);
    }

    public function show(Request $request, int $category, int $item)
    {
        $student=Auth::guard('student')->user();
        abort_unless($student,403);
        $subcategoryId=$request->filled('subcategory_id')?(int)$request->query('subcategory_id'):null;
        [$categoryRow,$subcategoryRow]=$this->scope($category,$subcategoryId);
        $attempt=DB::table('lebd_exam_section_student_attempts as a')
            ->join('lebd_exam_section_routine_items as i','i.id','=','a.routine_item_id')
            ->join('lebd_exam_section_routines as r','r.id','=','i.routine_id')
            ->where('a.routine_item_id',$item)->where('a.student_id',(int)$student->id)->where('a.status','submitted')
            ->where('r.category_id',$category)->when($subcategoryId,fn($q)=>$q->where('r.subcategory_id',$subcategoryId))
            ->select(['a.*','i.exam_name','i.exam_end_at','i.routine_id','r.category_id','r.subcategory_id','r.name as routine_name'])->first();
        abort_unless($attempt,404);
        $scopeKey=$attempt->subcategory_id?'sub:'.(int)$attempt->subcategory_id:'cat:'.(int)$attempt->category_id;
        $json=Schema::hasTable('lebd_exam_section_paper_settings')?DB::table('lebd_exam_section_paper_settings')->where('scope_key',$scopeKey)->value('settings_json'):null;
        $settings=$json?(json_decode($json,true)?:[]):[];
        abort_unless($this->resultVisible($attempt,$settings),403,'এই পরীক্ষার ফলাফল এখনো প্রকাশিত হয়নি।');
        $ids=$this->questionIds($attempt,$settings);
        $answers=json_decode((string)($attempt->answers_json ?? '{}'),true); if(!is_array($answers))$answers=[];
        $questions=DB::table('questions')->whereIn('id',$ids)->get(['id','title'])->keyBy('id');
        $options=$ids?DB::table('mcq_items')->whereIn('question_id',$ids)->orderBy('id')->get(['id','question_id','item','correct_answer'])->groupBy('question_id'):collect();
        $groups=['correct'=>[],'wrong'=>[],'unanswered'=>[]];
        foreach($ids as $qid){
            $question=$questions->get($qid); if(!$question) continue;
            $opts=$options->get($qid,collect()); $selected=$answers[(string)$qid]??null;
            $chosen=$selected!==null&&$selected!==''?$opts->first(fn($o)=>(string)$o->id===(string)$selected):null;
            $right=$opts->first(fn($o)=>in_array(strtolower(trim((string)$o->correct_answer)),['1','yes','true','correct'],true));
            $status=!$chosen?'unanswered':(in_array(strtolower(trim((string)$chosen->correct_answer)),['1','yes','true','correct'],true)?'correct':'wrong');
            $groups[$status][]=['title'=>$question->title,'options'=>$opts,'chosen_id'=>$chosen->id??null,'correct_id'=>$right->id??null];
        }
        return view('student.exam-section.question-review',[
            'category'=>$categoryRow,'subcategory'=>$subcategoryRow,'scopeTitle'=>$subcategoryRow->name??$categoryRow->name,
            'attempt'=>$attempt,'groups'=>$groups,
        ]);
    }
}
