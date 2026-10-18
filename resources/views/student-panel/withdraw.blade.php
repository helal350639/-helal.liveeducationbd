@extends('student')

@section('title', 'Withdraw Request')

@section('content')
@php
    // Use the SAME definition of available balance as the My Referral page:
    // gross referral balance minus outstanding pending withdrawal requests.
    // This is display/form validation only; it does NOT debit the wallet.
    $withdrawStudent = auth('student')->user();
    $walletGrossBalance = max(0, (float) ($student->referral_balance ?? 0));

    $pendingForWithdrawPage = isset($withdrawPending)
        ? max(0, (float) $withdrawPending)
        : (float) \Illuminate\Support\Facades\DB::table('withdraw_requests')
            ->where('student_id', $withdrawStudent->id)
            ->where('status', 'pending')
            ->sum('amount');

    $withdrawPageAvailable = isset($availableBalance)
        ? max(0, (float) $availableBalance)
        : max(0, round($walletGrossBalance - $pendingForWithdrawPage, 2));
@endphp

<div class="lebd-withdraw-page">
    <div class="lebd-withdraw-wrap">
        <div class="lebd-withdraw-heading">
            <a class="lebd-withdraw-back" href="{{ route('students.referral') }}" aria-label="Back to My Referral">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <div class="lebd-withdraw-kicker">LIVE EDUCATION BD · MY REFERRAL</div>
                <h1>Withdraw Request</h1>
                <p>আপনার অর্জিত রেফারেল ব্যালেন্স উত্তোলনের আবেদন করুন।</p>
            </div>
        </div>

        <div class="lebd-withdraw-grid">
            <aside class="lebd-withdraw-side">
                <div class="lebd-withdraw-balance-label"><i class="fa-solid fa-wallet"></i> Available Balance</div>
                <div class="lebd-withdraw-balance">৳ {{ number_format($withdrawPageAvailable, 2) }}</div>
                <span class="lebd-withdraw-balance-note">বর্তমানে উত্তোলনের জন্য উপলব্ধ ব্যালেন্স</span>
                @if($pendingForWithdrawPage > 0)
                    <div class="lebd-withdraw-pending-note"><i class="fa-regular fa-clock"></i> Pending Withdraw: ৳ {{ number_format($pendingForWithdrawPage, 2) }}</div>
                @endif
                <div class="lebd-withdraw-side-divider"></div>
                <div class="lebd-withdraw-info-row"><i class="fa-solid fa-shield-halved"></i><span>নিরাপদ আবেদন প্রক্রিয়া</span></div>
                <div class="lebd-withdraw-info-row"><i class="fa-regular fa-clock"></i><span>আবেদন জমার পর সাধারণত ২৪ ঘণ্টার মধ্যে যাচাইয়ের লক্ষ্য</span></div>
                <div class="lebd-withdraw-info-row"><i class="fa-solid fa-circle-check"></i><span>অনুমোদনের পর Transaction History-তে স্ট্যাটাস দেখতে পারবেন</span></div>
            </aside>

            <section class="lebd-withdraw-form-card" aria-label="Withdraw request form">
                <div class="lebd-withdraw-form-head">
                    <span class="lebd-withdraw-form-icon"><i class="fa-solid fa-money-bill-transfer"></i></span>
                    <div><h2>Request Details</h2><p>সঠিক তথ্য দিয়ে আবেদনটি পূরণ করুন</p></div>
                </div>

                @if(session('success'))
                    <div class="lebd-withdraw-alert lebd-withdraw-alert-success" role="status" id="withdrawSuccessMessage">
                        <i class="fa-solid fa-circle-check"></i>
                        <div><strong>আবেদন সফলভাবে জমা হয়েছে</strong><span>{{ session('success') }}</span><span>আপনার রিকোয়েস্টটি আগামী ২৪ ঘণ্টার মধ্যে যাচাই ও অনুমোদনের জন্য প্রক্রিয়া করা হবে।</span></div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="lebd-withdraw-alert lebd-withdraw-alert-error" role="alert" id="withdrawErrorMessage">
                        <i class="fa-solid fa-circle-exclamation"></i><div><strong>আবেদন জমা দেওয়া যায়নি</strong><span>{{ session('error') }}</span></div>
                    </div>
                @endif
                @if($errors->any())
                    <div class="lebd-withdraw-alert lebd-withdraw-alert-error" role="alert">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div><strong>দয়া করে তথ্যগুলো ঠিক করুন</strong>
                            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('student.withdraw.store') }}" method="POST" id="lebdWithdrawForm">
                    @csrf
                    <div class="lebd-withdraw-field">
                        <label for="lebdWithdrawAmount">উত্তোলনের পরিমাণ <span>*</span></label>
                        <div class="lebd-withdraw-input-shell"><span class="lebd-withdraw-prefix">৳</span>
                            <input id="lebdWithdrawAmount" type="number" inputmode="decimal" name="amount" min="0.01" step="0.01" max="{{ number_format($withdrawPageAvailable, 2, '.', '') }}" value="{{ old('amount') }}" placeholder="যেমন: 500" required>
                        </div>
                        <small>সর্বোচ্চ উপলব্ধ ব্যালেন্সের মধ্যে আবেদন করুন।</small>
                    </div>
                    <div class="lebd-withdraw-field">
                        <label for="lebdWithdrawMethod">পেমেন্ট মাধ্যম <span>*</span></label>
                        <div class="lebd-withdraw-input-shell"><i class="fa-solid fa-credit-card lebd-withdraw-input-icon"></i>
                            <select id="lebdWithdrawMethod" name="method" required>
                                <option value="">পেমেন্ট মাধ্যম নির্বাচন করুন</option>
                                {{-- IMPORTANT: These existing option values are kept unchanged for backend compatibility. --}}
                                <option value="bkash" {{ old('method') === 'bkash' ? 'selected' : '' }}>bKash</option>
                                <option value="nagad" {{ old('method') === 'nagad' ? 'selected' : '' }}>Rocket</option>
                                <option value="bank" {{ old('method') === 'bank' ? 'selected' : '' }}>Nagad</option>
                            </select>
                        </div>
                    </div>
                    <div class="lebd-withdraw-field">
                        <label for="lebdWithdrawAccount">পেমেন্ট অ্যাকাউন্ট নম্বর <span>*</span></label>
                        <div class="lebd-withdraw-input-shell"><i class="fa-solid fa-mobile-screen-button lebd-withdraw-input-icon"></i>
                            <input id="lebdWithdrawAccount" type="text" inputmode="numeric" name="account_number" value="{{ old('account_number') }}" placeholder="আপনার bKash / Rocket / Nagad নম্বর" autocomplete="off" maxlength="30" required>
                        </div>
                        <small>ভুল নম্বর দিলে পেমেন্ট পেতে সমস্যা হতে পারে।</small>
                    </div>
                    <div class="lebd-withdraw-field">
                        <label for="lebdWithdrawAccountConfirm">কনফার্ম পেমেন্ট অ্যাকাউন্ট নম্বর <span>*</span></label>
                        <div class="lebd-withdraw-input-shell"><i class="fa-solid fa-shield-halved lebd-withdraw-input-icon"></i>
                            <input id="lebdWithdrawAccountConfirm" type="text" inputmode="numeric" name="account_number_confirmation" value="{{ old('account_number_confirmation') }}" placeholder="একই অ্যাকাউন্ট নম্বর আবার লিখুন" autocomplete="off" maxlength="30" aria-describedby="lebdWithdrawAccountError" required>
                        </div>
                        <small id="lebdWithdrawAccountError" class="lebd-withdraw-match-error" role="alert" aria-live="polite" hidden>অ্যাকাউন্ট নম্বর দুটি মিলছে না। অনুগ্রহ করে আবার যাচাই করুন।</small>
                        <small>ভুল নম্বরে টাকা পাঠানো এড়াতে নম্বরটি আবার নিশ্চিত করুন।</small>
                    </div>
                    <div class="lebd-withdraw-advice"><i class="fa-regular fa-bell"></i><span>আবেদন সফল হলে সঙ্গে সঙ্গে Confirmation Notice দেখানো হবে। অনুমোদন সম্পন্ন হলে Referral Page-এ স্ট্যাটাস দেখতে পারবেন।</span></div>
                    @if($withdrawPageAvailable <= 0)
                        <div class="lebd-withdraw-alert lebd-withdraw-alert-error" role="status">
                            <i class="fa-solid fa-circle-info"></i><div><span>এই মুহূর্তে উত্তোলনের জন্য কোনো Available Balance নেই।</span></div>
                        </div>
                    @endif
                    <button type="submit" class="lebd-withdraw-submit" id="lebdWithdrawSubmit" {{ $withdrawPageAvailable <= 0 ? 'disabled' : '' }}>
                        <i class="fa-solid fa-paper-plane"></i> Submit Withdraw Request <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection

