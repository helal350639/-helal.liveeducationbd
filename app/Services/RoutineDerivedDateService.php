<?php

namespace App\Services;

use App\Models\Routine;
use Illuminate\Support\Facades\DB;

/**
 * Recalculates PDF/question creation timestamps of Revision and Model exams.
 * Uses the immediately preceding configured RoutineExam name in sort_order.
 * Does not modify Main Exam dates, syllabus, questions or source links.
 */
class RoutineDerivedDateService
{
    public function recalculate(int $routineId, bool $apply = true): array
    {
        $routine = Routine::findOrFail($routineId);
        $sequence = $routine->exams()->orderBy('sort_order')->orderBy('id')->get();
        $mains = DB::table('routine_item_rows')->where('routine_id', $routineId)
            ->get(['paper_name', 'exam_end_at'])->keyBy('paper_name');
        $derived = DB::table('routine_derived_exam_items')->where('routine_id', $routineId)
            ->get(['id', 'exam_name', 'exam_end_at', 'create_pdf_at', 'question_create_at'])
            ->keyBy('exam_name');

        $changes = [];
        $previousEnd = null;
        foreach ($sequence as $index => $exam) {
            $name = $exam->name;
            $main = $mains->get($name);
            $item = $derived->get($name);
            $target = $index === 0 ? $routine->created_at : $previousEnd;
            $target = $target ? (string) $target : null;

            if ($item) {
                $pdf = $item->create_pdf_at ? (string) $item->create_pdf_at : null;
                $question = $item->question_create_at ? (string) $item->question_create_at : null;
                if ($pdf !== $target || $question !== $target) {
                    $changes[] = [
                        'id' => (int) $item->id,
                        'exam_name' => $name,
                        'before_pdf' => $pdf,
                        'before_question' => $question,
                        'after' => $target,
                    ];
                    if ($apply) {
                        DB::table('routine_derived_exam_items')->where('id', $item->id)->update([
                            'create_pdf_at' => $target,
                            'question_create_at' => $target,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            // If the predecessor was not yet created, retain null until it exists.
            $previousEnd = $main ? $main->exam_end_at : ($item ? $item->exam_end_at : null);
        }
        return $changes;
    }
}
