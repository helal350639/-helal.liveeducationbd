<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `lebd_home_successes` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('lebd_home_successes')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `lebd_home_successes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('info','photo','feature') NOT NULL DEFAULT 'info',
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_home_successes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_success_order` (`type`,`is_active`,`sort_order`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_home_successes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
