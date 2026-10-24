@extends('homepage')
@section('title','Premium Recorded Class Packages')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-premium-package-fix114.css') }}">
@php
 $tabs=['all'=>'সব প্যাকেজ','combined'=>'Combined','department'=>'Department','non_department'=>'Non-Department'];
@endphp
<div class="lebd114-wrap">
 <div class="lebd114-hero"><span class="lebd114-kicker">LIVE EDUCATION BD • PREMIUM</span><h1>আপনার পছন্দের প্যাকেজ নির্বাচন করুন</h1><p>প্রয়োজন অনুযায়ী Department, Non-Department অথবা Combined Package কিনুন।</p></div>
 <nav class="lebd114-tabs" aria-label="Package categories">
  @foreach($tabs as $slug=>$label)
   <a href="{{route('premium.recordings.packages',array_filter(['type'=>$slug,'premium_group'=>$group,'premium_topic'=>$topic]))}}" class="lebd114-tab {{ $type===$slug?'is-active':'' }}" @if($type===$slug) aria-current="page" @endif>{{$label}}</a>
  @endforeach
 </nav>
 @if(session('success'))<div class="lebd114-success">{{session('success')}}</div>@endif
 @if($errors->any())<div class="lebd114-error">{{$errors->first()}}</div>@endif
 <div class="lebd114-grid">
 @forelse($packages as $package)
 @php
  $kind=str_replace(['-',' '],'_',(string)$package->package_type);
  if($kind==='combine')$kind='combined';
  $price=(float)$package->price;
  $final=(float)($package->final_price??$package->price);
  $saving=max(0,$price-$final);
  $duration=(int)($package->duration_value??$package->validity_days);
  $unit=(string)($package->duration_unit??'days');
  $dept=(array)($package->department_categories??[]);
  $nondept=(array)($package->non_department_categories??[]);
 @endphp
 <article class="lebd114-card">
   <header class="lebd114-cardhead"><span class="lebd114-tag">{{ $kind==='combined'?'COMBINED':($kind==='department'?'DEPARTMENT':'NON-DEPARTMENT') }}</span><h2>{{$package->name}}</h2><p>{{ $kind==='combined'?'Department ও Non-Department':($kind==='department'?'Department ভিত্তিক ক্লাস':'Non-Department ভিত্তিক ক্লাস') }}</p></header>
   <div class="lebd114-cardbody">
     <div class="lebd114-prices">@if($saving>0)<s>৳{{number_format($price,2)}}</s>@endif<strong>৳{{number_format($final,2)}}</strong>@if($saving>0)<small>সাশ্রয় ৳{{number_format($saving,2)}}</small>@endif</div>
     <div class="lebd114-meta"><span>◷ মেয়াদ: {{$duration}} {{$unit}}</span><span>✓ Recorded Classes</span></div>
     <details class="lebd114-detail"><summary>প্যাকেজের বিস্তারিত দেখুন <span>⌄</span></summary><div class="lebd114-description">{{ $package->description ?: 'নির্বাচিত বিভাগ ও বিষয়গুলোর Premium Recorded Classes দেখার সুবিধা।' }}</div>
     @if(count($dept))<p><b>Department:</b> {{implode(', ',array_map('ucfirst', $dept))}}</p>@endif
     @if(count($nondept))<p><b>Non-Department:</b> {{implode(', ',array_map(fn($x)=>str_replace('-',' ',$x), $nondept))}}</p>@endif
     <p class="lebd114-detailnote">কেনা প্যাকেজের মেয়াদ ও নির্বাচিত Category অনুযায়ী Access প্রযোজ্য হবে।</p></details>
     <a class="lebd114-buy" href="{{ route('premium.payment.review',$package) }}">প্যাকেজ কিনুন <span>→</span></a>
   </div>
 </article>
 @empty<div class="lebd114-empty">এই ধরনের কোনো Active Premium Package নেই। উপরের অন্য Category নির্বাচন করুন।</div>
 @endforelse
 </div>
 <a class="lebd114-back" href="{{route('homepage',array_filter(['premium_group'=>$group,'premium_topic'=>$topic,'premium_view'=>'recorded']))}}">← রেকর্ড ক্লাসে ফিরে যান</a>
</div>
@endsection
