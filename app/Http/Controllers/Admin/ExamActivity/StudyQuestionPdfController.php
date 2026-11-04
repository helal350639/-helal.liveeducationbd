<?php
namespace App\Http\Controllers\Admin\ExamActivity;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only printable study collection. Never reads the generated exam paper selection. */
class StudyQuestionPdfController extends Controller
{
    public function examSection(int $routine, int $item)
    {
        $routineRow = DB::table('lebd_exam_section_routines as r')
            ->join('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id')
            ->leftJoin('lebd_exam_section_subcategories as s', 's.id', '=', 'r.subcategory_id')
            ->where('r.id', $routine)
            ->select('r.*', 'c.name as category_name', 's.name as subcategory_name')->first();
        abort_unless($routineRow, 404);
        $exam = DB::table('lebd_exam_section_routine_items')
            ->where('id', $item)->where('routine_id', $routine)->where('type', 'exam')->first();
        abort_unless($exam, 404);

        $topics = DB::table('lebd_exam_section_routine_item_topics')
            ->where('item_id', $item)->orderBy('id')->get();
        $fallback = false;
        if ($topics->isEmpty()) {
            foreach (['chapter', 'subject', 'department'] as $level) {
                $id = (int) ($exam->{$level.'_id'} ?? 0);
                if ($id > 0) {
                    $topics = collect([(object) [
                        'topic_key' => $level.':'.$id,
                        'question_start' => $exam->question_start,
                        'question_end' => $exam->question_end,
                    ]]);
                    $fallback = true;
                    break;
                }
            }
        }

        $tables = [
            'department' => 'lebd_exam_routine_departments',
            'subject' => 'lebd_exam_routine_subjects',
            'chapter' => 'lebd_exam_routine_chapters',
        ];
        $sections = [];
        $alreadyIncluded = [];
        foreach ($topics as $topic) {
            if (!preg_match('/^(department|subject|chapter):(\d+)$/', (string) $topic->topic_key, $m)) continue;
            $level = $m[1]; $topicId = (int) $m[2];
            $info = DB::table($tables[$level])->where('id', $topicId)->first();
            if (!$info) continue;
            $subjectName = null; $departmentName = null; $chapterName = null;
            if ($level === 'department') $departmentName = $info->name;
            if ($level === 'subject') {
                $subjectName = $info->name;
                $departmentName = DB::table($tables['department'])->where('id', $info->department_id)->value('name');
            }
            if ($level === 'chapter') {
                $chapterName = $info->name;
                $parent = DB::table($tables['subject'])->where('id', $info->subject_id)->first();
                $subjectName = $parent->name ?? null;
                $departmentName = $parent ? DB::table($tables['department'])->where('id', $parent->department_id)->value('name') : null;
            }
            // Range refers to ordinal positions within the ordered linked questions, not global question IDs.
            $linked = DB::table('lebd_exam_routine_question_links as l')
                ->join('questions as q', 'q.id', '=', 'l.question_id')
                ->where('l.topic_level', $level)->where('l.topic_id', $topicId)
                ->where('q.type', 'MCQ')->where('q.status', 'Active')
                ->orderBy('l.id')->get(['q.id', 'q.title', 'q.explanation', 'q.solution', 'q.image']);
            $from = max(1, (int) ($topic->question_start ?: ($fallback ? $exam->question_start : null) ?: 1));
            $to = (int) ($topic->question_end ?: ($fallback ? $exam->question_end : null) ?: $linked->count());
            $to = min(max($from - 1, $to), $linked->count());
            $selected = $linked->slice($from - 1, max(0, $to - $from + 1))->values();
            $questions = [];
            foreach ($selected as $q) {
                if (isset($alreadyIncluded[$q->id])) continue;
                $alreadyIncluded[$q->id] = true;
                $options = DB::table('mcq_items')->where('question_id', $q->id)->orderBy('id')
                    ->get(['item', 'correct_answer', 'image']);
                $choices = [];
                foreach ($options as $idx => $option) {
                    $choices[] = [
                        'letter' => chr(65 + $idx),
                        'text' => trim(strip_tags((string) ($option->item ?? ''))),
                        'correct' => in_array(strtolower(trim((string) $option->correct_answer)), ['1','true','yes'], true),
                        'image' => $this->safeImage($option->image),
                    ];
                }
                $questions[] = [
                    'id' => $q->id,
                    'title' => trim(strip_tags((string) $q->title)),
                    'image' => $this->safeImage($q->image),
                    'choices' => $choices,
                    'explanation' => trim(strip_tags((string) ($q->explanation ?: $q->solution))),
                ];
            }
            $sections[] = [
                'department' => $departmentName, 'subject' => $subjectName,
                'chapter' => $chapterName, 'topic' => $info->name,
                'range' => $from.'–'.$to,
                'linked_count' => $linked->count(), 'questions' => $questions,
            ];
        }
        return view('admin.exam-activity.study-question-pdf', [
            'routine' => $routineRow, 'exam' => $exam, 'sections' => $sections,
            'count' => count($alreadyIncluded),
        ]);
    }

    private function safeImage(?string $path): ?string
    {
        if (!$path) return null;
        $path = trim($path);
        if ($path === '' || preg_match('/^(https?:)?\/\//i', $path)) return null;
        if (str_contains($path, '..') || str_contains($path, '\\')) return null;
        return asset(ltrim($path, '/'));
    }
}
