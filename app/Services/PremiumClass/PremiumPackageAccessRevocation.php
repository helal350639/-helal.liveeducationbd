<?php
namespace App\Services\PremiumClass;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A revocation is an explicit deny for one student/package pair.
 * Paid order rows remain intact for financial/audit history.
 */
final class PremiumPackageAccessRevocation
{
    public const TABLE = 'lebd_premium_package_access_revocations';

    public static function available(): bool
    {
        return Schema::hasTable(self::TABLE);
    }


    /**
     * Fetch all revoked student/package pairs for reporting in one query.
     * Returns [package_id => [student_id => true]].
     */
    public static function revokedPairs(array $packageIds): array
    {
        $packageIds = array_values(array_unique(array_filter(array_map('intval', $packageIds))));
        if (!$packageIds || !self::available()) return [];
        $map = [];
        foreach (DB::table(self::TABLE)->whereIn('package_id', $packageIds)
            ->whereNotNull('revoked_at')->get(['package_id','student_id']) as $row) {
            $map[(int)$row->package_id][(int)$row->student_id] = true;
        }
        return $map;
    }

    public static function isRevoked(int $studentId, int $packageId): bool
    {
        return $studentId > 0 && $packageId > 0 && self::available()
            && DB::table(self::TABLE)->where('student_id',$studentId)
                ->where('package_id',$packageId)->whereNotNull('revoked_at')->exists();
    }

    public static function revoke(int $studentId, int $packageId, ?int $adminId = null): void
    {
        if (!self::available()) {
            throw new \RuntimeException('Premium package revocation SQL is not installed.');
        }
        $now = now();
        DB::table(self::TABLE)->updateOrInsert(
            ['student_id'=>$studentId,'package_id'=>$packageId],
            ['revoked_at'=>$now,'revoked_by'=>$adminId,'updated_at'=>$now,'created_at'=>$now]
        );
    }

    public static function restore(int $studentId, int $packageId): void
    {
        if (!self::available()) return;
        DB::table(self::TABLE)->where('student_id',$studentId)->where('package_id',$packageId)
            ->update(['revoked_at'=>null,'revoked_by'=>null,'updated_at'=>now()]);
    }
}
