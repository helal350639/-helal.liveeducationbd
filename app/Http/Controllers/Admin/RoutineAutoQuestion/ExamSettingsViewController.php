<?php

namespace App\Http\Controllers\Admin\RoutineAutoQuestion;

use App\Http\Controllers\Controller;
use App\Services\RoutineAutoQuestion\BatchPaperPreviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamSettingsViewController extends Controller
{
    public function show(
        Request $request,
        int $courseId,
        int $batchId,
        int $examId,
        BatchPaperPreviewService $preview
    ) {
        $course = DB::table('courses')->where('id', $courseId)->first();
        abort_unless($course, 404);

        // Read-only history stays accessible even if a batch has now expired.
        $batch = DB::table('batches')
            ->where('id', $batchId)->where('course_id', $courseId)->first();
        abort_unless($batch, 404);

        $exam = $preview->exams($courseId, $batchId)
            ->first(fn ($e) => (int) $e->exam_id === $examId);
        abort_unless($exam, 404, 'Exam does not belong to this course and batch.');

        $settings = DB::table('routine_batch_paper_settings')
            ->where('batch_id', $batchId)->first();

        $department = null;
        if ($request->filled('department')) {
            $department = DB::table('batch_department as bd')
                ->join('departments as d', 'd.id', '=', 'bd.department_id')
                ->where('bd.batch_id', $batchId)
                ->where('d.id', (int) $request->query('department'))
                ->select('d.id', 'd.name')->first();
            abort_unless($department, 404);
        }

        // Same defaults as the Batch Settings edit page, but clearly marked unsaved.
        $effective = $settings ?: (object) [
            'question_limit' => 100, 'mcq_format' => 'bn',
            'running_minutes' => 40, 'correct_mark' => 1,
            'negative_mark' => 0, 'attempt_limit' => '1',
            'random_questions' => 0, 'question_display' => 'all',
            'paid_exam' => 0, 'email_result' => 0,
            'student_view_result' => 0, 'header_extra' => '',
            'details_extra' => '', 'banner_path' => null,
        ];

        $info = $preview->preview($exam, $effective);

        return view('admin.routine-auto-question.exam-settings-view',
            compact('course', 'batch', 'exam', 'settings', 'effective', 'info', 'department'));
    }
}
