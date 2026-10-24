<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `routine_derived_exam_sources` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('routine_derived_exam_sources')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `routine_derived_exam_sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `derived_item_id` bigint(20) UNSIGNED NOT NULL,
  `column_kind` varchar(20) NOT NULL,
  `column_id` bigint(20) UNSIGNED NOT NULL,
  `source_item_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_derived_exam_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `derived_source_unique` (`derived_item_id`,`column_kind`,`column_id`,`source_item_id`),
  ADD KEY `derived_source_item_idx` (`source_item_id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_derived_exam_sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
