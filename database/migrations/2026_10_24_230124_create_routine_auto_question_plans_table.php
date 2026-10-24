<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `routine_auto_question_plans` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('routine_auto_question_plans')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `routine_auto_question_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `row_key` varchar(255) NOT NULL,
  `requested` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_auto_question_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_exam_row` (`exam_id`,`row_key`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_auto_question_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
