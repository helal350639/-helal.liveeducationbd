<?php
namespace App\Http\Controllers\Admin\PremiumClass;

use App\Http\Controllers\Controller;
use App\Models\PremiumClass\PremiumClassPackage;
use App\Services\PremiumClass\PremiumPackageStudentReport;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\PremiumClass\PremiumPackageAccessRevocation;

class PremiumPackageStudentsReportController extends Controller
{
    public function index(Request $request, PremiumClassPackage $package)
    {
        abort_unless(Auth::user() && Auth::user()->AuthorizeRoles(['SuperAdmin','Admin']), 403);
        $method=(string)$request->query('method','all');
        abort_unless(in_array($method,['all','manual','wallet','ssl','other'],true),404);
        $search=trim((string)$request->query('q',''));
        abort_if(mb_strlen($search)>120,422,'Search text is too long.');
        $rows=PremiumPackageStudentReport::students((int)$package->id);
        if ($method!=='all') $rows=array_values(array_filter($rows,fn($row)=>isset($row['methods'][$method])));
        if ($search!=='') {
            $term=mb_strtolower($search);
            $rows=array_values(array_filter($rows,function($row)use($term){
                $s=$row['student'];
                return str_contains((string)$row['id'],$term)
                    || ($s && (str_contains(mb_strtolower((string)$s->name),$term)
                    || str_contains(mb_strtolower((string)$s->email),$term)
                    || str_contains(mb_strtolower((string)$s->contact),$term)));
            }));
        }
        $perPage=25;
        $page=max(1,(int)$request->query('page',1));
        $students=new LengthAwarePaginator(array_slice($rows,($page-1)*$perPage,$perPage),count($rows),$perPage,$page,[
            'path'=>$request->url(),'query'=>$request->query()
        ]);
        $counts=PremiumPackageStudentReport::summaries([(int)$package->id])[(int)$package->id];
        return view('admin.premium-class.packages.student-report',compact('package','students','counts','method','search'));
    }
    public function revoke(Request $request, PremiumClassPackage $package, int $student)
    {
        abort_unless(Auth::user() && Auth::user()->AuthorizeRoles(['SuperAdmin','Admin']), 403);
        abort_unless(DB::table('students')->where('id',$student)->exists(),404);
        abort_unless(PremiumPackageAccessRevocation::available(),503,'Premium package revocation SQL is not installed.');
        PremiumPackageAccessRevocation::revoke($student,(int)$package->id,(int)Auth::id());
        return redirect()->route('admin.premium-class-packages.students.report',['package'=>$package->id])
            ->with('success','Student-এর এই Premium Package-এর সব Access বন্ধ করা হয়েছে।');
    }

}
