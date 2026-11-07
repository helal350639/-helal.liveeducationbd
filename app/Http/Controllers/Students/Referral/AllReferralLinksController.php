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
        // Batch: all three enrollment paths converge on the original batch_student pivot:
        //  - Admin manual addition, - Referral Wallet checkout, - verified SSLCommerz purchase.
        // Only an enrolled student may create a batch referral link. Do not grant access here.
        // Respect the Batch status and optional scheduled date range.
        if (Schema::hasTable('batch_student') && Schema::hasTable('batches')) {
            $today = now('Asia/Dhaka')->toDateString();
            $batchRows = DB::table('batch_student as bs')
                ->join('batches as b', 'b.id', '=', 'bs.batch_id')
                ->where('bs.student_id', $id)
                ->where('b.status', 'Active')
                ->where(function ($q) use ($today) {
                    $q->whereNull('b.start_at')->orWhereDate('b.start_at', '<=', $today);
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('b.end_at')->orWhereDate('b.end_at', '>=', $today);
                })
                ->select('b.id', 'b.name', 'b.reference_discount', 'b.referrer_bonus')
                ->distinct()->orderBy('b.name')->get();
            foreach ($batchRows as $batch) {
                // Uses the EXISTING batchReference endpoint, which verifies that
                // this referrer is enrolled in the given batch before storing referral.
                $link = Route::has('student.batch-reference')
                    ? route('student.batch-reference', [
                        'type' => 'student',
                        'batch' => (int)$batch->id,
                        'referrer' => $id,
                    ]) : null;
                $sections['batch']['items'][] = [
                    'id' => (int)$batch->id,
                    'name' => (string)$batch->name,
                    'discount' => (float)($batch->reference_discount ?? 0),
                    'bonus' => (float)($batch->referrer_bonus ?? 0),
                    'url' => $link,
                    'ready' => $link !== null,
                    'source' => 'Active Batch enrollment',
                ];
            }
        }

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
                ->where('p.status','Active')
                ->select('p.id','p.name','p.referral_discount','p.referrer_bonus')->distinct()->orderBy('p.name')->get();
            foreach ($records as $p) $sections['exam']['items'][] = [
                'name'=>$p->name,'id'=>(int)$p->id,
                'discount'=>(float)$p->referral_discount,'bonus'=>(float)$p->referrer_bonus,
                'url'=>!empty($student->referral_id) && Route::has('student.exam-section.package.review')
                    ? route('student.exam-section.package.review',['package'=>$p->id,'ref'=>$student->referral_id]) : null,
                'ready'=>!empty($student->referral_id),'source'=>'Active Exam package referral',
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
        // E-Book continues to be populated by the pre-existing Blade bridge.
        return view('student.referral.all-links', [
            'sections'=>$sections,
            'referralCode'=>(string)($student->referral_id ?? ''),
        ]);
    }
}
