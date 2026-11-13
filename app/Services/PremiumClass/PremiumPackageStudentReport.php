<?php
namespace App\Services\PremiumClass;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only reporting. A student counts once in Total even if they have several paid orders or a manual grant. */
final class PremiumPackageStudentReport
{
    public const METHODS = ['manual' => 'Manual', 'wallet' => 'Referral Wallet', 'ssl' => 'SSLCommerz', 'other' => 'Other Paid'];

    private static function channel(string $transaction): string
    {
        if (str_starts_with($transaction, 'LEBDR')) return 'wallet';
        if (str_starts_with($transaction, 'LEBDP')) return 'ssl';
        return 'other'; // Do not falsely label legacy/unknown paid orders as SSLCommerz.
    }

    /** @return array<int,array<string,int>> */
    public static function summaries(array $packageIds): array
    {
        $packageIds = array_values(array_unique(array_map('intval', $packageIds)));
        $stats = [];
        foreach ($packageIds as $id) $stats[$id] = ['total'=>0,'active'=>0,'manual'=>0,'wallet'=>0,'ssl'=>0,'other'=>0];
        if (!$packageIds) return $stats;
        $seen = [];
        $active = [];
        $channels = [];
        $revokedPairs = PremiumPackageAccessRevocation::revokedPairs($packageIds);
        if (Schema::hasTable('lebd_premium_class_orders')) {
            $rows = DB::table('lebd_premium_class_orders')->whereIn('package_id', $packageIds)
                ->where('status', 'paid')->select('package_id','student_id','tran_id','expires_at')->cursor();
            foreach ($rows as $row) {
                $package=(int)$row->package_id; $student=(int)$row->student_id;
                // A revoked student/package pair is hidden from every current
                // package count, including historical paid-channel counts.
                // The underlying order remains untouched for audit/financial history.
                if (!empty($revokedPairs[$package][$student])) continue;
                $seen[$package][$student]=true;
                if ($row->expires_at && $row->expires_at > now()->toDateTimeString()) {
                    $active[$package][$student]=true;
                }
                $channels[$package][self::channel((string)$row->tran_id)][$student]=true;
            }
        }
        if (Schema::hasTable('lebd_premium_manual_grants')) {
            $rows=DB::table('lebd_premium_manual_grants')->whereIn('package_id',$packageIds)
                ->select('package_id','student_id','starts_at','ends_at')->cursor();
            foreach ($rows as $row) {
                $package=(int)$row->package_id; $student=(int)$row->student_id;
                if (!empty($revokedPairs[$package][$student])) continue;
                $seen[$package][$student]=true;
                if ($row->starts_at <= now()->toDateTimeString() && $row->ends_at > now()->toDateTimeString()) {
                    $active[$package][$student]=true;
                }
                $channels[$package]['manual'][$student]=true;
            }
        }
        foreach ($packageIds as $id) {
            $stats[$id]['total']=count($seen[$id]??[]);
            $stats[$id]['active']=count($active[$id]??[]);
            foreach (array_keys(self::METHODS) as $method) $stats[$id][$method]=count($channels[$id][$method]??[]);
        }
        return $stats;
    }

    /** One row per student; source methods preserved, latest expiry and current status consolidated. */
    public static function students(int $packageId): array
    {
        $rows=[];
        $now=now()->toDateTimeString();
        $revokedPairs = PremiumPackageAccessRevocation::revokedPairs([$packageId]);
        if (Schema::hasTable('lebd_premium_class_orders')) {
            foreach (DB::table('lebd_premium_class_orders')->where('package_id',$packageId)->where('status','paid')
                ->select('student_id','tran_id','amount','paid_at','expires_at')->orderByDesc('id')->cursor() as $order) {
                $id=(int)$order->student_id;
                // Do not expose a revoked student in the current package report.
                if (!empty($revokedPairs[$packageId][$id])) continue;
                if (!isset($rows[$id])) $rows[$id]=self::emptyRow($id);
                $type=self::channel((string)$order->tran_id);
                $rows[$id]['methods'][$type]=true;
                $rows[$id]['purchases']++;
                $rows[$id]['latest_expires']=max($rows[$id]['latest_expires'],(string)$order->expires_at);
                if ($order->expires_at && $order->expires_at > $now) $rows[$id]['active']=true;
            }
        }
        if (Schema::hasTable('lebd_premium_manual_grants')) {
            foreach (DB::table('lebd_premium_manual_grants')->where('package_id',$packageId)
                ->select('student_id','starts_at','ends_at')->cursor() as $grant) {
                $id=(int)$grant->student_id;
                if (!empty($revokedPairs[$packageId][$id])) continue;
                if (!isset($rows[$id])) $rows[$id]=self::emptyRow($id);
                $rows[$id]['methods']['manual']=true;
                $rows[$id]['latest_expires']=max($rows[$id]['latest_expires'],(string)$grant->ends_at);
                if ($grant->starts_at <= $now && $grant->ends_at > $now) $rows[$id]['active']=true;
            }
        }
        if (!$rows) return [];
        $students=DB::table('students')->whereIn('id',array_keys($rows))
            ->select('id','name','email','contact','studying','status')->get()->keyBy('id');
        foreach ($rows as $id => &$row) {
            $row['student']=$students->get($id);
            $row['revoked']=false;
        }
        unset($row);
        usort($rows,fn($a,$b)=>$b['id']<=>$a['id']);
        return $rows;
    }
    private static function emptyRow(int $id): array
    {
        return ['id'=>$id,'methods'=>[],'latest_expires'=>'','active'=>false,'purchases'=>0,'student'=>null];
    }
}
