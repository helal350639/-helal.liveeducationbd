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
        $from = Carbon::now('Asia/Dhaka')->startOfMonth()->format('Y-m-d H:i:s');
        $to = Carbon::now('Asia/Dhaka')->startOfMonth()->addMonth()->format('Y-m-d H:i:s');
        return DB::table('lebd_exam_section_student_attempts')->where('student_id',$studentId)
            ->where('started_at','>=',$from)->where('started_at','<',$to)->count();
    }

    public static function premium(int $studentId, int $categoryId, ?int $subcategoryId): bool
    {
        if (!Schema::hasTable('lebd_exam_section_student_package_access')) return false;
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

    public static function canStart(int $studentId, int $categoryId, ?int $subcategoryId): bool
    {
        return self::used($studentId) < 3 || self::premium($studentId,$categoryId,$subcategoryId);
    }
}
