@extends('homepage')
@section('title','Payment Preview — Live Education BD')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-payment-preview.css') }}">
<main class="lebdpv-page">
 <div class="lebdpv-breadcrumb"><a href="{{ route('premium.payment.review',$package) }}">← প্যাকেজ ও পেমেন্ট পদ্ধতিতে ফিরে যান</a></div>
 <section class="lebdpv-card">
  <header class="lebdpv-head"><span class="lebdpv-cart">🛒</span><div><h1>Payment Preview</h1><p>পেমেন্ট সম্পন্ন করার আগে নিচের তথ্যগুলো যাচাই করুন।</p></div></header>
  <div class="lebdpv-package">
   <div class="lebdpv-package-icon">▶</div><div><h2>{{ $package->name }}</h2>
    <p>Package Type: {{ ucwords(str_replace(['_','-'],' ',(string)$package->package_type)) }}</p>
    <p>মেয়াদ: {{ $package->duration_value ?? $package->validity_days }} {{ $package->duration_unit ?? 'days' }}</p>
    @if($package->department_categories)<p>Department: {{ implode(', ',(array)$package->department_categories) }}</p>@endif
    @if($package->non_department_categories)<p>Non-Department: {{ implode(', ',(array)$package->non_department_categories) }}</p>@endif
   </div>
  </div>
  @php
   $lebdTitle=$package->name;
   $lebdOriginalPrice=$main;
   $lebdPrice=$final;
   $lebdBalance=$balance;
   $lebdRemaining=$remaining;
   $lebdMethod=$method;
  @endphp
  @include('premium-class.partials.payment-preview-summary')
  <div class="lebdpv-method">Selected: <strong>{{ $method === 'referral' ? 'Referral Balance' : 'SSLCommerz Online Payment' }}</strong></div>
  @if($method === 'referral' && !$available)
   <div class="lebdpv-alert" role="alert">Referral Balance পর্যাপ্ত নয়। এই পদ্ধতিতে Confirm করা যাবে না। অন্য Payment Method নির্বাচন করুন।</div>
  @endif
  @if($errors->any())<div class="lebdpv-alert" role="alert">{{ $errors->first() }}</div>@endif
  <form method="POST" action="{{ route($payRoute,$package) }}" id="lebdpv-confirm">@csrf
    <input type="hidden" name="payment_method" value="{{ $method }}">
    <label class="lebdpv-accept"><input type="checkbox" name="terms_accepted" value="1" required> আমি Terms &amp; Conditions, Privacy Policy এবং Refund Policy-তে সম্মত।</label>
    <button type="submit" class="lebdpv-confirm" @disabled($method === 'referral' && !$available)>{{ $method === 'referral' ? '✓ কনফার্ম পেমেন্ট — Referral Balance' : '✓ SSLCommerz পেমেন্টে এগিয়ে যান' }}</button>
  </form>
  <a href="{{ route('premium.payment.review',$package) }}" class="lebdpv-cancel">← বাতিল করুন</a>
  <p class="lebdpv-note">Preview দেখলে কোনো টাকা কাটা হয় না। Confirm করলে সার্ভারে মূল্য ও ব্যালেন্স পুনরায় যাচাই হবে।</p>
 </section>
</main>
@endsection
