<?php
namespace App\Http\Controllers\Students\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\ExamSectionMonthlyAccess;

/** New Exam Section only. Never writes to legacy papers/exams tables. */
class StudentRoutineExamController extends Controller
{

    /** Scoped exam listing: no legacy paper ID and no data outside the selected category. */
    private function scopedExams(int $category, ?int $subcategory): array
    {
        $cat = DB::table('lebd_exam_section_categories')->where('id', $category)->where('status', 'Active')->first();
        abort_unless($cat, 404);
        $hasSubcategories = DB::table('lebd_exam_section_subcategories')->where('category_id', $category)->where('status', 'Active')->exists();
        $sub = null;
        if ($hasSubcategories) {
            abort_unless($subcategory, 404);
            $sub = DB::table('lebd_exam_section_subcategories')->where('category_id', $category)
                ->where('id', $subcategory)->where('status', 'Active')->first();
            abort_unless($sub, 404);
        } else {
            abort_if($subcategory !== null, 404);
        }
        $exams = DB::table('lebd_exam_section_routine_items as i')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->where('r.category_id', $category)->where('r.subcategory_id', $sub?->id)
            ->where('i.type', 'exam')
            ->select('i.id', 'i.routine_id', 'i.exam_name', 'i.exam_start_at', 'i.exam_end_at',
                'i.notes', 'r.name as routine_name', 'r.pdf_path')
            ->orderBy('i.exam_start_at')->orderBy('i.id')->get();
        return [$cat, $sub, $exams];
    }

