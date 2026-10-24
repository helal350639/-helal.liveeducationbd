<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `student_activity_days` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('student_activity_days')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `student_activity_days` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `activity_date` date NOT NULL,
  `last_seen_at` datetime NOT NULL,
  `active_seconds` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `page_views` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `click_count` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `student_activity_days`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `activity_student_day_unique` (`student_id`,`activity_date`),
  ADD KEY `activity_days_date_last_index` (`activity_date`,`last_seen_at`),
  ADD KEY `activity_days_student_last_index` (`student_id`,`last_seen_at`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `student_activity_days`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=983;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
