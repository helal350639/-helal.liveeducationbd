@extends('homepage')
@section('title','প্যাকেজ ও পেমেন্ট সামারি')
@section('content')
<link rel="stylesheet" href="{{asset('web/css/lebd-premium-package-fix114.css')}}">
<div class="lebd114-reviewwrap">
 <a class="lebd114-back" href="{{url()->previous()}}">← প্যাকেজ তালিকায় ফিরে যান</a>
 <div class="lebd114-hero"><span class="lebd114-kicker">CHECKOUT REVIEW</span><h1>প্যাকেজ ও দামের সামারি</h1><p>পেমেন্টের আগে নিচের তথ্যগুলো যাচাই করুন।</p></div>
 @if($errors->any())<div role="alert" style="padding:12px 16px;margin:12px 0;border:1px solid #ffc5c5;background:#fff2f2;color:#a71919;border-radius:9px;font-weight:600">{{ $errors->first() }}</div>@endif
 <div class="lebd114-reviewgrid">
  <section class="lebd114-reviewcard"><h2>প্যাকেজের বিবরণ</h2><dl><dt>প্যাকেজ</dt><dd>{{$package->name}}</dd><dt>ধরন</dt><dd>{{ucwords(str_replace(['-','_'],' ',(string)$package->package_type))}}</dd><dt>মেয়াদ</dt><dd>{{ $package->duration_value??$package->validity_days }} {{ $package->duration_unit??'days' }}</dd></dl><div class="lebd114-description">{{$package->description}}</div>
  @if(count((array)($package->department_categories??[])))<p><b>Department:</b> {{implode(', ',(array)$package->department_categories)}}</p>@endif
  @if(count((array)($package->non_department_categories??[])))<p><b>Non-Department:</b> {{implode(', ',(array)$package->non_department_categories)}}</p>@endif
  </section>
  <section class="lebd114-reviewcard"><h2>Payment Summary</h2><div class="lebd114-summaryrow"><span>মূল দাম</span><strong>৳{{number_format($main,2)}}</strong></div><div class="lebd114-summaryrow"><span>ছাড়</span><strong>− ৳{{number_format($discount,2)}}</strong></div><div class="lebd114-summaryrow lebd114-total"><span>সর্বমোট</span><strong>৳{{number_format($final,2)}}</strong></div>
  @auth('student')
   <form method="POST" action="{{route('premium.payment.checkout',$package)}}">@csrf<button type="submit" class="lebd114-buy">Payment →</button></form>
  @else
   <a href="{{route('students.login')}}" class="lebd114-buy">পেমেন্ট করতে লগইন করুন →</a>
  @endauth
  <p class="lebd114-secure">পেমেন্টের পর সার্ভার থেকে Transaction Verification সফল হলে Premium Access সক্রিয় হবে।</p>
  </section>
 </div>
</div>
@endsection
