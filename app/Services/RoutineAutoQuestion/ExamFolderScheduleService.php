<?php

namespace App\Services\RoutineAutoQuestion;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExamFolderScheduleService
{
    /** Resolve schedule for routine_exams records, keyed by routine-exam ID. Read-only. */
    public function forExams($exams): array
    {
        if ($exams->isEmpty()) return [];

        $routineIds = $exams->pluck('routine_id')->unique()->values()->all();
        $names = $exams->pluck('exam_name')->unique()->values()->all();

        $mainRows = DB::table('routine_item_rows')
            ->whereIn('routine_id', $routineIds)->whereIn('paper_name', $names)
            ->orderByDesc('id')->get(['routine_id','paper_name','exam_at','exam_end_at']);
        $derivedRows = DB::table('routine_derived_exam_items')
            ->whereIn('routine_id', $routineIds)->whereIn('exam_name', $names)
            ->orderByDesc('id')->get(['routine_id','exam_name','exam_at','exam_end_at']);

        $main = [];
        foreach ($mainRows as $row) {
            $key = $row->routine_id . ':' . $row->paper_name;
            if (!isset($main[$key])) $main[$key] = $row;
        }
        $derived = [];
        foreach ($derivedRows as $row) {
            $key = $row->routine_id . ':' . $row->exam_name;
            if (!isset($derived[$key])) $derived[$key] = $row;
        }

        $now = Carbon::now('Asia/Dhaka');
        $result = [];
        foreach ($exams as $exam) {
            $key = $exam->routine_id . ':' . $exam->exam_name;
            $isDerived = preg_match('/^(revision|revition|model)/i', trim($exam->exam_name)) === 1;
            $row = $isDerived ? ($derived[$key] ?? null) : ($main[$key] ?? null);
            $start = $row && $row->exam_at ? Carbon::parse($row->exam_at, 'Asia/Dhaka') : null;
            $end = $row && $row->exam_end_at ? Carbon::parse($row->exam_end_at, 'Asia/Dhaka') : null;
            $status = 'Not Scheduled';
            if ($start && $end && $end->lt($start)) {
                $status = 'Invalid Schedule';
            } elseif ($start && $end) {
                $status = $now->lt($start) ? 'Scheduled' : ($now->gt($end) ? 'Ended' : 'Published');
            } elseif ($start) {
                $status = $now->lt($start) ? 'Scheduled' : 'End Not Set';
            }
            $result[(int)$exam->exam_id] = [
                'start' => $start ? $start->format('d M Y, h:i A') : 'Not scheduled',
                'end' => $end ? $end->format('d M Y, h:i A') : 'Not scheduled',
                'status' => $status,
            ];
        }
        return $result;
    }
}
