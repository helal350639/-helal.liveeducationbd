<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Schema-safe student-scoped adapter. Configure verified source tables in config/lebd_seven_day.php.
 * All unconfigured or mismatched sources are skipped, NEVER replaced by example records.
 */
final class StudentDashboardSevenDayFeed
{
    public static function get($student): array
    {
        $tz = 'Asia/Dhaka';
        $now = Carbon::now($tz);
        $futureEnd = $now->copy()->addDays(7);
        $pastStart = $now->copy()->subHours(24);
        $upcoming = []; $results = [];
        // Use the verified LEBD Exam Section routine schema (no empty adapter mapping).
        $upcoming = self::examSectionUpcoming($now, $futureEnd);
        foreach (config('lebd_seven_day.upcoming_sources', []) as $source) {
            foreach (self::read($source, $student->id, 'start_at', $now, $futureEnd, true) as $row) {
                $start = Carbon::parse($row->feed_date, $tz);
                $upcoming[] = [
                    'title' => $row->feed_title,
                    'date' => $start->locale('bn')->translatedFormat('d F'),
                    'time' => $start->format('h:i A'),
                    'sort' => $start->timestamp,
                    'subtitle' => $row->feed_subtitle ?? '',
                    'url' => null,
                ];
            }
        }
        foreach (config('lebd_seven_day.result_sources', []) as $source) {
            foreach (self::read($source, $student->id, 'published_at', $pastStart, $now, false) as $row) {
                $published = Carbon::parse($row->feed_date, $tz);
                $results[] = [
                    'title' => $row->feed_title,
                    'published' => $published->locale('bn')->translatedFormat('d F Y, h:i A'),
                    'sort' => $published->timestamp,
                    'score' => isset($row->feed_score) ? (string)$row->feed_score : null,
                    'total' => isset($row->feed_total) ? (string)$row->feed_total : null,
                    'rank' => isset($row->feed_rank) ? (string)$row->feed_rank : null,
                    'url' => null,
                ];
            }
        }
        // Scheduled result releases: future date only; do not expose marks before publication.
        $scheduledResults = [];
        foreach (config('lebd_seven_day.scheduled_result_sources', []) as $source) {
            foreach (self::readScheduledResult($source, $student->id, $now, $futureEnd) as $row) {
                $release = Carbon::parse($row->feed_date, $tz);
                $scheduledResults[] = [
                    'title' => $row->feed_title,
                    'scheduled' => $release->locale('bn')->translatedFormat('d F Y, h:i A'),
                    'sort' => $release->timestamp,
                ];
            }
        }
        // Verified Exam Section submission table. Include submissions for this student only.
        // Result-published state is not encoded in the verified schema and MUST
        // not be inferred merely from score or submitted_at.
        $submittedResults = self::examSectionSubmitted($student, $now);
        usort($upcoming, fn($a,$b) => $a['sort'] <=> $b['sort']);
        usort($results, fn($a,$b) => $b['sort'] <=> $a['sort']);
        usort($scheduledResults, fn($a,$b) => $a['sort'] <=> $b['sort']);
        return ['upcoming' => $upcoming, 'results' => $results, 'scheduled_results' => $scheduledResults, 'submitted_results' => $submittedResults];
    }

    /**
     * Published Exam Section category/subcategory routines, in chronological order.
     * This is a DISCOVERY feed, not a student entitlement decision: the existing
     * /lebd-exam-take/{id} endpoint must enforce course purchase/enrolment access.
     * Do not present this as enrolment-filtered until actual enrolment joins are verified.
     */
    private static function examSectionUpcoming(Carbon $now, Carbon $futureEnd): array
    {
        foreach (['lebd_exam_section_routines', 'lebd_exam_section_routine_items',
                  'lebd_exam_section_categories', 'lebd_exam_section_subcategories'] as $table) {
            if (!Schema::hasTable($table)) return [];
        }
        $rows = DB::table('lebd_exam_section_routine_items as i')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->join('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id')
            ->leftJoin('lebd_exam_section_subcategories as s', 's.id', '=', 'r.subcategory_id')
            ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
            ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
            ->whereRaw('LOWER(TRIM(c.status)) = ?', ['active'])
            ->where(function ($query) {
                $query->whereNull('r.subcategory_id')
                    ->orWhereRaw('LOWER(TRIM(s.status)) = ?', ['active']);
            })
            ->whereNotNull('i.exam_start_at')
            ->whereNotNull('i.exam_end_at')
            ->where('i.exam_start_at', '>', $now->format('Y-m-d H:i:s'))
            ->where('i.exam_start_at', '<=', $futureEnd->format('Y-m-d H:i:s'))
            ->select('i.id', 'i.exam_name', 'i.exam_start_at', 'i.exam_end_at',
                     'c.name as category_name', 's.name as subcategory_name')
            ->orderBy('i.exam_start_at')->orderBy('i.id')->limit(500)->get();

        $items = [];
        foreach ($rows as $row) {
            $start = Carbon::parse($row->exam_start_at, 'Asia/Dhaka');
            $end = Carbon::parse($row->exam_end_at, 'Asia/Dhaka');
            if ($end->lessThanOrEqualTo($start)) continue;
            $subtitle = $row->category_name;
            if ($row->subcategory_name) $subtitle .= ' > ' . $row->subcategory_name;
            $items[] = [
                'title' => $row->exam_name,
                'date' => $start->locale('bn')->translatedFormat('d F'),
                'time' => $start->format('h:i A'),
                'sort' => $start->timestamp,
                'subtitle' => $subtitle,
                'url' => url('/lebd-exam-take/' . (int) $row->id),
            ];
        }
        return $items;
    }

