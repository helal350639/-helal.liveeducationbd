<?php
namespace App\Http\Controllers\Admin\MCQSubscription;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/** Order-free manual grants. Deliberately never writes payment orders or referral transactions. */
class ManualStudentGrantController extends Controller
{
    private function package(int $id)
    {
        $package = DB::table('mcq_subscription_packages')->where('id', $id)->first();
        abort_unless($package, 404);
        return $package;
    }

    private function requireTable(): void
    {
        if (!Schema::hasTable('lebd_mcq_manual_grants')) {
            abort(503, 'Manual grant table is missing. Apply the additive SQL from the ZIP first.');
        }
    }

    public function index(Request $request, int $package)
    {
        $this->requireTable();
        $record = $this->package($package);
        $search = trim((string) $request->query('search', ''));
        $students = Student::query()->select(['id','reg_id','name','contact','email','status'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', '%' . $search . '%')
                      ->orWhere('reg_id', 'like', '%' . $search . '%')
                      ->orWhere('contact', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                    if (ctype_digit($search)) $w->orWhere('id', (int) $search);
                });
            })->orderByDesc('id')->paginate(15)->withQueryString();
        $grants = DB::table('lebd_mcq_manual_grants as g')
            ->join('students as s', 's.id', '=', 'g.student_id')
            ->where('g.package_id', $package)
            ->select('g.*','s.name','s.reg_id','s.contact')
            ->orderByDesc('g.id')->paginate(15, ['*'], 'grants_page');
        $paid = DB::table('lebd_mcq_subscriptions')
            ->where('package_id',$package)->where('ends_at','>',now('Asia/Dhaka'))->pluck('student_id')->all();
        $paidIds = array_fill_keys(array_map('intval',$paid), true);
        $grantIds = DB::table('lebd_mcq_manual_grants')->where('package_id',$package)
            ->where('status','Active')->where('ends_at','>',now('Asia/Dhaka'))
            ->pluck('student_id')->all();
        $activeIds = array_fill_keys(array_map('intval',$grantIds), true);
        return view('admin.mcq-subscription.manual-students', compact('record','students','grants','activeIds','paidIds','search'));
    }

    public function store(Request $request, int $package)
    {
        $this->requireTable();
        $record = $this->package($package);
        $input = $request->validate([
            'student_ids' => ['required','array','min:1','max:100'],
            'student_ids.*' => ['required','integer','distinct', Rule::exists('students','id')],
            'note' => ['nullable','string','max:500'],
        ]);
        $ids = array_map('intval',$input['student_ids']);
        $activeStudents = Student::query()->whereIn('id',$ids)->where('status','Active')->pluck('id')->map(fn($n)=>(int)$n)->all();
        if (count($activeStudents) !== count($ids)) {
            return back()->withErrors(['student_ids'=>'Only Active students can receive a manual grant.'])->withInput();
        }
        $now = Carbon::now('Asia/Dhaka');
        $months = (int) $record->duration_value;
        if ($months < 1) return back()->withErrors(['student_ids'=>'Invalid package duration.']);
        $ends = $now->copy();
        switch ($record->duration_unit) {
            case 'days': $ends->addDays($months); break;
            case 'months': $ends->addMonthsNoOverflow($months); break;
            case 'years': $ends->addYearsNoOverflow($months); break;
            default: return back()->withErrors(['student_ids'=>'Unsupported duration unit.']);
        }
        $created = 0;
        $skipped = 0;
        DB::transaction(function () use ($ids,$package,$now,$ends,$input,&$created,&$skipped) {
            foreach ($ids as $studentId) {
                // Keep paid access unchanged; do not create redundant access while already paid.
                $paid = DB::table('lebd_mcq_subscriptions')->where('student_id',$studentId)
                    ->where('package_id',$package)->where('ends_at','>',$now)->exists();
                $old = DB::table('lebd_mcq_manual_grants')->where('student_id',$studentId)
                    ->where('package_id',$package)->lockForUpdate()->first();
                if ($paid || ($old && $old->status === 'Active' && Carbon::parse($old->ends_at)->gt($now))) {
                    $skipped++;
                    continue;
                }
                $payload = [
                    'starts_at'=>$now,'ends_at'=>$ends,'status'=>'Active',
                    'note'=>$input['note']??null,
                    'granted_by'=>Auth::id(), 'updated_at'=>$now,
                ];
                if ($old) DB::table('lebd_mcq_manual_grants')->where('id',$old->id)->update($payload);
                else DB::table('lebd_mcq_manual_grants')->insert($payload + [
                    'student_id'=>$studentId,'package_id'=>$package,'created_at'=>$now,
                ]);
                $created++;
            }
        });
        $destination = $request->input('save_action') === 'done'
            ? 'admin.mcq-subscription.index'
            : 'admin.mcq-subscription.manual-students';
        $routeArgs = $destination === 'admin.mcq-subscription.index' ? [] : [$package];
        return redirect()->route($destination, $routeArgs)
            ->with('success', "Manual grant saved: {$created}; already active: {$skipped}. No payment/order was created.");
    }

    public function revoke(Request $request, int $package, int $grant)
    {
        $this->requireTable();
        $this->package($package);
        DB::table('lebd_mcq_manual_grants')->where('package_id',$package)
            ->where('id',$grant)->where('status','Active')
            ->update(['status'=>'Revoked','updated_at'=>now('Asia/Dhaka')]);
        return back()->with('success','Manual grant revoked. Paid subscription, if any, is untouched.');
    }
}
