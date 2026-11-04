<?php
namespace App\Http\Controllers\Admin\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExamExtraStudentPickerController extends Controller
{
    private function scope(int $category, ?int $subcategory): array
    {
        $user = Auth::guard('web')->user();
        $role = $user && method_exists($user, 'authRole') ? optional($user->authRole())->name : null;
        abort_unless($user && in_array($role, ['Admin','SuperAdmin'], true), 403);
        $cat = DB::table('lebd_exam_section_categories')->where('id', $category)->first();
        abort_unless($cat, 404);
        $sub = null;
        if ($subcategory !== null) {
            $sub = DB::table('lebd_exam_section_subcategories')->where('id', $subcategory)
                ->where('category_id', $category)->first();
            abort_unless($sub, 404);
        }
        abort_unless(Schema::hasTable('lebd_extra_students'), 503, 'Extra Student module is not installed.');
        abort_unless(Schema::hasTable('lebd_exam_section_extra_student_picks'), 503, 'Install the additive Extra Student picker SQL first.');
        return [$cat, $sub];
    }

    public function index(Request $request, int $category)
    {
        $subcategory = $request->filled('subcategory_id') ? (int)$request->query('subcategory_id') : null;
        [$cat, $sub] = $this->scope($category, $subcategory);
        $q = trim((string)$request->query('q', ''));
        // Only show candidates NOT already saved for this exact Category/Sub-category.
        // The saved list below still contains every selected student.
        $query = DB::table('lebd_extra_students')
            ->where('status', 'Active')
            ->whereNotIn('id', function ($subquery) use ($category, $subcategory) {
                $subquery->select('extra_student_id')
                    ->from('lebd_exam_section_extra_student_picks')
                    ->where('category_id', $category)
                    ->where('subcategory_id', $subcategory);
            })
            ->orderBy('name')->orderBy('id');
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', '%'.$q.'%')
                    ->orWhere('contact', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%');
                if (ctype_digit($q)) $builder->orWhere('id', (int)$q);
            });
        }
        $students = $query->paginate(25)->withQueryString();
        $saved = DB::table('lebd_exam_section_extra_student_picks as p')
            ->join('lebd_extra_students as s', 's.id', '=', 'p.extra_student_id')
            ->where('p.category_id', $category)
            ->where('p.subcategory_id', $subcategory)
            ->orderBy('s.name')
            ->get(['p.id as pick_id','s.id','s.name','s.department','s.contact','s.email']);
        $selectedIds = $saved->pluck('id')->map(fn($id) => (int)$id)->all();
        $groups = collect();
        $assignedGroups = collect();
        if (Schema::hasTable('lebd_exam_extra_student_groups') && Schema::hasTable('lebd_exam_extra_student_group_members')) {
            $groups = DB::table('lebd_exam_extra_student_groups')
                ->where('category_id', $category)->where('subcategory_id', $subcategory)
                ->select('lebd_exam_extra_student_groups.*')->selectSub(function ($q) {
                    $q->from('lebd_exam_extra_student_group_members as m')->selectRaw('COUNT(*)')->whereColumn('m.group_id','lebd_exam_extra_student_groups.id');
                }, 'member_count')->orderBy('sort_order')->orderBy('id')->get();
            $assignedGroups = DB::table('lebd_exam_extra_student_group_members as m')
                ->join('lebd_exam_extra_student_groups as g','g.id','=','m.group_id')
                ->where('g.category_id',$category)->where('g.subcategory_id',$subcategory)
                ->whereIn('m.pick_id', $saved->pluck('pick_id')->all())
                ->pluck('g.name','m.pick_id');
        }
        return view('admin.exam-section.extra-student-picker', compact('cat','sub','students','saved','selectedIds','q','groups','assignedGroups'));
    }

    public function store(Request $request, int $category)
    {
        $data = $request->validate([
            'subcategory_id' => ['nullable','integer','min:1'],
            'student_ids' => ['required','array','min:1','max:200'],
            'student_ids.*' => ['required','integer','distinct','min:1'],
        ]);
        $subId = isset($data['subcategory_id']) ? (int)$data['subcategory_id'] : null;
        $this->scope($category, $subId);
        $ids = array_map('intval', $data['student_ids']);
        $valid = DB::table('lebd_extra_students')->where('status','Active')->whereIn('id',$ids)->pluck('id')->map(fn($v)=>(int)$v)->all();
        abort_unless(count($valid) === count($ids), 422, 'An invalid/inactive Extra Student was selected.');
        foreach ($valid as $id) {
            // NULL subcategory is not protected by MySQL composite UNIQUE; check in scoped transaction.
            DB::transaction(function () use ($category,$subId,$id) {
                $existing = DB::table('lebd_exam_section_extra_student_picks')
                    ->where('category_id',$category)->where('subcategory_id',$subId)
                    ->where('extra_student_id',$id)->exists();
                if (!$existing) DB::table('lebd_exam_section_extra_student_picks')->insert([
                    'category_id'=>$category,'subcategory_id'=>$subId,'extra_student_id'=>$id,
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            });
        }
        return redirect()->route('admin.exam-section.extra-students.index', array_filter([
            'category'=>$category,'subcategory_id'=>$subId],fn($v)=>$v!==null))
            ->with('success', count($valid).' Extra Student(s) selected.');
    }

    public function remove(Request $request, int $category, int $pick)
    {
        $subId = $request->filled('subcategory_id') ? (int)$request->input('subcategory_id') : null;
        $this->scope($category,$subId);
        DB::transaction(function () use ($pick,$category,$subId) {
            $query = DB::table('lebd_exam_section_extra_student_picks')
                ->where('id',$pick)->where('category_id',$category)->where('subcategory_id',$subId);
            if (!$query->exists()) return;
            if (Schema::hasTable('lebd_exam_extra_student_group_members')) {
                DB::table('lebd_exam_extra_student_group_members')->where('pick_id',$pick)->delete();
            }
            $query->delete();
        });
        return back()->with('success','Selection removed; original Extra Student was not deleted.');
    }
}
