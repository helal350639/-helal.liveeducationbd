<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `lebd_mcq_access_settings` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('lebd_mcq_access_settings')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `lebd_mcq_access_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `free_daily_reading` int(10) UNSIGNED NOT NULL DEFAULT 20,
  `free_monthly_quiz` int(10) UNSIGNED NOT NULL DEFAULT 3,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_mcq_access_settings`
  ADD PRIMARY KEY (`id`);
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
