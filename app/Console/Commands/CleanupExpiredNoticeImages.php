<?php

namespace App\Console\Commands;

use App\Models\Notice;
use Illuminate\Console\Command;

class CleanupExpiredNoticeImages extends Command
{
    protected $signature = 'lebd:notices-clean-expired-images';
    protected $description = 'Delete image files for expired notices while keeping notice records';

    public function handle(): int
    {
        $count = Notice::purgeExpiredImages();
        $this->info("Expired notice images cleaned: {$count}");
        return self::SUCCESS;
    }
}
