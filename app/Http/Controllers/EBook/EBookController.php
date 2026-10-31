<?php
namespace App\Http\Controllers\EBook;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SourceCtrl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EBookReferralController extends Controller
{
    private function selection(string $kind, int $category, int $package): array
    {
        abort_unless(in_array($kind, ['department', 'non-department'], true), 404);
        $cat = DB::table('lebd_ebook_categories')->where('id', $category)
            ->where('kind', $kind)->where('status', 'Active')->first();
        abort_unless($cat, 404);
        $p = DB::table('lebd_ebook_packages as p')
            ->join('lebd_ebook_package_categories as pc', 'pc.package_id', '=', 'p.id')
            ->where('p.id', $package)->where('pc.category_id', $category)
            ->where('p.status', 'Active')->select('p.*')->first();
        abort_unless($p, 404);
        $now = Carbon::now('Asia/Dhaka');
        $offerActive = !$p->offer_ends_at || Carbon::parse($p->offer_ends_at, 'Asia/Dhaka')->endOfDay()->gte($now);
        $amount = (int) round((float) ($offerActive ? $p->price : $p->main_price) * 100);
        abort_if($amount < 100 || $amount > 50000000, 422, 'Package price is invalid.');
        abort_unless(in_array($p->duration_unit, ['days','months','years'], true) && (int) $p->duration_value >= 1, 422);
        return [$cat, $p, $amount];
    }

    private function available(object $student): int
    {
        $pending = DB::table('withdraw_requests')->where('student_id', $student->id)
            ->where('status', 'pending')->sum('amount');
        return max(0, (int) round((float) $student->referral_balance * 100) - (int) round((float) $pending * 100));
    }

    public function preview(Request $request, string $kind, int $category, int $package)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 401);
        [$cat, $p, $amount] = $this->selection($kind, $category, $package);
        $available = $this->available($student);
        $token = (string) Str::uuid();
        $request->session()->put('lebd_ebook_referral_preview', [
            'student_id' => (int) $student->id, 'kind' => $kind,
            'category_id' => $category, 'package_id' => $package, 'token' => $token,
        ]);
        return view('ebook.referral-preview', compact('cat','p','kind','category','package','amount','available','token'));
    }

    public function confirm(Request $request)
    {
        $studentId = Auth::guard('student')->id();
        abort_unless($studentId, 401);
        $request->validate(['token' => 'required|uuid', 'agree' => 'accepted']);
        $ctx = $request->session()->get('lebd_ebook_referral_preview');
        abort_unless(is_array($ctx) && (int)($ctx['student_id'] ?? 0) === (int)$studentId
            && hash_equals((string)($ctx['token'] ?? ''), (string)$request->input('token')), 419,
            'Payment preview expired. Please start again.');
        // Consume the session token before any financial write.
        $request->session()->forget('lebd_ebook_referral_preview');
        $kind = (string)$ctx['kind'];
        $category = (int)$ctx['category_id'];
        $package = (int)$ctx['package_id'];
        $receipt = DB::transaction(function () use ($studentId, $kind, $category, $package) {
            // Lock student: serializes simultaneous wallet purchases, withdrawal reservations and credits
            // only when those writers also lock the same student record.
            $student = DB::table('students')->where('id', $studentId)->lockForUpdate()->first();
            abort_unless($student, 401);
            [$cat, $p, $amount] = $this->selection($kind, $category, $package);
            abort_if($this->available($student) < $amount, 422, 'পর্যাপ্ত Available Referral Balance নেই।');
            $now = Carbon::now('Asia/Dhaka');
            $existing = DB::table('lebd_ebook_entitlements')->where('user_id', $studentId)
                ->where('category_id', $category)->where('status', 'Active')
                ->where('expires_at', '>', $now)->orderByDesc('expires_at')->lockForUpdate()->first();
            $start = $existing ? Carbon::parse($existing->expires_at, 'Asia/Dhaka') : $now->copy();
            $end = $start->copy();
            switch ($p->duration_unit) {
                case 'days': $end->addDays((int)$p->duration_value); break;
                case 'months': $end->addMonthsNoOverflow((int)$p->duration_value); break;
                case 'years': $end->addYearsNoOverflow((int)$p->duration_value); break;
            }
            $trx = 'EBK'.strtoupper(Str::random(20));
            DB::table('lebd_ebook_payment_orders')->insert([
                'student_id' => $studentId, 'category_id' => $category, 'package_id' => $p->id,
                'transaction_id' => $trx, 'package_name' => $p->name,
                'amount' => number_format($amount / 100, 2, '.', ''), 'currency' => 'BDT',
                'duration_value' => $p->duration_value, 'duration_unit' => $p->duration_unit,
                'status' => 'Complete', 'gateway_validation_id' => 'REFERRAL_BALANCE',
                'paid_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('students')->where('id', $studentId)->update([
                'referral_balance' => number_format(((int)round((float)$student->referral_balance*100)-$amount)/100,2,'.',''),
            ]);
            if ($existing) {
                DB::table('lebd_ebook_entitlements')->where('id', $existing->id)->update([
                    'package_id' => $p->id, 'expires_at' => $end, 'updated_at' => now(),
                ]);
            } else {
                DB::table('lebd_ebook_entitlements')->insert([
                    'user_id' => $studentId, 'category_id' => $category, 'package_id' => $p->id,
                    'status' => 'Active', 'starts_at' => $now, 'expires_at' => $end,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            return ['trx' => $trx, 'phone' => (string)($student->contact ?? ''), 'kind' => $kind, 'category' => $category];
        }, 3);
        // Send only after committed successful purchase. An SMS provider failure never rolls back a purchase.
        if (config('lebd_payments.sms_enabled', true) && !app()->environment('local') && $receipt['phone'] !== '') {
            try {
                $message = 'Live Education BD: Your E-Book purchase was successful. Premium book access is now active. Log in to read your book. Thank you.';
                (new SourceCtrl())->sms_send($receipt['phone'], $message);
            } catch (\Throwable $e) {
                Log::warning('E-Book referral purchase SMS failed', ['transaction_id' => $receipt['trx'], 'error' => $e->getMessage()]);
            }
        }
        return redirect()->route('student.ebook.chapters', ['kind' => $receipt['kind'], 'category' => $receipt['category']])
            ->with('ebook_notice', 'Referral Balance দিয়ে E-Book কেনা সফল হয়েছে। Premium Access সক্রিয়। Order: '.$receipt['trx']);
    }
}
