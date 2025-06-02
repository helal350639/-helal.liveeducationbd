<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Batch;

class JobPreparationCtrl extends Controller
{
  public function courses()
  {
    $courses = Course::where('status', 'Active')
    ->where('web', 'Yes')
    ->get();
    return view('homepage.job-preparation.course', compact('courses'));
  }

  public function batches($course_id)
  {
    $course = Course::find($course_id);
    $batches = Batch::where('status', 'Active')
    ->where('course_id', $course_id)
    ->get();
    
    return view('homepage.job-preparation.batch', compact('course', 'batches'));
  }
}