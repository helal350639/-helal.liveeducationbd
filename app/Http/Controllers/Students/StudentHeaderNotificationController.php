<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Services\StudentHeaderNotificationFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentHeaderNotificationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(StudentHeaderNotificationFeed::get((int) auth('student')->id()));
    }

    /** Full-page notification centre for the authenticated student. */
    public function page()
    {
        return view('student.notifications.index');
    }

    public function seen(Request $request): JsonResponse
    {
        $data = $request->validate(['key' => ['required', 'string', 'max:180']]);
        abort_unless(Schema::hasTable('lebd_student_notification_reads'), 503, 'Notification read table is not installed.');
        DB::table('lebd_student_notification_reads')->updateOrInsert(
            ['student_id' => (int) auth('student')->id(), 'notification_key' => $data['key']],
            ['seen_at' => now('Asia/Dhaka')->toDateTimeString(), 'updated_at' => now(), 'created_at' => now()]
        );
        return response()->json(['success' => true]);
    }

    public function seenAll(): JsonResponse
    {
        abort_unless(Schema::hasTable('lebd_student_notification_reads'), 503, 'Notification read table is not installed.');
        $feed = StudentHeaderNotificationFeed::get((int) auth('student')->id());
        $now = now();
        foreach ($feed['notifications'] as $item) {
            DB::table('lebd_student_notification_reads')->updateOrInsert(
                ['student_id' => (int) auth('student')->id(), 'notification_key' => $item['key']],
                ['seen_at' => now('Asia/Dhaka')->toDateTimeString(), 'updated_at' => $now, 'created_at' => $now]
            );
        }
        return response()->json(['success' => true]);
    }
}
