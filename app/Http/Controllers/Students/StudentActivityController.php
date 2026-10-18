<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Services\StudentActivityContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentActivityController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $studentId = Auth::guard('student')->id();
        abort_unless($studentId, 403);
        $data = $request->validate([
            'session_key' => ['required', 'uuid'],
            'event_type' => ['required', 'in:page_view,click,heartbeat'],
            'path' => ['required', 'string', 'max:240'],
            'target_path' => ['nullable', 'string', 'max:240'],
            'target_label' => ['nullable', 'string', 'max:100'],
            'active_seconds' => ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        $sanitizePath = static function (?string $value): ?string {
            if ($value === null || $value === '') return null;
            // Accept only a *relative* path; no query, fragment or external host.
            $path = parse_url($value, PHP_URL_PATH);
            return is_string($path) && str_starts_with($path, '/')
                && !str_starts_with($path, '//') ? mb_substr($path, 0, 240) : null;
        };

        $path = $sanitizePath($data['path']) ?: '/';
        $targetPath = $data['event_type'] === 'click'
            ? $sanitizePath($data['target_path'] ?? null) : null;
        $label = $data['event_type'] === 'click'
            ? mb_substr(trim((string) ($data['target_label'] ?? '')), 0, 100) : null;
        $now = Carbon::now('Asia/Dhaka');
        $dateTime = $now->toDateTimeString();
        $today = $now->toDateString();
        $isPage = $data['event_type'] === 'page_view';
        $isClick = $data['event_type'] === 'click';
        $device = StudentActivityContext::device((string) $request->userAgent());

        DB::transaction(function () use (
            $studentId, $data, $path, $targetPath, $label, $now, $today,
            $dateTime, $isPage, $isClick, $device
        ) {
            DB::table('student_activity_sessions')->insertOrIgnore([
                'student_id' => $studentId, 'session_key' => $data['session_key'],
                'started_at' => $dateTime, 'last_seen_at' => $dateTime,
                'last_path' => $path, 'active_seconds' => 0,
            ]);
            $session = DB::table('student_activity_sessions')
                ->where('student_id', $studentId)
                ->where('session_key', $data['session_key'])->lockForUpdate()->first();
            if (!$session) abort(500, 'Activity session not found');

            $seconds = 0;
            if ($data['event_type'] === 'heartbeat') {
                $elapsed = max(0, (int) Carbon::parse($session->last_seen_at, 'Asia/Dhaka')->diffInSeconds($now));
                $seconds = min(20, (int) ($data['active_seconds'] ?? 0), $elapsed + 1);
            }
            DB::table('student_activity_sessions')->where('id', $session->id)->update([
                'last_seen_at' => $dateTime, 'last_path' => $path,
                'active_seconds' => DB::raw('active_seconds + ' . $seconds),
            ]);
            DB::table('student_activity_days')->insertOrIgnore([
                'student_id' => $studentId, 'activity_date' => $today,
                'last_seen_at' => $dateTime, 'active_seconds' => 0,
                'page_views' => 0, 'click_count' => 0,
            ]);
            DB::table('student_activity_days')
                ->where('student_id', $studentId)->where('activity_date', $today)
                ->update([
                    'last_seen_at' => $dateTime,
                    'active_seconds' => DB::raw('active_seconds + ' . $seconds),
                    'page_views' => DB::raw('page_views + ' . ($isPage ? 1 : 0)),
                    'click_count' => DB::raw('click_count + ' . ($isClick ? 1 : 0)),
                ]);

            if ($isPage || $isClick) {
                $eventId = DB::table('student_activity_events')->insertGetId([
                    'session_id' => $session->id, 'student_id' => $studentId,
                    'event_type' => $data['event_type'], 'path' => $path,
                    'target_label' => $label ?: null, 'occurred_at' => $dateTime,
                ]);
                $context = StudentActivityContext::fromPath($targetPath ?: $path);
                DB::table('student_activity_event_meta')->insert([
                    'event_id' => $eventId, 'student_id' => $studentId,
                    'course_id' => $context['course_id'],
                    'batch_id' => $context['batch_id'],
                    'device_type' => $device, 'target_path' => $targetPath,
                ]);
            }
        }, 2);

        return response()->json(['ok' => true]);
    }
}
