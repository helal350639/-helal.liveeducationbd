<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Restore foreign keys only after referenced tables have been created.
        if (Schema::hasTable('exam_mcq_chapters') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'exam_mcq_chapters')->where('CONSTRAINT_NAME', 'emcq_chap_sub')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `exam_mcq_chapters`
  ADD CONSTRAINT `emcq_chap_sub` FOREIGN KEY (`subject_id`) REFERENCES `exam_mcq_subjects` (`id`) ON DELETE CASCADE;
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('exam_mcq_question_links') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'exam_mcq_question_links')->where('CONSTRAINT_NAME', 'emcq_link_cat')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `exam_mcq_question_links`
  ADD CONSTRAINT `emcq_link_cat` FOREIGN KEY (`category_id`) REFERENCES `exam_mcq_categories` (`id`) ON DELETE CASCADE;
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('exam_mcq_subjects') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'exam_mcq_subjects')->where('CONSTRAINT_NAME', 'emcq_sub_cat')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `exam_mcq_subjects`
  ADD CONSTRAINT `emcq_sub_cat` FOREIGN KEY (`category_id`) REFERENCES `exam_mcq_categories` (`id`) ON DELETE CASCADE;
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_daily_updates') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_daily_updates')->where('CONSTRAINT_NAME', 'ldu_category_fk')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_daily_updates`
  ADD CONSTRAINT `ldu_category_fk` FOREIGN KEY (`category_id`) REFERENCES `lebd_daily_update_categories` (`id`);
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_daily_update_comments') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_daily_update_comments')->where('CONSTRAINT_NAME', 'lducmt_update_fk')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_daily_update_comments`
  ADD CONSTRAINT `lducmt_update_fk` FOREIGN KEY (`daily_update_id`) REFERENCES `lebd_daily_updates` (`id`);
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_ebook_blocks') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_ebook_blocks')->where('CONSTRAINT_NAME', 'fk_lebd_eb_chapter')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_ebook_blocks`
  ADD CONSTRAINT `fk_lebd_eb_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `lebd_ebook_chapters` (`id`) ON DELETE CASCADE;
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_ebook_chapters') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_ebook_chapters')->where('CONSTRAINT_NAME', 'fk_lebd_ec_category')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_ebook_chapters`
  ADD CONSTRAINT `fk_lebd_ec_category` FOREIGN KEY (`category_id`) REFERENCES `lebd_ebook_categories` (`id`) ON DELETE CASCADE;
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_exam_routine_chapters') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_exam_routine_chapters')->where('CONSTRAINT_NAME', 'ex143_chapter_subject_fk')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_exam_routine_chapters`
  ADD CONSTRAINT `ex143_chapter_subject_fk` FOREIGN KEY (`subject_id`) REFERENCES `lebd_exam_routine_subjects` (`id`);
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_exam_routine_subjects') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_exam_routine_subjects')->where('CONSTRAINT_NAME', 'ex143_subject_department_fk')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_exam_routine_subjects`
  ADD CONSTRAINT `ex143_subject_department_fk` FOREIGN KEY (`department_id`) REFERENCES `lebd_exam_routine_departments` (`id`);
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_exam_section_routine_items') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_exam_section_routine_items')->where('CONSTRAINT_NAME', 'ex157_routine_fk')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_exam_section_routine_items`
  ADD CONSTRAINT `ex157_routine_fk` FOREIGN KEY (`routine_id`) REFERENCES `lebd_exam_section_routines` (`id`) ON DELETE CASCADE;
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_exam_section_routine_item_topics') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_exam_section_routine_item_topics')->where('CONSTRAINT_NAME', 'ex166_item_fk')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_exam_section_routine_item_topics`
  ADD CONSTRAINT `ex166_item_fk` FOREIGN KEY (`item_id`) REFERENCES `lebd_exam_section_routine_items` (`id`) ON DELETE CASCADE;
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_exam_section_routine_labels') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_exam_section_routine_labels')->where('CONSTRAINT_NAME', 'lebd_ex161_labels_routine_fk')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_exam_section_routine_labels`
  ADD CONSTRAINT `lebd_ex161_labels_routine_fk` FOREIGN KEY (`routine_id`) REFERENCES `lebd_exam_section_routines` (`id`) ON DELETE CASCADE;
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_exam_section_subcategories') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_exam_section_subcategories')->where('CONSTRAINT_NAME', 'exs_subcategory_category_fk')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_exam_section_subcategories`
  ADD CONSTRAINT `exs_subcategory_category_fk` FOREIGN KEY (`category_id`) REFERENCES `lebd_exam_section_categories` (`id`);
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('lebd_recorded_videos') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'lebd_recorded_videos')->where('CONSTRAINT_NAME', 'lebd_recorded_videos_chapter_id_foreign')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `lebd_recorded_videos`
  ADD CONSTRAINT `lebd_recorded_videos_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `lebd_recorded_chapters` (`id`) ON DELETE CASCADE;
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('mcq_subscription_package_categories') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'mcq_subscription_package_categories')->where('CONSTRAINT_NAME', 'mcqspc_category_fk')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `mcq_subscription_package_categories` ADD CONSTRAINT `mcqspc_category_fk` FOREIGN KEY (`category_id`) REFERENCES `job_prep_categories` (`id`);
LEBD_SQL_MIGRATION);
        }

        if (Schema::hasTable('mcq_subscription_package_categories') && ! DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'mcq_subscription_package_categories')->where('CONSTRAINT_NAME', 'mcqspc_package_fk')->exists()) {
            DB::statement(<<<'LEBD_SQL_MIGRATION'
ALTER TABLE `mcq_subscription_package_categories` ADD CONSTRAINT `mcqspc_package_fk` FOREIGN KEY (`package_id`) REFERENCES `mcq_subscription_packages` (`id`) ON DELETE CASCADE;
LEBD_SQL_MIGRATION);
        }
    }

    public function down(): void
    {
        // Production-safe, non-destructive rollback.
    }
};
