<?php

namespace App\Http\Controllers\Students\ExamSection;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Student-only, read-only analytics for submitted Exam Section attempts. */
class ExamMarkProgressController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);

        foreach (['lebd_exam_section_categories','lebd_exam_section_routines','lebd_exam_section_routine_items','lebd_exam_section_student_attempts'] as $table) {
            abort_unless(Schema::hasTable($table), 503, 'Exam progress data is not available yet.');
        }

        // The report is always bound to the Category / Sub-category currently
        // opened in the Exam Blade page. No student-selectable scope filters.
        $categoryId = (int) $request->query('category_id', 0);
        $subcategoryId = $request->filled('subcategory_id') ? (int) $request->query('subcategory_id') : null;
        abort_unless($categoryId > 0, 404);

        $category = DB::table('lebd_exam_section_categories')
            ->where('id', $categoryId)
            ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
            ->first(['id', 'name']);
        abort_unless($category, 404);

        $subcategory = null;
        if ($subcategoryId) {
            abort_unless(Schema::hasTable('lebd_exam_section_subcategories'), 404);
            $subcategory = DB::table('lebd_exam_section_subcategories')
                ->where('id', $subcategoryId)
                ->where('category_id', $categoryId)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
                ->first(['id', 'category_id', 'name']);
            abort_unless($subcategory, 404);
        }

        $query = DB::table('lebd_exam_section_student_attempts as a')
            ->join('lebd_exam_section_routine_items as i','i.id','=','a.routine_item_id')
            ->join('lebd_exam_section_routines as r','r.id','=','i.routine_id')
            ->join('lebd_exam_section_categories as c','c.id','=','r.category_id')
            ->leftJoin('lebd_exam_section_subcategories as s','s.id','=','r.subcategory_id')
            ->where('a.student_id',(int)$student->id)->where('a.status','submitted')
            ->where('r.category_id', $categoryId)
            ->when($subcategoryId, fn($q) => $q->where('r.subcategory_id', $subcategoryId))
            ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
            ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
            ->whereRaw('LOWER(TRIM(c.status)) = ?', ['active'])
            ->where(function($q){ $q->whereNull('r.subcategory_id')->orWhereRaw('LOWER(TRIM(s.status)) = ?', ['active']); })
            ->select(['a.id as attempt_id','a.routine_item_id','a.score','a.correct_count','a.wrong_count','a.answers_json','a.submitted_at',
                'i.exam_name','i.exam_start_at','i.exam_end_at','i.routine_id','r.category_id','r.subcategory_id','r.name as routine_name',
                'c.name as category_name','s.name as subcategory_name'])
            ->orderBy('a.submitted_at');

        $rawAttempts = $query->get();
        // Respect the existing Exam Section result-publication rule for every scope.
        $settingsByScope = [];
        $visibleAttempts = $rawAttempts->filter(function($row) use (&$settingsByScope) {
            $scopeKey = $row->subcategory_id ? 'sub:'.(int)$row->subcategory_id : 'cat:'.(int)$row->category_id;
            if (!array_key_exists($scopeKey,$settingsByScope)) {
                $setting = Schema::hasTable('lebd_exam_section_paper_settings')
                    ? DB::table('lebd_exam_section_paper_settings')->where('scope_key',$scopeKey)->value('settings_json') : null;
                $settingsByScope[$scopeKey] = $setting ? (json_decode($setting,true) ?: []) : [];
            }
            $settings = $settingsByScope[$scopeKey];
            $immediate = in_array(strtolower((string)($settings['student_view_result'] ?? 'no')),['yes','1','true'],true);
            $ended = $row->exam_end_at && Carbon::parse($row->exam_end_at,'Asia/Dhaka')->lte(now('Asia/Dhaka'));
            return $immediate || $ended;
        })->values();

        // Category view includes its sub-categories; a sub-category view is exact.
        $attempts = $visibleAttempts->sortBy('submitted_at')->values();

        $rows = [];
        $subjectTotals = [];
        foreach ($attempts as $attempt) {
            $scopeKey = $attempt->subcategory_id ? 'sub:'.(int)$attempt->subcategory_id : 'cat:'.(int)$attempt->category_id;
            $settings = $settingsByScope[$scopeKey] ?? [];
            $questionIds = Schema::hasTable('lebd_exam_section_generated_questions')
                ? DB::table('lebd_exam_section_generated_questions')->where('routine_id',$attempt->routine_id)->where('exam_name',$attempt->exam_name)
                    ->orderBy('position')->orderBy('id')->pluck('question_id')->map(fn($v)=>(int)$v)->unique()->values()->all()
                : [];
            $limit = max(1,min(1000,(int)($settings['question_limit'] ?? 100)));
            $questionIds = array_slice($questionIds,0,$limit);
            $positive = (float)($settings['correct_mark'] ?? 1);
            $negative = (float)($settings['wrong_deduction'] ?? $settings['negative_mark'] ?? 0);
            $totalQuestions = count($questionIds);
            $correct = (int)$attempt->correct_count;
            $wrong = (int)$attempt->wrong_count;
            $answered = $correct + $wrong;
            $accuracy = $answered > 0 ? round(($correct/$answered)*100,1) : 0;
            $possible = $totalQuestions * $positive;
            $score = (float)$attempt->score;
            $percent = $possible > 0 ? round(($score/$possible)*100,1) : 0;
            $rows[] = [
                'id'=>(int)$attempt->routine_item_id,'name'=>(string)$attempt->exam_name,
                'category'=>(string)$attempt->category_name,'subcategory'=>(string)($attempt->subcategory_name ?? ''),
                'date'=>$attempt->submitted_at ? Carbon::parse($attempt->submitted_at,'Asia/Dhaka')->format('d M Y') : '—',
                'timestamp'=>$attempt->submitted_at ? Carbon::parse($attempt->submitted_at,'Asia/Dhaka')->timestamp : 0,
                'score'=>$score,'possible'=>$possible,'percent'=>$percent,'correct'=>$correct,'wrong'=>$wrong,
                'unanswered'=>max(0,$totalQuestions-$answered),'accuracy'=>$accuracy,'questions'=>$totalQuestions,
            ];

            if ($questionIds && Schema::hasTable('lebd_exam_section_generated_questions') && Schema::hasTable('mcq_items')) {
                $generated = DB::table('lebd_exam_section_generated_questions')->where('routine_id',$attempt->routine_id)
                    ->where('exam_name',$attempt->exam_name)->whereIn('question_id',$questionIds)
                    ->orderBy('position')->orderBy('id')->get(['question_id','topic_key']);
                $topicByQuestion=[];
                foreach($generated as $g) $topicByQuestion[(int)$g->question_id] ??= (string)$g->topic_key;
                $answers = json_decode((string)($attempt->answers_json ?? '{}'),true);
                if (!is_array($answers)) $answers=[];
                $options = DB::table('mcq_items')->whereIn('question_id',$questionIds)->get(['id','question_id','correct_answer'])->groupBy('question_id');
                $subjectNames=[]; $chapterIds=[]; $subjectIds=[]; $departmentIds=[];
                foreach($topicByQuestion as $topicKey) if(preg_match('/^(department|subject|chapter):(\d+)$/',$topicKey,$m)) {
                    if($m[1]==='chapter') $chapterIds[]=(int)$m[2]; elseif($m[1]==='subject') $subjectIds[]=(int)$m[2]; else $departmentIds[]=(int)$m[2];
                }
                $chapters=$chapterIds?DB::table('lebd_exam_routine_chapters')->whereIn('id',array_unique($chapterIds))->get(['id','subject_id'])->keyBy('id'):collect();
                foreach($chapters as $ch) $subjectIds[]=(int)$ch->subject_id;
                $subjects=$subjectIds?DB::table('lebd_exam_routine_subjects')->whereIn('id',array_unique($subjectIds))->get(['id','name','department_id'])->keyBy('id'):collect();
                foreach($subjects as $subj) $departmentIds[]=(int)$subj->department_id;
                $departments=$departmentIds?DB::table('lebd_exam_routine_departments')->whereIn('id',array_unique($departmentIds))->get(['id','name'])->keyBy('id'):collect();
                foreach($questionIds as $qid) {
                    $key=$topicByQuestion[(int)$qid] ?? ''; $label='অন্যান্য';
                    if(preg_match('/^(department|subject|chapter):(\d+)$/',$key,$m)) {
                        $id=(int)$m[2]; $sid=$m[1]==='subject'?$id:($m[1]==='chapter'?$chapters->get($id)?->subject_id:null);
                        if($sid && $subjects->has((int)$sid)) $label=(string)$subjects->get((int)$sid)->name;
                        elseif($m[1]==='department' && $departments->has($id)) $label=(string)$departments->get($id)->name;
                    }
                    $subjectNames[$label]=true;
                    $subjectTotals[$label] ??= ['subject'=>$label,'questions'=>0,'correct'=>0,'wrong'=>0,'marks'=>0.0,'possible'=>0.0];
                    $subjectTotals[$label]['questions']++; $subjectTotals[$label]['possible'] += $positive;
                    $chosenId=$answers[(string)$qid] ?? null;
                    if($chosenId!==null && $chosenId!=='') {
                        $chosen=($options->get((int)$qid) ?? collect())->first(fn($o)=>(string)$o->id===(string)$chosenId);
                        if($chosen && in_array(strtolower(trim((string)$chosen->correct_answer)),['1','yes','true','correct'],true)) {
                            $subjectTotals[$label]['correct']++; $subjectTotals[$label]['marks'] += $positive;
                        } elseif($chosen) {
                            $subjectTotals[$label]['wrong']++; $subjectTotals[$label]['marks'] -= $negative;
                        }
                    }
                }
            }
        }

        $subjectRows = collect($subjectTotals)->map(function($r){
            $r['percent']=$r['possible']>0?round($r['marks']/$r['possible']*100,1):0;
            return $r;
        })->sortByDesc('percent')->values();
        $totalExams = count($rows);
        $completed = $totalExams;
        $average = $totalExams ? round(collect($rows)->avg('percent'),1) : 0;
        $highest = $totalExams ? max(array_column($rows,'score')) : 0;
        $latest = $totalExams ? end($rows) : null;
        $first = $totalExams ? reset($rows) : null;
        $improvement = ($first && $latest && $first['percent'] > 0) ? round($latest['percent']-$first['percent'],1) : null;

        $scopeTitle = $subcategory ? $subcategory->name : $category->name;
        return view('student.exam-section.mark-progress', compact('category','subcategory','scopeTitle','categoryId','subcategoryId','rows','subjectRows','totalExams','completed','average','highest','latest','improvement'));
    }
}
