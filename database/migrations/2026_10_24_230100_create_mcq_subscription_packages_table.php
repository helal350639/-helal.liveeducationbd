<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `mcq_subscription_packages` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('mcq_subscription_packages')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `mcq_subscription_packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `access_type` enum('department','non-department','both') NOT NULL DEFAULT 'department',
  `duration_value` int(10) UNSIGNED NOT NULL,
  `duration_unit` enum('days','months','years') NOT NULL DEFAULT 'months',
  `main_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reading_limit` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL = unlimited in paid tier',
  `quiz_limit` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL = unlimited in paid tier',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `mcq_subscription_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_sort` (`status`,`sort_order`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `mcq_subscription_packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
