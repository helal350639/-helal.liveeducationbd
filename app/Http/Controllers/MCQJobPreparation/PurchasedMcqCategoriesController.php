<?php
namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use App\Services\MCQSubscription\ActivePackageAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurchasedMcqCategoriesController extends Controller
{
    private const DEPARTMENTS = ['Civil','Electrical','Mechanical','Computer','Electronic','Power','RAC','Architecture','Textile','Agriculture'];
    private const GENERAL = ['Bangla','English','Bangladesh','International','Math','Others'];

    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();
        $subscriptions = collect();
        $categoryKeys = [];
        if ($student) {
            $subscriptions = ActivePackageAccess::rows((int)$student->id);
            // Only actually assigned, active job_prep_categories may unlock a card.
            if ($subscriptions->isNotEmpty() && Schema::hasTable('mcq_subscription_package_categories') && Schema::hasTable('job_prep_categories')) {
                $assigned = DB::table('mcq_subscription_package_categories as pc')
                    ->join('job_prep_categories as c','c.id','=','pc.category_id')
                    ->whereIn('pc.package_id', $subscriptions->pluck('package_id')->all())
                    ->where('c.status','Active')
                    ->whereIn('c.kind',['department','non-department'])
                    ->select('c.id','c.name','c.kind')->distinct()->get();
                // Names are the existing card labels; do not authorize by access_type alone.
                foreach ($assigned as $category) {
                    $categoryKeys[] = (string)$category->name;
                }
                $categoryKeys = array_values(array_unique($categoryKeys));
            }
        }
        return view('mcq-study.purchased-categories', [
            'departments' => self::DEPARTMENTS,
            'general' => self::GENERAL,
            'subscriptions' => $subscriptions,
            'categoryKeys' => $categoryKeys,
            'isStudent' => (bool)$student,
        ]);
    }
}
