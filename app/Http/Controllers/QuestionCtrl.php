<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filter;
use App\Models\SubFilter;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\Role;
use App\Models\Question;
use App\Models\McqItem;
use App\Models\Label;
use App\Models\AnswerFile;
use App\Models\User;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use Validator;
use URL;

class QuestionCtrl extends Controller
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
    public function index()
    {
        $mcqs = Question::orderBy('id','DESC')->paginate(25);
        return view('layouts.questions.index', compact('mcqs'));
    }

    public function viewQuestion()
    {
        $courses = Course::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $semesters = Semester::where('status', 'Active')->get();
        $subjects = Subject::where('status', 'Active')->get();
        $chapters = Chapter::where('status', 'Active')->get();
        $users = User::where('status', 'Active')->get();

        $filters = Filter::with(['SubFilter'])->get();

        $cat = [
            'course_id' => null,
            'department_id' => null,
            'semester_id' => null,
            'subject_id' => null,
            'chapter_id' => null,
            'type' => null,
            'created_by' => null,
        ];

        $questions = Question::orderBy('id', 'DESC')
        ->where('type', 'MCQ')
        ->where('created_at', 'like', '%'.date('Y-m-d').'%')
        ->paginate(25);

        return view('layouts.questions.view', compact('courses', 'departments', 'semesters', 'subjects', 'chapters', 'cat', 'questions', 'filters', 'users'));
    }

    public function viewQuestionPost(Request $request)
    {
        $data = $request->all();
        $filter_ids = [];
        if(isset($data['filter_ids'])){
            $filter_ids = explode(',', $data['filter_ids']);
        }

        $courses = Course::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $semesters = Semester::where('status', 'Active')->get();
        $subjects = Subject::where('status', 'Active')->get();
        $chapters = Chapter::where('status', 'Active')->get();
        $users = User::where('status', 'Active')->get();

        $filters = Filter::with(['SubFilter'])->get();

        $questions = Question::orderBy('id', 'DESC')
        ->where('type', $data['type']);

        if(isset($data['chapter_id']))
        {
            $questions = $questions->whereHas('chapters', function($query) use($data){
                $query->where('chapter_id', $data['chapter_id']);
            });
        }
        elseif(isset($data['subject_id']))
        {
            $questions = $questions->whereHas('subjects', function($query) use($data){
                $query->where('subject_id', $data['subject_id']);
            });
        }
        elseif(isset($data['semester_id']))
        {
            $questions = $questions->whereHas('semesters', function($query) use($data){
                $query->where('semester_id', $data['semester_id']);
            });
        }
        elseif(isset($data['department_id']))
        {
            $questions = $questions->whereHas('departments', function($query) use($data){
                $query->where('department_id', $data['department_id']);
            });
        }
        elseif(isset($data['course_id']))
        {
            $questions = $questions->whereHas('courses', function($query) use($data){
                $query->where('course_id', $data['course_id']);
            });
        }

        if($filter_ids)
        {
            $questions->whereHas('filters', function($query) use($filter_ids){
                $query->whereIn('sub_filter_id', $filter_ids);
            });
        }

        if(isset($data['created_by']))
        {
            $questions = $questions->where('created_by', $data['created_by']);
        }

        if(isset($data['start']))
        {
            $questions = $questions->whereRaw('DATE(created_at) >= ?', [$data['start']])
            ->whereRaw('DATE(created_at) <= ?', [$data['end']]);
        }

        if(isset($data['title']))
        {
            $questions = $questions->where('title', 'like', '%'.$data['title'].'%');
        }

        $questions = $questions->paginate(25);

        $cat = [
            'course_id' => isset($data['course_id']) ? $data['course_id'] : null,
            'department_id' => isset($data['department_id']) ? $data['department_id'] : null,
            'semester_id' => isset($data['semester_id']) ? $data['semester_id'] : null,
            'subject_id' => isset($data['subject_id']) ? $data['subject_id'] : null,
            'chapter_id' => isset($data['chapter_id']) ? $data['chapter_id'] : null,
            'type' => isset($data['type']) ? $data['type'] : null,
            'created_by' => isset($data['created_by']) ? $data['created_by'] : null,
        ];

        if($request->ajax())
        {
            return view('layouts.questions.paginate', compact('questions'))->render();
        }

        return view('layouts.questions.view', compact('courses', 'departments', 'semesters', 'subjects', 'chapters', 'questions', 'cat', 'filters', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $courses = Course::where('status', 'Active')->get();
      $departments = Department::where('status', 'Active')->get();
      $filters = Filter::with(['SubFilter'])
      ->where('filters.status', 'Active')->orderBy('filters.id', 'ASC')->get();
      $labels = Filter::with(['SubFilter'])->orderBy('filters.id', 'ASC')
      ->where('filters.status', 'Active')
      ->where('filters.label', 'Yes')
      ->get();

      return view('layouts.questions.create', compact('courses', 'departments', 'filters', 'labels'));
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
            'course_id'      => 'required|array',
            'department_id'  => 'required|array',
            'semester_id'    => 'nullable|array',
            'subject_id'     => 'nullable|array',
            'chapter_id'     => 'nullable|array',
            'title'          => 'required|string',
            'answer'         => 'nullable|string',
            'answer_file'    => 'nullable|array',
            'video'          => 'nullable|string',
            'image'          => 'nullable|image|mimes:jpeg,jpg,png,gif|max:500',
            'items'          => 'nullable|array',
            'item_img'       => 'nullable|array',
            'correct'        => 'nullable|array',
            'filter'         => 'nullable|array',
            'label'          => 'nullable|array',
            'explanation'    => 'nullable',
        ]);

        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => 'Data validation failed',
                'errors' => $validator->getMessageBag()->toArray()
            ], 201);

            $this->throwValidationException($request, $validator);
        }

        $items = $correct  = $imgs = $filters = $labels = $courses = $departments = $semesters = $subjects = $chapters = $answerfiles = [];
        
        $data = $request->all();

        if(isset($data['_token'])) { unset($data['_token']); }
        if(isset($data['items']))
        {
            $items = $data['items'];
            unset($data['items']);
        }
        if(isset($data['correct']))
        {
            $correct  = $data['correct'];
            unset($data['correct']);
        }
        if(isset($data['item_img']))
        {
            $imgs = $data['item_img']; 
            unset($data['item_img']); 
        }

        if(isset($data['filter']))
        {
            $filters = $data['filter'];
            unset($data['filter']);
        }

        if(isset($data['label']))
        {
            $labels = $data['label'];
            unset($data['label']);
        }

        if(isset($data['course_id'])) 
        {
            $courses = $data['course_id'];
            unset($data['course_id']);
        }
        if(isset($data['department_id'])) 
        {
            $departments = $data['department_id'];
            unset($data['department_id']);
        }
        if(isset($data['semester_id'])) 
        {
            $semesters = $data['semester_id'];
            unset($data['semester_id']);
        }
        if(isset($data['subject_id'])) 
        {
            $subjects = $data['subject_id'];
            unset($data['subject_id']);
        }
        if(isset($data['chapter_id'])) 
        {
            $chapters = $data['chapter_id'];
            unset($data['chapter_id']);
        }
        if(isset($data['answer_file'])) 
        {
            $answerfiles = $data['answer_file'];
            unset($data['answer_file']);
        }

        $data['status'] = 'Active';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = null;
        $data['created_by'] = Auth::id();

        try {
            // upload title image
            if($request->hasFile('image'))
            {
                $data['image'] = $source->uploadImage($data['image'], 'questions/mcqs/titles/');
            }

            // insert data to database
            Question::insert($data);

            $mcq = Question::orderBy('id', 'DESC')->first();
            $mcq->courses()->attach($courses);
            $mcq->departments()->attach($departments);
            $mcq->semesters()->attach($semesters);
            $mcq->subjects()->attach($subjects);
            $mcq->chapters()->attach($chapters);

            // insert mcq items
            for($x = 0; count($items) > $x; $x++)
            {
                try {
                    McqItem::insert([
                        'question_id' => $mcq->id,
                        'item' => $items[$x],
                        'correct_answer' => isset($correct[0]) && $x == $correct[0] ? true : null,
                        'image' => isset($imgs[$x]) ? $source->uploadImage($imgs[$x], 'questions/mcqs/items/') : null,
                        'image_style' => null,
                    ]);
                }
                catch(\E $e)
                {
                    return $e;
                }
            }

            $mcq->filters()->attach($filters);

            $labelCounter = count($labels);

            for($a = 0; $labelCounter > $a; $a++)
            {
                try {
                    Label::insert(['label' => $labels[$a], 'question_id' => $mcq->id]);
                }
                catch(\E $e)
                {
                    return $e;
                }
            }

            $answerFileCounter = count($answerfiles);

            for($f = 0; $answerFileCounter > $f; $f++)
            {
                $file = [];
                try {
                    if($request->hasFile($answerfiles[$f]))
                    {
                        $file = $source->uploadImage($answerfiles[$f], 'questions/written/answer_files');
                    }
                    AnswerFile::insert([
                        'question_id' => $mcq->id,
                        'file' => isset($answerfiles[$f])? $source->uploadImage($answerfiles[$f], 'questions/written/files/'):null
                    ]);
                }catch(\E $e)
                {
                    return $e;
                }
            }
        }
        catch(\E $e)
        {
            return $e;
        }

        $question = Question::orderBy('id', 'DESC')->with(['mcqitems'])->first();

        return response()->json([
            'success' => true,
            'message' => 'The Question successfully created!',
            'last_id' => $mcq->id,
            'question' => $question
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\mcq  $mcq
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $mcq = Question::with(['getitems'])->find($id);
        return view('layouts.questions.read', compact('mcq'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\mcq  $mcq
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $courses = Course::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $semesters = Semester::where('status', 'Active')->get();
        $subjects = Subject::where('status', 'Active')->get();
        $chapters = Chapter::where('status', 'Active')->get();
        $filters = Filter::with(['SubFilter'])
        ->where('filters.status', 'Active')->orderBy('filters.id', 'ASC')->get();
        $labels = Filter::with(['SubFilter'])->orderBy('filters.id', 'ASC')
        ->where('filters.status', 'Active')
        ->where('filters.label', 'Yes')
        ->get();

        $question = Question::find($id);

        return view('layouts.questions.edit', compact('courses', 'departments', 'semesters', 'subjects', 'chapters', 'filters', 'labels', 'question'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\mcq  $mcq
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $source = new SourceCtrl;
        
        $this->validate($request, [
            'course_id'      => 'required|array',
            'department_id'  => 'required|array',
            'semester_id'    => 'nullable|array',
            'subject_id'     => 'nullable|array',
            'chapter_id'     => 'nullable|array',
            'title'          => 'required|string',
            'answer'         => 'nullable|string',
            'answer_file'    => 'nullable|array',
            'fileid'         => 'nullable|array',
            'video'          => 'nullable|string',
            'image'          => 'nullable|image|mimes:jpeg,jpg,png,gif|max:500',
            'items'          => 'nullable|array',
            'itemid'          => 'nullable|array',
            'item_img'       => 'nullable|array',
            'correct'        => 'nullable|array',
            'filter'         => 'nullable|array',
            'labelid'        => 'nullable|array',
            'label'          => 'nullable|array',
            'explanation'    => 'nullable',
        ]);
        
        $itemid = $items = $correct  = $imgs = $filters = $labelid = $labels = $courses = $departments = $semesters = $subjects = $chapters = $answerfiles = [];
        
        $data = $request->all();
        $question = Question::find($id);
        // dd($data);

        if(isset($data['_token'])) { unset($data['_token']); }
        if(isset($data['_method'])) { unset($data['_method']); }

        if(isset($data['itemid']))
        {
            $itemid = $data['itemid'];
            unset($data['itemid']);
        }
        if(isset($data['items']))
        {
            $items = $data['items'];
            unset($data['items']);
        }
        if(isset($data['correct']))
        {
            $correct  = $data['correct'];
            unset($data['correct']);
        }
        if(isset($data['item_img']))
        {
            $imgs = $data['item_img']; 
            unset($data['item_img']); 
        }

        if(isset($data['filter']))
        {
            $filters = $data['filter'];
            unset($data['filter']);
        }

        if(isset($data['labelid']))
        {
            $labelid = $data['labelid'];
            unset($data['labelid']);
        }

        if(isset($data['label']))
        {
            $labels = $data['label'];
            unset($data['label']);
        }

        if(isset($data['course_id'])) 
        {
            $courses = $data['course_id'];
            unset($data['course_id']);
        }
        if(isset($data['department_id'])) 
        {
            $departments = $data['department_id'];
            unset($data['department_id']);
        }
        if(isset($data['semester_id'])) 
        {
            $semesters = $data['semester_id'];
            unset($data['semester_id']);
        }
        if(isset($data['subject_id'])) 
        {
            $subjects = $data['subject_id'];
            unset($data['subject_id']);
        }
        if(isset($data['chapter_id'])) 
        {
            $chapters = $data['chapter_id'];
            unset($data['chapter_id']);
        }
        if(isset($data['answer_file'])) 
        {
            $answerfiles = $data['answer_file'];
            unset($data['answer_file']);
        }
        if(isset($data['fileid'])) 
        {
            $fileid = $data['fileid'];
            unset($data['fileid']);
        }

        $data['status'] = $request->status;
        $data['updated_by'] = Auth::id();

        // dd($data);

        try {
            // update data to database
            Question::where('id', $id)->update($data);

            if($question->type == 'MCQ')
            {
                $qitems = McqItem::where('question_id', $id)->pluck('id')->toArray();

                // update mcq items
                for($x = 0; count($items) > $x; $x++)
                {
                    if(isset($itemid[$x]) && in_array($itemid[$x], $qitems))
                    {
                        // find item in database
                        $item = McqItem::find($itemid[$x]);
                        $xitemimg = public_path($item->image);

                        // make an array for store
                        $itemarr = [];
                        $itemarr['item'] = $items[$x];
                        $itemarr['correct_answer'] = isset($correct[0]) && $x == $correct[0] ? true : null;

                        // check new file select make for upload
                        if(isset($imgs[$x]))
                        {
                            $itemarr['image'] = $source->uploadImage($imgs[$x], 'questions/mcqs/items/');
                            File::delete($xitemimg);
                        }else{
                            unset($itemarr['image']);
                        }

                        try {
                            // store array to the dabase
                            McqItem::where('id', $itemid[$x])->update($itemarr);
                            //delete existing image
                            if(isset($imgs[$x]) && File::exists($xitemimg))
                            {
                                File::delete($xitemimg);
                                unset($itemarr['image']);
                            }
                        }
                        catch(\E $e)
                        {
                            return $e;
                        }
                        // unset item from $qitems array
                        unset($qitems[array_search($itemid[$x], $qitems)]);
                    }else{
                        McqItem::insert([
                            'question_id' => $id,
                            'item' => $items[$x],
                            'correct_answer' => isset($correct[0]) && $x == $correct[0] ? true : null,
                            'image' => isset($imgs[$x]) ? $source->uploadImage($imgs[$x], 'questions/mcqs/items/') : null
                        ]);
                    }
                }

                if(isset($qitems))
                {
                    foreach($qitems as $val)
                    {
                        $mcqitem = McqItem::find($val);
                        if($mcqitem->image)
                        {
                            $mcqximg = public_path($mcqitem->image);
                            if(File::exists($mcqximg))
                            {
                                File::delete($mcqximg);
                            }
                        }
                        $mcqitem->delete();
                    }
                }
            }

            /** update manay to many relationships */
            $mcq = Question::find($id);
            $mcq->courses()->sync($courses);
            $mcq->departments()->sync($departments);
            $mcq->semesters()->sync($semesters);
            $mcq->subjects()->sync($subjects);
            $mcq->chapters()->sync($chapters);
            $mcq->filters()->sync($filters);

            /** update labels */
            $dblabels = Label::where('question_id', $id)->pluck('id')->toArray();
            for($a = 0; count($labels) > $a; $a++)
            {
                try {
                    if(isset($labelid[$a]) && in_array($labelid[$a], $dblabels))
                    {
                        Label::where('id', $labelid[$a])->update(['label' => $labels[$a]]);
                        // unset item from $qitems array
                        unset($dblabels[array_search($labelid[$a], $dblabels)]);
                    }else{
                        Label::insert(['question_id' => $mcq->id, 'label' => $labels[$a]]);
                    }
                }
                catch(\E $e)
                {
                    return $e;
                }
            }

            // delete labels
            foreach($dblabels as $k => $val)
            {
                Label::find($val)->delete();
            }

            /** update written answer fiels */
            // if($question->type == 'Written')
            // {
            //     $fileids = AnswerFile::where('question_id', $id)->pluck('id')->toArray();
            //     dd($answerfiles);
            //     for($f = 0; count($answerfiles) > $f; $f++)
            //     {
            //         if(isset($fileid[$f]) && in_array($fileid[$f], $fileids)){
            //             $file = AnswerFile::find($fileid[$f]);
            //             $xfile = public_path($file->file);
            //             try {
            //                 AnswerFile::where('id', $file->id)->update([
            //                     'file' => $source->uploadImage($answerfiles[$f], 'questions/written/files/')
            //                 ]);
            //                 if(File::exists($xfile))
            //                 {
            //                     File::delete($xfile);
            //                 }
            //             }catch(\E $e)
            //             {
            //                 return $e;
            //             }
            //             // unset item from $fileids array
            //             unset($fileids[array_search($fileid[$f], $fileids)]);
                        
            //         }else{
            //             AnswerFile::insert([
            //                 'file' => $source->uploadImage($answerfiles[$f], 'questions/written/files/')
            //             ]);
            //         }
            //     }
            // }
        }
        catch(\E $e)
        {
            return $e;
        }
        
        Session::flash('success', 'The question successfully Updated!');

        return redirect()->route('question.show', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\mcq  $mcq
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $mcq = Question::find($id);

        if($mcq->type == 'MCQ')
        {
            $items = McqItem::where('question_id', $mcq->id)->get();
            foreach($items as $item){
                $eximg = public_path($item->image);
                if(File::exists($eximg))
                {
                    File::delete($eximg);
                }
                $item->delete();
            }
        }

        if($mcq->type == 'Written')
        {
            $files = AnswerFile::where('question_id', $mcq->id)->get();
            foreach($files as $item){
                $exfile = public_path($item->file);
                if(File::exists($exfile))
                {
                    File::delete($exfile);
                }
                $item->delete();
            }
        }

        $labels = Label::where('question_id', $mcq->id)->get();
        foreach($labels as $label)
        {
            $label->delete();
        }

        $mcq->courses()->detach();
        $mcq->departments()->detach();
        $mcq->semesters()->detach();
        $mcq->subjects()->detach();
        $mcq->chapters()->detach();
        $mcq->filters()->detach();

        $mcq->delete();

        Session::flash('Success','The question successfully deleted');
        return redirect()->route('question.index');
    }

    //ajax request methods
    public function Questions(Request $request)
    {
        $questions = Question::orderBy('id', 'DESC')
        ->where('type', $request->type)
        ->where('title', 'like', '%'.$request->title.'%')
        ->with(['mcqitems'])->get();
        
        return response()->json([
            'success' => true,
            'questions' => $questions
        ], 201);
    }

    public function QuestionSearch(Request $request)
    {
        $mcqs = Question::where('title', 'like', '%'.$request->search.'%')->paginate(25);
        return view('layouts.questions.index', compact('mcqs'));
    }
    
}
