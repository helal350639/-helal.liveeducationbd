<?php
namespace App\Http\Controllers\Admin\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ExamExtraStudentGroupController extends Controller
{
    private function scope(int $category, ?int $subId): array
    {
        $user = Auth::guard('web')->user();
        $role = $user && method_exists($user, 'authRole') ? optional($user->authRole())->name : null;
        abort_unless($user && in_array($role, ['Admin', 'SuperAdmin'], true), 403);
        $cat = DB::table('lebd_exam_section_categories')->where('id', $category)->first();
        abort_unless($cat, 404);
        $sub = null;
        if ($subId !== null) {
            $sub = DB::table('lebd_exam_section_subcategories')->where('category_id',$category)->where('id',$subId)->first();
            abort_unless($sub,404);
        }
        abort_unless(Schema::hasTable('lebd_exam_extra_student_groups') && Schema::hasTable('lebd_exam_extra_student_group_members'),503,'Import CREATE_EXTRA_STUDENT_GROUPS_ONLY.sql first.');
        return [$cat,$sub];
    }

    private function subId(Request $r): ?int
    {
        return $r->filled('subcategory_id') ? (int)$r->input('subcategory_id') : null;
    }

    private function groupRow(int $category, ?int $subId, int $group)
    {
        $row = DB::table('lebd_exam_extra_student_groups')->where('id',$group)
            ->where('category_id',$category)->where('subcategory_id',$subId)->first();
        abort_unless($row,404);
        return $row;
    }

    private function returnParams(int $category, ?int $subId): array
    {
        return ['category'=>$category] + ($subId !== null ? ['subcategory_id'=>$subId] : []);
    }

    public function index(Request $r, int $category)
    {
        $subId=$this->subId($r);
        [$cat,$sub]=$this->scope($category,$subId);
        $groups=DB::table('lebd_exam_extra_student_groups as g')
            ->where('g.category_id',$category)->where('g.subcategory_id',$subId)
            ->select('g.*')
            ->selectSub(function ($q) {
                $q->from('lebd_exam_extra_student_group_members as m')->selectRaw('COUNT(*)')->whereColumn('m.group_id','g.id');
            },'member_count')
            ->orderBy('g.sort_order')->orderBy('g.id')->get();
        return view('admin.exam-section.extra-student-groups',compact('cat','sub','groups'));
    }

    private function validated(Request $r): array
    {
        $d=$r->validate([
            'name'=>['required','string','max:150'],
            'merit_from'=>['nullable','integer','min:1','max:9999999'],
            'merit_to'=>['nullable','integer','min:1','max:9999999'],
            'mark_from'=>['nullable','numeric','min:0','max:9999999'],
            'mark_to'=>['nullable','numeric','min:0','max:9999999'],
            'sort_order'=>['nullable','integer','min:0','max:999999'],
        ]);
        if (isset($d['merit_from'],$d['merit_to']) && $d['merit_from']>$d['merit_to']) {
            throw ValidationException::withMessages(['merit_to'=>'Merit range end must be greater than or equal to start.']);
        }
        if (isset($d['mark_from'],$d['mark_to']) && $d['mark_from']>$d['mark_to']) {
            throw ValidationException::withMessages(['mark_to'=>'Mark range end must be greater than or equal to start.']);
        }
        return [
            'name'=>trim($d['name']),'merit_from'=>$d['merit_from']??null,'merit_to'=>$d['merit_to']??null,
            'mark_from'=>$d['mark_from']??null,'mark_to'=>$d['mark_to']??null,
            'sort_order'=>(int)($d['sort_order']??0),
        ];
    }

    private function assertUniqueName(int $category,?int $subId,string $name,?int $ignore=null): void
    {
        $q=DB::table('lebd_exam_extra_student_groups')->where('category_id',$category)
            ->where('subcategory_id',$subId)->where('name',$name);
        if ($ignore !== null) $q->where('id','!=',$ignore);
        if ($q->exists()) throw ValidationException::withMessages(['name'=>'This group name already exists in this Category/Sub-category.']);
    }

