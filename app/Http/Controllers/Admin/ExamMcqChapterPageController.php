<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ExamMcqChapterPageController extends Controller
{
    public function show(string $kind, int $categoryId, int $subjectId)
    {
        abort_unless(in_array($kind, ['department', 'non-department'], true), 404);
        $category = DB::table('exam_mcq_categories')->where('id', $categoryId)->where('kind', $kind)->first();
        abort_unless($category, 404);
        $subject = DB::table('exam_mcq_subjects')->where('id', $subjectId)->where('category_id', $categoryId)->first();
        abort_unless($subject, 404);
        $chapters = DB::table('exam_mcq_chapters')->where('subject_id', $subjectId)
            ->orderBy('sort_order')->orderBy('id')->get();
        $chapterCounts = DB::table('exam_mcq_question_links')
            ->where('category_id', $categoryId)->where('level', 'chapter')
            ->whereIn('entity_id', $chapters->pluck('id')->all())
            ->select('entity_id', DB::raw('COUNT(*) as total'))
            ->groupBy('entity_id')->pluck('total', 'entity_id');
        return view('admin.exam-mcq.chapters', compact('kind','category','subject','chapters','chapterCounts'));
    }
}
