<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

/**
 * Read-only admin exam activity reporting.
 * Counts originate from LEBD tables, never from demo fixtures.
 * "Live" means an attempt's individual time has NOT elapsed;
 * it does not prove the student's browser is currently online.
 */
class AdminExamActivityDashboard
{
    public static function build(): array
    {
        $now = Carbon::now('Asia/Dhaka');
        $start = $now->copy()->startOfDay();
        $end = $now->copy()->endOfDay();
        $weekStart = $now->copy()->subDays(6)->startOfDay();
        $today = $now->toDateString();

        // Question paper metadata: do not assume an undeployed pass_mark column.
        $hasPassMark = Schema::hasColumn('papers', 'pass_mark');
        $paperCols = ['id', 'course_id', 'batch_id', 'department_id', 'name',
            'status', 'time', 'mark', 'minus', 'open', 'close', 'result_view', 'result_at'];
        if ($hasPassMark) {
            $paperCols[] = 'pass_mark';
        }
        $papers = DB::table('papers')->select($paperCols)->get()->keyBy('id');
        $questionCounts = DB::table('paper_question')
            ->select('paper_id')->selectRaw('COUNT(DISTINCT question_id) as count')
            ->groupBy('paper_id')->pluck('count', 'paper_id');
        $batchNames = DB::table('batches')->pluck('name', 'id');

        // Today's published routine rows are the source of the schedule.
        $hasEnd = Schema::hasColumn('routine_item_rows', 'exam_end_at');
        $scheduleQuery = DB::table('routines as r')
            ->join('routine_item_rows as i', 'i.routine_id', '=', 'r.id')
            ->where('r.status', 'Published')
            // Include exams which started earlier but whose published window may cross today.
            ->where('i.exam_at', '<=', $end->toDateTimeString())
            ->where('i.exam_at', '>=', $now->copy()->subDays(30)->startOfDay()->toDateTimeString())
            ->select('r.id as routine_id', 'r.course_id', 'r.batch_id',
                'r.department_id', 'r.name as routine_name', 'i.id as item_id',
                'i.paper_name', 'i.exam_at');
        $scheduleQuery->addSelect($hasEnd ? 'i.exam_end_at' : DB::raw('NULL as exam_end_at'));
        $scheduled = $scheduleQuery->orderBy('i.exam_at')->get();

        $rows = [];
        $todayPaperIds = [];
        foreach ($scheduled as $item) {
            $matching = $papers->filter(function ($p) use ($item) {
                return (int) $p->course_id === (int) $item->course_id
                    && (int) $p->batch_id === (int) $item->batch_id
                    && trim((string) $p->name) === trim((string) $item->paper_name);
            });
            $ids = $matching->keys()->map(fn ($v) => (int) $v)->all();
            foreach ($ids as $pid) {
                $todayPaperIds[$pid] = true;
            }

            $startAt = $item->exam_at ? Carbon::parse($item->exam_at, 'Asia/Dhaka') : null;
            $endValue = $item->exam_end_at;
            if (!$endValue && $matching->isNotEmpty()) {
                // Legacy database may lack a routine end: only use explicitly saved paper.close.
                $endValue = $matching->first()->close;
            }
            $endAt = $endValue ? Carbon::parse($endValue, 'Asia/Dhaka') : null;
            // Only show exams with a schedule window intersecting today.
            // Older schemas with no saved End can only verify today's start.
            if (!$startAt || ($startAt->lt($start) && (!$endAt || $endAt->lt($start)))) {
                continue;
            }

            $status = 'Unknown';
            if ($startAt) {
                if ($now->lt($startAt)) {
                    $status = 'Upcoming';
                } elseif (!$endAt) {
                    $status = 'End Not Set';
                } elseif ($now->lt($endAt)) {
                    $status = 'Live';
                } else {
                    $status = 'Completed'; // schedule window ended, not submission count
                }
            }
            $questionCount = $matching->sum(fn ($p) => (int) ($questionCounts[$p->id] ?? 0));
            $rows[] = [
                'id' => (int) $item->item_id,
                'batch' => $batchNames[$item->batch_id] ?? ('Batch #' . $item->batch_id),
                'batch_id' => (int) $item->batch_id,
                'paper_name' => $item->paper_name ?: 'Untitled Exam',
                'start' => $startAt ? $startAt->format('h:i A') : '—',
                'end' => $endAt ? $endAt->format('h:i A') : '—',
                'status' => $status,
                'paper_ids' => $ids,
                'paper_id' => $matching->first()->id ?? null,
                'questions' => $questionCount,
                'needs_attention' => empty($ids) || $questionCount === 0 || !$endAt,
            ];
        }

        $dailyAttempts = DB::table('exams')
            ->whereBetween('start_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->get(['id', 'student_id', 'paper_id', 'start_at', 'status']);
        $completedToday = DB::table('exams')
            ->where('status', 'Completed')
            ->whereBetween('end_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->count();

        $recentLive = DB::table('exams as e')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->join('papers as p', 'p.id', '=', 'e.paper_id')
            ->leftJoin('batches as b', 'b.id', '=', 'p.batch_id')
            ->where('e.status', 'Live')
            ->where('e.start_at', '>=', $now->copy()->subDays(7)->toDateTimeString())
            ->select('e.id', 'e.student_id', 'e.paper_id', 'e.start_at',
                's.name as student_name', 'p.name as exam_name', 'p.time',
                'p.batch_id', 'b.name as batch_name')
            ->orderByDesc('e.id')->get();
        $liveAttempts = $recentLive->filter(function ($a) use ($now) {
            return (int) $a->time > 0 && Carbon::parse($a->start_at, 'Asia/Dhaka')
                ->addMinutes((int) $a->time)->gt($now);
        });

        // One student is counted once per batch; repeated attempts do not inflate participation.
        $attemptsWeek = DB::table('exams')
            ->where('start_at', '>=', $weekStart->toDateTimeString())
            ->get(['id', 'student_id', 'paper_id', 'start_at']);
        $attemptsByBatch = [];
        $attemptsByHour = array_fill(0, 24, 0);
        $dayTrend = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $dayTrend[$day->toDateString()] = 0;
        }
        foreach ($attemptsWeek as $attempt) {
            $paper = $papers[$attempt->paper_id] ?? null;
            if ($paper && $paper->batch_id) {
                $attemptsByBatch[(int) $paper->batch_id][(int) $attempt->student_id] = true;
            }
            if ($attempt->start_at) {
                $date = Carbon::parse($attempt->start_at, 'Asia/Dhaka')->toDateString();
                if (isset($dayTrend[$date])) {
                    $dayTrend[$date]++;
                }
                if ($date === $today) {
                    $hour = (int) Carbon::parse($attempt->start_at, 'Asia/Dhaka')->format('G');
                    $attemptsByHour[$hour]++;
                }
            }
        }
        $batchStudents = DB::table('batch_student')
            ->select('batch_id')->selectRaw('COUNT(DISTINCT student_id) as count')
            ->groupBy('batch_id')->pluck('count', 'batch_id');

        $batchRows = [];
        foreach ($batchNames as $id => $name) {
            $total = (int) ($batchStudents[$id] ?? 0);
            $attempted = count($attemptsByBatch[(int) $id] ?? []);
            $active = $liveAttempts->filter(fn ($a) => (int) $a->batch_id === (int) $id)->count();
            if ($total === 0 && $attempted === 0 && $active === 0) {
                continue;
            }
            $batchRows[] = [
                'id' => (int) $id,
                'name' => (string) $name,
                'total' => $total,
                'attempted' => $attempted,
                'active' => $active,
                'rate' => $total > 0 ? round($attempted * 100 / $total, 1) : null,
            ];
        }
        usort($batchRows, fn ($a, $b) => $b['attempted'] <=> $a['attempted']);

        // Published completed results only; teacher-configured pass mark wins if present.
        $completedWeek = DB::table('exams')
            ->where('status', 'Completed')
            ->where('end_at', '>=', $weekStart->toDateTimeString())
            ->get(['id', 'paper_id', 'student_id', 'mark', 'end_at']);
        $passed = 0;
        $failed = 0;
        $passByBatch = [];
        foreach ($completedWeek as $exam) {
            $paper = $papers[$exam->paper_id] ?? null;
            if (!$paper) {
                continue;
            }
            $published = $paper->result_view === 'Yes'
                || ($paper->result_at && Carbon::parse($paper->result_at, 'Asia/Dhaka')->lte($now));
            if (!$published) {
                continue;
            }
            $full = ((int) ($questionCounts[$paper->id] ?? 0)) * (float) $paper->mark;
            if ($full <= 0) {
                continue;
            }
            $threshold = $hasPassMark && $paper->pass_mark !== null && $paper->pass_mark !== ''
                ? (float) $paper->pass_mark : $full * 0.75;
            $ok = (float) $exam->mark >= $threshold;
            $ok ? $passed++ : $failed++;
            if ($paper->batch_id) {
                $key = (int) $paper->batch_id;
                $passByBatch[$key]['pass'] = ($passByBatch[$key]['pass'] ?? 0) + (int) $ok;
                $passByBatch[$key]['fail'] = ($passByBatch[$key]['fail'] ?? 0) + (int) !$ok;
            }
        }
        foreach ($batchRows as &$row) {
            $p = $passByBatch[$row['id']]['pass'] ?? 0;
            $f = $passByBatch[$row['id']]['fail'] ?? 0;
            $row['pass_rate'] = ($p + $f) ? round($p * 100 / ($p + $f), 1) : null;
        }
        unset($row);

        $recent = DB::table('exams as e')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->join('papers as p', 'p.id', '=', 'e.paper_id')
            ->whereNotNull('e.start_at')
            ->orderByDesc('e.id')
            ->limit(10)
            ->get(['e.id', 'e.status', 'e.start_at', 'e.end_at', 'e.paper_id',
                's.name as student_name', 'p.name as paper_name']);

        $questionTotal = DB::table('questions')->count();
        $questionUsed = DB::table('paper_question')->distinct()->count('question_id');
        $papersPublished = $papers->filter(fn ($p) => $p->status === 'Published')->count();
        $resultsPublished = $papers->filter(fn ($p) => $p->status === 'Published' && ($p->result_view === 'Yes'
            || ($p->result_at && Carbon::parse($p->result_at, 'Asia/Dhaka')->lte($now))))->count();
        $hourChart = [];
        for ($h = 8; $h <= 22; $h += 2) {
            $hourChart[] = ['label' => Carbon::createFromTime($h, 0, 0, 'Asia/Dhaka')->format('gA'),
                'value' => array_sum(array_slice($attemptsByHour, $h, 2))];
        }
        $daily = [];
        foreach ($dayTrend as $date => $count) {
            $daily[] = ['label' => Carbon::parse($date)->format('D'), 'value' => $count];
        }
        $uniqueToday = $dailyAttempts->pluck('student_id')->unique()->count();
        $readyScheduled = collect($rows)->filter(fn ($r) => !$r['needs_attention'])->count();

        return [
            'now' => $now,
            'stats' => [
                'today' => count($rows),
                'live' => collect($rows)->where('status', 'Live')->count(),
                'students_today' => $uniqueToday,
                'completed_today' => $completedToday,
                'needs_questions' => collect($rows)->where('needs_attention', true)->count(),
                'weekly_participation' => array_sum(array_map(fn ($r) => $r['attempted'], $batchRows)),
                'live_attempts' => $liveAttempts->count(),
            ],
            'todayExams' => $rows,
            'batchRows' => $batchRows,
            'liveMonitor' => $liveAttempts->map(function ($a) use ($now) {
                $remaining = max(0, $now->diffInSeconds(
                    Carbon::parse($a->start_at, 'Asia/Dhaka')->addMinutes((int) $a->time), false));
                return [
                    'student' => $a->student_name,
                    'batch' => $a->batch_name ?: 'Batch #' . $a->batch_id,
                    'exam' => $a->exam_name,
                    'remaining' => gmdate('H:i:s', max(0, (int) $remaining)),
                    'paper_id' => (int) $a->paper_id,
                ];
            })->values()->all(),
            'liveTotal' => $liveAttempts->count(),
            'hourChart' => $hourChart,
            'dailyTrend' => $daily,
            'statusSplit' => ['live' => collect($rows)->where('status','Live')->count(),
                'completed' => collect($rows)->where('status','Completed')->count(),
                'upcoming' => collect($rows)->where('status','Upcoming')->count(),
                'other' => collect($rows)->where('status','End Not Set')->count()],
            'passFail' => ['pass' => $passed, 'fail' => $failed],
            'recent' => $recent,
            'questionBank' => ['total' => $questionTotal, 'assigned' => $questionUsed,
                'unassigned' => max(0, $questionTotal - $questionUsed)],
            'publishing' => ['published' => $resultsPublished, 'papers' => $papersPublished],
            'readyScheduled' => $readyScheduled,
        ];
    }
}
