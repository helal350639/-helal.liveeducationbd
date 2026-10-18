<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Batch;
use Carbon\Carbon;

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
     * Display the currently running batches for one course.
     * IMPORTANT: Don't render homepage.job-preparation.batch here. That view
     * is for a quiz and expects $quiz, not the $course/$batches payload.
     */
    public function batches($course_id)
    {
        $course = Course::where('status', 'Active')
            ->where('web', 'Yes')
            ->findOrFail($course_id);

        $today = Carbon::today('Asia/Dhaka')->toDateString();

        $batches = Batch::where('status', 'Active')
            ->where('course_id', $course->id)
            ->whereDate('start_at', '<=', $today)
            ->whereDate('end_at', '>=', $today)
            ->orderBy('start_at', 'ASC')
            ->get();

        return view('homepage.course-batches.index', compact('course', 'batches'));
    }

    /** Display all currently running batches (existing registration filter). */
    public function runningBatches()
    {
        $today = Carbon::today('Asia/Dhaka')->toDateString();

        $batches = Batch::with('course')
            ->where('status', 'Active')
            ->whereDate('start_at', '<=', $today)
            ->whereDate('end_at', '>=', $today)
            ->whereNotNull('reg_end_at')
            ->whereDate('reg_end_at', '>=', $today)
            ->orderBy('start_at', 'ASC')
            ->get();

        return view('homepage.job-preparation.running-batches', compact('batches'));
    }
}
