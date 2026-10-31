@extends('homepage')
@section('title',$cat->name.' | Premium Packages')
@section('content')
<link rel="stylesheet" href="{{asset('web/css/lebd-ebook-student-v22.css')}}">
<link rel="stylesheet" href="{{asset('web/css/lebd-ebook-premium-v25.css')}}">
<main class="ebp-student">
@if(session('error'))<div role="alert" style="color:#b42318;padding:14px">{{session('error')}}</div>@endif
<a href="{{route('student.ebook.chapters',['kind'=>$kind,'category'=>$cat->id])}}" class="ebp-back">← {{$cat->name}} সূচিপত্র</a>
<section class="ebp-notice"><div class="ebp-lock">🔒</div><h1>Premium Access প্রয়োজন</h1><p>এই বইয়ের সম্পূর্ণ অধ্যায় এবং Full Book পড়ার অনুমতি তোমার অ্যাকাউন্টে নেই। সম্পূর্ণ বই পড়তে একটি Premium Package কিনুন।</p></section>
<section class="ebp-catalog"><h2><i class="fa fa-crown"></i> {{$cat->name}} — Premium Packages</h2><p>দাম, অফার ও মেয়াদ দেখে Package নির্বাচন করুন।</p><div class="ebp-card-grid">
@forelse($packages as $p)
<article class="ebp-package-card"><div class="ebp-package-tag">PREMIUM E-BOOK</div><h3>{{$p->name}}</h3><p>{{$p->description}}</p><div class="ebp-package-duration">⏱ {{$p->duration_value}} {{['days'=>'দিন','months'=>'মাস','years'=>'বছর'][$p->duration_unit]??'দিন'}} Access</div><div class="ebp-package-prices"><span>৳{{number_format($p->main_price,0)}}</span><b>৳{{number_format((!$p->offer_ends_at || \Carbon\Carbon::parse($p->offer_ends_at)->endOfDay()->isFuture()) ? $p->price : $p->main_price,0)}}</b></div>
@if((float)$p->discount_amount>0 && (empty($p->offer_ends_at) || \Carbon\Carbon::parse($p->offer_ends_at)->endOfDay()->isFuture()))<div class="ebp-package-save">সাশ্রয় ৳{{number_format($p->discount_amount,0)}} @if($p->offer_ends_at) • অফার শেষ: {{\Carbon\Carbon::parse($p->offer_ends_at)->format('d/m/Y')}} @endif</div>@endif
<div class="ebp-package-bottom">
@auth('student')
<a class="ebp-buy-button" href="{{route('lebd.ebook.referral.preview',['kind'=>$kind,'category'=>$cat->id,'package'=>$p->id])}}"><i class="fa fa-shopping-bag"></i> প্যাকেজ কিনুন →</a>
@else<a class="ebp-buy-button" href="{{ url('/students/login') }}">প্যাকেজ কিনতে Student Login করুন →</a>@endauth
</div></article>
@empty<div class="ebp-no-packages">এই বইয়ের জন্য এখনো কোনো Premium Package প্রকাশ করা হয়নি।</div>@endforelse
</div></section></main>

<link rel="stylesheet" href="{{asset('web/css/lebd-ebook-pay-button-v34.css')}}">
@endsection
