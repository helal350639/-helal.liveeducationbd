<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Department;
use App\Models\Paper;
use App\Models\Syllabus;
use App\Models\Exam;
use App\Models\Routine;
use App\Models\RoutineSubjectColumn;
use App\Models\RoutineDepartmentColumn;
use App\Models\RoutineItemRow;
use Auth;

class CategoryCtrl extends Controller
{
  // courses for showing on the homepage
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

  // this batches for [exam, result, exam_pdf...] showing on the homepage everywhere 
  public function batches($type, $dashboard = null)
  {
    $student = Auth::guard('student')->user();
    // dd($dashboard);
    // dd($type);
    $batches = Batch::orderBy('batches.id', 'DESC')
    ->where('batches.status', 'Active');

    // batch query filter for 'Free Exams' & 'Premium Exams'
    if($type == 'Free Exams' || $type == 'Premium Exams')
    {
      $batches = $batches->leftJoin('papers', 'batches.id', 'papers.batch_id')
      ->whereRaw('DATE(batches.end_at) > ?', date('Y-m-d H:i:s'))
      ->whereIn('papers.status', ['Published', 'Scheduled']);

      if($type == 'Premium Exams')
      {
        $batches = $batches->whereNot('papers.permit', 'Every One');
      }

      if($type == 'Free Exams')
      {
        $batches = $batches->where('papers.permit', 'Every One');
      }

      if($dashboard == 'Dashboard' && $student)
      {
        $student_batches = $student->batches->pluck('id')->toArray();
        $batches = $batches->whereIn('batches.id', $student_batches);        
      }

      $batches = $batches->groupBy('batches.id', 'batches.name', 'batches.banner')
      ->select('batches.id', 'batches.name', 'batches.banner');
    }
    
    // batches filter query for 'Premium Results' & 'Free Results'
    if($type == 'Premium Results' || $type == 'Free Results')
    {
      $batches = $batches->leftJoin('papers', 'batches.id', 'papers.batch_id')
      ->leftJoin('exams', 'exams.paper_id', 'papers.id')
      ->where('exams.status', 'Completed');

      if($type == 'Premium Results')
      {
        $batches = $batches->whereNot('papers.permit', 'Every One');
      }

      if($type == 'Free Results')
      {
        $batches = $batches->where('papers.permit', 'Every One');
      }

      $batches = $batches->groupBy('batches.id', 'batches.name', 'batches.banner')
      ->select('batches.id', 'batches.name', 'batches.banner');
    }

    // batch query for exam pdf
    if($type == 'Exam PDF')
    {
      $batches = $batches->leftJoin('syllabi', 'syllabi.batch_id', 'batches.id');

      if($dashboard == 'Dashboard' && $student)
      {
        $student_batches = $student->batches->pluck('id')->toArray();
        $batches = $batches->whereIn('batches.id', $student_batches);        
      }

      $batches = $batches->where('syllabi.status', 'Published')
      ->groupBy('batches.id', 'batches.name', 'batches.banner')
      ->select('batches.id', 'batches.name', 'batches.banner');
    }

    //
    if($type == 'Exam Routine')
    {
      $batches = $batches->leftJoin('routines', 'routines.batch_id', 'batches.id');

      if($dashboard == 'Dashboard' && $student)
      {
        $student_batches = $student->batches->pluck('id')->toArray();
        $batches = $batches->whereIn('batches.id', $student_batches);        
      }

      $batches = $batches->whereNotNull('routines.batch_id')
      ->where('routines.status', 'Published')
      ->groupBy('batches.id', 'batches.name', 'batches.banner', 'routines.batch_id')
      ->select('batches.id', 'batches.name', 'batches.banner', 'routines.batch_id');
    }

    //finally execute the query
    $batches = $batches->get();
    // dd($batches);

    return view('homepage.batch', compact('type', 'batches', 'dashboard'));

  }

  // batches query according to the course id
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

  // department query for polytechnic study and where need directly call to the departments
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

