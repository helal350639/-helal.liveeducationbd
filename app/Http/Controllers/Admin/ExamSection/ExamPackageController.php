<?php
namespace App\Http\Controllers\Admin\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
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
        $counts = DB::table(self::SELECTIONS)->select('package_id')->selectRaw('COUNT(*) AS total')
            ->groupBy('package_id')->pluck('total','package_id');
        $studentReport = app(\App\Services\ExamSection\ExamPackageStudentReport::class);
        $studentStats = $studentReport->summary($packages->pluck('id')->all());
        return view('admin.exam-section.packages', compact('packages','counts','studentStats'));
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
            'referral_discount'=>'nullable|numeric|min:0|max:9999999',
            'referrer_bonus'=>'nullable|numeric|min:0|max:9999999',
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
        $data['referral_discount']=round((float)($data['referral_discount']??0),2);
        $data['referrer_bonus']=round((float)($data['referrer_bonus']??0),2);
        if ($data['referral_discount'] > max(0,$data['final_price']-1)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['referral_discount'=>'Referral discount must leave at least BDT 1 payable.']);
        }
        if (!Schema::hasColumn(self::PACKAGES,'referral_discount') || !Schema::hasColumn(self::PACKAGES,'referrer_bonus')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['referral_discount'=>'Import the Exam Section MANUAL SQL before saving packages.']);
        }
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
        DB::table(self::PACKAGES)->where('id',$package)->update(['status'=>'Inactive','updated_at'=>now()]);
        return back()->with('success','Package disabled. Historic orders and access preserved.');
    }

    private function adminOnly(): void
    {
        $user=Auth::user();
        abort_unless($user && method_exists($user,'AuthorizeRoles') && $user->AuthorizeRoles(['SuperAdmin','Admin']),403);
    }
    public function students(Request $request, int $package)
    {
        $this->adminOnly();
        $plan=DB::table(self::PACKAGES)->find($package); abort_unless($plan,404);
        $report=app(\App\Services\ExamSection\ExamPackageStudentReport::class);
        $stats=$report->summary([$package])[$package] ?? $report->blank();
        $rows=$report->students($package,trim((string)$request->query('q','')));
        return view('admin.exam-section.package-students',compact('plan','rows','stats'));
    }
    /** Grant one student without creating a payment order or touching wallet balance. */
    public function grantStudentId(int $package, int $studentId): bool
    {
        $this->adminOnly();
        abort_unless(Schema::hasTable('lebd_exam_section_manual_grants'),503,'Import Exam Section manual SQL first.');
        $plan=DB::table(self::PACKAGES)->where('id',$package)->where('status','Active')->firstOrFail();
        abort_unless(DB::table('students')->where('id',$studentId)->exists(),422,'Student not found.');
        return DB::transaction(function()use($plan,$studentId) {
            DB::table('students')->where('id',$studentId)->lockForUpdate()->first();
            $now=Carbon::now('Asia/Dhaka');
            $active=DB::table('lebd_exam_section_student_package_access')->where('student_id',$studentId)
                ->where('package_id',$plan->id)->where('status','active')
                ->where('starts_at','<=',$now->toDateTimeString())->where('ends_at','>',$now->toDateTimeString())->exists();
            if($active) return false;
            $end=$now->copy();
            $qty=max(1,(int)$plan->duration);
            switch(strtolower($plan->duration_unit)) {
                case 'days': $end->addDays($qty); break;
                case 'weeks': $end->addWeeks($qty); break;
                case 'years': $end->addYearsNoOverflow($qty); break;
                default: $end->addMonthsNoOverflow($qty);
            }
            $accessId=DB::table('lebd_exam_section_student_package_access')->insertGetId([
                'student_id'=>$studentId,'package_id'=>$plan->id,'status'=>'active',
                'starts_at'=>$now->toDateTimeString(),'ends_at'=>$end->toDateTimeString(),
                'payment_reference'=>'MANUAL-EXAM-'.Auth::id().'-'.\Illuminate\Support\Str::random(20),
                'created_at'=>now()
            ]);
            DB::table('lebd_exam_section_manual_grants')->insert([
                'student_id'=>$studentId,'package_id'=>$plan->id,'access_id'=>$accessId,
                'added_by'=>Auth::id(),'starts_at'=>$now->toDateTimeString(),
                'ends_at'=>$end->toDateTimeString(),'status'=>'active','created_at'=>now(),'updated_at'=>now(),
            ]);
            return true;
        },3);
    }
    public function addStudent(Request $request,int $package)
    {
        $this->adminOnly();
        $data=$request->validate(['student_id'=>'required|integer|exists:students,id']);
        $added=$this->grantStudentId($package,(int)$data['student_id']);
        return back()->with($added ? 'success' : 'info',
            $added ? 'Manual student access granted.' : 'Student already has active access to this package.');
    }
    public function removeStudent(Request $request,int $package,int $grant)
    {
        $this->adminOnly();
        $request->validate(['confirm_remove'=>'required|accepted']);
        abort_unless(Schema::hasTable('lebd_exam_section_manual_grants'),503);
        DB::transaction(function()use($package,$grant){
            $row=DB::table('lebd_exam_section_manual_grants')->where('id',$grant)
                ->where('package_id',$package)->lockForUpdate()->first();abort_unless($row,404);
            DB::table('students')->where('id',$row->student_id)->lockForUpdate()->first();
            if($row->status!=='active') return;
            // Only the access row CREATED by this manual grant may be revoked.
            DB::table('lebd_exam_section_student_package_access')->where('id',$row->access_id)
                ->where('student_id',$row->student_id)->where('package_id',$package)
                ->where('status','active')->where('payment_reference','LIKE','MANUAL-EXAM-%')
                ->update(['status'=>'revoked']);
            DB::table('lebd_exam_section_manual_grants')->where('id',$grant)->update([
                'status'=>'removed','removed_by'=>Auth::id(),'removed_at'=>now(),'updated_at'=>now(),
            ]);
        },3);
        return back()->with('success','Manual grant removed. Paid orders and other access remain unchanged.');
    }
}
