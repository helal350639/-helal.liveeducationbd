<?php
namespace App\Http\Controllers\Admin\PremiumClass;
use App\Http\Controllers\Controller;
use App\Models\PremiumClass\LebdVideoComplaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
class VideoComplaintController extends Controller {
 public function index(Request $request){
  abort_unless(Schema::hasTable('lebd_premium_video_complaints'),503,'Video complaint table is not installed.');
  $filters=$request->validate(['status'=>['nullable','in:pending,reviewed,resolved'],'group'=>['nullable','in:department,non-department'],'video_id'=>['nullable','integer','min:1']]);
  $query=LebdVideoComplaint::query()->latest('id');
  if(!empty($filters['status']))$query->where('status',$filters['status']);
  if(!empty($filters['group']))$query->where('premium_group',$filters['group']);
  if(!empty($filters['video_id']))$query->where('video_id',$filters['video_id']);
  $complaints=$query->paginate(25)->withQueryString();
  return view('admin.premium-class.complaints.index',compact('complaints','filters'));
 }
 public function update(Request $request,LebdVideoComplaint $complaint){
  $data=$request->validate(['status'=>['required','in:pending,reviewed,resolved'],'admin_note'=>['nullable','string','max:2000']]);
  $complaint->update(['status'=>$data['status'],'admin_note'=>$data['admin_note']??null,
   'reviewed_by'=>Auth::id(),'reviewed_at'=>now()]);
  return back()->with('success','অভিযোগের অবস্থা আপডেট হয়েছে।');
 }
}
