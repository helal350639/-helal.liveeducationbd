@extends('homepage')
@section('title', $p->name.' — E-Book Referral')
@section('content')
<style>
.ebref9{max-width:760px;margin:20px auto 55px;padding:0 15px;color:#0c2e61}
.ebref9 .card{overflow:hidden;border:1px solid #d4e3f8;border-radius:15px;background:#fff;box-shadow:0 8px 24px #174a7f13}
.ebref9 .head{background:linear-gradient(120deg,#12316d,#007bad);color:#fff;padding:16px 20px}
.ebref9 h1{font-size:22px;margin:0;line-height:1.45}.ebref9 .body{padding:19px}
.ebref9 .tag{display:inline-block;padding:4px 10px;border-radius:20px;color:#0d5f39;background:#eaf9f0;font-size:12px}
.ebref9 .line{display:flex;justify-content:space-between;gap:16px;align-items:center;padding:11px 0;border-bottom:1px solid #e0eaf8}
.ebref9 .line strong{white-space:nowrap}.ebref9 .total{background:#e8f9ed;color:#06703d;padding:12px;border-radius:9px;margin:15px 0;font-weight:700}
.ebref9 .btn{display:block;text-align:center;padding:11px 15px;border-radius:8px;text-decoration:none;font-weight:700;margin:9px 0;border:1px solid #a6c5f0;color:#0757bf;background:#f2f7ff}
.ebref9 .notice{background:#fff8e9;border:1px solid #f1d9a9;padding:12px;border-radius:8px;color:#79520b;line-height:1.65;font-size:13px}
@media(max-width:640px){.ebref9{margin:10px auto 25px;padding:0 10px}.ebref9 h1{font-size:17px}.ebref9 .head{padding:12px 14px}.ebref9 .body{padding:13px}.ebref9 .line{font-size:13px;padding:9px 0}.ebref9 .btn{padding:10px}}
</style>
<main class="ebref9"><section class="card"><header class="head"><div style="font-size:11px;font-weight:600">E-BOOK — REFERRAL PACKAGE</div><h1>{{ $p->name }}</h1></header>
<div class="body"><span class="tag">শুধু এই প্যাকেজ</span>
<div class="line"><span>Referred By (Student)</span><strong>{{ $owner->name }}</strong></div>
<div class="line"><span>অন্তর্ভুক্ত একটি E-Book</span><strong>{{ $cat->name }}</strong></div>
<div class="line"><span>Package Price</span><strong>৳{{ number_format($base,2) }}</strong></div>
<div class="line"><span>Referral Discount</span><strong>− ৳{{ number_format($discount,2) }}</strong></div>
@if($fee>0)<div class="line"><span>Extra Fee</span><strong>+ ৳{{ number_format($fee,2) }}</strong></div>@endif
<div class="line total"><span>Referral Payable</span><strong>৳{{ number_format($payable,2) }}</strong></div>
@if($owned)
<p class="notice">এই প্যাকেজে আপনার Active Access রয়েছে। আবার কিনতে হবে না।</p>
@else
<a class="btn" style="background:#0863dc;color:white" href="{{ route('lebd.ebook.referral.preview',['kind'=>$cat->kind,'category'=>$cat->id,'package'=>$p->id,'referral_purchase'=>1]) }}">এই প্যাকেজ Referral Discount-এ কিনুন →</a>
@endif
<a class="btn" href="{{ route('lebd.ebook.referral.package',['package'=>$p->id,'clear_referral'=>1]) }}">আরও প্যাকেজ দেখুন (সাধারণ মূল্য)</a>
</div></section></main>
@endsection
