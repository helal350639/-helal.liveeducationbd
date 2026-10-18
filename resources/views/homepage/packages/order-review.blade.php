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
@endphp
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
      @auth('student')
        <form method="POST" action="{{ route('lebd.subscription.payment.start', ['section'=>$section, 'package'=>$package->id]) }}">@csrf
          <button class="lebd-order-pay" type="submit">SSLCommerz-এ পেমেন্ট করুন</button>
        </form>
      @else
        <a class="lebd-order-pay" href="{{ route('students.login') }}">পেমেন্ট করতে Student Login করুন</a>
      @endauth
      <p class="lebd-order-note">পেমেন্ট SSLCommerz-এর নিরাপদ Checkout-এ সম্পন্ন হবে। সফল যাচাইয়ের পর Subscription চালু হবে।</p>
    </section>
  </div>
</div>
@endsection
