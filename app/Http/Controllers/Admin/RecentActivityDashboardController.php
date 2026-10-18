<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RecentActivityDashboard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecentActivityDashboardController extends Controller
{
    /** No student/teacher access to other students' browsing history. */
    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user && method_exists($user, 'authRole'), 403);
        $role = $user->authRole();
        abort_unless($role && in_array($role->name, ['SuperAdmin', 'Admin'], true), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();
        return view('admin.recent-activity-dashboard', RecentActivityDashboard::build($request));
    }

    public function student(Request $request, int $studentId)
    {
        $this->authorizeAdmin();
        $student = DB::table('students')->where('id', $studentId)
            ->first(['id', 'reg_id', 'name']);
        abort_unless($student, 404);
        $date = (string) $request->query('date', '');
        if ($date !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            || !Carbon::hasFormat($date, 'Y-m-d'))) abort(422, 'Invalid date');

        $days = DB::table('student_activity_days')->where('student_id', $studentId)
            ->orderByDesc('activity_date')->paginate(14, ['*'], 'history_page')->withQueryString();
        $events = DB::table('student_activity_events as e')
            ->leftJoin('student_activity_event_meta as m', 'm.event_id', '=', 'e.id')
            ->where('e.student_id', $studentId)
            ->when($date !== '', fn ($q) => $q->whereDate('e.occurred_at', $date))
            ->orderByDesc('e.id')
            ->select('e.*', 'm.device_type', 'm.course_id', 'm.batch_id', 'm.target_path')
            ->paginate(25, ['*'], 'event_page')->withQueryString();
        $totals = DB::table('student_activity_days')->where('student_id', $studentId)
            ->selectRaw('SUM(active_seconds) AS active, SUM(page_views) AS pages, SUM(click_count) AS clicks, MAX(last_seen_at) AS last_seen')->first();
        return view('admin.recent-activity-student', compact('student', 'days', 'events', 'totals', 'date'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();
        $start = Carbon::now('Asia/Dhaka')->subDays(29)->startOfDay()->toDateTimeString();
        $end = Carbon::now('Asia/Dhaka')->endOfDay()->toDateTimeString();
        $fileName = 'lebd-activity-' . Carbon::now('Asia/Dhaka')->format('Y-m-d') . '.csv';
        return response()->streamDownload(function () use ($start, $end) {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Student ID', 'Student', 'Event', 'Page Path', 'Target Label', 'Device', 'Date/Time']);
            DB::table('student_activity_events as e')
                ->join('students as st', 'st.id', '=', 'e.student_id')
                ->leftJoin('student_activity_event_meta as m', 'm.event_id', '=', 'e.id')
                ->whereBetween('e.occurred_at', [$start, $end])
                ->orderBy('e.id')
                ->select('e.student_id', 'st.name', 'e.event_type', 'e.path',
                    'e.target_label', 'm.device_type', 'e.occurred_at')
                ->chunk(300, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        $clean = static function ($v): string {
                            $v = (string) ($v ?? '');
                            return preg_match('/^[\s]*[=+\-@]/u', $v) ? "'" . $v : $v;
                        };
                        fputcsv($out, [$r->student_id, $clean($r->name), $r->event_type,
                            $clean($r->path), $clean($r->target_label), $r->device_type,
                            $r->occurred_at]);
                    }
                });
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

