@extends('homepage')
@section('title','প্যাকেজ ও পেমেন্ট সামারি')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-premium-package-fix114.css') }}">
<link rel="stylesheet" href="{{ asset('web/css/lebd-premium-payment-methods.css') }}">
<div class="lebd114-reviewwrap">
 <a class="lebd114-back" href="{{ url()->previous() }}">← প্যাকেজ তালিকায় ফিরে যান</a>
 <div class="lebd114-hero"><span class="lebd114-kicker">CHECKOUT REVIEW</span><h1>প্যাকেজ ও দামের সামারি</h1><p>পেমেন্টের আগে নিচের তথ্যগুলো যাচাই করুন।</p></div>
 @if($errors->any())<div role="alert" style="padding:12px 16px;margin:12px 0;border:1px solid #ffc5c5;background:#fff2f2;color:#a71919;border-radius:9px;font-weight:600">{{ $errors->first() }}</div>@endif
 <div class="lebd114-reviewgrid">
  <section class="lebd114-reviewcard"><h2>প্যাকেজের বিবরণ</h2><dl><dt>প্যাকেজ</dt><dd>{{ $package->name }}</dd><dt>ধরন</dt><dd>{{ ucwords(str_replace(['-','_'],' ',(string) $package->package_type)) }}</dd><dt>মেয়াদ</dt><dd>{{ $package->duration_value ?? $package->validity_days }} {{ $package->duration_unit ?? 'days' }}</dd></dl><div class="lebd114-description">{{ $package->description }}</div>
   @if(count((array)($package->department_categories ?? [])))<p><b>Department:</b> {{ implode(', ',(array)$package->department_categories) }}</p>@endif
   @if(count((array)($package->non_department_categories ?? [])))<p><b>Non-Department:</b> {{ implode(', ',(array)$package->non_department_categories) }}</p>@endif
  </section>
  <section class="lebd114-reviewcard"><h2>Payment Summary</h2><div class="lebd114-summaryrow"><span>মূল দাম</span><strong>৳{{ number_format($main,2) }}</strong></div><div class="lebd114-summaryrow"><span>ছাড়</span><strong>− ৳{{ number_format($discount,2) }}</strong></div><div class="lebd114-summaryrow lebd114-total"><span>সর্বমোট</span><strong>৳{{ number_format($final,2) }}</strong></div>
   <p class="lebd114-secure">পেমেন্ট সফল ও যাচাই হলে Premium Access সক্রিয় হবে।</p>
  </section>
 </div>
 @auth('student')
 @php
  $paymentReferralBalance = isset($referralBalance) && is_numeric($referralBalance) ? max(0, (float) $referralBalance) : null;
  $paymentReferralReady = \Illuminate\Support\Facades\Route::has('premium.payment.referral.checkout') && $paymentReferralBalance !== null && $paymentReferralBalance >= (float) $final;
 @endphp
 <section class="lebdpm-panel" aria-labelledby="lebdpm-heading">
  <h2 id="lebdpm-heading">পেমেন্ট পদ্ধতি বেছে নিন</h2>
  <form id="lebdpm-checkout" method="GET" action="{{ route('premium.payment.preview',$package) }}">
    <label class="lebdpm-method {{ $paymentReferralReady ? '' : 'lebdpm-unavailable' }}">
    <input type="radio" name="payment_method" value="referral" data-action="{{ \Illuminate\Support\Facades\Route::has('premium.payment.referral.checkout') ? route('premium.payment.referral.checkout',$package) : '' }}" @disabled(!$paymentReferralReady)>
    <span class="lebdpm-method-body"><strong>Use Your Referral Balance</strong><small>Available Balance দিয়ে প্যাকেজ কিনুন</small></span>
    <span class="lebdpm-balance"><strong>{{ $paymentReferralBalance === null ? '—' : '৳'.number_format($paymentReferralBalance,2) }}</strong><small>Available Balance</small></span>
   </label>
   @unless($paymentReferralReady)
    <p class="lebdpm-hint">{{ $paymentReferralBalance === null ? 'Referral Balance এখন দেখানো যাচ্ছে না। অনুগ্রহ করে আবার চেষ্টা করুন।' : 'Referral Balance অপর্যাপ্ত। SSLCommerz দিয়ে পেমেন্ট করতে পারেন।' }}</p>
   @endunless
   <label class="lebdpm-method lebdpm-gateway">
    <input type="radio" name="payment_method" value="sslcommerz" data-action="{{ route('premium.payment.checkout',$package) }}" checked>
    <span class="lebdpm-method-body"><strong>Online Payment Gateway — SSLCommerz</strong><span class="lebdpm-chips"><span>Card</span><span>Mobile Banking</span><span>Internet Banking</span><span>Wallet</span></span></span>
   </label>
   <aside class="lebdpm-warning"><strong>⚠ সতর্কতা</strong><p>Referral Balance নির্বাচন করলে পর্যাপ্ত ব্যালেন্স থেকে প্যাকেজের মূল্য কাটা হবে। SSLCommerz নির্বাচন করলে যাচাইকৃত Online Payment-এর পর Access চালু হবে।</p></aside>
   <label class="lebdpm-terms"><input type="checkbox" name="terms_accepted" value="1" required><span>I agree to the Terms &amp; Conditions, Privacy Policy and Refund Policy.</span></label>
   <button class="lebd114-buy lebdpm-pay" type="submit">Payment Preview →</button>
  </form>
 </section>
 @else
 <div class="lebdpm-login"><a href="{{ route('students.login') }}" class="lebd114-buy">পেমেন্ট করতে লগইন করুন →</a></div>
 @endauth
</div>
@endsection
