<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;
use App\Models\Batch;
use App\Models\Group;
use App\Models\Routine;
use App\Models\RoutineItem;
use App\Models\Role;
use App\Models\Paper;
use App\Models\Subject;
use App\Models\RoutineSubjectColumn;
use App\Models\RoutineDepartmentColumn;
use App\Models\RoutineItemRow;
use App\Models\RoutineSubjectRow;
use App\Models\RoutineDepartmentRow;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use DB;

class RoutineCtrl extends Controller
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
        $routines = Routine::orderBy('id','DESC')->paginate(25);
        return view('layouts.routines.index', compact('routines'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $courses = Course::orderBy('id', 'DESC')->where('status', 'Active')->get();
        $batches = Batch::where('status', 'Active')->get();
        $subjects = Subject::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        return view('layouts.routines.create', compact('courses', 'batches', 'departments', 'subjects'));
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

        $this->validate($request, [
          'course_id' => 'required|numeric',
          'name'      => 'required|unique:groups|max:255',
          'header'    => 'nullable|string',
          'details'   => 'nullable|max:1000',
          'pdf'       => 'nullable|mimes:pdf|max:10000',
          'subjects' => 'nullable',
          'departments' => 'nullable'
        ]);
        
        $data = $request->all();

        if(isset($data['_token']))
        {
          unset($data['_token']);
        }

        $data['status'] = isset($data['status']) ? 'Published' : 'Unpublished';

        $data['created_by'] = Auth::id();

        $subjects = $data['subjects'];
        $departments = $data['departments'];

        if(isset($data['subjects']))
        {
          unset($data['subjects']);
        }

        if(isset($data['departments']))
        {
          unset($data['departments']);
        }

        try {
            // upload pdf file
            if($request->hasFile('pdf'))
            {
              $data['pdf'] = $source->uploadImage($data['pdf'], 'routine/');
            }
            
            $routine = Routine::create($data);

            $routine->subjects()->attach($subjects);
            $routine->departments()->attach($departments);

            // insert subjects columns
            foreach($subjects as $subject)
            {
              DB::table('routine_subject_columns')->insert(
                [
                  'routine_id' => $routine->id,
                  'subject_id' => $subject
                ]
              );
            }

            // insert departments columns
            foreach($departments as $department)
            {
              DB::table('routine_department_columns')->insert(
                [
                  'routine_id' => $routine->id,
                  'department_id' => $department
                ]
              );
            }
        }
        catch(\Exception $e)
        {
          return $e->getMessage();
        }
        
        Session::flash('success', 'New routine successfully created!');

        return redirect()->route('routine.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $courses = Course::where('status', 'Active')->get();
        $batches = Batch::where('status', 'Active')->get();
        $subjects = Subject::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $routine = Routine::find($id);

        $subject_columns = $routine->subjects()->pluck('subject_id')->toArray();
        $department_columns = $routine->departments()->pluck('department_id')->toArray();

        return view('layouts.routines.edit', compact('routine', 'courses', 'departments', 'batches', 'subjects', 'departments', 'subject_columns', 'department_columns'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $source = new SourceCtrl;

        $this->validate($request, [
            'course_id' => 'required|numeric',
            'name'      => 'required|max:255',
            'header'    => 'nullable|max:255',
            'details'   => 'nullable|max:1000',
            'pdf'       => 'nullable|mimes:pdf|max:10000',
            'subjects'  => 'required|array',
            'departments' => 'required|array',
        ]);
        
        $data = $request->all();
        $routine = Routine::find($id);
        
        //get existing file
        $xpdf = public_path($routine->pdf);

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }
        if(isset($data['_method']))
        {
            unset($data['_method']);
        }

        // dd($ex_subjects);

        $subjects = $data['subjects'];
        $departments = $data['departments'];

        if(isset($data['subjects']))
        {
          unset($data['subjects']);
        }

        if(isset($data['departments']))
        {
          unset($data['departments']);
        }

        // dd($subjects[1]);

        try {

          $routine->subjects()->sync($subjects);
          $routine->departments()->sync($departments);

            // upload pdf file
            if($request->hasFile('pdf'))
            {
                $data['pdf'] = $source->uploadImage($data['pdf'], 'routine/');
            }

            Routine::where('id', $id)->update($data);

            if($request->hasFile('pdf') && File::exists($xpdf))
            {
                File::delete($xpdf);
            }
        }
        catch(\E $e)
        {
            return $e;
        }
        
        Session::flash('success', 'The routine successfully Updated!');

        return redirect()->route('routine.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        $routine = Routine::find($id);
        $xpdf = public_path($routine->pdf);

        if(File::exists($xpdf))
        {
            File::delete($xpdf);
        }

        //delete routine table
        $routine->delete();

        Session::flash('Success','The routine successfully deleted');
        return redirect()->route('routine.index');
      }
      return back()->with('error', 'Permission Denied');
    }
}
