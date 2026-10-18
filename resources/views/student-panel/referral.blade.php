@extends('student')

@section('title', 'My Referral')

@php
    $refStudent = Auth::guard('student')->user();
    $refCode = trim((string) ($refStudent->referral_id ?? ''));
    $mainReferralUrl = $refCode !== '' ? url('/register') . '?' . http_build_query(['ref' => $refCode]) : '';

    /*
     | Prefer the controller's paid/purchased collection whenever it is supplied.
     | Fallback: this student's existing batch memberships (never a global batch list).
     | If paid purchases differ from enrolled batches in your project, pass the exact
     | $purchasedCourses collection from the controller to this view.
     */
    $referralPurchasedBatches = isset($purchasedCourses)
        ? collect($purchasedCourses)
        : ($refStudent ? $refStudent->batches()->get() : collect());

    // Show an approval notice ONLY for a genuinely approved withdrawal of this student.
    // Never infer approval from available balance or referrals.
    $latestApprovedWithdrawal = $refStudent
        ? \Illuminate\Support\Facades\DB::table('withdraw_requests')
            ->where('student_id', $refStudent->id)
            ->where('status', 'approved')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first(['id', 'amount', 'method', 'updated_at'])
        : null;

    $withdrawalApprovalKey = $latestApprovedWithdrawal
        ? 'lebd_withdraw_approved_' . (int) $refStudent->id . '_' . (int) $latestApprovedWithdrawal->id . '_' . (string) ($latestApprovedWithdrawal->updated_at ?? '')
        : null;
@endphp

