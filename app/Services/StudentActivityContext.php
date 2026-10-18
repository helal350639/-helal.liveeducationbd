<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Resolves only VERIFIED URL patterns to concrete batch/course IDs.
 * Never assumes a student's enrollment means they visited that course.
 * Unknown paths are intentionally unattributed.
 */
class StudentActivityContext
{
    public static function fromPath(string $path): array
    {
        $courseId = null;
        $batchId = null;
        $id = null;

        // Both named routes home.course.show and students.course.show use BATCH IDs.
        if (preg_match('~^/(?:home/course|students/course|students/batch-exam)/(\d+)(?:/|$)~', $path, $m)) {
            $batchId = (int) $m[1];
        } elseif (preg_match('~^/(?:course/details|homepage/batch|student/exam-course)/(\d+)(?:/|$)~', $path, $m)) {
            $courseId = (int) $m[1];
        } elseif (preg_match('~^/students/(?:exam-info|exam|check|instruction|result|exam-paper|questions)/(\d+)(?:/|$)~', $path, $m)) {
            $id = (int) $m[1];
        } elseif (preg_match('~^/students/result-design/(\d+)(?:/|$)~', $path, $m)) {
            $id = (int) $m[1];
        }

        if ($id) {
            $paper = DB::table('papers')->where('id', $id)->first(['course_id', 'batch_id']);
            if ($paper) {
                $courseId = $paper->course_id ? (int) $paper->course_id : null;
                $batchId = $paper->batch_id ? (int) $paper->batch_id : null;
            }
        }

        if ($batchId) {
            $foundCourse = DB::table('batches')->where('id', $batchId)->value('course_id');
            if ($foundCourse) {
                $courseId = (int) $foundCourse;
            } else {
                $batchId = null;
            }
        }

        if ($courseId && !DB::table('courses')->where('id', $courseId)->exists()) {
            $courseId = null;
        }

        return ['course_id' => $courseId, 'batch_id' => $batchId];
    }

    public static function device(string $userAgent): string
    {
        if (preg_match('/iPad|Tablet|Android(?!.*Mobile)/i', $userAgent)) {
            return 'Tablet';
        }
        if (preg_match('/Mobile|Android|iPhone|iPod/i', $userAgent)) {
            return 'Mobile';
        }
        return $userAgent === '' ? 'Unknown' : 'Desktop';
    }
}
