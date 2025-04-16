<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SourceCtrl;
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
use Auth;
use Session;
use Mail;
use DB;
use \Mpdf\Mpdf;
use Spatie\Browsershot\Browsershot;

class StudentHomeCtrl extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:student');
  }

  public function home()
  {
    // $user = Auth::guard('student')->user();
    // // active courses
    // $courses = Course::orderBy('id', 'DESC')->where('status', 'Active')->get();

    // // autheticates user courses
    // $student = Student::find($user->id);
    // $mycourses = $student->courses()->orderBy('id', 'DESC')->get();

    // // authenticates user examps
    // // $batch_ids = $student->batches()->pluck('id')->toArray();
    // $papers = Paper::orderBy('id', 'DESC')->whereIn('status', ['Published', 'Scheduled'])->where('permit', 'Every One')->get();
    // return view('student-panel.home', compact('courses', 'mycourses', 'papers', 'student'));

    return redirect()->route('students.my-course');
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
    // dd(Session::get('_order'));
    $batch = Batch::find($id);
    // $batches = Batch::where('status', 'Active')->get();
    $departments = $batch->departments()->where('status', 'Active')->get();
    $groups = Group::where('status', 'Active')->get();
    return view('student-panel.course-show', compact('batch', 'departments', 'groups'));
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
    $departments = $batch->departments()->get();
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
      $student->batches()->attach($data['batch_id']);
      $student->departments()->attach($data['department_id']);

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
    $courses = $student->batches()->orderBy('id', 'DESC')->where('status', 'Active')->get();
    return view('student-panel.my-course', compact('courses'));
  }

  public function mySyllabus()
  {
    $user = Auth::guard('student')->user();
    $student = Student::find($user->id);
    $batches = $student->batches()->orderBy('id', 'DESC')->where('status', 'Active')->get();
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

    $syllabuses = $batch->syllabuses()
    ->Join('departments', 'departments.id', 'syllabi.department_id')
    ->LeftJoin('department_student', 'department_student.department_id', 'departments.id')
    ->LeftJoin('students', 'students.id', 'department_student.student_id')
    ->where('students.id', $student->id)
    ->select('syllabi.id', 'syllabi.name', 'students.name as student_name')
    ->get();
    // dd($syllabuses);
    return view('student-panel.my-syllabus', compact('syllabuses', 'batches', 'batch'));
  }

  public function exam()
  {
    $user = Auth::guard('student')->user();
    $papers = Paper::orderBy('id', 'DESC')->whereIn('status', ['Published', 'Scheduled'])->where('permit', 'Every One')->get();
    
    return view('student-panel.exam', compact('papers'));
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

    // dd($check);
    
    return view('student-panel.check', compact('paper', 'check'));
  }

  public function instruction($id)
  {
    $user = Auth::guard('student')->user();
    $paper = Paper::find($id);
    return view('student-panel.instruct', compact('paper'));
  }

  public function examShow($id)
  {
    $user = Auth::guard('student')->user();
    $exams = Exam::where('paper_id', $id)
    ->where('student_id', $user->id)
    ->where('status', 'Completed')
    ->count();
    $paper = Paper::find($id);
    // if on the paper settings has exam limitation and exam limitation number is less than already examed count on this paper. So it will redirect to the result page. Otherwise it will redirect to the exam start page.
    if($paper->exam_limit && $paper->exam_limit <= $exams)
    {
      //find paper
      $exams = Exam::where('paper_id', $paper->id)
      ->where('student_id', $user->id)
      ->get();
      $exam_id = Exam::where('paper_id', $paper->id)->orderBy('id', 'DESC')->first()->id;
      $mcq_ids = Choice::where('exam_id', $exam_id)->pluck('mcq_id')->toArray();
      $dbmcqs = McqItem::whereIn('id', $mcq_ids)->whereNotNull('correct_answer')->get();

      $questions = $paper->questions()->count();
      $answered = count($mcq_ids);
      $correct = $dbmcqs->count();
      $wrong = $answered - $correct;
      $no_answered = $questions - $answered;
      $marks = ($paper->mark * $correct) - ($wrong * $paper->minus);

      $result = [
        'questions' => $questions,
        'answered' => $answered,
        'correct' => $correct,
        'wrong' => $wrong,
        'no_answered' => $no_answered,
        'marks' => $marks,
      ];

      return view('student-panel.exam-show', compact('paper', 'result', 'exams'));
    }

    //insert exam to the database with status 'live'
    try {
      $live_exam = Exam::insert([
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
    }
    catch(\Exception $e)
    {
      return $e;
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
    // dd($questions);

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
      $candidates = $paper->candidate;
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

  public function examPaper($id)
  {
    $user = Auth::guard('student')->user();
    $exam = Exam::find($id);
    $paper = Paper::find($exam->paper_id);
    $choices = Choice::where('exam_id', $id)->pluck('question_id', 'mcq_id')->toArray();
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

    // if choice any question the do this action
    if($request->mcq_id)
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

      if($mcq_ids)
      {
        for($x = 0; $x < count($question_ids); $x++)
        {
          Choice::insert([
            'exam_id' => $request->exam_id,
            'question_id' => $question_ids[$x],
            'mcq_id' => $mcq_ids[$x]
          ]);
        }
      }
    }
    catch(\E $e)
    {
      return $e;
    }

    if($result)
    {
      $success = true;
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
    $data = $this->syllabusQuestions($id);
    
    $syllabus = $data['syllabus'];
    $groupedData = $data['questions'];
    
    return view('student-panel.syllabus', compact('syllabus', 'groupedData'));
  }

  public function generatePDF($id)
  {
    $syllabus = Syllabus::find($id);

    $data = $this->syllabusQuestions($id);
    
    $syllabus = $data['syllabus'];
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

    // $mpdf->Output(storage_path('app/mpdf/syllabus.pdf'), 'F');

    $mpdf->Output($syllabus ? $syllabus->name : '----Syllabus', 'D');

    // $mpdf->stream('document.pdf');

    // return $mpdf->download('Syllabus PDF.pdf');

    // return view('student-panel.syllabus-pdf', compact('syllabus', 'groupedData'));
  }

  public function complain()
  {
    return view('student-panel.complain');
  }

  public function complainStore(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|string',
      'email' => 'required|string',
      'contact' => 'required|string',
      'department' => 'required|string',
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
      return redirect()->route('students.complain');
    }
    catch(\E $e)
    {
      return $e;
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

}