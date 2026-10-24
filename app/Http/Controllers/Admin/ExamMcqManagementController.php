<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ExamMcqManagementController extends Controller
{
    private function kind(string $kind): void
    {
        abort_unless(in_array($kind, ['department','non-department'], true), 404);
    }

    private function definition(string $level): array
    {
        return match ($level) {
            'category' => ['exam_mcq_categories', 'kind'],
            'subject'  => ['exam_mcq_subjects', 'category_id'],
            'chapter'  => ['exam_mcq_chapters', 'subject_id'],
            default => abort(404),
        };
    }

    private function entity(string $kind, string $level, int $id): object
    {
        $this->kind($kind);
        [$table] = $this->definition($level);
        $q = DB::table($table)->where('id', $id);
        if ($level === 'category') $q->where('kind', $kind);
        if ($level === 'subject') $q->whereIn('category_id', DB::table('exam_mcq_categories')->where('kind',$kind)->select('id'));
        if ($level === 'chapter') $q->whereIn('subject_id', DB::table('exam_mcq_subjects')->whereIn('category_id', DB::table('exam_mcq_categories')->where('kind',$kind)->select('id'))->select('id'));
        return $q->first() ?? abort(404);
    }

    public function index(string $kind)
    {
        $this->kind($kind);
        $categories = DB::table('exam_mcq_categories')->where('kind',$kind)->orderBy('sort_order')->orderBy('id')->get();
        $subjects = DB::table('exam_mcq_subjects')->whereIn('category_id',$categories->pluck('id')->all())->orderBy('sort_order')->orderBy('id')->get()->groupBy('category_id');
        $subjectIds = $subjects->flatten(1)->pluck('id')->all();
        $chapters = DB::table('exam_mcq_chapters')->whereIn('subject_id',$subjectIds)->orderBy('sort_order')->orderBy('id')->get()->groupBy('subject_id');
        $linked = DB::table('exam_mcq_question_links')->whereIn('category_id',$categories->pluck('id')->all())->get(['category_id','level','entity_id'])->groupBy(fn($r)=>$r->level.':'.$r->entity_id);
        return view('admin.exam-mcq.manage',compact('kind','categories','subjects','chapters','linked'));
    }

    public function update(Request $request, string $kind, string $level, int $id)
    {
        $entry=$this->entity($kind,$level,$id);
        [$table,$parentColumn]=$this->definition($level);
        $parentValue=$entry->{$parentColumn};
        $data=$request->validate([
            'name'=>['required','string','max:180',Rule::unique($table,'name')->where($parentColumn,$parentValue)->ignore($id)],
            'sort_order'=>'required|integer|min:0|max:999999',
            'status'=>'required|in:Active,Inactive',
        ]);
        DB::table($table)->where('id',$id)->update(array_merge($data,['updated_at'=>now()]));
        return redirect()->back()->with('success',ucfirst($level).' updated successfully.');
    }

    public function destroy(Request $request, string $kind, string $level, int $id)
    {
        $entry=$this->entity($kind,$level,$id);
        [$table]=$this->definition($level);
        $block=null;
        DB::transaction(function () use ($kind,$level,$id,$table,&$block) {
            // No cascading deletions: preserve linked MCQs and routine selections.
            if ($level==='category' && DB::table('exam_mcq_subjects')->where('category_id',$id)->exists()) $block='Category has subjects. Delete or move them first.';
            if ($level==='subject' && DB::table('exam_mcq_chapters')->where('subject_id',$id)->exists()) $block='Subject has chapters. Delete them first.';
            if (!$block && Schema::hasTable('exam_mcq_question_links')) {
                // Use direct level/entity matching; ID ownership already checked above.
                if (DB::table('exam_mcq_question_links')->where('level',$level)->where('entity_id',$id)->exists()) $block='Linked MCQs exist. Unlink them before deleting.';
                if ($level==='category' && DB::table('exam_mcq_question_links')->where('category_id',$id)->exists()) $block='Category has linked MCQs. Unlink them first.';
            }
            if (!$block && Schema::hasTable('routine_mcq_selections')) {
                $column = ['category'=>'category_id','subject'=>'subject_id','chapter'=>'chapter_id'][$level];
                if (DB::table('routine_mcq_selections')->where($column,$id)->exists()) $block='This item is used in a Routine. Remove the Routine selection first.';
            }
            if (!$block) DB::table($table)->where('id',$id)->delete();
        });
        return redirect()->back()->with($block?'error':'success', $block ?: ucfirst($level).' deleted. Main question bank unchanged.');
    }
}
