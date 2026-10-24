<?php
namespace App\Services\RoutineAutoQuestion;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class RoutineQuestionPoolService
{
    /** Deterministic question order: link ID ascending; inclusive 1-based range. */
    private function linkedIds(object $selection): array
    {
        $level = $selection->chapter_id ? 'chapter' : ($selection->subject_id ? 'subject' : 'category');
        $entity = $selection->chapter_id ?: ($selection->subject_id ?: $selection->category_id);
        $ids = DB::table('exam_mcq_question_links as l')
            ->join('questions as q', 'q.id', '=', 'l.question_id')
            ->where('l.category_id', $selection->category_id)
            ->where('l.level', $level)->where('l.entity_id', $entity)
            ->where('q.type', 'MCQ')
            ->orderBy('l.id')->pluck('l.question_id')->unique()->values()->all();

        $from = $selection->range_from ?? $selection->range_start ?? null;
        $to = $selection->range_to ?? $selection->range_end ?? null;
        if ($from !== null || $to !== null) {
            if (!$from || !$to || (int)$to < (int)$from) return [];
            return array_slice($ids, max(0, (int)$from - 1), (int)$to - (int)$from + 1);
        }
        return $ids;
    }

    /** Match exam names as saved by Routine, including harmless case/spacing differences. */
    private function sameName($a, $b): bool
    {
        return mb_strtolower(trim((string)$a)) === mb_strtolower(trim((string)$b));
    }

    private function sourceItems(object $exam): Collection
    {
        if (!preg_match('/^(revision|revition|model)/i', trim((string)$exam->exam_name))) {
            $row = DB::table('routine_item_rows')->where('routine_id', $exam->routine_id)
                ->get(['id', 'paper_name'])
                ->first(fn ($item) => $this->sameName($item->paper_name, $exam->exam_name));
            return $row ? collect([(object)['id' => $row->id, 'kind' => null, 'column' => null]]) : collect();
        }

        $derived = DB::table('routine_derived_exam_items')->where('routine_id', $exam->routine_id)
            ->get(['id', 'exam_name'])
            ->first(fn ($item) => $this->sameName($item->exam_name, $exam->exam_name));
        if (!$derived) return collect();
        return DB::table('routine_derived_exam_sources')->where('derived_item_id', $derived->id)
            ->get(['source_item_id as id', 'column_kind as kind', 'column_id as column']);
    }

