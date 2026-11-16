<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only Exam Section progress, grouped by active category and sub-category. */
final class StudentLearningProgress
{
    public static function build($student): array
    {
        if (!$student) return [];

        foreach ([
            'lebd_exam_section_categories',
            'lebd_exam_section_routines',
            'lebd_exam_section_routine_items',
            'lebd_exam_section_student_attempts',
        ] as $table) {
            if (!Schema::hasTable($table)) return [];
        }

        $studentId = (int) $student->id;
        $hasSubcategories = Schema::hasTable('lebd_exam_section_subcategories');

        $categories = DB::table('lebd_exam_section_categories')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
            ->orderBy('sort_order')->orderBy('id')
            ->get(['id', 'name']);

        if ($categories->isEmpty()) return [];

        $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();
        $subcategories = $hasSubcategories
            ? DB::table('lebd_exam_section_subcategories')
                ->whereIn('category_id', $categoryIds)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
                ->orderBy('sort_order')->orderBy('id')
                ->get(['id', 'category_id', 'name'])
            : collect();

        // Keep every active category visible, including categories with no exams yet.
        // The category row is an aggregate of its root and sub-category exams.
        $scopes = [];
        foreach ($categories as $category) {
            $categoryId = (int) $category->id;
            $scopes[] = [
                'key' => $categoryId . ':category',
                'category_id' => $categoryId,
                'subcategory_id' => null,
                'kind' => 'category',
                'name' => (string) $category->name,
            ];
        }
        foreach ($subcategories as $subcategory) {
            $category = $categories->firstWhere('id', $subcategory->category_id);
            if (!$category) continue;
            $scopes[] = [
                'key' => (int) $subcategory->category_id . ':' . (int) $subcategory->id,
                'category_id' => (int) $subcategory->category_id,
                'subcategory_id' => (int) $subcategory->id,
                'kind' => 'subcategory',
                'name' => (string) $category->name . ' › ' . (string) $subcategory->name,
            ];
        }

        // Count only published routines and exam-type routine items, matching the
        // student-facing Exam Section pages. Attempts are student-specific.
        $routineQuery = DB::table('lebd_exam_section_routines as r')
            ->join('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id')
            ->whereIn('r.category_id', $categoryIds)
            ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
            ->whereRaw('LOWER(TRIM(c.status)) = ?', ['active']);

        if ($hasSubcategories) {
            $routineQuery->leftJoin('lebd_exam_section_subcategories as s', 's.id', '=', 'r.subcategory_id')
                ->where(function ($query) {
                    $query->whereNull('r.subcategory_id')
                        ->orWhereRaw('LOWER(TRIM(s.status)) = ?', ['active']);
                });
        }

        $routineIds = $routineQuery->pluck('r.id')->map(fn ($id) => (int) $id)->all();
        $itemsByCategory = [];
        $itemsBySubcategory = [];

        if ($routineIds) {
            $rows = DB::table('lebd_exam_section_routine_items as i')
                ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
                ->leftJoin('lebd_exam_section_student_attempts as a', function ($join) use ($studentId) {
                    $join->on('a.routine_item_id', '=', 'i.id')
                        ->where('a.student_id', '=', $studentId)
                        ->whereNotNull('a.submitted_at');
                })
                ->whereIn('r.id', $routineIds)
                ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
                ->select([
                    'i.id as item_id', 'i.exam_name', 'i.exam_start_at',
                    'r.category_id', 'r.subcategory_id', 'a.submitted_at',
                ])
                ->orderBy('i.exam_start_at')->orderBy('i.id')->orderByDesc('a.submitted_at')
                ->get();

            foreach ($rows as $row) {
                $itemId = (int) $row->item_id;
                $categoryId = (int) $row->category_id;
                $subcategoryId = $row->subcategory_id === null ? null : (int) $row->subcategory_id;
                $entry = [
                    'title' => $row->exam_name ?: ('Exam #' . $itemId),
                    'date' => $row->exam_start_at
                        ? Carbon::parse($row->exam_start_at, 'Asia/Dhaka')->format('d M Y')
                        : '—',
                    'submitted' => $row->submitted_at !== null,
                    // Marks/results remain hidden until the existing result publication flow confirms them.
                    'score' => null,
                    'correct' => null,
                    'wrong' => null,
                    'result' => 'not_configured',
                ];

                $itemsByCategory[$categoryId][$itemId] ??= $entry;
                if ($subcategoryId !== null) {
                    $itemsBySubcategory[$categoryId . ':' . $subcategoryId][$itemId] ??= $entry;
                }
            }
        }

        $result = [];
        foreach ($scopes as $scope) {
            $examMap = $scope['kind'] === 'category'
                ? ($itemsByCategory[$scope['category_id']] ?? [])
                : ($itemsBySubcategory[$scope['category_id'] . ':' . $scope['subcategory_id']] ?? []);

            $exams = array_values($examMap);
            $total = count($exams);
            $completed = count(array_filter($exams, fn ($exam) => $exam['submitted']));
            $percent = $total ? (int) round(100 * $completed / $total) : 0;

            if ($total === 0) $advice = 'এই বিভাগে এখনো প্রকাশিত পরীক্ষা নেই। নতুন পরীক্ষা এলে নিয়মিত অংশ নিন।';
            elseif ($completed === 0) $advice = 'এখনো কোনো পরীক্ষা জমা দেননি। প্রথম পরীক্ষা দিয়ে শুরু করুন।';
            elseif ($percent < 40) $advice = 'পরীক্ষায় অংশগ্রহণ বাড়ান। সপ্তাহে নিয়মিত লক্ষ্য ঠিক করে অনুশীলন করুন।';
            elseif ($percent < 75) $advice = 'অগ্রগতি হচ্ছে। বাকি পরীক্ষাগুলো পরিকল্পনা করে দিন ও ভুলগুলো রিভিশন করুন।';
            elseif ($percent < 100) $advice = 'ভালো ধারাবাহিকতা। বাকি পরীক্ষাগুলো শেষ করে দুর্বল বিষয়গুলো চর্চা করুন।';
            else $advice = 'নির্ধারিত সব পরীক্ষা দিয়েছেন। ভুল উত্তর বিশ্লেষণ করে নিয়মিত রিভিশন করুন।';

            $result[] = [
                'category_id' => $scope['category_id'],
                'subcategory_id' => $scope['subcategory_id'],
                'kind' => $scope['kind'],
                'name' => $scope['name'],
                'total' => $total,
                'completed' => $completed,
                'remaining' => max(0, $total - $completed),
                'percent' => $percent,
                'exams' => $exams,
                'advice' => $advice,
            ];
        }

        return $result;
    }
}