    public function store(Request $r,int $category)
    {
        $subId=$this->subId($r);$this->scope($category,$subId);
        $d=$this->validated($r);
        $this->assertUniqueName($category,$subId,$d['name']);
        DB::table('lebd_exam_extra_student_groups')->insert($d+[
            'category_id'=>$category,'subcategory_id'=>$subId,'created_at'=>now(),'updated_at'=>now(),
        ]);
        return redirect()->route('admin.exam-section.extra-student-groups.index',$this->returnParams($category,$subId))->with('success','Group created.');
    }

    public function update(Request $r,int $category,int $group)
    {
        $subId=$this->subId($r);$this->scope($category,$subId);
        $this->groupRow($category,$subId,$group);
        $d=$this->validated($r);$this->assertUniqueName($category,$subId,$d['name'],$group);
        DB::table('lebd_exam_extra_student_groups')->where('id',$group)->update($d+['updated_at'=>now()]);
        return redirect()->route('admin.exam-section.extra-student-groups.index',$this->returnParams($category,$subId))->with('success','Group updated.');
    }

    public function destroy(Request $r,int $category,int $group)
    {
        $subId=$this->subId($r);$this->scope($category,$subId);
        $this->groupRow($category,$subId,$group);
        DB::transaction(function () use ($group) {
            DB::table('lebd_exam_extra_student_group_members')->where('group_id',$group)->delete();
            DB::table('lebd_exam_extra_student_groups')->where('id',$group)->delete();
        });
        return redirect()->route('admin.exam-section.extra-student-groups.index',$this->returnParams($category,$subId))->with('success','Group removed; saved student selections were NOT deleted.');
    }

    public function assign(Request $r,int $category)
    {
        $subId=$this->subId($r);$this->scope($category,$subId);
        $data=$r->validate([
            'pick_ids'=>['required','array','min:1','max:200'],
            'pick_ids.*'=>['required','integer','distinct','min:1'],
            'group_id'=>['nullable','integer','min:1'],
        ]);
        $groupId=isset($data['group_id'])?(int)$data['group_id']:null;
        if($groupId !== null) $this->groupRow($category,$subId,$groupId);
        $picks=array_map('intval',$data['pick_ids']);
        $valid=DB::table('lebd_exam_section_extra_student_picks')
            ->where('category_id',$category)->where('subcategory_id',$subId)
            ->whereIn('id',$picks)->pluck('id')->map(fn($x)=>(int)$x)->all();
        if(count($picks)!==count($valid)) throw ValidationException::withMessages(['pick_ids'=>'Selection contains a student not saved in this Category/Sub-category.']);
        DB::transaction(function () use ($picks,$groupId) {
            DB::table('lebd_exam_extra_student_group_members')->whereIn('pick_id',$picks)->delete();
            if($groupId !== null) {
                $now=now();
                DB::table('lebd_exam_extra_student_group_members')->insert(array_map(
                    fn($pick)=>['group_id'=>$groupId,'pick_id'=>$pick,'created_at'=>$now,'updated_at'=>$now],$picks
                ));
            }
        });
        return redirect()->route('admin.exam-section.extra-students.index',$this->returnParams($category,$subId))
            ->with('success',count($picks).' selected student(s) '.($groupId===null?'unassigned.':'assigned to group.'));
    }