    /**
     * Real submitted exam attempts, from the user's verified database schema.
     * This does not reveal score/answer before result publication.
     * No 7-day limit: pending stays visible until a trustworthy publish-state
     * integration is configured.
     */
    private static function examSectionSubmitted($student, Carbon $now): array
    {
        foreach (['lebd_exam_section_student_attempts',
                  'lebd_exam_section_routine_items',
                  'lebd_exam_section_routines',
                  'lebd_exam_section_categories'] as $table) {
            if (!Schema::hasTable($table)) return [];
        }
        $rows = DB::table('lebd_exam_section_student_attempts as a')
            ->join('lebd_exam_section_routine_items as i', 'i.id', '=', 'a.routine_item_id')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->join('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id')
            ->where('a.student_id', (int) $student->id)
            ->whereNotNull('a.submitted_at')
            ->where('a.submitted_at', '<=', $now->toDateTimeString())
            ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
            ->select('a.id', 'a.routine_item_id', 'a.submitted_at',
                     'i.exam_name', 'i.exam_end_at',
                     'c.name as category_name')
            ->orderByDesc('a.submitted_at')->orderByDesc('a.id')
            ->limit(500)->get();

        $result = [];
        $seen = [];
        foreach ($rows as $row) {
            // One row per submitted exam; if multiple attempts exist, newest wins.
            $examId = (int) $row->routine_item_id;
            if (isset($seen[$examId])) continue;
            $seen[$examId] = true;
            $submitted = Carbon::parse($row->submitted_at, 'Asia/Dhaka');
            $end = $row->exam_end_at
                ? Carbon::parse($row->exam_end_at, 'Asia/Dhaka') : null;
            $result[] = [
                'id' => $examId,
                'title' => $row->exam_name,
                'subtitle' => $row->category_name,
                'submitted' => $submitted->locale('bn')->translatedFormat('d F Y, h:i A'),
                'sort' => $submitted->timestamp,
                // This is exam end date, NOT an invented publication timestamp.
                'exam_end' => $end ? $end->locale('bn')->translatedFormat('d F Y, h:i A') : null,
                'status' => 'pending',
            ];
        }
        return $result;
    }

    private static function readScheduledResult(array $source, int $studentId, Carbon $now, Carbon $futureEnd): array
    {
        $table = $source['table'] ?? null;
        $date = $source['scheduled_at'] ?? null;
        $student = $source['student_id'] ?? null;
        $title = $source['title'] ?? null;
        $status = $source['status'] ?? null;
        $scheduledValue = $source['scheduled_value'] ?? null;

        // Strict mapping: only a verified authorized student-scoped scheduling source.
        if (!$table || !$date || !$student || !$title || !$status || $scheduledValue === null || !Schema::hasTable($table)) {
            return [];
        }
        $cols = Schema::getColumnListing($table);
        foreach ([$date, $student, $title, $status] as $col) {
            if (!in_array($col, $cols, true)) return [];
        }
        return DB::table($table)
            ->where($student, $studentId)
            ->where($status, $scheduledValue)
            ->where($date, '>', $now->toDateTimeString())
            ->where($date, '<=', $futureEnd->toDateTimeString())
            ->select([$date.' as feed_date', $title.' as feed_title'])
            ->orderBy($date, 'asc')->limit(500)->get()->all();
    }

    private static function read(array $source, int $studentId, string $dateKey, Carbon $from, Carbon $to, bool $future): array
    {
        $table = $source['table'] ?? null;
        $date = $source[$dateKey] ?? null;
        $student = $source['student_id'] ?? null;
        $title = $source['title'] ?? null;
        if (!$table || !$date || !$student || !$title || !Schema::hasTable($table)) return [];
        $cols = Schema::getColumnListing($table);
        foreach ([$date,$student,$title] as $col) if (!in_array($col,$cols,true)) return [];
        $q = DB::table($table)->where($student,$studentId)
            ->where($date, $future ? '>=' : '>=', $from->toDateTimeString())
            ->where($date, '<', $to->toDateTimeString());
        // Only explicitly confirmed published results are exposed, never drafts.
        if (!$future) {
            $status = $source['status'] ?? null;
            if (!$status || !in_array($status,$cols,true) || !isset($source['published_value'])) return [];
            $q->where($status, $source['published_value']);
        } elseif (isset($source['status'], $source['published_value']) && in_array($source['status'],$cols,true)) {
            $q->where($source['status'], $source['published_value']);
        }
        $select = [$date.' as feed_date',$title.' as feed_title'];
        foreach (['subtitle','score','total','rank'] as $key) {
            if (isset($source[$key]) && in_array($source[$key],$cols,true)) $select[]=$source[$key].' as feed_'.$key;
        }
        return $q->select($select)->orderBy($date,$future?'asc':'desc')->limit(500)->get()->all();
    }
}
