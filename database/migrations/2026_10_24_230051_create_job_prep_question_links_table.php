<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `job_prep_question_links` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('job_prep_question_links')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `job_prep_question_links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `level` enum('category','subject','chapter') NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `question_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `job_prep_question_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_jpql_level_entity_question` (`level`,`entity_id`,`question_id`),
  ADD KEY `idx_jpql_question` (`question_id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `job_prep_question_links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
