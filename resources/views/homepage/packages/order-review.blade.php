@extends('homepage.packages.layout')

@section('page_title', 'অর্ডার যাচাই — '.$package->name)

@section('body')

@php

    $original = (float) ($package->main_price ?? $package->price);

    $final = (float) $package->price;

    $discount = max(0, $original - $final);

    $saving = $original > 0 ? 100 * $discount / $original : 0;

    $durationUnit = ['days'=>'দিন','months'=>'মাস','years'=>'বছর'][$package->duration_unit] ?? $package->duration_unit;

    $dept = (array)($categories['department'] ?? []);

    $nondept = (array)($categories['non-department'] ?? []);

    // Payment preview: wallet is charged only by the authenticated POST controller.

    $mcqPreview = request()->query('mcq_preview') === '1';

    $mcqMethod = request()->query('method') === 'referral' ? 'referral' : 'sslcommerz';

    $mcqStudent = \Illuminate\Support\Facades\Auth::guard('student')->user();

    $mcqAvailable = 0.0;

    if ($mcqStudent) {

        $mcqPending = (float) \Illuminate\Support\Facades\DB::table('withdraw_requests')

            ->where('student_id', $mcqStudent->id)->where('status', 'pending')->sum('amount');

        $mcqAvailable = max(0, round((float)($mcqStudent->referral_balance ?? 0) - $mcqPending, 2));

    }

    $mcqWalletToken = null;

    if ($mcqPreview && $mcqMethod === 'referral' && $mcqStudent) {

        $mcqWalletToken = session('mcq_wallet_nonce_'.$package->id);

        if (!is_string($mcqWalletToken) || strlen($mcqWalletToken) !== 40) {

            $mcqWalletToken = \Illuminate\Support\Str::random(40);

            session(['mcq_wallet_nonce_'.$package->id => $mcqWalletToken]);

        }

    }

@endphp

<style>

/* STEP-74: Scoped design only. Existing package and summary layout remains untouched. */

