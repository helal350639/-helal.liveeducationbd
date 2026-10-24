<?php

/**
 * Verify real tables, relationships and columns before enabling.
 * Each source MUST be student-scoped; never connect an unscoped exam/result table.
 * For course/batch/category/subcategory: create an authorized student-scoped
 * SQL view (or adapt the feed service to use the actual enrolment/attempt joins).
 * Do not invent fields: see README before setup.
 */
return [
    'upcoming_sources' => [
        // ['table'=>'verified_student_exam_view', 'student_id'=>'student_id',
        //  'start_at'=>'exam_starts_at', 'title'=>'exam_title', 'subtitle'=>'scope_label',
        //  'status'=>'status', 'published_value'=>'published'],
    ],
    // Published, student-owned results from the most recent rolling 24 hours.
    'result_sources' => [
        // ['table'=>'verified_published_student_results_view', 'student_id'=>'student_id',
        //  'published_at'=>'published_at', 'title'=>'exam_title',
        //  'score'=>'score', 'total'=>'max_score', 'rank'=>'rank',
        //  'status'=>'status', 'published_value'=>'published'],
    ],
    // Scheduled publication within next 7 days. This must NOT contain private marks.
    // The 'scheduled_value' is the actual stored status value; verify from your schema.
    'scheduled_result_sources' => [
        // ['table' => 'verified_student_result_schedule_view',
        //  'student_id' => 'student_id', 'scheduled_at' => 'result_publish_at',
        //  'title' => 'exam_title', 'status' => 'publication_status',
        //  'scheduled_value' => 'scheduled'],
    ],
];
