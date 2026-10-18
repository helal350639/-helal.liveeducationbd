<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\RecruitmentLogo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
class CourseCtrl extends Controller {
 public function __construct(){ $this->middleware('auth'); }
 private function logos(){return RecruitmentLogo::where('status','Active')->orderBy('sort_order')->orderBy('short_name')->get();}
 public function index(Request $request){
  $search=$request->input('search');
  $courses=Course::query()->with('recruitmentLogo')->when($search,function($q,$v){$q->where('name','like',"%{$v}%");})->orderByDesc('id')->paginate(25)->appends(['search'=>$search]);
  return view('layouts.courses.index',compact('courses'));
 }
 public function create(){ $logos=$this->logos();return view('layouts.courses.create',compact('logos')); }
 public function store(Request $request){
  $validated=$request->validate([
   'name'=>'required|unique:courses,name|max:255','details'=>'nullable|max:1000',
   'banner'=>'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
   'recruitment_logo_id'=>'nullable|exists:lebd_recruitment_logos,id',
  ]);
  $data=$request->except(['_token','banner','recruitment_logo_id']);
  $data['status']='Active';
  $data['recruitment_logo_id']=$request->filled('recruitment_logo_id')?$request->input('recruitment_logo_id'):null;
  if($request->hasFile('banner')){ $source=new SourceCtrl; $data['banner']=$source->uploadImage($request->file('banner'),'courses/'); }
  DB::table('courses')->insert($data);
  Session::flash('success','New course successfully created!');return redirect()->route('course.index');
 }
 public function show($id){$course=Course::findOrFail($id);return view('layouts.courses.read',compact('course'));}
 public function edit($id){$course=Course::findOrFail($id);$logos=$this->logos();return view('layouts.courses.edit',compact('course','logos'));}
 public function update(Request $request,$id){
  $course=Course::findOrFail($id);
  $request->validate([
   'name'=>'required|max:255','details'=>'nullable|max:1000',
   'banner'=>'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
   'recruitment_logo_id'=>'nullable|exists:lebd_recruitment_logos,id',
  ]);
  $data=$request->except(['_token','_method','banner','recruitment_logo_id','status','web','show_in_exam_section']);
  $data['status']=$request->has('status')?'Active':'Deactive';
  $data['web']=$request->has('web')?'Yes':'No';
  $data['show_in_exam_section']=$request->boolean('show_in_exam_section')?1:0;
  $data['recruitment_logo_id']=$request->filled('recruitment_logo_id')?$request->input('recruitment_logo_id'):null;
  $oldBanner=$course->banner;
  if($request->hasFile('banner')){ $source=new SourceCtrl; $data['banner']=$source->uploadImage($request->file('banner'),'courses/'); }
  DB::table('courses')->where('id',$id)->update($data);
  if(isset($data['banner']) && $oldBanner && !preg_match('/^https?:\/\//i',$oldBanner)){
    $oldPath=public_path(ltrim($oldBanner,'/'));
    if(is_file($oldPath)) File::delete($oldPath);
  }
  Session::flash('success','The course successfully Updated!');return redirect()->route('course.index');
 }
 public function destroy($id){
  if(Auth::user()->AuthorizeRoles('SuperAdmin')){
    $course=Course::findOrFail($id);
    $course->departments()->detach();$course->semesters()->detach();$course->subjects()->detach();
    $course->questions()->detach();$course->filters()->detach();$course->delete();
    Session::flash('Success','The course successfully deleted');return redirect()->route('course.index');
  }
  return back()->with('error','Permission Denied');
 }
}
