<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class Notice extends Model
{
    protected $fillable = [
        'title', 'details', 'image', 'start_at', 'end_at', 'status',
        'notice_type', 'url', 'open_new_tab', 'show_on_website', 'display_duration',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'open_new_tab' => 'boolean',
        'show_on_website' => 'boolean',
        'display_duration' => 'integer',
    ];

    /** Remove expired notice image files and clear their DB paths. */
    public static function purgeExpiredImages(): int
    {
        if (!Schema::hasTable('notices') || !Schema::hasColumn('notices', 'end_at') || !Schema::hasColumn('notices', 'image')) {
            return 0;
        }

        $now = Carbon::now('Asia/Dhaka');
        $expired = static::query()
            ->whereNotNull('end_at')
            ->where('end_at', '<', $now)
            ->whereNotNull('image')
            ->get();

        $removed = 0;
        foreach ($expired as $notice) {
            $relative = ltrim((string) $notice->image, '/\\');
            // Only delete local files inside the public directory; never follow external URLs.
            if ($relative !== '' && !preg_match('/^https?:\/\//i', $relative)) {
                $path = public_path($relative);
                $publicRoot = realpath(public_path());
                $directory = realpath(dirname($path));
                if ($publicRoot && $directory && ($directory === $publicRoot || strpos($directory, $publicRoot . DIRECTORY_SEPARATOR) === 0) && is_file($path)) {
                    File::delete($path);
                }
            }
            $notice->image = null;
            $notice->save();
            $removed++;
        }

        return $removed;
    }
}
