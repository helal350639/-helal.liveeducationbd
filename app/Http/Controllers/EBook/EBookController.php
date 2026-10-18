<?php
namespace App\Http\Controllers\EBook;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EBookController extends Controller {
 private function kind(string $kind): void {abort_unless(in_array($kind,['department','non-department'],true),404);}
 private function category(string $kind,int $id,bool $public=false): object {
  $this->kind($kind);$q=DB::table('lebd_ebook_categories')->where('kind',$kind)->where('id',$id);
  if($public)$q->where('status','Active');return $q->first() ?? abort(404);
 }
 private function chapter(string $kind,int $id,bool $public=false): array {
  $q=DB::table('lebd_ebook_chapters')->where('id',$id);if($public)$q->where('status','Active');
  $chapter=$q->first() ?? abort(404);$cat=$this->category($kind,(int)$chapter->category_id,$public);return [$cat,$chapter];
 }
 private function block(int $chapter,int $id): object {return DB::table('lebd_ebook_blocks')->where('chapter_id',$chapter)->where('id',$id)->first() ?? abort(404);}
 private function categoriesData(string $kind,bool $public=false) {
  $this->kind($kind);$q=DB::table('lebd_ebook_categories')->where('kind',$kind);
  if($public)$q->where('status','Active');return $q->orderBy('sort_order')->orderBy('id')->get();
 }
 public function categories(string $kind){$categories=$this->categoriesData($kind);$tocCounts=DB::table('lebd_ebook_chapters')->whereIn('category_id',$categories->pluck('id')->all())->select('category_id',DB::raw('COUNT(*) as total'))->groupBy('category_id')->pluck('total','category_id');return view('admin.ebook.categories',compact('kind','categories','tocCounts'));}
 public function storeCategory(Request $request,string $kind){$this->kind($kind);$d=$request->validate(['name'=>'required|string|max:200','icon'=>'nullable|string|max:80','icon_color'=>'nullable|regex:/^#[0-9A-Fa-f]{6}$/','card_color'=>'nullable|regex:/^#[0-9A-Fa-f]{6}$/','sort_order'=>'nullable|integer|min:0','status'=>'required|in:Active,Inactive','front_cover'=>'nullable|image|mimes:jpg,jpeg|max:8192','back_cover'=>'nullable|image|mimes:jpg,jpeg|max:8192','details_page'=>'nullable|image|mimes:jpg,jpeg|max:8192']);
  DB::table('lebd_ebook_categories')->insert(['kind'=>$kind,'name'=>$d['name'],'icon'=>$d['icon']?:'fa-book','icon_color'=>$d['icon_color']??'#1b72b9','card_color'=>$d['card_color']??'#eaf6ff','sort_order'=>$d['sort_order']??0,'status'=>$d['status'],'front_cover'=>$this->saveCover($request,'front_cover'),'back_cover'=>$this->saveCover($request,'back_cover'),'details_page'=>$this->saveCover($request,'details_page'),'created_at'=>now(),'updated_at'=>now()]);return back()->with('success','E-Book category created.');}
 public function updateCategory(Request $request,string $kind,int $category){$this->category($kind,$category);$d=$request->validate(['name'=>'required|string|max:200','icon'=>'nullable|string|max:80','icon_color'=>'nullable|regex:/^#[0-9A-Fa-f]{6}$/','card_color'=>'nullable|regex:/^#[0-9A-Fa-f]{6}$/','sort_order'=>'nullable|integer|min:0','status'=>'required|in:Active,Inactive','front_cover'=>'nullable|image|mimes:jpg,jpeg|max:8192','back_cover'=>'nullable|image|mimes:jpg,jpeg|max:8192','details_page'=>'nullable|image|mimes:jpg,jpeg|max:8192']);
  DB::table('lebd_ebook_categories')->where('id',$category)->update(['name'=>$d['name'],'icon'=>$d['icon']?:'fa-book','icon_color'=>$d['icon_color']??'#1b72b9','card_color'=>$d['card_color']??'#eaf6ff','sort_order'=>$d['sort_order']??0,'status'=>$d['status'],'updated_at'=>now()]+$this->coverUpdateFields($request,$category));return back()->with('success','Category updated.');}
 private function saveCover(Request $request,string $field): ?string {
  if(!$request->hasFile($field)) return null;
  $file=$request->file($field);
  $filename=\Illuminate\Support\Str::uuid()->toString().'.jpg';
  $directory=public_path('uploads/ebook/covers');
  if(!is_dir($directory) && !mkdir($directory,0755,true) && !is_dir($directory)) throw new \RuntimeException('Cover upload directory cannot be created');
  $file->move($directory,$filename);
  return 'uploads/ebook/covers/'.$filename;
 }
 private function coverUpdateFields(Request $request,int $category): array {
  $existing=DB::table('lebd_ebook_categories')->where('id',$category)->first(['front_cover','back_cover','details_page']);
  $result=[];
  foreach(['front_cover','back_cover','details_page'] as $field){
   if($request->hasFile($field)){
    $new=$this->saveCover($request,$field);
    $result[$field]=$new;
    $old=$existing->$field;
    if($old && str_starts_with($old,'uploads/ebook/covers/') && is_file(public_path($old))) @unlink(public_path($old));
   }
  }
  return $result;
 }
 public function deleteCategory(string $kind,int $category){$this->category($kind,$category);DB::table('lebd_ebook_categories')->where('id',$category)->delete();return back()->with('success','Category deleted.');}
 public function chapters(string $kind,int $category){
  $cat=$this->category($kind,$category);
  $chapters=DB::table('lebd_ebook_chapters')->where('category_id',$cat->id)->orderBy('sort_order')->orderBy('id')->get();
  $bookChapters=[];
  foreach($chapters as $chapterRow){
   $content=$this->content((int)$chapterRow->id);
   $bookChapters[]=['ch'=>$chapterRow,'blocks'=>$content['blocks'],'qs'=>$content['qs'],'opts'=>$content['opts']];
  }
  return view('admin.ebook.chapters',compact('kind','cat','chapters','bookChapters'));
 }
 public function storeChapter(Request $r,string $kind,int $category){$this->category($kind,$category);$d=$r->validate(['title'=>'required|string|max:255','sort_order'=>'nullable|integer|min:0','page_number'=>'nullable|integer|min:1|max:99999','status'=>'required|in:Active,Inactive']);DB::table('lebd_ebook_chapters')->insert(['category_id'=>$category,'title'=>$d['title'],'sort_order'=>$d['sort_order']??0,'page_number'=>$d['page_number']??null,'status'=>$d['status'],'created_at'=>now(),'updated_at'=>now()]);return back()->with('success','সূচিপত্রের অধ্যায় যোগ হয়েছে।');}
 public function updateChapter(Request $r,string $kind,int $category,int $chapter){$this->category($kind,$category);abort_unless(DB::table('lebd_ebook_chapters')->where('id',$chapter)->where('category_id',$category)->exists(),404);$d=$r->validate(['title'=>'required|string|max:255','sort_order'=>'nullable|integer|min:0','page_number'=>'nullable|integer|min:1|max:99999','status'=>'required|in:Active,Inactive']);DB::table('lebd_ebook_chapters')->where('id',$chapter)->update($d+['updated_at'=>now()]);return back()->with('success','অধ্যায় আপডেট হয়েছে।');}
 public function deleteChapter(string $kind,int $category,int $chapter){$this->category($kind,$category);DB::table('lebd_ebook_chapters')->where('id',$chapter)->where('category_id',$category)->delete();return back()->with('success','অধ্যায় মুছে দেওয়া হয়েছে।');}
 private function content(int $chapter){$blocks=DB::table('lebd_ebook_blocks')->where('chapter_id',$chapter)->orderBy('sort_order')->orderBy('id')->get();$ids=$blocks->where('block_type','database_mcq')->pluck('question_id')->filter()->all();$qs=DB::table('questions')->whereIn('id',$ids)->get()->keyBy('id');$opts=DB::table('mcq_items')->whereIn('question_id',$ids)->orderBy('id')->get()->groupBy('question_id');return compact('blocks','qs','opts');}
 public function editor(string $kind,int $chapter){[$cat,$ch]=$this->chapter($kind,$chapter);return view('admin.ebook.editor',array_merge(compact('kind','cat','ch'),$this->content($chapter)));}
 public function addBlock(Request $r,string $kind,int $chapter){$this->chapter($kind,$chapter);$d=$r->validate(['block_type'=>'required|in:heading,text,custom_question','title'=>'nullable|string|max:15000','content'=>'nullable|string|max:100000','options'=>'nullable|array|max:8','options.*'=>'nullable|string|max:2000','answer'=>'nullable|string|max:2000','explanation'=>'nullable|string|max:100000','position_after'=>'nullable|integer|min:0']);
  $type=$d['block_type'];if($type==='heading')Validator::make($d,['title'=>'required|string'])->validate();if($type==='text')Validator::make($d,['content'=>'required|string'])->validate();if($type==='custom_question')Validator::make($d,['title'=>'required|string','options'=>'required|array|min:2','answer'=>'required|string'])->validate();
  $sort=$this->newSort($chapter,(int)($d['position_after']??0));DB::table('lebd_ebook_blocks')->insert(['chapter_id'=>$chapter,'block_type'=>$type,'title'=>$d['title']??null,'content'=>$d['content']??null,'options_json'=>isset($d['options'])?json_encode(array_values(array_filter($d['options'],fn($x)=>trim((string)$x)!=='')),JSON_UNESCAPED_UNICODE):null,'answer'=>$d['answer']??null,'explanation'=>$d['explanation']??null,'sort_order'=>$sort,'created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Content added.');}
 private function newSort(int $chapter,int $after): int {
  return DB::transaction(function () use ($chapter,$after) {
   $entries=DB::table('lebd_ebook_blocks')->where('chapter_id',$chapter)->orderBy('sort_order')->orderBy('id')->pluck('id')->map(fn($id)=>(int)$id)->all();
   if ($after > 0) {
    $position=array_search($after,$entries,true);
    abort_if($position===false,404,'Insertion target not found in chapter.');
    $insertAt=$position+1;
   } else $insertAt=count($entries);
   // Normalize sequence first, including older rows with duplicate sort_order.
   foreach($entries as $i=>$id) DB::table('lebd_ebook_blocks')->where('id',$id)->update(['sort_order'=>-($i+1)]);
   foreach($entries as $i=>$id) DB::table('lebd_ebook_blocks')->where('id',$id)->update(['sort_order'=>$i+1]);
   // Gap at insertion point. Renumber backwards to avoid temporary duplicate order collisions.
   for($i=count($entries)-1;$i>=$insertAt;$i--) DB::table('lebd_ebook_blocks')->where('id',$entries[$i])->update(['sort_order'=>$i+2]);
   return $insertAt+1;
  });
 }
 public function updateBlock(Request $r,string $kind,int $chapter,int $block){$this->chapter($kind,$chapter);$item=$this->block($chapter,$block);abort_if($item->block_type==='database_mcq',403,'Main DB MCQs are read-only here.');$d=$r->validate(['title'=>'nullable|string|max:15000','content'=>'nullable|string|max:100000','options'=>'nullable|array|max:8','options.*'=>'nullable|string|max:2000','answer'=>'nullable|string|max:2000','explanation'=>'nullable|string|max:100000']);DB::table('lebd_ebook_blocks')->where('id',$block)->update(['title'=>$d['title']??null,'content'=>$d['content']??null,'options_json'=>isset($d['options'])?json_encode(array_values(array_filter($d['options'],fn($x)=>trim((string)$x)!=='')),JSON_UNESCAPED_UNICODE):$item->options_json,'answer'=>$d['answer']??null,'explanation'=>$d['explanation']??null,'updated_at'=>now()]);return back()->with('success','Content updated.');}
 public function deleteBlock(string $kind,int $chapter,int $block){$this->chapter($kind,$chapter);$this->block($chapter,$block);DB::table('lebd_ebook_blocks')->where('id',$block)->delete();return back()->with('success','Content deleted.');}
 public function moveBlock(Request $r,string $kind,int $chapter,int $block){$this->chapter($kind,$chapter);$this->block($chapter,$block);$direction=$r->validate(['direction'=>'required|in:up,down'])['direction'];$entries=DB::table('lebd_ebook_blocks')->where('chapter_id',$chapter)->orderBy('sort_order')->orderBy('id')->pluck('id')->all();$pos=array_search($block,$entries);$next=$direction==='up'?$pos-1:$pos+1;if($next>=0&&$next<count($entries)){[$entries[$pos],$entries[$next]]=[$entries[$next],$entries[$pos]];DB::transaction(function()use($entries){foreach($entries as $i=>$id)DB::table('lebd_ebook_blocks')->where('id',$id)->update(['sort_order'=>$i+1]);});}return back();}
 public function picker(Request $request,string $kind,int $chapter){
  [$cat,$ch]=$this->chapter($kind,$chapter);
  $perPage=(int)$request->query('per_page',25);
  if(!in_array($perPage,[25,50,100],true)) $perPage=25;
  $query=\App\Models\Question::query()->where('type','MCQ')->where('status','Active');
  $map=['course_id'=>['courses','course_id'],'department_id'=>['departments','department_id'],'semester_id'=>['semesters','semester_id'],'subject_id'=>['subjects','subject_id'],'chapter_id'=>['chapters','chapter_id']];
  foreach($map as $param=>$rel){
   if($request->filled($param)&&ctype_digit((string)$request->query($param))){
    $id=(int)$request->query($param);
    $query->whereHas($rel[0],function($q)use($rel,$id){$q->where($rel[1],$id);});
   }
  }
  if($request->filled('created_by')&&ctype_digit((string)$request->query('created_by')))$query->where('created_by',(int)$request->query('created_by'));
  foreach(['start'=>'>=','end'=>'<='] as $field=>$operator){
   if($request->filled($field)&&preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$request->query($field)))$query->whereDate('created_at',$operator,$request->query($field));
  }
  $search=trim((string)$request->query('search',''));
  if($search!=='')$query->where(function($q)use($search){$q->where('title','like','%'.$search.'%');if(ctype_digit($search))$q->orWhere('id',(int)$search);});
  $filterIds=array_values(array_unique(array_filter(array_map('intval',(array)$request->query('filter_ids',[])),fn($id)=>$id>0)));
  if($filterIds)$query->whereHas('filters',function($q)use($filterIds){$q->whereIn('sub_filter_id',$filterIds);});
  if($request->filled('syllabus')&&ctype_digit((string)$request->query('syllabus'))){
   $syllabus=\App\Models\Syllabus::find((int)$request->query('syllabus'));
   if(!$syllabus)$query->whereRaw('1=0');
   else $query->whereIn('id',$syllabus->questions()->select('questions.id'));
  }
  // Batch only feeds the dependent syllabus selector. No unsupported question/batch relation.
  $query->whereNotIn('id',DB::table('lebd_ebook_blocks')->where('chapter_id',$chapter)->where('block_type','database_mcq')->whereNotNull('question_id')->select('question_id'));
  $rows=$query->select('id','title','image','explanation')->orderByDesc('id')->paginate($perPage)->withQueryString();
  $ids=$rows->getCollection()->pluck('id')->all();
  $optionGroups=DB::table('mcq_items')->whereIn('question_id',$ids)->orderBy('id')->get(['question_id','item','correct_answer','image'])->groupBy('question_id');
  $courses=\App\Models\Course::where('status','Active')->orderBy('name')->get(['id','name']);
  $departments=\App\Models\Department::where('status','Active')->orderBy('name')->get(['id','name']);
  $semesters=\App\Models\Semester::where('status','Active')->orderBy('name')->get(['id','name']);
  $subjects=\App\Models\Subject::where('status','Active')->orderBy('name')->get(['id','name']);
  $chapters=\App\Models\Chapter::where('status','Active')->orderBy('name')->get(['id','name']);
  $users=\App\Models\User::where('status','Active')->orderBy('name')->get(['id','name']);
  $filters=\App\Models\Filter::with('SubFilter')->orderBy('order_number')->get();
  return view('admin.ebook.picker',compact('kind','cat','ch','rows','search','optionGroups','courses','departments','semesters','subjects','chapters','users','filters','filterIds'));
 }
 public function attachQuestions(Request $r,string $kind,int $chapter){$this->chapter($kind,$chapter);$d=$r->validate(['question_ids'=>'required|array|min:1|max:100','question_ids.*'=>'required|integer|distinct']);$ids=DB::table('questions')->where('type','MCQ')->where('status','Active')->whereIn('id',$d['question_ids'])->pluck('id');abort_unless($ids->count()===count($d['question_ids']),422);DB::transaction(function()use($chapter,$ids){$max=(int)DB::table('lebd_ebook_blocks')->where('chapter_id',$chapter)->max('sort_order');foreach($ids as $id){if(DB::table('lebd_ebook_blocks')->where('chapter_id',$chapter)->where('block_type','database_mcq')->where('question_id',$id)->exists())continue;DB::table('lebd_ebook_blocks')->insert(['chapter_id'=>$chapter,'block_type'=>'database_mcq','question_id'=>$id,'sort_order'=>++$max,'created_at'=>now(),'updated_at'=>now()]);}});return redirect()->route('admin.ebook.editor',compact('kind','chapter'))->with('success','Database MCQ added.');}
 public function adminPreview(string $kind,int $chapter){[$cat,$ch]=$this->chapter($kind,$chapter);$preview=true;return view('ebook.read',array_merge(compact('kind','cat','ch','preview'),$this->content($chapter)));}
 // Complete category book: every TOC chapter begins on its own A4 sheet.
 public function fullBook(string $kind,int $category){
  $cat=$this->category($kind,$category,false);
  $chapters=DB::table('lebd_ebook_chapters')->where('category_id',$category)
   ->orderBy('sort_order')->orderBy('id')->get();
  $bookChapters=[];
  foreach($chapters as $ch){
   $data=$this->content((int)$ch->id);
   $bookChapters[]=['ch'=>$ch,'blocks'=>$data['blocks'],'qs'=>$data['qs'],'opts'=>$data['opts']];
  }
  $preview=true;
  return view('ebook.full-book',compact('kind','cat','bookChapters','preview'));
 }
 public function publicFullBook(string $kind,int $category){
  $cat=$this->category($kind,$category,true);
  $chapters=DB::table('lebd_ebook_chapters')->where('category_id',$category)
   ->where('status','Active')->orderBy('sort_order')->orderBy('id')->get();
  $bookChapters=[];
  foreach($chapters as $ch){
   $data=$this->content((int)$ch->id);
   $bookChapters[]=['ch'=>$ch,'blocks'=>$data['blocks'],'qs'=>$data['qs'],'opts'=>$data['opts']];
  }
  $preview=false;
  return view('ebook.full-book',compact('kind','cat','bookChapters','preview'));
 }
 public function publicCategories(string $kind){$categories=$this->categoriesData($kind,true);return view('ebook.categories',compact('kind','categories'));}
 public function publicChapters(string $kind,int $category){$cat=$this->category($kind,$category,true);$chapters=DB::table('lebd_ebook_chapters')->where('category_id',$category)->where('status','Active')->orderBy('sort_order')->orderBy('id')->get();return view('ebook.chapters',compact('kind','cat','chapters'));}
 public function read(string $kind,int $category,int $chapter){[$cat,$ch]=$this->chapter($kind,$chapter,true);abort_unless((int)$cat->id===$category,404);$preview=false;return view('ebook.read',array_merge(compact('kind','cat','ch','preview'),$this->content($chapter)));}
}
