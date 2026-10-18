<?php







namespace App\Http\Controllers\Homepage;







use App\Http\Controllers\Controller;



use App\Http\Controllers\ScheduleCtrl;



use App\Http\Controllers\CircularCtrl;



use Illuminate\Http\Request;



use Illuminate\Support\Facades\Storage;



use App\Models\Course;



use App\Models\Paper;



use App\Models\Exam;



use App\Models\Question;



use App\Models\Teacher;



use App\Models\Student;



use App\Models\Batch;



use App\Models\Department;



use App\Models\Group;



use App\Models\McqItem;



use App\Models\Choice;



use App\Models\Page;



use App\Models\Complain;



use App\Models\Routine;



use App\Models\RoutineItemRow;



use App\Models\Circular;



use App\Models\CircularType;



use App\Models\Ad;



use App\Models\Video;



use App\Models\Service;



use App\Models\Visitor;



use App\Models\Subject;



use App\Models\BookCategory;



use App\Models\Book;



use App\Models\Filter;



use App\Models\QuestionComment;



use App\Models\QuestionReaction;



use App\Models\QuestionRating;



use App\Models\QuestionReading;



use App\Models\Notice;



use Auth;



use Session;



use DB;







class HomePageCtrl extends Controller



{



  public $departments;







  public function index()



  {



    Visitor::insert([



      'ip_address' => request()->ip(),



      'visited_at' => now(),



    ]);







    $courses = Course::with('batches')



    ->orderBy('id', 'ASC')



    ->where('status', 'Active')



    ->where('web', 'Yes')



    ->get();











$sliders = [];







$batchQuery = Batch::where('status', 'Active')



    ->where('web', 'Yes')



    ->whereRaw('DATE(reg_end_at) >= ?', [now()])



    ->orderBy('id', 'DESC');











$batches = $batchQuery



    ->with('course')



    ->limit(8)



    ->get();



















    // Active batches



    // $batches = Batch::where('status', 'Active')



    // ->whereRaw('DATE(reg_end_at) >= ?', [date('Y-m-d H:i:s')])



    // ->limit(8)



    // ->orderBy('id', 'DESC')



    // ->get();







    // teachers



    $teachers = Teacher::where('status', 'Active')



    ->where('web', 'Yes')



    ->orderBy('id', 'ASC')



    ->select('id', 'name', 'designation', 'image')



    ->get();







    $complains = Complain::orderBy('id', 'DESC')



    ->where('web', 'Yes')



    ->select('name', 'details')



    ->get();







    $json = Storage::get('about.json');



    $array = json_decode($json, true);



    $about = (object) $array;







    $circulars = CircularType::where('status', 'Active')->get();







    $ads = Ad::where('status', 'Active')->limit(3)->get();



    $videos = Video::where('status', 'Active')



    ->whereNotNull('url')



    ->whereNull('main')



    ->get();







    $main_video = Video::where('status', 'Active')->where('main', 'Yes')->first();



    $services = Service::orderBy('id', 'DESC')->where('status', 'Active')->get();







    //book categories



    $book_categories = BookCategory::where('status', 'Active')



    ->select('id', 'name')



    ->get();







    //notice



    $notice = Notice::latest()->where('status', 'published')->first();







    return view('homepage.index', compact('courses', 'sliders', 'batches', 'teachers', 'complains', 'about', 'circulars', 'ads', 'videos', 'main_video', 'services', 'book_categories','notice'));



  }







  /**

   * Public circular list. Optional type maintains legacy category links.

   * Only circulars whose application window is open in Bangladesh are visible.

   */

  public function circular($id = null)