  // department query according to the batch id
  public function department($type, $batch_id, $dashboard = null)
  {
    $student = Auth::guard('student')->user();
    // dd($dashboard);
    $batch = Batch::find($batch_id);

    $departments = $batch->departments()
    ->where('departments.status', 'Active');

    if($type == 'Free Exams' || $type == 'Premium Exams')
    {
      $departments = $departments->leftJoin('papers', 'departments.id', 'papers.department_id')

      ->where('papers.batch_id', $batch->id)
      // ->whereRaw('DATE(open) < ?', date('Y-m-d H:i:s'))
      // ->whereRaw('DATE(papers.close) > ?', date('Y-m-d H:i:s'))
      ->whereIn('papers.status', ['Published', 'Scheduled']);

      if($type == 'Premium Exams')
      {
        $departments = $departments->whereNot('papers.permit', 'Every One');
      }

      if($type == 'Free Exams')
      {
        $departments = $departments->where('papers.permit', 'Every One');
      }

      if($dashboard == 'Dashboard' && $student)
      {
        $student_departments = $student->departments->pluck('id')->toArray();
        $departments = $departments->whereIn('departments.id', $student_departments);        
      }

      $departments = $departments->groupBy('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id')
      ->select('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id');
    }

    // department query for the 'Free Results' & 'Premium Results'
    if($type == 'Free Results' || $type == 'Premium Results')
    {
      $departments = $departments->leftJoin('papers', 'departments.id', 'papers.department_id')
      ->leftJoin('exams', 'exams.paper_id', 'papers.id')
      ->where('papers.batch_id', $batch->id)
      ->where('exams.status', 'Completed');

      if($dashboard == 'Dashboard' && $student)
      {
        $student_departments = $student->departments->pluck('id')->toArray();
        $departments = $departments->whereIn('departments.id', $student_departments);        
      }

      if($type == 'Premium Results')
      {
        $departments = $departments->whereNot('papers.permit', 'Every One');
      }

      if($type == 'Free Results')
      {
        $departments = $departments->where('papers.permit', 'Every One');
      }

      $departments = $departments->groupBy('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id')
      ->select('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id');
    }

    if($type == 'Exam PDF')
    {
      $departments = $departments->leftJoin('syllabi', 'syllabi.department_id', 'departments.id');

      if($dashboard == 'Dashboard' && $student)
      {
        $student_departments = $student->departments->pluck('id')->toArray();
        $departments = $departments->whereIn('departments.id', $student_departments);
      }

      $departments = $departments->where('syllabi.status', 'Published')
      ->groupBy('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id')
      ->select('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id');
    }

    if($type == 'Exam Routine')
    {
      $departments = $departments->leftJoin('routines', 'routines.department_id', 'departments.id');


      if($dashboard == 'Dashboard' && $student)
      {
        $student_departments = $student->departments->pluck('id')->toArray();
        $departments = $departments->whereIn('departments.id', $student_departments);
      }

      $departments = $departments->where('routines.status', 'Published')
      ->groupBy('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id')
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

    if($type == 'Premium Results' || $type == 'Free Results')
    {
      return $this->result($type, $batch_id, $department_id);
    }

    if($type == 'Exam Routine')
    {
      return $this->routine($type, $batch_id, $department_id);
    }

    return $type.': Under development';
  }

  public function resultOption()
  {
    return view('homepage.result-option');
  }

  //
  public function syllabus($type, $batch_id, $department_id)
  {
    $department = Department::find($department_id);
    $syllabuses = Syllabus::orderBy('id', 'DESC')
    ->where('status', 'Published')
    ->where('batch_id', $batch_id)
    ->where('department_id', $department_id)
    ->get();

    return view('homepage.exam-pdf', compact('syllabuses', 'department'));
  }

  public function routine($type, $batch_id, $department_id)
  {
    $batch = Batch::find($batch_id);
    $routine = Routine::where('batch_id', $batch_id)
    ->where('department_id', $department_id)
    ->orderBy('id', 'DESC')
    ->where('status', 'Published')
    ->first();

    $subjects = $departments = $routine_rows = [];

    if($routine)
    {
      $subjects = $routine->subjects()->get();
  
      $departments = $routine->departments()->get();

      $routine_rows = RoutineItemRow::where('routine_item_rows.routine_id', $routine->id)
      ->with(['subjects', 'departments'])
      ->select('routine_item_rows.*')
      ->get();
    }

    // dd($routine);

    return view('homepage.exam-routine-view', compact('batch', 'routine', 'subjects', 'departments', 'routine_rows'));
  }

  public function paper($type, $batch_id, $department_id)
  {
    $batch = Batch::find($batch_id);
    $department = Department::find($department_id);
    $papers = Paper::orderBy('open', 'ASC')
    ->where('batch_id', $batch_id)
    ->where('department_id', $department_id)
    ->whereIn('status', ['Published', 'Scheduled']);
    // ->whereRaw('DATE(open) <= ?', date('Y-m-d H:i:s'))
    // ->whereRaw('DATE(close) >= ?', date('Y-m-d H:i:s'));

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

  public function result($type, $batch_id, $department_id)
  {
    $batch = Batch::find($batch_id);
    $department = Department::find($department_id);
    $results = Paper::where('exams.status', 'Completed')
    ->leftJoin('exams', 'papers.id', 'exams.paper_id')
    ->where('papers.batch_id', $batch_id)
    ->orderBy('papers.open', 'ASC');

    if($type == 'Premium Results')
    {
      $results = $results->whereNot('papers.permit', 'Every One');
    }
    elseif($type == 'Free Results')
    {
      $results = $results->where('papers.permit', 'Every One');
    }

    $results = $results->where('papers.department_id', $department_id)
    ->groupBy('papers.id', 'papers.name')
    ->select('papers.id', 'papers.name');
    $results = $results->get();

    // dd($results);

    return view('homepage.result', compact('type', 'batch', 'department', 'results'));
  }
}