<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only student learning progress. Does not alter exam/result publication rules. */
final class StudentLearningProgress
{
    public static function build($student): array
    {
        if (!$student) return [];
        foreach (['lebd_exam_section_categories', 'lebd_exam_section_routines',
            'lebd_exam_section_routine_items', 'lebd_exam_section_student_attempts'] as $table) {
            if (!Schema::hasTable($table)) return [];
        }
        $studentId = (int) $student->id;
        $hasSub = Schema::hasTable('lebd_exam_section_subcategories');
        $hasDirect = Schema::hasTable('lebd_exam_section_student_enrollments');
        $hasCourse = Schema::hasTable('course_student');
        $hasBatch = Schema::hasTable('batch_student');

        $direct = $hasDirect
            ? DB::table('lebd_exam_section_student_enrollments')->where('student_id', $studentId)
                ->get(['category_id', 'subcategory_id'])
            : collect();
        $rootCategoryIds = [];
        $directSubIds = [];
        foreach ($direct as $enrollment) {
            if ($enrollment->subcategory_id === null && $enrollment->category_id !== null) {
                $rootCategoryIds[(int) $enrollment->category_id] = true;
            } elseif ($enrollment->subcategory_id !== null) {
                $directSubIds[(int) $enrollment->subcategory_id] = true;
            }
        }

        // An exam section routine may be accessible via direct category/subcategory,
        // a student's registered course, or batch. A submitted attempt also proves
        // this student took that exact exam (even if an enrollment has since ended).
        $routines = DB::table('lebd_exam_section_routines as r')
            ->join('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id')
            ->when($hasSub, fn ($q) => $q->leftJoin('lebd_exam_section_subcategories as s', 's.id', '=', 'r.subcategory_id'))
            ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
            ->whereRaw('LOWER(TRIM(c.status)) = ?', ['active'])
            ->when($hasSub, fn ($q) => $q->where(function ($w) {
                $w->whereNull('r.subcategory_id')->orWhereRaw('LOWER(TRIM(s.status)) = ?', ['active']);
            }))
            ->where(function ($q) use ($studentId, $hasDirect, $hasCourse, $hasBatch) {
                if ($hasDirect) {
                    $q->orWhereExists(function ($sub) use ($studentId) {
                        $sub->selectRaw('1')->from('lebd_exam_section_student_enrollments as e')
                            ->where('e.student_id', $studentId)
                            ->whereColumn('e.category_id', 'r.category_id')
                            ->where(function ($w) {
                                $w->whereNull('e.subcategory_id')
                                    ->orWhereColumn('e.subcategory_id', 'r.subcategory_id');
                            });
                    });
                }
                if ($hasCourse) {
                    $q->orWhereExists(function ($sub) use ($studentId) {
                        $sub->selectRaw('1')->from('course_student as cs')
                            ->where('cs.student_id', $studentId)
                            ->whereColumn('cs.course_id', 'r.course_id');
                    });
                }
                if ($hasBatch) {
                    $q->orWhereExists(function ($sub) use ($studentId) {
                        $sub->selectRaw('1')->from('batch_student as bs')
                            ->where('bs.student_id', $studentId)
                            ->whereColumn('bs.batch_id', 'r.batch_id');
                    });
                }
                $q->orWhereExists(function ($sub) use ($studentId) {
                    $sub->selectRaw('1')->from('lebd_exam_section_routine_items as i')
                        ->join('lebd_exam_section_student_attempts as a', 'a.routine_item_id', '=', 'i.id')
                        ->whereColumn('i.routine_id', 'r.id')
                        ->where('a.student_id', $studentId)
                        ->whereNotNull('a.submitted_at');
                });
            })
            ->select(['r.id', 'r.category_id', 'r.subcategory_id'])
            ->get();

        $accessibleRoutineIds = $routines->pluck('id')->map(fn ($v) => (int) $v)->all();
        $scopeKeys = [];
        foreach ($routines as $routine) {
            $scopeKeys[$routine->category_id . ':' . ($routine->subcategory_id ?? 'root')] = [
                'category_id' => (int) $routine->category_id,
                'subcategory_id' => $routine->subcategory_id === null ? null : (int) $routine->subcategory_id,
            ];
        }
        // Keep explicitly enrolled categories visible even before the first routine.
        foreach ($rootCategoryIds as $categoryId => $_) {
            $scopeKeys[$categoryId . ':root'] = ['category_id' => $categoryId, 'subcategory_id' => null];
        }
        if ($hasSub && $directSubIds) {
            $knownSubs = DB::table('lebd_exam_section_subcategories')
                ->whereIn('id', array_keys($directSubIds))->get(['id', 'category_id']);
            foreach ($knownSubs as $sub) {
                $scopeKeys[$sub->category_id . ':' . $sub->id] = [
                    'category_id' => (int) $sub->category_id, 'subcategory_id' => (int) $sub->id,
                ];
            }
        }
        if (!$scopeKeys) return [];

        $categories = DB::table('lebd_exam_section_categories')
            ->whereIn('id', array_values(array_unique(array_column(array_values($scopeKeys), 'category_id'))))
            ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
            ->orderBy('sort_order')->orderBy('id')->get(['id', 'name'])->keyBy('id');
        $subs = $hasSub ? DB::table('lebd_exam_section_subcategories')
            ->whereIn('id', array_values(array_unique(array_filter(array_column(array_values($scopeKeys), 'subcategory_id')))))
            ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
            ->orderBy('sort_order')->orderBy('id')->get(['id', 'category_id', 'name'])->keyBy('id') : collect();

        $result = [];
        foreach ($scopeKeys as $scope) {
            $categoryId = $scope['category_id'];
            $subId = $scope['subcategory_id'];
            if (!isset($categories[$categoryId]) || ($subId !== null && !isset($subs[$subId]))) continue;
            $title = $categories[$categoryId]->name;
            if ($subId !== null) $title .= ' › ' . $subs[$subId]->name;

            // Never count unapproved routines: use only student-authorized routine IDs.
            $rows = $accessibleRoutineIds
                ? DB::table('lebd_exam_section_routine_items as i')
                    ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
                    ->leftJoin('lebd_exam_section_student_attempts as a', function ($join) use ($studentId) {
                        $join->on('a.routine_item_id', '=', 'i.id')
                            ->where('a.student_id', '=', $studentId)
                            ->whereNotNull('a.submitted_at');
                    })
                    ->whereIn('r.id', $accessibleRoutineIds)
                    ->where('r.category_id', $categoryId)
                    ->when($subId !== null,
                        fn ($q) => $q->where('r.subcategory_id', $subId),
                        fn ($q) => $q->whereNull('r.subcategory_id'))
                    ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
                    ->select(['i.id as item_id', 'i.exam_name', 'i.exam_start_at',
                        'a.id as attempt_id', 'a.submitted_at'])
                    ->orderBy('i.exam_start_at')->orderBy('i.id')->orderByDesc('a.submitted_at')
                    ->get()
                : collect();
            $exams = [];
            foreach ($rows as $row) {
                $itemId = (int) $row->item_id;
                if (isset($exams[$itemId])) continue;
                $submitted = $row->submitted_at !== null;
                $exams[$itemId] = [
                    'title' => $row->exam_name ?: ('Exam #' . $itemId),
                    'date' => $row->exam_start_at
                        ? Carbon::parse($row->exam_start_at, 'Asia/Dhaka')->format('d M Y') : '—',
                    'submitted' => $submitted,
                    // Publication is not proven; never disclose marks prematurely.
                    'score' => null,
                    'correct' => null,
                    'wrong' => null,
                    'result' => 'not_configured',
                ];
            }
            $total = count($exams);
            $completed = count(array_filter($exams, fn ($e) => $e['submitted']));
            $percent = $total ? (int) round(100 * $completed / $total) : 0;
            if ($total === 0) $advice = 'এই বিভাগে এখনো প্রকাশিত পরীক্ষা নেই। নতুন পরীক্ষা এলে নিয়মিত অংশ নিন।';
            elseif ($completed === 0) $advice = 'এখনো কোনো পরীক্ষা জমা দেননি। প্রথম পরীক্ষা দিয়ে শুরু করুন।';
            elseif ($percent < 40) $advice = 'পরীক্ষায় অংশগ্রহণ বাড়ান। সপ্তাহে নিয়মিত লক্ষ্য ঠিক করে অনুশীলন করুন।';
            elseif ($percent < 75) $advice = 'অগ্রগতি হচ্ছে। বাকি পরীক্ষাগুলো পরিকল্পনা করে দিন ও ভুলগুলো রিভিশন করুন।';
            elseif ($percent < 100) $advice = 'ভালো ধারাবাহিকতা। বাকি পরীক্ষাগুলো শেষ করে দুর্বল বিষয়গুলো চর্চা করুন।';
            else $advice = 'নির্ধারিত সব পরীক্ষা দিয়েছেন। ভুল উত্তর বিশ্লেষণ করে নিয়মিত রিভিশন করুন।';
            $result[] = $scope + [
                'name' => $title, 'kind' => $subId === null ? 'category' : 'subcategory',
                'total' => $total, 'completed' => $completed, 'percent' => $percent,
                'exams' => array_values($exams), 'advice' => $advice,
            ];
        }
        return $result;
    }
}
