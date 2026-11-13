<?php
namespace App\Http\Controllers\Students\PremiumClass;

use App\Http\Controllers\Controller;
use App\Models\PremiumClass\LebdRecordedVideo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PremiumPurchaseLandingController extends Controller
{
    private function ownedPaidOrder(int $orderId)
    {
        $studentId = auth('student')->id();
        abort_unless($studentId, 401);
        $order = DB::table('lebd_premium_class_orders')
            ->where('id', $orderId)
            ->where('student_id', $studentId)
            ->where('status', 'paid')
            ->when(\App\Services\PremiumClass\PremiumPackageAccessRevocation::available(), function($q) use ($studentId) {
                $q->whereNotExists(function($sub) use ($studentId) {
                    $sub->selectRaw('1')->from('lebd_premium_package_access_revocations as rv')
                        ->whereColumn('rv.package_id','lebd_premium_class_orders.package_id')
                        ->where('rv.student_id',$studentId)->whereNotNull('rv.revoked_at');
                });
            })->first();
        abort_unless($order, 404);
        return $order;
    }

    public function success(int $order)
    {
        $paid = $this->ownedPaidOrder($order);
        $packageName = DB::table('lebd_premium_class_packages')
            ->where('id', $paid->package_id)->value('name') ?: 'Premium Package';
        return redirect()->route('students.premium.video.classes')
            ->with('success', 'অভিনন্দন! আপনার Premium Video Package সফলভাবে কেনা হয়েছে। Premium Class Access চালু হয়েছে।')
            ->with('purchased_package_name', $packageName);
    }

    // Only redirect to a relative local page captured before visiting the package catalog.
    private function originalVideoUrl(): string
    {
        $path = session('lebd_premium_video_origin');
        if (is_string($path) && str_starts_with($path, '/')
            && !str_starts_with($path, '//')
            && !str_contains($path, "\\")
            && !preg_match('/[\r\n]/', $path)
            && !str_starts_with($path, '/premium-recordings/checkout/')
            && !str_starts_with($path, '/premium-recordings/purchased/')) {
            return url($path);
        }
        // If no originating video URL was recorded, use the existing safe catalog route.
        return route('premium.recordings.packages');
    }

    public function videos(int $order)
    {
        $paid = $this->ownedPaidOrder($order);
        abort_if(!$paid->expires_at || now()->greaterThanOrEqualTo($paid->expires_at), 403, 'Package access has expired.');

        $department = json_decode($paid->department_categories ?: '[]', true);
        $nonDepartment = json_decode($paid->non_department_categories ?: '[]', true);
        $department = is_array($department) ? array_values(array_filter($department, 'is_string')) : [];
        $nonDepartment = is_array($nonDepartment) ? array_values(array_filter($nonDepartment, 'is_string')) : [];

        // Safe compatibility guard: this older database schema lacks the category
        // columns used in the earlier version. Never expose all premium videos.
        $hasMappedColumns = Schema::hasColumn('lebd_recorded_videos', 'premium_group')
            && Schema::hasColumn('lebd_recorded_videos', 'premium_topic');

        $videos = LebdRecordedVideo::query()->where('is_published', 1);
        if (!$hasMappedColumns || (!$department && !$nonDepartment)) {
            $videos->whereRaw('1 = 0');
        } else {
            $videos->where(function ($q) use ($department, $nonDepartment) {
                if ($department) {
                    $q->orWhere(function ($sub) use ($department) {
                        $sub->where('premium_group', 'department')->whereIn('premium_topic', $department);
                    });
                }
                if ($nonDepartment) {
                    $q->orWhere(function ($sub) use ($nonDepartment) {
                        $sub->where('premium_group', 'non-department')->whereIn('premium_topic', $nonDepartment);
                    });
                }
            });
        }
        $videos = $videos->orderBy('sort_order')->orderBy('id')->paginate(24);
        $packageName = DB::table('lebd_premium_class_packages')
            ->where('id', $paid->package_id)->value('name') ?: 'Premium Package';
        return view('premium-class.purchased-videos', compact('videos', 'paid', 'packageName'));
    }
}
