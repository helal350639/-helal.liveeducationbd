<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SourceCtrl;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Email;
use App\Models\Role;
use App\Models\Student;
use Auth;
use Image;
use Toastr;
use File;
use Session;

class EmailCtrl extends Controller
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
        $emails = Email::orderBy('id','DESC')->paginate(25);
        return view('layouts.email.index', compact('emails'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $batches = Batch::where('status', 'Active')
        ->orderBy('id', 'DESC')
        ->select('id', 'name')
        ->get();

        return view('layouts.email.create', compact('batches'));
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
            'batch_id'  => 'required|array',
            'student_id'  => 'nullable|array',
            'details'   => 'nullable|max:1000',
        ]);
        
        $data = $request->all();
        // dd($data);
        if(isset($data['_token']))
        {
            unset($data['_token']);
        }

        // if(isset($data['status']))
        // {
        //     $data['status'] = 'Active';
        // }
        // else
        // {
        //     $data['status'] = 'Deactive';
        // }

        $batch_ids = $data['batch_id'];

        $students = Student::leftJoin('batch_student', 'batch_student.student_id', 'students.id')
        ->leftJoin('batches', 'batches.id', 'batch_student.batch_id')
        ->whereIn('batch_student.batch_id', $batch_ids);

        $student_contacts = $students->pluck('students.contact')->toArray();
        $student_ids = $students->pluck('students.id')->toArray();
        // ->get();

        $emailable_contact = '';

        function arrayMapping($student_contacts)
        {
            $contact = array_map(function($num)
            {
                return '+88'.$num;
            }, $student_contacts);

            return implode(', ', $contact);
        }

        if(isset($data['student_id']))
        {
            $students = Student::whereIn('id', $data['student_id']);
            $student_contacts = $students->pluck('contact')->toArray();
            $student_ids = $students->pluck('id')->toArray();
            $emailable_contact = arrayMapping($student_contacts);
        }
        else
        {
            $emailable_contact = arrayMapping($student_contacts);
        }

        // dd($emailable_contact);

        $data['batch_id'] = json_encode($data['batch_id']);

        $data['student_id'] = json_encode($student_ids);
        $data['sms_send_by'] = Auth::id();

        /** ------------- send sms by gateway --------------- */ 
        $source->sms_send($emailable_contact, $data['details']);

        try{
            Email::insert($data);
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }

        // $batch = Email::orderBy('id', 'DESC')->first();
        
        Session::flash('success', 'SMS Send successfull');

        return redirect()->route('sms.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $email = Email::find($id);
        return view('layouts.email.read', compact('sms'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $email = Email::find($id);
        return view('layouts.email.edit', compact('sms'));
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
        $this->validate($request, [
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

        try{
            Email::where('id', $id)->update($data);
        }
        catch(\E $e)
        {
            return $e;
        }
        
        Session::flash('success', 'The sms successfully Updated!');

        return redirect()->route('sms.index');
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
            $email = Email::find($id);

            // delete all relationship
            // $email->papers()->detach();

            //delete group table
            $email->delete();

            Session::flash('Success','The sms successfully deleted');
            return redirect()->route('sms.index');
        }

        Session::flash('error', 'Permission restricted!');
        return back();
    }

    /** get students according to the batch */
    public function getBatchStudents($batch_ids)
    {
        $batch_ids = explode(', ', $batch_ids);
        $students = Student::leftJoin('batch_student', 'batch_student.student_id', 'students.id')
        ->leftJoin('batches', 'batches.id', 'batch_student.batch_id')
        ->whereIn('batch_student.batch_id', $batch_ids)
        ->select(
            'students.id as student_id',
            'students.name as student_name',
            'students.contact',
            'batches.name as batch_name'
            )
        ->get();

        return response()->json([
            'students' => $students
        ], 200);

        // dd($students);        
    }
    
}
