<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `sub_filter_connections` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('sub_filter_connections')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `sub_filter_connections` (
  `parent_sub_filter_id` bigint(20) UNSIGNED NOT NULL,
  `child_sub_filter_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `sub_filter_connections`
  ADD PRIMARY KEY (`parent_sub_filter_id`,`child_sub_filter_id`),
  ADD KEY `sfc_child_idx` (`child_sub_filter_id`);
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
