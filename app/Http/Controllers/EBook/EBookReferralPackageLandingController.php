<?php
namespace App\Http\Controllers\EBook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\EBook\EBookReferralSettlement;

/** Display-only package-specific referral landing. Does not initiate financial transactions. */
class EBookReferralPackageLandingController extends Controller
{
    public function show(Request $request, int $package)
    {
        if ($request->boolean('clear_referral')) {
            $request->session()->forget(EBookReferralSettlement::SESSION_KEY);
            $cat = DB::table('lebd_ebook_package_categories as pc')
                ->join('lebd_ebook_categories as c','c.id','=','pc.category_id')
                ->where('pc.package_id',$package)->where('c.status','Active')
                ->select('c.id','c.kind')->first();
            abort_unless($cat,404);
            return redirect()->route('student.ebook.packages',['kind'=>$cat->kind,'category'=>$cat->id]);
        }
        $ref = trim((string) $request->query('ref', ''));
        abort_unless($ref !== '' && strlen($ref) <= 20 && preg_match('/^LEBD[0-9]+$/', $ref), 404);
        $owner = DB::table('students')->where('referral_id', $ref)->where('status', 'Active')
            ->select('id', 'name')->first();
        abort_unless($owner, 404);
        $p = DB::table('lebd_ebook_packages')->where('id', $package)->where('status', 'Active')->first();
        abort_unless($p, 404);
        $service = app(EBookReferralSettlement::class);
        // A student must currently hold THIS package to distribute its referral link.
        $eligibleOwner = $service->owner($package, $ref);
        abort_unless($eligibleOwner && (int)$eligibleOwner->id === (int)$owner->id,404);
        $cat = DB::table('lebd_ebook_package_categories as pc')
            ->join('lebd_ebook_categories as c','pc.category_id','=','c.id')
            ->where('pc.package_id',$package)->where('c.status','Active')
            ->orderBy('c.id')->select('c.id','c.name','c.kind')->first();
        abort_unless($cat,404);
        $buyerId = auth('student')->id();
        $quote = $service->quote($p,$ref,$buyerId ? (int)$buyerId : null);
        $base=$quote['base']/100;$discount=$quote['discount']/100;
        $fee=$quote['fee']/100;$payable=$quote['total']/100;
        // Preserve attribution only for this specific package. The checkout re-validates server-side.
        $request->session()->put(EBookReferralSettlement::SESSION_KEY,
            ['package_id'=>$package,'ref'=>$ref]);
        $owned = $buyerId && (DB::table('lebd_ebook_entitlements')
            ->where('user_id',$buyerId)->where('package_id',$package)
            ->where('status','Active')->where('expires_at','>',now('Asia/Dhaka'))->exists()
            || (Schema::hasTable('lebd_ebook_manual_grants') &&
                DB::table('lebd_ebook_manual_grants')->where('student_id',$buyerId)
                ->where('package_id',$package)->where('ends_at','>',now('Asia/Dhaka'))->exists()));
        return view('ebook.referral-package', compact('p','owner','cat','base','discount','fee','payable','owned'));
    }

}
