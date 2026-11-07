<?php
namespace App\Http\Controllers\Admin\PremiumClass;

use App\Http\Controllers\Controller;
use App\Models\PremiumClass\PremiumClassPackage;
use App\Services\PremiumClass\PremiumManualAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

/** Reuse the existing layouts.students.index and its check()/Done UI without replacing it. */
class PremiumManualStudentController extends Controller
{
    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user && $user->AuthorizeRoles(['SuperAdmin', 'Admin']), 403);
    }

    private function context(): array
    {
        $this->authorizeAdmin();
        $picker = Session::get('_lebd_premium_picker');
        abort_unless(is_array($picker) && isset($picker['package_id']), 403, 'Premium picker session expired. Open Add Student again.');
        $object = Session::get('_object');
        abort_unless(is_array($object) && ($object['type'] ?? '') === 'PremiumPackage'
            && (int)($object['id'] ?? 0) === (int)$picker['package_id'], 409, 'Student selection context does not match package.');
        return $picker;
    }

    /** Existing V13 Add Student URL redirects into the EXISTING Students Accounts screen. */
    public function index(Request $request, PremiumClassPackage $package)
    {
        return $this->start($request, $package);
    }

    public function start(Request $request, PremiumClassPackage $package)
    {
        $this->authorizeAdmin();
        abort_unless(PremiumManualAccess::available(), 503, 'Premium manual grants table missing.');
        abort_unless($package->is_active, 422, 'Cannot add students to an inactive package.');
        if (!Session::has('_lebd_premium_picker')) {
            // Do not silently discard an unfinished legacy Group/Batch selection.
            if (Session::has('_object')) {
                return redirect()->route('student.index')->withErrors([
                    'premium' => 'আগের Student selection চলমান আছে। সেটি Done করে আবার Add Student চাপুন।'
                ]);
            }
        }
        $picker = Session::get('_lebd_premium_picker');
        if (is_array($picker) && (int)($picker['package_id'] ?? 0) !== (int)$package->id) {
            return redirect()->route('admin.premium-class-packages.index')->withErrors([
                'premium' => 'অন্য Premium Package-এর Student Selection চলছে। আগে Done করুন।'
            ]);
        }
        if (!is_array($picker)) {
            $picker = ['package_id' => (int)$package->id, 'selected' => []];
        }
        Session::put('_lebd_premium_picker', $picker);
        Session::put('_object', [
            'id' => (int)$package->id,
            'type' => 'PremiumPackage',
            'name' => 'Premium: '.$package->name,
            'counter' => count($picker['selected']),
            'xids' => $picker['selected'],
        ]);
        return redirect()->route('student.index')->with('success', 'Students Accounts থেকে Student নির্বাচন করুন, তারপর Done চাপুন।');
    }

    /** Called only from StudentCtrl@addStudentObject when Premium picker is active. */
    public function select(Request $request)
    {
        $picker = $this->context();
        $data = $request->validate([
            'id' => ['required','integer','min:1','exists:students,id'],
            'action' => ['required','in:add,remove'],
        ]);
        $id = (int)$data['id'];
        $selected = array_values(array_unique(array_map('intval', $picker['selected'] ?? [])));
        if ($data['action'] === 'add') {
            if (!in_array($id, $selected, true)) $selected[] = $id;
            if (count($selected) > 200) {
                throw ValidationException::withMessages(['student' => 'একবারে সর্বোচ্চ ২০০ Student নির্বাচন করুন।']);
            }
        } else {
            $selected = array_values(array_filter($selected, fn($value) => $value !== $id));
        }
        $picker['selected'] = $selected;
        Session::put('_lebd_premium_picker', $picker);
        $object = Session::get('_object');
        $object['xids'] = $selected;
        $object['counter'] = count($selected);
        Session::put('_object', $object);
        return response()->json(['success' => true, 'counter' => count($selected)]);
    }

    /** Called only from StudentCtrl@addStudentComplete. No fake payment order is inserted. */
    public function complete()
    {
        $picker = $this->context();
        abort_unless(PremiumManualAccess::available(), 503, 'Premium manual grants table missing.');
        $id = (int)$picker['package_id'];
        $selected = array_values(array_unique(array_map('intval', $picker['selected'] ?? [])));
        if (!$selected) {
            return redirect()->route('student.index')->withErrors(['premium' => 'কমপক্ষে একজন Student নির্বাচন করুন।']);
        }
        if (count($selected) > 200) abort(422, 'Maximum 200 students per operation.');
        $exists = DB::table('students')->whereIn('id', $selected)->count();
        abort_unless($exists === count($selected), 422, 'Student selection contains an invalid account.');
        $added = 0;
        DB::transaction(function () use ($id, $selected, &$added) {
            // Serialize grants for the package. Check paid or manual entitlement before insert/update.
            $package = PremiumClassPackage::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($package->is_active, 422, 'Package is inactive.');
            foreach ($selected as $studentId) {
                if (PremiumManualAccess::owned($studentId, $id)) continue;
                $now = now();
                $values = [
                    'package_type' => $package->package_type,
                    'department_categories' => json_encode($package->department_categories ?: []),
                    'non_department_categories' => json_encode($package->non_department_categories ?: []),
                    'starts_at' => $now,
                    'ends_at' => $now->copy()->addDays(max(1, (int)$package->validity_days)),
                    'added_by' => Auth::id(),
                    'updated_at' => $now,
                ];
                $old = DB::table('lebd_premium_manual_grants')->where('student_id',$studentId)->where('package_id',$id)->first();
                if ($old) {
                    DB::table('lebd_premium_manual_grants')->where('id',$old->id)->update($values);
                } else {
                    DB::table('lebd_premium_manual_grants')->insert($values + [
                        'student_id' => $studentId, 'package_id' => $id, 'created_at' => $now,
                    ]);
                }
                $added++;
            }
        }, 3);
        // Clear ONLY this Premium selection; legacy Group/Batch sessions are never repurposed.
        Session::forget('_lebd_premium_picker');
        Session::forget('_object');
        return redirect()->route('admin.premium-class-packages.index')
            ->with('success', "Premium Package #{$id}: {$added} student(s) granted access; existing active access was preserved.");
    }
}
