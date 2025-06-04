<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Department;
use App\Models\Paper;

class ExamCtrl extends Controller
{
  public function liveCourses()
  {
    $batches = Batch::where('status', 'Active')
    ->where('web', 'Yes')
    ->get();

    return view('homepage.live-exams.batch', compact('batches'));
  }

  public function liveBatches($course_id)
  {
    $course = Course::find($course_id);
    $batches = Batch::where('status', 'Active')
    ->where('web', 'Yes')
    ->where('course_id', $course_id)
    ->get();
    
    return view('homepage.live-exams.batch', compact('course', 'batches'));
  }

  public function liveDepartments($batch_id)
  {
    $batch = Batch::find($batch_id);
    $departments = $batch->departments()
    ->get();

    return view('homepage.live-exams.department', compact('batch', 'departments'));
  }

  public function liveExams($department_id)
  {
    $department = Department::find($department_id);
    $papers = Paper::where('department_id', $department_id)->get();
    return view('homepage.live-exams.exam', compact('department', 'papers'));
  }

  /** --------------------------- free exam section ------------------ */

  public function freeBatches()
  {
    $batches = Batch::where('status', 'Active')
    ->where('web', 'Yes')
    ->get();
    
    return view('homepage.free-exams.batch', compact('batches'));
  }

  public function freeDepartments($batch_id)
  {
    $batch = Batch::find($batch_id);
    $departments = $batch->departments()
    ->get();

    return view('homepage.free-exams.department', compact('batch', 'departments'));
  }

  public function freeExams($department_id)
  {
    $department = Department::find($department_id);
    $papers = Paper::where('department_id', $department_id)->get();
    return view('homepage.free-exams.exam', compact('department', 'papers'));
  }
}