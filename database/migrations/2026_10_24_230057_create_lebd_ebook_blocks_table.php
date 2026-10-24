<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `lebd_ebook_blocks` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('lebd_ebook_blocks')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `lebd_ebook_blocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED NOT NULL,
  `block_type` varchar(30) NOT NULL,
  `question_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `options_json` longtext DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `explanation` longtext DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_ebook_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ebook_blocks_chapter` (`chapter_id`,`sort_order`,`id`),
  ADD KEY `idx_ebook_blocks_question` (`question_id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_ebook_blocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
