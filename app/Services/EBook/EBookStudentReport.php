<?php
namespace App\Services\EBook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class EBookStudentReport {
    public function entries(int $packageId): array {
        $rows=[];
        if(Schema::hasTable('lebd_ebook_payment_orders')) {
            $orders=DB::table('lebd_ebook_payment_orders')->where('package_id',$packageId)->where('status','Complete')->orderByDesc('paid_at')->get();
            foreach($orders as $o){
                $id=(int)$o->student_id;
                $method=$o->gateway_validation_id==='REFERRAL_BALANCE'?'wallet':'ssl';
                $rows[$id]['methods'][$method]=true;
                $rows[$id]['order_id']=$o->id;
                $rows[$id]['purchase_date']=$o->paid_at;
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
        foreach($rows as $id=>&$row){
            $row['student']=$students->get($id);
            $ent=$active->get($id,collect());
            $row['active']=$ent->isNotEmpty() || (isset($row['manual_until']) && $row['manual_until']>now()->toDateTimeString());
            $row['expires_at']=$ent->max('expires_at') ?: ($row['manual_until']??null);
            $row['methods']=$row['methods']??[];
        }unset($row);
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
