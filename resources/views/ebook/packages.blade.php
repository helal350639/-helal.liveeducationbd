@extends('homepage')
@section('title',$cat->name.' | Premium Packages')
@section('content')
<link rel="stylesheet" href="{{asset('web/css/lebd-ebook-student-v22.css')}}">
<link rel="stylesheet" href="{{asset('web/css/lebd-ebook-premium-v25.css')}}">
@php
 $lebdStudentId=(int)\Illuminate\Support\Facades\Auth::guard('student')->id();
 $lebdEntitlement=$lebdStudentId ? \Illuminate\Support\Facades\DB::table('lebd_ebook_entitlements')
  ->where('user_id',$lebdStudentId)->where('category_id',$cat->id)->where('status','Active')->where('expires_at','>',now())->orderByDesc('expires_at')->first() : null;
@endphp
<style>.ebp-v58-owned{background:#eafaf1;border:1px solid #b1e5c6;color:#046b3d;padding:15px;border-radius:12px;margin:15px 0;font-size:14px;font-weight:700}.ebp-v58-owned a{display:inline-block;background:#047a49;color:#fff!important;padding:10px 14px;border-radius:9px;text-decoration:none;margin-top:10px}</style>
<main class="ebp-student">
@if(session('error'))<div role="alert" style="color:#b42318;padding:14px">{{session('error')}}</div>@endif
<a href="{{route('student.ebook.chapters',['kind'=>$kind,'category'=>$cat->id])}}" class="ebp-back">← {{$cat->name}} সূচিপত্র</a>
@if($lebdEntitlement)
<section class="ebp-v58-owned">✅ এই প্যাকেজ কিনেছেন। আপনার Premium E-Book Access সক্রিয়।<br>
<small>মেয়াদ: {{\Carbon\Carbon::parse($lebdEntitlement->expires_at)->format('d/m/Y')}}</small><br>
<a href="{{route('student.ebook.full-book',['kind'=>$kind,'category'=>$cat->id])}}">📖 সম্পূর্ণ বই পড়ুন →</a></section>
@else
<section class="ebp-notice"><div class="ebp-lock">🔒</div><h1>Premium Access প্রয়োজন</h1><p>এই বইয়ের সম্পূর্ণ অধ্যায় এবং Full Book পড়তে একটি Premium Package কিনুন।</p></section>
@endif
<section class="ebp-catalog"><h2><i class="fa fa-crown"></i> {{$cat->name}} — Premium Packages</h2><p>দাম, অফার ও মেয়াদ দেখে Package নির্বাচন করুন।</p><div class="ebp-card-grid">
@forelse($packages as $p)
<article class="ebp-package-card"><div class="ebp-package-tag">PREMIUM E-BOOK</div><h3>{{$p->name}}</h3><p>{{$p->description}}</p><div class="ebp-package-duration">⏱ {{$p->duration_value}} {{['days'=>'দিন','months'=>'মাস','years'=>'বছর'][$p->duration_unit]??'দিন'}} Access</div>
<div class="ebp-package-prices"><span>৳{{number_format($p->main_price,0)}}</span><b>৳{{number_format($p->price,0)}}</b></div>
@if((float)$p->discount_amount>0 && (empty($p->offer_ends_at) || \Carbon\Carbon::parse($p->offer_ends_at)->endOfDay()->isFuture()))<div class="ebp-package-save">সাশ্রয় ৳{{number_format($p->discount_amount,0)}} @if($p->offer_ends_at) • অফার শেষ: {{\Carbon\Carbon::parse($p->offer_ends_at)->format('d/m/Y')}} @endif</div>@endif
<div class="ebp-package-bottom">
@if($lebdEntitlement && (int)$lebdEntitlement->package_id===(int)$p->id)
<div class="ebp-v58-owned">✓ এই প্যাকেজ কিনেছেন</div><a class="ebp-buy-button" href="{{route('student.ebook.full-book',['kind'=>$kind,'category'=>$cat->id])}}">সম্পূর্ণ বই পড়ুন →</a>
@elseif($lebdEntitlement)
<div class="ebp-v58-owned">✓ এই বইয়ের Premium Access চালু আছে</div><a class="ebp-buy-button" href="{{route('student.ebook.full-book',['kind'=>$kind,'category'=>$cat->id])}}">বই পড়ুন →</a>
@else
@auth('student')<form action="{{route('lebd.ebook.payment.start',['kind'=>$kind,'category'=>$cat->id,'package'=>$p->id])}}" method="POST">@csrf<button type="submit" class="ebp-buy-button">প্যাকেজ কিনুন →</button></form>
@else<a class="ebp-buy-button" href="{{url('/students/login')}}">প্যাকেজ কিনতে Student Login করুন →</a>@endauth
@endif
</div></article>
@empty<div class="ebp-no-packages">এই বইয়ের জন্য এখনো কোনো Premium Package প্রকাশ করা হয়নি।</div>@endforelse
</div></section></main>
<link rel="stylesheet" href="{{asset('web/css/lebd-ebook-pay-button-v34.css')}}">
@endsection
