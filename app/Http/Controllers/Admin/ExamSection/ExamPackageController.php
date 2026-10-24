<?php
namespace App\Http\Controllers\Admin\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExamPackageController extends Controller
{
    private const PACKAGES = 'lebd_exam_section_packages';
    private const SELECTIONS = 'lebd_exam_section_package_scopes';

    private function groups()
    {
        $categories = DB::table('lebd_exam_section_categories')->where('status', 'Active')->orderBy('sort_order')->orderBy('name')->get();
        $subs = DB::table('lebd_exam_section_subcategories')->where('status', 'Active')->whereIn('category_id', $categories->pluck('id')->all())
            ->orderBy('sort_order')->orderBy('name')->get()->groupBy('category_id');
        return $categories->map(function ($c) use ($subs) { $c->children = $subs->get($c->id, collect()); return $c; });
    }
    public function index()
    {
        $packages = DB::table(self::PACKAGES)->orderBy('sort_order')->orderByDesc('id')->paginate(25);
        $counts = DB::table(self::SELECTIONS)->select('package_id')->selectRaw('COUNT(*) AS total')->groupBy('package_id')->pluck('total','package_id');
        return view('admin.exam-section.packages', compact('packages','counts'));
    }
    public function create()
    {
        $groups = $this->groups(); $package = null; $selected = [];
        return view('admin.exam-section.package-form', compact('groups','package','selected'));
    }
    public function edit(int $package)
    {
        $package = DB::table(self::PACKAGES)->where('id',$package)->first(); abort_unless($package,404);
        $groups = $this->groups();
        $selected = DB::table(self::SELECTIONS)->where('package_id',$package->id)->get()->map(fn($x) => $x->subcategory_id ? 's:'.$x->subcategory_id : 'c:'.$x->category_id)->all();
        return view('admin.exam-section.package-form', compact('groups','package','selected'));
    }
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'=>'required|string|max:190', 'description'=>'nullable|string|max:5000',
            'duration'=>'required|integer|min:1|max:36500',
            'duration_unit'=>['required',Rule::in(['Days','Weeks','Months','Years'])],
            'main_price'=>'required|numeric|min:0|max:99999999',
            'discount_amount'=>'required|numeric|min:0|max:99999999',
            'sort_order'=>'required|integer|min:0|max:999999',
            'status'=>['required',Rule::in(['Active','Inactive'])],
            'scopes'=>'required|array|min:1', 'scopes.*'=>'required|string|regex:/^[cs]:[0-9]+$/',
        ]);
        if ((float)$data['discount_amount'] > (float)$data['main_price']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['discount_amount'=>'Discount cannot exceed main price.']);
        }
        $valid = [];
        foreach ($this->groups() as $category) {
            if ($category->children->isNotEmpty()) {
                foreach ($category->children as $sub) $valid['s:'.$sub->id] = ['category_id'=>$category->id,'subcategory_id'=>$sub->id];
            } else $valid['c:'.$category->id] = ['category_id'=>$category->id,'subcategory_id'=>null];
        }
        $selected = array_unique($data['scopes']);
        foreach ($selected as $scope) {
            if (!isset($valid[$scope])) throw \Illuminate\Validation\ValidationException::withMessages(['scopes'=>'Invalid or inactive Category/Sub-category: '.$scope]);
        }
        $rows = array_map(fn($k)=>$valid[$k], $selected);
        unset($data['scopes']);
        $data['final_price'] = round((float)$data['main_price']-(float)$data['discount_amount'],2);
        return [$data,$rows];
    }
    public function store(Request $request)
    {
        [$data,$scopes]=$this->validated($request);
        DB::transaction(function () use ($data,$scopes) {
            $id=DB::table(self::PACKAGES)->insertGetId($data+['created_at'=>now(),'updated_at'=>now()]);
            foreach($scopes as $scope) DB::table(self::SELECTIONS)->insert($scope+['package_id'=>$id,'created_at'=>now()]);
        });
        return redirect()->route('admin.exam-section.packages')->with('success','Exam package created.');
    }
    public function update(Request $request,int $package)
    {
        abort_unless(DB::table(self::PACKAGES)->where('id',$package)->exists(),404);
        [$data,$scopes]=$this->validated($request);
        DB::transaction(function () use ($package,$data,$scopes) {
            DB::table(self::PACKAGES)->where('id',$package)->update($data+['updated_at'=>now()]);
            DB::table(self::SELECTIONS)->where('package_id',$package)->delete();
            foreach($scopes as $scope) DB::table(self::SELECTIONS)->insert($scope+['package_id'=>$package,'created_at'=>now()]);
        });
        return redirect()->route('admin.exam-section.packages')->with('success','Exam package updated.');
    }
    public function destroy(int $package)
    {
        abort_unless(DB::table(self::PACKAGES)->where('id',$package)->exists(),404);
        DB::transaction(function() use($package){DB::table(self::SELECTIONS)->where('package_id',$package)->delete();DB::table(self::PACKAGES)->where('id',$package)->delete();});
        return back()->with('success','Package deleted.');
    }
}
