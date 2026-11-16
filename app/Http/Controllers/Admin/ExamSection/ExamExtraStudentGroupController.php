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

    // Group membership is permanent; marks are saved separately for each exam.
    public function viewMembers(Request $r, int $category, int $group)
    {
        $subId = $this->subId($r);
        [$cat, $sub] = $this->scope($category, $subId);
        $current = $this->groupRow($category, $subId, $group);
        abort_unless(Schema::hasTable('lebd_exam_extra_student_exam_marks'), 503, 'Import CREATE_EXTRA_STUDENT_EXAM_MARKS.sql first.');

        $examItems = DB::table('lebd_exam_section_routine_items as i')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->where('r.category_id', $category)
            ->where('r.subcategory_id', $subId)
            ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
            ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
            ->select('i.id', 'i.exam_name', 'i.exam_end_at', 'r.name as routine_name')
            ->orderByDesc('i.exam_end_at')->orderByDesc('i.id')->get();

        $examItemId = $r->filled('exam_item_id') ? (int) $r->query('exam_item_id') : (int) optional($examItems->first())->id;
        $selectedExam = $examItems->firstWhere('id', $examItemId);
        if ($examItemId && !$selectedExam) abort(404, 'Selected exam is not available in this Category/Sub-category.');

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
            ->leftJoin('lebd_exam_extra_student_exam_marks as em', function ($join) use ($examItemId, $current) {
                $join->on('em.pick_id', '=', 'p.id')->where('em.group_id', '=', $current->id);
                if ($examItemId) $join->where('em.routine_item_id', '=', $examItemId);
                else $join->whereRaw('1 = 0');
            })
            ->select('p.id as pick_id', 's.id as student_id', 's.name', 's.department', 's.contact', 's.email', 's.status', 'm.created_at as assigned_at', 'em.marks')
            ->orderBy('m.id')
            ->get();
        $memberCount = DB::table('lebd_exam_extra_student_group_members')->where('group_id', $current->id)->count();

        return view('admin.exam-section.extra-student-group-members',
            compact('cat', 'sub', 'current', 'members', 'memberCount', 'search', 'examItems', 'selectedExam', 'examItemId'));
    }

    /**
     * Bulk-fill missing or out-of-range simulation scores for one exam only.
     * These values remain explicitly simulation data and never become official submissions.
     */
    public function autoGenerateMarks(Request $r, int $category, int $group)
    {
        $subId = $this->subId($r);
        $this->scope($category, $subId);
        $current = $this->groupRow($category, $subId, $group);
        abort_unless(Schema::hasTable('lebd_exam_extra_student_exam_marks'), 503, 'Import CREATE_EXTRA_STUDENT_EXAM_MARKS.sql first.');

        $data = $r->validate(['exam_item_id' => ['required', 'integer', 'min:1']]);
        $exam = DB::table('lebd_exam_section_routine_items as i')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->where('i.id', (int) $data['exam_item_id'])
            ->where('r.category_id', $category)->where('r.subcategory_id', $subId)
            ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
            ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
            ->select('i.id')->first();
        abort_unless($exam, 404, 'Exam does not belong to this Category/Sub-category.');

        if ($current->mark_from === null || $current->mark_to === null) {
            throw ValidationException::withMessages(['mark_range' => 'Set both Mark From and Mark To in this Group before generating simulation scores.']);
        }
        $minCents = (int) round(((float) $current->mark_from) * 100);
        $maxCents = (int) round(((float) $current->mark_to) * 100);
        if ($minCents > $maxCents) {
            throw ValidationException::withMessages(['mark_range' => 'The Group Mark Range is invalid.']);
        }

        // Simulation scores use half-point increments only (e.g. 70, 70.5, 71).
        // Convert the configured range to the first/last valid 0.50 step.
        $minHalfSteps = (int) ceil($minCents / 50);
        $maxHalfSteps = (int) floor($maxCents / 50);
        if ($minHalfSteps > $maxHalfSteps) {
            throw ValidationException::withMessages(['mark_range' => 'The selected Mark Range contains no valid 0.50-step score. Adjust the Group range.']);
        }

        $result = DB::transaction(function () use ($current, $exam, $minCents, $maxCents, $minHalfSteps, $maxHalfSteps) {
            $members = DB::table('lebd_exam_extra_student_group_members')
                ->where('group_id', $current->id)->orderBy('id')->lockForUpdate()->get(['pick_id']);
            $created = 0;
            $kept = 0;
            foreach ($members as $member) {
                $existing = DB::table('lebd_exam_extra_student_exam_marks')
                    ->where('routine_item_id', $exam->id)->where('group_id', $current->id)
                    ->where('pick_id', $member->pick_id)->lockForUpdate()->first();
                if ($existing !== null) {
                    $existingCents = (int) round(((float) $existing->marks) * 100);
                    if ($existingCents >= $minCents && $existingCents <= $maxCents && $existingCents % 50 === 0) {
                        $kept++;
                        continue;
                    }
                }
                $halfSteps = $minHalfSteps === $maxHalfSteps
                    ? $minHalfSteps
                    : random_int($minHalfSteps, $maxHalfSteps);
                $cents = $halfSteps * 50;
                DB::table('lebd_exam_extra_student_exam_marks')->updateOrInsert(
                    ['routine_item_id' => $exam->id, 'group_id' => $current->id, 'pick_id' => $member->pick_id],
                    ['marks' => number_format($cents / 100, 2, '.', ''), 'updated_at' => now(), 'created_at' => $existing->created_at ?? now()]
                );
                $created++;
            }
            return compact('created', 'kept');
        });

        return redirect()->route('admin.exam-section.extra-student-groups.view',
            $this->returnParams($category, $subId) + ['group' => $group, 'exam_item_id' => $exam->id])
            ->with('success', $result['created'] . ' simulation score(s) generated within the Group range in 0.50 increments for this exam; ' . $result['kept'] . ' existing valid score(s) kept. These are not official exam marks.');
    }

    /** Save a distinct admin simulation score for each selected exam; never reuse it across exams. */
    public function saveMarks(Request $r, int $category, int $group)
    {
        $subId = $this->subId($r);
        $this->scope($category, $subId);
        $current = $this->groupRow($category, $subId, $group);
        abort_unless(Schema::hasTable('lebd_exam_extra_student_exam_marks'), 503, 'Import CREATE_EXTRA_STUDENT_EXAM_MARKS.sql first.');

        $data = $r->validate([
            'exam_item_id' => ['required', 'integer', 'min:1'],
            'marks' => ['required', 'array', 'max:500'],
            'marks.*' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
        ]);
        $exam = DB::table('lebd_exam_section_routine_items as i')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->where('i.id', (int) $data['exam_item_id'])
            ->where('r.category_id', $category)->where('r.subcategory_id', $subId)
            ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
            ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
            ->select('i.id')->first();
        abort_unless($exam, 404, 'Exam does not belong to this Category/Sub-category.');

        $min = $current->mark_from !== null ? (float) $current->mark_from : null;
        $max = $current->mark_to !== null ? (float) $current->mark_to : null;

        DB::transaction(function () use ($data, $current, $min, $max, $exam) {
            foreach ($data['marks'] as $pickId => $rawMark) {
                $pickId = (int) $pickId;
                $isMember = DB::table('lebd_exam_extra_student_group_members')
                    ->where('group_id', $current->id)->where('pick_id', $pickId)->exists();
                if (!$isMember) throw ValidationException::withMessages(['marks' => 'A selected student is not a member of this group. Refresh and try again.']);
                if ($rawMark === null || $rawMark === '') {
                    DB::table('lebd_exam_extra_student_exam_marks')->where('group_id', $current->id)
                        ->where('pick_id', $pickId)->where('routine_item_id', $exam->id)->delete();
                    continue;
                }
                $mark = (float) $rawMark;
                if (($min !== null && $mark < $min) || ($max !== null && $mark > $max)) {
                    throw ValidationException::withMessages(['marks.' . $pickId => 'Mark must be within this Group Mark Range (' . ($min ?? '0') . ' – ' . ($max ?? '—') . ').']);
                }
                DB::table('lebd_exam_extra_student_exam_marks')->updateOrInsert(
                    ['routine_item_id' => $exam->id, 'group_id' => $current->id, 'pick_id' => $pickId],
                    ['marks' => $mark, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        return redirect()->route('admin.exam-section.extra-student-groups.view',
            $this->returnParams($category, $subId) + ['group' => $group, 'exam_item_id' => $exam->id])
            ->with('success', 'This exam\'s Extra Student simulation scores saved successfully. Other exams remain unchanged.');
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
