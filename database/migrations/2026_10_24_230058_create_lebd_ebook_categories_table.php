<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `lebd_ebook_categories` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('lebd_ebook_categories')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `lebd_ebook_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kind` varchar(30) NOT NULL,
  `name` varchar(200) NOT NULL,
  `icon` varchar(80) NOT NULL DEFAULT 'fa-book',
  `icon_color` varchar(20) NOT NULL DEFAULT '#1b72b9',
  `card_color` varchar(20) NOT NULL DEFAULT '#eaf6ff',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(15) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `front_cover` varchar(255) DEFAULT NULL,
  `details_page` varchar(255) DEFAULT NULL,
  `back_cover` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_ebook_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ebook_category_kind` (`kind`,`status`,`sort_order`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_ebook_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
