<?php
namespace App\Http\Controllers\Admin\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/** Standalone Exam Section routine-topic tree; does NOT alter the main question bank. */
class RoutineTopicController extends Controller
{
    private const TABLES = [
        'department' => 'lebd_exam_routine_departments',
        'subject' => 'lebd_exam_routine_subjects',
        'chapter' => 'lebd_exam_routine_chapters',
    ];
    private function table(string $level): string { abort_unless(isset(self::TABLES[$level]),404); return self::TABLES[$level]; }
    private function parentKey(string $level): ?string { return ['department'=>null,'subject'=>'department_id','chapter'=>'subject_id'][$level]; }
    private function path($level, $parentId=null) {return route('admin.exam-section.routine-topic.index',['level'=>$level]+($parentId?['parent_id'=>$parentId]:[]));}
    public function landing()
    {
        return view('admin.exam-section.routine-topic-landing');
    }
    /** Resolve mode from explicit selection or an existing parent/topic.
     * Old bookmarked URLs and form actions may omit ?kind=... .
     * Never assume an arbitrary mode when records cannot establish one.
     */
    private function kind(Request $request): string
    {
        $explicit = (string) $request->input('kind', '');
        $level = (string) $request->route('level', '');
        $id = (int) $request->route('id', 0);
        $parentId = (int) $request->input('parent_id', 0);
        $inferred = null;

        if ($id > 0 && in_array($level, ['department','subject','chapter'], true)) {
            $inferred = $this->kindOf($level, $id);
        } elseif ($parentId > 0 && $level === 'subject') {
            $inferred = $this->kindOf('department', $parentId);
        } elseif ($parentId > 0 && $level === 'chapter') {
            $inferred = $this->kindOf('subject', $parentId);
        }
        if ($explicit !== '') {
            abort_unless(in_array($explicit, ['department','non-department'], true), 422, 'Invalid department type.');
            abort_unless($inferred === null || $inferred === $explicit, 422, 'Department type does not match the selected topic.');
            return $explicit;
        }
        abort_unless(in_array($inferred, ['department','non-department'], true), 422, 'Choose Department or Non-Department first.');
        return $inferred;
    }
    private function kindOf(string $level, int $id): ?string
    {
        if ($level==='department') return DB::table(self::TABLES['department'])->where('id',$id)->value('kind');
        if ($level==='subject') return DB::table(self::TABLES['department'])
            ->join(self::TABLES['subject'].' as s','s.department_id','=','lebd_exam_routine_departments.id')
            ->where('s.id',$id)->value('lebd_exam_routine_departments.kind');
        return DB::table(self::TABLES['chapter'].' as c')->join(self::TABLES['subject'].' as s','s.id','=','c.subject_id')
            ->join(self::TABLES['department'].' as d','d.id','=','s.department_id')
            ->where('c.id',$id)->value('d.kind');
    }
    public function index(Request $request, string $level='department')
    {
        // A direct, unscoped navigation should open the two-choice landing page.
        if (!$request->filled('kind') && !$request->filled('parent_id')) {
            return redirect()->route('admin.exam-section.routine-topics');
        }
        $kind=$this->kind($request);
        $table=$this->table($level);$parentKey=$this->parentKey($level);
        $parentId=$parentKey?(int)$request->query('parent_id'):null;
        $parents=collect(); $parent=null;
        if($parentKey){
          $pTable=$level==='subject'?self::TABLES['department']:self::TABLES['subject'];
          $parents=DB::table($pTable)
            ->when($level==='subject', fn($q)=>$q->where('kind',$kind))
            ->when($level==='chapter', fn($q)=>$q->whereIn('department_id', DB::table(self::TABLES['department'])->where('kind',$kind)->select('id')))
            ->orderBy('sort_order')->orderBy('name')->get();
          if($parentId){$parent=DB::table($pTable)->where('id',$parentId)->first();abort_unless($parent && $parents->contains('id',$parentId),404);}
        }
        $rows=DB::table($table)
          ->when($level==='department',fn($q)=>$q->where('kind',$kind))
          ->when($level==='subject',fn($q)=>$q->whereIn('department_id', DB::table(self::TABLES['department'])->where('kind',$kind)->select('id')))
          ->when($level==='chapter',fn($q)=>$q->whereIn('subject_id', DB::table(self::TABLES['subject'])->whereIn('department_id', DB::table(self::TABLES['department'])->where('kind',$kind)->select('id'))->select('id')))
          ->when($parentId&&$parentKey,fn($q)=>$q->where($parentKey,$parentId))
          ->orderBy('sort_order')->orderByDesc('id')->get();
        $counts=[];
        if($level!=='chapter'){
            $child=$level==='department'?self::TABLES['subject']:self::TABLES['chapter'];
            $fk=$level.'_id';
            $counts=DB::table($child)->select($fk,DB::raw('COUNT(*) as total'))->groupBy($fk)->pluck('total',$fk)->all();
        }
        // Count ONLY direct MCQ links attached to each topic in this level.
        $questionCounts=DB::table('lebd_exam_routine_question_links')
          ->where('topic_level',$level)->whereIn('topic_id',$rows->pluck('id')->all())
          ->select('topic_id',DB::raw('COUNT(DISTINCT question_id) as total'))
          ->groupBy('topic_id')->pluck('total','topic_id')->all();
        return view('admin.exam-section.routine-topics',compact('level','rows','parents','parent','parentId','parentKey','counts','questionCounts','kind'));
    }
    private function validateItem(Request $request,string $level):array
    {
        $kind=$this->kind($request);
        $data=$request->validate([
          'name'=>['required','string','max:190'],
          'sort_order'=>['required','integer','min:0','max:999999'],
          'status'=>['required',Rule::in(['Active','Inactive'])],
          'parent_id'=>[$level==='department'?'nullable':'required','nullable','integer','min:1'],
        ]);
        $key=$this->parentKey($level);
        if($key){
          $pTable=$level==='subject'?self::TABLES['department']:self::TABLES['subject'];
          abort_unless($this->kindOf($level==='subject'?'department':'subject',(int)$data['parent_id'])===$kind,422,'Invalid parent.');
          $data[$key]=$data['parent_id'];
        }
        if($level==='department')$data['kind']=$kind;
        unset($data['parent_id']);return $data;
    }
    public function store(Request $request,string $level)
    {
        $table=$this->table($level);$data=$this->validateItem($request,$level);
        DB::table($table)->insert($data+['created_at'=>now(),'updated_at'=>now()]);
        return redirect($this->path($level,$data[$this->parentKey($level)]??null).'?kind='.$this->kind($request))->with('success','Added successfully.');
    }
    public function update(Request $request,string $level,int $id)
    {
        $table=$this->table($level);abort_unless($this->kindOf($level,$id)===$this->kind($request),404);
        $data=$this->validateItem($request,$level);
        DB::table($table)->where('id',$id)->update($data+['updated_at'=>now()]);
        return redirect($this->path($level,$data[$this->parentKey($level)]??null).'?kind='.$this->kind($request))->with('success','Updated successfully.');
    }
    public function delete(Request $request,string $level,int $id)
    {
        $table=$this->table($level);$row=DB::table($table)->where('id',$id)->first();abort_unless($row && $this->kindOf($level,$id)===$this->kind($request),404);
        if($level!=='chapter'){
          $child=$level==='department'?self::TABLES['subject']:self::TABLES['chapter'];
          if(DB::table($child)->where($level.'_id',$id)->exists())return back()->with('error','Delete linked '.($level==='department'?'subjects':'chapters').' first.');
        }
        if(DB::table('lebd_exam_routine_question_links')->where('topic_level',$level)->where('topic_id',$id)->exists())
          return back()->with('error','Remove linked MCQs first.');
        DB::table($table)->where('id',$id)->delete();return back()->with('success','Deleted.');
    }
    private function questionTable(): ?string
    {
      // Question -> View Question normally points to the application's main questions table.
      // Do not silently use MCQ-for-Exam's separate tables.
      return Schema::hasTable('questions')?'questions':null;
    }
    private function questionColumns(string $table):array
    {
      $all=Schema::getColumnListing($table);
      $label=collect(['question','question_text','title','name','question_name','details','description'])->first(fn($c)=>in_array($c,$all,true));
      return [$label,$all];
    }
    /** Main-question filters are applied to original question IDs, never copied. */
    private function filteredQuestions(Request $request)
    {
        $query = DB::table('questions');
        $term = trim((string)$request->input('q',''));
        if ($term !== '') {
            $query->where(function($q) use($term) {
                $q->where('title','like','%'.$term.'%');
                if (ctype_digit($term)) $q->orWhere('id',(int)$term);
            });
        }
        if ($request->filled('course_id') && Schema::hasTable('course_question')) {
            $v=(int)$request->input('course_id');
            $query->whereExists(fn($q)=>$q->selectRaw('1')->from('course_question as cq')
                ->whereColumn('cq.question_id','questions.id')->where('cq.course_id',$v));
        }
        if ($request->filled('course_id') && $request->filled('department_id') && Schema::hasTable('course_department')) {
            $cid=(int)$request->input('course_id'); $did=(int)$request->input('department_id');
            if(!DB::table('course_department')->where('course_id',$cid)->where('department_id',$did)->exists()) {
                $query->whereRaw('1=0');
            }
        }
        if ($request->filled('department_id') && Schema::hasTable('department_question')) {
            $v=(int)$request->input('department_id');
            $query->whereExists(fn($q)=>$q->selectRaw('1')->from('department_question as dq')
                ->whereColumn('dq.question_id','questions.id')->where('dq.department_id',$v));
        }
        foreach (['semester_id'=>['question_semester','semester_id'],
                  'subject_id'=>['question_subject','subject_id'],
                  'chapter_id'=>['chapter_question','chapter_id']] as $field=>$spec) {
            if ($request->filled($field) && Schema::hasTable($spec[0])) {
                $v=(int)$request->input($field);
                $query->whereExists(fn($q)=>$q->selectRaw('1')->from($spec[0].' as pivotq')
                    ->whereColumn('pivotq.question_id','questions.id')->where('pivotq.'.$spec[1],$v));
            }
        }
        if ($request->filled('sub_filter_id') && Schema::hasTable('question_sub_filter')) {
            $v=(int)$request->input('sub_filter_id');
            $query->whereExists(fn($q)=>$q->selectRaw('1')->from('question_sub_filter as qsf')
                ->whereColumn('qsf.question_id','questions.id')->where('qsf.sub_filter_id',$v));
        }
        if ($request->filled('created_by')) $query->where('created_by',(int)$request->input('created_by'));
        if ($request->filled('date_from')) $query->whereDate('created_at','>=',$request->input('date_from'));
        if ($request->filled('date_to')) $query->whereDate('created_at','<=',$request->input('date_to'));
        return $query;
    }
    private function pickerFilters(Request $request): array
    {
        return $request->validate([
            'q'=>'nullable|string|max:200',
            'course_id'=>'nullable|integer|min:1',
            'department_id'=>'nullable|integer|min:1',
            'sub_filter_id'=>'nullable|integer|min:1',
            'semester_id'=>'nullable|integer|min:1',
            'subject_id'=>'nullable|integer|min:1',
            'chapter_id'=>'nullable|integer|min:1',
            'created_by'=>'nullable|integer|min:1',
            'date_from'=>'nullable|date',
            'date_to'=>'nullable|date|after_or_equal:date_from',
        ]);
    }
    public function picker(Request $request,string $level,int $id)
    {
        $table=$this->table($level);
        $topic=DB::table($table)->where('id',$id)->first();abort_unless($topic,404);
        $kind=$this->kindOf($level,$id);
        abort_unless(in_array($kind,['department','non-department'],true),404);
        // Dedicated read-only linked questions page; NEVER show the main picker here.
        if ($request->query('view') === 'linked') {
            abort_unless($this->questionTable(),404,'Main questions table not found.');
            $questions=DB::table('questions as q')
              ->join('lebd_exam_routine_question_links as l','l.question_id','=','q.id')
              ->where('l.topic_level',$level)->where('l.topic_id',$id)
              ->select('q.*')->distinct()->orderByDesc('q.id')->paginate(25)->withQueryString();
            $optionItems=collect();
            if (Schema::hasTable('mcq_items') && $questions->count()) {
                $optionItems=DB::table('mcq_items')->whereIn('question_id',$questions->pluck('id')->all())
                  ->select('id','question_id','item','correct_answer','image')->orderBy('id')->get()->groupBy('question_id');
            }
            return view('admin.exam-section.routine-topic-linked-questions',compact('level','topic','kind','questions','optionItems'));
        }
        $this->pickerFilters($request);
        $sourceTable=$this->questionTable();$questions=collect();
        $filters=[];
        if($sourceTable){
            $questionQuery=$this->filteredQuestions($request);
            if($request->query('view')==='linked'){
                $questionQuery->whereIn('questions.id',DB::table('lebd_exam_routine_question_links')
                    ->where('topic_level',$level)->where('topic_id',$id)->select('question_id'));
            }
            $questions=$questionQuery->select('questions.*')->orderByDesc('questions.id')
                ->paginate(25)->withQueryString();
        }
        $courses=Schema::hasTable('courses')?DB::table('courses')->where('status','Active')->select('id','name')->orderBy('name')->get():collect();
        $departments=Schema::hasTable('departments')?DB::table('departments')->where('status','Active')->select('id','name')->orderBy('name')->get():collect();
        $courseDepartmentMap=Schema::hasTable('course_department')?DB::table('course_department')->select('course_id','department_id')->get():collect();
        $semesters=Schema::hasTable('semesters')?DB::table('semesters')->select('id','name')->orderBy('name')->get():collect();
        $subjects=Schema::hasTable('subjects')?DB::table('subjects')->where('status','Active')->select('id','name')->orderBy('name')->get():collect();
        // Main-bank subject associations (not the independent Exam Section topic tree).
        $subjectQuestionLinks=Schema::hasTable('question_subject')?DB::table('question_subject')->distinct()->select('question_id','subject_id')->get():collect();
        $departmentQuestionLinks=Schema::hasTable('department_question')?DB::table('department_question')->distinct()->select('question_id','department_id')->get():collect();
        $courseQuestionLinks=Schema::hasTable('course_question')?DB::table('course_question')->distinct()->select('question_id','course_id')->get():collect();
        $chapters=Schema::hasTable('chapters')?DB::table('chapters')->where('status','Active')->select('id','name','subject_id')->orderBy('name')->get():collect();
        $chapterQuestionLinks=Schema::hasTable('chapter_question')?DB::table('chapter_question')->distinct()->select('question_id','chapter_id')->get():collect();
        $optionItems=collect();
        if(Schema::hasTable('mcq_items') && $questions->count()) {
            $optionItems=DB::table('mcq_items')->whereIn('question_id',$questions->pluck('id')->all())
                ->select('id','question_id','item','correct_answer','image')->orderBy('id')->get()->groupBy('question_id');
        }
        $subFilters=Schema::hasTable('sub_filters')?DB::table('sub_filters')->select('id','name','filter_id')->orderBy('name')->get():collect();
        $filterGroups=Schema::hasTable('filters')?DB::table('filters')->select('id','name')->orderBy('order_number')->get():collect();
        $creators=Schema::hasTable('users')?DB::table('users')->whereIn('id',DB::table('questions')->whereNotNull('created_by')->select('created_by'))
            ->select('id','name')->orderBy('name')->get():collect();
        $linked=DB::table('lebd_exam_routine_question_links')->where('topic_level',$level)
            ->where('topic_id',$id)->pluck('question_id')->map(fn($v)=>(int)$v)->all();
        return view('admin.exam-section.routine-topic-questions',compact('level','topic','kind','sourceTable',
            'questions','linked','courses','departments','courseDepartmentMap','semesters','subjects','chapters','subjectQuestionLinks','departmentQuestionLinks','courseQuestionLinks','chapterQuestionLinks','optionItems','subFilters','filterGroups','creators'));
    }
    public function attach(Request $request,string $level,int $id)
    {
        $this->table($level);
        abort_unless($this->kindOf($level,$id)===$this->kind($request),404);
        abort_unless($this->questionTable(),422,'Main questions table not found.');
        $allResults=$request->boolean('all_results');
        $filters=$this->pickerFilters($request);
        if($allResults){
            $query=$this->filteredQuestions($request);
            $total=(clone $query)->count();
            abort_if($total>10000,422,'Too many results. Narrow filters to 10,000 questions or fewer.');
            $ids=$query->orderBy('id')->pluck('id')->all();
        } else {
            $data=$request->validate(['question_ids'=>['required','array','min:1','max:25'],
                'question_ids.*'=>['integer','distinct','min:1']]);
            $ids=array_map('intval',$data['question_ids']);
            $found=DB::table('questions')->whereIn('id',$ids)->pluck('id')->all();
            abort_unless(count($found)===count($ids),422,'Some questions do not exist.');
        }
        foreach(array_chunk($ids,400) as $chunk){
            $rows=[];
            foreach($chunk as $qid)$rows[]=['topic_level'=>$level,'topic_id'=>$id,'question_id'=>(int)$qid,'created_at'=>now()];
            DB::table('lebd_exam_routine_question_links')->insertOrIgnore($rows);
        }
        if ($request->boolean('finish')) {
            return redirect()->route('admin.exam-section.routine-topic.index', ['level'=>$level,'kind'=>$this->kind($request)])
                ->with('success',count($ids).' original question IDs linked; original MCQ answers remain in mcq_items.');
        }
        return back()->with('success',count($ids).' original questions processed for linking; duplicates ignored.');
    }
    public function detach(string $level,int $id,int $questionId)
    {
      $this->table($level);
      abort_unless($this->kindOf($level,$id) !== null, 404);
      DB::table('lebd_exam_routine_question_links')->where('topic_level',$level)->where('topic_id',$id)->where('question_id',$questionId)->delete();
      return back()->with('success','Question link removed. Original question remains intact.');
    }
}
