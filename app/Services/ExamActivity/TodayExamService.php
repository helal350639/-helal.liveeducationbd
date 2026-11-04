<?php

namespace App\Services\ExamActivity;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-only, read-only schedule listing. No student-access or exam-attempt changes.
 * Uses published routine windows, not the existence of a generated question paper.
 */
class TodayExamService
{
    public static function get(?Carbon $at = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        $now = ($at ?: Carbon::now('Asia/Dhaka'))->copy()->timezone('Asia/Dhaka');
        // Date filter is inclusive; do not change the status clock used for Live/Completed.
        $date = $now->toDateString();
        $from = $fromDate ?: $date;
        $to = $toDate ?: $date;
        // Guard against malformed or reversed bounds, without silently widening scope.
        foreach ([$from, $to] as $bound) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $bound) ||
                !Carbon::createFromFormat('!Y-m-d', $bound, 'Asia/Dhaka') ||
                Carbon::createFromFormat('!Y-m-d', $bound, 'Asia/Dhaka')->format('Y-m-d') !== $bound) {
                throw new \InvalidArgumentException('Invalid Exam Activity date filter.');
            }
        }
        if ($from > $to || Carbon::parse($from, 'Asia/Dhaka')->diffInDays(Carbon::parse($to, 'Asia/Dhaka')) > 365) {
            throw new \InvalidArgumentException('Exam Activity date range must be within 366 days.');
        }
        $rows = ['batch' => [], 'exam_section' => []];

        if (Schema::hasTable('routines')) {
            // Original batch routine items.
            if (Schema::hasTable('routine_item_rows')) {
                $items = DB::table('routine_item_rows as i')
                    ->join('routines as r', 'r.id', '=', 'i.routine_id')
                    ->leftJoin('batches as b', 'b.id', '=', 'r.batch_id')
                    ->whereNotNull('r.batch_id')->where('r.status', 'Published')
                    ->whereBetween('i.exam_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                    ->select('r.id as routine_id', 'r.name as routine_name', 'r.batch_id',
                        'b.name as batch_name', 'i.id', 'i.paper_id', 'i.paper_name as exam_name',
                        'i.exam_at as starts_at', 'i.exam_end_at as ends_at')
                    ->get();
                foreach ($items as $item) {
                    $rows['batch'][] = array_merge(self::format($item, 'batch', $now), ['is_derived' => false]);
                }
            }
            // Derived exams can exist without a routine_item_rows entry.
            if (Schema::hasTable('routine_derived_exam_items')) {
                $items = DB::table('routine_derived_exam_items as i')
                    ->join('routines as r', 'r.id', '=', 'i.routine_id')
                    ->leftJoin('batches as b', 'b.id', '=', 'r.batch_id')
                    ->whereNotNull('r.batch_id')->where('r.status', 'Published')
                    ->whereBetween('i.exam_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                    ->select('r.id as routine_id', 'r.name as routine_name', 'r.batch_id',
                        'b.name as batch_name', 'i.id', 'i.exam_name',
                        'i.exam_at as starts_at', 'i.exam_end_at as ends_at')
                    ->get();
                foreach ($items as $item) {
                    $rows['batch'][] = array_merge(self::format($item, 'batch', $now), ['is_derived' => true]);
                }
            }
        }

        if (Schema::hasTable('lebd_exam_section_routines') && Schema::hasTable('lebd_exam_section_routine_items')) {
            $items = DB::table('lebd_exam_section_routine_items as i')
                ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
                ->leftJoin('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id')
                ->where('r.status', 'Published')->where('i.type', 'exam')
                ->whereBetween('i.exam_start_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->select('r.id as routine_id', 'r.name as routine_name',
                    'c.name as category_name', 'i.id', 'i.exam_name',
                    'i.exam_start_at as starts_at', 'i.exam_end_at as ends_at')
                ->get();
            foreach ($items as $item) {
                $rows['exam_section'][] = self::format($item, 'exam_section', $now);
            }
        }

        foreach ($rows as $kind => $list) {
            // Deduplicate different batch source tables representing the same exam.
            $seen = [];
            $unique = [];
            foreach ($list as $item) {
                $key = $item['routine_id'].'|'.mb_strtolower(trim($item['exam'])).'|'.$item['start_raw'];
                if (isset($seen[$key])) {
                    // When one source has an end time, retain its richer row.
                    if ($unique[$seen[$key]]['end_raw'] === null && $item['end_raw'] !== null) {
                        $unique[$seen[$key]] = $item;
                    }
                    continue;
                }
                $seen[$key] = count($unique);
                $unique[] = $item;
            }
            usort($unique, fn ($a, $b) => strcmp($a['start_raw'] ?? '', $b['start_raw'] ?? ''));
            $rows[$kind] = array_map(
                fn ($row) => array_merge($row, ExamQuestionStatus::forRow($kind, $row)),
                $unique
            );
        }
        $all = array_merge($rows['batch'], $rows['exam_section']);
        return [
            'batch' => $rows['batch'],
            'exam_section' => $rows['exam_section'],
            'total' => count($all),
            'live' => count(array_filter($all, fn ($i) => $i['status'] === 'Live')),
            'completed' => count(array_filter($all, fn ($i) => $i['status'] === 'Completed')),
        ];
    }

    private static function format(object $item, string $source, Carbon $now): array
    {
        // Routine datetime columns are local application wall-clock times.
        $start = $item->starts_at ? Carbon::parse($item->starts_at, 'Asia/Dhaka') : null;
        $end = $item->ends_at ? Carbon::parse($item->ends_at, 'Asia/Dhaka') : null;
        $status = 'Time Missing';
        if ($start) {
            $status = $now->lt($start) ? 'Upcoming'
                : (!$end ? 'End Unknown' : ($now->lt($end) ? 'Live' : 'Completed'));
        }
        return [
            'source' => $source,
            'routine_id' => (int) $item->routine_id,
            'item_id' => (int) $item->id,
            'batch_id' => (int) ($item->batch_id ?? 0),
            'paper_id' => (int) ($item->paper_id ?? 0),
            'routine' => (string) ($item->routine_name ?? ''),
            'group' => (string) (($source === 'batch' ? ($item->batch_name ?? null) : ($item->category_name ?? null)) ?: ($item->routine_name ?? '—')),
            'exam' => (string) ($item->exam_name ?? '—'),
            'start_raw' => $start ? $start->format('Y-m-d H:i:s') : null,
            'end_raw' => $end ? $end->format('Y-m-d H:i:s') : null,
            'start' => $start ? $start->format('d M Y h:i A') : '—',
            'end' => $end ? $end->format('d M Y h:i A') : '—',
            'status' => $status,
        ];
    }
}
