<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;
use App\Models\Role;
use App\Models\Vendor;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use DB;

class DepartmentCtrl extends Controller
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

      $departments = Department::query()
      ->when($search, function($query, $search)
      {
        $query->where('departments.name', 'like', "%{$search}%");
      })
      ->leftJoin('courses', 'courses.id', 'departments.course_id')
      ->orderBy('id','desc')
      ->select('departments.*', 'courses.name as course_name')
      ->paginate(25)
      ->appends(['search' => $search]);
      
      return view('layouts.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $courses = Course::where('status', 'Active')->get();
        return view('layouts.departments.create', compact('courses'));
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
            'name'      => 'required|unique:departments|max:255',
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

        $data['status'] = 'Active';

        try{
            Department::insert($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $department = Department::orderBy('id', 'DESC')->first();
        $department->courses()->attach($courses);
        
        Session::flash('success', 'New department successfully created!');

        return redirect()->route('department.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\department  $department
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $department = Department::find($id);
        return view('layouts.departments.read', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\department  $department
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $courses = Course::where('status', 'Active')->get();
        $department = Department::find($id);
        return view('layouts.departments.edit', compact('courses', 'department'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\department  $department
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'course_id' => 'required|array',
            'name'      => 'required|max:255',
            'details'   => 'nullable|max:1000',
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

        try{
            Department::where('id', $id)->update($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $department = Department::find($id);
        $department->courses()->sync($courses);
        
        Session::flash('success', 'The department successfully Updated!');

        return redirect()->route('department.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\department  $department
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Auth::user()->AuthorizeRoles('SuperAdmin'))
        {
            $department = Department::find($id);

            // remove relationship
            $department->courses()->detach();
            $department->semesters()->detach();
            $department->subjects()->detach();
            // $department->chapters()->detach();
            $department->questions()->detach();

            // delete department table
            $department->delete();

            Session::flash('Success','The department Successfully delete');
            return redirect()->route('department.index');
        }

        Session::flash('error', 'Permission restricted!');
        return back();
    }

    /** -------------- custom methods ------------- */
    // Ajax Call
    public function getDepartments($course_id)
    {
        $arr = explode(',', $course_id);
        $items = Department::whereHas('courses', function($q) use($arr) {
            $q->whereIn('course_id', $arr);
        })->select('id', 'name')->get();
        return response()->json(['data' => $items]);
    }
    public function getDepartmentsByBatch($batch_id)
    {
        $arr = explode(',', $batch_id);
        $items = Department::whereHas('batches', function($q) use($arr) {
            $q->whereIn('batch_id', $arr);
        })->select('id', 'name')->get();
        return response()->json(['data' => $items]);
    }
    
}
