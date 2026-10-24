<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `routine_batch_paper_settings` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('routine_batch_paper_settings')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `routine_batch_paper_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `question_limit` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `mcq_format` varchar(32) NOT NULL DEFAULT 'bn',
  `running_minutes` int(10) UNSIGNED NOT NULL DEFAULT 40,
  `correct_mark` decimal(7,2) NOT NULL DEFAULT 1.00,
  `negative_mark` decimal(7,2) NOT NULL DEFAULT 0.00,
  `attempt_limit` varchar(16) NOT NULL DEFAULT '1',
  `random_questions` tinyint(1) NOT NULL DEFAULT 0,
  `question_display` varchar(20) NOT NULL DEFAULT 'all',
  `paid_exam` tinyint(1) NOT NULL DEFAULT 0,
  `email_result` tinyint(1) NOT NULL DEFAULT 0,
  `student_view_result` tinyint(1) NOT NULL DEFAULT 0,
  `header_extra` text DEFAULT NULL,
  `details_extra` text DEFAULT NULL,
  `banner_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_batch_paper_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `routine_batch_paper_settings_batch_id_unique` (`batch_id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_batch_paper_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
