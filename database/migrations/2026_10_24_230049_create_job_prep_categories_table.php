<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `job_prep_categories` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('job_prep_categories')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `job_prep_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kind` enum('department','non-department') NOT NULL,
  `name` varchar(180) NOT NULL,
  `icon` varchar(80) NOT NULL DEFAULT 'folder',
  `icon_color` char(7) NOT NULL DEFAULT '#1677EE',
  `card_color` char(7) NOT NULL DEFAULT '#EDF4FF',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `job_prep_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_jpc_kind_name` (`kind`,`name`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `job_prep_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
