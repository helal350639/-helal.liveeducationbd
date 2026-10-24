<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Read-only MCQ for Exam source endpoints for Routine Item selectors.
 * Never changes questions or MCQ for Exam records.
 */
class RoutineMcqSourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function options(Request $request)
    {
        $v = Validator::make($request->all(), [
            'kind' => 'required|in:department,non-department',
            'category_id' => 'nullable|integer|min:1',
            'subject_id' => 'nullable|integer|min:1',
            'chapter_id' => 'nullable|integer|min:1',
        ])->validate();

        $kind = $v['kind'];
        $categoryId = (int) ($v['category_id'] ?? 0);
        $subjectId = (int) ($v['subject_id'] ?? 0);
        $chapterId = (int) ($v['chapter_id'] ?? 0);

        if ($chapterId && !$subjectId) {
            return response()->json(['message' => 'Chapter requires a subject.'], 422);
        }
        if ($subjectId && !$categoryId) {
            return response()->json(['message' => 'Subject requires a category.'], 422);
        }

        $categories = DB::table('exam_mcq_categories')
            ->where('kind', $kind)->where('status', 'Active')
            ->orderBy('sort_order')->orderBy('id')->get(['id','name']);

        $subjects = collect();
        $chapters = collect();
        $count = null;
        $level = null;

        if ($categoryId) {
            $category = DB::table('exam_mcq_categories')
                ->where('id', $categoryId)->where('kind', $kind)
                ->where('status', 'Active')->first();
            if (!$category) {
                return response()->json(['message' => 'Invalid or inactive category.'], 422);
            }
            $subjects = DB::table('exam_mcq_subjects')
                ->where('category_id', $categoryId)->where('status', 'Active')
                ->orderBy('sort_order')->orderBy('id')->get(['id','name']);
            $level = 'category';
            $entityId = $categoryId;

            if ($subjectId) {
                $subject = DB::table('exam_mcq_subjects')
                    ->where('category_id', $categoryId)->where('id', $subjectId)
                    ->where('status', 'Active')->first();
                if (!$subject) {
                    return response()->json(['message' => 'Invalid or inactive subject.'], 422);
                }
                $chapters = DB::table('exam_mcq_chapters')
                    ->where('subject_id', $subjectId)->where('status', 'Active')
                    ->orderBy('sort_order')->orderBy('id')->get(['id','name']);
                $level = 'subject';
                $entityId = $subjectId;

                if ($chapterId) {
                    $chapter = DB::table('exam_mcq_chapters')
                        ->where('subject_id', $subjectId)->where('id', $chapterId)
                        ->where('status', 'Active')->first();
                    if (!$chapter) {
                        return response()->json(['message' => 'Invalid or inactive chapter.'], 422);
                    }
                    $level = 'chapter';
                    $entityId = $chapterId;
                }
            }

            // Same exact level/entity linkage semantics as ExamMcqController::questions().
            // DISTINCT protects against accidental duplicated linkage rows.
            $count = DB::table('exam_mcq_question_links as links')
                ->join('questions as q', 'q.id', '=', 'links.question_id')
                ->where('links.category_id', $categoryId)
                ->where('links.level', $level)
                ->where('links.entity_id', $entityId)
                ->where('q.type', 'MCQ')
                ->distinct()->count('q.id');
        }

        return response()->json([
            'kind' => $kind,
            'categories' => $categories,
            'subjects' => $subjects,
            'chapters' => $chapters,
            'selection' => [
                'category_id' => $categoryId ?: null,
                'subject_id' => $subjectId ?: null,
                'chapter_id' => $chapterId ?: null,
                'level' => $level,
                'available_question_count' => $count,
                'count_scope' => 'direct_linked_at_selected_level',
            ],
        ]);
    }
}
