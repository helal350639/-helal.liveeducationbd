<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only combined reporting. No entitlement or payment mutation. */
class LebdOrdersOverviewController extends Controller
{
    public function index(Request $request)
    {
        // Explicit role check in addition to the existing admin-only route middleware.
        $user = Auth::guard('web')->user();
        abort_unless($user, 403);
        try { $role = $user->authRole()->name ?? null; } catch (\Throwable $e) { abort(403); }
        abort_unless(in_array($role, ['Admin','SuperAdmin'], true), 403);

        $params = $request->validate([
            'from'=>'nullable|date_format:Y-m-d', 'to'=>'nullable|date_format:Y-m-d',
            'status'=>'nullable|in:all,paid,pending',
            'period'=>'nullable|in:1d,7d,30d,1y,custom,all',
            'save_default'=>'nullable|in:1',
        ]);
        // One selection at a time. A saved default is scoped to this admin's session.
        $allowed = ['1d','7d','30d','1y','custom','all'];
        $savedPeriod = $request->session()->get('lebd_overview_default_period', 'all');
        if (!in_array($savedPeriod,$allowed,true)) $savedPeriod='all';
        if ($request->input('save_default') === '1') {
            $selectedToSave = $params['period'] ?? 'all';
            $request->session()->put('lebd_overview_default_period', $selectedToSave);
            $savedPeriod=$selectedToSave;
        }
        $hasRequestFilters = $request->hasAny(['from','to','status','period']);
        $period = $params['period'] ?? ($hasRequestFilters ? 'custom' : $savedPeriod);
        $from = $params['from'] ?? null;
        $to = $params['to'] ?? null;
        if (in_array($period,['1d','7d','30d','1y'],true)) {
            $today = now('Asia/Dhaka')->startOfDay();
            $to=$today->toDateString();
            $from=match($period) {
                '1d'=>$today->copy()->toDateString(),
                '7d'=>$today->copy()->subDays(6)->toDateString(),
                '30d'=>$today->copy()->subDays(29)->toDateString(),
                '1y'=>$today->copy()->subYear()->addDay()->toDateString(),
            };
        } elseif($period==='all') {
            $from=null; $to=null;
        }
        if ($from && $to) abort_if($from > $to, 422, 'Invalid date range');
        $status = $params['status'] ?? 'all';

        $specs = [
            'batch'=>['label'=>'Batch','table'=>'orders','key'=>'transaction_id','method'=>null,'package'=>'batches','pkg_key'=>'batch_id','paid'=>['Complete','Processing'],'pending'=>['Pending'],'route'=>'admin.lebd-orders.batch'],
            'video'=>['label'=>'Video','table'=>'lebd_premium_class_orders','key'=>'tran_id','method'=>null,'package'=>'lebd_premium_class_packages','pkg_key'=>'package_id','paid'=>['paid'],'pending'=>['pending'],'route'=>'admin.lebd-orders.video'],
            'ebook'=>['label'=>'E-Book','table'=>'lebd_ebook_payment_orders','key'=>'transaction_id','method'=>null,'package'=>'lebd_ebook_categories','pkg_key'=>'category_id','paid'=>['Complete'],'pending'=>['Pending'],'route'=>'admin.lebd-orders.ebook'],
            'mcq'=>['label'=>'MCQ Study','table'=>'lebd_mcq_payment_orders','key'=>'transaction_id','method'=>null,'package'=>'mcq_subscription_packages','pkg_key'=>'package_id','paid'=>['Complete'],'pending'=>['Pending'],'route'=>'admin.lebd.mcq-orders.index'],
            'exam'=>['label'=>'Exam Section','table'=>'lebd_exam_section_package_orders','key'=>'transaction_id','method'=>'method','package'=>'lebd_exam_section_packages','pkg_key'=>'package_id','paid'=>['paid'],'pending'=>['pending'],'route'=>'admin.lebd-orders.exam'],
        ];
        $cards=[]; $recent=collect(); $received=0; $pending=0; $totalOrders=0;
        $sources=['ssl'=>0,'referral'=>0,'other'=>0];
        $sslIncome=0.0; $referralIncome=0.0; $unclassifiedIncome=0.0;
        foreach ($specs as $key=>$spec) {
            $card=['label'=>$spec['label'],'count'=>0,'paid'=>0.0,'pending'=>0.0,'route'=>$spec['route'],'available'=>false];
            if (!Schema::hasTable($spec['table'])) { $cards[$key]=$card; continue; }
            $card['available']=true;
            $q=DB::table($spec['table'].' as o');
            if ($from) $q->where('o.created_at','>=',$from.' 00:00:00');
            if ($to) $q->where('o.created_at','<=',$to.' 23:59:59');
            // Overview intentionally reports only the same Paid/Pending groups as Orders pages.
            // Preserve raw database status; exclude failed/cancelled from these two financial groups.
            $q->whereIn('o.status', array_merge($spec['paid'], $spec['pending']));
            if ($status==='paid') $q->whereIn('o.status',$spec['paid']);
            elseif ($status==='pending') $q->whereIn('o.status',$spec['pending']);
            $paidSql=implode(',',array_map(fn($v)=>"'".str_replace("'","''",$v)."'",$spec['paid']));
            $pendingSql=implode(',',array_map(fn($v)=>"'".str_replace("'","''",$v)."'",$spec['pending']));
            $sum=(clone $q)->selectRaw("COUNT(*) AS total, COALESCE(SUM(CASE WHEN o.status IN ($paidSql) THEN o.amount ELSE 0 END),0) AS paid_total, COALESCE(SUM(CASE WHEN o.status IN ($pendingSql) THEN o.amount ELSE 0 END),0) AS pending_total")->first();
            $card['count']=(int)$sum->total;
            $card['paid']=(float)$sum->paid_total;
            $card['pending']=(float)$sum->pending_total;
            $cards[$key]=$card;
            $totalOrders+=$card['count'];$received+=$card['paid'];$pending+=$card['pending'];

            // All matching PAID orders, not the recent-15 sample. Reuse the same
            // date/status scope; never include pending in source-specific income.
            // Classification matches each section's documented transaction markers.
            $paidOrders=(clone $q)->whereIn('o.status',$spec['paid']);
            if ($key==='exam') {
                $referralCondition = "o.method = 'wallet'";
                $sslCondition = "o.method = 'sslcommerz'";
            } elseif ($key==='batch') {
                $referralCondition = "o.transaction_id LIKE 'LEBDBR%'";
                $sslCondition = "o.transaction_id NOT LIKE 'LEBDBR%'"; // legacy batch gateway orders
            } elseif ($key==='video') {
                $referralCondition = "o.tran_id LIKE 'LEBDR%'";
                $sslCondition = "o.tran_id LIKE 'LEBDP%'";
            } elseif ($key==='mcq') {
                $referralCondition = "o.transaction_id LIKE 'MCQR%'";
                $sslCondition = "o.transaction_id LIKE 'MCQS%'";
            } else { // E-Book: referral debit marker is authoritative.
                $referralCondition = "(o.gateway_validation_id = 'REFERRAL_BALANCE' OR o.transaction_id LIKE 'EBR%')";
                $sslCondition = "((o.gateway_validation_id IS NULL OR o.gateway_validation_id <> 'REFERRAL_BALANCE') AND o.transaction_id NOT LIKE 'EBR%')";
            }
            $income=(clone $paidOrders)->reorder()->selectRaw(
                "COALESCE(SUM(CASE WHEN ($sslCondition) THEN o.amount ELSE 0 END),0) AS ssl_income, " .
                "COALESCE(SUM(CASE WHEN ($referralCondition) THEN o.amount ELSE 0 END),0) AS referral_income"
            )->first();
            $sslIncome+=(float)($income->ssl_income??0);
            $referralIncome+=(float)($income->referral_income??0);


            $cols=['o.id','o.student_id','o.status','o.amount','o.created_at','o.'.$spec['key'].' as order_ref'];
            if ($spec['method']) $cols[]='o.'.$spec['method'].' as order_method';
            $rows=(clone $q)->select($cols)->orderByDesc('o.created_at')->orderByDesc('o.id')->limit(12)->get();
            foreach ($rows as $r) {
                $method=$key==='exam' ? (($r->order_method ?? '')==='wallet'?'referral':'ssl') : $this->source($key,(string)$r->order_ref);
                $sources[$method]++;
                $recent->push((object)['section'=>$key,'label'=>$spec['label'],'id'=>$r->id,'student_id'=>$r->student_id,'ref'=>$r->order_ref,'amount'=>$r->amount,'status'=>$r->status,'created_at'=>$r->created_at,'method'=>$method,'is_paid'=>in_array($r->status,$spec['paid'],true),'route'=>$spec['route']]);
            }
        }
        $recent=$recent->sortByDesc('created_at')->take(15)->values();
        // Display names are loaded only for recent rows, keeping summary queries inexpensive.
        if ($recent->isNotEmpty() && Schema::hasTable('students')) {
            $names=DB::table('students')->whereIn('id',$recent->pluck('student_id')->unique())->pluck('name','id');
            $recent=$recent->map(function($r)use($names){$r->student_name=$names[$r->student_id]??('Student #'.$r->student_id);return $r;});
        }
        // Account for paid records with unknown payment markers without inventing a source.
        $unclassifiedIncome=max(0,round($received-$sslIncome-$referralIncome,2));
        // Above payment-source counts describe recent rows only, never all-time payment share.
        return view('admin.lebd-orders.overview',compact('cards','recent','received','pending','totalOrders','sources','from','to','status','period','savedPeriod','sslIncome','referralIncome','unclassifiedIncome'));
    }
    private function source(string $section,string $ref): string
    {
        if ($section==='video') return str_starts_with($ref,'LEBDR')?'referral':'ssl';
        if ($section==='batch') return str_starts_with($ref,'LEBDBR')?'referral':'ssl';
        if ($section==='mcq') return str_starts_with($ref,'MCQR')?'referral':'ssl';
        if ($section==='ebook') return str_starts_with($ref,'EBR')?'referral':'ssl';
        return 'other';
    }
}
