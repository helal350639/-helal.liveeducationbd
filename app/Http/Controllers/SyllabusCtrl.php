<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;
use App\Models\Batch;
use App\Models\Group;
use App\Models\Syllabus;
use App\Models\Role;
use Auth;
use Image;
use Toastr;
use File;
use Session;

class SyllabusCtrl extends Controller
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
        $syllabus = Syllabus::orderBy('id','DESC')->paginate(25);
        return view('layouts.syllabuses.index', compact('syllabus'));
    }
    
    public function view($id)
    {
        if(!is_null(Session::get('_paper')))
        {
            Session::forget('_paper');
        }
        $syllabus = Syllabus::find($id);
        $questions = $syllabus->questions()->paginate(10);
        // dd($questions);

        return view('layouts.syllabuses.view', compact('syllabus', 'questions'));
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
        return view('layouts.syllabuses.create', compact('courses', 'batches', 'departments'));
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
            'header'    => 'required',
            'details'   => 'nullable|max:1000',
            'pdf'       => 'nullable|mimes:pdf|max:10000',
            'routine'   => 'nullable|mimes:pdf|max:10000',
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

        $data['created_by'] = Auth::id();

        try{
            // upload pdf file
            if($request->hasFile('pdf'))
            {
                $data['pdf'] = $source->uploadImage($data['pdf'], 'syllabus/temp/');
            }
            // upload pdf routine
            if($request->hasFile('routine'))
            {
                $data['routine'] = $source->uploadImage($data['routine'], 'routine/');
            }
            Syllabus::insert($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        // $batch = Group::orderBy('id', 'DESC')->first();
        
        Session::flash('success', 'New syllabus successfully created!');

        return redirect()->route('syllabus.index');
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
        return view('layouts.syllabuses.read', compact('group'));
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
        $syllabus = Syllabus::find($id);
        return view('layouts.syllabuses.edit', compact('syllabus', 'courses', 'departments', 'batches'));
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
            'header'    => 'required|max:255',
            'details'   => 'nullable|max:1000',
            'sample_pdf'=> 'nullable|mimes:pdf|max:5000',
            'pdf'       => 'nullable|mimes:pdf|max:10000',
            'routine'   => 'nullable|mimes:pdf|max:10000',
        ]);
        
        $data = $request->all();
        $syllabus = Syllabus::find($id);
        
        //get existing file
        $xsamplepdf = public_path($syllabus->sample_pdf);
        $xpdf = public_path($syllabus->pdf);
        $xroutine = public_path($syllabus->routine);

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }
        if(isset($data['_method']))
        {
            unset($data['_method']);
        }

        try{
            // upload pdf file
            if($request->hasFile('sample_pdf'))
            {
                $data['sample_pdf'] = $source->uploadImage($data['sample_pdf'], 'syllabus/sample/');
            }
            // upload pdf file
            if($request->hasFile('pdf'))
            {
                $data['pdf'] = $source->uploadImage($data['pdf'], 'syllabus/temp/');
            }
            // upload pdf routine
            if($request->hasFile('routine'))
            {
                $data['routine'] = $source->uploadImage($data['routine'], 'routine/');
            }

            // updated data to the database
            Syllabus::where('id', $id)->update($data);

            if($request->hasFile('sample_pdf') && File::exists($xsamplepdf))
            {
                File::delete($xsamplepdf);
            }

            if($request->hasFile('pdf') && File::exists($xpdf))
            {
                File::delete($xpdf);
            }
            
            if($request->hasFile('routine') && File::exists($xroutine))
            {
                File::delete($xroutine);
            }
        }
        catch(\E $e)
        {
            return $e;
        }
        
        Session::flash('success', 'The syllabus successfully Updated!');

        return redirect()->route('syllabus.index');
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
            $syllabus = Syllabus::find($id);

            // delete all relationship
            $syllabus->questions()->detach();

            //delete group table
            $syllabus->delete();

            Session::flash('Success','The syllabus successfully deleted');
            return redirect()->route('syllabus.index');
        }

        Session::flash('error', 'Permission restricted!');
        return back();
    }

    //custom routes
    public function addQuestion($type, $id)
    {
        $paper = Syllabus::find($id);
        $paper['type'] = $type;
        if($paper)
        {
            Session::put('_paper', $paper);
            return redirect()->route('question.view');
        }
        return back();
    }

    public function addToPaper(Request $request)
    {
        $data = $request->all();
        $ids = explode(',', $data['question']);
        if(!is_null(Session::get('_paper')))
        {
            $paper = Session::get('_paper');
            if($data['action'] == 'add')
            {
                $qcount = $paper->questions()->attach($ids);
            }
            else
            {
                $qcount = $paper->questions()->detach($ids);
            }

            $paper = Syllabus::find($paper->id);
            Session::put('_paper', $paper);

            return response()->json([
                'success' => true,
                'message' => 'The question added to the syllabus',
                'qcount' => $qcount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'We are getting error'
        ]);
    }

    public function pdf($id)
    {
        $syllabus = Syllabus::find($id);
        return view('layouts.syllabuses.print', compact('syllabus'));
    }
    
}
