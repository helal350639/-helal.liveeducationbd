<?php
namespace App\Http\Controllers\Admin\StudentAdvice;
use App\Http\Controllers\Controller;
use App\Models\StudentAdvice\StudentAdvice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
class StudentAdviceController extends Controller {
 private function guardAdmin():void { abort_unless(auth()->check() && auth()->user()->authorizeRoles(['SuperAdmin','Admin']),403); }
 public function index(){ $this->guardAdmin(); $items=StudentAdvice::orderBy('sort_order')->orderByDesc('id')->paginate(20); return view('admin.student-advice.index',compact('items')); }
 public function store(Request $r){$this->guardAdmin();$data=$this->validated($r);$data['photo_path']=$this->upload($r);StudentAdvice::create($data);return back()->with('success','শিক্ষার্থীর তথ্য যোগ হয়েছে।');}
 public function update(Request $r,StudentAdvice $advice){$this->guardAdmin();$data=$this->validated($r);if($r->hasFile('photo')){$path=$this->upload($r);$this->deletePhoto($advice->photo_path);$data['photo_path']=$path;}$advice->update($data);return back()->with('success','তথ্য আপডেট হয়েছে।');}
 public function destroy(StudentAdvice $advice){$this->guardAdmin();$this->deletePhoto($advice->photo_path);$advice->delete();return back()->with('success','তথ্য মুছে ফেলা হয়েছে।');}
 private function validated(Request $r):array{return $r->validate(['name'=>'required|string|max:150','bb_id'=>'nullable|string|max:60','university'=>'nullable|string|max:255','position'=>'required|string|max:255','organization'=>'nullable|string|max:255','advice'=>'required|string|max:3000','sort_order'=>'nullable|integer|min:0|max:999999','status'=>'required|in:Published,Draft','photo'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:1024']);}
 private function upload(Request $r):?string{if(!$r->hasFile('photo'))return null;$f=$r->file('photo');$dir=public_path('web/uploads/student-advice');if(!is_dir($dir))mkdir($dir,0755,true);$name=(string)\Illuminate\Support\Str::uuid().'.'.$f->extension();$f->move($dir,$name);return 'web/uploads/student-advice/'.$name;}
 private function deletePhoto(?string $path):void{if(!$path || !str_starts_with($path,'web/uploads/student-advice/'))return;$full=public_path($path);if(is_file($full))@unlink($full);}
}