    // Select only students already saved in this exact category/subcategory.
    // Every group count is taken from the persisted group-member table.
    public function students(Request $r, int $category, int $group)
    {
        $subId = $this->subId($r);
        [$cat,$sub] = $this->scope($category, $subId);
        $current = $this->groupRow($category, $subId, $group);
        $base = DB::table('lebd_exam_section_extra_student_picks as p')
            ->join('lebd_extra_students as s','s.id','=','p.extra_student_id')
            ->where('p.category_id',$category)->where('p.subcategory_id',$subId)
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')->from('lebd_exam_extra_student_group_members as m')
                    ->whereColumn('m.pick_id','p.id');
            });
        $search=trim((string)$r->query('q',''));
        if($search!=='') {
            $base->where(function ($q) use ($search) {
                $q->where('s.name','like','%'.$search.'%')
                  ->orWhere('s.department','like','%'.$search.'%')
                  ->orWhere('s.contact','like','%'.$search.'%');
                if(ctype_digit($search)) $q->orWhere('s.id',(int)$search);
            });
        }
        $columns=['p.id as pick_id','s.id as student_id','s.name','s.department','s.contact'];
        $shuffle=$r->boolean('shuffle');
        if($shuffle) {
            $key='esg_shuffle_seen_'.$category.'_'.($subId??'cat').'_'.$group;
            if($r->boolean('reset_shuffle')) $r->session()->forget($key);
            $seen=$r->session()->get($key,[]);
            if(!is_array($seen)) $seen=[];
            $remaining=(clone $base)->whereNotIn('p.id',$seen);
            if(!(clone $remaining)->exists()) {
                $seen=[];
                $remaining=clone $base;
            }
            $students=$remaining->inRandomOrder()->limit(25)->get($columns);
            $seen=array_values(array_unique(array_merge($seen,$students->pluck('pick_id')->all())));
            // Session remembers which IDs were already presented on shuffle.
            $r->session()->put($key,$seen);
        } else {
            $students=$base->orderBy('s.name')->orderBy('p.id')->paginate(25,$columns)->withQueryString();
        }
        $memberCount=DB::table('lebd_exam_extra_student_group_members')->where('group_id',$group)->count();
        $availableCount=(clone $base)->count();
        return view('admin.exam-section.extra-student-group-students',compact('cat','sub','current','students','shuffle','search','memberCount','availableCount'));
    }

    // Read-only group members: strictly scoped to the selected Category/Sub-category and Group.
    public function viewMembers(Request $r, int $category, int $group)
    {
        $subId = $this->subId($r);
        [$cat, $sub] = $this->scope($category, $subId);
        $current = $this->groupRow($category, $subId, $group);

        $query = DB::table('lebd_exam_extra_student_group_members as m')
            ->join('lebd_exam_section_extra_student_picks as p', 'p.id', '=', 'm.pick_id')
            ->join('lebd_extra_students as s', 's.id', '=', 'p.extra_student_id')
            ->where('m.group_id', $current->id)
            ->where('p.category_id', $category)
            ->where('p.subcategory_id', $subId);

        $search = trim((string) $r->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('s.name', 'like', '%' . $search . '%')
                  ->orWhere('s.department', 'like', '%' . $search . '%')
                  ->orWhere('s.contact', 'like', '%' . $search . '%');
                if (ctype_digit($search)) $q->orWhere('s.id', (int) $search);
            });
        }

        $members = $query
            ->select('s.id as student_id', 's.name', 's.department', 's.contact', 's.email', 's.status', 'm.created_at as assigned_at')
            ->orderBy('m.id')
            ->paginate(25)
            ->withQueryString();
        $memberCount = DB::table('lebd_exam_extra_student_group_members')
            ->where('group_id', $current->id)->count();

        return view('admin.exam-section.extra-student-group-members',
            compact('cat', 'sub', 'current', 'members', 'memberCount', 'search'));
    }

    public function addStudents(Request $r, int $category, int $group)
    {
        $subId=$this->subId($r);
        $this->scope($category,$subId);
        $this->groupRow($category,$subId,$group);
        $data=$r->validate([
            'pick_ids'=>['required','array','min:1','max:25'],
            'pick_ids.*'=>['required','integer','distinct','min:1'],
        ]);
        $ids=array_map('intval',$data['pick_ids']);
        DB::transaction(function () use ($ids,$category,$subId,$group) {
            // Lock each pick to prevent assigning the same student twice.
            $picks=DB::table('lebd_exam_section_extra_student_picks')
                ->where('category_id',$category)->where('subcategory_id',$subId)
                ->whereIn('id',$ids)->orderBy('id')->lockForUpdate()->pluck('id')->map(fn($v)=>(int)$v)->all();
            if(count($picks)!==count($ids)) throw ValidationException::withMessages(['pick_ids'=>'Some students were not saved in this Category/Sub-category.']);
            $existing=DB::table('lebd_exam_extra_student_group_members')->whereIn('pick_id',$picks)->count();
            if($existing) throw ValidationException::withMessages(['pick_ids'=>'Some students are already in a Group. Refresh and select available students.']);
            $now=now();
            DB::table('lebd_exam_extra_student_group_members')->insert(array_map(
                fn($id)=>['group_id'=>$group,'pick_id'=>$id,'created_at'=>$now,'updated_at'=>$now],$picks
            ));
        });
        // After saving, return to the Group Management page so the updated member count is visible.
        return redirect()->route('admin.exam-section.extra-student-groups.index',
            $this->returnParams($category, $subId))
            ->with('success', count($ids).' Student(s) added to Group.');
    }

}
