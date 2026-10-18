<?php
namespace App\Http\Controllers;
use App\Models\RecruitmentLogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
class RecruitmentLogoCtrl extends Controller {
 public function __construct(){ $this->middleware('auth'); }
 private function authorized(){abort_unless(auth()->user() && auth()->user()->AuthorizeRoles('SuperAdmin'),403);}
 public function index(){ $this->authorized();$logos=RecruitmentLogo::orderBy('sort_order')->orderBy('short_name')->paginate(50);return view('layouts.courses.recruitment-logos',compact('logos')); }
 public function store(Request $request){
  $this->authorized();$data=$request->validate(['name'=>'required|string|max:255','short_name'=>'required|string|max:50','website'=>'nullable|url|max:500','logo'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048','status'=>'required|in:Active,Inactive','sort_order'=>'nullable|integer|min:0|max:999999']);
  $record=new RecruitmentLogo();$record->name=$data['name'];$record->short_name=$data['short_name'];$record->website=$data['website']??null;$record->status=$data['status'];$record->sort_order=$data['sort_order']??0;
  if($request->hasFile('logo')){$record->logo_path=$this->upload($request->file('logo'));}$record->save();return back()->with('success','Logo saved');
 }
 public function update(Request $request,$id){
  $this->authorized();$record=RecruitmentLogo::findOrFail($id);
  $data=$request->validate(['name'=>'required|string|max:255','short_name'=>'required|string|max:50','website'=>'nullable|url|max:500','logo'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048','status'=>'required|in:Active,Inactive','sort_order'=>'nullable|integer|min:0|max:999999']);
  $record->name=$data['name'];$record->short_name=$data['short_name'];$record->website=$data['website']??null;$record->status=$data['status'];$record->sort_order=$data['sort_order']??0;
  if($request->hasFile('logo')){$record->logo_path=$this->upload($request->file('logo'));}$record->save();return back()->with('success','Logo updated');
 }
 private function upload($file){$dir=public_path('web/img/recruitment-logos');if(!is_dir($dir))File::makeDirectory($dir,0755,true);$name=bin2hex(random_bytes(12)).'.'.$file->extension();$file->move($dir,$name);return 'web/img/recruitment-logos/'.$name;}
}
