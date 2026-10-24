<?php
namespace App\Models\StudentAdvice;
use Illuminate\Database\Eloquent\Model;
class StudentAdvice extends Model {
 protected $table='lebd_student_advices';
 protected $fillable=['name','bb_id','university','position','organization','advice','photo_path','sort_order','status'];
 protected $casts=['sort_order'=>'integer'];
}
