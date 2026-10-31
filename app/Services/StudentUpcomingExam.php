<?php

namespace App\Services;

use App\Models\Paper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentUpcomingExam
{
    public static function get(int $courseId, ?int $batchId = null, ?int $departmentId = null): ?array
    {
        $now = Carbon::now('Asia/Dhaka');
        // For a purchased-batch dashboard, use the same exact batch/department
        // routine selector as All Exam Paper and Progress. Public/legacy
        // course-only pages retain their previous selection logic.
        if ($batchId !== null && $departmentId !== null) {
            $routine = BatchPublishedRoutineSelector::find($courseId, $batchId, $departmentId);
        } else {
            $query = DB::table('routines')->where('course_id', $courseId)->where('status', 'Published');
            if ($batchId !== null) $query->where('batch_id', $batchId);
            else $query->whereNull('batch_id');
            $routine = $query->orderByDesc('id')->first();
        }
        if (!$routine) return null;

        // Admin Routine has TWO scheduling sources: Main Exams and Revision/Model.
        // Previous implementation excluded derived items, incorrectly reporting
        // "Schedule Pending" while an active Revision/Model remained scheduled.
        $items = DB::table('routine_item_rows')->where('routine_id', $routine->id)
            ->whereNotNull('exam_at')->orderBy('id')->get(['id', 'paper_name', 'exam_at', 'exam_end_at'])
            ->map(function ($row) { $row->row_kind = 'main'; return $row; });
        if (Schema::hasTable('routine_derived_exam_items')) {
            $derived = DB::table('routine_derived_exam_items')->where('routine_id', $routine->id)
                ->whereNotNull('exam_at')->orderBy('id')->get(['id', 'exam_name', 'exam_at', 'exam_end_at'])
                ->map(function ($row) {
                    $row->paper_name = $row->exam_name;
                    $row->row_kind = 'derived';
                    return $row;
                });
            $items = $items->concat($derived);
        }
        // Keep the admin Show Routine Items grouping: Main Exams first,
        // followed by Revision/Model rows. Time checks remain independent.
        $items = $items->values();

        $selected = null;
        $routineStart = null;
        $routineEnd = null;
        // Check Main then Revision/Model as displayed by the Admin Routine.
        // Prefer an active window; if none, select the nearest future start.
        foreach ($items as $item) {
            $begin = Carbon::parse($item->exam_at, 'Asia/Dhaka');
            $end = $item->exam_end_at ? Carbon::parse($item->exam_end_at, 'Asia/Dhaka') : null;
            if (!$end || $end->lessThanOrEqualTo($begin)) continue;
            if ($now->greaterThanOrEqualTo($begin) && $now->lessThan($end)) {
                $selected = $item;
                $routineStart = $begin;
                $routineEnd = $end;
                break;
            }
            if ($now->lessThan($begin) && (!$routineStart || $begin->lessThan($routineStart))) {
                $selected = $item;
                $routineStart = $begin;
                $routineEnd = $end;
            }
        }
        // All main + derived exams are finished; keep their history in
        // All Exam Paper, not in a misleading Upcoming/Live card.
        if (!$selected) return null;
        $isDerived = $selected->row_kind === 'derived';
        $isCompleted = false;

        // Match only an actual paper for the chosen course/batch/department.
        // paper_id is preferred when explicitly linked; never infer a different ID.
        $paperQuery = Paper::query()->where('course_id', $courseId);
        if (!empty($selected->paper_id)) {
            $paperQuery->where('id', (int) $selected->paper_id);
        } else {
            $paperQuery->where('name', $selected->paper_name);
        }
        if ($batchId !== null) $paperQuery->where('batch_id', $batchId);
        else $paperQuery->whereNull('batch_id');
        if ($departmentId !== null) {
            $paperQuery->where(function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId)
                    ->orWhereHas('departments', function ($d) use ($departmentId) {
                        $d->where('departments.id', $departmentId);
                    })
                    ->orWhereIn('permit', ['Batch', 'Every One']);
            });
        }
        $paper = $isDerived ? null : $paperQuery->orderByDesc('id')->first();

        // LEBD-05: routine auto-question exams do not have a legacy papers row.
        // Resolve by the published routine and its exact exam name, never by a
        // similarly named question paper belonging to another batch.
        $autoExamId = null;
        if (!$isDerived && $batchId !== null && $departmentId !== null) {
            $autoExamId = DB::table('routine_exams')
                ->where('routine_id', (int) $routine->id)
                ->where('name', (string) $selected->paper_name)
                ->value('id');
            if ($autoExamId !== null) {
                $hasSaved = DB::table('routine_auto_question_plan_questions')
                    ->where('exam_id', (int) $autoExamId)
                    ->where(function ($q) use ($departmentId) {
                        $q->where('scope', 'common')
                          ->orWhere(function ($q) use ($departmentId) {
                              $q->where('scope', 'department')->where('department_id', $departmentId);
                          });
                    })->exists();
                if (!$hasSaved) $autoExamId = null;
            }
        }


        $topicRows = [];
        if ($isDerived) {
            // Derived exams link Main-Exam syllabi through their own admin
            // source selections; do not invent a routine_item_rows ID match.
            $topicRows[] = [
                'type' => 'Subject', 'name' => 'Revision / Model',
                'details' => 'এই পরীক্ষার সিলেবাস Admin Routine-এ নির্বাচিত Main Exam অনুযায়ী নির্ধারিত।',
            ];
        } else {
            if ($departmentId !== null) {
                $departmentName = DB::table('departments')->where('id', $departmentId)->value('name') ?: 'Department';
                $departmentTopic = DB::table('routine_department_rows')->where('item_id', $selected->id)
                    ->where('department_id', $departmentId)->value('details');
                $topicRows[] = [
                    'type' => 'Department', 'name' => $departmentName,
                    'details' => self::plainText($departmentTopic) ?: 'এই বিভাগের Topic দেওয়া হয়নি।',
                ];
            }
            $subjects = DB::table('routine_subject_rows as rs')
                ->leftJoin('subjects as s', 's.id', '=', 'rs.subject_id')
                ->where('rs.item_id', $selected->id)->orderBy('rs.id')
                ->get(['s.name', 'rs.chapter']);
            foreach ($subjects as $subject) {
                $topicRows[] = [
                    'type' => 'Subject', 'name' => $subject->name ?: 'Subject',
                    'details' => self::plainText($subject->chapter) ?: 'Syllabus দেওয়া হয়নি।',
                ];
            }
        }

        // The ROUTINE controls what the card calls scheduled/live.
        // Paper availability only controls the ability to enter an exam.
        $isLive = $routineEnd !== null && $now->greaterThanOrEqualTo($routineStart) && $now->lessThan($routineEnd);
        $paperAvailable = $paper !== null && in_array($paper->status, ['Scheduled', 'Published'], true);
        $paperOpen = ($paper && $paper->open) ? Carbon::parse($paper->open, 'Asia/Dhaka') : null;
        $paperClose = ($paper && $paper->close) ? Carbon::parse($paper->close, 'Asia/Dhaka') : null;
        $canStart = $isLive && $paperAvailable && (!$paperOpen || $now->greaterThanOrEqualTo($paperOpen))
            && (!$paperClose || $now->lessThan($paperClose));

        // Prefer the existing legacy Paper entry when it is actually valid.
        // Otherwise use the isolated routine-auto-exam flow; its controller
        // checks enrolled batch/department and live time again on every call.
        $autoCanStart = $isLive && $autoExamId !== null;
        $autoTakeUrl = $autoCanStart ? route('students.routine-auto.info', [
            'courseId' => $courseId, 'batchId' => $batchId,
            'examId' => (int) $autoExamId, 'departmentId' => $departmentId,
        ]) : null;

        $refreshAt = $isCompleted ? null : ($now->lessThan($routineStart) ? $routineStart : $routineEnd);
        if (!$isCompleted && $isLive && $paperOpen && $now->lessThan($paperOpen) && $paperOpen->lessThan($refreshAt)) $refreshAt = $paperOpen;
        if (!$isCompleted && $isLive && $paperClose && $now->lessThan($paperClose) && $paperClose->lessThan($refreshAt)) $refreshAt = $paperClose;

        $duration = ($paper && $paper->time) ? (int) $paper->time . ' mins' : 'Not set';
        $marks = ($paper && $paper->max !== null && $paper->mark !== null)
            ? (float) $paper->max * (float) $paper->mark : '—';

        return [
            'exam_no' => $selected->paper_name,
            'exam_at' => $selected->exam_at,
            'exam_end_at' => $selected->exam_end_at,
            'status' => $isCompleted ? 'completed' : ($isLive ? 'live' : 'scheduled'),
            'available_at' => $paperOpen && $paperOpen->greaterThan($routineStart)
                ? $paperOpen->toIso8601String() : $routineStart->toIso8601String(),
            'server_now' => $now->toIso8601String(),
            'duration' => $duration, 'mark' => $marks,
            'topic_rows' => $topicRows,
            'topic' => collect($topicRows)->map(fn ($row) => $row['name'] . ': ' . $row['details'])->implode(' | '),
            'is_live' => $isLive,
            'can_start' => $canStart || $autoCanStart,
            'take_url' => ($canStart && $paper) ? route('students.exam.info', array_filter([
                'paper' => $paper->id, 'department_id' => $departmentId,
            ], static fn ($value) => $value !== null)) : $autoTakeUrl,
            'refresh_at' => $refreshAt ? $refreshAt->toIso8601String() : null,
        ];
    }

    private static function plainText(?string $value): string
    {
        $text = preg_replace('/<br\\s*\\/?\\s*>/i', ' ', (string) $value);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim((string) preg_replace('/\\s+/u', ' ', $text));
    }
}
