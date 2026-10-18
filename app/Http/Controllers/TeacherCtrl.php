<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Course;
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

class TeacherCtrl extends Controller
{
    private $source;

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
        $teachers = Teacher::orderBy('id','DESC')->paginate(25);
        return view('layouts.teachers.index', compact('teachers'));
    }

    public function view($id, $name)
    {
        $object = $teachers = [];
        if($name == 'group')
        {
            $object = Group::find($id);
        }
        elseif($name == 'batch')
        {
            $object = Batch::find($id);
        }
        elseif($name == 'course')
        {
            $object = Course::find($id);
        }

        $teachers = $object->teachers()->paginate(25);

        return view('layouts.teachers.view', compact('object', 'teachers', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::all();
        return view('layouts.teachers.create', compact('roles'));
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
            'designation' => 'nullable|max:255',
            'email'     => 'required|unique:teachers',
            'contact'   => 'required|unique:teachers',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png,gif|max:500',
            'bio' => 'nullable|string|max:2000',
            'qualification' => 'nullable|string|max:500',
            'institution' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0|max:80',
            'specialization' => 'nullable|string|max:500',
            'total_classes' => 'nullable|integer|min:0',
            'total_students' => 'nullable|integer|min:0',
            'achievements' => 'nullable|string|max:3000',
            'profile_details' => 'nullable|string|max:8000',
]);

       $user = new Teacher;
       $user->name       = $request->name;
       $user->designation = $request->designation;
       $user->contact    = $request->contact;
       $user->email      = $request->email;
       $user->password   = bcrypt($request->password);
       $user->status     = $request->status ?? 'Inactive';
       $user->created_by = Auth::id();

        // LEBD-3: public teacher profile only (not authentication/payment fields).
        foreach (['bio','qualification','institution','experience_years','specialization','total_classes','total_students','achievements','profile_details'] as $profileField) {
            $user->{$profileField} = $request->input($profileField) !== '' ? $request->input($profileField) : null;
        }

       
       if($request->hasFile('image'))
       {
        $user->image = $source->uploadImage($request->image, 'teachers/');
       }

        $user->save();

        Session::flash('success', 'Teacher account Successfully Save');
        return redirect()->route('teacher.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $teacher = Teacher::find($id);
        return view('layouts.teachers.read', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $user = Teacher::find($id);
      return view('layouts.teachers.edit', compact('user'));
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
        $source = new SourceCtrl;
        $this->validate($request, [
            'name'      => 'required|max:255',
            'designation' => 'nullable|max:255',
            'contact'   => 'required',
            'email'     => 'required',
            'status'    => 'nullable|max:30',
            'web'       => 'nullable|max:30',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png,gif|max:1000',
            'bio' => 'nullable|string|max:2000',
            'qualification' => 'nullable|string|max:500',
            'institution' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0|max:80',
            'specialization' => 'nullable|string|max:500',
            'total_classes' => 'nullable|integer|min:0',
            'total_students' => 'nullable|integer|min:0',
            'achievements' => 'nullable|string|max:3000',
            'profile_details' => 'nullable|string|max:8000',
]);
        
        $data = $request->all();

        $obj = Teacher::find($id);
        $obj->name       = $request->input('name');
        $obj->designation = $request->input('designation');
        $obj->contact    = $request->input('contact');
        $obj->email      = $request->input('email');
        $obj->status     = isset($data['status']) ? 'Active' : 'Inactive';
        $obj->web        = isset($data['web']) ? 'Yes' : '';
        $obj->updated_by = Auth::id();

        // LEBD-3 v47: persist edited public profile attributes.
        // These fields never touch teacher login, payments, or enrollment.
        foreach (['bio', 'qualification', 'institution', 'experience_years',
                  'specialization', 'total_classes', 'total_students',
                  'achievements', 'profile_details'] as $profileField) {
            $fieldValue = $request->input($profileField);
            $obj->{$profileField} = ($fieldValue === '' || $fieldValue === null)
                ? null
                : $fieldValue;
        }

        if($request->hasFile('image'))
        {
            $x_img_path = public_path($obj->image);

            $obj->image = $source->uploadImage($request->image, 'teachers/');

            if (File::exists($x_img_path)) {
                File::delete($x_img_path);
            }
        }
        $obj->save();

        Session::flash('success', 'The Teacher Successfully Updated');
        return redirect()->route('teacher.show', $id);
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
        $teacher = Teacher::find($id);
        if(File::exists($teacher->image))
        {
            File::delete($teacher->image);
        }

        $teacher->delete();
        Session::flash('success', 'Teacher account Successfully Removed');
        return redirect()->route('teacher.index');
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

        $teachers = Teacher::find(Auth::id());
        $teachers->name       = $request->input('name');
        $teachers->contact    = $request->input('contact');
        $teachers->updated_by = Auth::id();

        if($request->hasFile('image'))
        {
            $x_img_path = public_path($teachers->image);

            if (File::exists($x_img_path)) {
                File::delete($x_img_path);
            }

            $image = $request->file('image');
            $img = $request->name.'_'.time() .'.'. $image->getClientOriginalExtension();
            $location =  public_path('/uploads/users/'.$img);
            Image::make($image)->save($location);
            $teachers->image = '/uploads/users/'.$img;
        }
        $teachers->save();

        Session::flash('success', 'Profile information updated');
        return redirect()->route('profile');
    }

    public function changePassword()
    {
        $profile = Teacher::find(Auth::id());
        return view('layouts.change_password', compact('profile'));
    }

    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required|min:8|max:32',
            'password'         => 'required|min:8|max:32|confirmed'
            ]);

        $user = Teacher::find(Auth::id());

        function passverify($curr, $dbpass){
            return password_verify($curr, $dbpass);
        }

        if(password_verify($request->current_password, $user->password) === false){
            Session::flash('error', 'Invalid password provided.');
            return redirect('/change_password');
        }else{
            $user = Teacher::find(Auth::id());
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

            if(!is_null($object))
            {
                Session::put('_object', [
                    'id' => $id,
                    'type' => $name,
                    'name' => $object->name,
                    'counter' => $object->teachers()->count(),
                    'xids' => $object->teachers()->pluck('id')->toArray()
                ]);

                Session::flash('success', 'Add teachers to the '.$name);
                return redirect()->route('student.index');
            }
        }

        Session::flash('error', 'Invalid Request');
        return back();
    }

    public function remove($id, $name, $objid)
    {
        $student = Teacher::find($id);
        if($name == 'group')
        {
            $student->groups()->detach($objid);
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
            $student = Teacher::find($data['id']);
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

            $this->addStudent($object['id'], $object['type']);
            // dd($object);

            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        }
        dd($data);
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
            elseif($object['type'] == 'Group')
            {
                return redirect()->route('group.index');
            }
        }
        return back();
    }
}
