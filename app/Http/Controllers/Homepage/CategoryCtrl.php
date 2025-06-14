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
    ->where('web', 'Yes')
    ->get();
    
    return view('homepage.course', compact('type', 'title', 'courses'));
  }

  public function batches($type)
  {
    // dd($type);
    $batches = Batch::orderBy('batches.id', 'DESC')
    ->where('batches.status', 'Active');

    if($type == 'Free Exams' || $type == 'Premium Exams')
    {
      $batches = $batches->leftJoin('papers', 'batches.id', 'papers.batch_id')
      ->whereRaw('DATE(papers.close) > ?', date('Y-m-d H:i:s'))
      ->whereIn('papers.status', ['Published', 'Scheduled']);

      if($type == 'Premium Exams')
      {
        $batches = $batches->whereNot('papers.permit', 'Every One');
      }

      if($type == 'Free Exams')
      {
        $batches = $batches->where('papers.permit', 'Every One');
      }

      $batches = $batches->groupBy('batches.id', 'batches.name', 'batches.banner')
      ->select('batches.id', 'batches.name', 'batches.banner');
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
    ->whereRaw('DATE(reg_end_at) > ?', date('Y-m-d H:i:s'))
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

    $departments = $batch->departments()
    ->where('departments.status', 'Active');

    if($type == 'Free Exams' || $type == 'Premium Exams')
    {
      $departments = $departments->leftJoin('papers', 'departments.id', 'papers.department_id')

      ->where('papers.batch_id', $batch->id)
      // ->whereRaw('DATE(open) < ?', date('Y-m-d H:i:s'))
      ->whereRaw('DATE(papers.close) > ?', date('Y-m-d H:i:s'))
      ->whereIn('papers.status', ['Published', 'Scheduled']);

      if($type == 'Premium Exams')
      {
        $departments = $departments->whereNot('papers.permit', 'Every One');
      }

      if($type == 'Free Exams')
      {
        $departments = $departments->where('papers.permit', 'Every One');
      }

      $departments = $departments->groupBy('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id')
      ->select('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id');
    }

    $departments = $departments->get();

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