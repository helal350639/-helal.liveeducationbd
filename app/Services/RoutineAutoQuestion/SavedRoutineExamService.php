<?php
namespace App\Services\RoutineAutoQuestion;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Keep exam-folder visibility tied to saved Routine items, not exam dates. */
class SavedRoutineExamService
{
    private function normalized(string $name): string
    {
        // PHP comparison avoids mixed MySQL collations; ignore incidental whitespace.
        return preg_replace('/\s+/u', '', mb_strtolower(trim($name), 'UTF-8'));
    }

    public function isSaved(int $routineId, string $examName): bool
    {
        $name = $this->normalized($examName);
        if (preg_match('/^exam-[0-9]+$/', $name)) {
            return DB::table('routine_item_rows')->where('routine_id', $routineId)
                ->pluck('paper_name')->contains(fn ($v) => $this->normalized((string) $v) === $name);
        }
        $type = str_starts_with($name, 'model-') ? 'model'
            : ((str_starts_with($name, 'revision-') || str_starts_with($name, 'revition-')) ? 'revision' : null);
        if (!$type) return false;
        return DB::table('routine_derived_exam_items')->where('routine_id', $routineId)
            ->get(['exam_name', 'exam_type'])
            ->contains(function ($row) use ($name, $type) {
                $savedType = $this->normalized((string)$row->exam_type);
                return $this->normalized((string)$row->exam_name) === $name
                    && ($type === 'revision' ? in_array($savedType, ['revision', 'revition'], true) : $savedType === 'model');
            });
    }

    public function filter(Builder $query): Builder
    {
        $candidateExams = (clone $query)->reorder()->select('e.id', 'e.name', 'r.id as routine_id')->get();
        if ($candidateExams->isEmpty()) return $query->whereRaw('1 = 0');

        $routineIds = $candidateExams->pluck('routine_id')->unique()->values()->all();
        $main = DB::table('routine_item_rows')->whereIn('routine_id', $routineIds)
            ->get(['routine_id', 'paper_name'])->groupBy('routine_id');
        $derived = DB::table('routine_derived_exam_items')->whereIn('routine_id', $routineIds)
            ->get(['routine_id', 'exam_name', 'exam_type'])->groupBy('routine_id');

        $allowedIds = [];
        foreach ($candidateExams as $exam) {
            $name = $this->normalized((string)$exam->name);
            $routineId = (int)$exam->routine_id;
            if (preg_match('/^exam-[0-9]+$/', $name)) {
                foreach ($main->get($routineId, collect()) as $item) {
                    if ($this->normalized((string)$item->paper_name) === $name) {
                        $allowedIds[] = (int)$exam->id;
                        break;
                    }
                }
                continue;
            }
            $type = str_starts_with($name, 'model-') ? 'model'
                : ((str_starts_with($name, 'revision-') || str_starts_with($name, 'revition-')) ? 'revision' : null);
            if (!$type) continue;
            foreach ($derived->get($routineId, collect()) as $item) {
                $storedType = $this->normalized((string)$item->exam_type);
                $typeMatches = $type === 'model' ? $storedType === 'model'
                    : in_array($storedType, ['revision', 'revition'], true);
                if ($typeMatches && $this->normalized((string)$item->exam_name) === $name) {
                    $allowedIds[] = (int)$exam->id;
                    break;
                }
            }
        }
        return $query->whereIn('e.id', $allowedIds ?: [-1]);
    }
}
