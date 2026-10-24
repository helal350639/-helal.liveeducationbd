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
use App\Models\Syllabus;
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
    public function index(Request $request)
    {
      $subject_id = $request->input('subject_id');
      $title = $request->input('title');
      $mcqs = Question::orderBy('id','DESC');
      if(isset($request->subject_id))
      {
        $mcqs = $mcqs->whereHas('subjects', function($query) use($request){
          $query->where('subject_id', $request->subject_id);
        });
      }
      if(isset($request->title))
      {
        $mcqs = $mcqs->where('title', 'like', '%'.$title.'%');        
      }
      $mcqs = $mcqs->paginate(25)->withQueryString();
    
      $subjects = Subject::where('status', 'Active')
      ->select('id', 'name')
      ->get();
      return view('layouts.questions.index', compact('mcqs', 'subjects', 'subject_id', 'title'));
    }

    public function viewQuestion()
    {
        $courses = Course::where('status', 'Active')
        ->select('id', 'name')
        ->get();
        $departments = Department::where('status', 'Active')
        ->select('id', 'name')
        ->get();
        $semesters = Semester::where('status', 'Active')
        ->select('id', 'name')
        ->get();
        $subjects = Subject::where('status', 'Active')
        ->select('id', 'name')
        ->get();
        $chapters = Chapter::where('status', 'Active')
        ->select('id', 'name')
        ->get();
        $users = User::where('status', 'Active')
        ->select('id', 'name')
        ->get();

        $filters = Filter::with(['SubFilter'])
        ->orderBy('order_number', 'asc')
        ->get();

        $cat = [
            'course_id' => null,
            'department_id' => null,
            'semester_id' => null,
            'subject_id' => null,
            'chapter_id' => null,
            'type' => null,
            'created_by' => null,
        ];

        $questions = Question::with(['getitems', 'mcqitems'])->orderBy('id', 'DESC')
        ->where('type', 'MCQ')
        ->paginate(25);

        return view('layouts.questions.view', compact('courses', 'departments', 'semesters', 'subjects', 'chapters', 'cat', 'questions', 'filters', 'users'));
    }

    public function viewQuestionPost(Request $request)
    {
        // LEBD-3: Empty HTML select/date inputs must NOT become database filters.
        // Preserve the original relationships, subfilters and 25-per-page AJAX view.
        $request->validate([
            'type' => 'nullable|in:MCQ,Written,Video',
            'course_id' => 'nullable|integer|min:1',
            'department_id' => 'nullable|integer|min:1',
            'semester_id' => 'nullable|integer|min:1',
            'subject_id' => 'nullable|integer|min:1',
            'chapter_id' => 'nullable|integer|min:1',
            'created_by' => 'nullable|integer|min:1',
            'start' => 'nullable|date_format:Y-m-d',
            'end' => 'nullable|date_format:Y-m-d',
            'title' => 'nullable|string|max:1000',
            'syllabus' => 'nullable|integer|min:1',
            'filter_ids' => 'nullable|string',
        ]);

        $questions = Question::query()->with(['getitems', 'mcqitems'])->orderByDesc('id')
            ->where('type', $request->input('type', 'MCQ'));

        foreach (['course_id' => 'courses', 'department_id' => 'departments',
                  'semester_id' => 'semesters', 'subject_id' => 'subjects',
                  'chapter_id' => 'chapters'] as $field => $relation) {
            if ($request->filled($field)) {
                $id = (int) $request->input($field);
                $questions->whereHas($relation, function ($q) use ($field, $id) {
                    $q->where($field, $id);
                });
            }
        }
        if ($request->filled('created_by')) {
            $questions->where('created_by', (int) $request->input('created_by'));
        }
        if ($request->filled('start')) {
            $questions->whereDate('created_at', '>=', $request->input('start'));
        }
        if ($request->filled('end')) {
            $questions->whereDate('created_at', '<=', $request->input('end'));
        }
        if ($request->filled('title')) {
            $questions->where('title', 'like', '%' . trim($request->input('title')) . '%');
        }
        if ($request->filled('syllabus')) {
            $syllabusId = (int) $request->input('syllabus');
            $syllabus = Syllabus::find($syllabusId);
            $questions->whereIn('id', $syllabus ? $syllabus->questions()->pluck('questions.id') : []);
        }
        $filterIds = array_values(array_unique(array_filter(array_map('intval',
            explode(',', (string) $request->input('filter_ids', ''))
        ), fn ($n) => $n > 0)));
        if ($filterIds) {
            $questions->whereHas('filters', function ($q) use ($filterIds) {
                $q->whereIn('sub_filter_id', $filterIds);
            });
        }
        $questions = $questions->paginate(25);

        if ($request->ajax()) {
            return view('layouts.questions.paginate', compact('questions'))->render();
        }
        $courses = Course::where('status', 'Active')->select('id', 'name')->get();
        $departments = Department::where('status', 'Active')->select('id', 'name')->get();
        $semesters = Semester::where('status', 'Active')->select('id', 'name')->get();
        $subjects = Subject::where('status', 'Active')->select('id', 'name')->get();
        $chapters = Chapter::where('status', 'Active')->select('id', 'name')->get();
        $users = User::where('status', 'Active')->select('id', 'name')->get();
        $filters = Filter::with(['SubFilter'])->orderBy('order_number')->get();
        $cat = [
            'course_id' => $request->input('course_id'),
            'department_id' => $request->input('department_id'),
            'semester_id' => $request->input('semester_id'),
            'subject_id' => $request->input('subject_id'),
            'chapter_id' => $request->input('chapter_id'),
            'type' => $request->input('type', 'MCQ'),
            'created_by' => $request->input('created_by'),
        ];
        return view('layouts.questions.view', compact(
            'courses', 'departments', 'semesters', 'subjects', 'chapters',
            'questions', 'cat', 'filters', 'users'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
      // Fix22: corrected legacy cutoff fallback; read-only JSON option source for the NEW Question filters.
      // Uses the exact fixed-stage order / fresh visibility criteria of Filter Management.
      if ($request->query('_lebd_fresh_filter_options') === '1') {
          $stageLabels = [
              'প্রশ্ন টাইপ','কোর্সের নাম','প্রশ্ন টপিক','ডিপার্টমেন্ট নাম',
              'বুক প্রকাশনির নাম','সাবজেক্ট নাম','চ্যাপ্টার নাম',
              'প্রশ্ন সোর্স','সাব-সোর্স','পরীক্ষার সন','Question Quality filter',
          ];
          $mainFilters = Filter::where('status', 'Active')
              ->orderBy('order_number')->orderBy('id')->get(['id','name']);
          $stages = [];
          foreach ($stageLabels as $i => $label) {
              $main = $mainFilters->get($i);
              $stages[] = ['position' => $i + 1, 'name' => $label,
                  'filter_id' => $main ? (int)$main->id : 0];
          }
          $baselineReady = \Illuminate\Support\Facades\Schema::hasTable('lebd_filter_visibility_baselines');
          $connectionReady = \Illuminate\Support\Facades\Schema::hasTable('sub_filter_connections');
          $cutoff = $baselineReady
              ? (int) (\Illuminate\Support\Facades\DB::table('lebd_filter_visibility_baselines')
                  ->where('id', 1)->value('legacy_max_sub_filter_id') ?? 18)
              : 18; // Fix22: legacy records precede confirmed new IDs 19, 21, 23; never MAX(id)
          $filterIds = array_values(array_filter(array_column($stages, 'filter_id')));
          $items = SubFilter::query()->where('id', '>', $cutoff)
              ->whereIn('filter_id', $filterIds)->where('status', 'Active')
              ->orderBy('id')->get(['id','filter_id','name','parent_id']);
          $connections = [];
          if ($connectionReady && $items->isNotEmpty()) {
              $links = \Illuminate\Support\Facades\DB::table('sub_filter_connections')
                  ->whereIn('child_sub_filter_id', $items->pluck('id')->all())
                  ->get(['child_sub_filter_id','parent_sub_filter_id']);
              foreach ($links as $link) {
                  $connections[(int)$link->child_sub_filter_id][] = (int)$link->parent_sub_filter_id;
              }
          }
          $options = $items->map(function ($item) use ($connections) {
              $parents = $connections[(int)$item->id] ?? [];
              if ($item->parent_id && !in_array((int)$item->parent_id, $parents, true)) {
                  $parents[] = (int)$item->parent_id;
              }
              return ['id'=>(int)$item->id, 'filter_id'=>(int)$item->filter_id,
                  'name'=>$item->name, 'parents'=>$parents];
          })->values();
          return response()->json([
              'ok'=>true, 'baseline_ready'=>$baselineReady,
              'connection_ready'=>$connectionReady, 'stages'=>$stages,
              'options'=>$options, 'fresh_count'=>$items->count(),
          ])->header('Cache-Control','no-store, no-cache, must-revalidate');
      }

      $courses = Course::where('status', 'Active')
      ->select('id', 'name')
      ->get();
      $departments = Department::where('status', 'Active')
      ->select('id', 'name')
      ->get();
      
      $filters = Filter::with(['SubFilter'])
      ->where('filters.status', 'Active')
      ->orderBy('filters.id', 'ASC')
      ->get();

      $labels = Filter::with(['SubFilter'])
      ->orderBy('filters.id', 'ASC')
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
          'video'          => 'nullable|url',
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

        // video url to embed url convert
        if($data['type'] == 'Video')
        {
          $url = $data['video'];
          $data['video'] = $source->videoId($url);
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
              catch(\Exception $e)
              {
                  return $e->getMessage();
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
            'video'          => 'nullable|url',
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

        // video url to embed url convert
        if($data['type'] == 'Video')
        {
          $url = $data['video'];
          $data['video'] = $source->videoId($url);
        }

        $data['status'] = $request->status;
        $data['updated_by'] = Auth::id();

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
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
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
      return back()->with('error', 'Permission Denied');
    }

    //ajax request methods
    public function Questions(Request $request)
    {
        $questions = Question::orderBy('id', 'DESC')
        ->where('type', $request->type)
        ->where('title', 'like', '%'.$request->title.'%')
        ->with(['mcqitems'])
        // ->select('id', 'title')
        ->limit(10)
        ->get();
        
        return response()->json([
            'success' => true,
            'questions' => $questions
        ], 201);
    }

    public function QuestionSearch(Request $request)
    {
        $mcqs = Question::where('title', 'like', '%'.$request->search.'%')
        ->paginate(25);
        return view('layouts.questions.index', compact('mcqs'));
    }

    public function saveTitle(Request $request)
    {
      $id = $request->id;
      $title = $request->title;

      Question::where('id', $id)->update([
        'title' => $title
      ]);

      return response()->json([
        'question' => $title
      ], 200);
    }
    
}
