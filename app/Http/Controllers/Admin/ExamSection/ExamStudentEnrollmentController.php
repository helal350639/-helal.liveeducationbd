<?php
namespace App\Http\Controllers\Admin\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ExamStudentEnrollmentController extends Controller
{
    private const CAT = 'lebd_exam_section_categories';
    private const SUB = 'lebd_exam_section_subcategories';
    private const ENROLL = 'lebd_exam_section_student_enrollments';

    private function category(int $id)
    {
        $category = DB::table(self::CAT)->where('id', $id)->first();
        abort_unless($category, 404);
        return $category;
    }

    private function validScope(int $categoryId, ?int $subId): void
    {
        $hasChildren = DB::table(self::SUB)->where('category_id',$categoryId)->exists();
        if ($hasChildren) {
            abort_unless($subId && DB::table(self::SUB)->where('id',$subId)->where('category_id',$categoryId)->exists(), 422, 'Select a valid sub-category.');
        } else {
            abort_if($subId !== null, 422, 'This category does not have sub-categories.');
        }
    }

    public function index(Request $request, int $category)
    {
        $cat = $this->category($category);
        abort_unless(Schema::hasTable(self::ENROLL), 503, 'Import Fix196 SQL first.');
        $subs = DB::table(self::SUB)->where('category_id',$category)->orderBy('sort_order')->orderBy('id')->get();
        $selectedSub = $request->filled('subcategory_id') ? (int) $request->query('subcategory_id') : null;
        // Each management page is scoped to exactly one category or sub-category.
        // A parent with children cannot enroll directly: choose Students on a child row.
        $this->validScope($category, $selectedSub);
        $selectedSubRow = $selectedSub !== null ? $subs->firstWhere('id', $selectedSub) : null;
        $enrollments = DB::table(self::ENROLL.' as e')
            ->join('students as s','s.id','=','e.student_id')
            ->leftJoin(self::SUB.' as sub','sub.id','=','e.subcategory_id')
            ->where('e.category_id',$category)
            ->when($selectedSub !== null, fn($q)=>$q->where('e.subcategory_id',$selectedSub))
            ->select('e.id','e.student_id','e.subcategory_id','e.created_at','s.name','s.email','s.contact','s.status','sub.name as sub_name')
            ->orderByDesc('e.id')->paginate(25)->withQueryString();
        $q = trim((string)$request->query('search',''));
        $students = DB::table('students')->when($q!=='',function($query) use($q) {
            $query->where(function($sub) use($q){$sub->where('name','like','%'.$q.'%')->orWhere('email','like','%'.$q.'%')->orWhere('contact','like','%'.$q.'%')->orWhere('id','like','%'.$q.'%');});
        })->orderByDesc('id')->limit(40)->get(['id','name','email','contact','status']);
        $subCounts = DB::table(self::ENROLL)->where('category_id',$category)->whereNotNull('subcategory_id')->select('subcategory_id')->selectRaw('COUNT(DISTINCT student_id) as total')->groupBy('subcategory_id')->pluck('total','subcategory_id');
        $uniqueCount = DB::table(self::ENROLL)->where('category_id',$category)->distinct()->count('student_id');
        return view('admin.exam-section.student-enrollments',compact('cat','subs','selectedSub','enrollments','students','q','subCounts','uniqueCount','selectedSubRow'));
    }

    public function store(Request $request,int $category)
    {
        $this->category($category);
        $data=$request->validate(['student_ids'=>'required|array|min:1|max:200','student_ids.*'=>'required|integer|distinct|exists:students,id']);
        // Scope comes from the Students link URL; no editable category selector.
        $subId = $request->query('subcategory_id') !== null && $request->query('subcategory_id') !== ''
            ? (int) $request->query('subcategory_id') : null;
        $this->validScope($category,$subId);
        $inserted=0;
        foreach($data['student_ids'] as $studentId) {
            $exists=DB::table(self::ENROLL)->where('category_id',$category)->where('student_id',$studentId)->where(function($query)use($subId){
                $subId===null ? $query->whereNull('subcategory_id') : $query->where('subcategory_id',$subId);
            })->exists();
            if(!$exists) {
                DB::table(self::ENROLL)->insert(['category_id'=>$category,'subcategory_id'=>$subId,'student_id'=>(int)$studentId,'created_at'=>now(),'updated_at'=>now()]);
                $inserted++;
            }
        }
        return redirect()->route('admin.exam-section.student-enrollments',['category'=>$category,'subcategory_id'=>$subId])->with('success',$inserted.' student(s) added. Already enrolled students were skipped.');
    }

    public function destroy(int $category,int $enrollment)
    {
        $this->category($category);
        $row = DB::table(self::ENROLL)->where('id',$enrollment)->where('category_id',$category)->first();
        abort_unless($row,404);
        DB::table(self::ENROLL)->where('id',$row->id)->delete();
        return back()->with('success','Enrollment removed. Student account was not deleted.');
    }
}