@section('stylesheets')
<style>
/* Scoped styles: do not affect the main homepage header or other student pages. */
.lebd-referral, .lebd-referral * { box-sizing: border-box; }
.lebd-referral {
    --lr-blue: #1169e7; --lr-navy:#102d59; --lr-muted:#667c99;
    width:100%; min-width:0; min-height:65vh;
    padding:20px 14px 42px;
    background:linear-gradient(180deg,#f0f6ff,#f8fafd 320px);
    color:var(--lr-navy); font-family:inherit;
}
.lebd-referral .lr-shell { max-width:1160px; width:100%; margin:0 auto; }
.lebd-referral .lr-head {
    display:flex; align-items:center; gap:12px;
    margin-bottom:16px;
}
.lebd-referral .lr-back {
    height:36px; width:36px; flex:0 0 36px;
    background:#fff; border:1px solid #deebfa; border-radius:11px;
    color:#1555aa; display:grid; place-items:center;
    text-decoration:none; box-shadow:0 3px 9px rgba(14,65,123,.05);
}
.lebd-referral .lr-head h1 { margin:0; font-size:23px; line-height:1.3; font-weight:800; }
.lebd-referral .lr-head p { margin:2px 0 0; font-size:12px; color:var(--lr-muted); }
.lebd-referral .lr-panel {
    background:#fff; border:1px solid #deebfb;
    border-radius:18px; padding:17px 18px;
    box-shadow:0 5px 17px rgba(14,59,128,.055);
    margin-bottom:15px; min-width:0;
}
.lebd-referral .lr-panel-head {
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; margin-bottom:15px;
}
.lebd-referral .lr-heading { display:flex; align-items:center; min-width:0; gap:10px; }
.lebd-referral .lr-heading-icon {
    height:34px; width:34px; flex:0 0 34px; border-radius:10px;
    color:#fff; background:linear-gradient(135deg,#1684f8,#0859c7);
    display:grid; place-items:center;
}
.lebd-referral .lr-panel-head h2 { margin:0; font-size:17px; font-weight:800; }
.lebd-referral .lr-panel-head p { margin:2px 0 0; color:var(--lr-muted); font-size:11px; }
.lebd-referral .lr-head-links { display:flex; align-items:center; gap:7px; flex-shrink:0; }
.lebd-referral .lr-more {
    display:inline-flex; align-items:center; gap:5px; text-decoration:none;
    font-size:12px; font-weight:750; color:#075ecf;
    background:#eaf3ff; border:1px solid #cfe3ff;
    border-radius:9px; padding:8px 11px; white-space:nowrap;
}
.lebd-referral .lr-nav {
    width:32px; height:32px; border:1px solid #d7e6f8;
    background:#fff; color:#145bbb; border-radius:9px; cursor:pointer;
    display:grid; place-items:center;
}
.lebd-referral .lr-scroll {
    display:flex; gap:13px; overflow-x:auto; scroll-snap-type:x mandatory;
    overscroll-behavior-inline:contain; scroll-behavior:smooth;
    scrollbar-width:thin; scrollbar-color:#cbddf5 transparent;
    padding:2px 2px 12px; min-width:0;
}
.lebd-referral .lr-course {
    flex:0 0 calc((100% - 39px)/4); min-width:0; scroll-snap-align:start;
    border:1px solid #dfeafa; border-radius:15px; background:#fff;
    box-shadow:0 3px 10px rgba(19,69,130,.07); overflow:hidden;
    display:flex; flex-direction:column;
}
.lebd-referral .lr-cover {
    aspect-ratio:16/9; display:grid; place-items:center; overflow:hidden;
    background:linear-gradient(135deg,#e6f3ff,#cddfff); position:relative;
}
.lebd-referral .lr-cover i { font-size:36px; color:#6ba9e8; opacity:.6; }
.lebd-referral .lr-cover img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.lebd-referral .lr-course-body { padding:12px; display:flex; flex:1; flex-direction:column; min-width:0; }
.lebd-referral .lr-course h3 {
    font-size:13px; font-weight:800; margin:0 0 5px; line-height:1.4;
    min-height:36px; overflow-wrap:anywhere;
}
.lebd-referral .lr-course p { margin:0 0 9px; font-size:11px; color:var(--lr-muted); }
.lebd-referral .lr-bonus {
    margin-top:auto; background:#e9faf0; color:#107a48;
    border-radius:9px; padding:8px 9px; font-size:11px;
    display:flex; align-items:center; justify-content:space-between; gap:5px;
}
.lebd-referral .lr-bonus strong { font-size:13px; white-space:nowrap; }
.lebd-referral .lr-action {
    display:flex; justify-content:center; align-items:center; gap:7px;
    width:100%; background:#1169e7; color:white; border:0;
    border-radius:9px; padding:10px 8px; font-size:11px; font-weight:700;
    cursor:pointer; margin-top:9px;
}
.lebd-referral .lr-action:disabled { opacity:.6; cursor:not-allowed; }
.lebd-referral .lr-action-row {display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:9px;}
.lebd-referral .lr-action-row .lr-action {min-width:0;margin-top:0;white-space:nowrap;}
.lebd-referral .lr-action-share {background:#079b70;}
.lebd-referral .lr-approval-overlay[hidden] {display:none !important;}
.lebd-referral .lr-approval-overlay {position:fixed;inset:0;display:grid;place-items:center;background:rgba(8,28,61,.52);z-index:100001;padding:16px;}
.lebd-referral .lr-approval-box {width:min(100%,430px);background:white;border-radius:20px;border:1px solid #d6f1df;padding:25px 23px;text-align:center;box-shadow:0 24px 75px rgba(7,35,75,.23);}
.lebd-referral .lr-approval-icon {width:60px;height:60px;border-radius:50%;margin:0 auto 12px;display:grid;place-items:center;background:#dffaea;color:#06945c;font-size:27px;}
.lebd-referral .lr-approval-box h2 {font-size:22px;color:#0b6348;margin:0 0 8px;font-weight:800;}
.lebd-referral .lr-approval-box p {font-size:13px;color:#546a82;margin:0 0 12px;line-height:1.6;}
.lebd-referral .lr-approved-amount {font-size:26px;color:#037a54;font-weight:800;padding:9px 12px;background:#edfcf4;border-radius:12px;margin-bottom:14px;}
.lebd-referral .lr-approval-buttons {display:flex;justify-content:center;flex-wrap:wrap;gap:8px;}
.lebd-referral .lr-approval-buttons button,.lebd-referral .lr-approval-buttons a {border:0;border-radius:10px;padding:10px 17px;background:#0d72e9;color:#fff;font-size:13px;font-weight:750;text-decoration:none;cursor:pointer;}
.lebd-referral .lr-approval-buttons a {background:#ebf3ff;color:#125bae;}

.lebd-referral .lr-empty { background:#f5f9ff; border:1px dashed #bdd5f3; border-radius:12px; padding:26px 12px; font-size:13px; text-align:center; color:#536986; }
.lebd-referral .lr-details-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
.lebd-referral .lr-details-grid > * { min-width:0; margin:0; }
.lebd-referral .lr-details-grid .lr-wide { grid-column:1/-1; }
.lebd-referral .lr-small-title { font-size:13px; font-weight:800; margin:0 0 12px; display:flex; gap:8px; align-items:center; }
.lebd-referral .lr-id-row { display:flex; align-items:center; gap:10px; min-width:0; }
.lebd-referral .lr-id-icon {
    width:38px;height:38px;flex:0 0 38px; background:#ecf2ff; color:#246be1;
    border-radius:11px; display:grid; place-items:center;
}
.lebd-referral .lr-id-info { min-width:0;flex:1; }
.lebd-referral .lr-id-info small { display:block; color:var(--lr-muted); font-size:11px; }
.lebd-referral .lr-id-info strong { display:block; font-size:18px; overflow-wrap:anywhere; }
.lebd-referral .lr-icon-btn {
    flex:0 0 36px; height:36px; width:36px; border:1px solid #d4e4ff;
    color:#1267e2; background:#eef5ff; border-radius:9px; cursor:pointer;
}
.lebd-referral .lr-link-row { display:flex; align-items:stretch; gap:7px; }
.lebd-referral .lr-link-row input {
    flex:1;min-width:0; height:38px; border:1px solid #dce8f8;
    border-radius:9px; padding:8px 10px; font-size:12px; color:#38516f;
    background:#f9fcff;
}
.lebd-referral .lr-mini-btn {
    background:#126de7; color:#fff; border:0; border-radius:9px;
    padding:0 12px; font-size:11px; font-weight:750; cursor:pointer;
}
.lebd-referral .lr-mini-btn:disabled {opacity:.55;cursor:not-allowed;}
.lebd-referral .lr-referrer {font-size:13px;color:#50647d;line-height:1.6;}
.lebd-referral .lr-referrer strong { color:#143462; }
.lebd-referral .lr-earning-grid {display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;}
.lebd-referral .lr-earning-cell {padding:11px;border-radius:11px;background:#f3f7fd;border:1px solid #e7eef9;}
.lebd-referral .lr-earning-cell small {display:block;font-size:11px;color:var(--lr-muted);line-height:1.4;}
.lebd-referral .lr-earning-cell strong {display:block;font-size:17px;color:#0b4da4;margin-top:4px;}
.lebd-referral .lr-balance-grid {display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px;margin-bottom:15px;}
.lebd-referral .lr-balance {border:1px solid #dce9f8;border-radius:15px;background:#fff;padding:15px;text-align:center;box-shadow:0 4px 15px rgba(21,65,130,.05);}
.lebd-referral .lr-balance small {display:block;font-size:12px;color:#677e9c;}
.lebd-referral .lr-balance strong {display:block;font-size:23px;font-weight:800;margin:6px 0;color:#0f4083;}
.lebd-referral .lr-balance a {font-size:12px;color:#fff;background:#059967;border-radius:9px;display:inline-block;padding:8px 14px;text-decoration:none;font-weight:700;}
.lebd-referral .lr-history {display:flex;align-items:center;justify-content:center;position:relative;gap:8px;text-align:center;text-decoration:none;color:#095fd2;font-weight:750;font-size:13px;padding:6px 35px;}
.lebd-referral .lr-history > .fa-chevron-right {position:absolute;right:8px;top:50%;transform:translateY(-50%);}
.lebd-referral .lr-history-panel {max-width:100%;}
.lebd-referral .lr-toast {position:fixed;bottom:24px;left:50%;transform:translate(-50%,18px);border-radius:11px;background:#083d70;color:white;padding:11px 18px;z-index:99999;opacity:0;transition:.2s;pointer-events:none;font-size:12px;max-width:90vw;text-align:center;}
.lebd-referral .lr-toast.is-visible {opacity:1;transform:translate(-50%,0);}
@media (max-width:1100px) {
    .lebd-referral .lr-course {flex-basis:calc((100% - 26px)/3);}
}
@media (max-width:790px) {
    .lebd-referral {padding:12px 10px 30px;}
    .lebd-referral .lr-shell {max-width:100%;}
    .lebd-referral .lr-course {flex-basis:calc((100% - 13px)/2);}
    .lebd-referral .lr-panel {padding:13px;border-radius:14px;margin-bottom:11px;}
    .lebd-referral .lr-head h1 {font-size:19px;}
    .lebd-referral .lr-balance strong {font-size:18px;}
    .lebd-referral .lr-earning-grid {gap:6px;}
    .lebd-referral .lr-earning-cell {padding:8px;}
    .lebd-referral .lr-earning-cell strong {font-size:14px;}
}
@media (max-width:540px) {
    .lebd-referral {padding:10px 9px 22px;}
    .lebd-referral .lr-head h1 {font-size:18px;}
    .lebd-referral .lr-panel-head h2 {font-size:14px;}
    .lebd-referral .lr-panel-head p {font-size:10px;}
    .lebd-referral .lr-course {flex-basis:84%;}
    .lebd-referral .lr-course-body {padding:10px;}
    .lebd-referral .lr-nav {display:none;}
    .lebd-referral .lr-scroll {gap:9px;padding-bottom:10px;}
    .lebd-referral .lr-details-grid {grid-template-columns:1fr;gap:10px;}
    .lebd-referral .lr-details-grid .lr-wide {grid-column:auto;}
    .lebd-referral .lr-earning-grid {grid-template-columns:1fr;}
    .lebd-referral .lr-earning-cell {display:flex;align-items:center;justify-content:space-between;gap:10px;}
    .lebd-referral .lr-earning-cell strong {margin:0;white-space:nowrap;}
    .lebd-referral .lr-balance-grid {gap:7px;}
    .lebd-referral .lr-balance {padding:10px 6px;}
    .lebd-referral .lr-balance small {font-size:10px;line-height:1.35;}
    .lebd-referral .lr-balance strong {font-size:16px;}
    .lebd-referral .lr-balance a {font-size:10px;padding:6px 8px;}
    .lebd-referral .lr-link-row {flex-wrap:wrap;}
    .lebd-referral .lr-link-row input {flex-basis:100%;}
    .lebd-referral .lr-mini-btn {height:34px;flex:1;}
}
@media (prefers-reduced-motion: reduce) {
    .lebd-referral .lr-scroll {scroll-behavior:auto;}
    .lebd-referral .lr-toast {transition:none;}
}
</style>
@endsection

@section('content')
<div class="lebd-referral">
  <div class="lr-shell">
    <div class="lr-head">
      <a href="{{ route('students.my-course') }}" class="lr-back" title="My Courses" aria-label="Go to My Courses"><i class="fa-solid fa-arrow-left"></i></a>
      <div>
        <h1>My Referral</h1>
        <p>আপনার কেনা ব্যাচের Referral এবং আয়ের তথ্য</p>
      </div>
    </div>

    <section class="lr-panel" aria-labelledby="lr-purchased-heading">
      <div class="lr-panel-head">
        <div class="lr-heading">
          <span class="lr-heading-icon"><i class="fa-solid fa-graduation-cap"></i></span>
          <div>
            <h2 id="lr-purchased-heading">My Purchased Courses</h2>
            <p>আপনার যুক্ত থাকা ব্যাচ থেকে Referral Link শেয়ার করুন · {{ $referralPurchasedBatches->count() }} টি</p>
          </div>
        </div>
        <div class="lr-head-links">
          @if($referralPurchasedBatches->count() > 1)
            <button type="button" class="lr-nav" data-lr-prev aria-label="Previous course"><i class="fa-solid fa-chevron-left"></i></button>
            <button type="button" class="lr-nav" data-lr-next aria-label="Next course"><i class="fa-solid fa-chevron-right"></i></button>
          @endif
          <a class="lr-more" href="{{ route('students.my-course') }}">See More <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </div>
      @if($referralPurchasedBatches->isNotEmpty())
        <div class="lr-scroll" id="lrPurchasedSlider" role="region" aria-label="Purchased course slider" tabindex="0">
          @foreach($referralPurchasedBatches as $batch)
            @php
              $batchShareUrl = $refCode !== ''
                ? url('/register') . '?' . http_build_query(['ref' => $refCode, 'batch' => $batch->id])
                : '';
              $batchBanner = trim((string) ($batch->banner ?? ''));
            @endphp
            <article class="lr-course">
              <div class="lr-cover">
                <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                @if($batchBanner !== '')
                  <img src="{{ $batchBanner }}" alt="{{ $batch->name }}" loading="lazy" onerror="this.style.display='none'">
                @endif
              </div>
              <div class="lr-course-body">
                <h3>{{ $batch->name }}</h3>
                <p>{{ $batch->subtitle ?? 'Enrolled Batch' }}</p>
                <div class="lr-bonus">
                  <span>Referral Bonus</span>
                  <strong>৳ {{ number_format((float) ($batch->referrer_bonus ?? 0), 2) }}</strong>
                </div>
                <div class="lr-action-row">
                  <button type="button" class="lr-action" data-lr-copy="{{ $batchShareUrl }}" {{ $batchShareUrl === '' ? 'disabled' : '' }}>
                    <i class="fa-solid fa-copy"></i> Copy Link
                  </button>
                  <button type="button" class="lr-action lr-action-share" data-lr-share="{{ $batchShareUrl }}" {{ $batchShareUrl === '' ? 'disabled' : '' }}>
                    <i class="fa-solid fa-share-nodes"></i> Share
                  </button>
                </div>
              </div>
            </article>
          @endforeach
        </div>
      @else
        <div class="lr-empty"><i class="fa-solid fa-box-open"></i> এখানে এখনো কোনো কেনা ব্যাচ পাওয়া যায়নি। <a href="{{ route('students.my-course') }}">My Courses দেখুন</a></div>
      @endif
    </section>

    <div class="lr-details-grid">
      <section class="lr-panel">
        <h2 class="lr-small-title"><i class="fa-solid fa-id-badge"></i> My Referral ID</h2>
        <div class="lr-id-row">
          <div class="lr-id-icon"><i class="fa-solid fa-user-plus"></i></div>
          <div class="lr-id-info">
            <small>আপনার Referral ID</small>
            <strong>{{ $refCode !== '' ? $refCode : 'N/A' }}</strong>
          </div>
          <button class="lr-icon-btn" type="button" data-lr-copy="{{ $refCode }}" {{ $refCode === '' ? 'disabled' : '' }} aria-label="Copy Referral ID"><i class="fa-solid fa-copy"></i></button>
        </div>
      </section>
      <section class="lr-panel">
        <h2 class="lr-small-title"><i class="fa-solid fa-users"></i> Referred By</h2>
        <div class="lr-referrer"><strong>{{ optional($refStudent->referrer)->name ?? 'N/A' }}</strong></div>
      </section>
      <section class="lr-panel lr-wide">
        <h2 class="lr-small-title"><i class="fa-solid fa-link"></i> Referral Link</h2>
        <div class="lr-link-row">
          <input id="lrReferralLink" readonly aria-label="Referral Link" value="{{ $mainReferralUrl }}" placeholder="Referral ID এখনো সেট করা হয়নি">
          <button type="button" class="lr-mini-btn" data-lr-copy="{{ $mainReferralUrl }}" {{ $mainReferralUrl === '' ? 'disabled' : '' }}>Copy</button>
          <button type="button" class="lr-mini-btn" data-lr-share="{{ $mainReferralUrl }}" {{ $mainReferralUrl === '' ? 'disabled' : '' }}>Share</button>
        </div>
      </section>
      <section class="lr-panel lr-wide">
        <h2 class="lr-small-title"><i class="fa-solid fa-chart-pie"></i> Earning Summary</h2>
        <div class="lr-earning-grid">
          <div class="lr-earning-cell"><small>Total Registration Earn</small><strong>৳ {{ number_format((float) ($registrationEarn ?? 0), 2) }}</strong></div>
          <div class="lr-earning-cell"><small>Website Link Share Earn</small><strong>{{ $websiteEarn ?? 0 }} Point</strong></div>
          <div class="lr-earning-cell"><small>MCQ &amp; Others Earn Point</small><strong>{{ $mcqEarn ?? 0 }} Point</strong></div>
        </div>
      </section>
    </div>

    <div class="lr-balance-grid">
      <section class="lr-balance"><small>Lifetime Earn</small><strong>৳ {{ number_format((float) ($lifetimeEarn ?? 0), 2) }}</strong></section>
      <section class="lr-balance"><small>Available Balance</small><strong>৳ {{ number_format((float) ($availableBalance ?? 0), 2) }}</strong><a href="{{ route('student.withdraw') }}">Withdraw</a></section>
      <section class="lr-balance"><small>Pending Withdraw</small><strong>৳ {{ number_format((float) ($withdrawPending ?? 0), 2) }}</strong></section>
    </div>
    <section class="lr-panel lr-history-panel">
      <a class="lr-history" href="{{ route('student.referral.history') }}"><span><i class="fa-solid fa-clock-rotate-left"></i> Transaction History</span><i class="fa-solid fa-chevron-right"></i></a>
    </section>
  </div>
  {{-- New approval -> show once in this browser after visiting Referral Page. --}}
  @if($latestApprovedWithdrawal)
    <div class="lr-approval-overlay" id="lrApprovalNotice" data-approval-key="{{ $withdrawalApprovalKey }}" hidden>
      <div class="lr-approval-box" role="dialog" aria-modal="true" aria-labelledby="lrApprovalTitle" aria-describedby="lrApprovalDesc">
        <div class="lr-approval-icon"><i class="fa-solid fa-circle-check"></i></div>
        <h2 id="lrApprovalTitle">Your Transaction Approved!</h2>
        <p id="lrApprovalDesc">আপনার Withdrawal Request অনুমোদিত হয়েছে।</p>
        <div class="lr-approved-amount">৳ {{ number_format((float) $latestApprovedWithdrawal->amount, 2) }}</div>
        <div class="lr-approval-buttons">
          <button type="button" data-lr-approval-close>ঠিক আছে</button>
          <a href="{{ route('student.referral.history') }}">Transaction History</a>
        </div>
      </div>
    </div>
  @endif
  <div class="lr-toast" id="lrCopyNotice" role="status" aria-live="polite"></div>
</div>
@endsection

@section('scripts')
<script>
(function () {
  'use strict';
  const slider = document.getElementById('lrPurchasedSlider');
  const section = document.querySelector('.lebd-referral');
  if (!section) return;
  let noticeTimer;
  const toast = (message) => {
    const el = document.getElementById('lrCopyNotice');
    if (!el) return;
    el.textContent = message;
    el.classList.add('is-visible');
    clearTimeout(noticeTimer);
    noticeTimer = setTimeout(() => el.classList.remove('is-visible'), 2500);
  };
  async function copy(text) {
    if (!text) return toast('Referral ID এখনো সেট করা হয়নি');
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
      } else {
        const field = document.createElement('textarea');
        field.value = text; field.style.position = 'fixed'; field.style.opacity = '0';
        document.body.appendChild(field); field.select();
        if (!document.execCommand('copy')) throw new Error('Copy failed');
        field.remove();
      }
      toast('✓ Copied Successfully');
    } catch (_) { toast('Copy করা যায়নি, আবার চেষ্টা করুন'); }
  }
  section.addEventListener('click', (event) => {
    const copyButton = event.target.closest('[data-lr-copy]');
    if (copyButton) return void copy(copyButton.dataset.lrCopy);
    const shareButton = event.target.closest('[data-lr-share]');
    if (shareButton) {
      const url = shareButton.dataset.lrShare;
      if (!url) return;
      if (navigator.share) {
        navigator.share({title:'Live Education BD Referral',url})
          .then(() => toast('✓ Shared Successfully'))
          .catch((err) => {
            if (err && err.name === 'AbortError') return; // user cancelled
            copy(url); // share unavailable/failed: copy usable link instead
          });
      } else {
        copy(url); // desktop browsers without Web Share support
      }
    }
  });
  // A newer approved withdrawal receives a new key, so it triggers a fresh notice.
  // Only dismissing acknowledges it. No extra API route or DB writes required.
  const approvedNotice = document.getElementById('lrApprovalNotice');
  if (approvedNotice) {
    const storageKey = approvedNotice.dataset.approvalKey || '';
    let acknowledged = false;
    try { acknowledged = !!storageKey && localStorage.getItem(storageKey) === '1'; } catch (_) {}
    if (!acknowledged) {
      approvedNotice.hidden = false;
      const close = approvedNotice.querySelector('[data-lr-approval-close]');
      if (close) close.focus();
    }
    const dismiss = () => {
      try { if (storageKey) localStorage.setItem(storageKey, '1'); } catch (_) {}
      approvedNotice.hidden = true;
    };
    approvedNotice.querySelector('[data-lr-approval-close]')?.addEventListener('click', dismiss);
    approvedNotice.addEventListener('click', (event) => {
      if (event.target === approvedNotice) dismiss();
    });
    approvedNotice.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') dismiss();
    });
  }
  const prev = section.querySelector('[data-lr-prev]');
  const next = section.querySelector('[data-lr-next]');
  const advance = (direction) => {
    if (!slider) return;
    const card = slider.querySelector('.lr-course');
    if (!card) return;
    const distance = card.getBoundingClientRect().width + 13;
    slider.scrollBy({left:direction * distance,behavior:'smooth'});
  };
  if (prev) prev.addEventListener('click', () => advance(-1));
  if (next) next.addEventListener('click', () => advance(1));
})();
</script>
@endsection