  {

      // Only actively accepting applications, using Bangladesh dates.

      $today = \Carbon\Carbon::today('Asia/Dhaka')->toDateString();

      $active = Circular::query()

          ->whereIn('status', ['Active', 'Published'])

          ->whereNotNull('start_at')->whereNotNull('end_at')

          ->whereDate('start_at', '<=', $today)

          ->whereDate('end_at', '>=', $today);



      // Keep old category-specific URLs working as before.

      $selectedType = null;

      if ($id !== null) {

          $selectedType = CircularType::findOrFail($id);

      }

      $counts = CircularType::where('status', 'Active')

          ->withCount(['circulars as active_circulars_count' => function ($query) use ($today) {

              $query->whereIn('status', ['Active', 'Published'])

                  ->whereNotNull('start_at')->whereNotNull('end_at')

                  ->whereDate('start_at', '<=', $today)

                  ->whereDate('end_at', '>=', $today);

          }])->orderBy('id')->get();

      $allCircularCount = (clone $active)->count();

      $typeId = request()->query('type');

      if ($selectedType === null && ctype_digit((string) $typeId) && (int)$typeId > 0) {

          $selectedType = $counts->firstWhere('id', (int)$typeId);

      }

      if ($selectedType) {

          $active->whereHas('circularType', function ($query) use ($selectedType) {

              $query->where('circular_types.id', $selectedType->id);

          });

      }

      $keyword = trim((string) request()->query('search', ''));

      if ($keyword !== '') {

          $active->where(function ($query) use ($keyword) {

              $query->where('title', 'like', '%' . $keyword . '%')

                  ->orWhere('admit_to', 'like', '%' . $keyword . '%')

                  ->orWhere('position', 'like', '%' . $keyword . '%');

          });

      }

      $deadlineFrom = request()->query('from');

      $deadlineTo = request()->query('to');

      if ($deadlineFrom && preg_match('/^**\d**{4}-**\d**{2}-**\d**{2}$/', $deadlineFrom) && checkdate((int)substr($deadlineFrom,5,2),(int)substr($deadlineFrom,8,2),(int)substr($deadlineFrom,0,4))) {

          $active->whereDate('end_at', '>=', $deadlineFrom);

      }

      if ($deadlineTo && preg_match('/^**\d**{4}-**\d**{2}-**\d**{2}$/', $deadlineTo) && checkdate((int)substr($deadlineTo,5,2),(int)substr($deadlineTo,8,2),(int)substr($deadlineTo,0,4))) {

          $active->whereDate('end_at', '<=', $deadlineTo);

      }

      // Newly uploaded/edited records first; stable id for equal timestamps.

      $circulars = $active->orderByDesc('updated_at')->orderByDesc('id')

          ->paginate(10)->withQueryString();

      return view('homepage.circular', compact('circulars', 'counts', 'allCircularCount', 'selectedType', 'keyword'));

  }



  public function circularShow($id)

  {

      $circular = Circular::findOrFail($id);

      return view('homepage.circular-read', compact('circular'));

  }



  public function serviceShow($id)



  {



    $service = Service::find($id);



    return view('homepage.service-read', compact('service'));



  }







  public function comingSoon()



  {



    return view('homepage.coming-soon');



  }







  public function course()



  {



    $courses = Batch::orderBy('id', 'DESC')



    ->whereRaw('DATE(reg_end_at) >= ?', [date('Y-m-d H:i:s')])



    ->where('status', 'Active')->get();







    return view('homepage.courses', compact('courses'));



  }







  public function courseShow($id)



  {



    $batch = Batch::find($id);



    if($batch->departments()->where('status', 'Active')->get())



    {



      $departments = $batch->departments()->where('status', 'Active')->get();



    }







    $routine = Routine::where('batch_id', $batch->id)



    ->orderBy('id', 'DESC')



    ->where('status', 'Published')



    ->first();







    $subjects = $departments = $routine_rows = [];







    if($routine)



    {



      $departments = $routine->departments()->get();



      // $subjects = $routine->subjects()->get();  







      // $routine_rows = RoutineItemRow::where('routine_item_rows.routine_id', $routine->id)



      // ->with(['subjects', 'departments'])



      // ->select('routine_item_rows.*')



      // ->get();



    }







    return view('homepage.course-show', compact('batch', 'departments', 'routine'));



    }











public function courseRoutine($id)



{



    $batch = Batch::findOrFail($id);







    $departments = $batch->departments()



        ->where('status','Active')



        ->get();







    $routine = Routine::where('batch_id',$batch->id)



        ->where('status','Published')



        ->orderBy('id','DESC')



        ->first();







    return view('homepage.course-routine', compact(



        'batch',



        'departments',



        'routine'



    ));







  }



  public function courseDetails($id)



{



    $course = Course::with('batches')



        ->findOrFail($id);











    return view(



        'homepage.course-details',



        compact('course')



    );



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



      // return back();



      return redirect()->route('students.my-course');



    }



    // $student->courses()->attach($request->course_id);



    $student->batches()->attach($request->batch_id);



    $student->departments()->attach($request->department_id);



    // $student->groups()->attach($request->group_id);







    Session::flash('success', 'You have successfully applied to the course.');



