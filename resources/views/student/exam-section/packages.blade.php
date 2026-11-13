@extends('homepage')
@section('title', 'Premium Exam Packages | Live Education BD')
@section('content')
<style>
.lebd-exam-catalog{max-width:980px;width:calc(100% - 28px);margin:20px auto 60px;color:#102f62}
.lebd-exam-catalog-top{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:11px 14px;margin-bottom:17px;background:#fff;border:1px solid #dce7f7;border-radius:14px}
.lebd-exam-catalog-top a{font-size:12px;font-weight:750;text-decoration:none;color:#1658b6}
.lebd-exam-catalog-heading{display:flex;align-items:center;gap:12px;margin:12px 0 15px}
.lebd-exam-catalog-heading .icon{width:48px;height:48px;display:grid;place-items:center;border-radius:14px;background:#fff1d4;color:#b87900;font-size:24px}
.lebd-exam-catalog-heading h1{margin:0;font-size:25px;font-weight:900;line-height:1.25}
.lebd-exam-catalog-heading p{margin:4px 0 0;color:#647b9b;font-size:11px}
.lebd-exam-catalog-notice{padding:11px 14px;border-radius:10px;background:#fff1c5;color:#77591d;font-size:12px;line-height:1.6;margin:12px 0}
.lebd-exam-catalog-filters{display:flex;gap:7px;flex-wrap:wrap;margin:14px 0}
.lebd-exam-catalog-filters button{border:1px solid #d5e1f3;background:#fff;color:#234b83;border-radius:22px;padding:8px 13px;font-size:11px;font-weight:750;cursor:pointer}
.lebd-exam-catalog-filters button.active{background:#2524c9;border-color:#2524c9;color:#fff}
.lebd-exam-catalog-list{display:flex;flex-direction:column;gap:10px}
.lebd-exam-package{display:grid;grid-template-columns:minmax(0,1fr) 145px;gap:13px;align-items:stretch;padding:13px;background:#fff;border:1px solid #dce7f5;border-radius:16px;box-shadow:0 5px 17px rgba(20,55,100,.055)}
.lebd-exam-package[hidden]{display:none!important}
.lebd-exam-package-main{display:flex;gap:11px;min-width:0}
.lebd-exam-package-icon{width:38px;height:38px;flex:0 0 38px;display:grid;place-items:center;border-radius:11px;background:#edf3ff;color:#236bd4;font-size:18px}
.lebd-exam-package-info{min-width:0}
.lebd-exam-package-info h2{font-size:16px;font-weight:850;margin:0 0 5px;line-height:1.3}
.lebd-exam-package-duration{display:inline-block;background:#f0f3fb;color:#617596;padding:3px 8px;border-radius:20px;font-size:10px;margin-bottom:7px}
.lebd-exam-package-tags{display:flex;gap:5px;flex-wrap:wrap;margin:2px 0 7px}
.lebd-exam-package-tags span{background:#edf4ff;color:#315f9a;padding:4px 7px;border-radius:5px;font-size:9px}
.lebd-exam-package-info p{font-size:11px;color:#5d7392;line-height:1.5;margin:3px 0;overflow-wrap:anywhere}
.lebd-exam-package-side{border-left:1px solid #e0e9f6;padding:5px 0 5px 12px;display:flex;flex-direction:column;justify-content:center;gap:7px;min-width:0}
.lebd-exam-package-price{font-size:22px;font-weight:900;white-space:nowrap}
.lebd-exam-package-old{font-size:10px;color:#9aa8ba;text-decoration:line-through;margin-left:4px}
.lebd-exam-package-save{align-self:flex-start;padding:3px 6px;border-radius:5px;background:#e6f8ed;color:#087a45;font-size:9px;font-weight:800}
.lebd-exam-package-link{display:block;width:100%;text-align:center;text-decoration:none!important;padding:9px 6px;border:1px solid #2537e9;border-radius:9px;background:#f3f5ff;color:#1726bd!important;font-family:inherit;font-size:10px;font-weight:850;line-height:1.35;cursor:pointer}
.lebd-exam-package-purchased{display:inline-flex;align-items:center;gap:4px;padding:6px 7px;border:1px solid #b8e8ca;border-radius:7px;background:#eafaf0;color:#087343;font-size:10px;font-weight:850;line-height:1.35}
.lebd-exam-package-empty{padding:28px 16px;border:1px dashed #cbd8e9;border-radius:13px;background:#fff;text-align:center;color:#657b98}
.lebd-exam-catalog-note{margin-top:13px;padding:11px 13px;border-radius:9px;background:#fff8e8;color:#8a682e;font-size:10px;line-height:1.6}
.lebd-exam-catalog-alert{padding:11px 13px;border-radius:9px;background:#e8f8ed;color:#176b3b;font-size:12px;margin:12px 0}
@media(max-width:620px){
 .lebd-exam-catalog{width:calc(100% - 18px);margin-top:10px}
 .lebd-exam-catalog-heading h1{font-size:21px}
 .lebd-exam-package{grid-template-columns:minmax(0,1fr) 108px;gap:8px;padding:10px;border-radius:13px}
 .lebd-exam-package-main{gap:8px}
 .lebd-exam-package-icon{width:32px;height:32px;flex-basis:32px;font-size:16px}
 .lebd-exam-package-info h2{font-size:13px}
 .lebd-exam-package-side{padding-left:8px}
 .lebd-exam-package-price{font-size:18px}
 .lebd-exam-package-link{font-size:9px;padding:8px 4px}
 .lebd-exam-package-tags span{font-size:8px;padding:4px 5px}
}
@media(max-width:370px){.lebd-exam-package{grid-template-columns:minmax(0,1fr) 96px}.lebd-exam-package-price{font-size:16px}}
</style>
@php
 $durationLabels = ['days'=>'দিন','weeks'=>'সপ্তাহ','months'=>'মাস','years'=>'বছর'];
 $filterMonths = function($package) {
   $unit = strtolower(trim((string)($package->duration_unit ?? '')));
   $value = (int)($package->duration ?? 0);
   if (str_contains($unit,'year')) return $value * 12;
   if (str_contains($unit,'month')) return $value;
   if (str_contains($unit,'week')) return 0;
   return 0;
 };
@endphp
<main class="lebd-exam-catalog" id="lebd-exam-package-catalog">
 <nav class="lebd-exam-catalog-top">
  <a href="{{ route('student.exam-section.categories') }}">← Exam Section-এ ফিরুন</a>
  <strong style="font-size:11px;color:#183e70">LIVE Education BD · Exam Packages</strong>
  <a href="{{ url('/') }}">Home</a>
 </nav>
 <header class="lebd-exam-catalog-heading">
  <span class="icon"><i class="fa-solid fa-crown"></i></span>
  <div><h1>All Premium Exam Packages</h1><p>পরীক্ষা বিভাগের সকল Active Premium Package</p></div>
 </header>
 <div class="lebd-exam-catalog-notice">
  @if(request('notice')==='limit')
   <strong>আপনার এই মাসের ৩টি ফ্রি পরীক্ষা ব্যবহার হয়ে গেছে।</strong><br>আরও পরীক্ষা দিতে Premium Package কিনুন।
  @else
   প্রতি মাসে ৩টি পরীক্ষা ফ্রি। আরও পরীক্ষা দিতে আপনার প্রয়োজন অনুযায়ী Premium Package বেছে নিন।
  @endif
  @if($used !== null)<br>এই মাসে শুরু করেছেন: <strong>{{ $used }} টি পরীক্ষা</strong>।@endif
 </div>
 @if(session('success'))<div class="lebd-exam-catalog-alert" role="status">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="lebd-exam-catalog-alert" style="background:#fff0f0;color:#a22" role="alert">{{ $errors->first() }}</div>@endif
 <nav class="lebd-exam-catalog-filters" aria-label="Filter exam packages">
  <button type="button" class="active" data-filter="all">সব প্যাকেজ</button>
  <button type="button" data-filter="3">৩ মাসের প্যাকেজ</button>
  <button type="button" data-filter="6">৬ মাসের প্যাকেজ</button>
  <button type="button" data-filter="12">১ বছরের প্যাকেজ</button>
 </nav>
 <section class="lebd-exam-catalog-list">
 @forelse($packages as $p)
  @php
   $original=(float)($p->main_price ?? $p->final_price);
   $price=(float)$p->final_price;
   $discount=max(0,(float)($p->discount_amount ?? ($original-$price)));
   $months=$filterMonths($p);
   $packageScopes=$scopes->get($p->id,collect());
   $owned=$activeAccesses->has($p->id);
  @endphp
  <article class="lebd-exam-package" data-months="{{ $months }}">
   <div class="lebd-exam-package-main">
    <span class="lebd-exam-package-icon"><i class="fa-solid fa-book-open"></i></span>
    <div class="lebd-exam-package-info">
     <h2>{{ $p->name }}</h2>
     <span class="lebd-exam-package-duration">মেয়াদ: {{ (int)$p->duration }} {{ $durationLabels[strtolower((string)$p->duration_unit)] ?? $p->duration_unit }}</span>
     <div class="lebd-exam-package-tags">
      @foreach($packageScopes as $scope)
       <span>{{ $scope->category_name }}@if($scope->subcategory_name) → {{ $scope->subcategory_name }}@endif</span>
      @endforeach
     </div>
     <p>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string)($p->description ?? ''))) ?: 'এই প্যাকেজের অন্তর্ভুক্ত বিভাগ ও পরীক্ষাগুলোতে Premium Access সুবিধা।',170) }}</p>
    </div>
   </div>
   <div class="lebd-exam-package-side">
    <div><span class="lebd-exam-package-price">৳{{ number_format($price,2) }}</span>
     @if($discount>0)<span class="lebd-exam-package-old">৳{{ number_format($original,2) }}</span>@endif
    </div>
    @if($discount>0)<span class="lebd-exam-package-save">Save {{ number_format($original>0 ? min(100,$discount*100/$original):0,2) }}%</span>@endif
    @if($owned)
     <span class="lebd-exam-package-purchased"><i class="fa-solid fa-circle-check"></i> আপনি প্যাকেজ কিনেছেন</span>
     <a class="lebd-exam-package-link" href="{{ route('student.exam-section.categories') }}">পরীক্ষা দিন →</a>
     <small style="font-size:9px;color:#087343;line-height:1.4">মেয়াদ: {{ \Carbon\Carbon::parse($activeAccesses->get($p->id)->ends_at)->format('d M Y, h:i A') }} পর্যন্ত</small>
    @else
     @auth('student')
      <a class="lebd-exam-package-link" href="{{ route('student.exam-section.package.review',$p->id) }}">প্যাকেজ কিনুন →</a>
     @else
      <a class="lebd-exam-package-link" href="{{ url('/students/login') }}">কিনতে Login করুন →</a>
     @endauth
    @endif
   </div>
  </article>
 @empty
  <div class="lebd-exam-package-empty">বর্তমানে কোনো Active Exam Package নেই।</div>
 @endforelse
 </section>
 <div class="lebd-exam-package-empty" id="lebd-exam-filter-empty" hidden>এই মেয়াদের কোনো প্যাকেজ বর্তমানে পাওয়া যায়নি।</div>
 <p class="lebd-exam-catalog-note">প্যাকেজ কিনুন নির্বাচন করলে প্যাকেজের বিস্তারিত ও Payment Summary দেখা যাবে। Referral Balance দিয়ে পেমেন্ট করলে সফল Transaction-এর পর Access চালু হবে। SSLCommerz-এর ক্ষেত্রে Gateway Verification সফল হওয়ার পর Access চালু হবে। Admin Panel থেকে শিক্ষার্থীকে Manual Add / Remove করার সুবিধাও রয়েছে।</p>
</main>
<script>
(function(){
 const root=document.getElementById('lebd-exam-package-catalog'); if(!root)return;
 const buttons=root.querySelectorAll('[data-filter]'), cards=root.querySelectorAll('.lebd-exam-package'), empty=root.querySelector('#lebd-exam-filter-empty');
 buttons.forEach(btn=>btn.addEventListener('click',()=>{
  const filter=btn.getAttribute('data-filter');
  buttons.forEach(b=>b.classList.toggle('active',b===btn));
  let visible=0;
  cards.forEach(card=>{const show=filter==='all'||card.getAttribute('data-months')===filter;card.hidden=!show;if(show)visible++;});
  empty.hidden=visible!==0;
 }));
})();
</script>
@endsection
