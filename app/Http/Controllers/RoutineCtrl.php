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
use Auth;
use Image;
use Toastr;
use File;
use Session;

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
        $departments = Department::where('status', 'Active')->get();
        return view('layouts.routines.create', compact('courses', 'batches', 'departments'));
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
        ]);
        
        $data = $request->all();
        if(isset($data['_token']))
        {
            unset($data['_token']);
        }

        if(isset($data['status']))
        {
            $data['status'] = 'Published';
        }
        else
        {
            $data['status'] = 'Unpublished';
        }

        $data['created_by'] = Auth::id();

        try{
            // upload pdf file
            if($request->hasFile('pdf'))
            {
                $data['pdf'] = $source->uploadImage($data['pdf'], 'routine/');
            }
            Routine::insert($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        // $batch = Group::orderBy('id', 'DESC')->first();
        
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
        $group = Group::find($id);
        return view('layouts.routines.read', compact('group'));
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
        $departments = Department::where('status', 'Active')->get();
        $routine = Routine::find($id);
        return view('layouts.routines.edit', compact('routine', 'courses', 'departments', 'batches'));
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

        try {
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

      Session::flash('error', 'Permission restricted!');
      return back();
    }


  /** ================== Add Item Section =================== */

  public function addItem($id)
  {
    $routine = Routine::find($id);
    $papers = Paper::orderBy('id', 'DESC')->get();
    $subjects = Subject::orderBy('id', 'DESC')->get();
    $departments = Department::orderBy('id', 'DESC')->get();
    
    $items = RoutineItem::orderBy('id', 'DESC')->get();

    return view('layouts.routines.add-item', compact('routine', 'papers', 'subjects', 'departments', 'items'));
  }
  
  public function addItemPost(Request $request)
  {
    $data = $request->all();
    
    if(isset($data['_token']))
    {
      unset($data['_token']);
    }

    try {
      RoutineItem::insert($data);
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }

    // return $this->addItem($data['routine_id']);
    return redirect()->route('routine.add-item', $data['routine_id']);
  }
}
