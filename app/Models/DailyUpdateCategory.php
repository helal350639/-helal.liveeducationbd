<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Daily Update category (current affairs, monthly updates, etc.).
 * MySQL table is created separately through phpMyAdmin.
 */
class DailyUpdateCategory extends Model
{
    protected $table = 'lebd_daily_update_categories';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Only categories available on the public page. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Order displayed categories the same way in admin and public pages. */
    public function scopeDisplayOrder(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Updates assigned to this category (model added in the next step). */
    public function updates(): HasMany
    {
        return $this->hasMany(DailyUpdate::class, 'category_id');
    }
}
