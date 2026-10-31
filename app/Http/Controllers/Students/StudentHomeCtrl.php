<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SourceCtrl;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Paper;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Student;
use App\Models\Batch;
use App\Models\Department;
use App\Models\Group;
use App\Models\McqItem;
use App\Models\Choice;
use App\Models\Syllabus;
use App\Models\Complain;
use App\Models\Order;
use App\Models\Routine;
use App\Models\Circular;
use App\Models\Referrer;
use App\Models\ReferralTransaction;
use Auth;
use Session;
use Mail;
use DB;
use \Mpdf\Mpdf;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use App\Models\DailyExam;
use Carbon\Carbon;


class StudentHomeCtrl extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:student');
  }

  public function dashboard()
  {
    $user = Auth::guard('student')->user();

    // today exam
$todayExam = DailyExam::where('status','Active')
    ->first();

    /*
|--------------------------------------------------------------------------
| Dynamic Exam Section Courses
|--------------------------------------------------------------------------
| Only courses checked by Admin will appear
| in the Student Dashboard Exam Section.
*/

$examSectionCourses = Course::where('status', 'Active')
    ->where('show_in_exam_section', 1)
    ->orderBy('name', 'ASC')
    ->get();

    // autheticates user courses
    $student = Student::find($user->id);

    // students batches array
    $student_batches_ids = $student->batches()->pluck('batch_id')->toArray();

    $student_departments_ids = $student->departments()->pluck('department_id')->toArray();

    // this sutdent courses
    $mycourses = $student->batches()->where('status', 'Active')->count();

    // free available exams
    $free_exam = Paper::whereRaw('DATE(close) > ?', date('Y-m-d'))
    ->whereIn('status', ['Published', 'Scheduled'])
    ->where('permit', 'Every One')
    ->count();

    // premium available exams
    $premium_exam = Paper::whereIn('batch_id', $student_batches_ids)
    ->whereIn('status', ['Published', 'Scheduled'])
    ->whereNot('permit', 'Every One')
    ->whereRaw('DATE(close) > ?', date('Y-m-d'))
    ->count();

    // student already completed the exams
    $exam = Exam::where('student_id', $user->id)
    ->where('status', 'Completed');
    $result = $exam->count();
    $exams = $exam->get();

    // batch routine for the student
    $routine = Routine::whereIn('batch_id', $student_batches_ids)
    ->where('status', 'Published')
    ->count();

    // batch exam pdf for the student
    $exam_pdf = Syllabus::whereIn('batch_id', $student_batches_ids)
    ->whereIn('department_id', $student_departments_ids)
    ->where('status', 'Published')
    ->count();

    $orders = Order::leftJoin('batches', 'batches.id', 'orders.batch_id')
    ->orderBy('orders.id', 'DESC')
    ->where('orders.student_id', $user->id)
    ->whereIn('orders.status', ['Processing', 'Complete'])
    ->select('orders.*', 'batches.name')
    ->get();

    $circulars = Circular::where('status', 'Published')->count();

    $courses = Batch::where('status', 'Active')
    ->whereHas('departments', function($query) use ($student_departments_ids) {
        $query->whereIn('departments.id', $student_departments_ids);
    })
    ->count();

    $total_exams = Paper::whereIn('batch_id', $student_batches_ids)
    ->whereIn('department_id', $student_departments_ids)
    ->count();
    // Referral Earn Balance

    $referralEarn = ReferralTransaction::where(
    'student_id',
    $student->id
)
->where('status','approved')
->sum('amount');

    $data = [
      'referralEarn' => $referralEarn,
      'courses' => $courses,
      'course' => $mycourses,
      'total_exams' => $total_exams,
      'myexams' => $premium_exam,
      'free_exam' => $free_exam,
      'premium_exam' => $premium_exam,
      'result' => $result,
      'routine' => $routine,
      'exam_pdf' => $exam_pdf,
      'e-book' => '0',
      'circular' => $circulars
    ];

    $references = Referrer::where('referrer_id', auth()->id())
    ->orderBy('id', 'DESC')
    ->get();

    $paper = Paper::orderBy('id', 'ASC')
    ->where('open', 'like', '%'.date('Y-m-d').'%')
    ->whereIn('batch_id', $student_batches_ids)
    ->whereIn('department_id', $student_departments_ids)
    ->whereIn('status', ['Published', 'Scheduled'])
    ->first();

    // dd($student_batches_ids);

   return view('student-panel.dashboard', compact(
    'data',
    'student',
    'orders',
    'references',
    'paper',
    'todayExam',
    'examSectionCourses'
));
  }

  public function profile()
  {
    $user = Auth::guard('student')->user();
    return view('student-panel.profile', compact('user'));
  }

  public function course()
  {
    $courses = Batch::orderBy('id', 'DESC')->where('status', 'Active')->get();
    return view('student-panel.course', compact('courses'));
  }

  public function courseShow($id)
  {
    $batch = Batch::find($id);
    $departments = $batch->departments()->where('status', 'Active')->get();
    $groups = Group::where('status', 'Active')->get();
    return view('homepage.course-show', compact('batch', 'departments', 'groups'));
  }

  public function applyCourse(Request $request)
  {
    $this->validate($request, [
      'batch_id' => 'required|numeric',
      'department_id' => 'required|numeric',
    ]);

    $data = $request->all();

    $user = Auth::guard('student')->user();
    $student = Student::find($user->id);
    $batch = Batch::find($request->batch_id);
    // for free course apply
    if($batch->price < 1)
    {
      try {
        $student->batches()->attach($data['batch_id']);
        $student->departments()->attach($data['department_id']);  
        
        Session::flash('success', 'The course successfully applied.');
        return redirect()->route('students.my-course');
      }
      catch(\Exception $e)
      {
        return $e->getMessage();
      }
    }

    if($student->courses()->find($request->course_id))
    {
      Session::flash('error', 'You already applied to the course.');
      return redirect()->route('students.my-course');
    }

    Session::put('_confirm', [
      'batch_id' => $data['batch_id'],
      'department_id' => $data['department_id'],
      'student_id' => $student->id,
    ]);

    return redirect()->route('students.course.confirm');
  }

  public function checkout($id)
  {
    //
    $batch = Batch::find($id);
    // Show only departments assigned to this batch, each once.
    // Duplicate rows in batch_department must not create duplicate checkout choices.
    $departments = $batch->departments()
        ->get()
        ->unique('id')
        ->values();
    return view('student-panel.course-checkout', compact('batch', 'departments'));
  }

  public function confirm()
  {
    if(Session::get('_confirm'))
    {
      $data = Session::get('_confirm');
      $batch = Batch::find($data['batch_id']);
      $department = Department::find($data['department_id']);
      return view('student-panel.course-confirm', compact('batch', 'department'));
    }
    return redirect()->route('students.course');
  }

  public function courseApplied($data)
  {
    try {
      $student = Student::find($data['student_id']);
      if(!$student->batches()->where('student_id', $student->id)->count())
      {
        $student->batches()->attach($data['batch_id']);
        $student->departments()->attach($data['department_id']);
      }

      return true;
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }

    return false;
  }

  public function myCourse()
{
    $user = Auth::guard('student')->user();

    $student = Student::find($user->id);


    // Student purchased batches
    $purchasedCourses = $student->batches()
        ->orderBy('id','DESC')
        ->where('status','Active')
        ->get();



    // Running batches
    $runningBatches = Batch::orderBy('id','DESC')
        ->where('status','Active')
        ->whereDate('start_at','<=',Carbon::today())
        ->whereDate('reg_end_at','>=',Carbon::now())
        ->get();



    return view('student-panel.my-course', compact(
        'purchasedCourses',
        'runningBatches'
    ));
}

  public function mySyllabus()
  {
    $user = Auth::guard('student')->user();
    $student = Student::find($user->id);
    $batches = $student->batches()->orderBy('id', 'DESC')
    ->where('status', 'Active')->get();
    return view('student-panel.my-syllabus', compact('batches'));
  }

  public function mySyllabusPost(Request $request)
  {
    $student = Auth::guard('student')->user();
    $this->Validate($request, [
      'batch_id' => 'required|numeric'
    ]);

    $student = Student::find($student->id);
    $batches = $student->batches()->orderBy('id', 'DESC')->where('status', 'Active')->get();

    $batch = Batch::find($request->batch_id);
    $batch_syllabuses = $batch->syllabuses()
    ->where('department_id', null)
    ->orderBy('id', 'DESC')
    ->get();

    $department_syllabuses = $batch->syllabuses()
    ->Join('departments', 'departments.id', 'syllabi.department_id')
    ->LeftJoin('department_student', 'department_student.department_id', 'departments.id')
    ->LeftJoin('students', 'students.id', 'department_student.student_id')
    ->where('students.id', $student->id)
    ->where('syllabi.batch_id', $request->batch_id)
    ->select('syllabi.id', 'syllabi.name', 'students.name as student_name')
    ->orderBy('syllabi.id', 'DESC')
    ->get();
    
    return view('student-panel.my-syllabus', compact('batch_syllabuses', 'department_syllabuses', 'batches', 'batch'));
  }

  public function exam()
  {
    $user = Auth::guard('student')->user();
    $papers = Paper::orderBy('id', 'DESC')->whereIn('status', ['Published', 'Scheduled'])->where('permit', 'Every One')->get();

    $batches = Batch::all();
    
    return view('student-panel.exam', compact('papers', 'batches'));
  }

  public function freeExam()
  {
    $batches = Batch::all();
    return view('student-panel.free-exam', compact('batches'));
  }

  public function batchExam($id)
  {
    $batch = Batch::find($id);
    $papers = Paper::where('batch_id', $id)->get();
    return view('student-panel.exam-batch', compact('batch', 'papers'));
  }

  public function check($id)
  {
    $user = Auth::guard('student')->user();
    $check = [];
    $exams = Exam::where('paper_id', $id)->where('student_id', $user->id)->count();
    $paper = Paper::find($id);
    
    if( $paper->status == 'Scheduled' && !is_null($paper->open) && strtotime($paper->open) > strtotime(date('Y-m-d H:i:s')) )
    {
      $check['scheduled'] = true;
    }
    elseif( $paper->status == 'Scheduled' && strtotime($paper->open) < strtotime(date('Y-m-d H:i:s')) )
    {
      Paper::where('id', $id)->update(['status' => 'Published']);
      $paper = Paper::find($id);
    }
    elseif($paper->exam_limit && $paper->exam_limit <= $exams)
    {
      return redirect()->route('students.result', $id);
    }
    elseif($paper->exam_limit && $paper->exam_limit > $exams)
    {
      $check['result-exam'] = true;
    }
    elseif($exams && is_null($paper->exam_limit))
    {
      $check['result-exam'] = true;
    }
    else{
      return redirect()->route('students.instruction', $id);
    }
    
    return view('student-panel.check', compact('paper', 'check'));
  }

  public function instruction($id)
  {
    $user = Auth::guard('student')->user();
    $paper = Paper::find($id);

    // check the user bought the batch
    $user_batch = $user->batches()->find($paper->batch_id);

    // check the user connected to the papers department
    $user_department = $user->departments()->find($paper->department_id);

    if($user_batch && $user_department || $paper->permit == 'Every One')
    {
      return view('student-panel.instruct', compact('paper'));
    }
    else
    {
      return redirect()->route('home.course.show', $paper->batch_id);
    }
  }
  /*
|--------------------------------------------------------------------------
| START ROUTINE EXAM DIRECTLY
|--------------------------------------------------------------------------
| Routine Time + Student Permission + Existing Attempt
|--------------------------------------------------------------------------
*/

