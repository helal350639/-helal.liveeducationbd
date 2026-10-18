<?php

namespace App\Services;

use App\Models\Paper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentExamPaperInfo
{
    public static function get(
        int $paperId,
        ?int $departmentId = null
    ): array {

        /*
        |--------------------------------------------------------------------------
        | 1. GET QUESTION PAPER
        |--------------------------------------------------------------------------
        */

        $paper = Paper::findOrFail($paperId);

        $now = Carbon::now('Asia/Dhaka');

        /*
        |--------------------------------------------------------------------------
        | 2. COUNT ACTUAL QUESTIONS
        |--------------------------------------------------------------------------
        | Only questions attached to this Question Paper.
        | Each Question ID is counted once.
        |--------------------------------------------------------------------------
        */

        $questionIds = DB::table('paper_question as pq')
            ->join(
                'questions as q',
                'q.id',
                '=',
                'pq.question_id'
            )
            ->where('pq.paper_id', $paper->id)
            ->distinct()
            ->pluck('q.id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $totalQuestions = $questionIds->count();

        /*
        |--------------------------------------------------------------------------
        | 3. SUBJECT / DEPARTMENT QUESTION COUNT
        |--------------------------------------------------------------------------
        */

        $counts = [];

        if ($questionIds->isNotEmpty()) {

            // Subject mapping of the Paper's questions
            $subjectTags = DB::table('question_subject as qs')
                ->join(
                    'subjects as s',
                    's.id',
                    '=',
                    'qs.subject_id'
                )
                ->whereIn(
                    'qs.question_id',
                    $questionIds->all()
                )
                ->orderBy('s.id')
                ->get([
                    'qs.question_id',
                    's.id as group_id',
                    's.name as group_name'
                ])
                ->groupBy('question_id');

            // Department mapping of the Paper's questions
            $departmentTags = DB::table(
                'department_question as dq'
            )
                ->join(
                    'departments as d',
                    'd.id',
                    '=',
                    'dq.department_id'
                )
                ->whereIn(
                    'dq.question_id',
                    $questionIds->all()
                )
                ->orderBy('d.id')
                ->get([
                    'dq.question_id',
                    'd.id as group_id',
                    'd.name as group_name'
                ])
                ->groupBy('question_id');

            /*
            | Every question belongs to one display group.
            | Priority: Subject > Department > Other.
            | Prevent double counting.
            */

            foreach ($questionIds as $questionId) {

                $subject = $subjectTags
                    ->get($questionId)?->first();

                $departmentOptions = $departmentTags
                    ->get($questionId);

                $department = null;

                if (
                    $departmentOptions &&
                    $departmentOptions->isNotEmpty()
                ) {

                    $department = $departmentId !== null
                        ? (
                            $departmentOptions->firstWhere(
                                'group_id',
                                $departmentId
                            )
                            ?? $departmentOptions->first()
                        )
                        : $departmentOptions->first();
                }

                if ($subject) {

                    $key = 'subject:' . $subject->group_id;

                    $name = $subject->group_name;

                } elseif ($department) {

                    $key = 'department:' . $department->group_id;

                    $name = $department->group_name;

                } else {

                    $key = 'other';

                    $name = 'অন্যান্য';
                }

                if (!isset($counts[$key])) {

                    $counts[$key] = [
                        'name' => $name,
                        'count' => 0
                    ];
                }

                $counts[$key]['count']++;
            }
        }

        $questionBreakdown = array_values($counts);

        /*
        |--------------------------------------------------------------------------
        | 4. GET MATCHING ROUTINE ITEM
        |--------------------------------------------------------------------------
        | Same Course + Batch + Exam No.
        |--------------------------------------------------------------------------
        */

        $routineQuery = DB::table('routines as r')
            ->join(
                'routine_item_rows as ri',
                'ri.routine_id',
                '=',
                'r.id'
            )
            ->where(
                'r.course_id',
                $paper->course_id
            )
            ->where(
                'r.status',
                'Published'
            )
            ->where(
                'ri.paper_name',
                $paper->name
            );

        if ($paper->batch_id !== null) {

            $routineQuery->where(
                'r.batch_id',
                $paper->batch_id
            );

        } else {

            $routineQuery->whereNull('r.batch_id');
        }

        $selectedDepartment = $departmentId
            ?? $paper->department_id;

        if ($selectedDepartment !== null) {

            $routineQuery->where(
                function ($query) use ($selectedDepartment) {

                    $query
                        ->whereNull('r.department_id')
                        ->orWhere(
                            'r.department_id',
                            $selectedDepartment
                        )
                        ->orWhereExists(
                            function ($sub) use ($selectedDepartment) {

                                $sub->selectRaw('1')
                                    ->from(
                                        'routine_department_columns as dc'
                                    )
                                    ->whereColumn(
                                        'dc.routine_id',
                                        'r.id'
                                    )
                                    ->where(
                                        'dc.department_id',
                                        $selectedDepartment
                                    );
                            }
                        );
                }
            );
        }

        $routineItem = $routineQuery
            ->orderByDesc('r.id')
            ->orderBy('ri.exam_at')
            ->first([
                'ri.exam_at',
                'ri.exam_end_at'
            ]);

        /*
        |--------------------------------------------------------------------------
        | 5. QUESTION PAPER DATE & TIME
        |--------------------------------------------------------------------------
        | Display actual Question Paper time.
        |--------------------------------------------------------------------------
        */

        $paperStart = $paper->open
            ? Carbon::parse(
                $paper->open,
                'Asia/Dhaka'
            )
            : null;

        $paperEnd = $paper->close
            ? Carbon::parse(
                $paper->close,
                'Asia/Dhaka'
            )
            : null;

        /*
        |--------------------------------------------------------------------------
        | 6. EXAM ACCESS TIME
        |--------------------------------------------------------------------------
        | Current LEBD rule:
        | Routine start to Routine end.
        | Paper Close restriction will be added later.
        |--------------------------------------------------------------------------
        */

        $accessStart = $routineItem?->exam_at
            ? Carbon::parse(
                $routineItem->exam_at,
                'Asia/Dhaka'
            )
            : $paperStart;

        $accessEnd = $routineItem?->exam_end_at
            ? Carbon::parse(
                $routineItem->exam_end_at,
                'Asia/Dhaka'
            )
            : $paperEnd;

        /*
        |--------------------------------------------------------------------------
        | 7. EXAM STATUS
        |--------------------------------------------------------------------------
        */

        $published = in_array(
            $paper->status,
            ['Published', 'Scheduled'],
            true
        );

        $isLive =
            $published &&
            $accessStart !== null &&
            $accessEnd !== null &&
            $now->greaterThanOrEqualTo($accessStart) &&
            $now->lessThan($accessEnd);

        if (
            $accessEnd !== null &&
            $now->greaterThanOrEqualTo($accessEnd)
        ) {

            $examStatus = 'Completed';

        } elseif ($isLive) {

            $examStatus = 'Live Now';

        } else {

            $examStatus = 'Upcoming';
        }

        /*
        |--------------------------------------------------------------------------
        | 8. STUDENT ACCESS
        |--------------------------------------------------------------------------
        | Keep current Student Exam permission rules.
        |--------------------------------------------------------------------------
        */

        $student = Auth::guard('student')->user();

        $hasAccess = false;

        if ($student) {

            if ($paper->permit === 'Every One') {

                $hasAccess = true;

            } else {

                $hasBatch =
                    $paper->batch_id !== null &&
                    $student->batches()
                        ->where(
                            'batches.id',
                            $paper->batch_id
                        )
                        ->exists();

                $hasDepartment =
                    $paper->department_id !== null &&
                    $student->departments()
                        ->where(
                            'departments.id',
                            $paper->department_id
                        )
                        ->exists();

                $hasAccess = $hasBatch && $hasDepartment;
            }
        }

        /*
|--------------------------------------------------------------------------
| 9. TAKE EXAM BUTTON
|--------------------------------------------------------------------------
*/

// Current testing rule
$canStartExam = $isLive;

// Generate existing Student Exam link
$takeExamUrl = $canStartExam
    ? route(
        'students.exam.start-routine',
        ['id' => $paper->id]
    )
    : null;

        /*
        |--------------------------------------------------------------------------
        | 10. RESULT PERMISSION
        |--------------------------------------------------------------------------
        */

        $resultTimeReached = $paper->result_at
            ? $now->greaterThanOrEqualTo(
                Carbon::parse(
                    $paper->result_at,
                    'Asia/Dhaka'
                )
            )
            : true;

        $canViewResult =
            $student !== null &&
            $hasAccess &&
            $paper->result_view === 'Yes' &&
            $accessEnd !== null &&
            $now->greaterThanOrEqualTo($accessEnd) &&
            $resultTimeReached;

        $resultUrl = $canViewResult
            ? route(
                'students.result',
                ['id' => $paper->id]
            )
            : null;

        /*
        |--------------------------------------------------------------------------
        | 11. RETURN DATA TO EXAM INFO BLADE
        |--------------------------------------------------------------------------
        */

        return [

            'paper' => $paper,

            'examStartAt' => (
                $paperStart ?? $accessStart
            )?->toIso8601String(),

            'examEndAt' => (
                $paperEnd ?? $accessEnd
            )?->toIso8601String(),

            'routineStartAt' => $accessStart
                ? $accessStart->toIso8601String()
                : null,

            'routineEndAt' => $accessEnd
                ? $accessEnd->toIso8601String()
                : null,

            'totalQuestions' => $totalQuestions,

            'questionBreakdown' => $questionBreakdown,

            'instructions' => $paper->details ?? '',

            'examStatus' => $examStatus,

            'canStartExam' => $canStartExam,

            'takeExamUrl' => $takeExamUrl,

            'canViewResult' => $canViewResult,

            'resultUrl' => $resultUrl,

        ];
    }
}