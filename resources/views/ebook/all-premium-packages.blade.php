@extends('homepage')
@section('title', 'All Premium E-Book Packages | Live Education BD')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-student-v22.css') }}">
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-premium-v25.css') }}">
@php $durationLabels = ['days'=>'দিন','months'=>'মাস','years'=>'বছর']; @endphp
<style>
.lebd-all-catalog{max-width:980px;width:calc(100% - 28px);margin:20px auto 60px;color:#102f62}
.lebd-all-catalog-top{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:11px 14px;margin-bottom:17px;background:#fff;border:1px solid #dce7f7;border-radius:14px}
.lebd-all-catalog-top a{font-size:12px;font-weight:750;text-decoration:none;color:#1658b6}
.lebd-all-catalog-heading{display:flex;align-items:center;gap:12px;margin:12px 0 15px}
.lebd-all-catalog-heading .icon{width:48px;height:48px;display:grid;place-items:center;border-radius:14px;background:#fff1d4;color:#b87900;font-size:24px}
.lebd-all-catalog-heading h1{margin:0;font-size:25px;font-weight:900;line-height:1.25}
.lebd-all-catalog-heading p{margin:4px 0 0;color:#647b9b;font-size:11px}
.lebd-all-catalog-filters{display:flex;gap:7px;flex-wrap:wrap;margin:14px 0}
.lebd-all-catalog-filters button{border:1px solid #d5e1f3;background:#fff;color:#234b83;border-radius:22px;padding:8px 13px;font-size:11px;font-weight:750;cursor:pointer}
.lebd-all-catalog-filters button.active{background:#2524c9;border-color:#2524c9;color:#fff}
.lebd-all-catalog-list{display:flex;flex-direction:column;gap:10px}
.lebd-all-package{display:grid;grid-template-columns:minmax(0,1fr) 145px;gap:13px;align-items:stretch;padding:13px;background:#fff;border:1px solid #dce7f5;border-radius:16px;box-shadow:0 5px 17px rgba(20,55,100,.055)}
.lebd-all-package[hidden]{display:none!important}
.lebd-all-package-main{display:flex;gap:11px;min-width:0}
.lebd-all-package-icon{width:38px;height:38px;flex:0 0 38px;display:grid;place-items:center;border-radius:11px;background:#edf3ff;color:#236bd4;font-size:18px}
.lebd-all-package-info{min-width:0}
.lebd-all-package-info h2{font-size:16px;font-weight:850;margin:0 0 5px;line-height:1.3}
.lebd-all-package-duration{display:inline-block;background:#f0f3fb;color:#617596;padding:3px 8px;border-radius:20px;font-size:10px;margin-bottom:7px}
.lebd-all-package-tags{display:flex;gap:5px;flex-wrap:wrap;margin:2px 0 7px}
.lebd-all-package-tags span{background:#edf4ff;color:#315f9a;padding:4px 7px;border-radius:5px;font-size:9px}
.lebd-all-package-info p{font-size:11px;color:#5d7392;line-height:1.5;margin:3px 0}
.lebd-all-package-side{border-left:1px solid #e0e9f6;padding:5px 0 5px 12px;display:flex;flex-direction:column;justify-content:center;gap:7px;min-width:0}
.lebd-all-package-price{font-size:22px;font-weight:900;white-space:nowrap}
.lebd-all-package-old{font-size:10px;color:#9aa8ba;text-decoration:line-through;margin-left:4px}
.lebd-all-package-save{align-self:flex-start;padding:3px 6px;border-radius:5px;background:#e6f8ed;color:#087a45;font-size:9px;font-weight:800}
.lebd-all-package-link{display:block;text-align:center;text-decoration:none!important;padding:9px 6px;border:1px solid #2537e9;border-radius:9px;background:#f3f5ff;color:#1726bd!important;font-size:10px;font-weight:850;line-height:1.35}
.lebd-all-package-purchased,.lebd-all-package-active{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:850;line-height:1.35;color:#087343}
.lebd-all-package-purchased{padding:6px 7px;border:1px solid #b8e8ca;border-radius:7px;background:#eafaf0}
.lebd-all-package-unavailable{font-size:9px;line-height:1.4;color:#9b5b21}

.lebd-all-package-empty{padding:28px 16px;border:1px dashed #cbd8e9;border-radius:13px;background:#fff;text-align:center;color:#657b98}
.lebd-all-catalog-note{margin-top:13px;padding:11px 13px;border-radius:9px;background:#fff8e8;color:#8a682e;font-size:10px;line-height:1.55}
@media(max-width:620px){
 .lebd-all-catalog{width:calc(100% - 18px);margin-top:10px}
 .lebd-all-catalog-heading h1{font-size:21px}
 .lebd-all-package{grid-template-columns:minmax(0,1fr) 108px;gap:8px;padding:10px;border-radius:13px}
 .lebd-all-package-main{gap:8px}
 .lebd-all-package-icon{width:32px;height:32px;flex-basis:32px;font-size:16px}
 .lebd-all-package-info h2{font-size:13px}
 .lebd-all-package-side{padding-left:8px}
 .lebd-all-package-price{font-size:18px}
 .lebd-all-package-link{font-size:9px;padding:8px 4px}
 .lebd-all-package-tags span{font-size:8px;padding:4px 5px}
}
@media(max-width:370px){.lebd-all-package{grid-template-columns:minmax(0,1fr) 96px}.lebd-all-package-price{font-size:16px}}
</style>
<main class="lebd-all-catalog" id="lebd-all-premium-catalog">
 <nav class="lebd-all-catalog-top"><a href="{{ url()->previous() }}">← আগের পেজে ফিরুন</a><strong>LIVE Education BD</strong><a href="{{ url('/') }}">Home</a></nav>
 <header class="lebd-all-catalog-heading"><span class="icon"><i class="fa-solid fa-crown"></i></span><div><h1>All Premium Packages</h1><p>Department ও Non-Department E-Book-এর সকল Active Premium Package</p></div></header>
 <nav class="lebd-all-catalog-filters" aria-label="Filter packages">
  <button type="button" class="active" data-filter="all">সব প্যাকেজ</button>
  <button type="button" data-filter="department">Department</button>
  <button type="button" data-filter="non-department">Non-Department</button>
  <button type="button" data-filter="combined">Combined</button>
 </nav>
 @if(!empty($catalogError))
  <div class="lebd-all-package-empty" role="alert" style="margin:12px 0;border-color:#f0c7a2;background:#fff8f1;color:#8a4b18">
   <strong>প্যাকেজ লোড করতে সমস্যা হয়েছে</strong><br>{{ $catalogError }}
  </div>
 @endif
 <section class="lebd-all-catalog-list">
 @forelse($packages as $p)
  @php
   $original=(float)($p->main_price ?? $p->price);
   $price=(float)$p->price;
   $discount=max(0,(float)($p->discount_amount ?? ($original-$price)));
   $offerActive=empty($p->offer_ends_at) || \Carbon\Carbon::parse($p->offer_ends_at)->endOfDay()->isFuture();
   $group=$p->catalog_filter_group ?? 'department';
  @endphp
  <article class="lebd-all-package" data-group="{{ $group }}">
   <div class="lebd-all-package-main">
    <span class="lebd-all-package-icon"><i class="fa-solid fa-book-open"></i></span>
    <div class="lebd-all-package-info">
     <h2>{{ $p->name }}</h2>
     <span class="lebd-all-package-duration">মেয়াদ: {{ (int)$p->duration_value }} {{ $durationLabels[$p->duration_unit] ?? 'দিন' }}</span>
     <div class="lebd-all-package-tags">
      @foreach(($p->catalog_category_labels ?? collect()) as $label)<span>{{ $label }}</span>@endforeach
      @if($group==='combined')<span>Combined</span>@endif
     </div>
     <p>{{ \Illuminate\Support\Str::limit($p->description ?: 'Premium E-Book Reading ও Full Book Access সুবিধা।',130) }}</p>
    </div>
   </div>
   <div class="lebd-all-package-side">
    <div><span class="lebd-all-package-price">৳{{ number_format($price,0) }}</span>
     @if($discount>0 && $offerActive)<span class="lebd-all-package-old">৳{{ number_format($original,0) }}</span>@endif
    </div>
    @if($discount>0 && $offerActive)<span class="lebd-all-package-save">Save {{ number_format($original>0 ? min(100,$discount*100/$original):0,2) }}%</span>@endif
    @if($p->has_been_purchased || $p->has_active_access)
      <span class="lebd-all-package-purchased"><i class="fa-solid fa-circle-check"></i> আপনি প্যাকেজ কিনেছেন</span>
      @if($p->has_active_access && $p->catalog_category_id)
        <a class="lebd-all-package-link" href="{{ route('student.ebook.chapters',['kind'=>$p->catalog_kind,'category'=>$p->catalog_category_id]) }}">বই পড়ুন →</a>
      @elseif($p->has_been_purchased && $p->catalog_category_id)
        <a class="lebd-all-package-link" href="{{ route('student.ebook.packages',['kind'=>$p->catalog_kind,'category'=>$p->catalog_category_id]) }}">প্যাকেজ নবায়ন করুন →</a>
      @endif
    @elseif($p->catalog_category_id)
      @auth('student')
        {{-- Existing payment start and checkout flow remains unchanged. --}}
        <form method="POST" action="{{ route('lebd.ebook.payment.start', ['kind'=>$p->catalog_kind,'category'=>$p->catalog_category_id,'package'=>$p->id]) }}" style="margin:0">
          @csrf
          <button type="submit" class="lebd-all-package-link" style="width:100%;font-family:inherit;cursor:pointer">প্যাকেজ কিনুন →</button>
        </form>
      @else
        <a class="lebd-all-package-link" href="{{ url('/students/login') }}">কিনতে Login করুন →</a>
      @endauth
    @else
      <span class="lebd-all-package-unavailable">বইয়ের বিভাগ বর্তমানে প্রকাশিত নয়</span>
    @endif
   </div>
  </article>
 @empty
  <div class="lebd-all-package-empty">
   @if(empty($catalogError))
    এখনো কোনো Active Premium Package প্রকাশ করা হয়নি। Admin Panel থেকে Package Status Active এবং Category Mapping পরীক্ষা করুন।
   @else
    প্যাকেজের তথ্য পাওয়া যাচ্ছে না। উপরের সমস্যার বার্তা অনুযায়ী database ও mapping পরীক্ষা করুন।
   @endif
  </div>
 @endforelse
 </section>
 <p class="lebd-all-catalog-note">প্যাকেজ কিনুন নির্বাচন করলে সংশ্লিষ্ট প্যাকেজের Payment Summary খুলবে। Referral Balance, SSLCommerz Checkout, Payment Preview ও সফল পেমেন্টের পর Access Activation আগের নিয়মেই চলবে।</p>
</main>
<script>
(function(){
 const root=document.getElementById('lebd-all-premium-catalog'); if(!root)return;
 const buttons=root.querySelectorAll('[data-filter]'); const cards=root.querySelectorAll('.lebd-all-package');
 buttons.forEach(btn=>btn.addEventListener('click',()=>{
  const filter=btn.getAttribute('data-filter');
  buttons.forEach(b=>b.classList.toggle('active',b===btn));
  cards.forEach(card=>{card.hidden=!(filter==='all'||card.getAttribute('data-group')===filter);});
 }));
})();
</script>
@endsection
