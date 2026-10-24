<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `daily_exams` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('daily_exams')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `daily_exams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `header` varchar(255) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `exam_title` varchar(255) DEFAULT NULL,
  `question_limit` varchar(255) DEFAULT NULL,
  `start_time` varchar(255) DEFAULT NULL,
  `finish_time` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `correct_mark` varchar(255) DEFAULT NULL,
  `negative_mark` varchar(255) DEFAULT NULL,
  `attempt` varchar(255) DEFAULT NULL,
  `display_question` varchar(255) DEFAULT NULL,
  `random_question` varchar(255) DEFAULT NULL,
  `permission` varchar(255) DEFAULT NULL,
  `paid_exam` varchar(255) DEFAULT NULL,
  `email_result` varchar(255) DEFAULT NULL,
  `max_candidate` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `daily_exams`
  ADD PRIMARY KEY (`id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `daily_exams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
