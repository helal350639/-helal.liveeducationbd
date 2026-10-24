<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `papers` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('papers')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `papers` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `header` longtext DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `mark` decimal(11,2) DEFAULT NULL,
  `minus` decimal(11,2) DEFAULT NULL,
  `result_view` varchar(255) DEFAULT NULL,
  `exam_limit` varchar(255) DEFAULT NULL,
  `random` varchar(255) DEFAULT NULL,
  `display` varchar(255) DEFAULT NULL,
  `permit` varchar(255) DEFAULT NULL,
  `paid` varchar(255) DEFAULT NULL,
  `max` int(11) DEFAULT NULL,
  `format` text DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `open` timestamp NULL DEFAULT NULL,
  `close` timestamp NULL DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `result_message` text DEFAULT NULL,
  `result_at` datetime DEFAULT NULL,
  `candidate` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `papers`
  ADD PRIMARY KEY (`id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `papers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
