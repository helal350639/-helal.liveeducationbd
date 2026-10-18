<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HomeSuccess extends Model
{
    protected $table='lebd_home_successes';
    protected $fillable=['type','title','description','subtitle','icon','image_path','sort_order','is_active'];
    protected $casts=['is_active'=>'boolean','sort_order'=>'integer'];
}
