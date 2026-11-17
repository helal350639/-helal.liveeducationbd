<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Builds student-specific header notifications from the website's real exam
 * and result schedules. Seen state is stored per authenticated student.
 */
class StudentHeaderNotificationFeed
{
    public static function get(int $studentId): array
    {
        $now = Carbon::now('Asia/Dhaka');
        $items = [];
        $seenKeys = [];

        if (Schema::hasTable('lebd_student_notification_reads')) {
            $seenKeys = DB::table('lebd_student_notification_reads')
                ->where('student_id', $studentId)->pluck('notification_key')->map(fn ($v) => (string) $v)->all();
        }

        // Existing manually published notices remain part of the same notification centre.
        if (Schema::hasTable('notices')) {
            try {
                $notices = DB::table('notices')->whereRaw('LOWER(status) = ?', ['published'])
                    ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', $now->toDateTimeString()))
                    ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now->toDateTimeString()))
                    ->orderByDesc('id')->limit(30)->get();
                foreach ($notices as $notice) {
                    $items[] = self::item(
                        'notice:' . $notice->id, 'notice', $notice->title ?: 'New Notice Posted',
                        Str::limit(trim(strip_tags((string) ($notice->details ?? 'নতুন একটি নোটিশ প্রকাশিত হয়েছে।'))), 150),
                        $notice->created_at ?? $now, null, $notice->image ?? null
                    );
                }
            } catch (\Throwable $e) {
                // Keep the notification centre available if an older notice schema differs.
            }
        }

        // Legacy question-paper exams: notify when their scheduled opening time arrives.
        if (Schema::hasTable('papers')) {
            $columns = Schema::getColumnListing('papers');
            if (in_array('open', $columns, true) && in_array('status', $columns, true)) {
                $papers = DB::table('papers')->whereIn('status', ['Published', 'Scheduled'])
                    ->whereNotNull('open')->where('open', '<=', $now->toDateTimeString())
                    ->where('open', '>=', $now->copy()->subDays(30)->toDateTimeString())
                    ->orderByDesc('open')->limit(100)->get();
                foreach ($papers as $paper) {
                    // Match the same permit categories enforced by the existing exam entry controller.
                    $allowed = match ((string) ($paper->permit ?? '')) {
                        'Every One' => true,
                        'Batch' => !empty($paper->batch_id) && Schema::hasTable('batch_student')
                            && DB::table('batch_student')->where('student_id', $studentId)->where('batch_id', $paper->batch_id)->exists(),
                        'Department' => !empty($paper->department_id) && Schema::hasTable('department_student')
                            && DB::table('department_student')->where('student_id', $studentId)->where('department_id', $paper->department_id)->exists(),
                        'Group' => !empty($paper->group_id) && Schema::hasTable('group_student')
                            && DB::table('group_student')->where('student_id', $studentId)->where('group_id', $paper->group_id)->exists(),
                        default => false,
                    };
                    if (!$allowed) continue;
                    $items[] = self::item(
                        'paper-start:' . $paper->id, 'exam', 'Exam Started',
                        '“' . ($paper->name ?: 'Exam') . '” পরীক্ষা শুরু হয়েছে। এখনই পরীক্ষায় অংশ নিন।',
                        $paper->open, url('/students/check/' . $paper->id)
                    );
                }
            }

            // Result is published only under the same rule used by the student result page.
            if (Schema::hasTable('exams') && in_array('result_view', $columns, true)) {
                $paperRows = DB::table('papers as p')->join('exams as e', 'e.paper_id', '=', 'p.id')
                    ->where('e.student_id', $studentId)->where('e.status', 'Completed')
                    ->where(function ($q) use ($now) {
                        $q->where('p.result_view', 'Yes')
                            ->orWhere(function ($q2) use ($now) {
                                $q2->whereNotNull('p.result_at')->where('p.result_at', '<=', $now->toDateTimeString());
                            });
                    })
                    ->select('p.id', 'p.name', 'p.result_at', 'p.result_view', 'p.updated_at', 'p.created_at')
                    ->groupBy('p.id', 'p.name', 'p.result_at', 'p.result_view', 'p.updated_at', 'p.created_at')
                    ->orderByDesc('p.result_at')->limit(100)->get();
                foreach ($paperRows as $paper) {
                    $publishedAt = $paper->result_at ?: ($paper->updated_at ?: $paper->created_at);
                    if (($paper->result_view ?? null) === 'Yes' && $paper->updated_at) $publishedAt = $paper->updated_at;
                    if ($publishedAt && Carbon::parse($publishedAt, 'Asia/Dhaka')->lt($now->copy()->subDays(30))) continue;
                    $items[] = self::item(
                        'paper-result:' . $paper->id, 'result', 'Exam Result Published',
                        '“' . ($paper->name ?: 'Exam') . '” পরীক্ষার ফলাফল প্রকাশিত হয়েছে। আপনার ফলাফল দেখুন।',
                        $publishedAt ?: $now, url('/students/result/' . $paper->id)
                    );
                }
            }
        }

        // Legacy Admin Routine schedules: Main Exam and Revision/Model rows.
        // These schedules are separate from papers.open, so they need their own start trigger.
        if (Schema::hasTable('routines') && Schema::hasTable('routine_item_rows') && Schema::hasTable('batch_student')) {
            $routineRows = DB::table('routine_item_rows as i')
                ->join('routines as r', 'r.id', '=', 'i.routine_id')
                ->where('r.status', 'Published')->whereNotNull('r.batch_id')
                ->whereNotNull('i.exam_at')->where('i.exam_at', '<=', $now->toDateTimeString())
                ->where('i.exam_at', '>=', $now->copy()->subDays(30)->toDateTimeString())
                ->select('i.id', 'i.paper_name', 'i.exam_at', 'r.id as routine_id', 'r.course_id', 'r.batch_id', 'r.department_id')
                ->orderByDesc('i.exam_at')->limit(150)->get();
            foreach ($routineRows as $row) {
                $belongsToBatch = DB::table('batch_student')->where('student_id', $studentId)
                    ->where('batch_id', $row->batch_id)->exists();
                if (!$belongsToBatch) continue;
                if ($row->department_id && Schema::hasTable('department_student')) {
                    $belongsToDepartment = DB::table('department_student')->where('student_id', $studentId)
                        ->where('department_id', $row->department_id)->exists();
                    if (!$belongsToDepartment) continue;
                }
                $paper = DB::table('papers')->where('course_id', $row->course_id)
                    ->where('batch_id', $row->batch_id)->where('name', $row->paper_name)
                    ->whereIn('status', ['Published', 'Scheduled'])->orderByDesc('id')->first(['id']);
                $key = $paper ? 'paper-start:' . $paper->id : 'routine-row-start:' . $row->id;
                // Open the actual exam entry when a legacy question paper exists.
                // If no paper row is linked, take the student to this exact batch's
                // exam page instead of sending them back to the Routine screen.
                $link = $paper
                    ? url('/students/start-routine-exam/' . $paper->id)
                    : url('/students/batch-exam/' . (int) $row->batch_id);
                $items[] = self::item(
                    $key, 'exam', 'Exam Started',
                    '“' . ($row->paper_name ?: 'Routine Exam') . '” পরীক্ষা শুরু হয়েছে। এখনই আপনার ব্যাচের পরীক্ষা পেজে যান।',
                    $row->exam_at, $link
                );
            }
        }

        // Revision / Model exams saved as derived routine items.
        if (Schema::hasTable('routine_derived_exam_items') && Schema::hasTable('routines') && Schema::hasTable('batch_student')) {
            $derivedRows = DB::table('routine_derived_exam_items as d')
                ->join('routines as r', 'r.id', '=', 'd.routine_id')
                ->where('r.status', 'Published')->whereNotNull('r.batch_id')
                ->whereNotNull('d.exam_at')->where('d.exam_at', '<=', $now->toDateTimeString())
                ->where('d.exam_at', '>=', $now->copy()->subDays(30)->toDateTimeString())
                ->select('d.id', 'd.exam_name', 'd.exam_at', 'r.course_id', 'r.batch_id', 'r.department_id')
                ->orderByDesc('d.exam_at')->limit(100)->get();
            foreach ($derivedRows as $row) {
                if (!DB::table('batch_student')->where('student_id', $studentId)->where('batch_id', $row->batch_id)->exists()) continue;
                if ($row->department_id && Schema::hasTable('department_student')
                    && !DB::table('department_student')->where('student_id', $studentId)->where('department_id', $row->department_id)->exists()) continue;
                // Revision / Model notifications must also open the relevant
                // batch exam page, never the routine display page.
                $items[] = self::item(
                    'routine-derived-start:' . $row->id, 'exam', 'Exam Started',
                    '“' . ($row->exam_name ?: 'Revision / Model Exam') . '” পরীক্ষা শুরু হয়েছে। এখনই আপনার ব্যাচের পরীক্ষা পেজে যান।',
                    $row->exam_at, url('/students/batch-exam/' . (int) $row->batch_id)
                );
            }
        }

        // Routine Auto Exam results: only the student whose own attempt is Completed is notified.
        if (Schema::hasTable('lebd_routine_auto_attempts') && Schema::hasTable('routine_exams') && Schema::hasTable('routines')) {
            $autoResults = DB::table('lebd_routine_auto_attempts as a')
                ->join('routine_exams as e', 'e.id', '=', 'a.routine_exam_id')
                ->join('routines as r', 'r.id', '=', 'e.routine_id')
                ->where('a.student_id', $studentId)->where('a.status', 'Completed')
                ->whereNotNull('a.submitted_at')
                ->select('a.id as attempt_id', 'a.routine_exam_id', 'a.department_id', 'a.submitted_at',
                    'e.name as exam_name', 'r.course_id', 'r.batch_id')
                ->orderByDesc('a.submitted_at')->limit(100)->get();
            foreach ($autoResults as $attempt) {
                if (Carbon::parse($attempt->submitted_at, 'Asia/Dhaka')->lt($now->copy()->subDays(30))) continue;
                $items[] = self::item(
                    'routine-auto-result:' . $attempt->attempt_id, 'result', 'Exam Result Published',
                    '“' . ($attempt->exam_name ?: 'Routine Auto Exam') . '” পরীক্ষার ফলাফল প্রকাশিত হয়েছে। আপনার ফলাফল দেখুন।',
                    $attempt->submitted_at,
                    url('/students/routine-auto-exam/' . (int) $attempt->course_id . '/' . (int) $attempt->batch_id . '/' . (int) $attempt->routine_exam_id . '/' . (int) $attempt->department_id . '/result') . '?attempt_id=' . (int) $attempt->attempt_id
                );
            }
        }

        // New Exam Section: start notices are triggered by the saved exam_start_at timestamp.
        $required = ['lebd_exam_section_routines', 'lebd_exam_section_routine_items', 'lebd_exam_section_categories'];
        $hasExamSection = collect($required)->every(fn ($table) => Schema::hasTable($table));
        if ($hasExamSection) {
            $hasSubs = Schema::hasTable('lebd_exam_section_subcategories');
            $startQuery = DB::table('lebd_exam_section_routine_items as i')
                ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
                ->join('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id');
            if ($hasSubs) $startQuery->leftJoin('lebd_exam_section_subcategories as s', 's.id', '=', 'r.subcategory_id');
            $startQuery->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
                ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
                ->whereRaw('LOWER(TRIM(c.status)) = ?', ['active'])
                ->whereNotNull('i.exam_start_at')->where('i.exam_start_at', '<=', $now->toDateTimeString())
                ->where('i.exam_start_at', '>=', $now->copy()->subDays(30)->toDateTimeString());
            if ($hasSubs) $startQuery->where(fn ($q) => $q->whereNull('r.subcategory_id')->orWhereRaw('LOWER(TRIM(s.status)) = ?', ['active']));
            $starts = $startQuery->select('i.id', 'i.exam_name', 'i.exam_start_at', 'r.category_id', 'r.subcategory_id')
                ->orderByDesc('i.exam_start_at')->limit(100)->get();
            foreach ($starts as $exam) {
                $url = url('/lebd-exam-browse/category/' . (int) $exam->category_id . '/exams')
                    . ($exam->subcategory_id ? '?subcategory_id=' . (int) $exam->subcategory_id : '');
                $items[] = self::item(
                    'routine-start:' . $exam->id, 'exam', 'Exam Started',
                    '“' . $exam->exam_name . '” পরীক্ষা শুরু হয়েছে। পরীক্ষার বিভাগে গিয়ে অংশ নিন।',
                    $exam->exam_start_at, $url
                );
            }

            // Results are student-specific: only notify a student who submitted that exam.
            if (Schema::hasTable('lebd_exam_section_student_attempts') && Schema::hasTable('lebd_exam_section_paper_settings')) {
                $attempts = DB::table('lebd_exam_section_student_attempts as a')
                    ->join('lebd_exam_section_routine_items as i', 'i.id', '=', 'a.routine_item_id')
                    ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
                    ->where('a.student_id', $studentId)->where('a.status', 'submitted')
                    ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
                    ->whereNotNull('i.exam_end_at')
                    ->select('a.id as attempt_id', 'a.routine_item_id', 'a.submitted_at', 'i.exam_name', 'i.exam_end_at', 'r.category_id', 'r.subcategory_id')
                    ->orderByDesc('i.exam_end_at')->limit(150)->get();
                $settingsRows = DB::table('lebd_exam_section_paper_settings')->get(['category_id', 'subcategory_id', 'settings_json']);
                $settings = [];
                foreach ($settingsRows as $row) {
                    $key = (int) $row->category_id . ':' . ($row->subcategory_id === null ? '0' : (int) $row->subcategory_id);
                    $settings[$key] = json_decode($row->settings_json, true) ?: [];
                }
                foreach ($attempts as $attempt) {
                    $scopeKey = (int) $attempt->category_id . ':' . ($attempt->subcategory_id === null ? '0' : (int) $attempt->subcategory_id);
                    $immediate = in_array(strtolower((string) ($settings[$scopeKey]['student_view_result'] ?? 'no')), ['yes', '1', 'true'], true);
                    $end = Carbon::parse($attempt->exam_end_at, 'Asia/Dhaka');
                    if (!$immediate && $now->lt($end)) continue;
                    $eventAt = $immediate ? ($attempt->submitted_at ?: $end) : $end;
                    if (Carbon::parse($eventAt, 'Asia/Dhaka')->lt($now->copy()->subDays(30))) continue;
                    $items[] = self::item(
                        'routine-result:' . $attempt->routine_item_id, 'result', 'Exam Result Published',
                        '“' . $attempt->exam_name . '” পরীক্ষার ফলাফল এখন দেখা যাবে। আপনার ফলাফল দেখুন।',
                        $eventAt, url('/lebd-exam-take/' . (int) $attempt->routine_item_id . '/result')
                    );
                }
            }
        }

        // Merge duplicate keys, attach per-student seen state, and show newest first.
        $unique = [];
        foreach ($items as $item) $unique[$item['key']] = $item;
        $items = array_values($unique);
        foreach ($items as &$item) $item['seen'] = in_array($item['key'], $seenKeys, true);
        unset($item);
        usort($items, fn ($a, $b) => strtotime($b['sort_at']) <=> strtotime($a['sort_at']));
        $items = array_slice($items, 0, 100);
        $unread = count(array_filter($items, fn ($item) => !$item['seen']));

        return ['notifications' => $items, 'total' => count($items), 'unread' => $unread];
    }

    private static function item(string $key, string $type, string $title, string $message, $at, ?string $url, ?string $image = null): array
    {
        $date = $at ? Carbon::parse($at, 'Asia/Dhaka') : Carbon::now('Asia/Dhaka');
        $map = [
            'exam' => ['fa-bullhorn', 'notice'],
            'result' => ['fa-trophy', 'result'],
            'notice' => ['fa-bell', 'notice'],
        ];
        [$icon, $tone] = $map[$type] ?? $map['notice'];
        return [
            'key' => $key, 'type' => $type, 'title' => $title, 'message' => $message,
            'icon' => $icon, 'tone' => $tone, 'time' => $date->diffForHumans(),
            'sort_at' => $date->toDateTimeString(), 'url' => $url,
            'image' => $image ? asset($image) : null, 'seen' => false,
        ];
    }
}
