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

      return view('layouts.papers.index', compact('papers', 'courses', 'batches', 'departments'));
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
        
        $data = $request->all();
        // dd($data);

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }

        if(isset($data['banner']))
        {
            $data['banner'] = $source->uploadImage($data['banner'], 'papers/');
        }

        if(!isset($data['format']))
        {
            $data['format'] = 0;
        }

        $data['created_at'] = date('Y-m-d H:i:s');

        try{
            Paper::insert($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $paper = Paper::orderBy('id', 'DESC')->first();

        // return response()->json(['success' => 'true', 'message' => 'File successfully uploaded!', 'paper' => $paper], 200);
        Session::flash('success', 'The question paper successfully created.');
        return redirect()->route('paper.show', $paper->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $paper = Paper::find($id);
        return view('layouts.papers.read', compact('paper'));
    }

    public function view($id)
    {
        if(!is_null(Session::get('_paper')))
        {
            Session::forget('_paper');
        }
        $paper = Paper::find($id);
        return view('layouts.papers.view', compact('paper'));
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
        $validator = Validator::make($request->all(), [
            'header' => 'required',
            'banner'   => 'nullable|image|mimes:jpeg,jpg,png,pdf|max:1000',
        ]);

        if($validator->fails()){
            return response()->json(
                [
                    'success' => false,
                    'message' => 'File upload failed',
                    'errors' => $validator->getMessageBag()->toArray()
                ],
                201
            );
        }

        $paper = Paper::find($id);
        $xbanner = public_path($paper->banner);
        
        $data = $request->all();

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

        try{
            Paper::where('id', $id)->update($data);

            if(File::exists($xbanner))
            {
                File::delete($xbanner);
            }
        }
        catch(\E $e)
        {
            return $e;
        }

        $paper = Paper::find($id);
        Session::flash('success', 'The question paper successfully updated!');
        
        return redirect()->route('paper.edit', $paper->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
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
        return redirect()->route('paper.index');
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
            ]
        );
        // return 'I am responding from server with id:'.$qid;
    }
}
