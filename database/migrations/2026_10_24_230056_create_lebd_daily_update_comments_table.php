<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `lebd_daily_update_comments` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('lebd_daily_update_comments')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `lebd_daily_update_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `daily_update_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comment` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `admin_reply` text DEFAULT NULL,
  `replied_by` bigint(20) UNSIGNED DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_daily_update_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lducmt_update_fk` (`daily_update_id`),
  ADD KEY `lducmt_status_created_idx` (`status`,`created_at`),
  ADD KEY `lducmt_student_created_idx` (`student_id`,`created_at`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_daily_update_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
