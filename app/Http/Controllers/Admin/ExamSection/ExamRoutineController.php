<?php
namespace App\Http\Controllers\Admin\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class ExamRoutineController extends Controller
{
    private const CAT='lebd_exam_section_categories';
    private const SUB='lebd_exam_section_subcategories';
    private const ROUT='lebd_exam_section_routines';
    private const ITEMS='lebd_exam_section_routine_items';

    private function row(int $id) {return DB::table(self::ROUT)->where('id',$id)->first() ?? abort(404);}
    private function children(int $category) {return DB::table(self::SUB)->where('category_id',$category)->orderBy('sort_order')->orderBy('id')->get();}
    private function categoryRow(int $category) {return DB::table(self::CAT)->where('id',$category)->first() ?? abort(404);}
    public function categories(){
        $categories=DB::table(self::CAT.' as c')->select('c.*')
          ->selectSub(fn($q)=>$q->from(self::SUB.' as s')->selectRaw('count(*)')->whereColumn('s.category_id','c.id'),'sub_count')
          ->selectSub(fn($q)=>$q->from(self::ROUT.' as r')->selectRaw('count(*)')->whereColumn('r.category_id','c.id'),'routine_count')
          ->orderBy('c.sort_order')->orderBy('c.name')->get();
        return view('admin.exam-section.routines-categories',compact('categories'));
    }
    public function category(int $category){
        $cat=$this->categoryRow($category);$subs=$this->children($category);
        if($subs->isEmpty()) return redirect()->route('admin.exam-section.routine.list',['category'=>$category]);
        $counts=DB::table(self::ROUT)->where('category_id',$category)->whereNotNull('subcategory_id')->select('subcategory_id',DB::raw('count(*) as n'))->groupBy('subcategory_id')->pluck('n','subcategory_id');
        return view('admin.exam-section.routines-subcategories',compact('cat','subs','counts'));
    }
    public function index(Request $request,int $category){
        $cat=$this->categoryRow($category);$subs=$this->children($category);
        $subId=$request->filled('subcategory_id')?(int)$request->query('subcategory_id'):null;
        if($subs->isNotEmpty()){
            abort_unless($subId && $subs->contains('id',$subId),404,'Select a Sub-category first.');
        } else {$subId=null;}
        $rows=DB::table(self::ROUT.' as r')
            ->where('r.category_id',$category)->where('r.subcategory_id',$subId)
            ->select('r.*')
            ->orderByDesc('r.id')->get();
        $sub=$subId?$subs->firstWhere('id',$subId):null;
        return view('admin.exam-section.routines-list',compact('cat','sub','rows','subId'));
    }
    private function formData(){
        return [DB::table(self::CAT)->orderBy('sort_order')->orderBy('name')->get(),
                DB::table(self::SUB)->orderBy('sort_order')->orderBy('name')->get()];
    }
    public function create(Request $request){
        $category=(int)$request->query('category_id');$cat=$this->categoryRow($category);
        $subs=$this->children($category);$subId=$request->filled('subcategory_id')?(int)$request->query('subcategory_id'):null;
        if($subs->isNotEmpty()) abort_unless($subId&&$subs->contains('id',$subId),404,'Select Sub-category first.');
        [$categories,$allSubcategories]=$this->formData();$routine=null;
        return view('admin.exam-section.routines-form',compact('cat','subs','subId','categories','allSubcategories','routine'));
    }
    private function validated(Request $request){
        $data=$request->validate([
         'category_id'=>'required|integer|exists:lebd_exam_section_categories,id',
         'subcategory_id'=>'nullable|integer|exists:lebd_exam_section_subcategories,id',
         'name'=>'required|string|max:190', 'header'=>'nullable|string',
         'status'=>['required',Rule::in(['Draft','Published'])],
         'pdf'=>'nullable|file|mimes:pdf|max:20480'
        ]);
        $catId=(int)$data['category_id'];$subs=$this->children($catId);
        if($subs->isNotEmpty()){
          if(empty($data['subcategory_id'])||!$subs->contains('id',(int)$data['subcategory_id'])){
            throw \Illuminate\Validation\ValidationException::withMessages(['subcategory_id'=>'Select a sub-category belonging to this category.']);
          }
        } else {$data['subcategory_id']=null;}
        unset($data['pdf']);return $data;
    }
    public function store(Request $request){
        $data=$this->validated($request);if($request->hasFile('pdf'))$data['pdf_path']=$request->file('pdf')->store('exam-section/routine-pdfs','public');
        $id=DB::table(self::ROUT)->insertGetId($data+['created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('admin.exam-section.routine.items',$id)->with('success','Routine created. Add Exam / Revision / Model items.');
    }
    public function edit(int $routine){
        $routine=$this->row($routine);$cat=$this->categoryRow($routine->category_id);$subs=$this->children($cat->id);$subId=$routine->subcategory_id;
        [$categories,$allSubcategories]=$this->formData();
        $editLabels=DB::table('lebd_exam_section_routine_labels')->where('routine_id',$routine->id)->orderBy('sort_order')->orderBy('id')->get()->groupBy('kind');
        return view('admin.exam-section.routines-form',compact('cat','subs','subId','categories','allSubcategories','routine','editLabels'));
    }
    public function update(Request $request,int $routine){
        $old=$this->row($routine);$data=$this->validated($request);
        if((int)$data['category_id']!==(int)$old->category_id || (int)($data['subcategory_id']??0)!==(int)($old->subcategory_id??0)){
            throw \Illuminate\Validation\ValidationException::withMessages(['category_id'=>'Routine location cannot be changed during edit.']);
        }
        if($request->hasFile('pdf')){$data['pdf_path']=$request->file('pdf')->store('exam-section/routine-pdfs','public');}
        $labelData=$request->validate([
          'labels'=>'sometimes|array',
          'labels.exam'=>'sometimes|array','labels.revision'=>'sometimes|array','labels.model'=>'sometimes|array',
          'labels.*.*'=>'string|max:190',
        ]);
        $allowed=['exam','revision','model'];
        $labels=[];
        foreach($allowed as $kind){
            $values=array_values(array_unique($labelData['labels'][$kind]??[]));
            if(count($values)>100) throw \Illuminate\Validation\ValidationException::withMessages(['labels'=>'Too many labels.']);
            foreach($values as $index=>$value){
                $value=trim($value);
                if($value==='')continue;
                $labels[]=['routine_id'=>$routine,'kind'=>$kind,'value'=>$value,'sort_order'=>$index,'created_at'=>now(),'updated_at'=>now()];
            }
        }
        DB::transaction(function()use($routine,$data,$labels){
            DB::table(self::ROUT)->where('id',$routine)->update($data+['updated_at'=>now()]);
            DB::table('lebd_exam_section_routine_labels')->where('routine_id',$routine)->whereIn('kind',['exam','revision','model'])->delete();
            if($labels)DB::table('lebd_exam_section_routine_labels')->insert($labels);
        });
        return redirect()->route('admin.exam-section.routine.list', ['category' => $old->category_id, 'subcategory_id' => $old->subcategory_id])->with('success', 'Routine details and labels saved.');
    }
    public function destroy(int $routine){
        $r=$this->row($routine);DB::table(self::ROUT)->where('id',$routine)->delete();
        return redirect()->route('admin.exam-section.routine.list',['category'=>$r->category_id,'subcategory_id'=>$r->subcategory_id])->with('success','Routine deleted.');
    }
    // Fix159: card actions are isolated from the existing category/subcategory management screens.
    public function updateRoutineCategory(Request $request, int $category){
        $this->categoryRow($category);
        $data=$request->validate(['name'=>'required|string|max:190','sort_order'=>'required|integer|min:0','status'=>['required',Rule::in(['Active','Inactive'])]]);
        DB::table(self::CAT)->where('id',$category)->update($data);
        return redirect()->route('admin.exam-section.routine')->with('success','Category updated.');
    }
    public function deleteRoutineCategory(int $category){
        $this->categoryRow($category);
        if(DB::table(self::SUB)->where('category_id',$category)->exists() || DB::table(self::ROUT)->where('category_id',$category)->exists()){
            return back()->with('error','Cannot delete: this category contains Sub-categories or Routines.');
        }
        DB::table(self::CAT)->where('id',$category)->delete();
        return redirect()->route('admin.exam-section.routine')->with('success','Category deleted.');
    }
    public function updateRoutineSubcategory(Request $request, int $category, int $subcategory){
        $this->categoryRow($category);
        abort_unless(DB::table(self::SUB)->where('id',$subcategory)->where('category_id',$category)->exists(),404);
        $data=$request->validate(['name'=>'required|string|max:190','sort_order'=>'required|integer|min:0','status'=>['required',Rule::in(['Active','Inactive'])]]);
        DB::table(self::SUB)->where('id',$subcategory)->update($data);
        return redirect()->route('admin.exam-section.routine.category',$category)->with('success','Sub-category updated.');
    }
    public function deleteRoutineSubcategory(int $category, int $subcategory){
        $this->categoryRow($category);
        abort_unless(DB::table(self::SUB)->where('id',$subcategory)->where('category_id',$category)->exists(),404);
        if(DB::table(self::ROUT)->where('subcategory_id',$subcategory)->exists()){
            return back()->with('error','Cannot delete: this Sub-category contains Routines.');
        }
        DB::table(self::SUB)->where('id',$subcategory)->delete();
        return redirect()->route('admin.exam-section.routine.category',$category)->with('success','Sub-category deleted.');
    }
    /** Auto dates: first named exam = routine creation; later exams = preceding exam end. */
    private function withCalculatedItemDates(object $routine, $items):void {
        $labels=DB::table('lebd_exam_section_routine_labels')->where('routine_id',$routine->id)
            ->whereIn('kind',['exam','revision','model'])->orderBy('sort_order')->orderBy('id')->get();
        $rank=[];
        foreach($labels as $label){$kind=$label->kind;$rank[$kind][$label->value]=count($rank[$kind]??[]);}
        foreach(['exam','revision','model'] as $kind){
            $group=$items->filter(fn($item)=>$item->type===$kind)->sort(function($a,$b)use($rank,$kind){
                $r=$rank[$kind]??[];
                $ka=$r[$a->exam_name]??PHP_INT_MAX;$kb=$r[$b->exam_name]??PHP_INT_MAX;
                if($ka!==$kb)return $ka<=>$kb;
                $na=preg_match('/(\d+)\s*$/',(string)$a->exam_name,$ma)?(int)$ma[1]:PHP_INT_MAX;
                $nb=preg_match('/(\d+)\s*$/',(string)$b->exam_name,$mb)?(int)$mb[1]:PHP_INT_MAX;
                return ($na<=>$nb)?:($a->id<=>$b->id);
            });
            $previousEnd=null;
            foreach($group as $item){
                $auto=$previousEnd ?: ($routine->created_at ?: $item->created_at);
                $item->pdf_create_at=$auto;
                $item->question_create_at=$auto;
                $previousEnd=$item->exam_end_at;
            }
        }
    }

    /** Persist the same calculated schedule after Add, Edit or Delete. */
    private function syncCalculatedItemDates(int $routineId):void {
        $routine=$this->row($routineId);
        $items=DB::table(self::ITEMS)->where('routine_id',$routineId)->orderBy('id')->get();
        $this->withCalculatedItemDates($routine,$items);
        foreach($items as $item){
            DB::table(self::ITEMS)->where('id',$item->id)->update([
                'pdf_create_at'=>$item->pdf_create_at,
                'question_create_at'=>$item->question_create_at,
            ]);
        }
    }

    public function items(int $routine){
        $routine=$this->row($routine);$cat=$this->categoryRow($routine->category_id);
        $sub=$routine->subcategory_id?DB::table(self::SUB)->where('id',$routine->subcategory_id)->first():null;
        $items=DB::table(self::ITEMS)->where('routine_id',$routine->id)->orderBy('id')->get();
        $this->withCalculatedItemDates($routine,$items);
        $departments=DB::table('lebd_exam_routine_departments')->where('status','Active')->orderBy('name')->get();
        $subjects=DB::table('lebd_exam_routine_subjects')->where('status','Active')->orderBy('name')->get();
        $chapters=DB::table('lebd_exam_routine_chapters')->where('status','Active')->orderBy('name')->get();
        $topicRows=collect(); // Selected per Exam Item, not fixed Category/Sub-category slots.
        $examLabels=DB::table('lebd_exam_section_routine_labels')->where('routine_id',$routine->id)->whereIn('kind',['exam','revision','model'])->orderBy('sort_order')->orderBy('id')->get()->groupBy('kind');
        $questionCounts=DB::table('lebd_exam_routine_question_links')->select('topic_level','topic_id',DB::raw('COUNT(DISTINCT question_id) as total'))->groupBy('topic_level','topic_id')->get();
        $topicCountMap=$questionCounts->mapWithKeys(fn($x)=>[$x->topic_level.':'.$x->topic_id=>(int)$x->total])->all();
        $topicNameMap=[];
        foreach(['department'=>$departments,'subject'=>$subjects,'chapter'=>$chapters] as $lvl=>$records){foreach($records as $record){$topicNameMap[$lvl.':'.$record->id]=$record->name;}}
        $savedTopics=DB::table('lebd_exam_section_routine_item_topics')->whereIn('item_id',$items->pluck('id')->all())->get()->groupBy('item_id');
        return view('admin.exam-section.routines-items',compact('routine','cat','sub','items','topicRows','savedTopics','departments','subjects','chapters','examLabels','topicCountMap','topicNameMap'));
    }
    private function itemData(Request $request,int $routine){
        $data=$request->validate([
         'type'=>['required',Rule::in(['exam','revision','model'])],
         'exam_name'=>'required|string|max:190',
         'exam_start_at'=>'required|date','exam_end_at'=>'required|date|after:exam_start_at',
         'pdf_create_at'=>'nullable|date','question_create_at'=>'nullable|date', // Ignored: calculated on server
         'topic_kind'=>['nullable',Rule::in(['department','non-department'])],
         'department_id'=>'nullable|integer|exists:lebd_exam_routine_departments,id',
         'subject_id'=>'nullable|integer|exists:lebd_exam_routine_subjects,id',
         'chapter_id'=>'nullable|integer|exists:lebd_exam_routine_chapters,id',
         'question_start'=>'nullable|integer|min:1', 'question_end'=>'nullable|integer|min:1',
         'notes'=>'nullable|string', 'source_exam_ids'=>'nullable|array', 'source_exam_ids.*'=>'integer',
         'topics'=>'required|array|min:1',
         'topics.*.key'=>'required|string|max:80',
         'topics.*.question_start'=>'nullable|integer|min:1',
         'topics.*.question_end'=>'nullable|integer|min:1'
        ]);
        if(!empty($data['department_id'])){
          $dept=DB::table('lebd_exam_routine_departments')->where('id',$data['department_id'])->first();
          abort_unless($dept && $dept->kind===$data['topic_kind'],422,'Wrong topic section.');
        }
        if(!empty($data['subject_id'])){
          $subject=DB::table('lebd_exam_routine_subjects')->where('id',$data['subject_id'])->first();
          abort_unless($subject && (int)$subject->department_id===(int)($data['department_id']??0),422,'Subject / Department mismatch.');
        }
        if(!empty($data['chapter_id'])){
          $chapter=DB::table('lebd_exam_routine_chapters')->where('id',$data['chapter_id'])->first();
          abort_unless($chapter && (int)$chapter->subject_id===(int)($data['subject_id']??0),422,'Chapter / Subject mismatch.');
        }
        if(isset($data['question_end']) && isset($data['question_start']) && $data['question_end']<$data['question_start']){
           throw \Illuminate\Validation\ValidationException::withMessages(['question_end'=>'Question End must not be before Question Start.']);
        }
        if(($data['type']==='revision'||$data['type']==='model')){
           $source=$data['source_exam_ids']??[];
           $valid=DB::table(self::ITEMS)->where('routine_id',$routine)->where('type','exam')->whereIn('id',$source)->count();
           if($valid!==count(array_unique($source)) || !$source){
             throw \Illuminate\Validation\ValidationException::withMessages(['source_exam_ids'=>'Choose valid source Exams from this routine.']);
           }
        }
        $topicData=$data['topics']; unset($data['topics']);
        $data['topic_kind']=$data['topic_kind']??'non-department';
        $data['department_id']=$data['department_id']??null;
        $data['subject_id']=$data['subject_id']??null;
        $data['chapter_id']=$data['chapter_id']??null;
        $data['question_start']=$topicData[0]['question_start']??null;
        $data['question_end']=$topicData[0]['question_end']??null;
        if(count($topicData)>60){throw \Illuminate\Validation\ValidationException::withMessages(['topics'=>'Maximum 60 topics per exam.']);}
        $keys=array_column($topicData,'key');
        if(count(array_unique($keys))!==count($keys)){throw \Illuminate\Validation\ValidationException::withMessages(['topics'=>'Duplicate topic selections.']);}
        foreach($topicData as $topic){
          if(!preg_match('/^(department|subject|chapter):([1-9][0-9]*)$/',$topic['key'],$matches)){
            throw \Illuminate\Validation\ValidationException::withMessages(['topics'=>'Please select a valid Routine Topic Name.']);
          }
          $level=$matches[1];$topicId=(int)$matches[2];
          $table=['department'=>'lebd_exam_routine_departments','subject'=>'lebd_exam_routine_subjects','chapter'=>'lebd_exam_routine_chapters'][$level];
          $record=DB::table($table)->where('id',$topicId)->where('status','Active')->first();
          if(!$record){throw \Illuminate\Validation\ValidationException::withMessages(['topics'=>'An inactive or deleted topic was selected.']);}
        }
        $labels=DB::table('lebd_exam_section_routine_labels')->where('routine_id',$routine)->where('kind',$data['type'])->pluck('value')->all();
        if($labels && !in_array($data['exam_name'],$labels,true)){
          throw \Illuminate\Validation\ValidationException::withMessages(['exam_name'=>'Choose an Exam No from Routine Edit.']);
        }
        foreach($topicData as $topic){
          if(isset($topic['question_start'],$topic['question_end']) && $topic['question_end']<$topic['question_start']){
            throw \Illuminate\Validation\ValidationException::withMessages(['topics'=>'Question Range To must be greater than or equal to From.']);
          }
        }
        $data['_topic_rows']=$topicData;
        $data['source_exam_ids']=json_encode($data['source_exam_ids']??[]);
        return $data;
    }
    public function storeItem(Request $request,int $routine){
        $this->row($routine);$data=$this->itemData($request,$routine);
        if(DB::table(self::ITEMS)->where('routine_id',$routine)->where('type',$data['type'])->where('exam_name',$data['exam_name'])->exists())
           return back()->withErrors(['exam_name'=>'Exam name already exists for this type.'])->withInput();
        $topicRows=$data['_topic_rows'];unset($data['_topic_rows']);
        unset($data['pdf_create_at'],$data['question_create_at']);
        DB::transaction(function()use($data,$routine,$topicRows){
          $itemId=DB::table(self::ITEMS)->insertGetId($data+['routine_id'=>$routine,'created_at'=>now(),'updated_at'=>now()]);
          $this->saveItemTopics($itemId,$topicRows);
          $this->syncCalculatedItemDates($routine);
        });
        return back()->with('success','Routine item added.');
    }
    public function updateItem(Request $request,int $routine,int $item){
        $this->row($routine);abort_unless(DB::table(self::ITEMS)->where('id',$item)->where('routine_id',$routine)->exists(),404);
        $data=$this->itemData($request,$routine);
        $topicRows=$data['_topic_rows'];unset($data['_topic_rows']);
        unset($data['pdf_create_at'],$data['question_create_at']);
        DB::transaction(function()use($data,$item,$topicRows){
          DB::table(self::ITEMS)->where('id',$item)->update($data+['updated_at'=>now()]);
          DB::table('lebd_exam_section_routine_item_topics')->where('item_id',$item)->delete();
          $this->saveItemTopics($item,$topicRows);
          $this->syncCalculatedItemDates($routine);
        });
        return back()->with('success','Routine item updated.');
    }
    private function saveItemTopics(int $itemId,array $topics):void{
       foreach($topics as $topic){
         DB::table('lebd_exam_section_routine_item_topics')->insert([
            'item_id'=>$itemId,'topic_key'=>$topic['key'],
            'question_start'=>$topic['question_start']??null,
            'question_end'=>$topic['question_end']??null,
            'created_at'=>now(),'updated_at'=>now()
         ]);
       }
    }
    public function deleteItem(int $routine,int $item){
        $this->row($routine);$row=DB::table(self::ITEMS)->where('routine_id',$routine)->where('id',$item)->first();abort_unless($row,404);
        DB::transaction(function()use($item,$routine){DB::table(self::ITEMS)->where('id',$item)->delete();$this->syncCalculatedItemDates($routine);});return back()->with('success','Item deleted.');
    }
}
