<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\Batch;
use App\Models\Role;
use Auth;
use Image;
use Toastr;
use File;
use Session;

class BatchCtrl extends Controller
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
        $batches = Batch::orderBy('id','DESC')->get();
        return view('layouts.batches.index', compact('batches'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('layouts.batches.create');
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
            'name'      => 'required|unique:batches|max:255',
            'details'   => 'nullable|max:1000',
        ]);
        
        $data = $request->all();
        if(isset($data['_token']))
        {
            unset($data['_token']);
        }

        if(isset($data['status']))
        {
            $data['status'] = 'Active';
        }
        else
        {
            $data['status'] = 'Deactive';
        }

        try{
            Batch::insert($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $batch = Batch::orderBy('id', 'DESC')->first();
        
        Session::flash('success', 'New batch successfully created!');

        return redirect()->route('batch.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $batch = Batch::find($id);
        return view('layouts.batches.read', compact('batch'));
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
        $departments = Department::where('status', 'Active')->get();
        $teachers = Teacher::where('status', 'Active')->get();
        $batch = Batch::find($id);
        return view('layouts.batches.edit', compact('courses', 'departments', 'teachers', 'batch'));
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
            'course_id'    => 'required|numeric',
            'department_id'=> 'required|array',
            'teacher_id'   => 'required|array',
            'name'         => 'required|max:255',
            'price'        => 'nullable|numeric',
            'discount'     => 'nullable|numeric',
            'net_price'    => 'nullable|numeric',
            'teacher_id'   => 'nullable|array',
            'video'        => 'nullable|url',
            'status'       => 'nullable|string',
            'details'      => 'nullable|string',
        ]);
        
        $data = $request->all();
        $batch = Batch::find($id);
        $xbanner = public_path($batch->banner);
        $departments = $teachers = [];

        if(isset($data['_token']))
        {
          unset($data['_token']);
        }

        if(isset($data['_method']))
        {
          unset($data['_method']);
        }

        $data['status'] = isset($data['status']) ? 'Active' : 'Deactive';
        $data['web'] = isset($data['web']) ? 'Yes' : 'No';

        if(isset($data['department_id']))
        {
          $departments = $data['department_id'];
          unset($data['department_id']);
        }

        if(isset($data['teacher_id']))
        {
          $teachers = $data['teacher_id'];
          unset($data['teacher_id']);
        }

        if(isset($data['teacher_id']))
        {
          unset($data['teacher_id']);
        }

        if(isset($data['banner']))
        {
          $data['banner'] = $source->uploadImage($request->banner, 'batches/');
        }

        if(isset($data['video']))
        {
          $data['video'] = substr($request->video, 32, 20);
        }
        else
        {
          unset($data['video']);
        }

        try {
          Batch::where('id', $id)->update($data);

          if(isset($data['banner']) && File::exists($xbanner))
          {
            File::delete($xbanner);
          }

          $batch->departments()->sync($departments);
          $batch->teachers()->sync($teachers);
        }
        catch(\E $e)
        {
          return $e;
        }
        
        Session::flash('success', 'The batch successfully Updated!');

        return redirect()->route('batch.index');
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
          $course = Batch::find($id);

          // delete all relationship
          // $course->courses()->detach();
          // $course->departments()->detach();
          $course->papers()->detach();

          //delete course table
          $course->delete();

          Session::flash('Success','The batch successfully deleted');
          return redirect()->route('batch.index');
        }

        Session::flash('error', 'Permission restricted!');
        return back();
    }

    /** -------------- custom methods ------------- */
    // Ajax Call
    public function getBatches($batch_id)
    {
        $arr = explode(',', $batch_id);
        $items = Batch::whereHas('course', function($q) use($arr) {
            $q->whereIn('course_id', $arr);
        })
        ->where('status', 'Active')
        ->select('id', 'name')
        ->get();
        return response()->json(['data' => $items]);
    }
    
}