public function startRoutineExam($id)
{
    $student = Auth::guard('student')->user();

    abort_unless($student, 403);

    $paper = Paper::findOrFail($id);

    $now = Carbon::now('Asia/Dhaka');

    // Check Question Paper status
    abort_unless(
        in_array($paper->status, ['Published', 'Scheduled'], true),
        403,
        'Question Paper is not available.'
    );

    // Check Routine start and end time
    $routine = DB::table('routines as r')
        ->join(
            'routine_item_rows as ri',
            'ri.routine_id',
            '=',
            'r.id'
        )
        ->where('r.course_id', $paper->course_id)
        ->where('r.status', 'Published')
        ->where('ri.paper_name', $paper->name)
        ->where('ri.exam_at', '<=', $now->toDateTimeString())
        ->where('ri.exam_end_at', '>', $now->toDateTimeString());

    if ($paper->batch_id !== null) {
        $routine->where('r.batch_id', $paper->batch_id);
    } else {
        $routine->whereNull('r.batch_id');
    }

    if ($paper->department_id !== null) {
        $departmentId = (int) $paper->department_id;

        $routine->where(function ($query) use ($departmentId) {
            $query->whereNull('r.department_id')
                ->orWhere('r.department_id', $departmentId)
                ->orWhereExists(function ($sub) use ($departmentId) {
                    $sub->selectRaw('1')
                        ->from('routine_department_columns as dc')
                        ->whereColumn('dc.routine_id', 'r.id')
                        ->where('dc.department_id', $departmentId);
                });
        });
    }

    /*
|--------------------------------------------------------------------------
| ROUTINE WINDOW + INDIVIDUAL EXAM DURATION
|--------------------------------------------------------------------------
| New attempt: Routine must be live.
| Running attempt: Student can finish their own duration.
|--------------------------------------------------------------------------
*/

$existingRunningExam = Exam::where('student_id', $student->id)
    ->where('paper_id', $paper->id)
    ->where('status', 'Live')
    ->orderByDesc('id')
    ->first();

$hasRemainingTime = false;

if ($existingRunningExam && $existingRunningExam->start_at) {

    $individualEndTime = Carbon::parse(
        $existingRunningExam->start_at,
        'Asia/Dhaka'
    )->addMinutes((int) $paper->time);

    $hasRemainingTime = $now->lessThan(
        $individualEndTime
    );
}

// New attempts require Live Routine.
// Existing attempts may continue within their own duration.

abort_unless(
    $routine->exists() || $hasRemainingTime,
    403,
    'Routine has ended and no active exam time remains.'
);

    // Check Student permission
    $allowed = match ($paper->permit) {
        'Every One' => true,

        'Batch' => $paper->batch_id !== null
            && $student->batches()
                ->where('batches.id', $paper->batch_id)
                ->exists(),

        'Department' => $paper->department_id !== null
            && $student->departments()
                ->where('departments.id', $paper->department_id)
                ->exists(),

        'Group' => $paper->group_id !== null
            && $student->groups()
                ->where('groups.id', $paper->group_id)
                ->exists(),

        default => false,
    };

    abort_unless(
        $allowed,
        403,
        'You do not have permission for this exam.'
    );

    /*
|--------------------------------------------------------------------------
| EXISTING LIVE ATTEMPT
|--------------------------------------------------------------------------
*/

$runningExam = Exam::where('student_id', $student->id)
    ->where('paper_id', $paper->id)
    ->where('status', 'Live')
    ->orderByDesc('id')
    ->first();

if ($runningExam) {

    $attemptEnd = Carbon::parse(
        $runningExam->start_at,
        'Asia/Dhaka'
    )->addMinutes((int) $paper->time);

    // Previous attempt still has time: resume it
    if ($now->lessThan($attemptEnd)) {

        return view('student-panel.exam-show', [
            'paper' => $paper,
            'exam' => $runningExam,
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| EXAM LIMIT FROM QUESTION PAPER
|--------------------------------------------------------------------------
| Count all started attempts, including expired ones.
|--------------------------------------------------------------------------
*/

$usedAttempts = Exam::where('student_id', $student->id)
    ->where('paper_id', $paper->id)
    ->count();

$examLimit = (int) ($paper->exam_limit ?? 0);

// 0 means unlimited
if ($examLimit > 0 && $usedAttempts >= $examLimit) {

    abort(
        403,
        'Your exam attempt limit has been reached.'
    );
}

/*
|--------------------------------------------------------------------------
| CREATE NEW EXAM ATTEMPT
|--------------------------------------------------------------------------
| Each new attempt gets its own duration.
|--------------------------------------------------------------------------
*/

$newExam = Exam::create([
    'student_id' => $student->id,
    'paper_id' => $paper->id,
    'start_at' => $now->toDateTimeString(),
    'status' => 'Live',
]);

return view('student-panel.exam-show', [
    'paper' => $paper,
    'exam' => $newExam,
]);
}

  public function examShow($id)
  {
    // get authenicate user
    $user = Auth::guard('student')->user();

    // get the exist exam count
    $exams = Exam::where('paper_id', $id)
    ->where('student_id', $user->id)
    ->where('status', 'Completed')
    ->count();

    // find the paper details
    $paper = Paper::find($id);

    /*
|--------------------------------------------------------------------------
| RESUME EXISTING LIVE EXAM
|--------------------------------------------------------------------------
| Same student + same paper
| Do not create another exam attempt
|--------------------------------------------------------------------------
*/

if (!$paper) {
    abort(404);
}

$runningExam = Exam::where('student_id', $user->id)
    ->where('paper_id', $paper->id)
    ->where('status', 'Live')
    ->orderByDesc('id')
    ->first();

if ($runningExam) {

    $exam = $runningExam;

    return view(
        'student-panel.exam-show',
        compact('paper', 'exam')
    );
}

    // if on the paper settings has exam limitation and exam limitation number is less than already examed count on this paper. So it will redirect to the result page. Otherwise it will redirect to the exam start page.
    if($paper->exam_limit && intval($paper->exam_limit) <= $exams)
    {
      $exam = $exams = $result = null;

      return view('student-panel.exam-show', compact('paper', 'result', 'exams', 'exam'));
    }
    else
    {
      //insert exam to the database with status 'live'
      try {
        $live_exam = Exam::create([
          'student_id' => $user->id,
          'paper_id' => $id,
          'start_at' => date('Y-m-d H:i:s'),
          'status' => 'Live',
          'created_at' => date('Y-m-d H:i:s')
        ]);
  
        if($live_exam)
        {
          // find out the last exam entry for this user
          $exam = Exam::where('paper_id', $id)
          ->where('student_id', $user->id)
          ->orderBy('id', 'DESC')
          ->first();
          
          return view('student-panel.exam-show', compact('paper', 'exam'));
        }
        else
        {
          return redirect()->route('students.instruction', $id);
        }
      }
      catch(\Exception $e)
      {
        return $e->getMessage();
      }
    }

    return back();
  }

  // return quesiton on ajax request
  public function getQuestions($paper_id)
  {
    $source = new SourceCtrl;
    // serve questions according to the paper
    $paper = Paper::find($paper_id);
    $questions = $paper->questions()->select('id', 'title')->with('mcqitems')->paginate(5);

    $success = false;
    if(!is_null($questions))
    {
      $success = true;
    }

    return response()->json(
      [
        'success' => $success,
        'questions' => $questions,
        'format' => $source->mcqlist()[$paper->format ?? 0]
      ]
    );
  }

  public function batchResult(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Old Results Page: Preserve Existing Behavior
    |--------------------------------------------------------------------------
    */

    $filtered = $request->filled('course_id')
        || $request->filled('batch_id')
        || $request->filled('department_id');

    if (!$filtered) {
        $batches = Batch::all();

        return view('student-panel.batch-result', ['batches' => $batches,'filtered' => false,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Selected Course / Batch / Department Validation
    |--------------------------------------------------------------------------
    */

    $data = $request->validate([
        'course_id' => 'required|integer|exists:courses,id',
        'batch_id' => 'nullable|integer|exists:batches,id',
        'department_id' => 'nullable|integer|exists:departments,id',
    ]);

    $course = Course::findOrFail($data['course_id']);

    abort_unless(
        $course->status === 'Active'
        && (int) $course->show_in_exam_section === 1,
        404
    );

    $batch = null;
    $department = null;

    if (!empty($data['batch_id'])) {
        $batch = Batch::where('course_id', $course->id)
            ->findOrFail($data['batch_id']);
    }

    if (!empty($data['department_id'])) {
        abort_unless($batch, 404);

        $department = $batch->departments()
            ->where('departments.id', $data['department_id'])
            ->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Only Current Student's Completed Exam Results
    |--------------------------------------------------------------------------
    */

    $studentId = Auth::guard('student')->id();

    $batchIds = $batch
        ? collect([$batch->id])
        : Batch::where('course_id', $course->id)
            ->pluck('id');

    $hasPaperCourseId = \Illuminate\Support\Facades\Schema::hasColumn(
        'papers',
        'course_id'
    );

    $results = Exam::query()
        ->with('paper')
        ->where('student_id', $studentId)
        ->where('status', 'Completed')
        ->whereHas('paper', function ($query) use (
            $course,
            $batch,
            $department,
            $batchIds,
            $hasPaperCourseId
        ) {

            if ($department) {
                $query->where('batch_id', $batch->id)
                      ->where('department_id', $department->id);

            } elseif ($batch) {
                $query->where('batch_id', $batch->id);

            } else {
                $query->where(function ($paperQuery) use (
                    $course,
                    $batchIds,
                    $hasPaperCourseId
                ) {
                    $paperQuery->whereIn('batch_id', $batchIds);

                    if ($hasPaperCourseId) {
                        $paperQuery->orWhere(
                            'course_id',
                            $course->id
                        );
                    }
                });
            }
        })
        ->orderBy('id', 'DESC')
        ->get()
        ->unique('paper_id')
        ->values();

    return view('student-panel.batch-result', [
        'filtered' => true,
        'batches' => collect(),
        'results' => $results,
        'course' => $course,
        'batch' => $batch,
        'department' => $department,
    ]);
}



  public function result($id, $value = null)
  {
    $result = [];
    $position = '';
    $user = Auth::guard('student')->user();

    $exams = Exam::orderBy('id', 'DESC')
    ->where('student_id', $user->id)
    ->where('paper_id', $id)
    ->get();

    $paper = Paper::find($id);
    if($value == 'after')
    {
      $exams = Exam::where('student_id', $user->id)->where('paper_id', $id)->orderBy('id', 'DESC')->limit(1)->get();
    }

    $allExams = Exam::orderBy('mark', 'DESC')
    ->where('paper_id', $paper->id)
    ->count();

    $candidates = 0;
    if($paper->candidate)
    {
      $candidates = $allExams + $paper->candidate;
    }
    else
    {
      $candidates = $allExams;
    }

    $passed = number_format($candidates * 80 / 100);
    
    if($paper->result_view == 'Yes' || $paper->result_at < date('Y-m-d H:i:s'))
    {
      $result['result'] = 'Yes';
    }
    else
    {
      $result['message'] = 'Yes';
    }

    return view('student-panel.result', compact('exams', 'paper', 'result', 'passed', 'candidates'));
  }

  // your exam paper
  public function examPaper($id)
  {
    $user = Auth::guard('student')->user();
    $paper = Paper::find($id);
    $exam = $paper->exams()->orderby('id', 'DESC')->where('student_id', $user->id)->first();
    $choices = Choice::where('exam_id', $exam->id)->pluck('question_id', 'mcq_id')->toArray();

    return view('student-panel.your-exam-paper', compact('paper', 'choices', 'exam'));
  }

  public function solution($id)
  {
    $paper = Paper::find($id);
    return view('student-panel.solution', compact('paper'));
  }

  public function examAdd(Request $request)
  {
    $user = Auth::guard('student')->user();
    $this->validate($request, [
      'paper_id' => 'required|numeric',
      'exam_id' => 'required|numeric',
      'question_id' => 'nullable|string',
      'mcq_id' => 'nullable|string',
    ]);

    $questions = $answered = $correct = $wrong = $no_answered = $marks = 0;
    $question_ids = [];
    $mcq_ids = [];

    //find paper
    $paper = Paper::find($request->paper_id);

    // Default result when student answers zero questions
$questions = $paper->questions()->count();
$no_answered = $questions;

    // if choice any question the do this action
    if($request->exam_id && $request->mcq_id)
    {
      $question_ids = explode(',', $request->question_id); 
      $mcq_ids = explode(',', $request->mcq_id);
      
      $dbmcqs = McqItem::whereIn('id', $mcq_ids)->whereNotNull('correct_answer')->get();

      $questions = $paper->questions()->count();
      $answered = count($question_ids);
      $correct = $dbmcqs->count();
      $wrong = $answered - $correct;
      $no_answered = $questions - $answered;
      $marks = ($paper->mark * $correct) - ($wrong * $paper->minus);

    }

    //default $result assigned by false 
    $success = false;

    try {

      if($request->exam_id)
      {
        for($x = 0; $x < count($question_ids); $x++)
        {
          Choice::insert([
            'exam_id' => $request->exam_id,
            'question_id' => $question_ids[$x],
            'mcq_id' => $mcq_ids[$x]
          ]);
        }

        $result = Exam::where('id', $request->exam_id)->update([
          'end_at'    => date('Y-m-d H:i:s'),
          'answer'    => $answered,
          'correct'   => $correct,
          'wrong'     => $wrong,
          'no_answer' => $no_answered,
          'mark'      => $marks,
          'status'    => 'Completed',
          'updated_at' => date('Y-m-d H:i:s')
        ]);

        if($result)
        {
          $success = true;
        }
      }
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }
    
    $exam = Exam::find($request->exam_id);
    if($paper->email == 'Yes')
    {
      $this->resultSendToMail($paper, $exam);
    }

    return response()->json([
      'success' => $success,
      'message' => $paper->message,
      'questions' => $questions,
      'answered' => $answered,
      'correct' => $correct,
      'wrong' => $wrong,
      'no_answered' => $no_answered,
      'marks' => $marks,
    ]);
  }

  public function resultSendToMail($paper, $exam)
  {
    $source = new SourceCtrl;
    $user = Auth::guard('student')->user();
    $data = [
      'email_from' => 'noreply@liveeducationbd.com',
      'from_name' => 'LiveEducationBD',
      'email_to' => $user->email,
      'subject' => 'Result | Live Education BD',
      'candidate' => $user->name,
      'course_name' => $exam->paper->course?$exam->paper->course->name:'',
      'exam_no' => $exam->paper->name,
      'start_at' => $source->dtformat($exam->start_at),
      'end_at' => $source->dtformat($exam->end_at),
      'answered' => $exam->answered,
      'correct' => $exam->correct,
      'wrong' => $exam->wrong,
      'no_answer' => $exam->no_answer,
      'mark' => $exam->mark,
      'minus' => $exam->minus,
      'percentage' => 100 / $exam->paper->questions->count() * $exam->mark,
      'comments' => '<a target="_blank" href="'.$source->host().'/students/result/'.$paper->id.'/after">View Details</a>'
    ];

    Mail::send('mail.send_result_tomail', $data, function($message) use ($data)
    {
      $message->to($data['email_to'])->subject($data['subject']);
      $message->from($data['email_from'], $data['from_name']);
    });
  }

  public function syllabusQuestions($id)
  {
    $syllabus = Syllabus::find($id);

    /** ------------ more fast optimization for large data -------- */
    $groupedData = [];

    // Step 1: Fetch Unique Questions
    $questions = Question::join('question_syllabus', 'question_syllabus.question_id', 'questions.id')
    ->join('syllabi', 'question_syllabus.syllabus_id', 'syllabi.id')
    ->leftJoin('question_subject', 'question_subject.question_id', 'questions.id')
    ->leftJoin('subjects', 'question_subject.subject_id', 'subjects.id')
    ->leftJoin('department_question', 'department_question.question_id', 'questions.id')
    ->leftJoin('departments', 'department_question.department_id', 'departments.id')
    ->leftJoin('chapter_question', 'chapter_question.question_id', 'questions.id')
    ->leftJoin('chapters', 'chapter_question.chapter_id', 'chapters.id')
    ->where('syllabi.id', $id)
    ->select([
        'questions.id as question_id',
        'questions.title as question_title',
        'questions.explanation',
        \DB::raw('GROUP_CONCAT(DISTINCT departments.name ORDER BY departments.name ASC) as department_names'),
        \DB::raw('GROUP_CONCAT(DISTINCT subjects.name ORDER BY subjects.name ASC) as subject_names'),
        \DB::raw('GROUP_CONCAT(DISTINCT chapters.name ORDER BY chapters.name ASC) as chapter_names')
    ])
    ->groupBy('questions.id', 'questions.title', 'questions.explanation')
    ->chunk(100, function ($questions) use (&$groupedData) {
      
      // Step 2: Fetch MCQs separately for all questions in this batch
      $mcqItems = McqItem::whereIn('question_id', $questions->pluck('question_id'))
        ->select('question_id', 'id', 'item', 'correct_answer')
        ->get()
        ->groupBy('question_id');

        $qids = [];

        foreach ($questions as $data) {
          $departments = explode(',', $data->department_names);
          $subjects = explode(',', $data->subject_names);
          $chapters = explode(',', $data->chapter_names);

            foreach ($departments as $department) {
              if (!isset($groupedData[$department]) && !in_array($data->question_id, $qids)) {
                $groupedData[$department] = [];
              }

              foreach ($subjects as $subject) {
                if (!isset($groupedData[$department][$subject]) && !in_array($data->question_id, $qids)) {
                  $groupedData[$department][$subject] = [];
                }

                foreach ($chapters as $chapter) {
                  if (!isset($groupedData[$department][$subject][$chapter]) && !in_array($data->question_id, $qids)) {
                    $groupedData[$department][$subject][$chapter] = [];
                  }

                  if(!in_array($data->question_id, $qids)) {
                    $groupedData[$department][$subject][$chapter][$data->question_id] = [
                    'question' => $data->question_title,
                    'explain' => $data->explanation,
                    'mcqs' => $mcqItems[$data->question_id] ?? []
                  ];
                  
                  array_push($qids, $data->question_id);
                }
              }
            }
          }
        }

        // Sleep for a short time to reduce CPU spike
        usleep(100000); // 100 milliseconds
    });
    // dd($groupedData);

    
    return [
      'syllabus' => $syllabus,
      'questions' => $groupedData
    ];
  }

  public function syllabus($id)
  {
    // check this syllabus batch request user permission
    $user = Auth::guard('student')->user();

    $data = $this->syllabusQuestions($id);
    
    $syllabus = $data['syllabus'];

    $user_batch = $user->batches()
    ->where('batch_id', $syllabus->batch_id)
    ->first();

    if(!$user_batch)
    {
      return redirect()->route('home.course.show', $syllabus->batch_id);
    }

    $groupedData = $data['questions'];
    
    return view('student-panel.syllabus', compact('syllabus', 'groupedData'));
  }

  public function generatePDF($id)
  {
    // check this syllabus batch request user permission
    $user = Auth::guard('student')->user();
    $syllabus = Syllabus::find($id);
    
    // if user not connected to the batch then redirect back
    $user_batch = $user->batches()
    ->where('batch_id', $syllabus->batch_id)
    ->first();

    if(!$user_batch)
    {
      return redirect()->route('home.course.show', $syllabus->batch_id);
    }

    //file name creation
    $syllabus_name = $syllabus->name ? $syllabus->name : '-';
    $batch_name = $syllabus->batch ? $syllabus->batch->name : '-';
    $department_name = $syllabus->department ? $syllabus->department->name : '-';
    $fileName = $syllabus_name.'_'.$batch_name.'_'.$department_name;

    //safe file name
    $fileName = preg_replace('/[\/\\\\:*?"<>|]/u', '-', $fileName);

    //check file already exist?
    if(Storage::exists('mpdf/'.$fileName))
    {
      return response()->json([
        'status' => 'success',
        'url' => route('students.syllabus.download', $fileName)
      ]);
    }

    $data = $this->syllabusQuestions($id);

    $groupedData = $data['questions'];

    ini_set("pcre.backtrack_limit", "10000000");
    ini_set("pcre.recursion_limit", "10000000");
    // ini_set('max_execution_time', 120); // 60 seconds or higher

    $mpdf = new Mpdf([
      'mode' => 'utf-8',
      'format' => 'A4',
      'dpi' => 100, 
      'default_font_size' => 14,
      'default_font' => 'nikosh',
      'fontDir' => storage_path('fonts'),
      'fontdata' => [
          'nikosh' => [
              'R' => 'Nikosh.ttf',
              'B' => 'Nikosh.ttf',
              'I' => 'Nikosh.ttf',
              'BI' => 'Nikosh.ttf',
              'useOTL' => 0xFF,
              'useKashida' => 75,
          ],
      ],
      'tempDir' => storage_path('app/mpdf'),
      'setAutoTopMargin' => 'stretch',
      'setAutoBottomMargin' => 'stretch',
    ]);

    $html = view('student-panel.syllabus-pdf', compact('syllabus', 'groupedData'))->render();

    $mpdf->useDictionaryLBR = false;
    $mpdf->SetWatermarkText('LiveEducationBD.com', 0.1);
    // $mpdf->watermark_font = 'Arial';
    // $mpdf->watermarkTextAlpha = 0.2; // More transparency
    $mpdf->showWatermarkText = true;
    
    ob_start();

    $mpdf->WriteHTML($html);

    ob_end_clean();

    $path = storage_path('app/mpdf/'.$fileName);
    $mpdf->Output($path, 'F');
    // if(!Storage::exists('mpdf/'.$fileName)){}

    return response()->json([
        'status' => 'success',
        'url' => route('students.syllabus.download', $fileName)
    ]);
  }

  public function download($file)
  {
    return Storage::download('mpdf/'.$file, $file.'.pdf');
  }

  public function complain()
  {
    return view('student-panel.complain');
  }

  public function complainStore(Request $request)
  {
    $this->Validate($request, [
      'name' => 'required|string',
      'email' => 'required|string',
      'contact' => 'required|string',
      'batch_id' => 'nullable|string',
      'department_id' => 'nullable|string',
      'details' => 'required|string',
    ]);

    $data = $request->all();

    if(isset($data['_token']))
    {
      unset($data['_token']);
    }

    $data['status'] = 'New';

    $data['created_at'] = date('Y-m-d H:i:s');

    try {
      Complain::insert($data);
      Session::flash('success', 'আমরা আপনের মতামতটি গ্রহণ করেছি। আপনার বিনয়ী মতামতের জন্য ধন্যবাদ।');
      return redirect()->route('students.dashboard');
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }
  }

  public function updatePaperAjax($id)
  {
    $paper = Paper::find($id);
    try{
      $result = Paper::where('id', $id)->update([
        'status' => 'Published'
      ]);

      if($result)
      {
        return response()->json([
          'success' => true,
          'message' => 'The paper published'
        ], 200);
      }
    }
    catch(\Exception $e)
    {
      return $e;
    }

    return response()->json([
      'success' => false,
      'message' => 'The paper update failed'
    ], 401);
  }

  public function batchRoutine()
  {
    $batches = Batch::all();
    return view('student-panel.routine-batch', compact('batches'));
  }

  public function batchReference($type = null, $batch_id, $referrer_id)
  {
    $student = Student::find($referrer_id);
    $student_batch = $student->batches()->where('id', $batch_id)->first();
    if($batch_id && $referrer_id && $student_batch || $type == 'teacher' && $batch_id && $referrer_id)
    {
      Session::put('batch-referrer', ['batch' => $batch_id, 'referrer' => $referrer_id, 'type' => $type]);
    }
    return redirect()->route('home.course.show', $batch_id);
  }

}