    return redirect()->route('students.my-course');



  }







  public function myCourse()



  {



    $user = Auth::guard('student')->user();



    $student = Student::find($user->id);



    $courses = $student->courses()->orderBy('id', 'DESC')->where('status', 'Active')->get();



    return view('homepage.my-course', compact('courses'));



  }







  public function exam()



  {



    $user = Auth::guard('student')->user();



    $student = Student::find($user->id);



    $batch_ids = $student->batches()->pluck('id')->toArray();



    $papers = Paper::orderBy('id', 'DESC')->whereIn('batch_id', $batch_ids)->get();



    return view('homepage.exam', compact('papers'));



  }







  public function check($id)



  {



    $user = Auth::guard('student')->user();



    $check = [];



    $exams = Exam::where('paper_id', $id)->where('student_id', $user->id)->count();



    $paper = Paper::find($id);







    if($paper->status == 'Scheduled' && $paper->open && $paper->open >= date('Y-m-d H:i:s'))



    {



      $check['scheduled'] = true;



    }



    elseif($paper->status == 'Scheduled' && $paper->open <= date('Y-m-d H:i:s'))



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



    else{



      return redirect()->route('students.instruction', $id);



    }







    // dd($check);







    return view('homepage.check', compact('paper', 'check'));



  }







  public function instruction($id)



  {



    $user = Auth::guard('student')->user();



    $paper = Paper::find($id);



    return view('homepage.instruct', compact('paper'));



  }







  public function examShow($id)



  {



    $user = Auth::guard('student')->user();



    $exams = Exam::where('paper_id', $id)->where('student_id', $user->id)->count();



    $paper = Paper::find($id);



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







      return view('homepage.exam-show', compact('paper', 'result', 'exams'));



    }







    return view('homepage.exam-show', compact('paper'));



  }







  public function result($id)



  {



    $user = Auth::guard('student')->user();



    $exams = Exam::where('student_id', $user->id)->where('paper_id', $id)->get();



    $paper = Paper::find($id);



    $result = 'Yes';







    return view('homepage.result', compact('exams', 'paper', 'result'));



  }







  public function solution($id)



  {



    $user = Auth::guard('student')->user();



    $exam = Exam::find($id);



    $paper = Paper::find($exam->paper_id);



    $choices = Choice::where('exam_id', $id)->pluck('mcq_id', 'question_id')->toArray();



    // dd($choices);



    return view('homepage.solution', compact('paper', 'choices'));



  }







  public function examAdd(Request $request)



  {



    $user = Auth::guard('student')->user();



    $this->validate($request, [



      'paper_id' => 'required|numeric',



      'question_id' => 'required|string',



      'mcq_id' => 'required|string',



    ]);







    $questions = $answered = $correct = $wrong = $no_answered = $marks = 0;







    $question_ids = explode(',', $request->question_id);



    $mcq_ids = explode(',', $request->mcq_id);







    //find paper



    $paper = Paper::find($request->paper_id);



    $dbmcqs = McqItem::whereIn('id', $mcq_ids)->whereNotNull('correct_answer')->get();







    $questions = $paper->questions()->count();



    $answered = count($question_ids);



    $correct = $dbmcqs->count();



    $wrong = $answered - $correct;



    $no_answered = $questions - $answered;



    $marks = ($paper->mark * $correct) - ($wrong * $paper->minus);







    try{



      $exam = new Exam;



      $exam->student_id = $user->id;



      $exam->paper_id = $paper->id;



      $exam->answer = $answered;



      $exam->correct = $correct;



      $exam->wrong = $wrong;



      $exam->no_answer = $no_answered;



      $exam->mark = $marks;



      $exam->save();















      for($x = 0; $x < count($question_ids); $x++)



      {



        Choice::insert([



          'exam_id' => $exam->id,



          'question_id' => $question_ids[$x],



          'mcq_id' => $mcq_ids[$x]



        ]);



      }



    }catch(\E $e)



    {



      return $e;



    }    







    return response()->json([



      'success' => true,



      'message' => $paper->message,



      'questions' => $questions,



      'answered' => $answered,



      'correct' => $correct,



      'wrong' => $wrong,



      'no_answered' => $no_answered,



      'marks' => $marks,



    ]);



  }







  public function page($slug)



  {



    $page = Page::where('slug', $slug)->first();



    if(is_null($page))



    {



      return 'Page Not Found! <a href="/">Back</a>';



    }



    return view('homepage.single-page', compact('page'));



  }







  public function resultList($paper_id)



  {



    $paper = Paper::find($paper_id);



    $exams = Exam::where('paper_id', $paper_id)



    ->orderBy('mark', 'DESC')



    ->get();



    return view('homepage.result-list', compact('paper', 'exams'));



  }















  public function routineFilter($id, $department = null)
  {
      $routine = Routine::with('departments')->findOrFail($id);
      // Prevent loading drafts from this public endpoint.
      abort_unless($routine->status === 'Published', 404);
      $departmentId = (int) $department;
      abort_unless($departmentId > 0, 404);
      // Department must be assigned to the batch AND to this published routine.
      $batch = Batch::findOrFail($routine->batch_id);
      abort_unless($batch->departments()->where('departments.id', $departmentId)->exists(), 404);
      abort_unless($routine->departments()->where('departments.id', $departmentId)->exists(), 404);
      $rows = RoutineItemRow::query()
          ->where('routine_id', $routine->id)
          ->whereHas('departments', function ($query) use ($departmentId) {
              $query->where('department_id', $departmentId);
          })
          ->with(['subjects.subject', 'departments' => function($query) use ($departmentId) {
              $query->where('department_id', $departmentId);
          }])
          ->orderBy('exam_at')
          ->orderBy('id')
          ->get();
      return response()->json(['routine_rows' => $rows]);
  }

  public function scheduleTest()



