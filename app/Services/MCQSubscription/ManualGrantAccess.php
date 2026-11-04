<?php
namespace App\Services\MCQSubscription;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Integration bridge: call alongside the existing paid-subscription query, never replace it. */
final class ManualGrantAccess
{
    public static function activePackageIds(int $studentId): array
    {
        if ($studentId < 1 || !Schema::hasTable('lebd_mcq_manual_grants')) return [];
        return DB::table('lebd_mcq_manual_grants')
            ->where('student_id',$studentId)->where('status','Active')
            ->where('starts_at','<=',now('Asia/Dhaka'))
            ->where('ends_at','>',now('Asia/Dhaka'))
            ->pluck('package_id')->map(fn($v)=>(int)$v)->all();
    }
    public static function hasActive(int $studentId, int $packageId): bool
    {
        return in_array($packageId, self::activePackageIds($studentId), true);
    }
}
