<?php
namespace App\Services\RoutineAutoQuestion;

use Illuminate\Support\Facades\DB;

/** Read Routine-selected departments; batch synchronization only adds valid relations. */
class RoutineBatchDepartmentSync
{
    private function assertRoutine(int $routineId, int $courseId, int $batchId, bool $published = false): void
    {
        $valid = DB::table('routines as r')
            ->join('batches as b', function ($join) {
                $join->on('b.id', '=', 'r.batch_id')->on('b.course_id', '=', 'r.course_id');
            })
            ->where('r.id', $routineId)->where('r.course_id', $courseId)
            ->where('r.batch_id', $batchId)
            ->when($published, fn ($q) => $q->where('r.status', 'Published'))->exists();
        abort_unless($valid, 404);
    }

    /** Map MCQ selection column IDs to real department IDs (they are not interchangeable). */
    private function selectedDepartmentIds(int $routineId, ?int $itemId = null)
    {
        $columns = DB::table('routine_department_columns')->where('routine_id', $routineId)
            ->get(['id', 'department_id']);
        $columnMap = $columns->pluck('department_id', 'id')->all();
        $knownIds = $columns->pluck('department_id')->map(fn ($id) => (int)$id)->all();

        return DB::table('routine_mcq_selections as s')
            ->join('routine_item_rows as i', 'i.id', '=', 's.routine_item_row_id')
            ->where('i.routine_id', $routineId)->where('s.column_kind', 'department')
            ->when($itemId !== null, fn ($q) => $q->where('s.routine_item_row_id', $itemId))
            ->pluck('s.column_id')
            ->map(function ($id) use ($columnMap, $knownIds) {
                $savedId = (int)$id;
                // RoutineItemCtrl saves real department IDs as selection column_id.
                return in_array($savedId, $knownIds, true)
                    ? $savedId : (int)($columnMap[$savedId] ?? 0);
            })
            ->filter(fn ($id) => $id > 0)->unique()->values();
    }

    public function departmentsForRoutine(int $routineId, int $courseId, int $batchId)
    {
        $this->assertRoutine($routineId, $courseId, $batchId);
        $ids = DB::table('batch_department')->where('batch_id', $batchId)->pluck('department_id')
            ->merge(DB::table('routine_department_columns')->where('routine_id', $routineId)->pluck('department_id'))
            ->merge(DB::table('routine_department_rows as dr')
                ->join('routine_item_rows as i', 'i.id', '=', 'dr.item_id')
                ->where('i.routine_id', $routineId)->pluck('dr.department_id'))
            ->merge($this->selectedDepartmentIds($routineId))
            ->map(fn ($id) => (int)$id)->filter(fn ($id) => $id > 0)->unique()->values()->all();

        return DB::table('departments')->whereIn('id', $ids)
            ->select('id', 'name')->orderBy('name')->get();
    }

    /** Only this exam's departments with saved MCQ selection; no empty legacy rows. */
    public function departmentsForExam(int $routineId, int $courseId, int $batchId, string $examName)
    {
        $this->assertRoutine($routineId, $courseId, $batchId, true);
        $matches = static fn ($name) => mb_strtolower(trim((string)$name)) === mb_strtolower(trim($examName));
        $derivedType = preg_match('/^(revision|revition|model)/i', trim($examName)) === 1;

        if ($derivedType) {
            $item = DB::table('routine_derived_exam_items')->where('routine_id', $routineId)
                ->get(['id', 'exam_name'])->first(fn ($row) => $matches($row->exam_name));
            abort_unless($item, 404);
            $sourceIds = DB::table('routine_derived_exam_sources')
                ->where('derived_item_id', $item->id)
                ->where('column_kind', 'department')
                ->get(['source_item_id', 'column_id']);
            $columnMap = DB::table('routine_department_columns')->where('routine_id', $routineId)
                ->pluck('department_id', 'id')->all();
            $ids = $sourceIds->map(function ($link) use ($routineId, $columnMap) {
                $selected = $this->selectedDepartmentIds($routineId, (int)$link->source_item_id);
                $sourceColumnId = (int)$link->column_id;
                // Source links can identify a real department or a column record.
                $departmentId = $selected->contains($sourceColumnId)
                    ? $sourceColumnId : (int)($columnMap[$sourceColumnId] ?? 0);
                return $departmentId && $selected->contains($departmentId) ? $departmentId : 0;
            });
        } else {
            $item = DB::table('routine_item_rows')->where('routine_id', $routineId)
                ->get(['id', 'paper_name'])->first(fn ($row) => $matches($row->paper_name));
            abort_unless($item, 404);
            $ids = $this->selectedDepartmentIds($routineId, (int)$item->id);
        }

        // Match the Department list to the exact filters rendered by Auto Add Questions.
        // Step 4Z may have saved older filters as Routine row text rather than
        // routine_mcq_selections. RoutineQuestionPoolService already reads both.
        // This is read-only and does not add unrelated batch departments.
        $exam = (object) ['routine_id' => $routineId, 'exam_name' => $examName];
        $poolRows = app(RoutineQuestionPoolService::class)->rows($exam);
        foreach ($poolRows as $poolRow) {
            if (($poolRow['scope'] ?? '') !== 'department') continue;
            $poolDepartmentId = (int) ($poolRow['department_id'] ?? 0);
            if ($poolDepartmentId > 0) $ids->push($poolDepartmentId);
        }

        $ids = $ids->map(fn ($id) => (int)$id)->filter(fn ($id) => $id > 0)
            ->unique()->values()->all();
        return DB::table('departments')->whereIn('id', $ids)
            ->select('id', 'name')->orderBy('name')->get();
    }

    public function sync(int $routineId): void
    {
        $routine = DB::table('routines as r')
            ->join('batches as b', function ($join) {
                $join->on('b.id', '=', 'r.batch_id')->on('b.course_id', '=', 'r.course_id');
            })
            ->where('r.id', $routineId)->select('r.id', 'r.batch_id')->first();
        if (!$routine) return;

        $ids = DB::table('routine_department_columns')->where('routine_id', $routineId)
            ->pluck('department_id')
            ->merge(DB::table('routine_department_rows as dr')
                ->join('routine_item_rows as i', 'i.id', '=', 'dr.item_id')
                ->where('i.routine_id', $routineId)->pluck('dr.department_id'))
            ->merge($this->selectedDepartmentIds($routineId))
            ->map(fn ($id) => (int)$id)->filter(fn ($id) => $id > 0)->unique()->values();

        foreach ($ids as $id) {
            if (!DB::table('departments')->where('id', $id)->exists()) continue;
            DB::table('batch_department')->insertOrIgnore([
                'batch_id' => $routine->batch_id, 'department_id' => $id,
            ]);
        }
    }
}
