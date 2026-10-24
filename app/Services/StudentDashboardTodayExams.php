<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Exam Section live-paper feed for the existing student dashboard. */
final class StudentDashboardTodayExams
{
    public static function forStudent($student = null): array
    {
        foreach (['lebd_exam_section_routines', 'lebd_exam_section_routine_items', 'lebd_exam_section_categories'] as $table) {
            if (!Schema::hasTable($table)) return [];
        }

        $now = Carbon::now('Asia/Dhaka');
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
            ->where('i.exam_start_at', '<=', $now->format('Y-m-d H:i:s'))
            ->where('i.exam_end_at', '>', $now->format('Y-m-d H:i:s'))
            ->select('i.id', 'i.exam_name', 'i.exam_start_at', 'i.exam_end_at',
                'r.name as routine_name', 'r.category_id', 'r.subcategory_id',
                'c.name as category_name', 's.name as subcategory_name')
            ->orderBy('i.exam_end_at')->orderBy('i.id')->get();

        $urlPrefix = '/lebd-exam-take/';
        $list = [];
        foreach ($rows as $row) {
            $start = Carbon::parse($row->exam_start_at, 'Asia/Dhaka');
            $end = Carbon::parse($row->exam_end_at, 'Asia/Dhaka');
            // Defensively recheck the clock; a category can be updated during the request.
            if (!$now->betweenIncluded($start, $end) || $now->equalTo($end)) continue;
            $seconds = max(0, $now->diffInSeconds($end, false));
            $minutes = intdiv((int)$seconds, 60);
            $batchLabel = $row->category_name;
            if ($row->subcategory_name) $batchLabel .= ' > ' . $row->subcategory_name;

            // The endpoint is protected by the existing student exam admission checks;
            // listing a link never grants access to paid/unpublished/unenrolled papers.
            $list[] = [
                'id' => (int) $row->id,
                'batch' => $batchLabel,
                'name' => $row->exam_name,
                'status' => 'live',
                'status_label' => 'চলমান',
                'start_ms' => $start->timestamp * 1000,
                'end_ms' => $end->timestamp * 1000,
                'start_label' => $start->format('d M Y h:i A'),
                'end_label' => $end->format('d M Y h:i A'),
                'timer_label' => 'সময় বাকি',
                'timer_value' => sprintf('%02d:%02d:%02d', intdiv($minutes, 60), $minutes % 60, (int)$seconds % 60),
                'question_count' => null,
                'can_open' => true,
                'info_url' => url($urlPrefix . (int)$row->id),
                'result_url' => null,
            ];
        }
        return $list;
    }
}
