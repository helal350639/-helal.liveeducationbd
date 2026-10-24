<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact original MariaDB structure for `student_activity_event_meta` from the provided SQL dump.
        // Existing live tables are left untouched.
        if (Schema::hasTable('student_activity_event_meta')) {
            return;
        }

            DB::statement(<<<'LEBD_SQL_MIGRATION'
CREATE TABLE `student_activity_event_meta` (
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `device_type` varchar(12) NOT NULL DEFAULT 'Unknown',
  `target_path` varchar(240) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LEBD_SQL_MIGRATION);
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `student_activity_event_meta`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `saem_course_event` (`course_id`,`event_id`),
  ADD KEY `saem_batch_event` (`batch_id`,`event_id`),
  ADD KEY `saem_device_event` (`device_type`,`event_id`),
  ADD KEY `saem_student_event` (`student_id`,`event_id`);
LEBD_SQL_MIGRATION);
    }

    public function down(): void
    {
        // Intentionally non-destructive: never drop pre-existing production tables.
        // For a disposable database, manually drop this table if rollback is needed.
    }
};
