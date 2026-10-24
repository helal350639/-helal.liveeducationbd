<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `lebd_recorded_videos` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('lebd_recorded_videos')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `lebd_recorded_videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `provider` varchar(20) NOT NULL,
  `video_url` text NOT NULL,
  `thumbnail_url` varchar(2048) DEFAULT NULL,
  `duration` varchar(40) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_recorded_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lebd_pr_videos_lookup` (`chapter_id`,`is_published`,`sort_order`),
  ADD KEY `lebd_video_subject` (`subject_id`,`chapter_id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_recorded_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
