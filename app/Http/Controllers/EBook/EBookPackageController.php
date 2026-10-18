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
        return view('admin.ebook-packages.index',compact('packages','counts'));
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
        $days=(int)$data['duration_value']*(['days'=>1,'months'=>30,'years'=>365][$data['duration_unit']]);
        $ids=array_map('intval',$data['category_ids']);
        $active=DB::table('lebd_ebook_categories')->whereIn('id',$ids)->where('status','Active')->count();
        if($active!==count($ids))return back()->withErrors(['category_ids'=>'শুধু Active E-Book Category নির্বাচন করুন।'])->withInput();
        DB::transaction(function()use($data,$days,$ids,$package){
            $row=['name'=>$data['name'],'description'=>$data['description']??null,'duration_days'=>$days,
                'duration_value'=>(int)$data['duration_value'],'duration_unit'=>$data['duration_unit'],
                'main_price'=>$data['main_price'],'discount_amount'=>$data['discount_amount'],
                'price'=>round((float)$data['main_price']-(float)$data['discount_amount'],2),
                'sort_order'=>$data['sort_order'],'offer_ends_at'=>$data['offer_ends_at']??null,'status'=>$data['status'],'updated_at'=>now()];
            if($package){DB::table('lebd_ebook_packages')->where('id',$package)->update($row);$id=$package;}
            else{$row['created_at']=now();$id=DB::table('lebd_ebook_packages')->insertGetId($row);}
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
        $packages=DB::table('lebd_ebook_packages as p')->join('lebd_ebook_package_categories as pc','p.id','=','pc.package_id')->where('pc.category_id',$category)->where('p.status','Active')->orderBy('p.sort_order')->orderBy('p.price')->select('p.*')->get();
        return view('ebook.packages',compact('kind','cat','packages'));
    }
}
