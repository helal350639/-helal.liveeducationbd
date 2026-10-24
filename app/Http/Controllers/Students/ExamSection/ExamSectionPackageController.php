<?php
namespace App\Http\Controllers\Students\ExamSection;

use App\Http\Controllers\Controller;
use App\Services\ExamSectionMonthlyAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExamSectionPackageController extends Controller
{
    public function index()
    {
        $packages=DB::table('lebd_exam_section_packages')->where('status','Active')->orderBy('sort_order')->orderBy('id')->get();
        $scopes=Schema::hasTable('lebd_exam_section_package_scopes') ? DB::table('lebd_exam_section_package_scopes as s')
            ->leftJoin('lebd_exam_section_categories as c','c.id','=','s.category_id')
            ->leftJoin('lebd_exam_section_subcategories as sub','sub.id','=','s.subcategory_id')
            ->whereIn('s.package_id',$packages->pluck('id')->all())
            ->select('s.package_id','c.name as category_name','sub.name as subcategory_name')->get()->groupBy('package_id') : collect();
        $student=Auth::guard('student')->user();
        $activeAccesses = collect();
        if ($student && Schema::hasTable('lebd_exam_section_student_package_access')) {
            $activeAccesses = DB::table('lebd_exam_section_student_package_access')
                ->where('student_id', $student->id)->where('status', 'active')
                ->where('starts_at', '<=', now('Asia/Dhaka')->format('Y-m-d H:i:s'))
                ->where('ends_at', '>', now('Asia/Dhaka')->format('Y-m-d H:i:s'))
                ->orderByDesc('ends_at')->get()->keyBy('package_id');
        }
        $used=$student && Schema::hasTable('lebd_exam_section_student_attempts') ? ExamSectionMonthlyAccess::used((int)$student->id) : null;
        return view('student.exam-section.packages',compact('packages','scopes','used','activeAccesses'));
    }
}
