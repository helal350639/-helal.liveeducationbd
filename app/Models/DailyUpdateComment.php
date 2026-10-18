<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Student comments on Daily Update questions.
 * Uses the existing phpMyAdmin-created lebd_daily_update_comments table.
 */
class DailyUpdateComment extends Model
{
    use SoftDeletes;

    protected $table = 'lebd_daily_update_comments';

    protected $fillable = [
        'daily_update_id',
        'student_id',
        'comment',
        'status',
        'admin_reply',
        'replied_by',
        'replied_at',
    ];

    protected $casts = [
        'daily_update_id' => 'integer',
        'student_id' => 'integer',
        'replied_by' => 'integer',
        'replied_at' => 'datetime',
    ];

    /** Question/update associated with this comment. */
    public function dailyUpdate(): BelongsTo
    {
        return $this->belongsTo(DailyUpdate::class, 'daily_update_id');
    }

    /** Student who submitted this comment, if still available. */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /** Comments awaiting admin moderation. */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /** Approved comments that may appear on the public page. */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /** Rejected comments remain available to administrators. */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }
}
