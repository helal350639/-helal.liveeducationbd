<?php
namespace App\Http\Controllers\Admin\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExamSectionController extends Controller
{
    // Unique student enrollments per exact subcategory; parent aggregates its children.
    private function examStudentCounts(): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('lebd_exam_section_student_enrollments')) {
            return ['categories'=>[], 'subcategories'=>[]];
        }
        $enrollments = DB::table('lebd_exam_section_student_enrollments')->get(['category_id','subcategory_id','student_id']);
        $categories = []; $subcategories = [];
        foreach ($enrollments as $enrollment) {
            $cat = (int) $enrollment->category_id;
            $sid = (int) $enrollment->student_id;
            $categories[$cat][$sid] = true;
            if ($enrollment->subcategory_id !== null) {
                $subcategories[(int) $enrollment->subcategory_id][$sid] = true;
            }
        }
        return ['categories'=>array_map('count',$categories),'subcategories'=>array_map('count',$subcategories)];
    }

    public function category()
    {
        $categories = DB::table('lebd_exam_section_categories as c')
            ->select('c.*')
            ->selectSub(function ($q) {
                $q->from('lebd_exam_section_subcategories as s')
                  ->selectRaw('COUNT(*)')->whereColumn('s.category_id', 'c.id');
            }, 'subcategory_count')
            ->orderBy('c.sort_order')->orderByDesc('c.id')->get();
        $studentCounts = $this->examStudentCounts();
        return view('admin.exam-section.category', compact('categories','studentCounts'));
    }
    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:190',
            'sort_order' => 'required|integer|min:0|max:999999',
            'status' => ['required', Rule::in(['Active','Inactive'])],
        ]);
        DB::table('lebd_exam_section_categories')->insert($data + ['created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success', 'Category added successfully.');
    }
    public function updateCategory(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:190',
            'sort_order' => 'required|integer|min:0|max:999999',
            'status' => ['required', Rule::in(['Active','Inactive'])],
        ]);
        $row = DB::table('lebd_exam_section_categories')->where('id', $id)->first();
        if (!$row) abort(404);
        DB::table('lebd_exam_section_categories')->where('id', $id)->update($data + ['updated_at'=>now()]);
        return back()->with('success', 'Category updated successfully.');
    }
    public function deleteCategory($id)
    {
        if (DB::table('lebd_exam_section_subcategories')->where('category_id',$id)->exists())
            return back()->with('error', 'Delete the linked sub-categories first.');
        DB::table('lebd_exam_section_categories')->where('id',$id)->delete();
        return back()->with('success','Category deleted.');
    }
    public function subcategory(Request $request)
    {
        $categories = DB::table('lebd_exam_section_categories')->orderBy('sort_order')->orderBy('name')->get();
        $categoryId = $request->filled('category_id') ? (int)$request->query('category_id') : null;
        $subcategories = DB::table('lebd_exam_section_subcategories as s')
            ->join('lebd_exam_section_categories as c','c.id','=','s.category_id')
            ->select('s.*','c.name as category_name')
            ->when($categoryId, fn($q) => $q->where('s.category_id',$categoryId))
            ->orderBy('s.sort_order')->orderByDesc('s.id')->get();
        $studentCounts = $this->examStudentCounts();
        return view('admin.exam-section.subcategory', compact('categories','subcategories','categoryId','studentCounts'));
    }
    public function storeSubcategory(Request $request)
    {
        $data = $request->validate([
            'category_id'=>'required|integer|exists:lebd_exam_section_categories,id',
            'name'=>'required|string|max:190',
            'sort_order'=>'required|integer|min:0|max:999999',
            'status'=>['required',Rule::in(['Active','Inactive'])],
        ]);
        DB::table('lebd_exam_section_subcategories')->insert($data + ['created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success', 'Sub-category added successfully.');
    }
    public function updateSubcategory(Request $request, $id)
    {
        $data = $request->validate([
            'category_id'=>'required|integer|exists:lebd_exam_section_categories,id',
            'name'=>'required|string|max:190',
            'sort_order'=>'required|integer|min:0|max:999999',
            'status'=>['required',Rule::in(['Active','Inactive'])],
        ]);
        if (!DB::table('lebd_exam_section_subcategories')->where('id',$id)->exists()) abort(404);
        DB::table('lebd_exam_section_subcategories')->where('id',$id)->update($data + ['updated_at'=>now()]);
        return back()->with('success','Sub-category updated successfully.');
    }
    public function deleteSubcategory($id)
    {
        DB::table('lebd_exam_section_subcategories')->where('id',$id)->delete();
        return back()->with('success','Sub-category deleted.');
    }
    public function routine() { return view('admin.exam-section.routine'); }
    // Counts unique Exam names per routine (labels + saved exam items), scoped by category/sub-category.
    // An exam is Complete only after its Exam End date. Missing/future End dates are Pending.
    private function autoQuestionExamCounts(array $categoryIds): array
    {
        if (!$categoryIds) return ['categories' => [], 'subcategories' => []];
        $routines = DB::table('lebd_exam_section_routines')
            ->whereIn('category_id', $categoryIds)->get(['id', 'category_id', 'subcategory_id']);
        if ($routines->isEmpty()) return ['categories' => [], 'subcategories' => []];
        $ids = $routines->pluck('id')->all();
        $labels = DB::table('lebd_exam_section_routine_labels')
            ->whereIn('routine_id', $ids)->where('kind', 'exam')
            ->get(['routine_id', 'value'])->groupBy('routine_id');
        $items = DB::table('lebd_exam_section_routine_items')
            ->whereIn('routine_id', $ids)->where('type', 'exam')
            ->get(['routine_id', 'exam_name', 'exam_end_at'])->groupBy('routine_id');
        $now = now();
        $cats = []; $subs = [];
        foreach ($routines as $routine) {
            $names = [];
            foreach (($labels[$routine->id] ?? collect()) as $label) {
                $name = trim((string) $label->value);
                if ($name !== '') $names[$name] = null;
            }
            foreach (($items[$routine->id] ?? collect()) as $item) {
                $name = trim((string) $item->exam_name);
                if ($name === '') continue;
                if (!array_key_exists($name, $names)) $names[$name] = null;
                if (!empty($item->exam_end_at)) {
                    $end = \Carbon\Carbon::parse($item->exam_end_at);
                    if ($names[$name] === null || $end->gt($names[$name])) $names[$name] = $end;
                }
            }
            $count = ['total' => count($names), 'complete' => 0, 'pending' => 0];
            foreach ($names as $end) {
                if ($end !== null && $end->lte($now)) $count['complete']++;
                else $count['pending']++;
            }
            $catId = (int) $routine->category_id;
            $subId = $routine->subcategory_id === null ? null : (int) $routine->subcategory_id;
            if (!isset($cats[$catId])) $cats[$catId] = ['total'=>0, 'complete'=>0, 'pending'=>0];
            foreach ($count as $key=>$val) $cats[$catId][$key] += $val;
            if ($subId !== null) {
                if (!isset($subs[$subId])) $subs[$subId] = ['total'=>0, 'complete'=>0, 'pending'=>0];
                foreach ($count as $key=>$val) $subs[$subId][$key] += $val;
            }
        }
        return ['categories'=>$cats, 'subcategories'=>$subs];
    }

    public function autoQuestion(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $order = (string) $request->query('order', 'sort');
        if (!in_array($order, ['sort', 'newest', 'oldest', 'name'], true)) $order = 'sort';
        $query = DB::table('lebd_exam_section_categories as c')
            ->where('c.status', 'Active')
            ->select('c.*')
            ->selectSub(function ($q) {
                $q->from('lebd_exam_section_subcategories as s')
                  ->selectRaw('COUNT(*)')->whereColumn('s.category_id', 'c.id');
            }, 'subcategory_count');
        if ($search !== '') $query->where('c.name', 'like', '%' . $search . '%');
        if ($order === 'newest') $query->orderByDesc('c.created_at')->orderByDesc('c.id');
        elseif ($order === 'oldest') $query->orderBy('c.created_at')->orderBy('c.id');
        elseif ($order === 'name') $query->orderBy('c.name');
        else $query->orderBy('c.sort_order')->orderBy('c.id');
        $categories = $query->paginate(25)->withQueryString();
        $examCounts = $this->autoQuestionExamCounts($categories->getCollection()->pluck('id')->all())['categories'];
        $studentCounts = $this->examStudentCounts();
        return view('admin.exam-section.auto-question', compact('categories', 'search', 'order', 'examCounts','studentCounts'));
    }
    // Fix173: auto-question read-only navigation. Existing management endpoints untouched.
    public function autoQuestionSubcategories(int $category)
    {
        $cat = DB::table('lebd_exam_section_categories')->where('id',$category)->where('status','Active')->first();
        abort_unless($cat,404);
        $subs = DB::table('lebd_exam_section_subcategories')->where('category_id',$category)
            ->where('status','Active')->orderBy('sort_order')->orderBy('id')->get();
        if ($subs->isEmpty()) return redirect()->route('admin.exam-section.auto-question.exams', $category);
        $examCounts = $this->autoQuestionExamCounts([(int) $cat->id])['subcategories'];
        $studentCounts = $this->examStudentCounts();
        return view('admin.exam-section.auto-question-subcategories', compact('cat','subs','examCounts','studentCounts'));
    }
    public function autoQuestionExams(int $category, Request $request)
    {
        $cat = DB::table('lebd_exam_section_categories')->where('id',$category)->where('status','Active')->first();
        abort_unless($cat,404);
        $hasSubs = DB::table('lebd_exam_section_subcategories')->where('category_id',$category)->where('status','Active')->exists();
        $subId = $request->filled('subcategory_id') ? (int)$request->query('subcategory_id') : null;
        if ($hasSubs) {
            abort_unless($subId, 404);
            $sub = DB::table('lebd_exam_section_subcategories')->where('id',$subId)->where('category_id',$category)->where('status','Active')->first();
            abort_unless($sub,404);
        } else {
            abort_if($subId,404);
            $sub = null;
        }
        $routines = DB::table('lebd_exam_section_routines')->where('category_id',$category)
            ->where('subcategory_id',$subId)->orderByDesc('id')->get();
        $labels = collect();
        if ($routines->isNotEmpty()) {
            $labels = DB::table('lebd_exam_section_routine_labels')->whereIn('routine_id',$routines->pluck('id')->all())
                ->where('kind','exam')->orderBy('sort_order')->orderBy('id')->get()->groupBy('routine_id');
        }
        return view('admin.exam-section.auto-question-exams',compact('cat','sub','routines','labels','hasSubs'));
    }

    // Shared settings belong to category only if it has no active sub-categories.
    // Otherwise each active sub-category owns its own settings.
    private function paperSettingScope(int $category, ?int $subcategory): array
    {
        $cat = DB::table('lebd_exam_section_categories')->where('id', $category)->where('status', 'Active')->first();
        abort_unless($cat, 404);
        $hasSubs = DB::table('lebd_exam_section_subcategories')->where('category_id', $category)->where('status', 'Active')->exists();
        if ($hasSubs) {
            abort_unless($subcategory, 404, 'Choose a sub-category.');
            $sub = DB::table('lebd_exam_section_subcategories')->where('id', $subcategory)->where('category_id', $category)->where('status', 'Active')->first();
            abort_unless($sub, 404);
        } else {
            abort_if($subcategory, 404);
            $sub = null;
        }
        return [$cat, $sub];
    }
    public function paperSettings(int $category, Request $request)
    {
        $subId = $request->filled('subcategory_id') ? (int) $request->query('subcategory_id') : null;
        [$cat, $sub] = $this->paperSettingScope($category, $subId);
        $scopeKey = $sub ? 'sub:' . $sub->id : 'cat:' . $cat->id;
        $saved = DB::table('lebd_exam_section_paper_settings')->where('scope_key', $scopeKey)->first();
        $settings = $saved ? json_decode($saved->settings_json, true) : [];
        if (!is_array($settings)) $settings = [];
        // Read-only schedule: take actual Exam dates and labels from this exact group.
        $routines = DB::table('lebd_exam_section_routines')->where('category_id', $cat->id)
            ->where('subcategory_id', $sub?->id)->orderByDesc('id')->get();
        $routineSchedule = collect();
        if ($routines->isNotEmpty()) {
            $routineIds = $routines->pluck('id')->all();
            $items = DB::table('lebd_exam_section_routine_items')
                ->whereIn('routine_id', $routineIds)->where('type', 'exam')->get()->groupBy('routine_id');
            $labels = DB::table('lebd_exam_section_routine_labels')
                ->whereIn('routine_id', $routineIds)->where('kind', 'exam')
                ->orderBy('sort_order')->orderBy('id')->get()->groupBy('routine_id');
            foreach ($routines as $routine) {
                $actual = ($items[$routine->id] ?? collect())->keyBy('exam_name');
                $seen = [];
                foreach (($labels[$routine->id] ?? collect()) as $label) {
                    if (isset($seen[$label->value])) continue;
                    $seen[$label->value] = true;
                    $item = $actual->get($label->value);
                    $routineSchedule->push((object)[
                        'routine' => $routine->name, 'exam' => $label->value,
                        'start' => $item?->exam_start_at, 'end' => $item?->exam_end_at,
                    ]);
                }
                foreach (($items[$routine->id] ?? collect()) as $item) {
                    if (isset($seen[$item->exam_name])) continue;
                    $routineSchedule->push((object)[
                        'routine' => $routine->name, 'exam' => $item->exam_name,
                        'start' => $item->exam_start_at, 'end' => $item->exam_end_at,
                    ]);
                }
            }
        }
        return view('admin.exam-section.auto-question-paper-settings', compact('cat', 'sub', 'scopeKey', 'settings', 'routineSchedule'));
    }
    public function savePaperSettings(int $category, Request $request)
    {
        $subId = $request->filled('subcategory_id') ? (int) $request->input('subcategory_id') : null;
        [$cat, $sub] = $this->paperSettingScope($category, $subId);
        $data = $request->validate([
            'header_text' => 'nullable|string|max:10000', 'details' => 'nullable|string|max:10000',
            'banner' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'question_limit' => 'required|integer|min:1|max:10000',
            'exam_minutes' => 'required|integer|min:1|max:1440',
            'correct_mark' => 'required|numeric|min:0|max:1000',
            'wrong_deduction' => 'required|numeric|min:0|max:1000',
            'attempt_limit' => 'required|integer|min:1|max:100',
            'random_questions' => 'required|boolean', 'display_questions' => ['required',Rule::in(['all','one'])],
            'paid_exam' => ['required',Rule::in(['free','paid'])], 'email_result' => 'required|boolean',
            'student_view_result' => ['required',Rule::in(['no','yes'])],
        ]);
        $scopeKey = $sub ? 'sub:' . $sub->id : 'cat:' . $cat->id;
        $old = DB::table('lebd_exam_section_paper_settings')->where('scope_key', $scopeKey)->first();
        $existing = $old ? (json_decode($old->settings_json, true) ?: []) : [];
        // Keep pre-existing optional header/banner settings; they are no longer manually edited here.
        foreach (['header_text', 'details', 'mcq_format'] as $field) {
            if (!array_key_exists($field, $data) && array_key_exists($field, $existing)) $data[$field] = $existing[$field];
        }
        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('exam-section-paper-banners', 'public');
        } elseif (isset($existing['banner_path'])) {
            $data['banner_path'] = $existing['banner_path'];
        }
        unset($data['banner']);
        $data['mcq_format'] = in_array($data['mcq_format'] ?? 'bn', ['bn','en'],true) ? ($data['mcq_format'] ?? 'bn') : 'bn';
        $payload = ['category_id' => $cat->id, 'subcategory_id' => $sub?->id,
            'settings_json' => json_encode($data, JSON_UNESCAPED_UNICODE), 'updated_at' => now()];
        if ($old) DB::table('lebd_exam_section_paper_settings')->where('scope_key', $scopeKey)->update($payload);
        else DB::table('lebd_exam_section_paper_settings')->insert($payload + ['scope_key'=>$scopeKey,'created_at'=>now()]);
        return back()->with('success','Shared Paper Settings saved for this '.($sub ? 'Sub-category.' : 'Category.'));
    }

    public function routineTopics() { return redirect()->route('admin.exam-section.routine-topic.index', ['level'=>'department']); }
    public function packages() { return view('admin.exam-section.packages'); }
}
