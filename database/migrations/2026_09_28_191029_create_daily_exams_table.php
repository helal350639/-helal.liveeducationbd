<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_exams', function (Blueprint $table) {

            $table->id();
            $table->integer('course_id')->nullable();
            $table->integer('batch_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->string('header')->nullable();
            $table->string('details')->nullable();
            $table->string('banner')->nullable();
            $table->string('exam_title')->nullable();
            $table->string('question_limit')->nullable();
            $table->string('start_time')->nullable();
            $table->string('finish_time')->nullable();
            $table->string('duration')->nullable();
            $table->string('correct_mark')->nullable();
            $table->string('negative_mark')->nullable();
            $table->string('attempt')->nullable();
            $table->string('display_question')->nullable();
            $table->string('random_question')->nullable();
            $table->string('permission')->nullable();
            $table->string('paid_exam')->nullable();
            $table->string('email_result')->nullable();
            $table->string('max_candidate')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('daily_exam_papers');
    }
};