@section('stylesheets')
<style>
.lebd-withdraw-page, .lebd-withdraw-page *{box-sizing:border-box}
.lebd-withdraw-page{width:100%;min-height:calc(100vh - 120px);padding:28px 18px 55px;background:linear-gradient(135deg,#f0f6ff 0%,#f8fbff 52%,#f5f7fc 100%);font-family:inherit;color:#112a50}
.lebd-withdraw-wrap{width:100%;max-width:1080px;margin:0 auto}
.lebd-withdraw-heading{display:flex;align-items:center;gap:15px;margin-bottom:22px}
.lebd-withdraw-back{width:43px;height:43px;flex:0 0 43px;border-radius:12px;background:#fff;box-shadow:0 4px 18px rgba(17,50,89,.09);display:flex;align-items:center;justify-content:center;color:#1264dc;text-decoration:none}
.lebd-withdraw-back:hover{color:#fff;background:#086bdf}
.lebd-withdraw-kicker{font-weight:800;color:#3973c3;font-size:10px;letter-spacing:1.1px;margin-bottom:5px}
.lebd-withdraw-heading h1{font-size:27px;margin:0 0 4px;font-weight:800;color:#0a2956;line-height:1.25}
.lebd-withdraw-heading p{margin:0;color:#617899;font-size:13px}
.lebd-withdraw-grid{display:grid;grid-template-columns:minmax(0,.85fr) minmax(0,1.35fr);gap:20px;align-items:stretch}
.lebd-withdraw-side{background:linear-gradient(150deg,#063a84 0%,#0a65d6 60%,#079faa 100%);padding:31px 25px;border-radius:20px;color:#fff;min-width:0;box-shadow:0 14px 35px rgba(9,78,162,.13)}
.lebd-withdraw-balance-label{font-size:13px;display:flex;gap:9px;align-items:center;color:#e4f2ff;font-weight:600}
.lebd-withdraw-balance{font-size:37px;line-height:1.35;font-weight:800;margin-top:15px;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}
.lebd-withdraw-balance-note{font-size:12px;color:#d4ecff}
.lebd-withdraw-pending-note{display:flex;align-items:center;gap:7px;margin-top:12px;padding:8px 10px;border-radius:9px;background:rgba(255,255,255,.13);color:#eff9ff;font-size:12px;font-weight:600}
.lebd-withdraw-side-divider{height:1px;background:rgba(255,255,255,.25);margin:31px 0 25px}
.lebd-withdraw-info-row{display:flex;align-items:flex-start;gap:11px;font-size:13px;line-height:1.65;margin:15px 0;color:#e8f3ff}
.lebd-withdraw-info-row i{width:19px;flex:0 0 19px;color:#76f6d4;font-size:15px;margin-top:3px}
.lebd-withdraw-form-card{background:#fff;padding:28px;border:1px solid #e5eef9;box-shadow:0 12px 30px rgba(17,50,89,.06);border-radius:20px;min-width:0}
.lebd-withdraw-form-head{display:flex;align-items:center;gap:12px;padding-bottom:18px;border-bottom:1px solid #eaf0f9;margin-bottom:22px}
.lebd-withdraw-form-icon{height:48px;width:48px;display:flex;align-items:center;justify-content:center;border-radius:13px;background:#ebf3ff;color:#1167e0;font-size:21px;flex:0 0 48px}
.lebd-withdraw-form-head h2{font-size:19px;margin:0 0 4px;font-weight:800;color:#102e5e}
.lebd-withdraw-form-head p{font-size:12px;color:#7487a1;margin:0}
.lebd-withdraw-field{margin-bottom:18px}
.lebd-withdraw-field label{font-size:13px;font-weight:700;color:#253c5d;display:block;margin-bottom:8px}
.lebd-withdraw-field label span{color:#e83b53}
.lebd-withdraw-input-shell{display:flex;align-items:center;border:1px solid #d9e5f6;border-radius:11px;background:#fafcff;overflow:hidden;transition:box-shadow .15s,border-color .15s}
.lebd-withdraw-input-shell:focus-within{border-color:#277df3;box-shadow:0 0 0 3px rgba(39,125,243,.1);background:#fff}
.lebd-withdraw-input-shell input,.lebd-withdraw-input-shell select{width:100%;flex:1;min-width:0;height:46px;padding:0 13px;border:0!important;outline:0!important;background:transparent!important;color:#1b355c;font-size:14px;box-shadow:none!important}
.lebd-withdraw-input-shell select{cursor:pointer}
.lebd-withdraw-prefix{border-right:1px solid #e4ebf6;padding:0 16px;color:#1671dd;font-size:21px;font-weight:700}
.lebd-withdraw-input-icon{margin-left:15px;color:#6f93c5;font-size:15px}
.lebd-withdraw-field small{font-size:11px;color:#7c90aa;display:block;margin-top:6px}
.lebd-withdraw-field .lebd-withdraw-match-error{color:#c6283a;font-weight:700}
.lebd-withdraw-field .lebd-withdraw-match-error[hidden]{display:none!important}
.lebd-withdraw-input-shell.lebd-withdraw-input-mismatch{border-color:#e34b5c;background:#fff9fa}

.lebd-withdraw-advice{background:#f0f7ff;border:1px solid #dbeafb;border-radius:11px;padding:12px 13px;display:flex;gap:10px;color:#40658e;font-size:12px;line-height:1.6;margin:5px 0 19px}
.lebd-withdraw-advice i{color:#1671dd;margin-top:2px}
.lebd-withdraw-submit{background:linear-gradient(90deg,#086fea,#075ac9);display:flex;align-items:center;justify-content:center;gap:10px;padding:14px 16px;width:100%;border:0;border-radius:11px;color:#fff;font-size:14px;font-weight:800;cursor:pointer;box-shadow:0 7px 18px rgba(5,101,214,.18)}
.lebd-withdraw-submit:hover{background:#044fba;color:#fff}
.lebd-withdraw-submit:disabled{opacity:.65;cursor:not-allowed}
.lebd-withdraw-alert{display:flex;align-items:flex-start;gap:10px;padding:12px;border-radius:10px;margin-bottom:16px;font-size:12px;line-height:1.55}
.lebd-withdraw-alert i{margin-top:2px;font-size:17px}
.lebd-withdraw-alert strong,.lebd-withdraw-alert span{display:block}
.lebd-withdraw-alert ul{margin:6px 0 0 16px;padding:0}
.lebd-withdraw-alert-success{background:#e8f9f0;border:1px solid #b0eace;color:#08633f}
.lebd-withdraw-alert-error{background:#fff0f0;border:1px solid #f6c7ca;color:#a42f3c}
@media(max-width:720px){
 .lebd-withdraw-page{padding:15px 11px 35px;min-height:calc(100vh - 95px)}
 .lebd-withdraw-heading{margin-bottom:15px;gap:10px}
 .lebd-withdraw-heading h1{font-size:21px}
 .lebd-withdraw-heading p{font-size:11px}
 .lebd-withdraw-back{width:37px;height:37px;flex-basis:37px}
 .lebd-withdraw-grid{grid-template-columns:minmax(0,1fr);gap:13px}
 .lebd-withdraw-side{padding:20px 20px;border-radius:16px}
 .lebd-withdraw-balance{font-size:29px;margin-top:7px}
 .lebd-withdraw-side-divider{margin:16px 0 10px}
 .lebd-withdraw-info-row{font-size:11px;margin:9px 0}
 .lebd-withdraw-form-card{padding:19px 15px;border-radius:16px}
 .lebd-withdraw-form-head{margin-bottom:18px}
 .lebd-withdraw-form-head h2{font-size:17px}
 .lebd-withdraw-input-shell input,.lebd-withdraw-input-shell select{font-size:16px}
}
@media(min-width:721px) and (max-width:880px){.lebd-withdraw-grid{grid-template-columns:1fr 1.2fr}.lebd-withdraw-form-card{padding:20px}}
@media(prefers-reduced-motion:reduce){.lebd-withdraw-page *{transition:none!important}}
</style>
@endsection

@section('scripts')
@if(session('success') || session('error'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swal === 'undefined') return;
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Withdraw Request Submitted!',
            html: '<div style="line-height:1.7">আপনার Withdrawal Request সফলভাবে জমা হয়েছে।<br><strong>আপনার আবেদনটি ২৪ ঘণ্টার মধ্যে যাচাই ও অনুমোদনের জন্য প্রক্রিয়া করা হবে।</strong></div>',
            confirmButtonText: 'ঠিক আছে',
            confirmButtonColor: '#086fea'
        });
    @elseif(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Request Not Submitted',
            text: document.querySelector('#withdrawErrorMessage span')?.textContent?.trim() || 'দয়া করে আবার চেষ্টা করুন।',
            confirmButtonText: 'ঠিক আছে'
        });
    @endif
});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('lebdWithdrawForm');
  if (!form) return;
  var btn = document.getElementById('lebdWithdrawSubmit');
  var account = document.getElementById('lebdWithdrawAccount');
  var confirmAccount = document.getElementById('lebdWithdrawAccountConfirm');
  var error = document.getElementById('lebdWithdrawAccountError');

  function validateAccountMatch(showError) {
    var mismatch = account.value.trim() !== confirmAccount.value.trim();
    var shouldShow = mismatch && (showError || confirmAccount.value.length > 0);
    confirmAccount.setCustomValidity(mismatch ? 'অ্যাকাউন্ট নম্বর দুটি মিলছে না।' : '');
    confirmAccount.parentElement.classList.toggle('lebd-withdraw-input-mismatch', shouldShow);
    error.hidden = !shouldShow;
    confirmAccount.setAttribute('aria-invalid', shouldShow ? 'true' : 'false');
    return !mismatch;
  }

  account.addEventListener('input', function () { validateAccountMatch(false); });
  confirmAccount.addEventListener('input', function () { validateAccountMatch(false); });

  form.addEventListener('submit', function (event) {
    if (!validateAccountMatch(true)) {
      event.preventDefault();
      confirmAccount.focus();
      confirmAccount.reportValidity();
      return;
    }
    if (!form.checkValidity()) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> আবেদন জমা হচ্ছে...';
  });
});
</script>
@endsection
