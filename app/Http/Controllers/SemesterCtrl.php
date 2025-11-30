<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Role;
use Auth;
use Image;
use Toastr;
use File;
use Session;

class SemesterCtrl extends Controller
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

      $semesters = Semester::query()
      ->when($search, function($query, $search)
      {
        $query->where('semesters.name', 'like', "%{$search}%");
      })
      ->orderBy('id','DESC')
      ->paginate(25)
      ->appends(['search' => $search]);

      return view('layouts.semesters.index', compact('semesters'));
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
        return view('layouts.semesters.create', compact('courses', 'departments'));
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
            'course_id' => 'required|array',
            'department_id' => 'required|array',
            'name'      => 'required|unique:semesters|max:255',
            'details'   => 'nullable|max:1000',
        ]);
        
        $data = $request->all();

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

        $data['status'] = 'Active';

        // dd($data);

        try{
            Semester::insert($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $semester = Semester::orderBy('id', 'DESC')->first();
        $semester->courses()->attach($courses);
        $semester->departments()->attach($departments);
        
        Session::flash('success', 'New semester successfully created!');

        return redirect()->route('semester.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\subfilter  $semester
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $semester = Semester::find($id);
        return view('layouts.semesters.read', compact('semester'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\subfilter  $semester
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $courses = Course::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $semester = Semester::find($id);
        return view('layouts.semesters.edit', compact('courses', 'departments', 'semester'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\subfilter  $semester
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'course_id'     => 'required|array',
            'department_id' => 'required|array',
            'name'          => 'required|max:255',
            'details'       => 'nullable|max:1000',
        ]);
        
        $data = $request->all();

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

        try{
            Semester::where('id', $id)->update($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        Semester::find($id)->courses()->sync($courses);
        Semester::find($id)->departments()->sync($departments);
        
        Session::flash('success', 'The semester successfully Updated!');

        return redirect()->route('semester.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\subfilter  $semester
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        $semester = Semester::find($id);

        $semester->courses()->detach();
        $semester->departments()->detach();
        $semester->subjects()->detach();
        // $semester->chapters()->detach();
        $semester->questions()->detach();

        $semester->delete();

        Session::flash('Success','The semester successfully delete');
        return redirect()->route('semester.index');
      
      }
      return back()->with('error', 'Permission Denied');
    }    

    /** -------------- custom methods ------------- */
    // Ajax Call
    public function getSemesters($department_id)
    {
        $arr = explode(',', $department_id);
        $items = Semester::where('status', 'Active')
        ->whereHas('departments', function($query) use($arr)
        {
            $query->whereIn('department_id', $arr);
        })->select('id', 'name')->get();
        return response()->json(['data' => $items]);
    }
    
}
