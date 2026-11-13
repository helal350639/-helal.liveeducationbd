<?php
namespace App\Services\EBook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class EBookStudentReport {
    public function entries(int $packageId): array {
        $rows=[];
        if(Schema::hasTable('lebd_ebook_payment_orders')) {
            $orders=DB::table('lebd_ebook_payment_orders')->where('package_id',$packageId)
                ->whereIn('status',['Complete','Completed','Paid','Success','Successful'])
                ->orderByDesc('paid_at')->get();
            foreach($orders as $o){
                $id=(int)$o->student_id;
                $method=$o->gateway_validation_id==='REFERRAL_BALANCE'?'wallet':'ssl';
                $rows[$id]['methods'][$method]=true;
                $rows[$id]['order_id']=$o->id;
                $rows[$id]['purchase_date']=$o->paid_at;
                $base=$o->paid_at ?: $o->created_at;
                if($base && (int)$o->duration_value>0){
                    try {
                        $end=\Carbon\Carbon::parse($base,'Asia/Dhaka');
                        switch((string)$o->duration_unit){
                            case 'days': $end->addDays((int)$o->duration_value); break;
                            case 'months': $end->addMonthsNoOverflow((int)$o->duration_value); break;
                            case 'years': $end->addYearsNoOverflow((int)$o->duration_value); break;
                            default: $end=null;
                        }
                        if($end && $end->gt(\Carbon\Carbon::now('Asia/Dhaka'))){
                            $old=$rows[$id]['paid_until']??null;
                            if(!$old || $end->gt(\Carbon\Carbon::parse($old,'Asia/Dhaka')))
                                $rows[$id]['paid_until']=$end->toDateTimeString();
                        }
                    } catch(\Throwable $e) {}
                }
            }
        }
        if(Schema::hasTable('lebd_ebook_manual_grants')) {
            foreach(DB::table('lebd_ebook_manual_grants')->where('package_id',$packageId)->get() as $g){
                $id=(int)$g->student_id;
                $rows[$id]['methods']['manual']=true;
                $rows[$id]['manual_until']=$g->ends_at;
            }
        }
        if (!$rows) return [];
        $ids=array_keys($rows);
        $students=DB::table('students')->whereIn('id',$ids)->get()->keyBy('id');
        $active=DB::table('lebd_ebook_entitlements')->whereIn('user_id',$ids)->where('status','Active')
            ->where('expires_at','>',now())->where('package_id',$packageId)->get()->groupBy('user_id');
        $revokedStudents = Schema::hasTable('lebd_ebook_access_revocations')
            ? DB::table('lebd_ebook_access_revocations')->where('package_id',$packageId)->pluck('student_id')->map(fn($v)=>(int)$v)->all()
            : [];
        foreach($rows as $id=>&$row){
            $row['student']=$students->get($id);
            $ent=$active->get($id,collect());
            $row['revoked']=in_array((int)$id,$revokedStudents,true);
            $row['active']=!$row['revoked'] && (
                $ent->isNotEmpty()
                || (isset($row['manual_until']) && $row['manual_until']>now('Asia/Dhaka')->toDateTimeString())
                || (isset($row['paid_until']) && $row['paid_until']>now('Asia/Dhaka')->toDateTimeString())
            );
            $row['expires_at']=collect([$ent->max('expires_at'),$row['manual_until']??null,$row['paid_until']??null])
                ->filter()->sortDesc()->first();
            $row['methods']=$row['methods']??[];
        }unset($row);

        // Removed package/student pairs must disappear from the active package
        // roster and all unique-student/source counts. Financial order history
        // remains untouched in lebd_ebook_payment_orders.
        foreach ($revokedStudents as $revokedId) {
            unset($rows[(int) $revokedId]);
        }

        return $rows;
    }
    public function summary(array $packages): array {
        $result=[];
        foreach($packages as $id){
            $counts=['total'=>0,'active'=>0,'manual'=>0,'wallet'=>0,'ssl'=>0];
            foreach($this->entries((int)$id) as $r){
                $counts['total']++;
                if($r['active'])$counts['active']++;
                foreach(['manual','wallet','ssl'] as $method)if(isset($r['methods'][$method]))$counts[$method]++;
            }
            $result[(int)$id]=$counts;
        }
        return $result;
    }
}