    /** Parse only the exact MCQ filter paths saved by the Routine form. */
    private function parseSavedFilter(string $line, string $kind, Collection $categories,
        Collection $subjects, Collection $chapters): ?array
    {
        $line = trim(html_entity_decode($line, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (!preg_match('/^(Non-Department|Department)\s*>\s*(.+?)\s*\(Available Questions:\s*\d+\)/iu', $line, $m)) return null;
        if (mb_strtolower($m[1]) !== ($kind === 'department' ? 'department' : 'non-department')) return null;
        $parts = array_map('trim', explode('>', $m[2]));
        if (count($parts) < 1 || count($parts) > 3 || $parts[0] === '') return null;
        $expected = $kind === 'department' ? 'department' : 'non-department';
        $cat = $categories->first(fn($c) => $c->kind === $expected && mb_strtolower(trim($c->name)) === mb_strtolower($parts[0]));
        if (!$cat) return null;
        $sub = null; $chapter = null;
        if (count($parts) > 1) {
            $sub = $subjects->first(fn($s) => (int)$s->category_id === (int)$cat->id && mb_strtolower(trim($s->name)) === mb_strtolower($parts[1]));
            if (!$sub) return null;
        }
        if (count($parts) > 2) {
            $chapter = $chapters->first(fn($h) => (int)$h->subject_id === (int)$sub->id && mb_strtolower(trim($h->name)) === mb_strtolower($parts[2]));
            if (!$chapter) return null;
        }
        $from = $to = null;
        if (preg_match('/Question Range:\s*(\d+)\s*[-–]\s*(\d+)/iu', $line, $r)) {
            $from = (int)$r[1]; $to = (int)$r[2];
            if ($from < 1 || $to < $from) return null;
        }
        return [$cat, $sub, $chapter, $from, $to];
    }

    public function rows(object $exam): array
    {
        $sources = $this->sourceItems($exam);
        $departmentColumns = DB::table('routine_department_columns')
            ->where('routine_id', $exam->routine_id)->get(['id', 'department_id']);
        $departmentMap = $departmentColumns->pluck('department_id', 'id')->all();
        $departmentIds = $departmentColumns->pluck('department_id')->map(fn ($id) => (int)$id)->all();
        $all = [];

        foreach ($sources as $source) {
            $selections = DB::table('routine_mcq_selections as s')
                ->join('exam_mcq_categories as c', 'c.id', '=', 's.category_id')
                ->leftJoin('exam_mcq_subjects as sub', 'sub.id', '=', 's.subject_id')
                ->leftJoin('exam_mcq_chapters as ch', 'ch.id', '=', 's.chapter_id')
                ->where('s.routine_item_row_id', $source->id)
                ->when($source->kind, fn ($q) => $q->where('s.column_kind', $source->kind)->where('s.column_id', $source->column))
                ->select('s.*', 'c.name as category_name', 'sub.name as subject_name', 'ch.name as chapter_name')
                ->orderBy('s.id')->get();

            foreach ($selections as $s) {
                // The saved Routine column is authoritative. Older saved selections can have
                // a different mcq_kind label; never silently discard the selected MCQs.
                $scope = $s->column_kind === 'department' ? 'department' : 'common';
                $deptId = 0;
                if ($scope === 'department') {
                    // RoutineItemCtrl::validatedMcqSelections saves the real department ID,
                    // not the routine_department_columns primary key. Prefer that first.
                    $savedId = (int)$s->column_id;
                    $deptId = in_array($savedId, $departmentIds, true)
                        ? $savedId : (int)($departmentMap[$savedId] ?? 0);
                    if (!$deptId) continue;
                }

                $from = $s->range_from ?? $s->range_start ?? null;
                $to = $s->range_to ?? $s->range_end ?? null;
                $key = $source->id.'|'.$s->column_kind.'|'.$s->column_id.'|'.$scope.'|'.$deptId.'|'.$s->category_id.'|'.($s->subject_id ?: 0)
                    .'|'.($s->chapter_id ?: 0).'|'.($from ?? '').'|'.($to ?? '');
                if (!isset($all[$key])) {
                    $all[$key] = [
                        'key' => $key, 'scope' => $scope, 'department_id' => $deptId,
                        'category' => $s->category_name, 'subject' => $s->subject_name,
                        'chapter' => $s->chapter_name, 'question_ids' => [], 'sources' => [],
                        'routine_column' => $scope === 'common' ? 'Subject '.$s->column_id : 'Department '.$deptId,
                        'range_start' => $from, 'range_end' => $to,
                        'category_id' => (int)$s->category_id,
                        'subject_id' => $s->subject_id ? (int)$s->subject_id : null,
                        'chapter_id' => $s->chapter_id ? (int)$s->chapter_id : null,
                    ];
                }
                $all[$key]['question_ids'] = array_values(array_unique(array_merge(
                    $all[$key]['question_ids'], $this->linkedIds($s)
                )));
                $all[$key]['sources'][] = (int)$s->id;
            }
        }
        // Step 4Z compatibility: older saved Routine rows may contain MCQ filters
        // in their display text even if a corresponding structured selection is missing.
        // Read those specific rows only. Never write to, replace, or infer bank records.
        $categoryByName = DB::table('exam_mcq_categories')->get(['id', 'kind', 'name']);
        $subjectsByName = DB::table('exam_mcq_subjects')->get(['id', 'category_id', 'name']);
        $chaptersByName = DB::table('exam_mcq_chapters')->get(['id', 'subject_id', 'name']);
        foreach ($sources as $source) {
            $subjectRows = DB::table('routine_subject_rows')->where('item_id', $source->id)
                ->get(['subject_id as column_id', 'chapter as filter_text']);
            $departmentRows = DB::table('routine_department_rows')->where('item_id', $source->id)
                ->get(['department_id as column_id', 'details as filter_text']);
            foreach (['subject' => $subjectRows, 'department' => $departmentRows] as $kind => $displayRows) {
                if ($source->kind && $source->kind !== $kind) continue;
                foreach ($displayRows as $display) {
                    if ($source->kind && (int)$source->column !== (int)$display->column_id) continue;
                    $columnId = (int)$display->column_id;
                    if ($kind === 'department' && $columnId < 1) continue;
                    $text = str_replace(['<br>', '<br/>', '<br />', "\r"], ["\n", "\n", "\n", ''], (string)$display->filter_text);
                    foreach (preg_split('/\n/u', strip_tags($text)) as $line) {
                        $parsed = $this->parseSavedFilter($line, $kind, $categoryByName, $subjectsByName, $chaptersByName);
                        if (!$parsed) continue; // Ordinary legacy notes are not MCQ filters.
                        [$category, $subject, $chapter, $from, $to] = $parsed;
                        $scope = $kind === 'department' ? 'department' : 'common';
                        $departmentId = $scope === 'department' ? $columnId : 0;
                        $key = $source->id.'|'.$kind.'|'.$columnId.'|'.$scope.'|'.$departmentId.'|'.$category->id
                            .'|'.($subject->id ?? 0).'|'.($chapter->id ?? 0).'|'.($from ?? '').'|'.($to ?? '');
                        if (isset($all[$key])) continue;
                        $selection = (object)[
                            'category_id' => $category->id,
                            'subject_id' => $subject->id ?? null,
                            'chapter_id' => $chapter->id ?? null,
                            'range_from' => $from, 'range_to' => $to,
                        ];
                        $ids = $this->linkedIds($selection);
                        $all[$key] = [
                            'key' => $key, 'scope' => $scope, 'department_id' => $departmentId,
                            'category' => $category->name, 'subject' => $subject->name ?? null,
                            'chapter' => $chapter->name ?? null, 'question_ids' => $ids, 'sources' => [],
                            'routine_column' => $scope === 'common' ? 'Subject '.$columnId : 'Department '.$departmentId,
                            'range_start' => $from, 'range_end' => $to,
                            'category_id' => (int)$category->id,
                            'subject_id' => isset($subject->id) ? (int)$subject->id : null,
                            'chapter_id' => isset($chapter->id) ? (int)$chapter->id : null,
                        ];
                    }
                }
            }
        }
        foreach ($all as &$row) $row['available'] = count($row['question_ids']);
        unset($row);
        return array_values($all);
    }
}
