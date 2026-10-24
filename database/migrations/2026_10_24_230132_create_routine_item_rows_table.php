<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `routine_item_rows` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('routine_item_rows')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `routine_item_rows` (
  `id` int(11) NOT NULL,
  `routine_id` int(11) DEFAULT NULL,
  `paper_name` varchar(255) DEFAULT NULL,
  `exam_at` timestamp NULL DEFAULT NULL,
  `exam_end_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `create_pdf_at` datetime DEFAULT NULL,
  `question_create_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_item_rows`
  ADD PRIMARY KEY (`id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `routine_item_rows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
