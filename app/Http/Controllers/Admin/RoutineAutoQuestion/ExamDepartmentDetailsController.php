<?php

namespace App\Http\Controllers\Admin\RoutineAutoQuestion;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExamDepartmentDetailsController extends Controller
{
    private function context(int $courseId, int $batchId, int $examId): array
    {
        $course = DB::table('courses')->where('id', $courseId)->first();
        abort_unless($course, 404);
        $batch = DB::table('batches')->where('course_id', $courseId)->where('id', $batchId)->first();
        abort_unless($batch, 404);
        $exam = DB::table('routine_exams as e')
            ->join('routines as r', 'r.id', '=', 'e.routine_id')
            ->where('r.course_id', $courseId)->where('r.batch_id', $batchId)
            ->where('r.status', 'Published')->where('e.id', $examId)
            ->select('r.id as routine_id','r.name as routine_name',
                'e.id as exam_id','e.name as exam_name','e.sort_order')->first();
        abort_unless($exam, 404);

        $settings = DB::table('routine_batch_paper_settings')->where('batch_id', $batchId)->first();
        $isDerived = (bool) preg_match('/^(revision|revition|model)/i', trim($exam->exam_name));
        $examRow = $isDerived
            ? DB::table('routine_derived_exam_items')->where('routine_id', $exam->routine_id)->where('exam_name', $exam->exam_name)->first()
            : DB::table('routine_item_rows')->where('routine_id', $exam->routine_id)->where('paper_name', $exam->exam_name)->first();
        $start = $examRow->exam_at ?? null;
        $end = $examRow->exam_end_at ?? null;
        $now = Carbon::now('Asia/Dhaka');
        $status = (!$start || !$end) ? 'Not Scheduled'
            : ($now->lt(Carbon::parse($start, 'Asia/Dhaka')) ? 'Scheduled'
            : ($now->lte(Carbon::parse($end, 'Asia/Dhaka')) ? 'Published' : 'Ended'));

        // Union of Batch membership and explicitly saved Routine department columns.
        $departments = app(\App\Services\RoutineAutoQuestion\RoutineBatchDepartmentSync::class)
            ->departmentsForExam((int) $exam->routine_id, $courseId, $batchId, (string) $exam->exam_name);

        $next = DB::table('routine_exams')->where('routine_id', $exam->routine_id)
            ->where(function ($q) use ($exam) {
                $q->where('sort_order', '>', $exam->sort_order)
                  ->orWhere(function ($q2) use ($exam) {
                      $q2->where('sort_order', '=', $exam->sort_order)
                         ->where('id', '>', $exam->exam_id);
                  });
            })->orderBy('sort_order')->orderBy('id')->first();
        $nextDate = null;
        if ($next) {
            $nextDerived = (bool) preg_match('/^(revision|revition|model)/i', trim($next->name));
            $nextDate = $nextDerived
                ? DB::table('routine_derived_exam_items')->where('routine_id', $exam->routine_id)->where('exam_name', $next->name)->value('exam_at')
                : DB::table('routine_item_rows')->where('routine_id', $exam->routine_id)->where('paper_name', $next->name)->value('exam_at');
        }
        return compact('course','batch','exam','settings','departments','start','end','status','next','nextDate','isDerived');
    }

    public function show(int $courseId, int $batchId, int $examId)
    {
        return view('admin.routine-auto-question.exam-department-details',
            $this->context($courseId, $batchId, $examId));
    }

    public function paper(int $courseId, int $batchId, int $examId, int $departmentId)
    {
        $data = $this->context($courseId, $batchId, $examId);
        $department = $data['departments']->firstWhere('id', $departmentId);
        abort_unless($department, 404);
        // The saved auto-question plan is the source of truth. Do not regenerate/shuffle
        // questions in a read-only paper view.
        $selected = DB::table('routine_auto_question_plan_questions')
            ->where('exam_id', $examId)
            ->where(function ($query) use ($departmentId) {
                $query->where('scope', 'common')
                    ->orWhere(function ($own) use ($departmentId) {
                        $own->where('scope', 'department')->where('department_id', $departmentId);
                    });
            })->get(['question_id', 'scope']);

        $commonIds = [];
        $departmentIds = [];
        foreach ($selected as $row) {
            $id = (int) $row->question_id;
            if ($id < 1) continue;
            if ($row->scope === 'common') $commonIds[$id] = $id;
            else $departmentIds[$id] = $id;
        }
        // A question selected both ways belongs to Common once, not twice.
        $departmentIds = array_diff_key($departmentIds, $commonIds);
        $allIds = array_values($commonIds + $departmentIds);
        $questions = $allIds ? DB::table('questions')->whereIn('id', $allIds)
            ->get(['id', 'title', 'explanation'])->keyBy('id') : collect();
        $options = [];
        $correct = [];
        if ($allIds) {
            foreach (DB::table('mcq_items')->whereIn('question_id', $allIds)
                ->orderBy('id')->get(['question_id', 'item', 'correct_answer']) as $item) {
                $id = (int) $item->question_id;
                $index = count($options[$id] ?? []);
                if ($index >= 5) continue;
                $letter = chr(65 + $index);
                $options[$id][] = ['letter' => $letter, 'text' => $item->item];
                if ($item->correct_answer) $correct[$id] = $letter;
            }
        }
        return view('admin.routine-auto-question.department-paper-preview',
            array_merge($data, compact('department', 'questions', 'commonIds',
                'departmentIds', 'options', 'correct')));
    }
}
