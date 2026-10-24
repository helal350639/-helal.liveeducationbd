<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `circulars` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('circulars')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `circulars` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `start_at` date DEFAULT NULL,
  `end_at` date DEFAULT NULL,
  `published_at` date DEFAULT NULL,
  `admit_to` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `fee` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `pdf_link` varchar(2048) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `notify` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `circulars`
  ADD PRIMARY KEY (`id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `circulars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
