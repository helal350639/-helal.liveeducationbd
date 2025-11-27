<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Role;
use App\Models\Vendor;
use Auth;
use Image;
use Toastr;
use File;
use Session;

class SubjectCtrl extends Controller
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

      $subjects = Subject::query()
      ->when($search, function($query, $search)
      {
        $query->where('name', 'like', "%{$search}%");
      })
      ->orderBy('id','DESC')
      ->paginate(25)
      ->appends(['search' => $search]);
      
      return view('layouts.subjects.index', compact('subjects'));
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
        $semesters = Semester::where('status', 'Active')->get();
        return view('layouts.subjects.create', compact('courses', 'departments', 'semesters'));
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
            'course_id'   => 'required|array',
            'department_id' => 'required|array',
            'semester_id' => 'nullable|array',
            'name'        => 'required|unique:subjects|max:255',
            'details'     => 'nullable|max:1000',
        ]);
        
        $data = $request->all();
        $courses = $departments = $semesters = [];

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

        $data['status'] = 'Active';
        $data['created_by'] = Auth::id();

        try {
            Subject::insert($data);

            $subject = Subject::orderBy('id', 'DESC')->first();
            $subject->courses()->attach($courses);
            $subject->departments()->attach($departments);
            $subject->semesters()->attach($semesters);
        }
        catch(\E $e)
        {
            return $e;
        }
        
        Session::flash('success', 'New subject successfully created!');
        return redirect()->route('subject.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\department  $subject
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subject = Subject::find($id);
        return view('layouts.subjects.read', compact('subject'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\department  $subject
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $courses = Course::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $semesters = Semester::where('status', 'Active')->get();
        $subject = Subject::find($id);
        return view('layouts.subjects.edit', compact('courses', 'departments', 'semesters' , 'subject'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\department  $subject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'course_id'   => 'required|array',
            'department_id' => 'required|array',
            'semester_id' => 'nullable|array',
            'name'        => 'required|max:255',
            'details'     => 'nullable|max:1000',
        ]);
        
        $data = $request->all();
        $courses = $departments = $semesters = [];

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

        try {
            Subject::where('id', $id)->update($data);

            $subject = Subject::find($id);
            $subject->courses()->sync($courses);
            $subject->departments()->sync($departments);
            $subject->semesters()->sync($semesters);
        }
        catch(\E $e)
        {
            return $e;
        }
        
        Session::flash('success', 'The subject successfully Updated!');

        return redirect()->route('subject.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\department  $subject
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        $subject = Subject::find($id);

        // remove relationship
        $subject->courses()->detach();
        $subject->departments()->detach();
        $subject->semesters()->detach();
        $subject->chapters()->detach();
        $subject->questions()->detach();

        $subject->delete();

        Session::flash('Success','This subject Successfully delete');
        return redirect()->route('subject.index');        
      }
      return back()->with('error', 'Permission Denied');
    }

    /** -------------- custom methods ------------- */
    // Ajax Call
    public function getSubjects($department_id)
    {
        $arr = explode(',', $department_id);
        $items = Subject::whereHas('departments', function($query) use($arr) {
            $query->whereIn('department_id', $arr);
        })->select('id', 'name')->get();
        return response()->json(['data' => $items]);
    }

    public function getSemsSubjects($semester_id)
    {
        $arr = explode(',', $semester_id);
        $items = Subject::whereHas('semesters', function($query) use($arr) {
            $query->whereIn('semester_id', $arr);
        })->select('id', 'name')->get();
        return response()->json(['data' => $items]);
    }    
}
