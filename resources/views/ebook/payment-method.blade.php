@extends('homepage')
@section('title','E-Book — পেমেন্ট পদ্ধতি')
@section('content')
<style>
.eb50{max-width:750px;width:calc(100% - 24px);margin:16px auto 65px;font-family:inherit;color:#0c2b62}.eb50 *{box-sizing:border-box}.eb50 .card50{background:#fff;border-radius:15px;padding:17px;margin-bottom:14px;box-shadow:0 4px 18px #0b367315;border:1px solid #e0eafa}.eb50 h1{font-size:21px;text-align:center;font-weight:800;margin:0 0 15px}.eb50 .heading{font-size:15px;font-weight:800;margin:0 0 12px}.eb50 .line{display:flex;justify-content:space-between;border-bottom:1px solid #e4ebf6;padding:9px 0;font-size:13px;gap:12px}.eb50 .line:last-child{border:0}.eb50 .line span{color:#526a8b}.eb50 .line b{font-weight:800}.eb50 .total{background:#edf6ff;border-radius:9px;padding:10px;margin-top:4px;font-weight:800;display:flex;justify-content:space-between}.eb50 .choice{display:flex;gap:11px;align-items:center;border:1px solid #a6d4ff;border-radius:11px;background:#eff8ff;padding:15px 12px;margin:10px 0;cursor:pointer}.eb50 .choice:has(input:checked){border:2px solid #1265eb;background:#e8f3ff;padding:14px 11px}.eb50 .choice input{accent-color:#087af3;width:17px;height:17px;flex-shrink:0}.eb50 .choice-content{flex:1;min-width:0}.eb50 .choice strong{display:block;font-size:13px}.eb50 .choice small{display:block;font-size:10px;color:#58749b}.eb50 .wallet{background:white;border-radius:9px;padding:7px 10px;text-align:right;color:#078454;font-size:14px;white-space:nowrap}.eb50 .wallet small{font-size:9px}.eb50 .tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:7px}.eb50 .tags em{font-style:normal;background:white;border:1px solid #d4e1f3;padding:3px 6px;border-radius:4px;font-size:9px;color:#3f618a}.eb50 .notice{background:#1b145e;color:#fff;padding:14px;border-radius:10px;font-size:11px;line-height:1.7;margin:14px 0}.eb50 .check{font-size:11px;display:flex;gap:8px;align-items:flex-start;margin:12px 0}.eb50 .check input{margin-top:2px;accent-color:#077be9}.eb50 .next{display:block;width:100%;padding:13px 10px;border:0;background:linear-gradient(100deg,#076ad7,#009ce9);color:#fff;font-weight:800;border-radius:8px;cursor:pointer}.eb50 .back{font-size:12px;text-decoration:none;color:#146bd4;display:inline-block;margin-bottom:12px}@media(max-width:520px){.eb50 .card50{padding:12px}.eb50 h1{font-size:18px}.eb50 .wallet{font-size:12px}.eb50 .choice{padding:12px 9px}.eb50 .choice:has(input:checked){padding:11px 8px}}
</style>
<main class="eb50">
<a class="back" href="{{route('student.ebook.packages',['kind'=>$kind,'category'=>$category])}}">← প্যাকেজে ফিরুন</a>
<div class="card50"><h1>পেমেন্টের বিবরণ</h1>
<div class="line"><span>বই</span><b>{{$cat->name}}</b></div>
<div class="line"><span>প্যাকেজ</span><b>{{$p->name}}</b></div>
<div class="line"><span>মেয়াদ</span><b>{{$p->duration_value}} {{['days'=>'দিন','months'=>'মাস','years'=>'বছর'][$p->duration_unit]??'দিন'}}</b></div>
<div class="line"><span>কোর্সের মূল্য</span><b>৳{{number_format((float)$p->main_price,2)}}</b></div>
<div class="line"><span>অন্যান্য ফি (+)</span><b>৳0.00</b></div>
<div class="line"><span>ডিসকাউন্ট (-)</span><b>− ৳{{number_format(max(0,(float)$p->main_price-$amount/100),2)}}</b></div>
<div class="total"><span>মোট =</span><b>৳{{number_format($amount/100,2)}}</b></div></div>
<form action="{{route('lebd.ebook.payment.review')}}" method="POST" class="card50" id="eb51-method-form">@csrf
<input type="hidden" name="token" value="{{$token}}">
<h2 class="heading" style="text-align:center">আমাদের পেমেন্ট সিস্টেমে অন্তর্ভুক্ত আছে</h2>
<label class="choice"><input type="radio" name="method" value="referral" @checked($available >= $amount)><span class="choice-content"><strong>Use Your Referral Balance</strong><small>Available Balance দিয়ে প্যাকেজ কিনুন</small></span><b class="wallet">৳{{number_format($available/100,2)}}<small>Available Balance</small></b></label>
<label class="choice"><input type="radio" name="method" value="ssl" id="eb51-ssl-choice" @checked($available < $amount)><span class="choice-content"><strong>Online Payment Gateway — SSLCommerz</strong><span class="tags"><em>Card</em><em>Mobile Banking</em><em>Internet Banking</em><em>Wallet</em></span></span></label>
<div class="notice"><strong>⚠ সতর্কতা</strong><div>পেমেন্ট গেটওয়েতে প্রবেশ করে আপনার পেমেন্ট সম্পন্ন হওয়ার পর অনুগ্রহ করে নিশ্চিতকরণের জন্য ৫–৬ সেকেন্ড অপেক্ষা করুন। নিশ্চিতকরণ না আসা পর্যন্ত পেজটি বন্ধ করবেন না বা ত্যাগ করবেন না।</div></div>
@if($available < $amount)<p style="font-size:11px;color:#9b5116">Referral Balance পর্যাপ্ত নয়। SSLCommerz ব্যবহার করতে পারবেন।</p>@endif
<label class="check"><input type="checkbox" required name="agree" value="1"> I agree to the Terms & Conditions, Privacy Policy and Refund Policy.</label>
<button class="next" type="submit">Payment Preview →</button>
</form></main>
<script>
// STEP-52: Payment Preview opens ONLY after clicking the submit button.
// Selecting SSLCommerz or checking Terms must never submit the form.
(function () {
  'use strict';
  var form = document.getElementById('eb51-method-form');
  if (!form) return;
  form.addEventListener('submit', function () {
    var button = form.querySelector('button[type="submit"]');
    if (button) { button.disabled = true; button.textContent = 'Opening Payment Preview...'; }
  });
})();
</script>
@endsection
