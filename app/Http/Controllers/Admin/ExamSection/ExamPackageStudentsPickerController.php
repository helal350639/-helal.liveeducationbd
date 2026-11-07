<?php
namespace App\Http\Controllers\Admin\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class ExamPackageStudentsPickerController extends Controller
{
    private function admin(): void
    {
        $user=Auth::user();
        abort_unless($user && method_exists($user,'AuthorizeRoles') && $user->AuthorizeRoles(['SuperAdmin','Admin']),403);
    }
    private function context(): array
    {
        $this->admin();
        $ctx=Session::get('_lebd_exam_package_picker');
        $obj=Session::get('_object');
        abort_unless(is_array($ctx) && is_array($obj) && ($obj['type']??'')==='ExamSectionPackage'
            && (int)($ctx['package_id']??0)===(int)($obj['id']??0),409,'Exam package selection session expired.');
        return $ctx;
    }
    public function start(int $package)
    {
        $this->admin();
        abort_unless(Schema::hasTable('lebd_exam_section_manual_grants'),503,'Exam manual grant table missing.');
        $plan=DB::table('lebd_exam_section_packages')->where('id',$package)->where('status','Active')->firstOrFail();
        // Never overwrite another module's unfinished Student Accounts selection.
        if(Session::has('_object') && !Session::has('_lebd_exam_package_picker')) {
            return redirect()->route('student.index')->withErrors(['picker'=>'আগের Student Selection Done করুন।']);
        }
        $ctx=Session::get('_lebd_exam_package_picker');
        if(is_array($ctx) && (int)($ctx['package_id']??0)!==$package) {
            return redirect()->route('student.index')->withErrors(['picker'=>'আগের Exam Package Selection Done করুন।']);
        }
        $ctx=$ctx??['package_id'=>$package,'selected'=>[]];
        Session::put('_lebd_exam_package_picker',$ctx);
        Session::put('_object',['id'=>$package,'type'=>'ExamSectionPackage','name'=>'Exam Section: '.$plan->name,
            'counter'=>count($ctx['selected']),'xids'=>$ctx['selected']]);
        return redirect()->route('student.index')->with('success','Student Accounts থেকে শিক্ষার্থী Select করে Done চাপুন।');
    }
    public function select(Request $request)
    {
        $ctx=$this->context();
        $data=$request->validate(['id'=>'required|integer|exists:students,id','action'=>'required|in:add,remove']);
        $ids=array_values(array_unique(array_map('intval',$ctx['selected'])));
        $id=(int)$data['id'];
        if($data['action']==='add' && !in_array($id,$ids,true)) $ids[]=$id;
        if($data['action']==='remove') $ids=array_values(array_filter($ids,fn($x)=>$x!==$id));
        abort_if(count($ids)>200,422,'Maximum 200 students at once.');
        $ctx['selected']=$ids;
        Session::put('_lebd_exam_package_picker',$ctx);
        $obj=Session::get('_object');
        $obj['xids']=$ids;$obj['counter']=count($ids);
        Session::put('_object',$obj);
        return response()->json(['success'=>true,'counter'=>count($ids)]);
    }
    public function complete()
    {
        $ctx=$this->context();
        $ids=array_values(array_unique(array_map('intval',$ctx['selected'])));
        if(!$ids) return redirect()->route('student.index')->withErrors(['picker'=>'কমপক্ষে একজন Student Select করুন।']);
        $pkg=(int)$ctx['package_id'];
        $controller=app(ExamPackageController::class);
        $added=0;$skipped=0;
        // Each student's grant is committed atomically; existing paid access is skipped.
        foreach($ids as $id) {
            if($controller->grantStudentId($pkg,$id)) $added++;
            else $skipped++;
        }
        Session::forget('_lebd_exam_package_picker');Session::forget('_object');
        return redirect()->route('admin.exam-section.packages.students',$pkg)
            ->with('success',"{$added} student(s) added, {$skipped} already active. No payment or referral bonus created.");
    }
}