{



    // return ScheduleCtrl::scheduleEmail();



    return ScheduleCtrl::scheduleSms();



}







  public function booksDepartment($id)



  {



    return redirect()->route('home.coming-soon');



    $category = DB::table('book_categories')->find($id);



    $departments = Department::where('status', 'Active')



    ->get();







    return view('homepage.books.department', compact('category', 'departments'));



  }







  public function books($id)



  {



    $books = Book::where('status', 'Active')



    ->where('department_id', $id)



    ->get();







    return view('homepage.books.index', compact('books'));



  }







  public function readBook(Request $request, $id)



  {



    $data = $request->validate(



      [



        "question_id" => 'nullable|numeric',



        "chapter" => 'nullable|numeric',



        "source" => 'nullable|string',



        "sub-source" => 'nullable|string',



        "year" => 'nullable|string',



        "reaction" => 'nullable|string',



        "rating" => 'nullable|string',



        "reading" => 'nullable|string'



      ]



    );







    //



    $chapter = $source = $subsource = $year = $reaction = $rating = $reading = '';



    if(isset($data['chapter']))



    {



      $chapter = $data['chapter'];



    }



    if(isset($data['source']))



    {



      $source = $data['source'];



    }



    if(isset($data['sub-source']))



    {



      $subsource = $data['sub-source'];



    }



    if(isset($data['year']))



    {



      $year = $data['year'];



    }



    if(isset($data['reaction']))



    {



      $reaction = $data['reaction'];



    }



    if(isset($data['rating']))



    {



      $rating = $data['rating'];



    }



    if(isset($data['reading']))



    {



      $reading = $data['reading'];



    }







    $subject = Book::find($id);







    // dd($subject->questions()->toSql());



    $questions = $subject->questions()



    ->where('type', 'MCQ')



    ->when($chapter, function($query, $chapter)



    {



      $query->whereHas('chapters', function ($q) use ($chapter)



      {



        $q->where('chapter_id', $chapter);



      });



    })



    ->when($source, function($query, $source)



    {



      $query->whereHas('filters', function ($q) use ($source)



      {



        $q->where('sub_filter_id', $source);



      });



    })



    ->when($subsource, function($query, $subsource)



    {



      $query->whereHas('filters', function ($q) use ($subsource)



      {



        $q->where('sub_filter_id', $subsource);



      });



    })



    ->when($year, function($query, $year)



    {



      $query->whereHas('filters', function ($q) use ($year)



      {



        $q->where('sub_filter_id', $year);



      });



    })



    ->when($reaction, function($query, $reaction)



    {



      $query->whereHas('reactions', function ($q) use ($reaction)



      {



        $q->where('reaction', $reaction);



      });



    })



    ->when($rating, function($query, $rating)



    {



      $query->whereHas('ratings', function ($q) use ($rating)



      {



        $q->where('rating', $rating);



      });



    })



    ->when($reading, function($query, $reading)



    {



      $query->whereHas('readings', function ($q) use ($reading)



      {



        $q->where('reading', $reading);



      });



    })



    ->paginate(25);







    $chapters = $subject->chapters()



    ->select('id', 'name')



    ->get();







    //question sources



    $qsource = Filter::where('slug', 'source')



    ->select('id', 'name', 'slug')



    ->first();



    $sources = $qsource->subfilter()



    ->select('id', 'name')



    ->get();







    // get sub sources



    $subsource = Filter::where('slug', 'sub-source')



    ->select('id', 'name', 'slug')



    ->first();







    $subsources = $subsource->subfilter()



    ->select('id', 'name')



    ->get();







    // get sub sources



    $year = Filter::where('slug', 'year')



    ->select('id', 'name', 'slug')



    ->first();







    $years = $year->subfilter()



    ->select('id', 'name')



    ->get();







    if($questions)



    {



      return view('homepage.books.read', compact('subject', 'chapters', 'questions', 'sources', 'subsources', 'years'));



    }



    return back();



  }



}