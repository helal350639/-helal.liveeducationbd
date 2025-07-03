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
        'batch_id'    => 'required|array',
        'student_id'  => 'nullable|array',
        'details'     => 'nullable|max:10000',
      ]);

      // process request data      
      $data = $request->all();

      // remove csrf token
      if(isset($data['_token']))
      {
        unset($data['_token']);
      }

      // get batch ids
      $batch_ids = $data['batch_id'];

      $emailable_ids = '';
      $student_emails = [];
      $student_ids = [];

      function arrayMapping($student_emails)
      {
        $emails = array_map(function($email)
        {
          return $email;
        }, 
        $student_emails);

        return implode(', ', $emails);
      }

      if(isset($data['student_id']))
      {
        $students = Student::whereIn('id', $data['student_id']);
        $student_emails = $students->pluck('email')->toArray();
        $student_ids = $students->pluck('id')->toArray();
      }
      else
      {
        $students = Student::leftJoin('batch_student', 'batch_student.student_id', 'students.id')
        ->leftJoin('batches', 'batches.id', 'batch_student.batch_id')
        ->whereIn('batch_student.batch_id', $batch_ids);

        $student_emails = $students->pluck('students.email')->toArray();
        $student_ids = $students->pluck('students.id')->toArray();
      }

      $data['batch_id'] = json_encode($data['batch_id']);

      $data['student_id'] = json_encode($student_ids);
      $data['sms_send_by'] = Auth::id();

      try {
        if($data['status'] == 'Sent')
        {
          // create email data
          $emailData = [
            'email_to' => 'liveeducationbd24@gmail.com',
            'email_bcc' => $student_emails,
            'subject'  => $request->title,
            'comments' => $request->details
          ];
  
          // send email to the students
          $source->sendMail($emailData);
        }

        // store entry to the database
        Email::insert($data);
      }
      catch(\Exception $e)
      {
        return $e->getMessage();
      }
      
      Session::flash('success', 'Email Send successfull');

      return redirect()->route('email.index');
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
        return view('layouts.email.read', compact('email'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $batches = Batch::where('status', 'Active')
      ->orderBy('id', 'DESC')
      ->select('id', 'name')
      ->get();

      $email = Email::find($id);
      $student_str = json_decode($email->student_id);
      $student_ids = array_map('intval', $student_str);

      $students = Student::whereIn('id', $student_ids)->get();
      return view('layouts.email.edit', compact('email', 'batches', 'students'));
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
        'batch_id'    => 'required|array',
        'student_id'  => 'nullable|array',
        'details'     => 'nullable|max:10000',
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

        try {
            Email::where('id', $id)->update($data);
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
        
        Session::flash('success', 'The email successfully Updated!');

        return redirect()->route('email.index');
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

            Session::flash('Success','The email successfully deleted');
            return redirect()->route('email.index');
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
