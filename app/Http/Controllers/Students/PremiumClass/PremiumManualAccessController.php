<?php
namespace App\Http\Controllers\Students\PremiumClass;
use App\Http\Controllers\Controller;
use App\Services\PremiumClass\PremiumManualAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class PremiumManualAccessController extends Controller {
  public function index(){
    $id=(int)auth('student')->id();abort_unless($id,403);
    $manual=PremiumManualAccess::available()
        ? DB::table('lebd_premium_manual_grants as g')->leftJoin('lebd_premium_class_packages as p','p.id','=','g.package_id')
           ->where('g.student_id',$id)->where('g.starts_at','<=',now())->where('g.ends_at','>',now())
           ->select('g.*','p.name as package_name')->orderByDesc('g.ends_at')->get()
        : collect();
    $paid=Schema::hasTable('lebd_premium_class_orders')
        ? DB::table('lebd_premium_class_orders as o')->leftJoin('lebd_premium_class_packages as p','p.id','=','o.package_id')
           ->where('o.student_id',$id)->where('o.status','paid')->where('o.expires_at','>',now())
           ->select('o.*','p.name as package_name')->orderByDesc('o.expires_at')->get()
        : collect();
    return view('premium-class.my-premium-access',compact('manual','paid'));
  }
}
