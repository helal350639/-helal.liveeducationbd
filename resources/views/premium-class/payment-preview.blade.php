@extends('homepage')
@section('title','Payment Preview — Live Education BD')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-payment-preview.css') }}">
<main class="lebdpv-page">
 <div class="lebdpv-breadcrumb"><a href="{{ route('premium.payment.review', ['package' => $package->id] + ($quote['referrer_id'] ? ['ref' => $quote['referrer_code']] : [])) }}">← প্যাকেজ ও পেমেন্ট পদ্ধতিতে ফিরে যান</a></div>
 <section class="lebdpv-card">
  <header class="lebdpv-head"><span class="lebdpv-cart">🛒</span><div><h1>Payment Preview</h1><p>পেমেন্টের আগে নিচের তথ্যগুলো যাচাই করুন।</p></div></header>
  <div class="lebdpv-package"><div class="lebdpv-package-icon">▶</div><div><h2>{{ $package->name }}</h2>
   <p>Package Type: {{ ucwords(str_replace(['_','-'],' ',(string)$package->package_type)) }}</p>
   <p>মেয়াদ: {{ $package->duration_value ?? $package->validity_days }} {{ $package->duration_unit ?? 'days' }}</p>
   @if($package->department_categories)<p>Department: {{ implode(', ',(array)$package->department_categories) }}</p>@endif
   @if($package->non_department_categories)<p>Non-Department: {{ implode(', ',(array)$package->non_department_categories) }}</p>@endif
  </div></div>
  @php
   $lebdTitle=$package->name;
   $lebdOriginalPrice=$main;
   $lebdPrice=$final;
   $lebdBalance=$balance;
   $lebdRemaining=$remaining;
   $lebdMethod=$method;
  @endphp
  @if($quote['referrer_id'])
  <div role="status" style="margin:14px 0;padding:12px 15px;border:1px solid #b5dec8;border-radius:9px;background:#effbf4;color:#17633b;line-height:1.7">
   <strong>Referred By:</strong> {{ $quote['referrer_name'] }}
   <div>Valid Referral Link — Discount: <strong>৳{{ number_format($quote['discount_cents']/100,2) }}</strong></div>
  </div>
  @endif
  <div class="lebdpv-panel">
   <div class="lebdpv-heading"><span class="lebdpv-icon">▣</span><div><h2>পেমেন্টের সারসংক্ষেপ</h2><p>Confirm করার আগে মূল্য ও ব্যালেন্স যাচাই করুন</p></div></div>
   <div class="lebdpv-line"><span>মূল দাম</span><strong>৳{{ number_format($main,2) }}</strong></div>
   <div class="lebdpv-line"><span>Package Discount</span><strong>− ৳{{ number_format(max(0,$main-$quote['base_cents']/100),2) }}</strong></div>
   @if($quote['fee_cents']>0)<div class="lebdpv-line"><span>Extra Fee</span><strong>+ ৳{{ number_format($quote['fee_cents']/100,2) }}</strong></div>@endif
   @if($quote['referrer_id'])<div class="lebdpv-line"><span>Referral Discount</span><strong>− ৳{{ number_format($quote['discount_cents']/100,2) }}</strong></div>@endif
   <div class="lebdpv-line"><span>সর্বমোট পরিশোধযোগ্য</span><strong>৳{{ number_format($final,2) }}</strong></div>
   @if($method === 'referral')
    <div class="lebdpv-line"><span>Available Referral Balance</span><strong>৳{{ number_format($balance,2) }}</strong></div>
    <div class="lebdpv-line"><span>Wallet থেকে পরিশোধযোগ্য</span><strong>৳{{ number_format($final,2) }}</strong></div>
    <div class="lebdpv-remaining"><span>Confirm করলে অবশিষ্ট Balance</span><strong>৳{{ number_format($remaining,2) }}</strong></div>
   @else
    <div class="lebdpv-remaining"><span>SSLCommerz-এ পরিশোধযোগ্য</span><strong>৳{{ number_format($final,2) }}</strong></div>
   @endif
  </div>
  <div class="lebdpv-method">Selected: <strong>{{ $method === 'referral' ? 'Referral Balance' : 'SSLCommerz Online Payment' }}</strong></div>
  @if($method === 'referral' && !$available)<div class="lebdpv-alert" role="alert">Referral Balance পর্যাপ্ত নয়। অন্য Payment Method নির্বাচন করুন।</div>@endif
  @if($errors->any())<div class="lebdpv-alert" role="alert">{{ $errors->first() }}</div>@endif
  <form method="POST" action="{{ route($payRoute,$package) }}" id="lebdpv-confirm">@csrf
   <input type="hidden" name="payment_method" value="{{ $method }}">
   <label class="lebdpv-accept"><input type="checkbox" name="terms_accepted" value="1" required> আমি Terms &amp; Conditions, Privacy Policy এবং Refund Policy-তে সম্মত।</label>
   <button type="submit" class="lebdpv-confirm" @disabled($method === 'referral' && !$available)>{{ $method === 'referral' ? '✓ কনফার্ম পেমেন্ট — Referral Balance' : '✓ SSLCommerz পেমেন্টে এগিয়ে যান' }}</button>
  </form>
  <a href="{{ route('premium.payment.review', ['package' => $package->id] + ($quote['referrer_id'] ? ['ref' => $quote['referrer_code']] : [])) }}" class="lebdpv-cancel">← বাতিল করুন</a>
  <p class="lebdpv-note">Preview-তে টাকা কাটা হয় না। Confirm-এ সার্ভার মূল্য ও Referral Link পুনরায় যাচাই করবে।</p>
 </section>
</main>
@endsection
