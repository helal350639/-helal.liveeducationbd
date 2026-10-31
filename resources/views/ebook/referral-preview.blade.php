@extends('homepage')
@section('title','E-Book Payment Preview')
@section('content')
<style>
.lebd-eb54{width:min(calc(100% - 24px),570px);margin:14px auto 62px;color:#092b68;font-family:inherit}
.lebd-eb54 *{box-sizing:border-box}
.lebd-eb54 .shell{background:#fff;border:1px solid #cfe2fc;border-radius:17px;padding:17px;box-shadow:0 5px 24px #092b6810}
.lebd-eb54 .back{color:#116cef;text-decoration:none;font-size:13px;font-weight:700;display:inline-block;margin-bottom:13px}
.lebd-eb54 .heading{display:flex;gap:12px;align-items:center;margin-bottom:16px}
.lebd-eb54 .head-icon{width:40px;height:40px;border-radius:50%;display:grid;place-items:center;background:#8d3bd0;color:#fff;font-size:21px}
.lebd-eb54 h1{margin:0;font-size:21px;line-height:1.3;font-weight:800}
.lebd-eb54 h2{font-size:18px;margin:0 0 10px;font-weight:800}
.lebd-eb54 .subtitle{font-size:11px;color:#657ea5;margin-top:4px}
.lebd-eb54 .book{background:#f0f6ff;border:1px solid #c8dffb;padding:12px;border-radius:10px;display:flex;gap:12px;align-items:center;margin-bottom:13px}
.lebd-eb54 .cover{width:90px;height:92px;flex:none;object-fit:contain;border-radius:8px;background:#fff;color:#1175d5;display:flex;justify-content:center;align-items:center;font-size:31px}
.lebd-eb54 .book-title{font-size:15px;font-weight:800;line-height:1.4;margin-bottom:6px}
.lebd-eb54 .book-meta{font-size:12px;margin:3px 0;color:#173e70}
.lebd-eb54 .summary{border:1px solid #d5e4fa;border-radius:11px;padding:13px;margin-bottom:12px}
.lebd-eb54 .line{display:flex;justify-content:space-between;gap:12px;padding:10px 2px;border-bottom:1px solid #e0eaf6;font-size:12px}
.lebd-eb54 .line b{text-align:right}
.lebd-eb54 .line:last-child{border:0}
.lebd-eb54 .remaining{padding:11px;border-radius:7px;background:#e5f8ee;color:#057242;font-size:13px;font-weight:800;margin-top:6px}
.lebd-eb54 .buttons{display:grid;gap:8px}
.lebd-eb54 .submit,.lebd-eb54 .cancel{display:block;width:100%;border-radius:8px;text-align:center;padding:12px;font-size:13px;font-weight:800}
.lebd-eb54 .submit{border:0;background:linear-gradient(95deg,#075bc9,#099bee);color:#fff;cursor:pointer}
.lebd-eb54 .submit:disabled{opacity:.6;cursor:not-allowed}
.lebd-eb54 .cancel{border:1px solid #a8cefb;background:#fff;color:#1669d6;text-decoration:none}
.lebd-eb54 .agree{font-size:11px;display:flex;gap:8px;align-items:flex-start;margin:10px 0}
.lebd-eb54 .agree input{margin-top:2px}
.lebd-eb54 .alert{border-radius:8px;padding:9px;background:#fff5e9;color:#8b4618;font-size:12px;margin:8px 0}
@media(max-width:480px){.lebd-eb54{width:calc(100% - 16px);margin-top:8px}.lebd-eb54 .shell{padding:12px}.lebd-eb54 h1{font-size:19px}.lebd-eb54 .cover{width:73px;height:83px}.lebd-eb54 .book-title{font-size:13px}}
</style>
<main class="lebd-eb54">
<section class="shell">
<a class="back" href="{{route('lebd.ebook.referral.preview',['kind'=>$kind,'category'=>$category,'package'=>$package])}}">← প্যাকেজে ফিরে যান</a>
<div class="heading"><span class="head-icon">🛒</span><div><h1>Payment Preview</h1><div class="subtitle">{{ $method==='referral' ? 'Referral Balance থেকে টাকা কাটার আগে যাচাই করুন।' : 'SSLCommerz Gateway-তে যাওয়ার আগে তথ্য যাচাই করুন।' }}</div></div></div>
<div class="book">
@if(!empty($cat->front_cover))
<img class="cover" src="{{asset($cat->front_cover)}}" alt="{{$cat->name}}">
@else
<div class="cover">📚</div>
@endif
<div><div class="book-title">{{$cat->name}} — {{$p->name}}</div><div class="book-meta">Package: {{$p->name}}</div><div class="book-meta">মেয়াদ: {{$p->duration_value}} {{['days'=>'দিন','months'=>'মাস','years'=>'বছর'][$p->duration_unit]??'দিন'}}</div><div class="book-meta">Payment: {{ $method==='referral'?'Referral Balance':'SSLCommerz' }}</div></div>
</div>
<div class="summary">
<h2>পেমেন্টের সারসংক্ষেপ</h2>
<div class="line"><span>ব্যাচের মোট মূল্য</span><b>৳{{number_format($amount/100,2)}}</b></div>
@if($method==='referral')
<div class="line"><span>আপনার Available Referral Balance</span><b>৳{{number_format($available/100,2)}}</b></div>
<div class="line"><span>Referral Balance থেকে কাটা হবে</span><b style="color:#bc2222">৳{{number_format($amount/100,2)}}</b></div>
<div class="remaining">পেমেন্টের পর Available Balance <span style="float:right">৳{{number_format(max(0,$available-$amount)/100,2)}}</span></div>
@else
<div class="line"><span>পেমেন্ট পদ্ধতি</span><b>SSLCommerz</b></div>
<div class="remaining">Gateway-তে পেমেন্ট করতে হবে <span style="float:right">৳{{number_format($amount/100,2)}}</span></div>
@endif
</div>
@if($method==='referral')
@if($available<$amount)<div class="alert">পর্যাপ্ত Referral Balance নেই। ফিরে গিয়ে SSLCommerz নির্বাচন করুন।</div>@endif
<form method="POST" action="{{route('lebd.ebook.referral.confirm')}}" class="eb54-form">
@csrf
<input type="hidden" name="token" value="{{$token}}">
<label class="agree"><input type="checkbox" name="agree" value="1" required> Terms & Conditions, Privacy Policy এবং Refund Policy-তে সম্মত।</label>
<div class="buttons"><button class="submit" type="submit" @if($available<$amount) disabled @endif>✓ কনফার্ম পেমেন্ট</button><a class="cancel" href="{{route('lebd.ebook.referral.preview',['kind'=>$kind,'category'=>$category,'package'=>$package])}}">← বাতিল করুন</a></div>
</form>
@else
<form method="POST" action="{{route('lebd.ebook.payment.gateway-confirm',['kind'=>$kind,'category'=>$category,'package'=>$package])}}" class="eb54-form">
@csrf
<label class="agree"><input type="checkbox" name="agree" value="1" required> Terms & Conditions, Privacy Policy এবং Refund Policy-তে সম্মত।</label>
<div class="buttons"><button class="submit" type="submit">Payment Now</button></div>
</form>
@endif
</section></main>
<script>
(function(){document.querySelectorAll('.lebd-eb54 .eb54-form').forEach(function(form){form.addEventListener('submit',function(e){if(!form.checkValidity()){return;}var btn=form.querySelector('.submit');if(btn){btn.disabled=true;btn.textContent='Processing...';}});});})();
</script>
@endsection
