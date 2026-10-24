<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Batch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class JobPreparationCtrl extends Controller
{
    /** Display active and web-visible courses (existing behavior). */
    public function courses()
    {
        $courses = Course::where('status', 'Active')
            ->where('web', 'Yes')
            ->get();

        return view('homepage.job-preparation.course', compact('courses'));
    }

    /**
     * Single registration-availability rule shared by the course list and
     * the "all running batches" page. Class start/end dates do not decide
     * whether enrollment is open.
     */
    private function registrationOpenBatches(): Builder
    {
        $today = Carbon::today('Asia/Dhaka')->toDateString();

        return Batch::query()
            ->with('course')
            ->where('status', 'Active')
            ->whereNotNull('reg_end_at')
            ->whereDate('reg_end_at', '>=', $today);
    }

    /** Display batches whose registration is still open for one course. */
    public function batches($course_id)
    {
        $course = Course::where('status', 'Active')
            ->where('web', 'Yes')
            ->findOrFail($course_id);

        $batches = $this->registrationOpenBatches()
            ->where('course_id', $course->id)
            ->orderBy('id', 'DESC')
            ->get();

        return view('homepage.course-batches.index', compact('course', 'batches'));
    }

    /** Display every active batch with unexpired registration. */
    public function runningBatches()
    {
        $batches = $this->registrationOpenBatches()
            ->orderBy('id', 'DESC')
            ->get();

        return view('homepage.job-preparation.running-batches', compact('batches'));
    }
}
