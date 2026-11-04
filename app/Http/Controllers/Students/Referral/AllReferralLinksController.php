<?php
namespace App\Http\Controllers\Students\Referral;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

/** Read-only eligible packages. Does not grant access, debit wallet or award bonuses. */
final class AllReferralLinksController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        $id = (int) $student->id;
        $now = now('Asia/Dhaka')->format('Y-m-d H:i:s');
        $sections = [
            'batch' => ['title'=>'Batch Referral Link','icon'=>'fa-layer-group','items'=>[]],
            'exam' => ['title'=>'Exam Section Referral Link','icon'=>'fa-calendar-check','items'=>[]],
            'mcq' => ['title'=>'MCQ Study Referral Link','icon'=>'fa-list-check','items'=>[]],
            'video' => ['title'=>'Video Section Referral Link','icon'=>'fa-video','items'=>[]],
            'ebook' => ['title'=>'E-Book Referral Link','icon'=>'fa-book','items'=>[]],
        ];
        // Merge paid and manually granted MCQ entitlements; unique by package ID.
        $mcqIds = [];
        if (Schema::hasTable('lebd_mcq_subscriptions')) {
            $mcqIds = DB::table('lebd_mcq_subscriptions')
                ->where('student_id',$id)->where('starts_at','<=',$now)->where('ends_at','>',$now)
                ->pluck('package_id')->map(fn ($v)=>(int)$v)->all();
        }
        if (Schema::hasTable('lebd_mcq_manual_grants')) {
            $manual = DB::table('lebd_mcq_manual_grants')->where('student_id',$id)
                ->where('status','Active')->where('starts_at','<=',$now)->where('ends_at','>',$now)
                ->pluck('package_id')->map(fn ($v)=>(int)$v)->all();
            $mcqIds = array_values(array_unique(array_merge($mcqIds,$manual)));
        }
        if ($mcqIds && Schema::hasTable('mcq_subscription_packages')) {
            $records = DB::table('mcq_subscription_packages')->whereIn('id',$mcqIds)
                ->select('id','name','referral_discount','referrer_bonus')->orderBy('name')->get();
            foreach ($records as $p) {
                $sections['mcq']['items'][] = [
                    'name'=>(string)$p->name,'id'=>(int)$p->id,
                    'discount'=>(float)$p->referral_discount,'bonus'=>(float)$p->referrer_bonus,
                    'url'=>($student->referral_id ? route('lebd.packages.details',['section'=>'mcq','package'=>$p->id,'ref'=>(string)$student->referral_id]) : null),
                    'ready'=>false, // Controlled end-to-end referral checkout not yet deployed.
                    'source'=>'Active package',
                ];
            }
        }
        // Use ONLY route destinations already registered by Laravel. These are
        // package CATALOG links, not referral-token links and do not award rewards.
        $examCatalogUrl = Route::has('student.exam-section.packages')
            ? route('student.exam-section.packages') : null;
        $videoCatalogUrl = null;
        foreach (Route::getRoutes() as $existingRoute) {
            if (in_array('GET', $existingRoute->methods(), true)
                && trim($existingRoute->uri(), '/') === 'premium-recordings/packages') {
                $videoCatalogUrl = url('/premium-recordings/packages');
                break;
            }
        }
        // Exam Section: status/time are sufficient to display owned packages; referral reward not yet enabled.
        if (Schema::hasTable('lebd_exam_section_student_package_access') && Schema::hasTable('lebd_exam_section_packages')) {
            $records = DB::table('lebd_exam_section_student_package_access as a')
                ->join('lebd_exam_section_packages as p','p.id','=','a.package_id')
                ->where('a.student_id',$id)->where('a.status','active')
                ->where('a.starts_at','<=',$now)->where('a.ends_at','>',$now)
                ->select('p.id','p.name')->distinct()->orderBy('p.name')->get();
            foreach ($records as $p) $sections['exam']['items'][] = [
                'name'=>$p->name,'id'=>(int)$p->id,'discount'=>null,'bonus'=>null,
                'url'=>$examCatalogUrl,'ready'=>false,'source'=>'Active package catalog',
            ];
        }
        if (Schema::hasTable('lebd_premium_class_orders') && Schema::hasTable('lebd_premium_class_packages')) {
            $records = DB::table('lebd_premium_class_orders as o')
                ->join('lebd_premium_class_packages as p','p.id','=','o.package_id')
                ->where('o.student_id',$id)->whereIn('o.status',['paid','Paid','Complete','completed','success','Success'])
                ->where('o.expires_at','>',$now)->select('p.id','p.name')->distinct()->orderBy('p.name')->get();
            foreach ($records as $p) $sections['video']['items'][] = [
                'name'=>$p->name,'id'=>(int)$p->id,'discount'=>null,'bonus'=>null,
                'url'=>$videoCatalogUrl,'ready'=>false,'source'=>'Active package catalog',
            ];
        }
        // Batch/E-Book have intentionally no inferred entitlement: route/schema must be verified.
        return view('student.referral.all-links', [
            'sections'=>$sections,
            'referralCode'=>(string)($student->referral_id ?? ''),
        ]);
    }
}
