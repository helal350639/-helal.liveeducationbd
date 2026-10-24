<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ExamMcqController extends Controller
{
    private function kind(string $kind): void { abort_unless(in_array($kind,['department','non-department'],true),404); }
    private function category(string $kind,int $id) {
        $this->kind($kind);
        return DB::table('exam_mcq_categories')->where('kind',$kind)->where('id',$id)->first() ?? abort(404);
    }
    private function subject(int $categoryId,int $id) {
        return DB::table('exam_mcq_subjects')->where('category_id',$categoryId)->where('id',$id)->first() ?? abort(404);
    }
    private function chapter(int $subjectId,int $id) {
        return DB::table('exam_mcq_chapters')->where('subject_id',$subjectId)->where('id',$id)->first() ?? abort(404);
    }
    private function level(string $kind,int $categoryId,Request $r): array {
        $category=$this->category($kind,$categoryId);
        $subjectId=(int)$r->input('subject_id',0); $chapterId=(int)$r->input('chapter_id',0);
        if($chapterId && !$subjectId) abort(422,'Chapter requires a subject');
        if($subjectId) $this->subject($categoryId,$subjectId);
        if($chapterId) $this->chapter($subjectId,$chapterId);
        return [$category,$chapterId?'chapter':($subjectId?'subject':'category'),$chapterId?:($subjectId?:$categoryId),$subjectId,$chapterId];
    }
    private function common(Request $r,string $table,string $parent,$parentId): array {
        return $r->validate(['name'=>['required','string','max:180',Rule::unique($table)->where($parent,$parentId)],'status'=>'required|in:Active,Inactive','sort_order'=>'nullable|integer|min:0|max:999999']);
    }
    public function index(string $kind) {
        $this->kind($kind);
        $categories=DB::table('exam_mcq_categories')->where('kind',$kind)->orderBy('sort_order')->orderBy('id')->get();
        $categoryCounts=DB::table('exam_mcq_question_links')->whereIn('category_id',$categories->pluck('id')->all())
            ->select('category_id',DB::raw('COUNT(*) as total'))->groupBy('category_id')->pluck('total','category_id');
        $subjectCounts=DB::table('exam_mcq_subjects')->whereIn('category_id',$categories->pluck('id')->all())
            ->select('category_id',DB::raw('COUNT(*) as total'))->groupBy('category_id')->pluck('total','category_id');
        return view('admin.exam-mcq.index',compact('kind','categories','categoryCounts','subjectCounts'));
    }
    public function addCategory(Request $r,string $kind) {
        $this->kind($kind); $v=$this->common($r,'exam_mcq_categories','kind',$kind);
        DB::table('exam_mcq_categories')->insert(['kind'=>$kind,'name'=>$v['name'],'status'=>$v['status'],'sort_order'=>$v['sort_order']??0,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Category added');
    }
    public function addSubject(Request $r,string $kind,int $categoryId) {
        $this->category($kind,$categoryId); $v=$this->common($r,'exam_mcq_subjects','category_id',$categoryId);
        DB::table('exam_mcq_subjects')->insert(['category_id'=>$categoryId,'name'=>$v['name'],'status'=>$v['status'],'sort_order'=>$v['sort_order']??0,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Subject added');
    }
    public function addChapter(Request $r,string $kind,int $categoryId,int $subjectId) {
        $this->category($kind,$categoryId); $this->subject($categoryId,$subjectId);
        $v=$this->common($r,'exam_mcq_chapters','subject_id',$subjectId);
        DB::table('exam_mcq_chapters')->insert(['subject_id'=>$subjectId,'name'=>$v['name'],'status'=>$v['status'],'sort_order'=>$v['sort_order']??0,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Chapter added');
    }
    public function detail(string $kind,int $categoryId) {
        $category=$this->category($kind,$categoryId);
        $subjects=DB::table('exam_mcq_subjects')->where('category_id',$categoryId)->orderBy('sort_order')->orderBy('id')->get();
        $chapters=DB::table('exam_mcq_chapters')->whereIn('subject_id',$subjects->pluck('id')->all())->orderBy('sort_order')->orderBy('id')->get()->groupBy('subject_id');
        $counts=DB::table('exam_mcq_question_links')->where('category_id',$categoryId)->select('level','entity_id',DB::raw('COUNT(*) AS total'))->groupBy('level','entity_id')->get();
        return view('admin.exam-mcq.detail',compact('kind','category','subjects','chapters','counts'));
    }
    private function optionLists():array {
        $models=['courses'=>\App\Models\Course::class,'departments'=>\App\Models\Department::class,'semesters'=>\App\Models\Semester::class,'subjects'=>\App\Models\Subject::class,'chapters'=>\App\Models\Chapter::class,'users'=>\App\Models\User::class];
        $data=[];
        foreach ($models as $key => $model) {
            $query = $model::query();
            if ($key === 'courses') {
                $query->where('status', 'Active');
            }
            $data[$key] = $query->orderBy('name')->get(['id', 'name']);
        }
        $data['filters']=\App\Models\Filter::with('SubFilter')->get();
        return $data;
    }
    private function filtered(Request $r) {
        $q=Question::query()->where('type','MCQ')->orderByDesc('id');
        foreach(['course_id'=>'courses','department_id'=>'departments','semester_id'=>'semesters','filter_subject_id'=>'subjects','filter_chapter_id'=>'chapters'] as $input=>$rel) {
            if($r->filled($input)) { $id=$r->input($input); $q->whereHas($rel,fn($x)=>$x->where('id',$id)); }
        }
        if($r->filled('created_by')) $q->where('created_by',$r->created_by);
        if($r->filled('title')) $q->where('title','like','%'.$r->title.'%');
        if($r->filled('start')) $q->whereDate('created_at','>=',$r->start);
        if($r->filled('end')) $q->whereDate('created_at','<=',$r->end);
        $ids=array_filter((array)$r->input('filter_ids',[]));
        if($ids) $q->whereHas('filters',fn($x)=>$x->whereIn('sub_filter_id',$ids));
        return $q;
    }
    // Fix70: Option lists reflect actual MCQ relationships in the main Question Bank.
    // The same picker URL serves JSON options; no new route or database schema needed.
    private function connectedPickerOptions(Request $request): array
    {
        $steps = [
            'course_id' => ['courses', 'course_question', 'course_id', \App\Models\Course::class],
            'department_id' => ['departments', 'department_question', 'department_id', \App\Models\Department::class],
            'semester_id' => ['semesters', 'question_semester', 'semester_id', \App\Models\Semester::class],
            'filter_subject_id' => ['subjects', 'question_subject', 'subject_id', \App\Models\Subject::class],
            'filter_chapter_id' => ['chapters', 'chapter_question', 'chapter_id', \App\Models\Chapter::class],
        ];
        $query = Question::query()->where('type', 'MCQ');
        $result = [];
        foreach ($steps as $field => $info) {
            [$relation, $pivot, $foreign, $model] = $info;
            // Query values must match the selected parent chain, not every DB item.
            if ($field === 'course_id') {
                // Show every Active course, including courses without existing MCQs.
                // Subsequent options remain connected to actual Question Bank MCQs.
                $options = $model::query()->where('status', 'Active');
            } else {
                $ids = DB::table($pivot)->whereIn('question_id', (clone $query)->select('id'))
                    ->distinct()->pluck($foreign)->filter()->values()->all();
                $options = $model::query()->whereIn('id', $ids);
            }
            $result[$field] = $options->orderBy('name')
                ->get(['id', 'name'])->map(fn($x) => ['id' => (string)$x->id, 'name' => $x->name])->all();
            if ($request->filled($field)) {
                $id = (int) $request->input($field);
                $query->whereHas($relation, fn($q) => $q->where('id', $id));
            }
        }
        return $result;
    }

    private function pickerCascadeScript(): string
    {
        // Injected only into the rendered picker response to preserve the existing Blade,
        // bulk-selection buttons, advanced filters, and all linked-MCQ workflows.
        return <<<'JAVASCRIPT'
<script id="lebd70-picker-cascade">
(function(){
  'use strict';
  var fields=['course_id','department_id','semester_id','filter_subject_id','filter_chapter_id'];
  var selects=fields.map(function(field){return document.querySelector('select[name="'+field+'"]');});
  if(!selects.some(Boolean)) return;
  var form=(selects.find(function(el){return !!el;})||{}).form;
  var serial=0;
  function updateSelect(el,rows,keep){
    if(!el)return;
    var fallback=el.querySelector('option[value=""]');
    var label=fallback?fallback.textContent:'All';
    el.textContent='';
    var empty=new Option(label,'');el.add(empty);
    (rows||[]).forEach(function(row){el.add(new Option(row.name,String(row.id)));});
    el.value=keep && Array.from(el.options).some(function(o){return o.value===keep;})?keep:'';
    if(window.jQuery && window.jQuery.fn.select2 && window.jQuery(el).data('select2'))window.jQuery(el).trigger('change.select2');
  }
  async function cascade(changed,initial){
    var current=++serial;
    var values=selects.map(function(el){return el?el.value:'';});
    for(var j=changed+1;!initial && j<selects.length;j++){
      values[j]='';if(selects[j])selects[j].value='';
    }
    var params=new URLSearchParams(window.location.search);
    params.set('_lebd70_picker_options','1');
    fields.forEach(function(f,i){params.delete(f);if(values[i])params.set(f,values[i]);});
    try{
      var response=await fetch(window.location.pathname+'?'+params.toString(),{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});
      if(!response.ok)throw Error('Request failed');
      var result=await response.json();if(current!==serial)return;
      fields.forEach(function(f,i){
        if(i<=changed && !initial)return;
        // Hide options until the required parent is chosen (semester is optional).
        var available=result[f]||[];
        if(i===1 && !values[0])available=[];
        if(i===2 && !values[1])available=[];
        if(i===3 && !values[1])available=[];
        if(i===4 && !values[3])available=[];
        updateSelect(selects[i],available,values[i]);
      });
    }catch(err){console.warn('MCQ picker: dependent options could not be loaded',err);}
  }
  selects.forEach(function(el,index){if(el)el.addEventListener('change',function(){cascade(index);});});
  // On first load constrain descendants to any already selected parents.
  var last=-1;
  for(var i=0;i<selects.length;i++){if(selects[i] && selects[i].value)last=i;}
  cascade(last,true);
})();
</script>
JAVASCRIPT;
    }

    public function picker(Request $r,string $kind,int $categoryId) {
        [$category,$level,$entityId,$subjectId,$chapterId]=$this->level($kind,$categoryId,$r);
        if ($r->boolean('_lebd70_picker_options')) {
            return response()->json($this->connectedPickerOptions($r));
        }
        $questions=$this->filtered($r)->paginate(25)->withQueryString();
        $linked=DB::table('exam_mcq_question_links')->where('level',$level)->where('entity_id',$entityId)->pluck('question_id')->all();
        $questionOptions=DB::table('mcq_items')->whereIn('question_id',$questions->pluck('id')->all())->orderBy('id')
            ->get(['question_id','item','correct_answer','image'])->groupBy('question_id');
        $subjectsTree=DB::table('exam_mcq_subjects')->where('category_id',$categoryId)->orderBy('sort_order')->get();
        $chaptersTree=DB::table('exam_mcq_chapters')->whereIn('subject_id',$subjectsTree->pluck('id')->all())->orderBy('sort_order')->get()->groupBy('subject_id');
        $html = view('admin.exam-mcq.picker',array_merge(compact('kind','category','level','entityId','subjectId','chapterId','questions','linked','questionOptions','subjectsTree','chaptersTree'),$this->optionLists()))->render();
        return response($html . $this->pickerCascadeScript());
    }
    public function attach(Request $r,string $kind,int $categoryId) {
        [$category,$level,$entityId,$subjectId,$chapterId]=$this->level($kind,$categoryId,$r);
        $v=$r->validate(['question_ids'=>'required|array|min:1|max:200','question_ids.*'=>'required|integer|distinct|exists:questions,id']);
        $valid=Question::whereIn('id',$v['question_ids'])->where('type','MCQ')->pluck('id');
        $rows=[]; foreach($valid as $qid) $rows[]=['category_id'=>$categoryId,'level'=>$level,'entity_id'=>$entityId,'question_id'=>$qid,'created_at'=>now(),'updated_at'=>now()];
        DB::table('exam_mcq_question_links')->insertOrIgnore($rows);
        return redirect()->route('admin.exam-mcq.questions',['kind'=>$kind,'categoryId'=>$categoryId,'subject_id'=>$subjectId?:null,'chapter_id'=>$chapterId?:null])->with('success','Selected MCQs linked');
    }
    public function questions(Request $r,string $kind,int $categoryId) {
        [$category,$level,$entityId,$subjectId,$chapterId]=$this->level($kind,$categoryId,$r);
        $questions=DB::table('exam_mcq_question_links as l')->join('questions as q','q.id','=','l.question_id')
          ->where('l.category_id',$categoryId)->where('l.level',$level)->where('l.entity_id',$entityId)
          ->select('l.id as link_id','q.id','q.title','q.status','q.answer','q.explanation')->orderByDesc('l.id')->paginate(25)->withQueryString();
        $questionOptions=DB::table('mcq_items')->whereIn('question_id',$questions->pluck('id')->all())->orderBy('id')
            ->get(['question_id','item','correct_answer','image'])->groupBy('question_id');
        return view('admin.exam-mcq.questions',compact('kind','category','level','entityId','subjectId','chapterId','questions','questionOptions'));
    }
    public function detach(Request $r,string $kind,int $categoryId,int $linkId) {
        $this->category($kind,$categoryId);
        DB::table('exam_mcq_question_links')->where('category_id',$categoryId)->where('id',$linkId)->delete();
        return back()->with('success','Question link removed; main database unchanged');
    }
    public function deleteEntity(Request $r,string $kind,int $categoryId,string $level,int $id) {
        $this->category($kind,$categoryId); abort_unless(in_array($level,['subject','chapter'],true),404);
        if($level==='subject') { $this->subject($categoryId,$id);
            $chapterIds=DB::table('exam_mcq_chapters')->where('subject_id',$id)->pluck('id')->all();
            DB::transaction(function()use($id,$chapterIds){DB::table('exam_mcq_question_links')->where('level','subject')->where('entity_id',$id)->delete();DB::table('exam_mcq_question_links')->where('level','chapter')->whereIn('entity_id',$chapterIds)->delete();DB::table('exam_mcq_chapters')->where('subject_id',$id)->delete();DB::table('exam_mcq_subjects')->where('id',$id)->delete();});
        } else { $subjectId=(int)$r->input('subject_id'); $this->subject($categoryId,$subjectId);$this->chapter($subjectId,$id);
            DB::transaction(function()use($id){DB::table('exam_mcq_question_links')->where('level','chapter')->where('entity_id',$id)->delete();DB::table('exam_mcq_chapters')->where('id',$id)->delete();}); }
        return back()->with('success','Section removed; main questions unchanged');
    }
}
