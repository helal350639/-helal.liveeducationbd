<?php

namespace App\Http\Controllers\Admin\ExamActivity;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AutoExamOverviewController extends Controller
{
    /** Read-only list of routine exams for the existing admin dashboard. */
    public function index()
    {
        $batchExams = DB::table('routine_derived_exam_items as e')
            ->join('routines as r', 'r.id', '=', 'e.routine_id')
            ->leftJoin('batches as b', 'b.id', '=', 'r.batch_id')
            ->whereNotNull('r.batch_id')
            ->where('r.status', 'Published')
            ->select('e.id', 'e.exam_name', 'e.exam_type', 'e.exam_at as starts_at',
                'e.exam_end_at as ends_at', 'r.name as routine_name', 'b.name as batch_name')
            ->orderBy('e.exam_at')
            ->get();

        $examSectionExams = DB::table('lebd_exam_section_routine_items as e')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'e.routine_id')
            ->leftJoin('batches as b', 'b.id', '=', 'r.batch_id')
            ->where('r.status', 'Published')
            ->where('e.type', 'exam')
            ->select('e.id', 'e.exam_name', 'e.exam_start_at as starts_at',
                'e.exam_end_at as ends_at', 'r.name as routine_name',
                'r.category_id', 'r.subcategory_id', 'b.name as batch_name')
            ->orderBy('e.exam_start_at')
            ->get();

        return view('admin.exam-activity.auto-exams', compact('batchExams', 'examSectionExams'));
    }
}
