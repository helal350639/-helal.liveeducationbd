<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `complains` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('complains')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `complains` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(18) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `department_id` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `solution` longtext DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `web` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `complains`
  ADD PRIMARY KEY (`id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `complains`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
