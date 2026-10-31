<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AllBatchExamPaperController extends Controller
{
    public function index(Request $request, int $batchId)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student && $student->batches()->where('batches.id', $batchId)->exists(), 403);
        $batch = DB::table('batches')->where('id', $batchId)->first();
        abort_unless($batch, 404);

        // Same intersection of active orders and department membership as PurchasedBatchExamsController.
        $verifiedIds = [];
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'department_id')
            && Schema::hasColumn('orders', 'status')) {
            $orders = DB::table('orders')->where('student_id', $student->id)
                ->where('batch_id', $batchId)->whereIn('status', ['Processing', 'Complete'])
                ->whereNotNull('department_id')->pluck('department_id')->map(fn ($id) => (int) $id)->all();
            $memberIds = $student->departments()->pluck('departments.id')->map(fn ($id) => (int) $id)->all();
            $verifiedIds = array_values(array_unique(array_intersect($orders, $memberIds)));
        }
        abort_if(empty($verifiedIds), 403, 'এই ব্যাচের কোনো অনুমোদিত Department পাওয়া যায়নি।');

        $selected = $request->query('department_id');
        if ($selected !== null && $selected !== '') {
            abort_unless(ctype_digit((string) $selected) && in_array((int) $selected, $verifiedIds, true), 403);
            $departmentId = (int) $selected;
        } elseif (count($verifiedIds) === 1) {
            $departmentId = $verifiedIds[0];
        } else {
            $departmentId = null;
        }

        $departments = DB::table('departments')->whereIn('id', $verifiedIds)
            ->orderBy('name')->get(['id', 'name']);
        $selectedDepartment = $departmentId ? $departments->firstWhere('id', $departmentId) : null;
        $exams = [];
        $routine = null;
        $now = Carbon::now('Asia/Dhaka');

        // Multiple departments: require explicit selection, not a union that could mix question banks.
        if ($departmentId !== null) {
            $routine = \App\Services\BatchPublishedRoutineSelector::find(
                (int) $batch->course_id, $batchId, $departmentId
            );

            if ($routine) {
                $routineExamIds = DB::table('routine_exams')->where('routine_id', $routine->id)
                    ->get(['id', 'name'])->keyBy(fn ($e) => trim((string) $e->name));

                $mainRows = DB::table('routine_item_rows')->where('routine_id', $routine->id)
                    ->orderBy('id')->get(['id', 'paper_name', 'exam_at', 'exam_end_at']);
                foreach ($mainRows as $item) {
                    $name = trim((string) $item->paper_name);
                    $examRecord = $routineExamIds->get($name);
                    $examId = $examRecord ? (int) $examRecord->id : null;
                    $hasQuestions = $examId && DB::table('routine_auto_question_plan_questions')
                        ->where('exam_id', $examId)
                        ->where(function ($q) use ($departmentId) {
                            $q->where('scope', 'common')
                                ->orWhere(function ($sub) use ($departmentId) {
                                    $sub->where('scope', 'department')->where('department_id', $departmentId);
                                });
                        })->exists();
                    $exams[] = $this->examRow($name, $item->exam_at, $item->exam_end_at,
                        $this->topics((int) $item->id, $departmentId), $now,
                        $hasQuestions ? route('students.routine-auto.info', [
                            'courseId' => $batch->course_id, 'batchId' => $batchId,
                            'examId' => $examId, 'departmentId' => $departmentId,
                        ]) : null, 'Main Exam');
                    $lastIndex = count($exams) - 1;
                    $exams[$lastIndex]['attempted'] = false;
                    $exams[$lastIndex]['can_take'] = false;
                    $exams[$lastIndex]['result_url'] = null;
                    if ($examId && Schema::hasTable('lebd_routine_auto_attempts')) {
                        $history = DB::table('lebd_routine_auto_attempts')
                            ->where('routine_exam_id', $examId)
                            ->where('student_id', $student->id)
                            ->where('department_id', $departmentId)->orderByDesc('id')->get();
                        $submitted = $history->firstWhere('status', 'Completed');
                        $exams[$lastIndex]['attempted'] = (bool)$submitted;
                        if ($submitted && $hasQuestions) {
                            $exams[$lastIndex]['result_url'] = route('students.routine-auto.result', [
                                'courseId'=>$batch->course_id, 'batchId'=>$batchId,
                                'examId'=>$examId, 'departmentId'=>$departmentId,
                                'attempt_id'=>$submitted->id,
                            ]);
                        }
                    }
                    if ($hasQuestions && $item->exam_at && $item->exam_end_at) {
                        $open = Carbon::parse($item->exam_at, 'Asia/Dhaka');
                        $close = Carbon::parse($item->exam_end_at, 'Asia/Dhaka');
                        $exams[$lastIndex]['can_take'] = ($now->gte($open) && $now->lt($close))
                            || ($now->gte($close->copy()->addHours(24)) && !$exams[$lastIndex]['attempted']);
                    }

                }
                if (Schema::hasTable('routine_derived_exam_items')) {
                    $derived = DB::table('routine_derived_exam_items')->where('routine_id', $routine->id)
                        ->orderBy('id')->get(['exam_name', 'exam_at', 'exam_end_at']);
                    foreach ($derived as $item) {
                        $exams[] = $this->examRow($item->exam_name, $item->exam_at, $item->exam_end_at,
                            [], $now, null, 'Revision / Model');
                    }
                }
                usort($exams, fn ($a, $b) => $a['stamp'] <=> $b['stamp'] ?: strnatcasecmp($a['name'], $b['name']));
            }
        }

        return view('student-exams.all-exam-papers', compact(
            'batch', 'departments', 'selectedDepartment', 'exams', 'routine'
        ));
    }

    private function examRow($name, $start, $end, array $topics, Carbon $now, ?string $infoUrl, string $kind): array
    {
        $from = $start ? Carbon::parse($start, 'Asia/Dhaka') : null;
        $until = $end ? Carbon::parse($end, 'Asia/Dhaka') : null;
        $status = !$from ? 'Unscheduled' : ($now->lt($from) ? 'Upcoming'
            : (!$until || $now->lt($until) ? 'Live Now' : 'Completed'));
        return [
            'name' => (string) $name, 'kind' => $kind,
            'date' => $from ? $from->format('d M Y') : '—',
            'time' => $from ? $from->format('h:i A') . ($until ? ' – ' . $until->format('h:i A') : '') : '—',
            'status' => $status, 'topics' => $topics,
            'info_url' => $infoUrl, 'stamp' => $from ? $from->timestamp : PHP_INT_MAX,
        ];
    }

    private function topics(int $rowId, int $departmentId): array
    {
        $topics = [];
        $department = DB::table('routine_department_rows as rd')
            ->join('departments as d', 'd.id', '=', 'rd.department_id')
            ->where('rd.item_id', $rowId)->where('rd.department_id', $departmentId)
            ->first(['d.name', 'rd.details']);
        if ($department) {
            $topics[] = ['name' => $department->name, 'details' => $this->plain($department->details)];
        }
        $subjects = DB::table('routine_subject_rows as rs')
            ->leftJoin('subjects as s', 's.id', '=', 'rs.subject_id')
            ->where('rs.item_id', $rowId)->orderBy('rs.id')->get(['s.name', 'rs.chapter']);
        foreach ($subjects as $subject) {
            $topics[] = ['name' => $subject->name ?: 'Subject', 'details' => $this->plain($subject->chapter)];
        }
        return $topics;
    }

    private function plain($value): string
    {
        $html = preg_replace('~<br\s*/?>|</p>|</li>|</div>~i', '; ', (string) $value);
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'))) ?: '—';
    }
}
