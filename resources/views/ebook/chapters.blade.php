@extends('homepage')
@section('title', $cat->name.' | E-Book')
@section('content')
<link rel="stylesheet" href="{{asset('web/css/lebd-ebook-student-v22.css')}}">
@php
$lebdStudentId=(int)\Illuminate\Support\Facades\Auth::guard('student')->id();
$lebdHasActiveEntitlement=$lebdStudentId>0 && \Illuminate\Support\Facades\DB::table('lebd_ebook_entitlements')
 ->where('user_id',$lebdStudentId)->where('category_id',$cat->id)->where('status','Active')->where('expires_at','>',now())->exists();
$paid=$paid||$lebdHasActiveEntitlement;
@endphp
<style>.leb-v58-purchased{padding:12px 15px;border:1px solid #b3e5c7;background:#eafaf1;color:#07683e;border-radius:10px;font-size:13px;font-weight:800;margin:12px 0}</style>
<main class="leb-v23-library">
<nav class="leb-v23-breadcrumb"><a href="{{url('/')}}">হোম</a><span>›</span><a href="{{route('student.ebook.categories',['kind'=>$kind])}}">{{ $kind==='department'?'Department E-Books':'General E-Books' }}</a><span>›</span><b>{{$cat->name}}</b></nav>
<section class="leb-v23-detail-hero">
<div class="leb-v23-detail-cover" aria-hidden="true"><div class="leb-v23-cover-placeholder"><i class="fa {{$cat->icon ?: 'fa-book'}}"></i></div></div>
<div class="leb-v23-detail-text"><span class="leb-v23-eyebrow"><i class="fa-solid fa-book-open-reader"></i> LIVE EDUCATION BD • E-BOOK</span><h1>{{$cat->name}}</h1><p>সম্পূর্ণ বইয়ের সূচিপত্র দেখুন, অধ্যায় নির্বাচন করুন এবং প্রিমিয়াম কেনার আগে ফ্রি প্রিভিউ পড়ুন।</p>
<div class="leb-v23-detail-metrics"><span><b>{{$chapters->count()}}</b> অধ্যায়</span><span><i class="fa-solid fa-file-lines"></i> A4 • ২ কলাম</span><span><i class="fa-solid fa-check-circle"></i> উত্তর ও ব্যাখ্যা</span></div>
@if(session('ebook_notice'))<div class="leb-v23-notice">{{session('ebook_notice')}}</div>@endif
@if($paid)<div class="leb-v58-purchased">✅ এই প্যাকেজ কিনেছেন — Premium Access সক্রিয়</div>@endif
<div class="leb-v23-detail-actions">
@if($paid)
<a class="leb-v23-primary" href="{{route('student.ebook.full-book',['kind'=>$kind,'category'=>$cat->id])}}"><i class="fa-solid fa-book-open"></i> সম্পূর্ণ বই পড়ুন</a><span class="leb-v23-access">✓ Premium Active</span>
@else
@if($chapters->isNotEmpty())<a class="leb-v23-primary" href="{{route('student.ebook.read',['kind'=>$kind,'category'=>$cat->id,'chapter'=>$chapters->first()->id])}}"><i class="fa-solid fa-eye"></i> ফ্রি প্রিভিউ পড়ুন</a>@endif
<a class="leb-v23-secondary" href="{{route('student.ebook.packages',['kind'=>$kind,'category'=>$cat->id])}}"><i class="fa-solid fa-crown"></i> Premium Package</a>
@endif
</div></div></section>
<section class="leb-v23-toc" id="toc"><div class="leb-v23-section-top"><div><small>BOOK CONTENTS</small><h2>সূচিপত্র</h2><p>প্রতিটি অধ্যায় আলাদাভাবে পড়ার সুবিধা</p></div><span class="leb-v23-section-count">মোট {{$chapters->count()}} অধ্যায়</span></div>
@forelse($chapters as $ch)
<div class="leb-v23-chapter"><span class="leb-v23-chapter-num">{{str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT)}}</span><div class="leb-v23-chapter-main"><small>অধ্যায়-{{str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT)}}</small><h3>{{$ch->title}}</h3></div>
@if($paid||$loop->first)<a class="leb-v23-chapter-link" href="{{route('student.ebook.read',['kind'=>$kind,'category'=>$cat->id,'chapter'=>$ch->id])}}">{{$paid?'অধ্যায় পড়ুন':'ফ্রি পড়ুন'}} <i class="fa-solid fa-arrow-right"></i></a>
@else<a class="leb-v23-chapter-lock" href="{{route('student.ebook.packages',['kind'=>$kind,'category'=>$cat->id])}}"><i class="fa-solid fa-lock"></i> Premium</a>@endif
</div>
@empty<div class="leb-v23-empty">এখনো অধ্যায় প্রকাশ করা হয়নি।</div>@endforelse
</section>
@unless($paid)<section class="leb-v23-premium" id="leb-v23-packages"><div class="leb-v23-premium-header"><span><i class="fa-solid fa-crown"></i> PREMIUM ACCESS</span><h2>সম্পূর্ণ বই পড়ার জন্য Package</h2><p>ফ্রি প্রিভিউয়ের বাইরে বাকি অধ্যায় পড়তে সক্রিয় Subscription প্রয়োজন।</p></div>
@forelse($packages as $package)<div class="leb-v23-plan"><div><strong>{{$package->name}}</strong><p>{{$package->description}}</p><small>{{$package->duration_days}} দিনের Access</small></div><div class="leb-v23-plan-end"><b>৳{{number_format($package->price,0)}}</b><a class="leb-v23-secondary" href="{{route('student.ebook.packages',['kind'=>$kind,'category'=>$cat->id])}}">প্যাকেজ দেখুন →</a></div></div>
@empty<div class="leb-v23-plan-empty">এই বইয়ের কোনো Premium Package এখনো প্রকাশ করা হয়নি।</div>@endforelse
<p class="leb-v23-package-caution">শুধুমাত্র যাচাইকৃত পেমেন্টে Premium Access সক্রিয় হবে।</p></section>@endunless
</main>
@endsection
