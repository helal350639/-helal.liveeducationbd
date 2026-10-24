<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `lebd_exam_section_student_attempts` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('lebd_exam_section_student_attempts')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `lebd_exam_section_student_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `routine_item_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `started_at` datetime NOT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `answers_json` longtext DEFAULT NULL,
  `correct_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `wrong_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `score` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'started',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_exam_section_student_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `one_attempt_per_student_item` (`routine_item_id`,`student_id`),
  ADD KEY `student_exam_attempt_student` (`student_id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_exam_section_student_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
