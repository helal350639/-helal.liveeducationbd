<?php
namespace App\Services\ExamSection;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only package reporting. Never creates grants, orders, or wallet transactions. */
class ExamPackageStudentReport
{
    private const ACCESS='lebd_exam_section_student_package_access';
    private const MANUAL='lebd_exam_section_manual_grants';
    private const ORDERS='lebd_exam_section_package_orders';

    public function blank(): array {return ['total'=>0,'active'=>0,'manual'=>0,'wallet'=>0,'ssl'=>0];}
    public function summary(array $packageIds): array
    {
        $ids=array_values(array_unique(array_map('intval',$packageIds)));
        $out=[];foreach($ids as $id) $out[$id]=$this->blank();
        if(!$ids || !Schema::hasTable(self::ACCESS)) return $out;
        $now=now('Asia/Dhaka')->toDateTimeString();
        $rows=DB::table(self::ACCESS.' as a')->whereIn('a.package_id',$ids)
            ->select('a.package_id','a.student_id')->selectRaw('MAX(CASE WHEN a.status = ? AND a.starts_at <= ? AND a.ends_at > ? THEN 1 ELSE 0 END) as is_active',['active',$now,$now])
            ->groupBy('a.package_id','a.student_id')->get();
        foreach($rows as $row){$id=(int)$row->package_id;$out[$id]['total']++;$out[$id]['active']+=(int)$row->is_active;}
        if(Schema::hasTable(self::MANUAL)) {
            $manual=DB::table(self::MANUAL.' as g')->join(self::ACCESS.' as a','a.id','=','g.access_id')
                ->whereIn('g.package_id',$ids)->where('g.status','active')->where('a.status','active')
                ->where('a.starts_at','<=',$now)->where('a.ends_at','>',$now)
                ->select('g.package_id')->selectRaw('COUNT(DISTINCT g.student_id) as qty')->groupBy('g.package_id')->get();
            foreach($manual as $r)$out[(int)$r->package_id]['manual']=(int)$r->qty;
        }
        if(Schema::hasTable(self::ORDERS)) {
            $paid=DB::table(self::ORDERS.' as o')->join(self::ACCESS.' as a',function($join){
                $join->on('a.student_id','=','o.student_id')->on('a.package_id','=','o.package_id')
                    ->on('a.payment_reference','=','o.transaction_id');
            })->whereIn('o.package_id',$ids)->where('o.status','paid')->where('a.status','active')
                ->where('a.starts_at','<=',$now)->where('a.ends_at','>',$now)
                ->select('o.package_id','o.method')->selectRaw('COUNT(DISTINCT o.student_id) as qty')
                ->groupBy('o.package_id','o.method')->get();
            foreach($paid as $r){$type=strtolower((string)$r->method);$key=$type==='wallet'?'wallet':(in_array($type,['sslcommerz','ssl'],true)?'ssl':null);if($key)$out[(int)$r->package_id][$key]+=(int)$r->qty;}
        }
        return $out;
    }
    public function students(int $packageId,string $search='')
    {
        $now=now('Asia/Dhaka')->toDateTimeString();
        $q=DB::table(self::ACCESS.' as a')->join('students as s','s.id','=','a.student_id')
            ->where('a.package_id',$packageId)
            ->select('s.id as student_id','s.name as student_name','s.email as student_email','s.contact as student_contact','s.studying','s.status as student_status')
            ->selectRaw('MAX(a.ends_at) as expiry')
            ->selectRaw('MAX(CASE WHEN a.status = ? AND a.starts_at <= ? AND a.ends_at > ? THEN 1 ELSE 0 END) as active',['active',$now,$now]);
        if($search!=='') $q->where(function($x)use($search){
            $x->where('s.name','like','%'.$search.'%')->orWhere('s.email','like','%'.$search.'%');
            if(ctype_digit($search))$x->orWhere('s.id',(int)$search);
        });
        $rows=$q->groupBy('s.id','s.name','s.email','s.contact','s.studying','s.status')
            ->orderBy('s.id')->paginate(30)->withQueryString();
        $ids=$rows->getCollection()->pluck('student_id')->all();
        $manual=collect();$orders=collect();
        if($ids && Schema::hasTable(self::MANUAL)) {
            $manual=DB::table(self::MANUAL.' as g')->join(self::ACCESS.' as a','a.id','=','g.access_id')
                ->where('g.package_id',$packageId)->whereIn('g.student_id',$ids)
                ->where('g.status','active')->where('a.status','active')
                ->where('a.starts_at','<=',$now)->where('a.ends_at','>',$now)
                ->select('g.student_id','g.id as grant_id','g.ends_at')->orderByDesc('g.ends_at')->get()->groupBy('student_id');
        }
        if($ids && Schema::hasTable(self::ORDERS)) {
            $orders=DB::table(self::ORDERS.' as o')->join(self::ACCESS.' as a',function($join){
                $join->on('a.student_id','=','o.student_id')->on('a.package_id','=','o.package_id')->on('a.payment_reference','=','o.transaction_id');
            })->where('o.package_id',$packageId)->whereIn('o.student_id',$ids)
                ->where('o.status','paid')->where('a.status','active')->where('a.starts_at','<=',$now)->where('a.ends_at','>',$now)
                ->select('o.student_id','o.method')->orderByDesc('o.id')->get()->groupBy('student_id');
        }
        $rows->getCollection()->transform(function($row)use($manual,$orders){
            $m=$manual->get($row->student_id,collect())->first();
            $paid=$orders->get($row->student_id,collect())->first();
            $row->manual_grant_id=$m?->grant_id;
            $row->access_method=$paid ? (strtolower($paid->method)==='wallet'?'Referral Wallet':'SSLCommerz') : ($m?'manual':'other');
            // Paid access takes precedence when paid and manual overlap.
            $row->premium_status=$row->active?'Active':'Expired/Inactive';
            return $row;
        });
        return $rows;
    }
}
