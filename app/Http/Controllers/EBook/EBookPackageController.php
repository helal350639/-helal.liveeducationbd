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
                    ->where('status','Active')->where('expires_at','>',$now)
                    ->pluck('package_id');
                foreach ($entitledIds as $id) $active[(int)$id] = true;
            }

            $activePackageIds = array_map('intval',array_keys($active));
        }

        foreach ($packages as $package) {
            $package->has_active_access = in_array((int)$package->id,$activePackageIds,true);
        }

        return view('ebook.packages',compact('kind','cat','packages','activePackageIds'));
    }
}
