@extends('homepage')
@section('title','প্যাকেজ ও পেমেন্ট সামারি')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-premium-package-fix114.css') }}">
<link rel="stylesheet" href="{{ asset('web/css/lebd-premium-payment-methods.css') }}">
@php
  $premiumBasePrice = $quote['base_cents']/100;
  $premiumFee = $quote['fee_cents']/100;
  $premiumRefDiscount = $quote['discount_cents']/100;
@endphp
<div class="lebd114-reviewwrap">
 <a class="lebd114-back" href="{{ route('premium.recordings.packages') }}">← প্যাকেজ তালিকায় ফিরে যান</a>
 <div class="lebd114-hero"><span class="lebd114-kicker">CHECKOUT REVIEW</span><h1>প্যাকেজ ও দামের সামারি</h1><p>পেমেন্টের আগে নিচের তথ্যগুলো যাচাই করুন।</p></div>
 @if(session('premium_ref_code_'.$package->id) && !$quote['referrer_id'])
 <div role="alert" style="padding:12px 16px;margin:12px 0;border:1px solid #e99e42;background:#fff7e9;color:#854900;border-radius:9px;font-weight:600">এই Referral Link যাচাই করা যায়নি। Referral Discount প্রযোজ্য হয়নি। Link Owner-এর Active Package, Referral Code এবং একই Package ID যাচাই করুন। ভুল দামে Payment করবেন না।</div>
 @elseif($quote['referrer_id'])
 <div role="status" style="padding:12px 16px;margin:12px 0;border:1px solid #8dcdaa;background:#effcf3;color:#12683e;border-radius:9px;font-weight:600">✓ Referred By: {{ $quote['referrer_name'] }}। Valid Referral Link applied. Referral Discount: ৳{{ number_format($quote['discount_cents']/100,2) }}। এই ছাড় SSLCommerz ও Referral Wallet—উভয় পদ্ধতিতে প্রযোজ্য।</div>
 @endif
 @if($errors->any())<div role="alert" style="padding:12px 16px;margin:12px 0;border:1px solid #ffc5c5;background:#fff2f2;color:#a71919;border-radius:9px;font-weight:600">{{ $errors->first() }}</div>@endif
 <div class="lebd114-reviewgrid">
  <section class="lebd114-reviewcard"><h2>প্যাকেজের বিবরণ</h2><dl><dt>প্যাকেজ</dt><dd>{{ $package->name }}</dd><dt>ধরন</dt><dd>{{ ucwords(str_replace(['-','_'],' ',(string) $package->package_type)) }}</dd><dt>মেয়াদ</dt><dd>{{ $package->duration_value ?? $package->validity_days }} {{ $package->duration_unit ?? 'days' }}</dd></dl><div class="lebd114-description">{{ $package->description }}</div>
   @if(count((array)($package->department_categories ?? [])))<p><b>Department:</b> {{ implode(', ',(array)$package->department_categories) }}</p>@endif
   @if(count((array)($package->non_department_categories ?? [])))<p><b>Non-Department:</b> {{ implode(', ',(array)$package->non_department_categories) }}</p>@endif
  </section>
  <section class="lebd114-reviewcard"><h2>Payment Summary</h2>
   <div class="lebd114-summaryrow"><span>মূল দাম</span><strong>৳{{ number_format($main,2) }}</strong></div>
   <div class="lebd114-summaryrow"><span>Package Discount</span><strong>− ৳{{ number_format($discount,2) }}</strong></div>
   @if($premiumFee > 0)<div class="lebd114-summaryrow"><span>Extra Fee</span><strong>+ ৳{{ number_format($premiumFee,2) }}</strong></div>@endif
   @if($quote['referrer_id'])
    <div class="lebd114-summaryrow"><span>Referral Link Discount</span><strong style="color:#08783b">− ৳{{ number_format($premiumRefDiscount,2) }}</strong></div>
    <p style="color:#126c42">Active Premium Buyer-এর Referral Link প্রয়োগ হয়েছে।</p>
   @endif
   <div class="lebd114-summaryrow lebd114-total"><span>সর্বমোট</span><strong>৳{{ number_format($final,2) }}</strong></div>
   <p class="lebd114-secure">সফল ও যাচাইকৃত পেমেন্টের পর Premium Access সক্রিয় হবে।</p>
  </section>
 </div>
 @auth('student')
 @php
  $paymentReferralBalance = isset($referralBalance) && is_numeric($referralBalance) ? max(0,(float)$referralBalance) : null;
  $paymentReferralReady = \Illuminate\Support\Facades\Route::has('premium.payment.referral.checkout') && $paymentReferralBalance !== null && $paymentReferralBalance >= (float)$final;
 @endphp
 <section class="lebdpm-panel" aria-labelledby="lebdpm-heading">
  <h2 id="lebdpm-heading">পেমেন্ট পদ্ধতি বেছে নিন</h2>
  <form id="lebdpm-checkout" method="GET" action="{{ route('premium.payment.preview',$package) }}">
   @if($quote['referrer_id'])<input type="hidden" name="ref" value="{{ $quote['referrer_code'] }}">@endif
   <label class="lebdpm-method {{ $paymentReferralReady ? '' : 'lebdpm-unavailable' }}"><input type="radio" name="payment_method" value="referral" @disabled(!$paymentReferralReady)><span class="lebdpm-method-body"><strong>Use Your Referral Balance</strong><small>Available Balance দিয়ে প্যাকেজ কিনুন</small></span><span class="lebdpm-balance"><strong>{{ $paymentReferralBalance === null ? '—' : '৳'.number_format($paymentReferralBalance,2) }}</strong><small>Available Balance</small></span></label>
   @unless($paymentReferralReady)<p class="lebdpm-hint">{{ $paymentReferralBalance === null ? 'Referral Balance এখন দেখানো যাচ্ছে না।' : 'Referral Balance অপর্যাপ্ত। SSLCommerz ব্যবহার করুন।' }}</p>@endunless
   <label class="lebdpm-method lebdpm-gateway"><input type="radio" name="payment_method" value="sslcommerz" checked><span class="lebdpm-method-body"><strong>Online Payment Gateway — SSLCommerz</strong><span class="lebdpm-chips"><span>Card</span><span>Mobile Banking</span><span>Internet Banking</span><span>Wallet</span></span></span></label>
   <aside class="lebdpm-warning"><strong>⚠ সতর্কতা</strong><p>Referral Discount দুই Payment Method-এই সমান। Referrer Bonus কেবল সফল Verified Purchase-এ জমা হবে।</p></aside>
   <label class="lebdpm-terms"><input type="checkbox" name="terms_accepted" value="1" required><span>I agree to the Terms &amp; Conditions, Privacy Policy and Refund Policy.</span></label>
   <button class="lebd114-buy lebdpm-pay" type="submit" @disabled(session('premium_ref_code_'.$package->id) && !$quote['referrer_id'])>Payment Preview →</button>
  </form>
 </section>
 @else
 <div class="lebdpm-login"><a href="{{ route('students.login') }}" class="lebd114-buy">পেমেন্ট করতে লগইন করুন →</a></div>
 @endauth
</div>
@endsection
