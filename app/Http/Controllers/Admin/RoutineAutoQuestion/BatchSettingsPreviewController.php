<?php

namespace App\Http\Controllers\Admin\RoutineAutoQuestion;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BatchSettingsPreviewController extends Controller
{
    public function show(int $courseId, int $batchId)
    {
        $course = DB::table('courses')->where('id', $courseId)->first();
        abort_unless($course, 404);

        $now = Carbon::now('Asia/Dhaka');
        $batch = DB::table('batches')
            ->where('id', $batchId)
            ->where('course_id', $courseId)
            ->where('status', 'Active')
            ->where('start_at', '<=', $now->toDateTimeString())
            ->where('end_at', '>=', $now->toDateTimeString())
            ->first();
        abort_unless($batch, 404);

        return view('admin.routine-auto-question.batch-settings-preview',
            compact('course', 'batch'));
    }
}
