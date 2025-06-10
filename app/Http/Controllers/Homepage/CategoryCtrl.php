<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Department;
use App\Models\Paper;
use App\Models\Syllabus;

class CategoryCtrl extends Controller
{
  public function courses($type)
  {
    $title = '';
    
    if($type == 'job')
    {
      $title = 'Job Preparation';
    }
    else
    {
      $title = $type;
    }

    $courses = Course::where('status', 'Active')    
    ->whereRaw('DATE(reg_end_at) > ?', date('Y-m-d H:i:s'))
    ->get();
    
    return view('homepage.course', compact('type', 'title', 'courses'));
  }

  public function batches($type)
  {
    // dd($type);
    $batches = Batch::where('batches.status', 'Active');

    if($type == 'Free Exams' || $type == 'Premium Exams')
    {
      $batches = $batches->withCount([
        'papers as items_count' => function ($query) use ($type)
        {
          if($type == 'Premium Exams')
          {
            $query->whereNot('permit', 'Every One');
          }

          if($type == 'Free Exams')
          {
            $query->where('permit', 'Every One');
          }

          // ->whereRaw('DATE(open) < ?', date('Y-m-d H:i:s'))
          $query->whereRaw('DATE(close) > ?', date('Y-m-d H:i:s'))
          ->whereIn('status', ['Published', 'Scheduled']);
        }
        ]);
    }
    
    if($type == 'premium_result')
    {
      $type = 'Premium Result';
    }

    if($type == 'free_result')
    {
      $type = 'Free Result';
    }
    $batches = $batches->get();
    // dd($batches);

    return view('homepage.batch', compact('type', 'batches'));

  }

  public function batch($type, $course_id)
  {
    $course = Course::find($course_id);
    $batches = Batch::where('status', 'Active')
    ->where('course_id', $course_id)
    ->get();

    if($type == 'job')
    {
      return view('homepage.job-batch', compact('type', 'course', 'batches'));
    }
    
    return view('homepage.batch', compact('type', 'course', 'batches'));
  }

  public function departments($type)
  {
    $title = '';

    if($type == 'Polytechnic'){
      $title = 'Polytechnic Study';
    }

    $departments = Department::all();
    return view('homepage.polytechnic-department', compact('type', 'title', 'departments'));
  }

  public function semester($type)
  {
    $semesters = Semester::all();
    return view('homepage.polytechnic-semester', compact('type', 'semesters'));
  }

  public function department($type, $batch_id)
  {
    // dd($type);
    $batch = Batch::find($batch_id);

    $departments = $batch->departments();

    if($type == 'Free Exams' || $type == 'Premium Exams')
    {
      $departments = $departments->withCount([
        'papers as items_count' => function ($query) use ($batch, $type)
        {
          if($type == 'Premium Exams')
          {
            $query->whereNot('permit', 'Every One');
          }

          if($type == 'Free Exams')
          {
            $query->where('permit', 'Every One');
          }

          $query->where('batch_id', $batch->id)
          // ->whereRaw('DATE(open) < ?', date('Y-m-d H:i:s'))
          ->whereRaw('DATE(close) > ?', date('Y-m-d H:i:s'))
          ->whereIn('status', ['Published', 'Scheduled']);
        }
        ]);
    }

    $departments = $departments->where('status', 'Active')
    ->get();

    // dd($departments);
    
    return view('homepage.department', compact('type', 'batch', 'departments'));
  }

  public function checkMethod($type, $batch_id, $department_id)
  {
    if($type == 'Exam PDF')
    {
      return $this->syllabus($type, $batch_id, $department_id);
    }

    if($type == 'Premium Exams' || $type == 'Free Exams')
    {
      return $this->paper($type, $batch_id, $department_id);
    }

    return $type.': Under development';
  }

  public function resultOption()
  {
    return view('homepage.result-option');
  }

  public function paper($type, $batch_id, $department_id)
  {
    $batch = Batch::find($batch_id);
    $department = Department::find($department_id);
    $papers = Paper::orderBy('id', 'DESC')
    ->where('batch_id', $batch_id)
    ->where('department_id', $department_id)
    ->whereIn('status', ['Published', 'Scheduled'])
    // ->whereRaw('DATE(open) <= ?', date('Y-m-d H:i:s'))
    ->whereRaw('DATE(close) >= ?', date('Y-m-d H:i:s'));

    if($type == 'Premium Exams')
    {
      $papers = $papers->whereNot('permit', 'Every One');
    }

    if($type == 'Free Exams')
    {
      $papers = $papers->where('permit', 'Every One');
    }
    
    $papers = $papers->get();
    
    return view('homepage.paper', compact('type', 'batch', 'department', 'papers'));
  }

  //
  public function syllabus($type, $batch_id, $department_id)
  {
    $department = Department::find($department_id);
    $syllabuses = Syllabus::orderBy('id', 'DESC')
    ->where('department_id', $department_id)
    ->get();

    return view('homepage.exam-pdf', compact('syllabuses', 'department'));
  }
}