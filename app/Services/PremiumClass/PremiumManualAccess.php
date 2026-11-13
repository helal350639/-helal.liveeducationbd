<?php
namespace App\Services\PremiumClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
final class PremiumManualAccess {
    public const ORDER_OFFSET = 1000000000000;
    public static function available(): bool {return Schema::hasTable('lebd_premium_manual_grants');}
    public static function active(int $studentId,int $packageId): bool {
        return self::available() && !PremiumPackageAccessRevocation::isRevoked($studentId,$packageId)
            && DB::table('lebd_premium_manual_grants')->where('student_id',$studentId)->where('package_id',$packageId)
            ->where('starts_at','<=',now())->where('ends_at','>',now())->exists();
    }
    public static function hasCategory(int $studentId,string $group,string $topic): bool {
        if(!self::available()) return false;
        $rows=DB::table('lebd_premium_manual_grants')->where('student_id',$studentId)->where('starts_at','<=',now())->where('ends_at','>',now())
            ->when(PremiumPackageAccessRevocation::available(), function($q) use ($studentId) {
                $q->whereNotExists(function($sub) use ($studentId) {
                    $sub->selectRaw('1')->from('lebd_premium_package_access_revocations as rv')
                        ->whereColumn('rv.package_id','lebd_premium_manual_grants.package_id')
                        ->where('rv.student_id',$studentId)->whereNotNull('rv.revoked_at');
                });
            })->get();
        foreach($rows as $r){
            $field=$group==='department'?'department_categories':($group==='non-department'?'non_department_categories':null);
            if(!$field)continue;
            $cats=json_decode((string)$r->{$field},true);
            if(is_array($cats) && in_array($topic,$cats,true))return true;
        }
        return false;
    }
    public static function paid(int $studentId,int $packageId): bool {
        return Schema::hasTable('lebd_premium_class_orders') && !PremiumPackageAccessRevocation::isRevoked($studentId,$packageId)
            && DB::table('lebd_premium_class_orders')->where('student_id',$studentId)->where('package_id',$packageId)
            ->where('status','paid')->where('expires_at','>',now())->exists();
    }
    public static function owned(int $studentId,int $packageId): bool {return self::active($studentId,$packageId) || self::paid($studentId,$packageId);}
    public static function virtualOrder(int $studentId,int $virtualId): ?object {
        if(!self::available() || $virtualId < self::ORDER_OFFSET)return null;
        $grantId=$virtualId-self::ORDER_OFFSET;
        $r=DB::table('lebd_premium_manual_grants')->where('id',$grantId)->where('student_id',$studentId)
            ->where('starts_at','<=',now())->where('ends_at','>',now())->first();
        if(!$r || PremiumPackageAccessRevocation::isRevoked($studentId,(int)$r->package_id))return null;
        $p=DB::table('lebd_premium_class_packages')->where('id',$r->package_id)->first();
        return (object)[
            'id'=>$virtualId,'student_id'=>$studentId,'package_id'=>$r->package_id,'package_name'=>$p->name??'Premium Video Package',
            'package_type'=>$r->package_type,'department_categories'=>$r->department_categories,
            'non_department_categories'=>$r->non_department_categories,'expires_at'=>$r->ends_at,
            'status'=>'manual','amount'=>0,'paid_at'=>null,'validity_days'=>0,
        ];
    }
}
