<?php
namespace App\Services\PremiumClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class PremiumAccess {
 public static function hasAccess(int $studentId,string $group,string $topic):bool {
  if (!$studentId || !Schema::hasTable('lebd_premium_class_orders')) return false;
  return DB::table('lebd_premium_class_orders')->where('student_id',$studentId)->where('status','paid')
   ->where('expires_at','>',now())->get()->contains(function($order)use($group,$topic){
    $type=str_replace('-','_',(string)$order->package_type);
    if($type!=='combined' && $type!==str_replace('-','_',$group))return false;
    $json=$group==='department'?$order->department_categories:$order->non_department_categories;
    $arr=json_decode((string)$json,true);
    return is_array($arr)&&in_array($topic,$arr,true);
   });
 }
}
