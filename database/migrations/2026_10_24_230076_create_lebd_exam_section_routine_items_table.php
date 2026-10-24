<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `lebd_exam_section_routine_items` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('lebd_exam_section_routine_items')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `lebd_exam_section_routine_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `routine_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'exam',
  `exam_name` varchar(190) NOT NULL,
  `exam_start_at` datetime DEFAULT NULL,
  `exam_end_at` datetime DEFAULT NULL,
  `pdf_create_at` datetime DEFAULT NULL,
  `question_create_at` datetime DEFAULT NULL,
  `topic_kind` varchar(20) NOT NULL DEFAULT 'department',
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `chapter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_start` int(10) UNSIGNED DEFAULT NULL,
  `question_end` int(10) UNSIGNED DEFAULT NULL,
  `source_exam_ids` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_exam_section_routine_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `routine_id` (`routine_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `chapter_id` (`chapter_id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_exam_section_routine_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
