<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecentActivityDashboard
{
    /** Build report from real activity data; zero means no tracking yet. */
    public static function build(Request $request): array
    {
        $days = (int) $request->query('days', 7);
        if (!in_array($days, [1, 7, 30], true)) $days = 7;
        $today = Carbon::now('Asia/Dhaka');
        $start = $today->copy()->subDays($days - 1)->startOfDay();
        $end = $today->copy()->endOfDay();
        $rangeStart = $start->toDateTimeString();
        $rangeEnd = $end->toDateTimeString();
        $courseId = max(0, (int) $request->query('course', 0));
        $batchId = max(0, (int) $request->query('batch', 0));
        $departmentId = max(0, (int) $request->query('department', 0));
        $device = (string) $request->query('device', '');
        $device = in_array($device, ['Mobile', 'Desktop', 'Tablet', 'Unknown'], true) ? $device : '';
        $kind = (string) $request->query('type', '');
        $kind = in_array($kind, ['click', 'page_view'], true) ? $kind : '';
        $keyword = mb_substr(trim((string) $request->query('q', '')), 0, 70);

        $query = DB::table('student_activity_events as e')
            ->join('students as st', 'st.id', '=', 'e.student_id')
            ->leftJoin('student_activity_event_meta as em', 'em.event_id', '=', 'e.id')
            ->whereBetween('e.occurred_at', [$rangeStart, $rangeEnd]);
        if ($courseId) $query->where('em.course_id', $courseId);
        if ($batchId) $query->where('em.batch_id', $batchId);
        if ($device !== '') $query->where('em.device_type', $device);
        if ($kind !== '') $query->where('e.event_type', $kind);
        if ($departmentId) {
            // Student membership as currently recorded, not historical enrollment.
            $query->whereExists(function ($q) use ($departmentId) {
                $q->selectRaw('1')->from('department_student as ds')
                    ->whereColumn('ds.student_id', 'e.student_id')
                    ->where('ds.department_id', $departmentId);
            });
        }
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('st.name', 'like', '%' . $keyword . '%')
                    ->orWhere('st.reg_id', 'like', '%' . $keyword . '%');
            });
        }

        $total = (clone $query)->selectRaw("COUNT(*) AS events, COUNT(DISTINCT e.student_id) AS unique_students, SUM(CASE WHEN e.event_type='page_view' THEN 1 ELSE 0 END) AS pages, SUM(CASE WHEN e.event_type='click' THEN 1 ELSE 0 END) AS clicks")->first();
        $totalTrackedStudents = DB::table('student_activity_days')->distinct()->count('student_id');
        $courseVisits = (clone $query)->where('e.event_type', 'page_view')
            ->whereNotNull('em.course_id')->count();

        $activeSessions = DB::table('student_activity_sessions as se')
            ->where('se.started_at', '<=', $rangeEnd)
            ->where('se.last_seen_at', '>=', $rangeStart)
            ->whereIn('se.student_id', (clone $query)->select('e.student_id')->distinct());
        $avgSeconds = (float) ((clone $activeSessions)->avg('se.active_seconds') ?? 0);

        $topCourses = (clone $query)->where('e.event_type', 'page_view')
            ->whereNotNull('em.course_id')
            ->join('courses as c', 'c.id', '=', 'em.course_id')
            ->select('c.id', 'c.name')->selectRaw('COUNT(*) AS total')
            ->groupBy('c.id', 'c.name')->orderByDesc('total')->limit(40)->get();

        $topClicks = (clone $query)->where('e.event_type', 'click')
            ->selectRaw("COALESCE(NULLIF(e.target_label, ''),'Unnamed button') AS title")
            ->selectRaw('COUNT(*) AS total')->groupBy('title')
            ->orderByDesc('total')->limit(40)->get();

        // Counts by distinct, mutually exclusive categories (no fake percentages).
        $types = (clone $query)->selectRaw("CASE
          WHEN e.event_type='click' THEN 'Button Click'
          WHEN e.path LIKE '/students/exam/%' OR e.path LIKE '/students/exam-info/%' THEN 'Exam Page'
          WHEN e.path LIKE '/students/result/%' OR e.path LIKE '/students/result-design/%' THEN 'Result Page'
          WHEN e.path LIKE '/home/course/%' OR e.path LIKE '/students/course/%' OR e.path LIKE '/course/details/%' THEN 'Course Page'
          ELSE 'Other Page' END AS title")
            ->selectRaw('COUNT(*) AS total')->groupBy('title')->orderByDesc('total')->get();

        $dailyRaw = (clone $query)->where('e.event_type', 'page_view')
            ->selectRaw('DATE(e.occurred_at) AS activity_date')
            ->selectRaw('COUNT(DISTINCT e.student_id) AS visitors')
            ->selectRaw('COUNT(*) AS views')->groupBy('activity_date')->get()->keyBy('activity_date');
        $trend = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $row = $dailyRaw->get($date);
            $trend[] = [
                'date' => $date,
                'label' => Carbon::parse($date)->format($days <= 7 ? 'D' : 'M d'),
                'visitors' => $row ? (int) $row->visitors : 0,
                'views' => $row ? (int) $row->views : 0,
            ];
        }

        $recent = (clone $query)->select([
            'e.id', 'e.student_id', 'st.name', 'st.reg_id', 'e.event_type',
            'e.path', 'e.target_label', 'e.occurred_at', 'em.device_type',
            'em.target_path',
        ])->orderByDesc('e.occurred_at')->orderByDesc('e.id')->limit(40)->get();

        $topStudents = (clone $query)->select('st.id', 'st.name', 'st.reg_id')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('st.id', 'st.name', 'st.reg_id')
            ->orderByDesc('total')->limit(35)->get();

        $popularPages = (clone $query)->where('e.event_type', 'page_view')
            ->select('e.path')->selectRaw('COUNT(*) AS total')
            ->groupBy('e.path')->orderByDesc('total')->limit(35)->get();

        $sessionTotals = DB::table('student_activity_sessions')
            ->select('student_id')->selectRaw('SUM(active_seconds) AS active_seconds')
            ->selectRaw('MAX(last_seen_at) AS last_seen_at')
            ->groupBy('student_id');
        $studentRows = (clone $query)
            ->leftJoinSub($sessionTotals, 'sec', 'sec.student_id', '=', 'e.student_id')
            ->select('st.id', 'st.name', 'st.reg_id')
            ->selectRaw('GREATEST(MAX(e.occurred_at), MAX(COALESCE(sec.last_seen_at, e.occurred_at))) AS last_seen')
            ->selectRaw('COUNT(*) AS activity_count')
            ->selectRaw("SUM(CASE WHEN e.event_type='page_view' THEN 1 ELSE 0 END) AS pages")
            ->selectRaw("SUM(CASE WHEN e.event_type='click' THEN 1 ELSE 0 END) AS clicks")
            ->selectRaw('MAX(COALESCE(sec.active_seconds, 0)) AS active_seconds')
            ->groupBy('st.id', 'st.name', 'st.reg_id')
            ->orderByDesc('last_seen')->orderByDesc('st.id')
            ->paginate(10, ['*'], 'page')->withQueryString();

        // Funnel measures distinct tracked visitors to each stage. "Submitted" means
        // completed in the exam database, not merely that a Submit button was clicked.
        $pageOnly = (clone $query)->where('e.event_type', 'page_view');
        $stage1 = (clone $pageOnly)->whereIn('e.path', ['/', '/students', '/students/'])->distinct()->count('e.student_id');
        $stage2 = (clone $pageOnly)->where(function ($q) {
            $q->where('e.path', 'like', '/home/course/%')
                ->orWhere('e.path', 'like', '/students/course/%')
                ->orWhere('e.path', 'like', '/course/details/%');
        })->distinct()->count('e.student_id');
        $stage3 = (clone $pageOnly)->where('e.path', 'like', '/students/exam-info/%')->distinct()->count('e.student_id');
        $stage4 = (clone $pageOnly)->where('e.path', 'like', '/students/exam/%')->distinct()->count('e.student_id');
        $submittedQuery = DB::table('exams as ex')->where('ex.status', 'Completed')
            ->whereBetween('ex.end_at', [$rangeStart, $rangeEnd]);
        if ($courseId || $batchId) {
            $submittedQuery->join('papers as p', 'p.id', '=', 'ex.paper_id');
            if ($courseId) $submittedQuery->where('p.course_id', $courseId);
            if ($batchId) $submittedQuery->where('p.batch_id', $batchId);
        }
        if ($departmentId) $submittedQuery->whereExists(function ($q) use ($departmentId) {
            $q->selectRaw('1')->from('department_student as ds')
                ->whereColumn('ds.student_id', 'ex.student_id')
                ->where('ds.department_id', $departmentId);
        });
        if ($keyword !== '') $submittedQuery->whereExists(function ($q) use ($keyword) {
            $q->selectRaw('1')->from('students as ss')->whereColumn('ss.id', 'ex.student_id')
                ->where(function ($q) use ($keyword) {
                    $q->where('ss.name', 'like', '%' . $keyword . '%')
                        ->orWhere('ss.reg_id', 'like', '%' . $keyword . '%');
                });
        });
        $stage5 = $device !== '' || $kind !== ''
            ? null // Can't infer a device/type for submitted rows without joining actual tracked events.
            : $submittedQuery->distinct()->count('ex.student_id');


        // STUDENT GROWTH: cumulative registered Student accounts (not page visits).
        // Uses students.created_at; the chart includes the registered baseline before the window.
        $growthDays = (int) $request->query('growth_days', 30);
        if (!in_array($growthDays, [7, 30, 90], true)) $growthDays = 30;
        $growthStart = $today->copy()->subDays($growthDays - 1)->startOfDay();
        $growthBaseline = (int) DB::table('students')
            ->whereNotNull('created_at')
            ->where('created_at', '<', $growthStart->toDateTimeString())
            ->count();
        $dailyRegistrations = DB::table('students')
            ->whereBetween('created_at', [
                $growthStart->toDateTimeString(),
                $today->copy()->endOfDay()->toDateTimeString(),
            ])
            ->selectRaw('DATE(created_at) AS registration_date, COUNT(*) AS registrations')
            ->groupBy('registration_date')
            ->get()
            ->keyBy('registration_date');
        $studentGrowth = [];
        $runningStudents = $growthBaseline;
        for ($i = 0; $i < $growthDays; $i++) {
            $day = $growthStart->copy()->addDays($i);
            $date = $day->toDateString();
            $registeredToday = (int) ($dailyRegistrations->get($date)->registrations ?? 0);
            $runningStudents += $registeredToday;
            $studentGrowth[] = [
                'date' => $date,
                'label' => $day->format('M d'),
                'registered' => $registeredToday,
                'total' => $runningStudents,
            ];
        }

        // COURSE ENROLLMENT: count DISTINCT students in each Course, using BOTH
        // direct course_student membership and batch_student -> batches.course_id.
        // UNION prevents students enrolled through both routes from being double-counted.
        // Counts represent memberships, NOT website visits or paid orders.
        $directEnrollments = DB::table('course_student')
            ->select('course_id', 'student_id')
            ->whereNotNull('course_id')
            ->whereNotNull('student_id');
        $batchEnrollments = DB::table('batch_student as bs')
            ->join('batches as b', 'b.id', '=', 'bs.batch_id')
            ->select('b.course_id', 'bs.student_id')
            ->whereNotNull('b.course_id')
            ->whereNotNull('bs.student_id');
        $enrollmentPairs = $directEnrollments->union($batchEnrollments);
        $enrollmentRows = DB::query()
            ->fromSub($enrollmentPairs, 'membership')
            ->join('courses as c', 'c.id', '=', 'membership.course_id')
            ->join('students as enrolled_student', 'enrolled_student.id', '=', 'membership.student_id')
            ->select('c.id', 'c.name')
            ->selectRaw('COUNT(DISTINCT membership.student_id) AS enrollment_count')
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('enrollment_count')
            ->orderBy('c.name')
            ->get();
        $totalCourseEnrollments = (int) $enrollmentRows->sum('enrollment_count');
        $enrollmentBreakdown = [];
        foreach ($enrollmentRows->take(5) as $row) {
            $enrollmentBreakdown[] = [
                'name' => (string) $row->name,
                'count' => (int) $row->enrollment_count,
            ];
        }
        $otherEnrollments = (int) $enrollmentRows->skip(5)->sum('enrollment_count');
        if ($otherEnrollments > 0) {
            $enrollmentBreakdown[] = ['name' => 'Others', 'count' => $otherEnrollments];
        }

        return [
            'growthDays' => $growthDays,
            'studentGrowth' => $studentGrowth,
            'enrollmentBreakdown' => $enrollmentBreakdown,
            'enrollmentAll' => $enrollmentRows,
            'enrollmentTotal' => $totalCourseEnrollments,
            'days' => $days, 'courseId' => $courseId, 'batchId' => $batchId,
            'departmentId' => $departmentId, 'device' => $device, 'kind' => $kind,
            'keyword' => $keyword, 'today' => $today,
            'stats' => [
                'students' => (int) ($total->unique_students ?? 0),
                'events' => (int) ($total->events ?? 0),
                'seen_students' => $totalTrackedStudents,
                'sessions' => (int) (clone $activeSessions)->count(),
                'avg_seconds' => (int) round($avgSeconds),
                'pages' => (int) ($total->pages ?? 0),
                'clicks' => (int) ($total->clicks ?? 0),
                'course_visits' => $courseVisits,
            ],
            'courses' => DB::table('courses')->whereNotNull('name')->orderBy('name')->get(['id', 'name']),
            'batches' => DB::table('batches')->whereNotNull('name')->orderBy('name')->get(['id', 'name']),
            'departments' => DB::table('departments')->whereNotNull('name')->orderBy('name')->get(['id', 'name']),
            'topCourses' => $topCourses, 'topClicks' => $topClicks,
            'activityTypes' => $types, 'trend' => $trend, 'recent' => $recent,
            'topStudents' => $topStudents, 'popularPages' => $popularPages,
            'studentRows' => $studentRows,
            'funnel' => [
                ['name' => 'Homepage', 'count' => $stage1],
                ['name' => 'Course Page', 'count' => $stage2],
                ['name' => 'Exam Information', 'count' => $stage3],
                ['name' => 'Start Exam Page', 'count' => $stage4],
                ['name' => 'Completed Exam (DB)', 'count' => $stage5],
            ],
        ];
    }
}
