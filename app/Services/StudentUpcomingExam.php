<?php

namespace App\Services;

use App\Models\Paper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StudentUpcomingExam
{
    public static function get(
        int $courseId,
        ?int $batchId = null,
        ?int $departmentId = null
    ): ?array {

        $now = Carbon::now('Asia/Dhaka');

        /*
        |--------------------------------------------------------------------------
        | 1. FIND PUBLISHED ROUTINE
        |--------------------------------------------------------------------------
        */

        $query = DB::table('routines')
            ->where('course_id', $courseId)
            ->where('status', 'Published');

        if ($batchId !== null) {
            $query->where('batch_id', $batchId);
        } else {
            $query->whereNull('batch_id');
        }

        if ($departmentId !== null) {

            $query->where(function ($q) use ($departmentId) {

                $q->whereNull('department_id')
                    ->orWhere('department_id', $departmentId)
                    ->orWhereExists(function ($sub) use ($departmentId) {

                        $sub->selectRaw('1')
                            ->from('routine_department_columns as dc')
                            ->whereColumn(
                                'dc.routine_id',
                                'routines.id'
                            )
                            ->where('dc.department_id', $departmentId);
                    });
            });
        }

        $routine = $query
            ->orderByDesc('id')
            ->first();

        if (!$routine) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. FIND CURRENT OR NEXT EXAM
        |--------------------------------------------------------------------------
        | Next card ONLY after Routine End Time
        |--------------------------------------------------------------------------
        */

        $items = DB::table('routine_item_rows')
            ->where('routine_id', $routine->id)
            ->whereNotNull('exam_at')
            ->orderBy('exam_at')
            ->orderBy('id')
            ->get();

        $selected = null;
        $selectedPaper = null;
        $startAt = null;
        $endAt = null;

        foreach ($items as $item) {

            $routineStart = Carbon::parse(
                $item->exam_at,
                'Asia/Dhaka'
            );

            $routineEnd = $item->exam_end_at
                ? Carbon::parse(
                    $item->exam_end_at,
                    'Asia/Dhaka'
                )
                : null;

            // Move to next exam only after Routine End
            if (
                $routineEnd !== null &&
                $now->greaterThanOrEqualTo($routineEnd)
            ) {
                continue;
            }

            // Avoid showing an already-started
            // exam indefinitely without End Time
            if (
                $routineEnd === null &&
                $now->greaterThanOrEqualTo($routineStart)
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | MATCH QUESTION PAPER
            |--------------------------------------------------------------------------
            */

            $paperQuery = Paper::query()
                ->where('course_id', $courseId)
                ->where('name', $item->paper_name);

            if ($batchId !== null) {
                $paperQuery->where('batch_id', $batchId);
            } else {
                $paperQuery->whereNull('batch_id');
            }

            if ($departmentId !== null) {

                $paperQuery->where(function ($q) use ($departmentId) {

                    $q->where(
                        'department_id',
                        $departmentId
                    )
                    ->orWhereHas(
                        'departments',
                        function ($d) use ($departmentId) {

                            $d->where(
                                'departments.id',
                                $departmentId
                            );
                        }
                    )
                    ->orWhereIn(
                        'permit',
                        ['Batch', 'Every One']
                    );
                });
            }

            $paper = $paperQuery
                ->orderByDesc('id')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | EXAM AVAILABLE TIME
            |--------------------------------------------------------------------------
            | Use the later of Routine Start and Paper Open
            |--------------------------------------------------------------------------
            */

            $start = $routineStart->copy();

            if ($paper && $paper->open) {

                $paperStart = Carbon::parse(
                    $paper->open,
                    'Asia/Dhaka'
                );

                if ($paperStart->greaterThan($start)) {
                    $start = $paperStart;
                }
            }

            $selected = $item;
            $selectedPaper = $paper;
            $startAt = $start;
            $endAt = $routineEnd;

            break;
        }

        if (!$selected) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. DEPARTMENT TOPIC - FIRST ROW
        |--------------------------------------------------------------------------
        */

        $topicRows = [];

        if ($departmentId !== null) {

            $departmentName = DB::table('departments')
                ->where('id', $departmentId)
                ->value('name') ?: 'Department';

            $departmentTopic = DB::table('routine_department_rows')
                ->where('item_id', $selected->id)
                ->where('department_id', $departmentId)
                ->value('details');

            $topicRows[] = [
                'type' => 'Department',
                'name' => $departmentName,
                'details' => self::plainText($departmentTopic)
                    ?: 'এই বিভাগের Topic দেওয়া হয়নি।',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 4. SUBJECT & SYLLABUS ROWS
        |--------------------------------------------------------------------------
        */

        $subjects = DB::table('routine_subject_rows as rs')
            ->leftJoin(
                'subjects as s',
                's.id',
                '=',
                'rs.subject_id'
            )
            ->where('rs.item_id', $selected->id)
            ->orderBy('rs.id')
            ->get([
                's.name',
                'rs.chapter'
            ]);

        foreach ($subjects as $subject) {

            $topicRows[] = [
                'type' => 'Subject',
                'name' => $subject->name ?: 'Subject',
                'details' => self::plainText($subject->chapter)
                    ?: 'Syllabus দেওয়া হয়নি।',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 5. LIVE STATUS & EXAM PERMISSION
        |--------------------------------------------------------------------------
        */

        $paper = $selectedPaper;

        $paperAvailable = $paper !== null
            && in_array(
                $paper->status,
                ['Scheduled', 'Published'],
                true
            );

        $isLive = $paperAvailable
            && $startAt !== null
            && $endAt !== null
            && $now->greaterThanOrEqualTo($startAt)
            && $now->lessThan($endAt);

        // Paper Close controls exam access,
        // but NEVER changes the Routine Card.
        $paperClose = ($paper && $paper->close)
            ? Carbon::parse(
                $paper->close,
                'Asia/Dhaka'
            )
            : null;

        /*
|--------------------------------------------------------------------------
| TAKE EXAM PERMISSION - CURRENT TESTING RULE
|--------------------------------------------------------------------------
| Routine Start Time reached + Live Exam
| Ignore Paper Close Time for now
|--------------------------------------------------------------------------
*/

$canStart = $isLive;

        /*
        |--------------------------------------------------------------------------
        | 6. AUTO REFRESH AT NEXT TRANSITION
        |--------------------------------------------------------------------------
        */

        $refreshAt = $endAt;

        // Refresh when exam becomes live
        if (
            $startAt !== null &&
            $now->lessThan($startAt)
        ) {

            if (
                $refreshAt === null ||
                $startAt->lessThan($refreshAt)
            ) {
                $refreshAt = $startAt;
            }
        }

        // Refresh when Question Paper closes
        if (
            $paperClose !== null &&
            $now->lessThan($paperClose)
        ) {

            if (
                $refreshAt === null ||
                $paperClose->lessThan($refreshAt)
            ) {
                $refreshAt = $paperClose;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 7. QUESTION PAPER DETAILS
        |--------------------------------------------------------------------------
        */

        $duration = ($paper && $paper->time)
            ? (int) $paper->time . ' mins'
            : 'Not set';

        $marks = (
            $paper &&
            $paper->max !== null &&
            $paper->mark !== null
        )
            ? (float) $paper->max
                * (float) $paper->mark
            : '—';

        /*
        |--------------------------------------------------------------------------
        | 8. RETURN DATA
        |--------------------------------------------------------------------------
        */

        return [
            'exam_no' => $selected->paper_name,

            'exam_at' => $selected->exam_at,

            'exam_end_at' => $selected->exam_end_at,

            // NEW: Used by Upcoming Exam Notice
            'available_at' => $startAt
                ? $startAt->toIso8601String()
                : null,

            // NEW: Server clock reference
            'server_now' => $now->toIso8601String(),

            'duration' => $duration,

            'mark' => $marks,

            'topic_rows' => $topicRows,

            'topic' => collect($topicRows)
                ->map(
                    fn ($row) =>
                        $row['name'] . ': ' . $row['details']
                )
                ->implode(' | '),

            'is_live' => $isLive,

            // NEW: Actual exam entry permission
            'can_start' => $canStart,

            'take_url' => ($canStart && $paper)
    ? route(
        'students.exam.info',
        array_filter(
            [
                'paper' => $paper->id,
                'department_id' => $departmentId,
            ],
            static fn ($value) => $value !== null
        )
    )
    : null,

            'refresh_at' => $refreshAt
                ? $refreshAt->toIso8601String()
                : null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAN TOPIC TEXT
    |--------------------------------------------------------------------------
    */

    private static function plainText(?string $value): string
    {
        $text = preg_replace(
            '/<br\s*\/?\s*>/i',
            ' ',
            (string) $value
        );

        $text = html_entity_decode(
            strip_tags($text),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        return trim(
            (string) preg_replace('/\s+/u', ' ', $text)
        );
    }
}