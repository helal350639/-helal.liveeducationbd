<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * My Batch -> existing Common Exam Dashboard.
 * Never grants exam access on the basis of a missing department mapping.
 */
class PurchasedBatchExamsController extends Controller
{
    public function index(Request $request, int $batch)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);

        $ownedBatch = $student->batches()
            ->where('batches.id', $batch)
            ->first();
        abort_unless($ownedBatch, 403, 'এই ব্যাচে আপনার প্রবেশাধিকার নেই।');

        $course = $ownedBatch->course;
        abort_unless($course, 404);

        $verifiedDepartments = [];

        // Only verified purchase + membership can identify a department.
        // An older/free enrollment may have no matching order. That should
        // not hide the dashboard, but it must not expose a different exam.
        if (Schema::hasTable('orders')
            && Schema::hasColumn('orders', 'student_id')
            && Schema::hasColumn('orders', 'batch_id')
            && Schema::hasColumn('orders', 'department_id')
            && Schema::hasColumn('orders', 'status')) {
            $orderDepartmentIds = DB::table('orders')
                ->where('student_id', $student->id)
                ->where('batch_id', $batch)
                ->whereIn('status', ['Processing', 'Complete'])
                ->whereNotNull('department_id')
                ->distinct()
                ->pluck('department_id')
                ->map(static fn ($id) => (int) $id)
                ->all();

            $memberDepartmentIds = $student->departments()
                ->pluck('departments.id')
                ->map(static fn ($id) => (int) $id)
                ->all();

            $verifiedDepartments = array_values(array_intersect($orderDepartmentIds, $memberDepartmentIds));
        }

        $department = null;
        $chosen = $request->query('department_id');

        if ($chosen !== null && $chosen !== '') {
            abort_unless(ctype_digit((string) $chosen), 404);
            $departmentId = (int) $chosen;
            abort_unless(in_array($departmentId, $verifiedDepartments, true), 403, 'এই Department-এ প্রবেশাধিকার নেই।');
            $department = Department::findOrFail($departmentId);
        } elseif (count($verifiedDepartments) === 1) {
            $department = Department::findOrFail($verifiedDepartments[0]);
        }

        $data = [
            'course' => $course,
            'batch' => $ownedBatch,
            'department' => $department,
            'examTitle' => $ownedBatch->name,
        ];

        // Existing shared Blade automatically calls StudentUpcomingExam
        // when upcomingExam is null. Prevent that automatic lookup if the
        // department isn't verified: pass a non-null empty value instead.
        // This displays the common dashboard without leaking other exams.
        if ($department === null) {
            $data['upcomingExam'] = [];
        }

        return view('student-exams.index', $data);
    }
}