    public function overview(Request $request, int $category, string $section)
    {
        abort_unless(in_array($section, ['routine', 'results', 'papers', 'missing'], true), 404);
        $subId = $request->filled('subcategory_id') ? (int) $request->query('subcategory_id') : null;
        [$cat, $sub, $exams] = $this->scopedExams($category, $subId);
        $student = Auth::guard('student')->user();
        if (in_array($section, ['results', 'missing'], true) && !$student) {
            return redirect()->guest(url('/student/login'));
        }
        $itemIds = $exams->pluck('id')->all();
        $attempts = ($student && $itemIds)
            ? DB::table('lebd_exam_section_student_attempts')->where('student_id', $student->id)
                ->whereIn('routine_item_id', $itemIds)->get()->keyBy('routine_item_id')
            : collect();
        $settingsRow = DB::table('lebd_exam_section_paper_settings')
            ->where('category_id', $category)->where('subcategory_id', $sub?->id)->first();
        $settings = $settingsRow ? (json_decode($settingsRow->settings_json, true) ?: []) : [];
        $resultAllowed = filter_var($settings['student_view_result'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $now = now('Asia/Dhaka');
        $records = $exams->map(function ($exam) use ($attempts, $now, $resultAllowed) {
            $attempt = $attempts->get($exam->id);
            $end = $exam->exam_end_at ? Carbon::parse($exam->exam_end_at, 'Asia/Dhaka') : null;
            $start = $exam->exam_start_at ? Carbon::parse($exam->exam_start_at, 'Asia/Dhaka') : null;
            return (object) ['exam' => $exam, 'attempt' => $attempt,
                'submitted' => $attempt && $attempt->status === 'submitted',
                'ended' => $end && $now->gte($end),
                'live' => $start && $end && $now->gte($start) && $now->lt($end),
                'result_visible' => $attempt && $attempt->status === 'submitted' && $end && $now->gte($end) && $resultAllowed];
        });
        if ($section === 'missing') {
            // Never count future or still-live exams as missed.
            $records = $records->filter(fn($r) => $r->ended && !$r->submitted)->values();
        } elseif ($section === 'results') {
            $records = $records->filter(fn($r) => $r->submitted)->values();
        }
        // Derive displayed syllabus from the actual saved Routine Topic Name selections.
        $routineTopics = collect();
        if ($section === 'routine' && $itemIds) {
            $saved = DB::table('lebd_exam_section_routine_item_topics')
                ->whereIn('item_id', $itemIds)->orderBy('id')->get();
            $subjectIds = []; $chapterIds = []; $departmentIds = [];
            foreach ($saved as $topic) {
                if (!preg_match('/^(department|subject|chapter):(\d+)$/', (string)$topic->topic_key, $m)) continue;
                if ($m[1] === 'subject') $subjectIds[] = (int)$m[2];
                if ($m[1] === 'chapter') $chapterIds[] = (int)$m[2];
                if ($m[1] === 'department') $departmentIds[] = (int)$m[2];
            }
            $chapters = $chapterIds ? DB::table('lebd_exam_routine_chapters')->whereIn('id', array_unique($chapterIds))->get()->keyBy('id') : collect();
            foreach ($chapters as $chapter) $subjectIds[] = (int)$chapter->subject_id;
            $subjects = $subjectIds ? DB::table('lebd_exam_routine_subjects')->whereIn('id', array_unique($subjectIds))->get()->keyBy('id') : collect();
            foreach ($subjects as $subject) $departmentIds[] = (int)$subject->department_id;
            $departments = $departmentIds ? DB::table('lebd_exam_routine_departments')->whereIn('id', array_unique($departmentIds))->get()->keyBy('id') : collect();
            $routineTopics = $saved->groupBy('item_id')->map(function ($list) use ($departments, $subjects, $chapters) {
                return $list->map(function ($topic) use ($departments,$subjects,$chapters) {
                    if (!preg_match('/^(department|subject|chapter):(\d+)$/', (string)$topic->topic_key, $m)) return null;
                    $id = (int)$m[2]; $dept = null; $subj = null; $chap = null;
                    if ($m[1] === 'department') $dept = $departments->get($id);
                    if ($m[1] === 'subject') { $subj = $subjects->get($id); $dept = $subj ? $departments->get($subj->department_id) : null; }
                    if ($m[1] === 'chapter') { $chap=$chapters->get($id); $subj=$chap ? $subjects->get($chap->subject_id) : null; $dept=$subj ? $departments->get($subj->department_id) : null; }
                    return (object)['subject' => $subj->name ?? ($dept->name ?? '—'), 'syllabus' => $chap->name ?? ($subj->name ?? ($dept->name ?? '—')),
                        'range_from'=>$topic->question_start, 'range_to'=>$topic->question_end, 'path'=>implode(' > ',array_filter([$dept->name ?? null,$subj->name ?? null,$chap->name ?? null]))];
                })->filter()->values();
            });
        }
        return view('student.exam-section.exam-overview', compact('cat','sub','section','records','settings','routineTopics'));
    }

    private function context(int $item): array
    {
        $row = DB::table('lebd_exam_section_routine_items as i')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->join('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id')
            ->where('i.id', $item)->where('i.type', 'exam')
            ->select('i.*', 'r.category_id', 'r.subcategory_id', 'r.name as routine_name',
                'c.name as category_name', 'c.status as category_status')->first();
        abort_unless($row && strcasecmp((string)$row->category_status, 'Active') === 0, 404);
        if ($row->subcategory_id) {
            $active = DB::table('lebd_exam_section_subcategories')->where('id', $row->subcategory_id)
                ->where('category_id', $row->category_id)->where('status', 'Active')->exists();
            abort_unless($active, 404);
        }
        $configRow = DB::table('lebd_exam_section_paper_settings')
            ->where('category_id', $row->category_id)
            ->where('subcategory_id', $row->subcategory_id)->first();
        $settings = $configRow ? (json_decode($configRow->settings_json, true) ?: []) : [];
        return [$row, $settings];
    }

    private function questionIds(object $item, array $settings): array
    {
        $ids = DB::table('lebd_exam_section_generated_questions')
            ->where('routine_id', $item->routine_id)->where('exam_name', $item->exam_name)
            ->orderBy('position')->orderBy('id')->pluck('question_id')->map(fn($n)=>(int)$n)->unique()->values()->all();
        $limit = max(1, min(1000, (int)($settings['question_limit'] ?? 100)));
        return array_slice($ids, 0, $limit);
    }

    private function admission(object $row, array $settings, int $studentId): void
    {
        $now = now('Asia/Dhaka');
        abort_unless($row->exam_start_at && $row->exam_end_at, 403, 'Exam schedule is not available.');
        abort_unless($now->gte(Carbon::parse($row->exam_start_at, 'Asia/Dhaka')) &&
            $now->lt(Carbon::parse($row->exam_end_at, 'Asia/Dhaka')), 403, 'This exam is not currently live.');
        // Payment integration is not implemented. Do not allow paid papers to be taken free.
        $paid = filter_var($settings['paid_exam'] ?? false, FILTER_VALIDATE_BOOLEAN);
        abort_if($paid && !ExamSectionMonthlyAccess::premium($studentId, (int)$row->category_id, $row->subcategory_id ? (int)$row->subcategory_id : null), 403, 'This paper requires an active verified premium package.');
        // If an exam explicitly requires enrollment, honor the manual enrollment scope.
        if (filter_var($settings['require_enrollment'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            abort_unless(DB::table('lebd_exam_section_student_enrollments')
                ->where('student_id', $studentId)->where('category_id', $row->category_id)
                ->where('subcategory_id', $row->subcategory_id)->exists(), 403, 'You are not enrolled in this exam.');
        }
    }

    public function show(Request $request, int $item)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403, 'Please log in as a student.');
        [$row, $settings] = $this->context($item);
        $existing = DB::table('lebd_exam_section_student_attempts')
            ->where('routine_item_id', $item)->where('student_id', $student->id)->first();
        if ($existing && $existing->status === 'submitted') return redirect()->route('student.exam-section.take.result', ['item'=>$item]);
        $this->admission($row, $settings, (int)$student->id);
        $ids = $this->questionIds($row, $settings);
        if (!$ids) return view('student.exam-section.take-unavailable', ['message'=>'এই পরীক্ষার প্রশ্ন এখনো তৈরি করা হয়নি।']);
        $minutes = max(1, min(600, (int)($settings['running_minutes'] ?? $settings['exam_running_time'] ?? 40)));
        $start = $existing ? Carbon::parse($existing->started_at, 'Asia/Dhaka') : now('Asia/Dhaka');
        $deadline = $start->copy()->addMinutes($minutes);
        $routineEnd = Carbon::parse($row->exam_end_at, 'Asia/Dhaka');
        if ($deadline->gt($routineEnd)) $deadline = $routineEnd;
        if (now('Asia/Dhaka')->gte($deadline)) return view('student.exam-section.take-unavailable', ['message'=>'এই পরীক্ষার সময় শেষ হয়েছে।']);
        if (!$existing) {
            // Lock student row while allocating one of the three monthly free slots.
            // Prevent simultaneous tabs from starting a fourth paper.
            $admitted = DB::transaction(function () use ($student, $row, $item, $start) {
                DB::table('students')->where('id', $student->id)->lockForUpdate()->first();
                if (!ExamSectionMonthlyAccess::canStart((int)$student->id, (int)$row->category_id, $row->subcategory_id ? (int)$row->subcategory_id : null)) {
                    return false;
                }
                DB::table('lebd_exam_section_student_attempts')->insertOrIgnore([
                    'routine_item_id'=>$item, 'student_id'=>$student->id, 'started_at'=>$start->format('Y-m-d H:i:s'),
                    'status'=>'started', 'created_at'=>now(), 'updated_at'=>now()
                ]);
                return true;
            }, 3);
            if (!$admitted) return redirect()->route('student.exam-section.packages', ['notice'=>'limit']);
        }
        $questions = DB::table('questions')->whereIn('id', $ids)->get(['id','title'])->keyBy('id');
        $options = DB::table('mcq_items')->whereIn('question_id', $ids)->orderBy('id')->get(['id','question_id','item'])->groupBy('question_id');
        $ordered = collect($ids)->map(fn($id)=>$questions->get($id))->filter()->values();
        return view('student.exam-section.take', compact('row','ordered','options','deadline','settings'));
    }

    public function submit(Request $request, int $item)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        [$row, $settings] = $this->context($item);
        $attempt = DB::table('lebd_exam_section_student_attempts')->where('routine_item_id',$item)
            ->where('student_id',$student->id)->first();
        abort_unless($attempt, 403);
        if ($attempt->status === 'submitted') return redirect()->route('student.exam-section.take.result', ['item'=>$item]);
        $minutes=max(1,min(600,(int)($settings['running_minutes'] ?? $settings['exam_running_time'] ?? 40)));
        $deadline=Carbon::parse($attempt->started_at,'Asia/Dhaka')->addMinutes($minutes);
        $routineEnd=Carbon::parse($row->exam_end_at,'Asia/Dhaka');
        if($deadline->gt($routineEnd)) $deadline=$routineEnd;
        // Five seconds to allow an in-flight request to reach the server, not extra exam time.
        abort_if(now('Asia/Dhaka')->gt($deadline->copy()->addSeconds(5)),403,'Exam submission deadline has passed.');
        $ids = $this->questionIds($row, $settings);
        $validAnswers = DB::table('mcq_items')->whereIn('question_id',$ids)->get(['id','question_id','correct_answer']);
        $byQuestion = $validAnswers->groupBy('question_id');
        $sent=$request->input('answers', []);
        abort_unless(is_array($sent),422);
        $stored=[];$correct=0;$wrong=0;
        foreach($ids as $qid){
            $selection=$sent[$qid] ?? null;
            if($selection===null || $selection==='') continue;
            $chosen=($byQuestion->get($qid) ?? collect())->first(fn($v)=>(string)$v->id === (string)$selection);
            if(!$chosen) continue;
            $stored[(string)$qid]=(int)$chosen->id;
            $isCorrect=in_array(strtolower(trim((string)$chosen->correct_answer)),['1','yes','true','correct'],true);
            if($isCorrect) $correct++; else $wrong++;
        }
        $mark=(float)($settings['correct_mark'] ?? 1);
        $minus=(float)($settings['negative_mark'] ?? 0);
        DB::table('lebd_exam_section_student_attempts')->where('id',$attempt->id)->where('status','started')->update([
            'answers_json'=>json_encode($stored),'correct_count'=>$correct,'wrong_count'=>$wrong,
            'score'=>round($correct*$mark-$wrong*$minus,2),'status'=>'submitted',
            'submitted_at'=>now('Asia/Dhaka')->format('Y-m-d H:i:s'),'updated_at'=>now()
        ]);
        return redirect()->route('student.exam-section.take.result',['item'=>$item]);
    }

    public function result(int $item)
    {
        $student=Auth::guard('student')->user();
        abort_unless($student,403);
        [$row,$settings]=$this->context($item);
        $attempt=DB::table('lebd_exam_section_student_attempts')->where('routine_item_id',$item)
            ->where('student_id',$student->id)->where('status','submitted')->first();
        abort_unless($attempt,404);
        $visible=filter_var($settings['student_view_result'] ?? false,FILTER_VALIDATE_BOOLEAN);
        $ended=$row->exam_end_at && now('Asia/Dhaka')->gte(Carbon::parse($row->exam_end_at,'Asia/Dhaka'));
        return view('student.exam-section.take-result',compact('row','attempt','visible','ended'));
    }
}
