<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `routine_mcq_selections` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('routine_mcq_selections')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `routine_mcq_selections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `routine_item_row_id` bigint(20) UNSIGNED NOT NULL,
  `column_kind` varchar(16) NOT NULL,
  `column_id` bigint(20) UNSIGNED NOT NULL,
  `mcq_kind` varchar(20) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `chapter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `range_start` int(10) UNSIGNED DEFAULT NULL,
  `range_end` int(10) UNSIGNED DEFAULT NULL,
  `range_from` int(10) UNSIGNED DEFAULT NULL,
  `range_to` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_mcq_selections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `routine_mcq_item_column_idx` (`routine_item_row_id`,`column_kind`,`column_id`),
  ADD KEY `routine_mcq_source_idx` (`mcq_kind`,`category_id`,`subject_id`,`chapter_id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_mcq_selections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
