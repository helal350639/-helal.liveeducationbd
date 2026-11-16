<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** One free slot is consumed when a distinct exam is STARTED, not upon refresh. */
final class ExamSectionMonthlyAccess
{
    public static function used(int $studentId): int
    {
        foreach ([
            'lebd_exam_section_student_attempts',
            'lebd_exam_section_routine_items',
            'lebd_exam_section_routines',
            'lebd_exam_section_paper_settings',
        ] as $table) {
            if (!Schema::hasTable($table)) return 0;
        }

        $from = Carbon::now('Asia/Dhaka')->startOfMonth()->format('Y-m-d H:i:s');
        $to = Carbon::now('Asia/Dhaka')->startOfMonth()->addMonth()->format('Y-m-d H:i:s');
        $attempts = DB::table('lebd_exam_section_student_attempts as a')
            ->join('lebd_exam_section_routine_items as i', 'i.id', '=', 'a.routine_item_id')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->where('a.student_id', $studentId)
            ->where('a.started_at', '>=', $from)
            ->where('a.started_at', '<', $to)
            ->get(['r.category_id', 'r.subcategory_id']);

        if ($attempts->isEmpty()) return 0;

        $scopeKeys = $attempts->map(fn ($row) => $row->subcategory_id
            ? 'sub:' . (int) $row->subcategory_id
            : 'cat:' . (int) $row->category_id)->unique()->values();
        $settings = DB::table('lebd_exam_section_paper_settings')
            ->whereIn('scope_key', $scopeKeys)->pluck('settings_json', 'scope_key');

        // Only attempts belonging to a Free scope consume the monthly free quota.
        // Paid-package attempts must not reduce the student's three free attempts.
        return $attempts->filter(function ($row) use ($settings) {
            $key = $row->subcategory_id ? 'sub:' . (int) $row->subcategory_id : 'cat:' . (int) $row->category_id;
            $config = json_decode((string) ($settings->get($key) ?? '{}'), true) ?: [];
            return strtolower((string) ($config['paid_exam'] ?? 'free')) !== 'paid';
        })->count();
    }

    public static function premium(int $studentId, int $categoryId, ?int $subcategoryId): bool
    {
        foreach (['lebd_exam_section_student_package_access', 'lebd_exam_section_packages', 'lebd_exam_section_package_scopes'] as $table) {
            if (!Schema::hasTable($table)) return false;
        }
        $now=Carbon::now('Asia/Dhaka')->format('Y-m-d H:i:s');
        $grants=DB::table('lebd_exam_section_student_package_access as a')
            ->join('lebd_exam_section_packages as p','p.id','=','a.package_id')
            ->where('a.student_id',$studentId)->where('a.status','active')
            ->where('a.starts_at','<=',$now)->where('a.ends_at','>',$now)
            ->where('p.status','Active')->pluck('a.package_id');
        if ($grants->isEmpty()) return false;
        return DB::table('lebd_exam_section_package_scopes')->whereIn('package_id',$grants)
            ->where('category_id',$categoryId)
            ->where(function($q) use ($subcategoryId) {
                $q->whereNull('subcategory_id');
                if ($subcategoryId !== null) $q->orWhere('subcategory_id',$subcategoryId);
            })->exists();
    }

    public static function remaining(int $studentId): int
    {
        return max(0, 3 - self::used($studentId));
    }

    public static function canStart(int $studentId, int $categoryId, ?int $subcategoryId): bool
    {
        return self::remaining($studentId) > 0 || self::premium($studentId,$categoryId,$subcategoryId);
    }
}
