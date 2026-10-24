<?php
namespace App\Http\Controllers\Admin\PremiumClass;

use App\Http\Controllers\Controller;
use App\Models\PremiumClass\PremiumClassPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public const DEPARTMENTS = [
        'civil'=>'Civil','electrical'=>'Electrical','mechanical'=>'Mechanical',
        'computer'=>'Computer','electronic'=>'Electronic','power'=>'Power',
        'rac'=>'RAC','architecture'=>'Architecture','textile'=>'Textile','agriculture'=>'Agriculture',
    ];
    public const NON_DEPARTMENTS = [
        'bangla-grammar'=>'বাংলা ব্যাকরণ','bangla-literature'=>'বাংলা সাহিত্য ও সংস্কৃতি',
        'bangladesh'=>'বাংলাদেশ বিষয়াবলি','international'=>'আন্তর্জাতিক বিষয়াবলি',
        'english'=>'ইংরেজি','math'=>'গণিত','science'=>'বিজ্ঞান ও প্রযুক্তি','others'=>'অন্যান্য',
    ];

    public function index()
    {
        $packages=PremiumClassPackage::orderBy('sort_order')->orderByDesc('id')->get();
        return view('admin.premium-class.packages.index', [
            'packages'=>$packages,'departments'=>self::DEPARTMENTS,'nonDepartments'=>self::NON_DEPARTMENTS,
        ]);
    }

    public function create()
    {
        return view('admin.premium-class.packages.form', [
            'package'=>null,'departments'=>self::DEPARTMENTS,'nonDepartments'=>self::NON_DEPARTMENTS,
        ]);
    }

    public function edit(PremiumClassPackage $package)
    {
        return view('admin.premium-class.packages.form', [
            'package'=>$package,'departments'=>self::DEPARTMENTS,'nonDepartments'=>self::NON_DEPARTMENTS,
        ]);
    }

    private function checkDatabase(): void
    {
        if (!Schema::hasTable('lebd_premium_class_packages') || !Schema::hasColumn('lebd_premium_class_packages','department_categories')) {
            abort(503, 'Fix108 Premium Package SQL has not been imported in phpMyAdmin.');
        }
    }

    private function data(Request $request): array
    {
        $this->checkDatabase();
        $data=$request->validate([
            'name'=>['required','string','max:150'],
            'package_type'=>['required',Rule::in(['department','non-department','combined'])],
            'description'=>['nullable','string','max:1500'],
            'department_categories'=>['nullable','array','max:10'],
            'department_categories.*'=>['string',Rule::in(array_keys(self::DEPARTMENTS))],
            'non_department_categories'=>['nullable','array','max:8'],
            'non_department_categories.*'=>['string',Rule::in(array_keys(self::NON_DEPARTMENTS))],
            'duration_value'=>['required','integer','min:1','max:3650'],
            'duration_unit'=>['required',Rule::in(['days','weeks','months','years'])],
            'price'=>['required','numeric','min:0','max:9999999.99'],
            'discount_amount'=>['required','numeric','min:0','max:9999999.99'],
            'sort_order'=>['nullable','integer','min:0','max:999999'],
            'is_active'=>['required','boolean'],
        ]);
        $departments=array_values(array_unique($data['department_categories']??[]));
        $nonDepartments=array_values(array_unique($data['non_department_categories']??[]));
        if($data['package_type']==='department')$nonDepartments=[];
        if($data['package_type']==='non-department')$departments=[];
        if(($data['package_type']==='department'||$data['package_type']==='combined') && count($departments)===0){
            throw \Illuminate\Validation\ValidationException::withMessages(['department_categories'=>'কমপক্ষে একটি Department নির্বাচন করুন।']);
        }
        if(($data['package_type']==='non-department'||$data['package_type']==='combined') && count($nonDepartments)===0){
            throw \Illuminate\Validation\ValidationException::withMessages(['non_department_categories'=>'কমপক্ষে একটি Non-Department নির্বাচন করুন।']);
        }
        $price=(float)$data['price'];$discount=(float)$data['discount_amount'];
        if($discount>$price){
            throw \Illuminate\Validation\ValidationException::withMessages(['discount_amount'=>'Discount মূল মূল্যের বেশি হতে পারবে না।']);
        }
        $unit=$data['duration_unit'];$n=(int)$data['duration_value'];
        $days=$n * ['days'=>1,'weeks'=>7,'months'=>30,'years'=>365][$unit];
        $data['validity_days']=$days;
        $data['department_categories']=$departments;
        $data['non_department_categories']=$nonDepartments;
        $data['discount_amount']=round($discount,2);
        $data['final_price']=round($price-$discount,2);
        $data['sort_order']=(int)($data['sort_order']??0);
        return $data;
    }

    public function store(Request $request)
    {
        PremiumClassPackage::create($this->data($request));
        return redirect()->route('admin.premium-class-packages.index')->with('success','Premium Class Package তৈরি হয়েছে।');
    }
    public function update(Request $request, PremiumClassPackage $package)
    {
        $package->update($this->data($request));
        return redirect()->route('admin.premium-class-packages.index')->with('success','Premium Class Package আপডেট হয়েছে।');
    }
    public function destroy(PremiumClassPackage $package)
    {
        $package->update(['is_active'=>false]);
        return redirect()->route('admin.premium-class-packages.index')->with('success','Package নিষ্ক্রিয় করা হয়েছে।');
    }
}
