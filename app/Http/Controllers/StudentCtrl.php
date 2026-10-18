<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Course;
use App\Models\Department;
use App\Models\Batch;
use App\Models\Group;
use App\Models\Role;
use App\Models\RoleUser;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use DB;

class StudentCtrl extends Controller
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
      $active = $inactive = '';
      if($request->status == 'Active')
      {
        $active = $request->status;
      }
      elseif($request->status == 'Inactive')
      {
        $inactive = $request->status;
      }

        $students = Student::leftJoin('users', 'users.id', 'students.created_by')
        ->orderBy('students.id','DESC')
        ->select('students.*', 'users.name as admin_name')
        ->when($active, function($query, $active){
          $query->where('students.last_login_at', '>=', now()->subDays(3));
        })
        ->when($inactive, function($query){
          $query->where('students.last_login_at', '<', now()->subDays(3));
        })
        ->paginate(25);
        return view('layouts.students.index', compact('students'));
    }

    public function view($id, $name)
    {
        $object = $students = [];
        if($name == 'group')
        {
            $object = Group::find($id);
        }
        elseif($name == 'department')
        {
            $object = Department::find($id);
        }
        elseif($name == 'batch')
        {
            $object = Batch::find($id);
        }
        elseif($name == 'course')
        {
            $object = Course::find($id);
        }

        $students = $object->students()->orderBy('id', 'desc')->paginate(25);

        return view('layouts.students.view', compact('object', 'students', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::all();
        return view('layouts.students.create', compact('roles'));
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
            'name'      => 'required|max:255',
            'email'     => 'required|unique:students',
            'contact'   => 'required|unique:students',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png,gif|max:500'
        ]);

       $user = new Student;
       $user->name       = $request->name;
       $user->contact    = $request->contact;
       $user->email      = $request->email;
       $user->password   = bcrypt($request->password);
       $user->status     = $request->status ?? 'Inactive';
       $user->created_by = Auth::id();
       
       if($request->hasFile('image'))
       {
        $user->image = $source->uploadImage($request->image, 'students/');
       }

        $user->save();

        Session::flash('success', 'User Successfully Save');
        return redirect()->route('student.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $student = Student::find($id);
        return view('layouts.students.read', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
{
    if(
        Auth::user()->AuthorizeRoles('SuperAdmin') ||
        Auth::user()->AuthorizeRoles('Admin')
    )
    {
        $user = Student::find($id);

        return view('layouts.students.edit', compact('user'));
    }

    Session::flash('error', 'Permission restricted!');

    return back();
}
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // dd($request->input('contact'));
        $this->validate($request, [
            'name'      => 'required|max:255',
            'password'  => 'nullable|min:8|max:32',
            'status'    => 'max:30',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png,gif|max:500'
        ]);

        $student = Student::find($id);
        if($student->contact != $request->input('contact'))
        {
            $this->validate($request, [
                'contact' => 'unique:students'
            ]);
        }
        if($student->email != $request->input('email'))
        {
            $this->validate($request, [
                'email' => 'required|unique:students'
            ]);
        }

        $student->name       = $request->input('name');
        $student->contact    = $request->input('contact');
        $student->email      = $request->input('email');
        if($request->password)
        {
            $student->password   = bcrypt($request->input('password'));
        }
        $student->status     = $request->input('status') ?? 'Inactive';
        $student->updated_by = Auth::id();

        if($request->hasFile('image'))
        {
            $x_img_path = public_path($student->image);

            $user->image = $source->uploadImage($request->image, 'students/');

            if (File::exists($x_img_path)) {
                File::delete($x_img_path);
            }
        }
        $student->save();

        Session::flash('success', 'The Student Successfully Updated');
        return redirect()->route('student.show', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        $student = Student::find($id);
        if(File::exists($student->image))
        {
            File::delete($student->image);
        }

        $student->delete();
        Session::flash('success', 'Student Successfully Removed');
        return redirect()->route('student.index');
      }
      return back()->with('error', 'Permission Denied');
    }

    // ----------------------- profile route ------------

    public function profile()
    {
        $user = Auth::user();
        return view('layouts.profile.read', compact('user'));
    }

    public function profileEdit()
    {
        $user = Auth::user();
        return view('layouts.profile.edit', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'contact' => 'nullable'
        ]);

        $students = Student::find(Auth::id());
        $students->name       = $request->input('name');
        $students->contact    = $request->input('contact');
        $students->updated_by = Auth::id();

        if($request->hasFile('image'))
        {
            $x_img_path = public_path($students->image);

            if (File::exists($x_img_path)) {
                File::delete($x_img_path);
            }

            $image = $request->file('image');
            $img = $request->name.'_'.time() .'.'. $image->getClientOriginalExtension();
            $location =  public_path('/uploads/users/'.$img);
            Image::make($image)->save($location);
            $students->image = '/uploads/users/'.$img;
        }
        $students->save();

        Session::flash('success', 'Profile information updated');
        return redirect()->route('profile');
    }

    public function changePassword()
    {
        $profile = Student::find(Auth::id());
        return view('layouts.change_password', compact('profile'));
    }

    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required|min:8|max:32',
            'password'         => 'required|min:8|max:32|confirmed'
            ]);

        $user = Student::find(Auth::id());

        function passverify($curr, $dbpass){
            return password_verify($curr, $dbpass);
        }

        if(password_verify($request->current_password, $user->password) === false)
        {
            Session::flash('error', 'Invalid password provided.');
            return redirect('/change_password');
        }
        else
        {
            $user = Student::find(Auth::id());
            $user->password = bcrypt($request->password);
            $user->save();

            Session::flash('success', 'Password successfully updated.');
            return redirect('/change_password');
        }
    }
    
    public function addStudent($id, $name)
    {
        if($id && $name)
        {
            $object = [];
            if($name == 'Group')
            {
                $object = Group::find($id);
            }
            elseif($name == 'Batch')
            {
                $object = Batch::find($id);
            }
            elseif($name == 'Course')
            {
                $object = Course::find($id);
            }
            elseif($name == 'Department')
            {
                $object = Department::find($id);
            }

            if(!is_null($object))
            {
                Session::put('_object', [
                    'id' => $id,
                    'type' => $name,
                    'name' => $object->name,
                    'counter' => $object->students()->count(),
                    'xids' => $object->students()->pluck('id')->toArray()
                ]);

                Session::flash('success', 'Add students to the '.$name);
                return redirect()->route('student.index');
            }
        }

        Session::flash('error', 'Invalid Request');
        return back();
    }

    public function remove($id, $name, $objid)
    {
        $student = Student::find($id);
        if($name == 'group')
        {
            $student->groups()->detach($objid);
        }
        elseif($name == 'department')
        {
            $student->departments()->detach($objid);
        }
        elseif($name == 'batch')
        {
            $student->batches()->detach($objid);
        }
        elseif($name == 'course')
        {
            $student->courses()->detach($objid);
        }

        Session::flash('success', 'The student successfully removed from '.$name);
        return back();
    }

    public function addStudentObject(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
            'action' => 'required|string',
        ]);

        $data = $request->all();
        if(!is_null(Session::get('_object')))
        {
            $msg = '';
            $student = Student::find($data['id']);
            $object = Session::get('_object');
            if($object['type'] == 'Group')
            {
                if($data['action'] == 'add')
                {
                    $student->groups()->attach($object['id']);
                    $msg = 'A student added';
                }
                elseif($data['action'] == 'remove')
                {
                    $student->groups()->detach($object['id']);
                    $msg = 'A student removed';
                }
            }
            elseif($object['type'] == 'Batch')
            {
                if($data['action'] == 'add')
                {
                    $student->batches()->attach($object['id']);
                    $msg = 'A student added';
                }
                elseif($data['action'] == 'remove')
                {
                    $student->batches()->detach($object['id']);
                    $msg = 'A student removed';
                }
            }
            elseif($object['type'] == 'Department')
            {
                if($data['action'] == 'add')
                {
                    $student->departments()->attach($object['id']);
                    $msg = 'A student added';
                }
                elseif($data['action'] == 'remove')
                {
                    $student->departments()->detach($object['id']);
                    $msg = 'A student removed';
                }
            }

            $this->addStudent($object['id'], $object['type']);
            // dd($object);

            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        }
        // dd($data);
    }

    public function addStudentComplete()
    {
        if(!is_null(Session::get('_object')))
        {
            $object = Session::get('_object');
            Session::forget('_object');
            if($object['type'] == 'Batch')
            {
                return redirect()->route('batch.index');
            }
            elseif($object['type'] == 'Department')
            {
                return redirect()->route('department.index');
            }
            elseif($object['type'] == 'Group')
            {
                return redirect()->route('group.index');
            }
        }
        return back();
    }

    /** -------- send email for verification ----------- */
    public function sendVerifyEmail($id)
    {
        $data = [];
        $source = new SourceCtrl;
        $student = Student::find($id);
        if($student->status == 'Inactive')
        {
            $remember_token = $data['remember_token'] = ucwords(md5(123));
            $data['status'] = NULL;

            try{
                Student::where('id', $id)->update($data);
            }
            catch(\Exception $e)
            {
                return $e;
            }

            $emailData = [
                'email_to' => $student->email,
                'subject' => 'Email Verification | Sign Up at Live Education BD',
                'comments' => 'Hello '.$student->name.',<br> Your email verification link below. Click on the link to verify your account. <a target="_blank" href="'.$source->host().'/account_verify/'.$remember_token.'">'.$source->host().'/account_verify/'.$remember_token.'</a> Otherwise, browse the URL. '
            ];
    
            $source->sendMail($emailData);
        }

        return back();
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'value' => 'string'
        ]);

        $students = Student::orderBy('id', 'DESC')
        ->where('name', 'like', '%'. $request->value.'%')
        ->orWhere('email', 'like', '%'. $request->value.'%')
        ->orWhere('contact', 'like', '%'. $request->value.'%')
        ->paginate(25);

        // dd($students);

        return view('layouts.students.index', compact('students'));
    }

    // login to the student panel
    public function loginById($id)
    {
        $user = Student::find($id);

        Auth::guard('student')->loginUsingId($user->id);
    
        return redirect()->intended(route('students.dashboard'));
    }
}
