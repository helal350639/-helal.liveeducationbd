@extends('homepage.packages.layout')
@section('page_title', $package->name)
@section('body')
<a class="up-inline-back" href="{{ route('lebd.packages.index', ['section'=>$section]) }}">← সব প্যাকেজ</a>
<section class="up-intro"><div class="up-crown" aria-hidden="true">♛</div><div><h1>{{ $package->name }}</h1><p>প্যাকেজের বিস্তারিত তথ্য</p></div></section>
<section class="up-details">
<div class="up-details-head"><h2>{{ $package->name }}</h2><strong class="up-price">৳{{ number_format((float)$package->price, (float)$package->price == floor((float)$package->price) ? 0 : 2) }}</strong></div>
@if((float)$package->discount_amount > 0 && (float)$package->main_price > 0)
<p><span style="text-decoration:line-through;color:#9299aa">মূল দাম: ৳{{ number_format((float)$package->main_price, 2) }}</span> &nbsp; <strong style="color:#087a3e">ছাড়: ৳{{ number_format((float)$package->discount_amount, 2) }} ({{ number_format(((float)$package->discount_amount / (float)$package->main_price)*100, 2) }}%)</strong></p>
@endif
<p><strong>মেয়াদ:</strong> {{ (int)$package->duration_value }} {{ ['days'=>'দিন', 'months'=>'মাস', 'years'=>'বছর'][$package->duration_unit] ?? $package->duration_unit }}</p>
<p><strong>MCQ Reading:</strong> {{ $package->reading_limit === null ? 'Unlimited' : 'প্রতিদিন '.(int)$package->reading_limit.' টি' }}</p>
<p><strong>Quiz:</strong> {{ $package->quiz_limit === null ? 'Unlimited' : 'প্রতি মাসে '.(int)$package->quiz_limit.' টি' }}</p>
@if($package->description)<p class="up-full-description">{{ $package->description }}</p>@endif
@if(!empty($categories['department']))<h3>Department Categories</h3><ul class="up-category-list">@foreach($categories['department'] as $name)<li>✓ {{ $name }}</li>@endforeach</ul>@endif
@if(!empty($categories['non-department']))<h3>Non-Department Categories</h3><ul class="up-category-list">@foreach($categories['non-department'] as $name)<li>✓ {{ $name }}</li>@endforeach</ul>@endif
<a class="up-action up-buy up-detail-buy" style="display:inline-block;text-decoration:none;text-align:center" href="{{ route('lebd.packages.checkout', ['section'=>$section,'package'=>$package->id]) }}">🛒 প্যাকেজ কিনুন</a>
<p class="up-disclaimer">অর্ডার দেখার পর পেমেন্ট করা যাবে। SSLCommerz Payment Connection পরবর্তী ধাপে যুক্ত হবে।</p>
</section>
@endsection
