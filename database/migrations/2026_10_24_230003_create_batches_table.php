<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `batches` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('batches')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `batches` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `price` decimal(11,2) DEFAULT NULL,
  `fee` decimal(11,2) DEFAULT NULL,
  `discount` decimal(11,2) DEFAULT NULL,
  `referrer_bonus` decimal(11,2) DEFAULT NULL,
  `reference_discount` decimal(11,2) DEFAULT NULL,
  `selling_price` decimal(11,2) DEFAULT NULL,
  `net_price` decimal(11,2) DEFAULT NULL,
  `extra_fee_note` text DEFAULT NULL,
  `start_at` date DEFAULT NULL,
  `end_at` date DEFAULT NULL,
  `reg_end_at` datetime DEFAULT NULL,
  `offer_end_at` datetime DEFAULT NULL,
  `extra_student` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `web` varchar(20) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `short` longtext DEFAULT NULL,
  `details` longtext DEFAULT NULL,
  `what_is` longtext DEFAULT NULL,
  `learn` longtext DEFAULT NULL,
  `routine` longtext DEFAULT NULL,
  `faq` longtext DEFAULT NULL,
  `refund` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`);
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
