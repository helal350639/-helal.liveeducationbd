<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `lebd_premium_video_complaints` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('lebd_premium_video_complaints')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `lebd_premium_video_complaints` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `video_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `premium_group` varchar(40) NOT NULL DEFAULT '',
  `premium_topic` varchar(100) NOT NULL DEFAULT '',
  `subject_title` varchar(255) DEFAULT NULL,
  `chapter_title` varchar(255) DEFAULT NULL,
  `video_title` varchar(255) NOT NULL,
  `reason` varchar(40) NOT NULL,
  `comment` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_premium_video_complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lebd_vcomplaint_video` (`video_id`),
  ADD KEY `lebd_vcomplaint_student` (`student_id`),
  ADD KEY `lebd_vcomplaint_status` (`status`,`created_at`),
  ADD KEY `lebd_vcomplaint_topic` (`premium_group`,`premium_topic`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_premium_video_complaints`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
