<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\Role;
use App\Models\Vendor;
use Auth;
use Image;
use Toastr;
use File;
use Session;

class ChapterCtrl extends Controller
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
      $search = $request->input('search');
      $chapters = Chapter::query()
      ->when($search, function($query, $search)
      {
        return $query->where('name', 'like', "%{$search}%");
      })
      ->orderBy('id','DESC')
      ->paginate(25)
      ->appends(['search' => $search]);

      return view('layouts.chapters.index', compact('chapters'));
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
        $subjects = Subject::where('status', 'Active')->get();
        return view('layouts.chapters.create', compact('courses', 'departments', 'subjects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'course_id'     => 'nullable|array',
            'department_id' => 'nullable|array',
            'semester_id'   => 'nullable|array',
            'subject_id'    => 'nullable|array',
            'name'          => 'required|unique:chapters|max:255',
            'details'       => 'nullable|max:1000',
        ]);
        
        $data = $request->all();
        $courses = $departments = $semesters = $subjects = [];

        if(isset($data['_token']))
        {
            unset($data['_token']);
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

        $data['status'] = 'Active';

        try{
            Chapter::insert($data);

            $chapter = Chapter::orderBy('id', 'DESC')->first();
            // $chapter->courses()->attach($courses);
            // $chapter->departments()->attach($departments);
            // $chapter->semesters()->attach($semesters);
            $chapter->subjects()->attach($subjects);
        }
        catch(\E $e)
        {
            return $e;
        }
        
        Session::flash('success', 'New chapter successfully created!');

        return redirect()->route('chapter.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\department  $chapter
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $chapter = Chapter::find($id);
        return view('layouts.chapters.read', compact('chapter'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\department  $chapter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $courses = Course::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $subjects = Subject::where('status', 'Active')->get();
        $semesters = Semester::where('status', 'Active')->get();
        $chapter = Chapter::find($id);
        return view('layouts.chapters.edit', compact('courses', 'departments', 'subjects', 'semesters', 'chapter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\department  $chapter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'course_id'     => 'nullable|array',
            'department_id' => 'nullable|array',
            'semester_id'   => 'nullable|array',
            'subject_id'    => 'nullable|array',
            'name'          => 'required|max:255',
            'details'       => 'nullable|max:1000',
        ]);
        
        $data = $request->all();
        $courses = $departments = $semesters = $subjects = [];

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }
        if(isset($data['_method']))
        {
            unset($data['_method']);
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

        try{
            Chapter::where('id', $id)->update($data);

            $chapter = Chapter::find($id);
            // $chapter->courses()->sync($courses);
            // $chapter->departments()->sync($departments);
            // $chapter->semesters()->sync($semesters);
            $chapter->subjects()->sync($subjects);
        }
        catch(\E $e)
        {
            return $e;
        }
        
        Session::flash('success', 'The chapter successfully Updated!');

        return redirect()->route('chapter.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\department  $chapter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Auth::user()->AuthorizeRoles('SuperAdmin'))
        {
            $chapter = Chapter::find($id);

            // remove relationship
            // $chapter->courses()->detach();
            // $chapter->departments()->detach();
            // $chapter->semesters()->detach();
            $chapter->subjects()->detach();
            $chapter->questions()->detach();

            $chapter->delete();

            Session::flash('Success','This chapter Successfully deleted');
            return redirect()->route('chapter.index');
        }        

        Session::flash('error', 'Permission restricted!');
        return back();
    }

    /** -------------- custom methods ------------- */
    // Ajax Call
    public function getChapters($subject_id)
    {
        $arr = explode(',', $subject_id);
        $items = Chapter::whereHas('subjects', function($query) use($arr) {
            $query->whereIn('subject_id', $arr);
        })->select('id', 'name')->get();
        return response()->json(['data' => $items]);
    }    
}
