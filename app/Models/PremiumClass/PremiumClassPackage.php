<?php
namespace App\Models\PremiumClass;

use Illuminate\Database\Eloquent\Model;

class PremiumClassPackage extends Model
{
    protected $table='lebd_premium_class_packages';
    protected $fillable=[
        'name','package_type','description','department_categories','non_department_categories',
        'duration_value','duration_unit','validity_days','price','discount_amount',
        'final_price','sort_order','is_active',
    ];
    protected $casts=[
        'department_categories'=>'array','non_department_categories'=>'array',
        'price'=>'decimal:2','discount_amount'=>'decimal:2','final_price'=>'decimal:2',
        'validity_days'=>'integer','duration_value'=>'integer','sort_order'=>'integer','is_active'=>'boolean',
    ];
}
