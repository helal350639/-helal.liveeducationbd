<?php
namespace App\Services\RoutineAutoQuestion;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BatchPaperPreviewService
{
    public function exams(int $courseId, int $batchId)
    {
        return DB::table('routines as r')
            ->join('routine_exams as e','e.routine_id','=','r.id')
            ->where('r.course_id',$courseId)->where('r.batch_id',$batchId)
            ->select('r.id as routine_id','r.name as routine_name','e.id as exam_id','e.name as exam_name','e.sort_order')
            ->orderBy('r.id')->orderBy('e.sort_order')->orderBy('e.id')->get();
    }

    public function preview(object $exam, object $settings): array
    {
        $isDerived = (bool) preg_match('/^(revition|revision|model)/i', trim($exam->exam_name));
        $item = $isDerived
            ? DB::table('routine_derived_exam_items')->where('routine_id',$exam->routine_id)->where('exam_name',$exam->exam_name)->first()
            : DB::table('routine_item_rows')->where('routine_id',$exam->routine_id)->where('paper_name',$exam->exam_name)->first();

        $next = DB::table('routine_exams')->where('routine_id',$exam->routine_id)
            ->where(function($q) use ($exam) {
                $q->where('sort_order','>', $exam->sort_order)
                  ->orWhere(function($j) use ($exam) {
                      $j->where('sort_order',$exam->sort_order)->where('id','>',$exam->exam_id);
                  });
            })->orderBy('sort_order')->orderBy('id')->first();
        $nextRow = null;
        if ($next) {
            $nextDerived = (bool) preg_match('/^(revition|revision|model)/i', trim($next->name));
            $nextRow = $nextDerived
                ? DB::table('routine_derived_exam_items')->where('routine_id',$exam->routine_id)->where('exam_name',$next->name)->first()
                : DB::table('routine_item_rows')->where('routine_id',$exam->routine_id)->where('paper_name',$next->name)->first();
        }
        $now = Carbon::now('Asia/Dhaka');
        $start = $item && $item->exam_at ? Carbon::parse($item->exam_at, 'Asia/Dhaka') : null;
        $end = $item && $item->exam_end_at ? Carbon::parse($item->exam_end_at, 'Asia/Dhaka') : null;
        $status = !$start || !$end ? 'Not Scheduled' : ($now->lt($start) ? 'Scheduled' : ($now->lte($end) ? 'Published' : 'Ended'));

        // Counts here are configured question ranges, NOT proven unique bank-question totals.
        // Derived exams inherit source exam links; actual question count needs bank resolution later.
        $counts=[];
        if (!$isDerived && $item) {
            $links=DB::table('routine_mcq_selections')->where('routine_item_row_id',$item->id)
                ->get(['column_kind','column_id','range_from','range_to','range_start','range_end']);
            foreach($links as $l) {
                $from=$l->range_from ?? $l->range_start;
                $to=$l->range_to ?? $l->range_end;
                $n=($from!==null && $to!==null && $to >= $from) ? (int)$to-(int)$from+1 : null;
                $key=$l->column_kind.'_'.$l->column_id;
                if (!isset($counts[$key])) $counts[$key]=['kind'=>$l->column_kind,'column_id'=>$l->column_id,'estimated'=>0,'unbounded'=>false];
                if ($n===null) $counts[$key]['unbounded']=true;
                else $counts[$key]['estimated']+=$n;
            }
        }
        return compact('item','next','nextRow','start','end','status','counts','isDerived');
    }
}
