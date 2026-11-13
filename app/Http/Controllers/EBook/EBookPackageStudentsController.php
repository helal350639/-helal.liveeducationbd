<?php
namespace App\Http\Controllers\EBook;
use App\Http\Controllers\Controller;
use App\Services\EBook\EBookStudentReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
class EBookPackageStudentsController extends Controller {
    private function admin():void { $u=Auth::user();abort_unless($u && $u->AuthorizeRoles(['SuperAdmin','Admin']),403); }
    private function current():array {
        $this->admin();
        $context=Session::get('_lebd_ebook_picker');
        $obj=Session::get('_object');
        abort_unless(is_array($context) && is_array($obj) && ($obj['type']??'')==='EBookPackage' && (int)$context['package_id']===(int)$obj['id'],409,'E-Book selection session expired.');
        return $context;
    }
    public function start(int $package) {
        $this->admin();
        abort_unless(Schema::hasTable('lebd_ebook_manual_grants'),503,'Import E-Book manual grants SQL first.');
        $p=DB::table('lebd_ebook_packages')->where('id',$package)->first(); abort_unless($p,404);
        abort_unless($p->status==='Active',422,'Inactive package cannot be assigned.');
        if(Session::has('_object') && !Session::has('_lebd_ebook_picker'))return redirect()->route('student.index')->withErrors(['ebook'=>'আগের Student selection Done করে আবার চেষ্টা করুন।']);
        $context=Session::get('_lebd_ebook_picker');
        if(is_array($context) && (int)($context['package_id']??0)!==$package)return redirect()->route('admin.ebook-packages.index')->withErrors(['ebook'=>'আগের E-Book Package selection Done করুন।']);
        // Reconcile the selection with the database on every visit. A prior active
        // manual grant should appear checked, without creating another grant.
        $now=Carbon::now('Asia/Dhaka');
        $existingIds=DB::table('lebd_ebook_manual_grants')
            ->where('package_id',$package)
            ->where('starts_at','<=',$now)
            ->where('ends_at','>',$now)
            ->pluck('student_id')->map(fn($id)=>(int)$id)->unique()->values()->all();
        $context=$context??['package_id'=>$package,'selected'=>[]];
        $previous=array_values(array_unique(array_map('intval',$context['selected']??[])));
        $context['existing']=$existingIds;
        $context['selected']=array_values(array_unique(array_merge($existingIds,$previous)));
        Session::put('_lebd_ebook_picker',$context);
        Session::put('_object',['id'=>$package,'type'=>'EBookPackage','name'=>'E-Book: '.$p->name,'counter'=>count($context['selected']),'xids'=>$context['selected']]);
        return redirect()->route('student.index')->with('success','আগে থেকে Active Manual Student-দের Checkbox Checked আছে। নতুন Student নির্বাচন করে Done করুন। Access সরাতে Remove Manual ব্যবহার করুন।');
    }
    public function select(Request $request) {
        $ctx=$this->current();$v=$request->validate(['id'=>'required|integer|exists:students,id','action'=>'required|in:add,remove']);
        $set=array_values(array_unique(array_map('intval',$ctx['selected'])));$id=(int)$v['id'];
        if($v['action']==='add' && !in_array($id,$set,true))$set[]=$id;
        if($v['action']==='remove')$set=array_values(array_filter($set,fn($x)=>$x!==$id));
        // Existing grants are display-only here; unchecking them must NOT revoke
        // entitlement. The dedicated Remove Manual action handles revocation.
        $existing=array_values(array_unique(array_map('intval',$ctx['existing']??[])));
        $newIds=array_diff($set,$existing);
        if(count($newIds)>200)throw ValidationException::withMessages(['student'=>'Maximum 200 new students at once.']);
        $ctx['selected']=$set;Session::put('_lebd_ebook_picker',$ctx);
        $obj=Session::get('_object');$obj['xids']=$set;$obj['counter']=count($set);Session::put('_object',$obj);
        return response()->json(['success'=>true,'counter'=>count($set)]);
    }
    /**
     * Revoke one manual package assignment without touching a paid order.
     * Must be called inside an active DB transaction.
     */
    private function revokeManualGrantInsideTransaction(int $package, int $student, Carbon $now): array
    {
        $grant = DB::table('lebd_ebook_manual_grants')
            ->where('package_id', $package)
            ->where('student_id', $student)
            ->lockForUpdate()
            ->first();

        if (!$grant) {
            return ['removed' => false, 'deactivated' => 0, 'preserved' => 0];
        }

        $categories = DB::table('lebd_ebook_package_categories')
            ->where('package_id', $package)
            ->pluck('category_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        DB::table('lebd_ebook_manual_grants')->where('id', $grant->id)->delete();

        $deactivated = 0;
        $preserved = 0;

        foreach ($categories as $category) {
            $entitlements = DB::table('lebd_ebook_entitlements')
                ->where('user_id', $student)
                ->where('category_id', $category)
                ->where('package_id', $package)
                ->where('status', 'Active')
                ->lockForUpdate()
                ->get();

            if ($entitlements->isEmpty()) {
                continue;
            }

            $hasPaid = app(\App\Services\EBook\EBookAccess::class)
                ->hasActivePaidOrder($student, $package, $category);

            // Entitlements are package-specific. A different package in the
            // same category must not keep this removed package's entitlement alive.
            if ($hasPaid) {
                $preserved += $entitlements->count();
                continue;
            }

            $ids = $entitlements->pluck('id')->all();
            $deactivated += DB::table('lebd_ebook_entitlements')
                ->whereIn('id', $ids)
                ->where('status', 'Active')
                ->update(['status' => 'Inactive', 'updated_at' => $now]);
        }

        return ['removed' => true, 'deactivated' => $deactivated, 'preserved' => $preserved];
    }

    public function complete() {
        $ctx = $this->current();
        $ids = array_values(array_unique(array_map('intval', $ctx['selected'] ?? [])));
        $existingIds = array_values(array_unique(array_map('intval', $ctx['existing'] ?? [])));
        $removedIds = array_values(array_diff($existingIds, $ids));

        // An empty selection is valid when the Admin is removing all previously
        // assigned manual students from this package.
        if (!$ids && !$removedIds) {
            return redirect()->route('student.index')->withErrors(['ebook' => 'Student নির্বাচন করুন অথবা আগে যুক্ত Student Uncheck করে Done দিন।']);
        }

        $package = (int) $ctx['package_id'];
        $added = 0;
        $removed = 0;
        $deactivated = 0;
        $preserved = 0;

        DB::transaction(function () use ($package, $ids, $removedIds, &$added, &$removed, &$deactivated, &$preserved) {
            $p = DB::table('lebd_ebook_packages')->where('id', $package)->lockForUpdate()->first();
            abort_unless($p && $p->status === 'Active', 422);

            $cats = DB::table('lebd_ebook_package_categories')
                ->where('package_id', $package)->pluck('category_id')->all();
            abort_if(!$cats, 422, 'Package categories missing.');

            $now = Carbon::now('Asia/Dhaka');

            // Unchecked existing students lose only this package's manual grant.
            // A paid purchase of this exact package is preserved; other packages keep their own rows.
            foreach ($removedIds as $studentId) {
                $result = $this->revokeManualGrantInsideTransaction($package, (int) $studentId, $now);
                if ($result['removed']) $removed++;
                $deactivated += $result['deactivated'];
                $preserved += $result['preserved'];
            }

            // Selected students receive/retain this package's manual access.
            foreach ($ids as $id) {
                $student = DB::table('students')->where('id', $id)->first();
                if (!$student) continue;

                $end = $now->copy();
                switch ($p->duration_unit) {
                    case 'days': $end->addDays($p->duration_value); break;
                    case 'months': $end->addMonthsNoOverflow($p->duration_value); break;
                    case 'years': $end->addYearsNoOverflow($p->duration_value); break;
                    default: abort(422, 'Invalid duration');
                }

                $existingGrant = DB::table('lebd_ebook_manual_grants')
                    ->where('student_id', $id)
                    ->where('package_id', $package)
                    ->lockForUpdate()
                    ->first();

                if (Schema::hasTable('lebd_ebook_access_revocations')) {
                    DB::table('lebd_ebook_access_revocations')
                        ->where('student_id', $id)->where('package_id', $package)->delete();
                }

                if ($existingGrant && $existingGrant->ends_at > $now->toDateTimeString()) {
                    continue;
                }

                // Do not shorten a longer paid entitlement or another active access.
                foreach ($cats as $cat) {
                    $old = DB::table('lebd_ebook_entitlements')
                        ->where('user_id', $id)
                        ->where('category_id', $cat)
                        ->where('package_id', $package)
                        ->lockForUpdate()
                        ->orderByDesc('expires_at')
                        ->first();

                    if ($old && $old->status === 'Active' && $old->expires_at > $now->toDateTimeString()
                        && $old->expires_at > $end->toDateTimeString()) {
                        continue;
                    }

                    if ($old) {
                        DB::table('lebd_ebook_entitlements')->where('id', $old->id)->update([
                            'package_id' => $package,
                            'status' => 'Active',
                            'expires_at' => $end,
                            'updated_at' => $now,
                        ]);
                    } else {
                        DB::table('lebd_ebook_entitlements')->insert([
                            'user_id' => $id,
                            'category_id' => $cat,
                            'package_id' => $package,
                            'status' => 'Active',
                            'starts_at' => $now,
                            'expires_at' => $end,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }

                $fields = ['starts_at' => $now, 'ends_at' => $end, 'added_by' => Auth::id(), 'updated_at' => $now];
                if ($existingGrant) {
                    DB::table('lebd_ebook_manual_grants')->where('id', $existingGrant->id)->update($fields);
                } else {
                    DB::table('lebd_ebook_manual_grants')->insert($fields + [
                        'student_id' => $id,
                        'package_id' => $package,
                        'created_at' => $now,
                    ]);
                }
                $added++;
            }
        }, 3);

        Session::forget('_lebd_ebook_picker');
        Session::forget('_object');

        $message = "E-Book Package #{$package}: {$added} student(s) added/renewed; {$removed} manual assignment(s) removed.";
        if ($deactivated) $message .= " {$deactivated} manual-only access record(s) disabled.";
        if ($preserved) $message .= " {$preserved} access record(s) preserved because paid/other access exists.";

        return redirect()->route('admin.ebook-packages.index')->with('success', $message);
    }

    /**
     * Revoke a package's manual grant only. Do not change payment orders or wallets.
     * Existing E-Book entitlement rows may have been extended by prior manual grants,
     * so their expiry need not exactly equal the latest manual grant expiry.
     */
    public function removeManual(Request $request, int $package, int $student)
    {
        $this->admin();
        abort_unless(Schema::hasTable('lebd_ebook_manual_grants'), 503);
        $request->validate(['confirm_remove' => ['required', 'accepted']]);

        $result = ['removed' => false, 'deactivated' => 0, 'preserved' => 0];
        DB::transaction(function () use ($package, $student, &$result) {
            $result = $this->revokeManualGrantInsideTransaction(
                $package,
                $student,
                Carbon::now('Asia/Dhaka')
            );
        }, 3);

        $message = $result['removed']
            ? "Manual assignment removed. {$result['deactivated']} manual-only entitlement(s) disabled."
            : 'No manual assignment found; no changes were made.';

        if ($result['preserved']) {
            $message .= " {$result['preserved']} entitlement(s) preserved because paid or other active access exists.";
        }

        return redirect()->route('admin.ebook-packages.students.report', [
            'package' => $package,
            'method' => 'manual',
        ])->with('success', $message);
    }

    /**
     * Revoke every current access source for this exact package/student pair.
     * Financial orders remain as historical receipts; the revocation record
     * prevents paid-order fallback from silently restoring access.
     */
    public function removeAccess(Request $request, int $package, int $student)
    {
        $this->admin();
        $request->validate(['confirm_remove' => ['required', 'accepted']]);
        abort_unless(DB::table('lebd_ebook_packages')->where('id', $package)->exists(), 404);
        abort_unless(DB::table('students')->where('id', $student)->exists(), 404);
        abort_unless(Schema::hasTable('lebd_ebook_access_revocations'), 503,
            'E-Book access revocation SQL must be installed first.');

        $now = Carbon::now('Asia/Dhaka');
        DB::transaction(function () use ($package, $student, $now) {
            if (Schema::hasTable('lebd_ebook_manual_grants')) {
                DB::table('lebd_ebook_manual_grants')
                    ->where('package_id', $package)->where('student_id', $student)->delete();
            }
            if (Schema::hasTable('lebd_ebook_entitlements')) {
                DB::table('lebd_ebook_entitlements')
                    ->where('package_id', $package)->where('user_id', $student)
                    ->where('status', 'Active')
                    ->update(['status' => 'Inactive', 'updated_at' => $now]);
            }
            DB::table('lebd_ebook_access_revocations')->updateOrInsert(
                ['student_id' => $student, 'package_id' => $package],
                ['revoked_at' => $now, 'revoked_by' => Auth::id(),
                 'updated_at' => $now, 'created_at' => $now]
            );
        }, 3);

        return redirect()->route('admin.ebook-packages.students.report', [
            'package' => $package, 'method' => 'all',
        ])->with('success',
            'এই শিক্ষার্থীর জন্য নির্বাচিত প্যাকেজের সব Access বন্ধ করা হয়েছে। Payment/Referral Order history অক্ষত আছে। নতুন করে প্যাকেজ কিনলে বা Admin আবার Add করলে Access চালু হবে।');
    }

    public function report(Request $request,int $package,EBookStudentReport $service){
        $this->admin();$p=DB::table('lebd_ebook_packages')->find($package);abort_unless($p,404);
        $method=$request->query('method','all');abort_unless(in_array($method,['all','active','manual','wallet','ssl'],true),422);
        $rows=$service->entries($package);$count=$service->summary([$package])[$package];
        $rows=array_filter($rows,fn($r)=>$method==='all' || ($method==='active'?$r['active']:isset($r['methods'][$method])));
        $q=trim((string)$request->query('q',''));
        if($q!=='')$rows=array_filter($rows,fn($r)=>$r['student'] && (str_contains(mb_strtolower($r['student']->name),mb_strtolower($q)) || str_contains((string)$r['student']->id,$q) || str_contains(mb_strtolower((string)$r['student']->email),mb_strtolower($q))));
        return view('admin.ebook-packages.student-report',compact('p','rows','count','method','q'));
    }
}
