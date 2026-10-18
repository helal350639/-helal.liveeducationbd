<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * LEBD: student-visible exam schedule for today, from published routines.
 * Read-only. Never creates Exam attempts or mutates any tables.
 */
class StudentDashboardTodayExams
{
    public static function forStudent($student): array
    {
        if (!$student || !Schema::hasTable('routines') || !Schema::hasTable('routine_item_rows')
            || !Schema::hasTable('papers')) {
            return [];
        }

        $now = Carbon::now('Asia/Dhaka');
        $dayStart = $now->copy()->startOfDay();
        $dayEnd = $now->copy()->endOfDay();
        $batchIds = $student->batches()->pluck('batches.id')->map(fn ($id) => (int) $id)->all();
        $departmentIds = $student->departments()->pluck('departments.id')->map(fn ($id) => (int) $id)->all();
        $groupIds = method_exists($student, 'groups')
            ? $student->groups()->pluck('groups.id')->map(fn ($id) => (int) $id)->all() : [];

        $hasEndColumn = Schema::hasColumn('routine_item_rows', 'exam_end_at');
        $query = DB::table('routines as r')
            ->join('routine_item_rows as i', 'i.routine_id', '=', 'r.id')
            ->where('r.status', 'Published')
            ->where('i.exam_at', '<=', $dayEnd->toDateTimeString())
            ->where('i.exam_at', '>=', $dayStart->copy()->subDays(30)->toDateTimeString())
            ->select(
                'r.course_id', 'r.batch_id', 'r.department_id',
                'i.id as routine_item_id', 'i.paper_name', 'i.exam_at'
            );
        $query->addSelect($hasEndColumn ? 'i.exam_end_at' : DB::raw('NULL as exam_end_at'));
        // Every One papers remain public; filter strictly below before presenting any row.
        $items = $query->orderBy('i.exam_at')->get();
        if ($items->isEmpty()) return [];

        $papers = DB::table('papers')
            ->whereIn('status', ['Published', 'Scheduled'])
            ->get(['id','name','course_id','batch_id','department_id','group_id',
                   'permit','status','close','time']);
        $names = DB::table('batches')->pluck('name', 'id');
        $counts = Schema::hasTable('paper_question')
            ? DB::table('paper_question')->select('paper_id')
                ->selectRaw('COUNT(DISTINCT question_id) AS total')
                ->groupBy('paper_id')->pluck('total', 'paper_id') : collect();

        $completedPaperIds = Schema::hasTable('exams')
            ? DB::table('exams')->where('student_id', $student->id)
                ->where('status', 'Completed')->distinct()->pluck('paper_id')->map(fn ($v)=>(int)$v)->all()
            : [];

        $result = [];
        $seen = [];
        foreach ($items as $item) {
            // Several departments can have different Papers with the same title.
            // Select a paper the current student is actually allowed to view.
            $paper = $papers->first(function ($p) use ($item, $batchIds, $departmentIds, $groupIds) {
                if ((int)$p->course_id !== (int)$item->course_id
                    || (int)$p->batch_id !== (int)$item->batch_id
                    || trim((string)$p->name) !== trim((string)$item->paper_name))
                    return false;
                if ($item->department_id !== null && (int)$item->department_id !== 0
                    && (int)$p->department_id !== (int)$item->department_id)
                    return false;
                $inBatch = in_array((int)$p->batch_id, $batchIds, true);
                $inDepartment = in_array((int)$p->department_id, $departmentIds, true);
                $inGroup = in_array((int)$p->group_id, $groupIds, true);
                return match (trim((string)$p->permit)) {
                    'Every One' => true,
                    'Batch' => $inBatch,
                    'Department' => $inBatch && $inDepartment,
                    'Group' => $inBatch && $inGroup,
                    default => false,
                };
            });
            if (!$paper || isset($seen[$paper->id])) continue;

            $start = Carbon::parse($item->exam_at, 'Asia/Dhaka');
            $endValue = $item->exam_end_at ?: $paper->close;
            $end = $endValue ? Carbon::parse($endValue, 'Asia/Dhaka') : null;
            if ($start->gt($dayEnd) || ($start->lt($dayStart) && (!$end || $end->lt($dayStart)))) continue;

            $isPublished = $paper->status === 'Published';
            $status = $now->lt($start) ? 'upcoming' : ($end && $now->lt($end) ? 'live' : 'ended');
            $hasCompleted = in_array((int)$paper->id, $completedPaperIds, true);
            $resultUrl = $hasCompleted ? route('students.result', $paper->id) : null;
            $infoUrl = route('students.exam.info', array_filter([
                'paper' => (int)$paper->id,
                'department_id' => $paper->department_id ? (int)$paper->department_id : null,
            ], static fn ($value) => $value !== null));
            $canOpen = $isPublished && $end && $end->greaterThan($start);
            $result[] = [
                'id' => (int)$paper->id,
                'batch' => $names[$paper->batch_id] ?? ('Batch #' . $paper->batch_id),
                'name' => (string)$paper->name,
                'start_ms' => $start->getTimestamp() * 1000,
                'end_ms' => $end?->getTimestamp() * 1000,
                'start_label' => $start->format('h:i A'),
                'end_label' => $end ? $end->format('h:i A') : 'শেষ সময় নির্ধারিত নয়',
                'question_count' => $counts->has($paper->id) ? (int)$counts[$paper->id] : null,
                'status' => $status,
                'status_label' => $status === 'upcoming' ? 'আসন্ন' : ($status === 'live' && $isPublished ? 'লাইভ চলছে' : 'সময়সীমা শেষ'),
                'timer_label' => $status === 'upcoming' ? 'শুরু হতে বাকি' : ($status === 'live' ? 'সময় বাকি' : 'অবস্থা'),
                'timer_value' => $status === 'ended' ? 'সময় শেষ' : '00:00:00',
                'can_open' => $canOpen,
                'info_url' => ($canOpen && $status !== 'ended') ? $infoUrl : null,
                'result_url' => $resultUrl,
            ];
            $seen[$paper->id] = true;
        }
        return $result;
    }
}
