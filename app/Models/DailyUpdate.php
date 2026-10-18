<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A Daily Update question/post managed by the admin panel.
 * The lebd_daily_updates table was created manually in phpMyAdmin.
 */
class DailyUpdate extends Model
{
    use SoftDeletes;

    protected $table = 'lebd_daily_updates';

    protected $fillable = [
        'category_id',
        'title',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation',
        'topic',
        'featured_image',
        'update_date',
        'year',
        'month',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'update_date' => 'date',
        'year' => 'integer',
        'month' => 'integer',
        'published_at' => 'datetime',
        'view_count' => 'integer',
    ];

    /** The category to which this question belongs. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DailyUpdateCategory::class, 'category_id');
    }

    /** Comments on this question; comment model is added in the next step. */
    public function comments(): HasMany
    {
        return $this->hasMany(DailyUpdateComment::class, 'daily_update_id');
    }

    /** Only published content should be visible on the student/public page. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /** Display the most recent updates first. */
    public function scopeLatestUpdates(Builder $query): Builder
    {
        return $query->orderByDesc('update_date')->orderByDesc('id');
    }
}
