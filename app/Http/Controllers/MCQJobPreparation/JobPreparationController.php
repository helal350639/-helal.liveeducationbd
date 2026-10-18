<?php

namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JobPreparationController extends Controller
{
    private function checkKind(string $kind): void { abort_unless(in_array($kind, ['department', 'non-department'], true), 404); }
    private function category(string $kind, int $id)
    {
        $this->checkKind($kind);
        return DB::table('job_prep_categories')->where('kind', $kind)->where('id', $id)->first() ?? abort(404);
    }
    private function subject(string $kind, int $category, int $id)
    {
        $this->category($kind, $category);
        return DB::table('job_prep_subjects')->where('category_id', $category)->where('id', $id)->first() ?? abort(404);
    }
    private function chapter(string $kind, int $category, int $subject, int $id)
    {
        $this->subject($kind, $category, $subject);
        return DB::table('job_prep_chapters')->where('subject_id', $subject)->where('id', $id)->first() ?? abort(404);
    }
    private function validateName(Request $request, string $table, string $parent, $parentValue, ?int $ignore = null): array
    {
        $unique = Rule::unique($table, 'name')->where($parent, $parentValue);
        if ($ignore) $unique->ignore($ignore);
        return $request->validate([
            'name' => ['required','string','max:180', $unique],
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'status' => ['required',Rule::in(['Active','Inactive'])],
        ]);
    }
    private function payload(array $data): array
    {
        return ['name'=>trim($data['name']),'status'=>$data['status'],'sort_order'=>(int)($data['sort_order'] ?? 0),'updated_at'=>now()];
    }
    public function categories(Request $request, string $kind)
    {
        $this->checkKind($kind);
        $search = trim((string) $request->query('search', ''));
        $query = DB::table('job_prep_categories')->where('kind', $kind);
        if ($search !== '') $query->where('name', 'like', '%' . $search . '%');
        if (in_array($request->query('status'), ['Active','Inactive'], true)) $query->where('status', $request->query('status'));
        if ($request->query('sort') === 'latest') $query->orderByDesc('id');
        elseif ($request->query('sort') === 'name') $query->orderBy('name');
        else $query->orderBy('sort_order')->orderBy('id');
        $rows = $query->paginate(25)->withQueryString();
        $ids = $rows->getCollection()->pluck('id')->all();
        $subjectCounts = DB::table('job_prep_subjects')->whereIn('category_id', $ids)
            ->select('category_id', DB::raw('COUNT(*) as aggregate'))->groupBy('category_id')->pluck('aggregate', 'category_id');
        $directCounts = DB::table('job_prep_question_links')->where('level','category')->whereIn('entity_id',$ids)
            ->select('entity_id', DB::raw('COUNT(*) as aggregate'))->groupBy('entity_id')->pluck('aggregate','entity_id');
        return view('admin.job-preparation.category-index', compact('rows','kind','search','subjectCounts','directCounts'));
    }
    // One category dashboard: category questions, subjects, chapters, and all linked MCQs.
    // Questions are always read LIVE from the main questions table; no copies are stored.
    public function categoryOverview(Request $request, string $kind, int $category)
    {
        $cat = $this->category($kind,$category);
        $subjectCount = DB::table('job_prep_subjects')->where('category_id',$category)->count();
        $subjectIds = DB::table('job_prep_subjects')->where('category_id',$category)->pluck('id');
        $chapterCount = DB::table('job_prep_chapters')->whereIn('subject_id',$subjectIds)->count();
        $chapterIds = DB::table('job_prep_chapters')->whereIn('subject_id',$subjectIds)->pluck('id');
        $totalLinks = DB::table('job_prep_question_links')->where(function($q) use ($category,$subjectIds,$chapterIds) {
            $q->where(function($a)use($category){$a->where('level','category')->where('entity_id',$category);});
            if ($subjectIds->isNotEmpty()) $q->orWhere(function($a)use($subjectIds){$a->where('level','subject')->whereIn('entity_id',$subjectIds);});
            if ($chapterIds->isNotEmpty()) $q->orWhere(function($a)use($chapterIds){$a->where('level','chapter')->whereIn('entity_id',$chapterIds);});
        })->count();
        $directCount = DB::table('job_prep_question_links')->where('level','category')->where('entity_id',$category)->count();
        $search = trim((string)$request->query('search',''));
        $subjectQuery = DB::table('job_prep_subjects')->where('category_id',$category);
        if ($search !== '') $subjectQuery->where('name','like','%'.$search.'%');
        if (in_array($request->query('status'), ['Active','Inactive'], true)) $subjectQuery->where('status', $request->query('status'));
        if ($request->query('sort') === 'latest') $subjectQuery->orderByDesc('id');
        elseif ($request->query('sort') === 'name') $subjectQuery->orderBy('name');
        else $subjectQuery->orderBy('sort_order')->orderBy('id');
        $subjects = $subjectQuery->paginate(20)->withQueryString();
        $ids = $subjects->getCollection()->pluck('id')->all();
        $chapterCounts = DB::table('job_prep_chapters')->whereIn('subject_id',$ids)->select('subject_id',DB::raw('COUNT(*) as aggregate'))->groupBy('subject_id')->pluck('aggregate','subject_id');
        $questionCounts = DB::table('job_prep_question_links')->where('level','subject')->whereIn('entity_id',$ids)->select('entity_id',DB::raw('COUNT(*) as aggregate'))->groupBy('entity_id')->pluck('aggregate','entity_id');
        return view('admin.job-preparation.overview', compact('kind','cat','subjectCount','chapterCount','totalLinks','directCount','subjects','chapterCounts','questionCounts','search'));
    }

    public function createCategory(string $kind)
    {
        $this->checkKind($kind);
        return view('admin.job-preparation.category-form', ['kind' => $kind, 'category' => null]);
    }

    public function editCategory(string $kind, int $category)
    {
        $row = $this->category($kind, $category);
        return view('admin.job-preparation.category-form', ['kind' => $kind, 'category' => $row]);
    }

    // Standalone subject creation from the category dashboard.
    public function createSubjectGlobal(string $kind)
    {
        $this->checkKind($kind);
        $categories = DB::table('job_prep_categories')->where('kind', $kind)->orderBy('name')->get();
        return view('admin.job-preparation.subject-create', compact('kind', 'categories'));
    }

    public function storeSubjectGlobal(Request $request, string $kind)
    {
        $this->checkKind($kind);
        $request->validate(['category_id' => 'required|integer']);
        $category = $this->category($kind, (int) $request->input('category_id'));
        $data = $this->validateName($request, 'job_prep_subjects', 'category_id', $category->id);
        DB::table('job_prep_subjects')->insert($this->payload($data) + ['category_id'=>$category->id, 'created_at'=>now()]);
        return redirect()->route('admin.job-preparation.categories', ['kind'=>$kind])->with('success', 'Subject added under '.$category->name.'.');
    }

    // Standalone chapter creation, with a verified subject/category relationship.
    public function createChapterGlobal(string $kind)
    {
        $this->checkKind($kind);
        $categories = DB::table('job_prep_categories')->where('kind', $kind)->orderBy('name')->get();
        $subjects = DB::table('job_prep_subjects as s')->join('job_prep_categories as c', 'c.id','=','s.category_id')
            ->where('c.kind', $kind)->select('s.id','s.name','s.category_id')->orderBy('s.name')->get();
        return view('admin.job-preparation.chapter-create', compact('kind', 'categories','subjects'));
    }

    public function storeChapterGlobal(Request $request, string $kind)
    {
        $this->checkKind($kind);
        $request->validate(['category_id'=>'required|integer', 'subject_id'=>'required|integer']);
        $category = $this->category($kind, (int) $request->input('category_id'));
        $subject = $this->subject($kind, (int) $category->id, (int) $request->input('subject_id'));
        $data = $this->validateName($request, 'job_prep_chapters', 'subject_id', $subject->id);
        DB::table('job_prep_chapters')->insert($this->payload($data) + ['subject_id'=>$subject->id, 'created_at'=>now()]);
        return redirect()->route('admin.job-preparation.categories', ['kind'=>$kind])->with('success', 'Chapter added under '.$subject->name.'.');
    }

    // Icon styling is stored on categories only; no changes to main questions.
    private function categoryAppearance(Request $request): array
    {
        $data = $request->validate([
            'icon' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'icon_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'card_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        return [
            'icon' => strtolower($data['icon']),
            'icon_color' => strtoupper($data['icon_color']),
            'card_color' => strtoupper($data['card_color']),
        ];
    }

    public function storeCategory(Request $request, string $kind)
    {
        $this->checkKind($kind);
        $data=$this->validateName($request,'job_prep_categories','kind',$kind);
        $appearance=$this->categoryAppearance($request);
        DB::table('job_prep_categories')->insert(array_merge($this->payload($data), $appearance, ['kind'=>$kind,'created_at'=>now()]));
        return redirect()->route('admin.job-preparation.categories', ['kind' => $kind])->with('success','Category added successfully.');
    }
    public function updateCategory(Request $request,string $kind,int $category)
    {
        $this->category($kind,$category);
        $data=$this->validateName($request,'job_prep_categories','kind',$kind,$category);
        $appearance=$this->categoryAppearance($request);
        DB::table('job_prep_categories')->where('id',$category)->update(array_merge($this->payload($data), $appearance));
        return redirect()->route('admin.job-preparation.categories', ['kind' => $kind])->with('success','Category updated.');
    }
    public function destroyCategory(string $kind,int $category)
    {
        $this->category($kind,$category);
        $hasSubjects = DB::table('job_prep_subjects')->where('category_id',$category)->exists();
        $hasQuestions = DB::table('job_prep_question_links')->where('level','category')->where('entity_id',$category)->exists();
        if ($hasSubjects || $hasQuestions) return back()->with('error','Cannot delete: remove or move the subjects and linked questions first. Alternatively set this category Inactive.');
        DB::table('job_prep_categories')->where('id',$category)->delete();
        return back()->with('success','Empty category deleted.');
    }
    public function subjects(Request $request, string $kind, int $category)
    {
        $cat = $this->category($kind, $category);
        $search = trim((string) $request->query('search',''));
        $query = DB::table('job_prep_subjects')->where('category_id',$category);
        if ($search !== '') $query->where('name','like','%'.$search.'%');
        if (in_array($request->query('status'), ['Active','Inactive'], true)) $query->where('status', $request->query('status'));
        if ($request->query('sort') === 'latest') $query->orderByDesc('id');
        elseif ($request->query('sort') === 'name') $query->orderBy('name');
        else $query->orderBy('sort_order')->orderBy('id');
        $rows = $query->paginate(25)->withQueryString();
        $ids = $rows->getCollection()->pluck('id')->all();
        $chapterCounts = DB::table('job_prep_chapters')->whereIn('subject_id',$ids)
            ->select('subject_id',DB::raw('COUNT(*) as aggregate'))->groupBy('subject_id')->pluck('aggregate','subject_id');
        $directCounts = DB::table('job_prep_question_links')->where('level','subject')->whereIn('entity_id',$ids)
            ->select('entity_id',DB::raw('COUNT(*) as aggregate'))->groupBy('entity_id')->pluck('aggregate','entity_id');
        $navCategories = DB::table('job_prep_categories')->where('kind',$kind)->orderBy('name')->get(['id','name']);
        return view('admin.job-preparation.manage', compact('rows','kind','search','chapterCounts','directCounts','navCategories') + ['level'=>'subject','parent'=>$cat,'crumbs'=>[$cat]]);
    }
    public function storeSubject(Request $request,string $kind,int $category)
    {
        $this->category($kind,$category);
        $data=$this->validateName($request,'job_prep_subjects','category_id',$category);
        DB::table('job_prep_subjects')->insert($this->payload($data)+['category_id'=>$category,'created_at'=>now()]);
        return back()->with('success','Subject added.');
    }
    public function updateSubject(Request $request,string $kind,int $category,int $subject)
    {
        $this->subject($kind,$category,$subject);
        $data=$this->validateName($request,'job_prep_subjects','category_id',$category,$subject);
        DB::table('job_prep_subjects')->where('id',$subject)->update($this->payload($data));
        return back()->with('success','Subject updated.');
    }
    public function destroySubject(string $kind,int $category,int $subject)
    {
        $this->subject($kind,$category,$subject);
        $hasChapters = DB::table('job_prep_chapters')->where('subject_id',$subject)->exists();
        $hasQuestions = DB::table('job_prep_question_links')->where('level','subject')->where('entity_id',$subject)->exists();
        if ($hasChapters || $hasQuestions) return back()->with('error','Cannot delete: subject has chapters or linked questions. Set Inactive to preserve content.');
        DB::table('job_prep_subjects')->where('id',$subject)->delete();
        return back()->with('success','Empty subject deleted.');
    }
    public function chapters(Request $request, string $kind, int $category, int $subject)
    {
        $sub = $this->subject($kind,$category,$subject);
        $cat = $this->category($kind,$category);
        $search = trim((string) $request->query('search',''));
        $query = DB::table('job_prep_chapters')->where('subject_id',$subject);
        if ($search !== '') $query->where('name','like','%'.$search.'%');
        if (in_array($request->query('status'), ['Active','Inactive'], true)) $query->where('status', $request->query('status'));
        if ($request->query('sort') === 'latest') $query->orderByDesc('id');
        elseif ($request->query('sort') === 'name') $query->orderBy('name');
        else $query->orderBy('sort_order')->orderBy('id');
        $rows = $query->paginate(25)->withQueryString();
        $ids = $rows->getCollection()->pluck('id')->all();
        $directCounts = DB::table('job_prep_question_links')->where('level','chapter')->whereIn('entity_id',$ids)
            ->select('entity_id',DB::raw('COUNT(*) as aggregate'))->groupBy('entity_id')->pluck('aggregate','entity_id');
        $navCategories = DB::table('job_prep_categories')->where('kind',$kind)->orderBy('name')->get(['id','name']);
        $navSubjects = DB::table('job_prep_subjects')->where('category_id',$category)->orderBy('name')->get(['id','name']);
        return view('admin.job-preparation.manage', compact('rows','kind','search','directCounts','navCategories','navSubjects') + ['level'=>'chapter','parent'=>$sub,'crumbs'=>[$cat,$sub]]);
    }
    public function storeChapter(Request $request,string $kind,int $category,int $subject)
    {
        $this->subject($kind,$category,$subject);
        $data=$this->validateName($request,'job_prep_chapters','subject_id',$subject);
        DB::table('job_prep_chapters')->insert($this->payload($data)+['subject_id'=>$subject,'created_at'=>now()]);
        return back()->with('success','Chapter added.');
    }
    public function updateChapter(Request $request,string $kind,int $category,int $subject,int $chapter)
    {
        $this->chapter($kind,$category,$subject,$chapter);
        $data=$this->validateName($request,'job_prep_chapters','subject_id',$subject,$chapter);
        DB::table('job_prep_chapters')->where('id',$chapter)->update($this->payload($data));
        return back()->with('success','Chapter updated.');
    }
    public function destroyChapter(string $kind,int $category,int $subject,int $chapter)
    {
        $this->chapter($kind,$category,$subject,$chapter);
        if (DB::table('job_prep_question_links')->where('level','chapter')->where('entity_id',$chapter)->exists())
            return back()->with('error','Cannot delete: chapter contains linked questions. Set Inactive to preserve content.');
        DB::table('job_prep_chapters')->where('id',$chapter)->delete();
        return back()->with('success','Empty chapter deleted.');
    }
    private function target(string $kind,string $level,int $entity)
    {
        $this->checkKind($kind);
        if($level==='category') return $this->category($kind,$entity);
        if($level==='subject') {
            $sub=DB::table('job_prep_subjects')->where('id',$entity)->first() ?? abort(404);
            $this->category($kind,(int)$sub->category_id);
            return $sub;
        }
        if($level==='chapter') {
            $chapter=DB::table('job_prep_chapters')->where('id',$entity)->first() ?? abort(404);
            $sub=DB::table('job_prep_subjects')->where('id',$chapter->subject_id)->first() ?? abort(404);
            $this->category($kind,(int)$sub->category_id);
            return $chapter;
        }
        abort(404);
    }
    public function questions(Request $request,string $kind,string $level,int $entity)
    {
        $target = $this->target($kind, $level, $entity);

        // The link order is the reading order: newest attached MCQ first.
        // Query source questions live so main database edits are reflected immediately.
        $rows = DB::table('job_prep_question_links as l')
            ->join('questions as q', 'q.id', '=', 'l.question_id')
            ->where('l.level', $level)
            ->where('l.entity_id', $entity)
            ->where('q.type', 'MCQ')
            ->when(in_array($request->query('status'), ['Active','Inactive'], true), function($q) use ($request){$q->where('q.status',$request->query('status'));})
            ->when(trim((string)$request->query('search','')) !== '', function($q) use ($request){$term=trim((string)$request->query('search')); $q->where(function($sub) use ($term){$sub->where('q.title','like','%'.$term.'%'); if(ctype_digit($term)) $sub->orWhere('q.id',(int)$term);});})
            ->select('l.id as link_id', 'q.id', 'q.title', 'q.image', 'q.answer', 'q.explanation', 'q.solution', 'q.status', 'l.created_at')
            ->orderByDesc('l.id')
            ->paginate(25)
            ->withQueryString();

        $questionIds = $rows->getCollection()->pluck('id')->all();
        $items = DB::table('mcq_items')
            ->whereIn('question_id', $questionIds)
            ->orderBy('id')
            ->get(['id', 'question_id', 'item', 'correct_answer', 'image', 'image_style'])
            ->groupBy('question_id');

        return view('admin.job-preparation.questions', compact('kind', 'level', 'entity', 'target', 'rows', 'items'));
    }
    public function picker(Request $request,string $kind,string $level,int $entity)
    {
        $target = $this->target($kind, $level, $entity);
        $perPage = (int) $request->query('per_page',25);
        if (!in_array($perPage,[25,50,100],true)) $perPage=25;

        // Existing Question Database categories are separate from Job Preparation categories.
        // Use the same relationships as QuestionCtrl::viewQuestionPost.
        $query = \App\Models\Question::query()
            ->where('type','MCQ')->where('status','Active');
        $map = [
            'course_id'=>['courses','course_id'],
            'department_id'=>['departments','department_id'],
            'semester_id'=>['semesters','semester_id'],
            'subject_id'=>['subjects','subject_id'],
            'chapter_id'=>['chapters','chapter_id'],
        ];
        foreach($map as $param=>$rel){
            if($request->filled($param) && ctype_digit((string)$request->query($param))){
                $id=(int)$request->query($param);
                $query->whereHas($rel[0], function($q)use($rel,$id){$q->where($rel[1],$id);});
            }
        }
        if($request->filled('created_by') && ctype_digit((string)$request->query('created_by'))){
            $query->where('created_by',(int)$request->query('created_by'));
        }
        if($request->filled('start') && preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$request->query('start'))){
            $query->whereDate('created_at','>=',$request->query('start'));
        }
        if($request->filled('end') && preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$request->query('end'))){
            $query->whereDate('created_at','<=',$request->query('end'));
        }
        $search=trim((string)$request->query('search',''));
        if($search!==''){
            $query->where(function($q)use($search){
                $q->where('title','like','%'.$search.'%');
                if(ctype_digit($search)) $q->orWhere('id',(int)$search);
            });
        }
        $filterIds=array_values(array_filter(array_map('intval',(array)$request->query('filter_ids',[])),fn($id)=>$id>0));
        if($filterIds){
            $query->whereHas('filters',function($q)use($filterIds){$q->whereIn('sub_filter_id',$filterIds);});
        }
        if($request->filled('syllabus') && ctype_digit((string)$request->query('syllabus'))){
            $syllabus=\App\Models\Syllabus::find((int)$request->query('syllabus'));
            if(!$syllabus) $query->whereRaw('1=0');
            else $query->whereIn('id',$syllabus->questions()->select('questions.id'));
        }
        // Batch is a parent selector for the syllabus dropdown in the original View Questions page.
        // Do not invent a direct question-batch relationship.
        $query->whereNotIn('id',DB::table('job_prep_question_links')
            ->select('question_id')->where('level',$level)->where('entity_id',$entity));
        $rows=$query->select('id','title','image','explanation')->orderByDesc('id')->paginate($perPage)->withQueryString();
        $ids=$rows->getCollection()->pluck('id')->all();
        $optionGroups=DB::table('mcq_items')->whereIn('question_id',$ids)->orderBy('id')
            ->get(['question_id','item','correct_answer','image'])->groupBy('question_id');
        $courses=\App\Models\Course::where('status','Active')->orderBy('name')->get(['id','name']);
        $departments=\App\Models\Department::where('status','Active')->orderBy('name')->get(['id','name']);
        $semesters=\App\Models\Semester::where('status','Active')->orderBy('name')->get(['id','name']);
        $subjects=\App\Models\Subject::where('status','Active')->orderBy('name')->get(['id','name']);
        $chapters=\App\Models\Chapter::where('status','Active')->orderBy('name')->get(['id','name']);
        $users=\App\Models\User::where('status','Active')->orderBy('name')->get(['id','name']);
        $filters=\App\Models\Filter::with('SubFilter')->orderBy('order_number')->get();
        return view('admin.job-preparation.picker',compact('kind','level','entity','target','rows','search','optionGroups',
            'courses','departments','semesters','subjects','chapters','users','filters','filterIds'));
    }
    public function attach(Request $request,string $kind,string $level,int $entity)
    {
        $this->target($kind,$level,$entity);
        $data=$request->validate(['question_ids'=>'required|array|min:1|max:200','question_ids.*'=>'required|integer|distinct|exists:questions,id']);
        $eligible=DB::table('questions')->whereIn('id',$data['question_ids'])->where('type','MCQ')->where('status','Active')->pluck('id');
        if($eligible->count()!==count($data['question_ids'])) return back()->withErrors(['question_ids'=>'Only Active MCQ questions are allowed.']);
        $records=$eligible->map(fn($id)=>['level'=>$level,'entity_id'=>$entity,'question_id'=>$id,'created_at'=>now()])->all();
        DB::table('job_prep_question_links')->insertOrIgnore($records);
        return redirect()->route('admin.job-preparation.questions',compact('kind','level','entity'))->with('success','Selected questions linked successfully.');
    }
    public function detach(string $kind,string $level,int $entity,int $question)
    {
        $this->target($kind,$level,$entity);
        DB::table('job_prep_question_links')->where('level',$level)->where('entity_id',$entity)->where('question_id',$question)->delete();
        return back()->with('success','Question unlinked. Original question remains in main database.');
    }
}
