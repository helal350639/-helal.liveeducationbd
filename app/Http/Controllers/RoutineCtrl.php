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
            // Routine::insert($data);
            $routine = Routine::create($data);

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
        $subjects = Subject::where('status', 'Active')->get();
        $departments = Department::where('status', 'Active')->get();
        $routine = Routine::find($id);

        $subject_columns = RoutineSubjectColumn::where('routine_id', $id)
        ->pluck('subject_id')
        ->toArray();

        $department_columns = RoutineDepartmentColumn::where('routine_id', $id)
        ->pluck('department_id')
        ->toArray();

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

    $subjects = RoutineSubjectColumn::leftJoin('subjects', 'subjects.id', 'routine_subject_columns.subject_id')
    ->orderBy('routine_subject_columns.id', 'ASC')
    ->where('routine_subject_columns.routine_id', $id)
    ->select('routine_subject_columns.*', 'subjects.name')
    ->get();

    $departments = RoutineDepartmentColumn::leftJoin('departments', 'departments.id', 'routine_department_columns.department_id')
    ->orderBy('routine_department_columns.id', 'ASC')
    ->where('routine_department_columns.routine_id', $id)
    ->select('routine_department_columns.*', 'departments.name')
    ->get();

    $routine_rows = RoutineItemRow::where('routine_item_rows.routine_id', $id)
    ->with(['subjects', 'departments'])
    ->select('routine_item_rows.*')
    ->get();
    // dd($routine_rows);

    return view('layouts.routines.add-item', compact('routine', 'papers', 'subjects', 'departments', 'routine_rows'));
  }
  
  public function addItemPost(Request $request)
  {
    $data = $request->all();

    // dd($data);

    try {

      $routineItem = RoutineItemRow::create([
        'routine_id' => $data['routine_id'],
        'paper_name' => $data['paper_name'],
        'exam_at' => $data['exam_at']
      ]);

      foreach($data['subject_id'] as $key => $value)
      {
        RoutineSubjectRow::insert([
          'item_id' => $routineItem->id,
          'column_id' => $value,
          'chapter' => $data['subject_item'][$key]
        ]);
      }

      foreach($data['department_id'] as $key => $value)
      {
        RoutineDepartmentRow::insert([
          'item_id' => $routineItem->id,
          'column_id' => $value,
          'details' => $data['department_item'][$key]
        ]);
      }
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }

    // return $this->addItem($data['routine_id']);
    return redirect()->route('routine.add-item', $data['routine_id']);
  }

  //delete routine item
  public function deleteItem($id)
  {
    $item_row = RoutineItemRow::find($id);
    $subject_rows =  RoutineSubjectRow::where('item_id', $id)->get();
    $department_rows =  RoutineDepartmentRow::where('item_id', $id)->get();

    if($subject_rows)
    {
      foreach($subject_rows as $row)
      {
        $row->delete();
      }
    }

    if($department_rows)
    {
      foreach($department_rows as $row)
      {
        $row->delete();
      }
    }

    $item_row->delete();

    Session::flash('success', 'The item deleted');
    return back();
  }
}
