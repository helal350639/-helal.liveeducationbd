<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Independent entry/attempt/result for saved routine auto questions.
 * Never writes to legacy papers, exams, choices, or MCQ selection tables.
 */
class RoutineAutoStudentExamController extends Controller
{
    private function tableReady(): bool
    {
        return Schema::hasTable('lebd_routine_auto_attempts');
    }

    private function ctx(int $courseId, int $batchId, int $examId, int $departmentId): array
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        $batch = $student->batches()->where('batches.id', $batchId)
            ->where('batches.course_id', $courseId)->first();
        abort_unless($batch, 403, 'এই ব্যাচে আপনার প্রবেশাধিকার নেই।');
        // Match the current purchased-batch controller: order + department membership.
        $ownsDept = $student->departments()->where('departments.id', $departmentId)->exists();
        $validOrder = DB::table('orders')->where('student_id', $student->id)
            ->where('batch_id', $batchId)->where('department_id', $departmentId)
            ->whereIn('status', ['Processing', 'Complete'])->exists();
        abort_unless($ownsDept && $validOrder, 403, 'এই Department-এর অনুমতি যাচাই হয়নি।');
        $assigned = DB::table('batch_department')->where('batch_id', $batchId)
            ->where('department_id', $departmentId)->exists();
        abort_unless($assigned, 403);
        $exam = DB::table('routine_exams as e')
            ->join('routines as r', 'r.id', '=', 'e.routine_id')
            ->where('e.id', $examId)->where('r.course_id', $courseId)
            ->where('r.batch_id', $batchId)->where('r.status', 'Published')
            ->select('e.id', 'e.name', 'r.id as routine_id')->first();
        abort_unless($exam, 404);
        $row = DB::table('routine_item_rows')->where('routine_id', $exam->routine_id)
            ->where('paper_name', $exam->name)->orderByDesc('id')->first();
        abort_unless($row && $row->exam_at && $row->exam_end_at, 404);
        $validDepartment = DB::table('routine_department_columns')
            ->where('routine_id', $exam->routine_id)->where('department_id', $departmentId)->exists()
            || DB::table('routine_department_rows')->where('item_id', $row->id)
                ->where('department_id', $departmentId)->exists();
        // Batch-wide routine with no explicit columns is also permitted.
        $hasColumns = DB::table('routine_department_columns')->where('routine_id', $exam->routine_id)->exists();
        abort_unless($validDepartment || !$hasColumns, 403);
        $common = DB::table('routine_auto_question_plan_questions')
            ->where('exam_id', $examId)->where('scope', 'common')->pluck('question_id')->all();
        $own = DB::table('routine_auto_question_plan_questions')
            ->where('exam_id', $examId)->where('scope', 'department')
            ->where('department_id', $departmentId)->pluck('question_id')->all();
        $ids = array_values(array_unique(array_filter(array_map('intval', array_merge($common, $own)))));
        abort_unless(count($ids) > 0, 404, 'এই পরীক্ষায় কোনো Saved MCQ নেই।');
        $settings = DB::table('routine_batch_paper_settings')->where('batch_id', $batchId)->first();
        $duration = 30;
        foreach (['time', 'duration', 'exam_time', 'duration_minutes'] as $key) {
            if ($settings && isset($settings->{$key}) && (int) $settings->{$key} > 0) {
                $duration = min(600, (int) $settings->{$key});
                break;
            }
        }
        // Only recognized persisted settings enable reattempts; unknown settings fail closed.
        $attemptLimit = 1;
        if ($settings) {
            foreach (['max_attempts', 'attempt_limit', 'attempts_allowed', 'allowed_attempts', 'number_of_attempts', 'repeat_count'] as $column) {
                if (isset($settings->{$column}) && is_numeric($settings->{$column}) && (int)$settings->{$column} > 0) {
                    $attemptLimit = min(100, (int)$settings->{$column});
                    break;
                }
            }
            if ($attemptLimit === 1) {
                foreach (['attempt_type', 'attempt_mode', 'attempt_setting', 'exam_attempt', 'retake_option', 'attempts'] as $column) {
                    $mode = strtolower(trim((string)($settings->{$column} ?? '')));
                    if (in_array($mode, ['multiple', 'multi', 'unlimited'], true)) {
                        $attemptLimit = 100; // Numeric limit above always takes precedence.
                        break;
                    }
                }
            }
        }
        $now = Carbon::now('Asia/Dhaka');
        $start = Carbon::parse($row->exam_at, 'Asia/Dhaka');
        $end = Carbon::parse($row->exam_end_at, 'Asia/Dhaka');
        return compact('student', 'batch', 'exam', 'row', 'ids', 'duration', 'now', 'start', 'end', 'departmentId', 'courseId', 'batchId', 'examId', 'attemptLimit');
    }

    private function attempts(array $c)
    {
        if (!$this->tableReady()) return collect();
        return DB::table('lebd_routine_auto_attempts')
            ->where('student_id', $c['student']->id)
            ->where('routine_exam_id', $c['examId'])
            ->where('department_id', $c['departmentId'])
            ->orderByDesc('id')->get();
    }

    private function attemptRow(array $c)
    {
        $requested = request()->query('attempt_id', request()->input('attempt_id'));
        $list = $this->attempts($c);
        if ($requested !== null && ctype_digit((string)$requested)) {
            return $list->first(fn ($a) => (int)$a->id === (int)$requested);
        }
        return $list->first();
    }

    private function permissions(array $c): array
    {
        $all = $this->attempts($c);
        $completed = $all->where('status', 'Completed')->count();
        $live = $c['now']->gte($c['start']) && $c['now']->lt($c['end']);
        $late = $c['now']->gte($c['end']->copy()->addHours(24)) && $all->isEmpty();
        $canStart = ($live && $all->count() < $c['attemptLimit']) || $late;
        return compact('all', 'completed', 'live', 'late', 'canStart');
    }

    private function uri(string $name, array $c): string
    {
        return route('students.routine-auto.'.$name, [
            'courseId'=>$c['courseId'], 'batchId'=>$c['batchId'],
            'examId'=>$c['examId'], 'departmentId'=>$c['departmentId']]);
    }

    public function info(int $courseId,int $batchId,int $examId,int $departmentId)
    {
        $c = $this->ctx($courseId,$batchId,$examId,$departmentId);
        $permissions = $this->permissions($c);
        $attempt = $this->attemptRow($c);
        $running = $attempt && $attempt->status === 'Live'
            && $c['now']->lt(Carbon::parse($attempt->deadline_at, 'Asia/Dhaka'));
        return view('student-exams.routine-auto-info', [
            'c'=>$c, 'count'=>count($c['ids']), 'live'=>$permissions['live'],
            'running'=>$running, 'late'=>$permissions['late'], 'canStart'=>$permissions['canStart'],
            'attempt'=>$attempt, 'ready'=>$this->tableReady(),
            'attemptCount'=>$permissions['all']->count(), 'attemptLimit'=>$c['attemptLimit'],
            'startUrl'=>$this->uri('start',$c), 'attemptUrl'=>$this->uri('attempt',$c),
            'resultUrl'=>$this->uri('result',$c)]);
    }

    public function start(int $courseId,int $batchId,int $examId,int $departmentId)
    {
        $c = $this->ctx($courseId,$batchId,$examId,$departmentId);
        abort_unless($this->tableReady(), 503, 'Run STEP-6 storage SQL first.');
        $target = null;
        // Serialize requests per student: double-clicks cannot create extra attempts.
        DB::transaction(function () use ($c, $examId, $departmentId, &$target) {
            DB::table('students')->where('id', $c['student']->id)->lockForUpdate()->first();
            $permission = $this->permissions($c);
            $previous = $permission['all']->first();
            if ($previous && $previous->status === 'Live') {
                $target = $this->uri('attempt',$c).'?attempt_id='.$previous->id;
                return;
            }
            if (!$permission['canStart']) {
                $target = $previous && $previous->status === 'Completed'
                    ? $this->uri('result',$c).'?attempt_id='.$previous->id
                    : $this->uri('info',$c);
                return;
            }
            $deadline = $c['now']->copy()->addMinutes($c['duration']);
            $id = DB::table('lebd_routine_auto_attempts')->insertGetId([
                'routine_exam_id'=>$examId, 'student_id'=>$c['student']->id,
                'department_id'=>$departmentId, 'question_ids_json'=>json_encode($c['ids']),
                'answers_json'=>'{}', 'question_count'=>count($c['ids']),
                'started_at'=>$c['now']->toDateTimeString(),
                'deadline_at'=>$deadline->toDateTimeString(),
                'status'=>'Live', 'created_at'=>now(), 'updated_at'=>now(),
            ]);
            $target = $this->uri('attempt',$c).'?attempt_id='.$id;
        }, 3);
        return redirect($target ?: $this->uri('info',$c));
    }

    public function attempt(int $courseId,int $batchId,int $examId,int $departmentId)
    {
        $c = $this->ctx($courseId,$batchId,$examId,$departmentId);
        $attempt = $this->attemptRow($c);
        abort_unless($attempt, 404);
        if ($attempt->status === 'Completed') return redirect($this->uri('result',$c).'?attempt_id='.$attempt->id);
        $ids = array_values(array_map('intval', json_decode($attempt->question_ids_json, true) ?: []));
        $questions = DB::table('questions')->whereIn('id',$ids)->get(['id','title'])->keyBy('id');
        $options = DB::table('mcq_items')->whereIn('question_id',$ids)
            ->orderBy('id')->get(['id','question_id','item'])->groupBy('question_id');
        $ordered = collect($ids)->map(fn ($id)=>$questions->get($id))->filter()->values();
        return view('student-exams.routine-auto-attempt', [
            'c'=>$c, 'attempt'=>$attempt, 'questions'=>$ordered, 'options'=>$options,
            'submitUrl'=>$this->uri('submit',$c), 'resultUrl'=>$this->uri('result',$c)]);
    }

    public function submit(Request $request,int $courseId,int $batchId,int $examId,int $departmentId)
    {
        $c = $this->ctx($courseId,$batchId,$examId,$departmentId);
        abort_unless($this->tableReady(),503);
        $request->validate(['answers'=>'nullable|array','answers.*'=>'nullable|integer|min:1']);
        $attempt = $this->attemptRow($c);
        abort_unless($attempt,404);
        if ($attempt->status === 'Completed') return redirect($this->uri('result',$c).'?attempt_id='.$attempt->id);
        $ids = array_values(array_unique(array_map('intval',json_decode($attempt->question_ids_json,true) ?: [])));
        $raw = $request->input('answers', []);
        $answers = [];
        foreach ($ids as $id) {
            $value = $raw[$id] ?? null;
            if ($value === null || $value === '') continue;
            $answers[$id] = (int) $value;
        }
        $mcqs = DB::table('mcq_items')->whereIn('question_id',$ids)
            ->get(['id','question_id','correct_answer'])->keyBy('id');
        $right = $wrong = 0;
        foreach ($answers as $questionId=>$choiceId) {
            $choice = $mcqs->get($choiceId);
            abort_unless($choice && (int)$choice->question_id === (int)$questionId, 422, 'Invalid MCQ selection.');
            if ($choice->correct_answer) $right++; else $wrong++;
        }
        $unanswered = max(0,count($ids)-count($answers));
        // Atomic compare-and-set makes double submit harmless.
        DB::table('lebd_routine_auto_attempts')->where('id',$attempt->id)->where('status','Live')->update([
            'answers_json'=>json_encode($answers), 'correct_count'=>$right,
            'wrong_count'=>$wrong, 'unanswered_count'=>$unanswered,
            'score'=>$right, 'status'=>'Completed',
            'submitted_at'=>Carbon::now('Asia/Dhaka')->toDateTimeString(),
            'updated_at'=>now(),
        ]);
        return redirect($this->uri('result',$c).'?attempt_id='.$attempt->id);
    }

    public function result(int $courseId,int $batchId,int $examId,int $departmentId)
    {
        $c = $this->ctx($courseId,$batchId,$examId,$departmentId);
        $attempt = $this->attemptRow($c);
        abort_unless($attempt && $attempt->status === 'Completed',404);
        return view('student-exams.routine-auto-result', compact('c','attempt'));
    }
}
