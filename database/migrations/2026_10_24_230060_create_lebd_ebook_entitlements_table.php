<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `lebd_ebook_entitlements` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('lebd_ebook_entitlements')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `lebd_ebook_entitlements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'Active',
  `starts_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_ebook_entitlements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ebook_entitlement` (`user_id`,`category_id`,`status`,`expires_at`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_ebook_entitlements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
