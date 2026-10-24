<?php
namespace App\Http\Controllers\Students\ExamSection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
class StudentExamCategoryController extends Controller {
    private function liveIds(): array {
        $now = now()->format('Y-m-d H:i:s');
        $rows = DB::table('lebd_exam_section_routine_items as i')
          ->join('lebd_exam_section_routines as r', 'r.id','=','i.routine_id')
          ->where('i.type','exam')->whereNotNull('i.exam_start_at')->whereNotNull('i.exam_end_at')
          ->where('i.exam_start_at','<=',$now)->where('i.exam_end_at','>',$now)
          ->select('r.category_id','r.subcategory_id')->distinct()->get();
        return [$rows->pluck('category_id')->map(fn($n)=>(int)$n)->unique()->all(),
          $rows->filter(fn($r)=>$r->subcategory_id!==null)->pluck('subcategory_id')->map(fn($n)=>(int)$n)->unique()->all()];
    }
    public function categories(Request $request) {
      // Keep the working root URL as the single navigation entry point.
      // Never turn a valid card into an unhelpful 404 because of a stale scope.
      if ($request->filled('category')) {
        $raw = (string) $request->query('category');
        if (!ctype_digit($raw) || (int) $raw < 1) {
          return redirect()->route('student.exam-section.categories')->with('error', 'Invalid exam category.');
        }
        $category = (int) $raw;
        $cat = DB::table('lebd_exam_section_categories')->where('id', $category)->first();
        if (!$cat || strcasecmp((string) $cat->status, 'Active') !== 0) {
          return redirect()->route('student.exam-section.categories')->with('error', 'This exam category is no longer available.');
        }
        if ($request->query('view') === 'exams' || $request->filled('subcategory_id')) {
          return $this->exams($category, $request);
        }
        return $this->category($category);
      }
      [$liveCats,$liveSubs]=$this->liveIds();
      $categories=DB::table('lebd_exam_section_categories')->where('status','Active')->orderBy('sort_order')->orderBy('id')->get();
      return view('student.exam-section.categories',compact('categories','liveCats'));
    }
    public function category(int $category) {
      $cat=DB::table('lebd_exam_section_categories')->where('id',$category)->first();
      if (!$cat || strcasecmp((string) $cat->status, 'Active') !== 0) {
        return redirect()->route('student.exam-section.categories')->with('error','This exam category is unavailable.');
      }
      $subs=DB::table('lebd_exam_section_subcategories')->where('category_id',$category)->where('status','Active')->orderBy('sort_order')->orderBy('id')->get();
      if ($subs->isEmpty()) {
        return $this->exams($category, new Request(['view'=>'exams']));
      }
      [$liveCats,$liveSubs]=$this->liveIds();
      return view('student.exam-section.subcategories',compact('cat','subs','liveSubs'));
    }
    public function exams(int $category, Request $request) {
      $cat=DB::table('lebd_exam_section_categories')->where('id',$category)->first();
      if (!$cat || strcasecmp((string) $cat->status,'Active') !== 0) return redirect()->route('student.exam-section.categories')->with('error','This category is unavailable.');
      $hasSubs=DB::table('lebd_exam_section_subcategories')->where('category_id',$category)->where('status','Active')->exists();
      $sub=null;
      if($hasSubs){
        $subId=(int)$request->query('subcategory_id',0);
        $sub=DB::table('lebd_exam_section_subcategories')->where('category_id',$category)->where('status','Active')->where('id',$subId)->first();
        if (!$sub) return redirect()->route('student.exam-section.browse-category', ['category'=>$category])->with('error','This sub-category is unavailable.');
      }
      $routines=DB::table('lebd_exam_section_routines')->where('category_id',$category)->where('subcategory_id',$sub?->id)->get(['id','name']);
      $ids=$routines->pluck('id')->all();
      $exams=count($ids)?DB::table('lebd_exam_section_routine_items')->whereIn('routine_id',$ids)->where('type','exam')->orderBy('exam_start_at')->get(['id','routine_id','exam_name','exam_start_at','exam_end_at']):collect();
      return view('student.exam-section.exams',compact('cat','sub','routines','exams'));
    }
}
