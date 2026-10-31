<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only progress for one purchased batch and one verified department. */
final class BatchRoutineProgress
{
    public static function get(?int $courseId, ?int $batchId, ?int $departmentId): ?array
    {
        if (!$courseId || !$batchId || !$departmentId || !Auth::guard('student')->check()) {
            return null;
        }
        $student = Auth::guard('student')->user();
        if (!$student->batches()->where('batches.id', $batchId)->exists()
            || !$student->departments()->where('departments.id', $departmentId)->exists()) {
            return null;
        }
        if (!Schema::hasTable('orders') || !Schema::hasTable('routines')
            || !Schema::hasTable('routine_item_rows') || !Schema::hasTable('routine_exams')
            || !Schema::hasColumn('orders', 'department_id') || !Schema::hasColumn('orders', 'status')) {
            return null;
        }
        $order = DB::table('orders')->where('student_id', $student->id)
            ->where('batch_id', $batchId)->where('department_id', $departmentId)
            ->whereIn('status', ['Processing', 'Complete'])->exists();
        if (!$order) {
            return null;
        }

        // Identical published batch/department selection across dashboard, routine and list.
        $routine = BatchPublishedRoutineSelector::find($courseId, $batchId, $departmentId);
        if (!$routine) {
            return ['total_exams' => 0, 'completed_exams' => 0];
        }

        $mainRows = DB::table('routine_item_rows')->where('routine_id', $routine->id)
            ->get(['paper_name']);
        $total = $mainRows->count();
        if (Schema::hasTable('routine_derived_exam_items')) {
            $total += DB::table('routine_derived_exam_items')
                ->where('routine_id', $routine->id)->count();
        }

        $completed = 0;
        if (Schema::hasTable('lebd_routine_auto_attempts')) {
            $submittedIds = DB::table('lebd_routine_auto_attempts as a')
                ->join('routine_exams as e', 'e.id', '=', 'a.routine_exam_id')
                ->where('e.routine_id', $routine->id)
                ->where('a.student_id', $student->id)
                ->where('a.department_id', $departmentId)
                ->where('a.status', 'Completed')
                ->whereNotNull('a.submitted_at')
                ->pluck('e.name')->map(fn ($name) => trim((string) $name))->all();
            $submitted = array_fill_keys($submittedIds, true);
            foreach ($mainRows as $row) {
                if (isset($submitted[trim((string) $row->paper_name)])) {
                    $completed++;
                }
            }
        }

        return [
            'total_exams' => $total,
            'completed_exams' => min($completed, $total),
        ];
    }
}
