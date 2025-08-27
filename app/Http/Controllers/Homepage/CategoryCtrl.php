<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Department;
use App\Models\Question;
use App\Models\Paper;
use App\Models\Syllabus;
use App\Models\Exam;
use App\Models\Routine;
use App\Models\RoutineSubjectColumn;
use App\Models\RoutineDepartmentColumn;
use App\Models\RoutineItemRow;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Chapter;
use Auth;
use DateTime;

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

  // recorded videos
  public function recordedVideo($type, $id)
  {
    $title = '';
    $videos = [];
    $chapter = Chapter::find($id);
    $questions = $chapter->questions()->where('type', 'Video')->get();
    // if($type == 'Subject')
    // {
    //   $subject = Subject::find($id);
    //   $videos = $subject->questions()->where('Type', 'Video')->get();
    //   $title = $subject->name; 
    // }
    // if($type == 'Semester')
    // {
    //   $semester = Semester::find($id);
    //   $videos = $semester->questions()->where('Type', 'Video')->get();
    //   $title = $semester->name;
    // }
    // if($type == 'Department')
    // {
    //   $department = Department::find($id);
    //   $videos = $department->questions()->where('Type', 'Video')->get();
    //   $title = $department->name;
    // }

    // if(empty($videos))
    // {
    //   return back();
    // }
    $title = 'Recorded Videos';

    return view('homepage.recorded-courses.video', compact('questions', 'title'));
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

    if($type == 'Recorded Courses')
    {
      // return 'This page is under development';
      return $this->courseDepartment($type, $course_id, $dashboard = null);
    }
    
    return view('homepage.batch', compact('type', 'course', 'batches'));
  }

  public function courseDepartment($type, $course_id, $dashboard = null)
  {
    $course = Course::find($course_id);
    $departments = $course->departments()->get();
    $batch = $course;
    // return 'under development';
    return view('homepage.department', compact('type', 'batch', 'departments'));
  }

  // this batches for [exam, result, exam_pdf...] showing on the homepage everywhere 
  public function batches($type, $dashboard = null)
  {
    $student = Auth::guard('student')->user();
    $batches = Batch::orderBy('batches.id', 'DESC')
    ->where('batches.status', 'Active');

    if($dashboard == 'Dashboard' && $student)
    {
      if($type != 'Free Exams' || $type != 'Free Results')
      {
        $student_batches = $student->batches->pluck('id')->toArray();
        $batches = $batches->whereIn('batches.id', $student_batches);
      }
    }

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

      $batches = $batches->groupBy('batches.id', 'batches.name', 'batches.banner')
      ->select('batches.id', 'batches.name', 'batches.banner');
    }
    
    // batches filter query for 'Premium Results' & 'Free Results'
    if($type == 'Premium Results' || $type == 'Free Results')
    {
      $batches = $batches->leftJoin('papers', 'batches.id', 'papers.batch_id')
      ->leftJoin('exams', 'exams.paper_id', 'papers.id')
      ->whereRaw('DATE(batches.end_at) > ?', date('Y-m-d H:i:s'))
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

      $batches = $batches->where('syllabi.status', 'Published')
      ->groupBy('batches.id', 'batches.name', 'batches.banner')
      ->select('batches.id', 'batches.name', 'batches.banner');
    }

    //
    if($type == 'Exam Routine')
    {
      $batches = $batches->leftJoin('routines', 'routines.batch_id', 'batches.id');

      $batches = $batches->whereNotNull('routines.batch_id')
      ->where('routines.status', 'Published')
      ->groupBy('batches.id', 'batches.name', 'batches.banner', 'routines.batch_id')
      ->select('batches.id', 'batches.name', 'batches.banner', 'routines.batch_id');
    }

    if($type == 'Recorded Courses')
    {
      $batches = $batches->whereHas('course.questions', function ($q) {
        $q->where('type', 'Video');
      })
      // ->whereHas('department.questions', function ($q) {
      //   $q->where('type', 'Video');
      // })
      ->whereRaw('DATE(end_at) > ?', date('Y-m-d H:i:s'));
    }
    //finally execute the query
    $batches = $batches->get();
    // dd($batches);

    return view('homepage.batch', compact('type', 'batches', 'dashboard'));

  }

  // department query for polytechnic study and where need directly call to the departments
  public function departments($type, $semester_id)
  {
    $title = '';

    if($type == 'Polytechnic'){
      $title = 'Polytechnic Study';
    }

    $semester = Semester::find($semester_id);

    $departments = $semester->departments()->get();
    return view('homepage.polytechnic-studies.department', compact('type', 'title', 'departments'));
  }

  public function semesters($type)
  {
    $semesters = Semester::all();
    return view('homepage.polytechnic-studies.semester', compact('type', 'semesters'));
  }

  // department query according to the batch id
  public function department($type, $batch_id, $dashboard = null)
  {
    $student = Auth::guard('student')->user();
    // dd($dashboard);
    $batch = Batch::find($batch_id);

    $departments = $batch->departments()
    ->where('departments.status', 'Active');

    if($dashboard == 'Dashboard' && $student)
    {
      $student_departments = $student->departments->pluck('id')->toArray();
      $departments = $departments->whereIn('departments.id', $student_departments);
    }

    if($type == 'Free Exams' || $type == 'Premium Exams')
    {
      $departments = $departments->leftJoin('papers', 'departments.id', 'papers.department_id')

      ->where('papers.batch_id', $batch->id)
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

    // department query for the 'Free Results' & 'Premium Results'
    if($type == 'Free Results' || $type == 'Premium Results')
    {
      $departments = $departments->leftJoin('papers', 'departments.id', 'papers.department_id')
      ->leftJoin('exams', 'exams.paper_id', 'papers.id')
      ->where('papers.batch_id', $batch->id)
      ->where('exams.status', 'Completed');

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

      $departments = $departments->where('syllabi.status', 'Published')
      ->groupBy('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id')
      ->select('departments.id', 'departments.name', 'batch_department.batch_id', 'batch_department.department_id');
    }

    if($type == 'Exam Routine')
    {
      $routine_department_ids = RoutineDepartmentColumn::groupBy('department_id')
      ->pluck('department_id')
      ->toArray();
      
      $departments = $departments->whereIn('departments.id', $routine_department_ids)
      ->select('departments.id', 'departments.name');
    }

    if($type == 'Recorded Courses')
    {
      // $departments = $departments->whereHas('departments.questions', function($q){
      //   return $q->where('type', 'Video');
      // });
      $departments = $departments->leftJoin('department_question', 'department_question.department_id', 'departments.id')
      ->leftJoin('questions', 'questions.id', 'department_question.question_id')
      ->where('questions.type', 'Video')
      ->where('questions.status', 'Active')
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

    if($type == 'Recorded Courses')
    {
      return $this->subjects($type, $batch_id, $department_id);
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
    $now = now();
    $past = new DateTime();
    $past->modify('+11 minutes');
    $now = $past->format('Y-m-d H:i:s');

    $scheduled_syllabus = Syllabus::where('status', 'Scheduled')->where('datetime', '<=', $now)->get();
    if($scheduled_syllabus)
    {
      foreach($scheduled_syllabus as $row)
      {
        Syllabus::where('id', $row->id)->update(['status' => 'Published']);
      }
    }

    $department = Department::find($department_id);
    $syllabuses = Syllabus::orderBy('id', 'ASC')
    ->whereIn('status', ['Published', 'Scheduled'])
    ->where('batch_id', $batch_id)
    ->where('department_id', $department_id)
    ->get();

    return view('homepage.exam-pdf', compact('syllabuses', 'department'));
  }

  public function routine($type, $batch_id, $department_id)
  {
    $batch = Batch::find($batch_id);
    $routine = Routine::where('batch_id', $batch_id)
    // ->where('department_id', $department_id)
    ->orderBy('id', 'DESC')
    ->where('status', 'Published')
    ->first();

    $subjects = $departments = $routine_rows = [];

    if($routine)
    {
      $subjects = $routine->subjects()->get();
  
      $departments = $routine->departments()->where('id', $department_id)->get();

      $routine_rows = RoutineItemRow::where('routine_item_rows.routine_id', $routine->id)
      ->with(['subjects', 'departments' => function($query) use($department_id){
        $query->where('department_id', $department_id);
      }])
      ->select('routine_item_rows.*')
      ->get();
    }

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

  public function subjects($type, $batch_id = null, $department_id)
  {    
      $subjects = Subject::where('status', 'Active')->get();
      return view('homepage.recorded-courses.subject', compact('subjects'));
  }

  public function chapters($type, $subject_id)
  {
    $subject = Subject::find($subject_id);
    $chapters = $subject->chapters()->get();
    return view('homepage.recorded-courses.chapter', compact('chapters'));
  }

  public function recordedCourses($type, $batch_id = null, $chapter_id)
  {
    $batch = Batch::find($batch_id);
    $chapter = Chapter::find($chapter_id);

    $questions = $chapter->questions()
    ->where('type', 'Video')
    ->where('status', 'Active')
    ->get();

    return view('homepage.recorded-courses.video', compact('questions', 'batch', 'department'));
  }
}