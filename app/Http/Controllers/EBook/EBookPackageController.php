<?php
namespace App\Http\Controllers\EBook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EBookPackageController extends Controller
{
    private function checkAdmin(): void
    {
        abort_unless(auth()->check(),403);
        // This application's admin routes use the web guard; preserve that convention.
        // Assign admin-only authorization middleware in routes/web.php if available.
    }
    public function index()
    {
        $this->checkAdmin();
        $packages=DB::table('lebd_ebook_packages')->orderBy('sort_order')->orderByDesc('id')->get();
        $counts=DB::table('lebd_ebook_package_categories')->select('package_id')->selectRaw('COUNT(*) as total')->groupBy('package_id')->pluck('total','package_id');
        $studentCounts = app(\App\Services\EBook\EBookStudentReport::class)->summary($packages->pluck('id')->all());
        return view('admin.ebook-packages.index',compact('packages','counts','studentCounts'));
    }
    public function form(?int $package=null)
    {
        $this->checkAdmin();
        $item=$package ? DB::table('lebd_ebook_packages')->find($package) : null;
        abort_if($package && !$item,404);
        $categories=DB::table('lebd_ebook_categories')->orderBy('kind')->orderBy('sort_order')->orderBy('id')->get();
        $selected=$item ? DB::table('lebd_ebook_package_categories')->where('package_id',$item->id)->pluck('category_id')->map(fn($n)=>(int)$n)->all() : [];
        return view('admin.ebook-packages.form',compact('item','categories','selected'));
    }
    public function save(Request $request,?int $package=null)
    {
        $this->checkAdmin();
        if($package) abort_unless(DB::table('lebd_ebook_packages')->where('id',$package)->exists(),404);
        $data=$request->validate([
            'name'=>'required|string|max:180', 'description'=>'nullable|string|max:3000',
            'duration_value'=>'required|integer|min:1|max:3650',
            'duration_unit'=>['required',Rule::in(['days','months','years'])],
            'main_price'=>'required|numeric|min:0|max:99999999',
            'discount_amount'=>'required|numeric|min:0|lte:main_price',
            'sort_order'=>'required|integer|min:0|max:100000',
            'offer_ends_at'=>'nullable|date',
            'status'=>['required',Rule::in(['Active','Inactive'])],
            'category_ids'=>'required|array|min:1',
            'category_ids.*'=>'required|integer|distinct|exists:lebd_ebook_categories,id',
        ]);
        // Optional referral settings: preserve current values unless the actual Admin form sends them.
        // Missing DB columns are NOT silently ignored when the request includes these fields.
        $referralFields = ['referral_discount','referrer_bonus','extra_fee','extra_fee_note'];
        $submittedReferral = array_filter($referralFields, fn($field) => $request->exists($field));
        $referral = [];
        if ($submittedReferral) {
            foreach ($referralFields as $field) {
                abort_unless(Schema::hasColumn('lebd_ebook_packages', $field), 503, 'E-Book referral SQL must be imported before saving referral settings.');
            }
            $referral = $request->validate([
                'referral_discount' => ['nullable','numeric','min:0','max:9999999.99'],
                'referrer_bonus' => ['nullable','numeric','min:0','max:9999999.99'],
                'extra_fee' => ['nullable','numeric','min:0','max:9999999.99'],
                'extra_fee_note' => ['nullable','string','max:255'],
            ]);
            $fee = round((float)($referral['extra_fee'] ?? ($package ? DB::table('lebd_ebook_packages')->where('id',$package)->value('extra_fee') : 0)), 2);
            $note = trim((string)($referral['extra_fee_note'] ?? ($package ? DB::table('lebd_ebook_packages')->where('id',$package)->value('extra_fee_note') : '')));
            if ($fee > 0 && $note === '') {
                throw \Illuminate\Validation\ValidationException::withMessages(['extra_fee_note'=>'Extra Fee-এর কারণ লিখুন।']);
            }
            $base = max(0, round((float)$data['main_price'] - (float)$data['discount_amount'], 2));
            $refDiscount = round((float)($referral['referral_discount'] ?? ($package ? DB::table('lebd_ebook_packages')->where('id',$package)->value('referral_discount') : 0)), 2);
            if ($refDiscount > max(0, $base + $fee - 1)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['referral_discount'=>'Referral Discount দিয়ে Payable ৳১-এর নিচে নেওয়া যাবে না।']);
            }
            if (array_key_exists('extra_fee', $referral) && $fee == 0) $referral['extra_fee_note'] = null;
        }
        $days=(int)$data['duration_value']*(['days'=>1,'months'=>30,'years'=>365][$data['duration_unit']]);
        $ids=array_map('intval',$data['category_ids']);
        $active=DB::table('lebd_ebook_categories')->whereIn('id',$ids)->where('status','Active')->count();
        if($active!==count($ids))return back()->withErrors(['category_ids'=>'শুধু Active E-Book Category নির্বাচন করুন।'])->withInput();
        DB::transaction(function()use($data,$days,$ids,$package,$referral){
            $row=['name'=>$data['name'],'description'=>$data['description']??null,'duration_days'=>$days,
                'duration_value'=>(int)$data['duration_value'],'duration_unit'=>$data['duration_unit'],
                'main_price'=>$data['main_price'],'discount_amount'=>$data['discount_amount'],
                'price'=>round((float)$data['main_price']-(float)$data['discount_amount'],2),
                'sort_order'=>$data['sort_order'],'offer_ends_at'=>$data['offer_ends_at']??null,'status'=>$data['status'],'updated_at'=>now()];
            if($package){DB::table('lebd_ebook_packages')->where('id',$package)->update($row);$id=$package;}
            else{$row['created_at']=now();$id=DB::table('lebd_ebook_packages')->insertGetId($row);}
            if ($referral) DB::table('lebd_ebook_packages')->where('id',$id)->update($referral);
            DB::table('lebd_ebook_package_categories')->where('package_id',$id)->delete();
            DB::table('lebd_ebook_package_categories')->insert(array_map(fn($c)=>['package_id'=>$id,'category_id'=>$c],$ids));
        });
        return redirect()->route('admin.ebook-packages.index')->with('success','E-Book Package saved.');
    }
    public function delete(int $package)
    {
        $this->checkAdmin();
        // Disable package instead of deleting a historic purchased/linked package.
        DB::table('lebd_ebook_packages')->where('id',$package)->update(['status'=>'Inactive','updated_at'=>now()]);
        return back()->with('success','Package disabled.');
    }
    public function catalog(Request $request,string $kind,int $category)
    {
        abort_unless(in_array($kind,['department','non-department'],true),404);
        $cat=DB::table('lebd_ebook_categories')->where('id',$category)->where('kind',$kind)->where('status','Active')->first()??abort(404);
        $packages=DB::table('lebd_ebook_packages as p')
            ->join('lebd_ebook_package_categories as pc','p.id','=','pc.package_id')
            ->where('pc.category_id',$category)->where('p.status','Active')
            ->orderBy('p.sort_order')->orderBy('p.price')->select('p.*')->get();

        $studentId = auth('student')->id();
        $activePackageIds = [];
        if ($studentId && $packages->isNotEmpty()) {
            $packageIds = $packages->pluck('id')->map(fn($id)=>(int)$id)->all();
            $now = now('Asia/Dhaka')->toDateTimeString();
            $active = [];

            // Administrator manual assignment is an entitlement, not a payment order.
            if (Schema::hasTable('lebd_ebook_manual_grants')) {
                $manualIds = DB::table('lebd_ebook_manual_grants')
                    ->where('student_id',$studentId)
                    ->whereIn('package_id',$packageIds)
                    ->where('starts_at','<=',$now)->where('ends_at','>',$now)
                    ->pluck('package_id');
                foreach ($manualIds as $id) $active[(int)$id] = true;
            }

            // Existing paid/referral access recorded in the entitlement table.
            if (Schema::hasTable('lebd_ebook_entitlements')) {
                $entitledIds = DB::table('lebd_ebook_entitlements')
                    ->where('user_id',$studentId)->where('category_id',$category)
                    ->whereIn('package_id',$packageIds)
                    ->where('status','Active')->where('starts_at','<=',$now)->where('expires_at','>',$now)
                    ->pluck('package_id');
                foreach ($entitledIds as $id) $active[(int)$id] = true;
            }

            $activePackageIds = array_map('intval',array_keys($active));
        }

        $ebookAccess = app(\App\Services\EBook\EBookAccess::class);
        foreach ($packages as $package) {
            $packageId = (int) $package->id;
            $package->has_active_access = $studentId > 0
                && $ebookAccess->packageAccess($studentId, $packageId, (int) $category);
            $package->is_purchased = $studentId > 0
                && $ebookAccess->isPaidPackage($studentId, $packageId, (int) $category);
        }

        // Read-only category labels used by the refreshed catalog cards and filters.
        // This does not alter package eligibility, pricing, checkout, or payment processing.
        $packageCategoryMap = collect();
        if ($packages->isNotEmpty()) {
            $packageCategoryMap = DB::table('lebd_ebook_package_categories as pc')
                ->join('lebd_ebook_categories as c', 'c.id', '=', 'pc.category_id')
                ->whereIn('pc.package_id', $packages->pluck('id')->all())
                ->select('pc.package_id', 'c.name', 'c.kind')
                ->orderBy('c.name')
                ->get()
                ->groupBy('package_id');
        }

        foreach ($packages as $package) {
            $linkedCategories = $packageCategoryMap->get($package->id, collect());
            $package->catalog_category_labels = $linkedCategories->pluck('name')->filter()->unique()->values();
            $linkedKinds = $linkedCategories->pluck('kind')->filter()->unique()->values();
            $package->catalog_filter_group = $linkedKinds->count() > 1 ? 'combined' : ($linkedKinds->first() ?: $kind);
        }

        return view('ebook.packages',compact('kind','cat','packages','activePackageIds'));
    }

    /**
     * Global read-only catalogue of all Active E-Book packages.
     * Package visibility depends on package status, not category status.
     * Purchase continues through the existing category-scoped checkout routes.
     */
    public function allCatalog()
    {
        $packages = collect();
        $catalogError = null;
        $activePackageIds = [];
        $purchasedPackageIds = [];

        // Fail visibly rather than returning a completely blank page when a
        // required E-Book table is absent or its schema is incomplete.
        if (!Schema::hasTable('lebd_ebook_packages')) {
            $catalogError = 'E-Book package table পাওয়া যায়নি। Admin database setup পরীক্ষা করুন।';
        } else {
            try {
                $packageQuery = DB::table('lebd_ebook_packages');
                if (Schema::hasColumn('lebd_ebook_packages', 'status')) {
                    $packageQuery->whereRaw('LOWER(TRIM(`status`)) = ?', ['active']);
                }
                if (Schema::hasColumn('lebd_ebook_packages', 'sort_order')) {
                    $packageQuery->orderBy('sort_order');
                }
                if (Schema::hasColumn('lebd_ebook_packages', 'price')) {
                    $packageQuery->orderBy('price');
                }
                $packageQuery->orderBy('id');
                $packages = $packageQuery->get();

                $categoryMap = collect();
                $activeCategoryMap = collect();
                if ($packages->isNotEmpty()
                    && Schema::hasTable('lebd_ebook_package_categories')
                    && Schema::hasTable('lebd_ebook_categories')) {

                    $packageIds = $packages->pluck('id')->map(fn ($id) => (int) $id)->all();
                    $categoryQuery = DB::table('lebd_ebook_package_categories as pc')
                        ->join('lebd_ebook_categories as c', 'c.id', '=', 'pc.category_id')
                        ->whereIn('pc.package_id', $packageIds)
                        ->select('pc.package_id', 'c.id as category_id', 'c.name', 'c.kind');

                    $hasCategoryStatus = Schema::hasColumn('lebd_ebook_categories', 'status');
                    if ($hasCategoryStatus) {
                        $categoryQuery->addSelect('c.status');
                    }
                    if (Schema::hasColumn('lebd_ebook_categories', 'sort_order')) {
                        $categoryQuery->orderBy('c.sort_order');
                    }
                    $allLinks = $categoryQuery->orderBy('c.name')->get();

                    $categoryMap = $allLinks->groupBy('package_id');
                    $activeCategoryMap = $hasCategoryStatus
                        ? $allLinks->filter(fn ($row) => strtolower(trim((string) $row->status)) === 'active')->groupBy('package_id')
                        : $categoryMap;
                }

                foreach ($packages as $package) {
                    $linked = $categoryMap->get($package->id, collect());
                    $activeLinked = $activeCategoryMap->get($package->id, collect());

                    $package->catalog_category_labels = $linked
                        ->pluck('name')->filter()->unique()->values();

                    $kinds = $linked->pluck('kind')->filter()->unique()->values();
                    $package->catalog_filter_group = $kinds->count() > 1
                        ? 'combined'
                        : ($kinds->first() ?: 'department');

                    // Purchase URLs must use a category that the checkout accepts.
                    $firstActiveCategory = $activeLinked->first();
                    $package->catalog_category_id = $firstActiveCategory
                        ? (int) $firstActiveCategory->category_id : null;
                    $package->catalog_kind = $firstActiveCategory
                        ? (string) $firstActiveCategory->kind : null;
                }

                $studentId = (int) auth('student')->id();
                if ($studentId && $packages->isNotEmpty()) {
                    $packageIds = $packages->pluck('id')->map(fn ($id) => (int) $id)->all();
                    $now = now('Asia/Dhaka')->toDateTimeString();

                    if (Schema::hasTable('lebd_ebook_payment_orders')
                        && Schema::hasColumn('lebd_ebook_payment_orders', 'student_id')
                        && Schema::hasColumn('lebd_ebook_payment_orders', 'package_id')
                        && Schema::hasColumn('lebd_ebook_payment_orders', 'status')) {
                        $purchasedPackageIds = DB::table('lebd_ebook_payment_orders')
                            ->where('student_id', $studentId)
                            ->whereIn('package_id', $packageIds)
                            ->whereIn('status', ['Complete', 'Completed', 'Paid', 'Success', 'Successful'])
                            ->pluck('package_id')->map(fn ($id) => (int) $id)
                            ->unique()->values()->all();
                    }

                    if (Schema::hasTable('lebd_ebook_entitlements')
                        && Schema::hasColumn('lebd_ebook_entitlements', 'user_id')
                        && Schema::hasColumn('lebd_ebook_entitlements', 'package_id')
                        && Schema::hasColumn('lebd_ebook_entitlements', 'status')
                        && Schema::hasColumn('lebd_ebook_entitlements', 'starts_at')
                        && Schema::hasColumn('lebd_ebook_entitlements', 'expires_at')) {
                        $activePackageIds = DB::table('lebd_ebook_entitlements')
                            ->where('user_id', $studentId)->whereIn('package_id', $packageIds)
                            ->where('status', 'Active')->where('starts_at', '<=', $now)
                            ->where('expires_at', '>', $now)
                            ->pluck('package_id')->map(fn ($id) => (int) $id)
                            ->unique()->values()->all();
                    }

                    if (Schema::hasTable('lebd_ebook_manual_grants')
                        && Schema::hasColumn('lebd_ebook_manual_grants', 'student_id')
                        && Schema::hasColumn('lebd_ebook_manual_grants', 'package_id')
                        && Schema::hasColumn('lebd_ebook_manual_grants', 'starts_at')
                        && Schema::hasColumn('lebd_ebook_manual_grants', 'ends_at')) {
                        $manualIds = DB::table('lebd_ebook_manual_grants')
                            ->where('student_id', $studentId)->whereIn('package_id', $packageIds)
                            ->where('starts_at', '<=', $now)->where('ends_at', '>', $now)
                            ->pluck('package_id')->map(fn ($id) => (int) $id)->all();
                        $activePackageIds = array_values(array_unique(array_merge($activePackageIds, $manualIds)));
                    }
                }

                foreach ($packages as $package) {
                    $packageId = (int) $package->id;
                    $package->has_active_access = in_array($packageId, $activePackageIds, true);
                    $package->has_been_purchased = in_array($packageId, $purchasedPackageIds, true);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('E-Book global premium catalogue failed', [
                    'message' => $e->getMessage(),
                ]);
                $packages = collect();
                $catalogError = 'প্যাকেজ তালিকা লোড করা যায়নি। E-Book package/category mapping ও database schema পরীক্ষা করুন।';
            }
        }

        return view('ebook.all-premium-packages', compact(
            'packages', 'activePackageIds', 'purchasedPackageIds', 'catalogError'
        ));
    }

}