.lebd-order-page .lebd-mcq-payment-details{margin-top:18px;border:1px solid #d6e4f7;border-radius:13px;overflow:hidden;background:#fff}

.lebd-order-page .lebd-mcq-payment-details header{background:#eef5ff;padding:11px 14px;font-size:15px;font-weight:800;color:#103766}

.lebd-order-page .lebd-mcq-payment-detail-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 14px;border-top:1px solid #e5eef7;font-size:14px}

.lebd-order-page .lebd-mcq-payment-detail-row:first-of-type{border-top:0}

.lebd-order-page .lebd-mcq-payment-detail-row strong{white-space:nowrap}

.lebd-order-page .lebd-mcq-payment-detail-row.is-discount{color:#058755}

.lebd-order-page .lebd-mcq-payment-detail-row.is-total{background:#eef6ff;font-weight:800;color:#0a3263}

.lebd-order-page .lebd-mcq-methods{margin-top:16px;padding:15px;border:1px solid #e1eaf8;border-radius:14px;background:#fff;box-shadow:0 3px 15px rgba(23,54,92,.06)}

.lebd-order-page .lebd-mcq-methods h3{text-align:center;margin:0 0 13px;font-size:17px;color:#102b57}

.lebd-order-page .lebd-mcq-method-option{display:flex;gap:12px;align-items:center;border:1.5px solid #a7d3fd;border-radius:11px;background:#ecf8ff;padding:15px 12px;cursor:pointer;margin-bottom:9px;min-width:0}

.lebd-order-page .lebd-mcq-method-option.is-selected{border-color:#1637b6;box-shadow:inset 0 0 0 1px #1637b6;background:#f0faff}

.lebd-order-page .lebd-mcq-method-option input{flex:0 0 auto;width:17px;height:17px;accent-color:#0876ee;margin:0}

.lebd-order-page .lebd-mcq-method-main{flex:1;min-width:0;font-size:13px;color:#052b64}

.lebd-order-page .lebd-mcq-method-main strong{font-size:14px}

.lebd-order-page .lebd-mcq-method-main small{display:block;font-size:10px;color:#5f7ca1;margin-top:4px}

.lebd-order-page .lebd-mcq-balance{background:#fff;border-radius:10px;padding:10px 13px;text-align:right;font-size:12px;font-weight:800;color:#05875a;white-space:nowrap}

.lebd-order-page .lebd-mcq-balance small{display:block;color:#6c88a2;font-size:10px}

.lebd-order-page .lebd-mcq-method-option.gateway{align-items:flex-start;justify-content:center}

.lebd-order-page .lebd-mcq-gateway-methods{display:flex;gap:5px;flex-wrap:wrap;justify-content:center;margin-top:10px}

.lebd-order-page .lebd-mcq-gateway-methods span{font-size:10px;color:#36547a;border:1px solid #dae5f4;background:#fff;padding:6px 8px;border-radius:5px}

.lebd-order-page .lebd-mcq-warning{margin-top:14px;padding:14px 15px;background:#1b126a;border-left:5px solid #40a4ff;color:#fff;border-radius:11px;font-size:12px;line-height:1.65}

.lebd-order-page .lebd-mcq-warning strong{display:block;margin-bottom:3px}

.lebd-order-page .lebd-mcq-terms{display:flex;align-items:center;gap:9px;border:1px solid #dce6f3;background:#fff;padding:12px;border-radius:9px;margin-top:13px;font-size:11px;line-height:1.5;cursor:pointer}

.lebd-order-page .lebd-mcq-terms input{width:15px;height:15px;flex-shrink:0}

.lebd-order-page .lebd-mcq-preview-button{display:block;width:100%;border:0;border-radius:10px;background:#13875a;color:white;padding:13px 16px;font-size:15px;font-weight:800;margin-top:12px;cursor:pointer}

.lebd-order-page .lebd-mcq-preview-button:disabled{opacity:.55;cursor:not-allowed}

.lebd-order-page .lebd-mcq-local-preview{margin-top:12px;padding:15px;background:#edf8f1;border:1px solid #b5e8cd;border-radius:11px;font-size:13px;line-height:1.7}

.lebd-order-page .lebd-mcq-local-preview[hidden]{display:none!important}

.lebd-order-page .lebd-mcq-local-preview strong{color:#075b35}

.lebd-order-page .lebd-mcq-design-note{font-size:11px;color:#687f9e;margin:9px 0 0}

@media(max-width:600px){.lebd-order-page .lebd-mcq-methods{padding:11px}.lebd-order-page .lebd-mcq-balance{padding:8px;font-size:11px}.lebd-order-page .lebd-mcq-method-main strong{font-size:12px}.lebd-order-page .lebd-mcq-method-option{padding:12px 9px;gap:8px}.lebd-order-page .lebd-mcq-payment-detail-row{font-size:12px}}

</style>

@if($mcqPreview)

<style>

.lebd-mcq-nextpage{max-width:880px;margin:30px auto;padding:18px;color:#0b2e60}

.lebd-mcq-nextpage a{text-decoration:none;color:#0867c5}

.lebd-mcq-nextpage .lebd-mcq-preview-panel{background:#fff;border:1px solid #d5e4fa;border-radius:17px;padding:24px;margin:16px 0;box-shadow:0 8px 24px #123b6410}

.lebd-mcq-nextpage h1{font-size:29px;margin:14px 0 3px}

.lebd-mcq-nextpage .lebd-mcq-preview-sub{color:#5d789b;margin:0 0 20px}

.lebd-mcq-nextpage .lebd-mcq-row{display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e6eef9;padding:13px 0;gap:15px}

.lebd-mcq-nextpage .lebd-mcq-row strong{white-space:nowrap}

.lebd-mcq-nextpage .lebd-mcq-final{background:#e9f9f0;color:#078452;padding:14px;border-radius:10px;font-weight:bold}

.lebd-mcq-nextpage .lebd-mcq-preview-primary{display:block;text-align:center;width:100%;padding:17px 16px;border-radius:10px;background:#0d73dc;color:#fff;font-size:20px;font-weight:800;border:0;line-height:1.45;cursor:pointer}.lebd-mcq-nextpage .lebd-mcq-preview-primary:disabled{opacity:.65;cursor:not-allowed}

.lebd-mcq-nextpage .lebd-mcq-preview-back{display:block;border:1px solid #a5c9ff;text-align:center;padding:13px;border-radius:10px;margin-top:12px}

.lebd-mcq-nextpage .lebd-mcq-preview-scope{background:#f1f7ff;border:1px solid #d4e5ff;padding:15px;border-radius:12px;line-height:1.8}

.lebd-mcq-nextpage .lebd-mcq-preview-note{font-size:12px;color:#617894;line-height:1.5}

@media(max-width:600px){.lebd-mcq-nextpage{margin:8px auto;padding:12px}.lebd-mcq-nextpage .lebd-mcq-preview-panel{padding:15px}.lebd-mcq-nextpage h1{font-size:24px}}

</style>

<main class="lebd-mcq-nextpage">

<a href="{{ url()->current() }}">← প্যাকেজে ফিরে যান</a>

<h1>Payment Preview</h1>

<p class="lebd-mcq-preview-sub">{{ $mcqMethod === 'referral' ? 'Referral Balance থেকে টাকা কাটার আগে যাচাই করুন।' : 'SSLCommerz-এ যাওয়ার আগে প্যাকেজের মূল্য যাচাই করুন।' }}</p>

@if(session('error'))
<p role="alert" style="padding:12px;background:#fff1f0;color:#a61b1b;border-radius:8px">{{ session('error') }}</p>
@endif
<section class="lebd-mcq-preview-panel">

<div class="lebd-mcq-preview-scope"><strong>{{ $package->name }}</strong><div>মেয়াদ: {{ (int)$package->duration_value }} {{ $durationUnit }}</div><div>Payment: {{ $mcqMethod === 'referral' ? 'Referral Balance' : 'SSLCommerz' }}</div></div>

</section>

<section class="lebd-mcq-preview-panel">

<h2>পেমেন্টের সারসংক্ষেপ</h2>

<div class="lebd-mcq-row"><span>প্যাকেজের মূল মূল্য</span><strong>৳{{ number_format($original,2) }}</strong></div>

<div class="lebd-mcq-row"><span>ডিসকাউন্ট</span><strong>− ৳{{ number_format($discount,2) }}</strong></div>

<div class="lebd-mcq-row"><span>{{ $mcqMethod === 'referral' ? 'মোট মূল্য' : 'SSLCommerz-এ পরিশোধযোগ্য' }}</span><strong>৳{{ number_format($final,2) }}</strong></div>

@if($mcqMethod === 'referral')

<div class="lebd-mcq-row"><span>আপনার Available Referral Balance</span><strong>৳{{ number_format($mcqAvailable,2) }}</strong></div>

<div class="lebd-mcq-row"><span>Referral Balance থেকে কাটা হবে</span><strong>৳{{ number_format($final,2) }}</strong></div>

<div class="lebd-mcq-final">পেমেন্টের পর আনুমানিক Available Balance: ৳{{ number_format(max(0,$mcqAvailable-$final),2) }}</div>

@if($mcqAvailable < $final)<p role="alert" class="lebd-mcq-preview-note" style="color:#b42318">পর্যাপ্ত Referral Balance নেই। SSLCommerz বেছে নিন।</p>@endif

@else

<div class="lebd-mcq-final">SSLCommerz: Payment Confirm চাপলে নিরাপদ Gateway Checkout খুলবে।</div>

@endif

</section>

@if($mcqMethod === 'sslcommerz')

  @auth('student')

    {{-- Use the existing, previously working SSLCommerz payment start endpoint. --}}

    <form method="POST" action="{{ route('lebd.subscription.payment.start', ['section' => $section, 'package' => $package->id]) }}" id="lebdMcqGatewayPaymentForm">

      @csrf

      <button type="submit" class="lebd-mcq-preview-primary" id="lebdMcqGatewayConfirm">Payment Confirm →</button>

    </form>

    <script>

    (function(){

      var form=document.getElementById('lebdMcqGatewayPaymentForm');

      if(!form)return;

      form.addEventListener('submit',function(){

        var btn=document.getElementById('lebdMcqGatewayConfirm');

        if(btn){btn.disabled=true;btn.textContent='Processing…';}

      });

    })();

    </script>

  @else

    <a class="lebd-mcq-preview-primary" href="{{ route('students.login') }}">পেমেন্ট করতে Student Login করুন →</a>

  @endauth

@else

  @auth('student')

    <form method="POST" action="{{ route('lebd.subscription.payment.referral', ['section'=>$section,'package'=>$package->id]) }}" id="lebdMcqReferralPaymentForm">

      @csrf

      <input type="hidden" name="checkout_token" value="{{ $mcqWalletToken }}">

      <button type="submit" class="lebd-mcq-preview-primary" id="lebdMcqReferralConfirm" @if($mcqAvailable < $final) disabled @endif>কনফার্ম পেমেন্ট →</button>

    </form>

    <script>

    (function(){var f=document.getElementById('lebdMcqReferralPaymentForm');if(!f)return;f.addEventListener('submit',function(){var b=document.getElementById('lebdMcqReferralConfirm');if(b){b.disabled=true;b.textContent='Processing…';}});})();

    </script>

  @else

    <a class="lebd-mcq-preview-primary" href="{{ route('students.login') }}">পেমেন্ট করতে Student Login করুন →</a>

  @endauth

@endif

@if($mcqMethod === 'referral')

<a class="lebd-mcq-preview-back" href="{{ url()->current() }}">← বাতিল করুন</a>

@endif

<p class="lebd-mcq-preview-note">@if($mcqMethod === 'sslcommerz')SSLCommerz-এর যাচাইকৃত পেমেন্ট সফল হওয়ার পরেই Subscription চালু হবে।@else Referral Balance পেমেন্ট Confirm করলে যাচাই শেষে টাকা কাটা হবে এবং MCQ Subscription চালু হবে।@endif</p>

</main>

@else

<div class="lebd-order-page">

  <a href="{{ route('lebd.packages.index', ['section'=>$section]) }}" class="up-inline-back">← প্যাকেজ তালিকায় ফিরুন</a>

  <section class="lebd-order-heading"><h1>অর্ডার যাচাই করুন</h1><p>পেমেন্টের আগে আপনার প্যাকেজ ও মূল্য মিলিয়ে নিন।</p></section>

  <div class="lebd-order-grid">

    <section class="lebd-order-box">

      <h2>{{ $package->name }}</h2>

      <p class="lebd-order-muted">মেয়াদ: {{ (int)$package->duration_value }} {{ $durationUnit }}</p>

      @if($package->description)<p class="lebd-order-description">{{ $package->description }}</p>@endif

      <h3>অন্তর্ভুক্ত বিষয়</h3>

      @if(count($dept))

        <strong>Department</strong>

        <div class="lebd-order-chips">@foreach($dept as $cat)<span>{{ $cat }}</span>@endforeach</div>

      @endif

      @if(count($nondept))

        <strong>Non-Department</strong>

        <div class="lebd-order-chips">@foreach($nondept as $cat)<span>{{ $cat }}</span>@endforeach</div>

      @endif

      <div class="lebd-order-features"><span>📖 {{ $package->reading_limit === null ? 'Unlimited Reading' : 'প্রতিদিন '.(int)$package->reading_limit.' MCQ' }}</span><span>📝 {{ $package->quiz_limit === null ? 'Unlimited Quiz' : 'মাসে '.(int)$package->quiz_limit.' Quiz' }}</span></div>

    </section>

    <section class="lebd-order-box lebd-order-summary">

      <h2>অর্ডার সামারি</h2>

      <div class="lebd-order-line"><span>মূল মূল্য</span><strong>৳{{ number_format($original,2) }}</strong></div>

      <div class="lebd-order-line lebd-order-discount"><span>ছাড় ({{ number_format($saving,2) }}%)</span><strong>− ৳{{ number_format($discount,2) }}</strong></div>

      <div class="lebd-order-line lebd-order-total"><span>সর্বমোট পরিশোধযোগ্য</span><strong>৳{{ number_format($final,2) }}</strong></div>

      @if(session('error'))<p class="lebd-order-note" role="alert">{{ session('error') }}</p>@endif

      <p class="lebd-order-note">পেমেন্ট পদ্ধতি নিচে নির্বাচন করুন। এই ধাপে কোনো পেমেন্ট সম্পন্ন হবে না।</p>

    </section>

  </div>

  @auth('student')

    <section class="lebd-mcq-payment-details" aria-label="পেমেন্টের বিস্তারিত">

      <header>পেমেন্টের বিস্তারিত</header>

      <div class="lebd-mcq-payment-detail-row"><span>কোর্সের মূল্য</span><strong>৳{{ number_format($original,2) }}</strong></div>

      <div class="lebd-mcq-payment-detail-row"><span>অন্যান্য ফি (+)</span><strong>৳0.00</strong></div>

      <div class="lebd-mcq-payment-detail-row is-discount"><span>ডিসকাউন্ট (-)</span><strong>− ৳{{ number_format($discount,2) }}</strong></div>

      <div class="lebd-mcq-payment-detail-row is-total"><span>মোট =</span><strong>৳{{ number_format($final,2) }}</strong></div>

    </section>

    <form method="GET" action="{{ url()->current() }}" id="lebdMcqPreviewForm">
      <input type="hidden" name="mcq_preview" value="1">
      <section class="lebd-mcq-methods" aria-label="পেমেন্ট পদ্ধতি নির্বাচন">
        <h3>আমাদের পেমেন্ট সিস্টেমে অন্তর্ভুক্ত আছে</h3>
        <label class="lebd-mcq-method-option" id="lebdMcqReferralOption">
          <input type="radio" name="method" value="referral">
          <span class="lebd-mcq-method-main"><strong>Use Your Referral Balance</strong><small>Available Balance দিয়ে ব্যয় করুন</small></span>
          <span class="lebd-mcq-balance">৳{{ number_format($mcqAvailable,2) }}<small>Available Balance</small></span>
        </label>
        <label class="lebd-mcq-method-option gateway is-selected" id="lebdMcqGatewayOption">
          <input type="radio" name="method" value="sslcommerz" checked>
          <span class="lebd-mcq-method-main"><strong>Online Payment Gateway — SSLCommerz</strong><span class="lebd-mcq-gateway-methods"><span>Card</span><span>Mobile Banking</span><span>Internet Banking</span><span>Wallet</span></span></span>
        </label>
      </section>
      <aside class="lebd-mcq-warning"><strong>⚠️ দ্রষ্টব্য</strong>Referral Balance নির্বাচন করলে পর্যাপ্ত টাকা থাকলে Confirm-এর পর কাটা হবে। SSLCommerz নির্বাচন করলে যাচাইকৃত Gateway Payment সফল হওয়ার পর Access চালু হবে।</aside>
      <label class="lebd-mcq-terms"><input type="checkbox" name="terms_accepted" value="1" id="lebdMcqTerms" required><span>I agree to the Terms &amp; Conditions, Privacy Policy and Refund Policy.</span></label>
      <button class="lebd-mcq-preview-button" type="submit" id="lebdMcqPreviewButton">Payment Preview →</button>
      <p class="lebd-mcq-design-note">Referral ও SSLCommerz উভয়ের Payment সফল হওয়ার পর প্যাকেজের মেয়াদ অনুযায়ী Access চালু হবে।</p>
    </form>
  @else

    <a class="lebd-order-pay" href="{{ route('students.login') }}">পেমেন্ট করতে Student Login করুন</a>

  @endauth

</div>

<script>
(function () {
  var options = document.querySelectorAll('#lebdMcqPreviewForm input[name="method"]');
  function updateSelection() {
    options.forEach(function (radio) {
      var label = radio.closest('.lebd-mcq-method-option');
      if (label) label.classList.toggle('is-selected', radio.checked);
    });
  }
  options.forEach(function (radio) { radio.addEventListener('change', updateSelection); });
  updateSelection();
})();
</script>

@endif

@endsection
