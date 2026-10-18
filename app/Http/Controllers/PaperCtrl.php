<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SourceCtrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Filter;
use App\Models\SubFilter;
use App\Models\Role;
use App\Models\Label;
use App\Models\Paper;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Department;
use App\Models\Group;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Choice;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use Validator;

class PaperCtrl extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $courses = Course::where('status', 'Active')->get();
      $batches = Batch::where('status', 'Active')->get();
      $departments = Department::where('status', 'Active')->get();

      $course_id = $request->input('course_id');
      $batch_id = $request->input('batch_id');
      $department_id = $request->input('department_id');

      $papers = Paper::query()
      ->when($course_id, function($query, $course_id)
      {
        return $query->where('course_id', $course_id);
      })
      ->when($batch_id, function($query, $batch_id)
      {
        return $query->where('batch_id', $batch_id);
      })
      ->when($department_id, function($query, $department_id)
      {
        return $query->where('department_id', $department_id);
      })
      ->orderBy('id','DESC')
      ->paginate(25)
      ->appends($request->all());

      return view('layouts.papers.index', compact('papers', 'courses', 'batches', 'departments', 'course_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $courses = Course::where('status', 'Active')->get();
        $batches = Batch::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $groups = Group::where('status', 'Active')->get();
        return view('layouts.papers.create', compact('courses', 'batches', 'departments', 'groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    $source = new SourceCtrl;

    $validator = Validator::make($request->all(), [
        'header' => 'required',
        'banner' => 'mimes:jpeg,jpg,png,pdf|max:1000',
    ]);

    if($validator->fails())
    {
        return response()->json(
            [
                'success' => false,
                'message' => 'File upload failed',
                'errors' => $validator->getMessageBag()->toArray()
            ],
            201
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Form Data
    |--------------------------------------------------------------------------
    */

    $data = $request->all();


    /*
    |--------------------------------------------------------------------------
    | Multiple Departments
    |--------------------------------------------------------------------------
    */

    $departmentIds = $data['department_id'] ?? [];

    // Make sure department_id is always an array
    if (!is_array($departmentIds))
    {
        $departmentIds = [$departmentIds];
    }

    // Remove department array from main paper data
    unset($data['department_id']);


    /*
    |--------------------------------------------------------------------------
    | Keep First Department as Primary Department
    |--------------------------------------------------------------------------
    |
    | Old system uses papers.department_id as a single value.
    | So first selected department will be stored there.
    | All selected departments will also be saved
    | in paper_departments table.
    |
    */

    $data['department_id'] = $departmentIds[0] ?? null;


    /*
    |--------------------------------------------------------------------------
    | Remove Token
    |--------------------------------------------------------------------------
    */

    if(isset($data['_token']))
    {
        unset($data['_token']);
    }


    /*
    |--------------------------------------------------------------------------
    | Banner Upload
    |--------------------------------------------------------------------------
    */

    if(isset($data['banner']))
    {
        $data['banner'] = $source->uploadImage(
            $data['banner'],
            'papers/'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Default Format
    |--------------------------------------------------------------------------
    */

    if(!isset($data['format']))
    {
        $data['format'] = 0;
    }


    /*
    |--------------------------------------------------------------------------
    | Created Date
    |--------------------------------------------------------------------------
    */

    $data['created_at'] = date('Y-m-d H:i:s');


    /*
    |--------------------------------------------------------------------------
    | Create Question Paper
    |--------------------------------------------------------------------------
    */

    try
    {
        // Create paper
        Paper::insert($data);

        // Get newly created paper
        $paper = Paper::orderBy('id', 'DESC')->first();


        /*
        |--------------------------------------------------------------------------
        | Save Multiple Departments
        |--------------------------------------------------------------------------
        */

        if($paper && !empty($departmentIds))
        {
            $paper->departments()->sync($departmentIds);
        }
    }
    catch(\Exception $e)
    {
        return $e;
    }


    /*
    |--------------------------------------------------------------------------
    | Success Message
    |--------------------------------------------------------------------------
    */

    Session::flash(
        'success',
        'The question paper successfully created.'
    );


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    return redirect()->route(
        'paper.show',
        $paper->id
    );
}

    /**
 * Display the specified resource.
 *
 * @param  \App\subfilter  $subfilter
 * @return \Illuminate\Http\Response
 */
public function show($id)
{
    $paper = Paper::with([
        'course',
        'batch',
        'department',
        'departments',
        'questions'
    ])->find($id);

    if($paper)
    {
        // All active courses
        $courses = Course::where('status', 'Active')
            ->orderBy('name', 'asc')
            ->get();

        return view(
            'layouts.papers.read',
            compact('paper', 'courses')
        );
    }
    else
    {
        Session::flash(
            'error',
            'The question paper maybe deleted or not found!'
        );

        return redirect()->route('paper.index');
    }
}


public function view($id)
{
    if(!is_null(Session::get('_paper')))
    {
        Session::forget('_paper');
    }

    $paper = Paper::find($id);

    return view(
        'layouts.papers.view',
        compact('paper')
    );
}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $courses = Course::where('status', 'Active')->get();
        $batches = Batch::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $groups = Group::where('status', 'Active')->get();
        $paper = Paper::find($id);

        return view('layouts.papers.edit', compact('paper', 'courses', 'batches', 'departments', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
{
    $source = new SourceCtrl;


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    $validator = Validator::make($request->all(), [

        'header' => 'required',

        'banner' => 'nullable|image|mimes:jpeg,jpg,png,pdf|max:1000',

    ]);


    if($validator->fails())
    {
        return response()->json(
            [
                'success' => false,

                'message' => 'File upload failed',

                'errors' => $validator->getMessageBag()->toArray()
            ],

            201
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Existing Paper
    |--------------------------------------------------------------------------
    */

    $paper = Paper::find($id);


    if(!$paper)
    {
        return back()->with(
            'error',
            'Question Paper not found.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Old Banner
    |--------------------------------------------------------------------------
    */

    $xbanner = public_path($paper->banner);


    /*
    |--------------------------------------------------------------------------
    | Get Form Data
    |--------------------------------------------------------------------------
    */

    $data = $request->all();


    /*
    |--------------------------------------------------------------------------
    | Multiple Departments
    |--------------------------------------------------------------------------
    */

    $departmentIds = $data['department_id'] ?? [];


    // Make sure department_id is always an array

    if(!is_array($departmentIds))
    {
        $departmentIds = [$departmentIds];
    }


    /*
    |--------------------------------------------------------------------------
    | Remove department_id Array From Main Paper Data
    |--------------------------------------------------------------------------
    */

    unset($data['department_id']);


    /*
    |--------------------------------------------------------------------------
    | Keep First Department as Primary Department
    |--------------------------------------------------------------------------
    |
    | Old system uses papers.department_id as a single value.
    | Therefore first selected department will remain in
    | papers.department_id.
    |
    | All selected departments will be stored in
    | paper_departments table.
    |
    |--------------------------------------------------------------------------
    */

    $data['department_id'] = $departmentIds[0] ?? null;


    /*
    |--------------------------------------------------------------------------
    | Remove Token
    |--------------------------------------------------------------------------
    */

    if(isset($data['_token']))
    {
        unset($data['_token']);
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Method
    |--------------------------------------------------------------------------
    */

    if(isset($data['_method']))
    {
        unset($data['_method']);
    }


    /*
    |--------------------------------------------------------------------------
    | Banner Upload
    |--------------------------------------------------------------------------
    */

    $newBannerUploaded = false;


    if(isset($data['banner']) && $data['banner'])
    {
        $data['banner'] = $source->uploadImage(
            $data['banner'],
            'papers/'
        );

        $newBannerUploaded = true;
    }
    else
    {
        // Do not overwrite existing banner
        unset($data['banner']);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Question Paper
    |--------------------------------------------------------------------------
    */

    try
    {
        Paper::where(
            'id',
            $id
        )->update($data);


        /*
        |--------------------------------------------------------------------------
        | Save Multiple Departments
        |--------------------------------------------------------------------------
        */

        $paper = Paper::find($id);


        if($paper)
        {
            $paper->departments()->sync($departmentIds);
        }


        /*
        |--------------------------------------------------------------------------
        | Delete Old Banner Only When New Banner Uploaded
        |--------------------------------------------------------------------------
        */

        if(
            $newBannerUploaded &&
            File::exists($xbanner)
        )
        {
            File::delete($xbanner);
        }
    }
    catch(\Exception $e)
    {
        return $e;
    }


    /*
    |--------------------------------------------------------------------------
    | Success Message
    |--------------------------------------------------------------------------
    */

    Session::flash(
        'success',
        'The question paper successfully updated!'
    );


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    return redirect()->route(
        'paper.edit',
        $paper->id
    );
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        $paper = Paper::find($id);
        $xbanner = public_path($paper->banner);
           
        if (File::exists($xbanner)) {
            File::delete($xbanner);
        }

        //remove attached questions
        $paper->questions()->detach();

        $paper->delete();

        Session::flash('success', 'The question paper successfully deleted!');
        // return redirect()->route('paper.index');
        return back();
      }
      return back()->with('error', 'Permission Denied');
    }

    //custom routes
    public function addQuestion($id)
    {
        $paper = Paper::find($id);
        if($paper)
        {
            Session::put('_paper', $paper);
            return redirect()->route('question.view');
        }
        return back();
    }

    public function addToPaper(Request $request)
    {
        $data = $request->all();
        $ids = explode(',', $data['question']);
        if(!is_null(Session::get('_paper')))
        {
            $paper = Session::get('_paper');
            if($data['action'] == 'add')
            {
                $qcount = $paper->questions()->attach($ids);
            }
            else
            {
                $qcount = $paper->questions()->detach($ids);
            }

            $paper = Paper::find($paper->id);
            Session::put('_paper', $paper);

            return response()->json([
                'success' => true,
                'message' => 'The question added to the paper',
                'qcount' => $qcount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'We are getting error'
        ]);
    }

    public function solution($id)
    {
        $paper = Paper::find($id);

        return view('layouts.papers.solution', compact('paper'));
    }

    public function solutionAjax($id)
    {
        $source = new SourceCtrl;
        $paper = Paper::find($id);
        $questions = $paper->questions()->with('mcqitems')->paginate(15);

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'format' => $source->mcqlist()[$paper->format ?? 0]
        ], 200);
    }

    public function result($id)
    {
        $paper = Paper::find($id);
        $exams = Exam::where('paper_id', $id)->orderBy('mark', 'DESC')->get();
        return view('layouts.papers.result', compact('paper', 'exams'));
    }

    public function resultCsv($id)
    {
        $paper = Paper::find($id);
        $exams = Exam::where('paper_id', $id)->orderBy('mark', 'DESC')->get();
        
        $csvFileName = 'Exam_No_'.$paper->name.'_results.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
        ];
        
        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['SL No.', 'Student Name', 'Registration ID', 'Department', 'Correct', 'Wrong', 'Blank', 'Total Mark']);
        
        foreach ($exams as $key => $exam)
        {
            $department = $exam->paper->department ? $exam->paper->department->name : '';
            
            fputcsv($output, [
                $key + 1,
                $exam->student->name,
                str_pad($exam->student->id, 6, '0', STR_PAD_LEFT),
                $department,
                $exam->correct,
                $exam->wrong,
                $exam->no_answer,
                $exam->mark,
            ]);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        return Response::make($csvContent, 200, $headers);
        // return view('layouts.papers.result', compact('paper', 'exams'));
    }
    
    public function exam($id)
    {
        $students = []; 
        $paper = Paper::find($id);
        if($paper->permit == 'Batch')
        {
            $batch = Batch::find($paper->batch_id);
            $students = $batch->students()->pluck('id')->toArray();
        }
        if($paper->permit == 'Department')
        {
            $department = Department::find($paper->department_id);
            $students = $department->students()->pluck('id')->toArray();
        }
        if($paper->permit == 'Group')
        {
            $group = Group::find($paper->group_id);
            $students = $group->students()->pluck('id')->toArray();
        }
        // dd($students);
        $exams = Exam::where('paper_id', $id)->orderBy('mark', 'DESC')->get();
        return view('layouts.papers.exam', compact('paper', 'exams', 'students'));
    }

    public function copy($id)
    {
        $courses = Course::where('status', 'Active')->get();
        $batches = Batch::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $groups = Group::where('status', 'Active')->get();
        $paper = Paper::find($id);
        return view('layouts.papers.copy', compact('paper', 'courses', 'batches', 'departments', 'groups'));
    }
    
    public function copyStore(Request $request)
    {
        $source = new SourceCtrl;
        $validator = Validator::make($request->all(), [
            'paper_id' => 'required|numeric',
            'header' => 'required',
            'banner' => 'mimes:jpeg,jpg,png,pdf|max:1000',
        ]);
        
        $data = $request->all();
        $paper = Paper::find($request->paper_id);
        // dd($paper);

        if(isset($data['paper_id']))
        {
            unset($data['paper_id']);
        }

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }

        if(isset($data['_method']))
        {
            unset($data['_method']);
        }

        if(isset($data['banner']))
        {
            $data['banner'] = $source->uploadImage($data['banner'], 'papers/');
        }
        elseif(!is_null($paper->banner))
        {
            $data['banner'] = $paper->banner;
        }

        if(!isset($data['format']))
        {
            $data['format'] = 0;
        }

        try {
            Paper::insert($data);
            
            //find questions
            $questions = $paper->questions()->pluck('id')->toArray();
            // dd($questions);

            $paper = Paper::orderBy('id', 'DESC')->first();

            //add questions
            $paper->questions()->attach($questions);
        }
        catch(\Exception $e)
        {
            return $e;
        }
        
        Session::flash('success', 'The question paper successfully copied.');
        return redirect()->route('paper.show', $paper->id);
    }

    /** re-check result */
    public function reCheckResult($exam_id)
    {
        $exam = Exam::find($exam_id);
        $paper = Paper::find($exam->paper_id);
        $questions = $paper->questions()
        ->leftJoin('mcq_items', 'mcq_items.question_id', 'questions.id')
        ->whereNotNull('mcq_items.correct_answer')
        ->pluck('mcq_items.id', 'mcq_items.question_id')
        ->toArray();
        $choices = Choice::where('exam_id', $exam_id);
        $correct_mcqs = $choices->pluck('mcq_id', 'question_id')->toArray();

        $correct = $wrong = 0;

        foreach ($questions as $key => $value) {
            if (array_key_exists($key, $correct_mcqs)) {
                if ($correct_mcqs[$key] === $value) {
                    $correct++;
                } else {
                    $wrong++;
                }
            }
        }

        $total_questions = count($questions);
        $answered = $correct + $wrong;
        $no_answered = $total_questions - $answered;
        $total_mark = ($correct * $paper->mark) - ($wrong * $paper->minus);

        try{
            Exam::where('id', $exam_id)->update([
                'answer' => $answered,
                'correct' => $correct,
                'wrong' => $wrong,
                'no_answer' => $no_answered,
                'mark' => $total_mark
            ]);
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }

        Session::flash('success', 'The exam result updated');

        return redirect()->route('paper.exam', $paper->id);
    }

    /** detuch question from question paper */
public function detachQuestion($paperId, $qid)
{
    $paper = Paper::find($paperId);

    $paper->questions()->detach($qid);

    return response()->json([
        'success' => true
    ]);
}


/**
 * Get Routine Exams by Batch
 */
public function getRoutineExams($batch_id)
{
    try {

        // Get latest published routine for this batch
        $routine = \App\Models\Routine::where(
            'batch_id',
            $batch_id
        )
        ->where(
            'status',
            'Published'
        )
        ->orderBy(
            'id',
            'DESC'
        )
        ->first();


        // No routine found
        if (!$routine) {

            return response()->json([
                'success' => false,
                'exams' => []
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Get Exam Names from Routine
        |--------------------------------------------------------------------------
        */

        $exams = $routine->exams()
            ->orderBy(
                'sort_order',
                'ASC'
            )
            ->get([
                'id',
                'name',
                'sort_order'
            ]);


        /*
        |--------------------------------------------------------------------------
        | Attach Start & End Time from Routine Item
        |--------------------------------------------------------------------------
        */

        $examData = $exams->map(function ($exam) use ($routine) {

            $item = \App\Models\RoutineItemRow::where(
                'routine_id',
                $routine->id
            )
            ->where(
                'paper_name',
                $exam->name
            )
            ->first();


            return [
                'id' => $exam->id,

                'name' => $exam->name,

                'sort_order' => $exam->sort_order,

                'exam_at' => $item
                    ? $item->exam_at
                    : null,

                'exam_end_at' => $item
                    ? $item->exam_end_at
                    : null,
            ];

        });


        return response()->json([

            'success' => true,

            'exams' => $examData

        ]);

    }
    catch (\Exception $e) {

        return response()->json([

            'success' => false,

            'exams' => [],

            'message' => $e->getMessage()

        ], 500);

    }
}



/**
 * Auto Add Question Page
 */
public function autoAddQuestion($id)
{
    $paper = Paper::with([
        'course',
        'batch',
        'department',
        'departments',
        'questions'
    ])->find($id);

    if (!$paper) {
        return back()->with(
            'error',
            'Question Paper not found'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | All Active Courses
    |--------------------------------------------------------------------------
    |
    | Batch দিয়ে Course filter হবে না।
    | Question database-এ ব্যবহৃত সব Active Course এখানে দেখাবে।
    |
    */

    $courses = Course::where(
        'status',
        'Active'
    )
    ->orderBy(
        'name',
        'asc'
    )
    ->get();


    /*
    |--------------------------------------------------------------------------
    | Active Departments
    |--------------------------------------------------------------------------
    */

   return view(
    'layouts.papers.auto_add_question',
    compact(
        'paper',
        'courses'
    )
);
}


/**
 * Get Departments by Course
 */
public function getDepartmentsByCourse($course_id)
{
    $course = Course::find($course_id);

    if (!$course) {
        return response()->json([
            'success' => false,
            'data' => []
        ], 404);
    }

    $departments = $course->departments()
        ->where('departments.status', 'Active')
        ->orderBy('departments.name', 'asc')
        ->get([
            'departments.id',
            'departments.name'
        ]);

    return response()->json([
        'success' => true,
        'data' => $departments
    ]);
}

/**
 * Get Available Questions Count
 */
public function getAvailableQuestions(Request $request)
{
    $courseId     = $request->input('course_id');
    $departmentId = $request->input('department_id');
    $subjectId    = $request->input('subject_id');
    $chapterId    = $request->input('chapter_id');
    $paperId      = $request->input('paper_id');

    /*
    |--------------------------------------------------------------------------
    | Course Required
    |--------------------------------------------------------------------------
    */

    if (!$courseId) {

        return response()->json([
            'success' => true,
            'count'   => 0
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Question Query
    |--------------------------------------------------------------------------
    */

    $query = \App\Models\Question::query();


    /*
    |--------------------------------------------------------------------------
    | Course Filter
    |--------------------------------------------------------------------------
    */

    $query->whereHas(
        'courses',
        function ($q) use ($courseId) {

            $q->where(
                'course_id',
                $courseId
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Department Filter
    |--------------------------------------------------------------------------
    */

    if ($departmentId) {

        $query->whereHas(
            'departments',
            function ($q) use ($departmentId) {

                $q->where(
                    'department_id',
                    $departmentId
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Subject Filter
    |--------------------------------------------------------------------------
    */

    if ($subjectId) {

        $query->whereHas(
            'subjects',
            function ($q) use ($subjectId) {

                $q->where(
                    'subject_id',
                    $subjectId
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Chapter Filter
    |--------------------------------------------------------------------------
    */

    if ($chapterId) {

        $query->whereHas(
            'chapters',
            function ($q) use ($chapterId) {

                $q->where(
                    'chapter_id',
                    $chapterId
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Only MCQ Questions
    |--------------------------------------------------------------------------
    */

    $query->where(
        'type',
        'MCQ'
    );


    /*
    |--------------------------------------------------------------------------
    | Exclude Questions Already Added to This Paper
    |--------------------------------------------------------------------------
    */

    if ($paperId) {

        $paper = Paper::find($paperId);

        if ($paper) {

            $existingQuestionIds =
                $paper->questions()
                    ->pluck('questions.id')
                    ->toArray();


            if (!empty($existingQuestionIds)) {

                $query->whereNotIn(
                    'questions.id',
                    $existingQuestionIds
                );

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $count = $query->count();


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return response()->json([
        'success' => true,
        'count'   => $count
    ]);
}

}