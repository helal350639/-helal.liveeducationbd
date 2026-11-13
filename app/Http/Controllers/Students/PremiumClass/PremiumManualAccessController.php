<?php
namespace App\Http\Controllers\Students\PremiumClass;
use App\Http\Controllers\Controller;
use App\Services\PremiumClass\PremiumManualAccess;
use App\Services\PremiumClass\PremiumPackageAccessRevocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class PremiumManualAccessController extends Controller {
  public function index(){
    $id=(int)auth('student')->id();abort_unless($id,403);
    $manual=PremiumManualAccess::available()
        ? DB::table('lebd_premium_manual_grants as g')->leftJoin('lebd_premium_class_packages as p','p.id','=','g.package_id')
           ->where('g.student_id',$id)->where('g.starts_at','<=',now())->where('g.ends_at','>',now())
           ->when(PremiumPackageAccessRevocation::available(), function($q) use ($id) {
             $q->whereNotExists(function($sub) use ($id) {
               $sub->selectRaw('1')->from('lebd_premium_package_access_revocations as rv')
                 ->whereColumn('rv.package_id','g.package_id')->where('rv.student_id',$id)->whereNotNull('rv.revoked_at');
             });
           })
           ->select('g.*','p.name as package_name')->orderByDesc('g.ends_at')->get()
        : collect();
    $paid=Schema::hasTable('lebd_premium_class_orders')
        ? DB::table('lebd_premium_class_orders as o')->leftJoin('lebd_premium_class_packages as p','p.id','=','o.package_id')
           ->where('o.student_id',$id)->where('o.status','paid')->where('o.expires_at','>',now())
           ->when(PremiumPackageAccessRevocation::available(), function($q) use ($id) {
             $q->whereNotExists(function($sub) use ($id) {
               $sub->selectRaw('1')->from('lebd_premium_package_access_revocations as rv')
                 ->whereColumn('rv.package_id','o.package_id')->where('rv.student_id',$id)->whereNotNull('rv.revoked_at');
             });
           })
           ->select('o.*','p.name as package_name')->orderByDesc('o.expires_at')->get()
        : collect();
    return view('premium-class.my-premium-access',compact('manual','paid'));
  }
}
