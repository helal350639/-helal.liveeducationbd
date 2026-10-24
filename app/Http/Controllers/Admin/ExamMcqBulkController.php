<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamMcqBulkController extends Controller
{
    public function attach(Request $request, string $kind, int $categoryId)
    {
        abort_unless(in_array($kind, ['department', 'non-department'], true), 404);
        $category = DB::table('exam_mcq_categories')->where('id', $categoryId)->where('kind', $kind)->first();
        abort_unless($category, 404);

        $input = $request->validate([
            'mode' => 'required|in:all,page', 'subject_id'=>'nullable|integer|min:0',
            'chapter_id'=>'nullable|integer|min:0', 'page'=>'nullable|integer|min:1',
            'course_id'=>'nullable|integer', 'department_id'=>'nullable|integer',
            'semester_id'=>'nullable|integer', 'filter_subject_id'=>'nullable|integer',
            'filter_chapter_id'=>'nullable|integer','created_by'=>'nullable|integer',
            'title'=>'nullable|string|max:1000', 'start'=>'nullable|date',
            'end'=>'nullable|date', 'filter_ids'=>'nullable|array',
            'filter_ids.*'=>'integer',
        ]);
        $subjectId = (int)($input['subject_id'] ?? 0);
        $chapterId = (int)($input['chapter_id'] ?? 0);
        abort_if($chapterId && !$subjectId, 422, 'Chapter requires subject');
        if ($subjectId) abort_unless(DB::table('exam_mcq_subjects')->where('id',$subjectId)->where('category_id',$categoryId)->exists(),404);
        if ($chapterId) abort_unless(DB::table('exam_mcq_chapters')->where('id',$chapterId)->where('subject_id',$subjectId)->exists(),404);
        $level=$chapterId?'chapter':($subjectId?'subject':'category');
        $entityId=$chapterId ?: ($subjectId ?: $categoryId);

        $query=Question::query()->where('type','MCQ');
        foreach(['course_id'=>'courses','department_id'=>'departments','semester_id'=>'semesters','filter_subject_id'=>'subjects','filter_chapter_id'=>'chapters'] as $key=>$relation) {
            if($request->filled($key)) {
                $filterId=(int)$request->input($key);
                $query->whereHas($relation, function($rel) use ($filterId){$rel->where('id',$filterId);});
            }
        }
        if($request->filled('created_by')) $query->where('created_by',$request->input('created_by'));
        if($request->filled('title')) $query->where('title','like','%'.$request->input('title').'%');
        if($request->filled('start')) $query->whereDate('created_at','>=',$request->input('start'));
        if($request->filled('end')) $query->whereDate('created_at','<=',$request->input('end'));
        $filterIds=array_filter((array)$request->input('filter_ids',[]));
        if($filterIds) $query->whereHas('filters',function($rel) use ($filterIds){$rel->whereIn('sub_filter_id',$filterIds);});

        $inserted=0;
        $insert=function($ids) use ($categoryId,$level,$entityId,&$inserted) {
            $now=now();$rows=[];
            foreach($ids as $id) $rows[]=['category_id'=>$categoryId,'level'=>$level,'entity_id'=>$entityId,'question_id'=>(int)$id,'created_at'=>$now,'updated_at'=>$now];
            if($rows) $inserted+=DB::table('exam_mcq_question_links')->insertOrIgnore($rows);
        };
        if($input['mode']==='page') {
            $page=(int)($input['page']??1);
            $ids=$query->orderByDesc('questions.id')->paginate(25,['questions.id'],'page',$page)->getCollection()->pluck('id');
            $insert($ids);
        } else {
            $query->orderBy('questions.id')->select('questions.id')->chunkById(300,function($group) use ($insert) {
                $insert($group->pluck('id'));
            }, 'questions.id','id');
        }
        return redirect()->route('admin.exam-mcq.questions',[
            'kind'=>$kind,'categoryId'=>$categoryId,'subject_id'=>$subjectId ?: null,'chapter_id'=>$chapterId ?: null
        ])->with('success', $inserted.' MCQ linked. Previously added questions skipped.');
    }
}
