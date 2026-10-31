<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Exam Section — Package Payment Method</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f0f5fc;color:#092b66;font-family:Arial,'Noto Sans Bengali',sans-serif}
.mcq-pay{max-width:760px;margin:15px auto 45px;padding:0 12px}.mcq-pay a{color:#1169d4;text-decoration:none}.mcq-pay .back{font-size:12px;font-weight:700;display:inline-block;margin:9px 0 12px}
.mcq-pay .card{border:1px solid #d3e3fa;border-radius:13px;background:#fff;box-shadow:0 3px 18px #0d4c8712;margin-bottom:14px;overflow:hidden}
.mcq-pay .card-title{background:#eff5fd;font-size:15px;font-weight:800;padding:11px 13px}.mcq-pay .price-row{display:flex;gap:12px;justify-content:space-between;align-items:center;padding:11px 13px;border-top:1px solid #dfebf8;font-size:12px}.mcq-pay .price-row strong{text-align:right;white-space:nowrap}.mcq-pay .price-row.total{font-size:15px;font-weight:800;background:#ecf6ff}.mcq-pay .details{padding:13px;font-size:12px;line-height:1.65}.mcq-pay .details b{font-size:17px}.mcq-pay .scope{margin-top:8px;color:#506b90}
.mcq-pay .heading{text-align:center;font-size:16px;font-weight:800;padding:14px 10px 10px;margin:0}
.mcq-pay .methods{padding:0 13px 12px}.mcq-pay .option{display:flex;align-items:center;gap:10px;padding:11px;border:1px solid #a5cfff;border-radius:10px;background:#edf7ff;margin-bottom:9px;cursor:pointer;min-height:69px}.mcq-pay .option.is-selected{border:2px solid #2061f6;padding:10px;background:#e8f3ff}.mcq-pay .option input{width:16px;height:16px;accent-color:#0670dc;flex:none;margin:0}.mcq-pay .option-text{flex:1;min-width:0;font-size:12px}.mcq-pay .option-text b{font-size:12px}.mcq-pay .option-text small{display:block;font-size:10px;color:#567b9b;margin-top:4px}.mcq-pay .balance{padding:9px 11px;background:#fff;border-radius:9px;text-align:center;white-space:nowrap;color:#048b5b;font-size:14px;font-weight:800}.mcq-pay .balance small{font-size:9px;color:#4c7495;display:block}.mcq-pay .pills{display:flex;gap:5px;flex-wrap:wrap;margin-top:7px}.mcq-pay .pills span{font-size:9px;background:#fff;border:1px solid #d5e1f2;padding:5px 6px;border-radius:4px}.mcq-pay .insufficient{font-size:11px;color:#ad5720;padding:0 5px 8px}
.mcq-pay .notice{background:#1c156b;color:#fff;padding:14px 16px;border-radius:10px;font-size:11px;line-height:1.65;margin:12px 0}.mcq-pay .notice strong{font-size:13px}.mcq-pay .agree{display:flex;align-items:flex-start;gap:9px;padding:12px;background:#fff;border:1px solid #d6e5f7;border-radius:9px;margin:10px 0;font-size:11px}.mcq-pay .agree input{margin:1px 0 0;flex:none;accent-color:#086fdc}.mcq-pay .preview-btn{border:0;width:100%;border-radius:9px;background:linear-gradient(100deg,#168454,#0b945f);color:#fff;font-size:14px;font-weight:800;padding:13px;cursor:pointer}.mcq-pay .error{padding:12px;border-radius:9px;background:#fff0f0;color:#ad2424;margin:10px 0}.mcq-pay .subtle{font-size:11px;color:#667e9d}
@media(max-width:600px){.mcq-pay{padding:0 8px;margin-top:8px}.mcq-pay .option{padding:10px 8px}.mcq-pay .card-title{font-size:13px}.mcq-pay .heading{font-size:14px}.mcq-pay .balance{padding:7px;font-size:12px}}
</style>
</head>
<body>
<main class="mcq-pay">
<a class="back" href="{{ route('student.exam-section.packages') }}">← প্যাকেজ তালিকায় ফিরুন</a>
@if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
<section class="card" aria-label="পেমেন্টের বিবরণ">
 <div class="card-title">পেমেন্টের বিবরণ</div>
 <div class="details"><b>{{ $plan->name }}</b><div class="subtle">মেয়াদ: {{ $plan->duration }} {{ $plan->duration_unit }}</div>
 @if(!empty($plan->description))<div>{{ $plan->description }}</div>@endif
 @if($scopes->isNotEmpty())<div class="scope"><strong>অন্তর্ভুক্ত বিভাগ:</strong> @foreach($scopes as $scope){{ $scope->category_name }}@if($scope->subcategory_name) → {{ $scope->subcategory_name }}@endif @unless($loop->last), @endunless @endforeach</div>@endif
 </div>
 <div class="price-row"><span>কোর্সের মূল্য</span><strong>৳ {{ number_format((float)$plan->main_price,2) }}</strong></div>
 <div class="price-row"><span>অন্যান্য ফি (+)</span><strong>৳ 0.00</strong></div>
 <div class="price-row"><span>ডিসকাউন্ট (-)</span><strong style="color:#c93434">− ৳ {{ number_format((float)$plan->discount_amount,2) }}</strong></div>
 <div class="price-row total"><span>মোট =</span><strong>৳ {{ number_format((float)$plan->final_price,2) }}</strong></div>
</section>
{{-- Preview must POST checkout.review (token creator), never checkout (token consumer). --}}
<form method="POST" action="{{ route('student.exam-section.package.checkout.review',$plan->id) }}" id="mcq-package-review-form">
@csrf
<section class="card"><h2 class="heading">আমাদের পেমেন্ট সিস্টেমে অন্তর্ভুক্ত আছে</h2>
<div class="methods">
 <label class="option" id="mcq-wallet-option"><input type="radio" name="method" value="wallet" {{ old('method')==='wallet'?'checked':'' }} required><span class="option-text"><b>Use Your Referral Balance</b><small>Available Balance দিয়ে প্যাকেজ কিনুন</small></span><span class="balance">৳{{ number_format((float)$available,2) }}<small>Available Balance</small></span></label>
 @if(round((float)$available*100)<round((float)$plan->final_price*100))<div class="insufficient">Referral Balance দিয়ে পুরো মূল্য পরিশোধের জন্য পর্যাপ্ত টাকা নেই। SSLCommerz নির্বাচন করতে পারেন।</div>@endif
 <label class="option" id="mcq-ssl-option"><input type="radio" name="method" value="sslcommerz" {{ old('method','sslcommerz')==='sslcommerz'?'checked':'' }} required><span class="option-text"><b>Online Payment Gateway — SSLCommerz</b><span class="pills"><span>Card</span><span>Mobile Banking</span><span>Internet Banking</span><span>Wallet</span></span></span></label>
</div></section>
<div class="notice"><strong>⚠ সতর্কতা</strong><div>পেমেন্ট গেটওয়েতে প্রবেশ করে আপনার পেমেন্ট সম্পন্ন হওয়ার পর অনুগ্রহ করে নিশ্চিতকরণের জন্য ৫–৬ সেকেন্ড অপেক্ষা করুন। নিশ্চিতকরণ না আসা পর্যন্ত পেজটি বন্ধ করবেন না বা ত্যাগ করবেন না। Referral Balance নির্বাচন করলে পর্যাপ্ত Balance থেকে মূল্য কাটা হবে।</div></div>
<label class="agree"><input type="checkbox" name="terms" value="1" required {{ old('terms')?'checked':'' }}><span>I agree to the <b>Terms &amp; Conditions, Privacy Policy and Refund Policy.</b></span></label>
<button class="preview-btn" type="submit" id="mcq-preview-btn">Payment Preview →</button>
</form>
</main>
<script>
(function(){'use strict';var options=document.querySelectorAll('.mcq-pay .option');function update(){options.forEach(function(el){var input=el.querySelector('input[type=radio]');el.classList.toggle('is-selected',!!input&&input.checked);});}document.querySelectorAll('.mcq-pay input[name=method]').forEach(function(input){input.addEventListener('change',update);});update();})();
</script>
</body>
</html>
