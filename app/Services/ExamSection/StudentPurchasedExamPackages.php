<?php
namespace App\Services\ExamSection;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

/** Read-only view of a student's already-granted Exam Section packages. */
final class StudentPurchasedExamPackages
{
    public function forCurrentStudent(): array
    {
        $studentId = Auth::guard('student')->id();
        if (!$studentId || !Schema::hasTable('lebd_exam_section_student_package_access')
            || !Schema::hasTable('lebd_exam_section_packages')) {
            return ['packages' => collect(), 'scopes' => collect()];
        }

        $now = now('Asia/Dhaka')->format('Y-m-d H:i:s');
        // Paid and manual assignments share this exact table. Never infer entitlement
        // from an order alone; an expired/revoked grant must not appear active.
        $packages = DB::table('lebd_exam_section_student_package_access as a')
            ->join('lebd_exam_section_packages as p', 'p.id', '=', 'a.package_id')
            ->where('a.student_id', $studentId)
            ->where('a.status', 'active')
            ->where('a.starts_at', '<=', $now)
            ->where('a.ends_at', '>', $now)
            ->select('a.package_id', 'a.starts_at', 'a.ends_at', 'p.name',
                     'p.duration', 'p.duration_unit', 'p.description')
            ->orderByDesc('a.ends_at')->get()
            ->unique('package_id')->values();

        $scopes = collect();
        if ($packages->isNotEmpty()
            && Schema::hasTable('lebd_exam_section_package_scopes')
            && Schema::hasTable('lebd_exam_section_categories')
            && Schema::hasTable('lebd_exam_section_subcategories')) {
            $scopes = DB::table('lebd_exam_section_package_scopes as s')
                ->join('lebd_exam_section_categories as c', 'c.id', '=', 's.category_id')
                ->leftJoin('lebd_exam_section_subcategories as sub', 'sub.id', '=', 's.subcategory_id')
                ->whereIn('s.package_id', $packages->pluck('package_id')->all())
                ->where('c.status', 'Active')
                ->where(function ($q) {
                    $q->whereNull('s.subcategory_id')->orWhere('sub.status', 'Active');
                })
                ->select('s.package_id', 's.category_id', 's.subcategory_id',
                         'c.name as category_name', 'sub.name as subcategory_name')
                ->orderBy('c.name')->orderBy('sub.name')->get()->groupBy('package_id');
        }

        return compact('packages', 'scopes');
    }
}
