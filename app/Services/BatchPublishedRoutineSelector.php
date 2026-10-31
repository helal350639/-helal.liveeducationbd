<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only selection, scoped to one purchased batch/course/department.
 * Never returns a routine of a different batch, course or department.
 */
final class BatchPublishedRoutineSelector
{
    public static function find(int $courseId, int $batchId, int $departmentId): ?object
    {
        if (!$courseId || !$batchId || !$departmentId || !Schema::hasTable('routines') || !Schema::hasTable('routine_item_rows')) {
            return null;
        }
        $hasColumns = Schema::hasTable('routine_department_columns');
        $hasRows = Schema::hasTable('routine_department_rows');
        $hasDerived = Schema::hasTable('routine_derived_exam_items');
        $q = DB::table('routines as r')
            ->where('r.course_id', $courseId)
            ->where('r.batch_id', $batchId)
            ->where('r.status', 'Published')
            ->where(function ($filter) use ($departmentId, $hasColumns, $hasRows) {
                $filter->where('r.department_id', $departmentId);
                if ($hasColumns) {
                    $filter->orWhereExists(function ($sub) use ($departmentId) {
                        $sub->selectRaw('1')->from('routine_department_columns as dc')
                            ->whereColumn('dc.routine_id','r.id')->where('dc.department_id',$departmentId);
                    });
                }
                if ($hasRows) {
                    $filter->orWhereExists(function ($sub) use ($departmentId) {
                        $sub->selectRaw('1')->from('routine_item_rows as ri')
                            ->join('routine_department_rows as rd','rd.item_id','=','ri.id')
                            ->whereColumn('ri.routine_id','r.id')->where('rd.department_id',$departmentId);
                    });
                }
                $filter->orWhere(function ($common) use ($hasColumns, $hasRows) {
                    $common->whereNull('r.department_id');
                    if ($hasColumns) $common->whereNotExists(function ($sub) {
                        $sub->selectRaw('1')->from('routine_department_columns as dc')->whereColumn('dc.routine_id','r.id');
                    });
                    if ($hasRows) $common->whereNotExists(function ($sub) {
                        $sub->selectRaw('1')->from('routine_item_rows as ri')
                            ->join('routine_department_rows as rd','rd.item_id','=','ri.id')
                            ->whereColumn('ri.routine_id','r.id');
                    });
                });
            })
            ->where(function ($items) use ($hasDerived) {
                $items->whereExists(function ($sub) {
                    $sub->selectRaw('1')->from('routine_item_rows as ri')->whereColumn('ri.routine_id','r.id');
                });
                if ($hasDerived) $items->orWhereExists(function ($sub) {
                    $sub->selectRaw('1')->from('routine_derived_exam_items as di')->whereColumn('di.routine_id','r.id');
                });
            });
        return $q->orderByDesc('r.id')->first('r.*');
    }
}
