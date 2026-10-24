<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `routine_derived_exam_items` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('routine_derived_exam_items')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `routine_derived_exam_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `routine_id` bigint(20) UNSIGNED NOT NULL,
  `exam_type` varchar(16) NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `exam_at` datetime NOT NULL,
  `exam_end_at` datetime DEFAULT NULL,
  `create_pdf_at` datetime DEFAULT NULL,
  `question_create_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_derived_exam_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `derived_routine_exam_unique` (`routine_id`,`exam_name`),
  ADD KEY `derived_routine_idx` (`routine_id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_derived_exam_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
