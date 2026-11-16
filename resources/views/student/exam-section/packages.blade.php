@php $le185PageTitle = 'Exam Section'; @endphp
@extends('student')
@section('title', 'Exam Section')

@section('stylesheets')
<style>
:root{--navy:#102653;--blue:#0867d8;--line:#d7e5f8;--ink:#102d59;--muted:#6882a5;--soft:#f3f7ff}
.lep-page *{box-sizing:border-box}
.lep-page{min-height:calc(100vh - 80px);background:#f3f7fd;color:var(--ink);font-family:Arial,"Noto Sans Bengali",sans-serif;padding:10px 0 24px}
.lep-container{max-width:1040px;margin:10px auto;padding:12px}
.lep-page .top{display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:12px;margin-bottom:12px}
.lep-page .back{color:#1267c5;text-decoration:none;font-weight:700}
.lep-page .top strong{font-size:11px;color:#174477;text-align:right}
.lep-page .catalog-heading{display:flex;align-items:center;gap:12px;margin:8px 0 14px}
.lep-page .catalog-heading .icon{width:42px;height:42px;display:grid;place-items:center;border-radius:12px;background:#fff0cf;color:#bd7800;font-size:20px;box-shadow:0 2px 7px #c78c2720;flex:none}
.lep-page .catalog-heading h1{font-size:clamp(22px,3vw,29px);line-height:1.2;margin:0;color:#102d59}
.lep-page .catalog-heading p{font-size:11px;color:#6a83a5;margin:4px 0 0}
.lep-page .note{padding:12px 15px;background:#fff2c9;border:1px solid #f4df9e;border-radius:11px;margin:12px 0;font-size:13px;line-height:1.6;color:#684b0d}
.lep-page .note p{margin:4px 0 0}
.lep-page .notice-buy{display:inline-flex;align-items:center;justify-content:center;gap:7px;margin:0 0 12px;padding:9px 14px;border-radius:8px;background:linear-gradient(135deg,#ff8b24,#f45a22);color:#fff;text-decoration:none;font-size:12px;font-weight:800;box-shadow:0 3px 0 #c64d1d}
.lep-page .filters-label{font-size:13px;font-weight:800;margin:12px 0 7px}
.lep-page .filters{display:flex;align-items:center;gap:7px;overflow-x:auto;padding:2px 1px 10px;scrollbar-width:thin}
.lep-page .filters button{font:inherit;font-size:11px;white-space:nowrap;flex:none;padding:8px 13px;border:1px solid #cbdcf5;border-radius:999px;background:#fff;color:#15569b;cursor:pointer}
.lep-page .filters button.is-selected{background:#145fd5;color:#fff;border-color:#145fd5;box-shadow:0 2px 6px #145fd52b}
.lep-page .package-list{display:flex;flex-direction:column;gap:9px}
.lep-page .package-card{display:grid;grid-template-columns:minmax(0,1fr) 120px;gap:12px;align-items:center;background:#fff;border:1px solid var(--line);border-radius:13px;padding:12px 13px;box-shadow:0 3px 12px #153c6a0b;min-width:0}
.lep-page .package-main{display:flex;align-items:flex-start;gap:10px;min-width:0}
.lep-page .package-icon{width:34px;height:34px;display:grid;place-items:center;flex:none;border-radius:9px;background:#eaf2ff;color:#1768d6;font-size:15px}
.lep-page .package-info{min-width:0;flex:1}
.lep-page .package-info h2{font-size:14px;line-height:1.35;margin:0 0 4px;color:#102e5b;overflow-wrap:anywhere}
.lep-page .package-duration{display:inline-block;padding:3px 7px;border-radius:12px;background:#edf3ff;color:#57749a;font-size:9px;margin:0 0 5px}
.lep-page .package-description{font-size:10px;line-height:1.5;color:#7186a3;margin:2px 0 4px;overflow-wrap:anywhere}
.lep-page .details{font-size:10px;line-height:1.55;color:#536f94;min-width:0}
.lep-page .details.collapsed{max-height:31px;overflow:hidden;position:relative}
.lep-page .details.collapsed:after{content:"";position:absolute;bottom:0;left:0;right:0;height:12px;background:linear-gradient(transparent,#fff);pointer-events:none}
.lep-page .details p{margin:0 0 4px;overflow-wrap:anywhere}
.lep-page .scope{overflow-wrap:anywhere}
.lep-page .more{display:inline-block;border:0;background:none;color:#0967bf;font-size:10px;font-weight:800;padding:2px 0;margin:2px 0 0;cursor:pointer}
.lep-page .package-side{display:flex;flex-direction:column;align-items:stretch;gap:7px;min-width:0}
.lep-page .price{font-size:20px;color:#0867c9;font-weight:900;white-space:nowrap;text-align:right;line-height:1.2}
.lep-page .buy{display:flex;align-items:center;justify-content:center;gap:5px;min-height:32px;background:linear-gradient(135deg,#165fe0,#0649ca);padding:7px 8px;color:#fff;border-radius:7px;text-align:center;font-weight:800;text-decoration:none;font-size:10px;line-height:1.3;border:1px solid #0d56d4;box-shadow:0 2px 0 #0b3b9e}
.lep-page .owned{background:#e4f8ec;color:#087344;border:1px solid #9ce0b9;box-shadow:none;cursor:default}
.lep-page .owned-note{color:#168052;font-size:9px;line-height:1.4;margin:0;text-align:right}
.lep-page .muted{color:var(--muted);font-size:10px;line-height:1.6;margin:12px 0}
.lep-page .empty-filter{padding:22px 12px;text-align:center;border:1px dashed #b7cbe4;border-radius:12px;background:#fff;color:#607b9a;font-size:13px}
.lep-page .package-card[hidden],.lep-page .empty-filter[hidden],.lep-page .more[hidden]{display:none!important}
@media(max-width:600px){
 .lep-container{margin:6px auto;padding:10px}.lep-page .top{font-size:11px;align-items:flex-start;flex-direction:column}.lep-page .catalog-heading{gap:12px;padding:16px;border-radius:18px;flex-direction:column;align-items:flex-start}.lep-page .catalog-heading .heading-main{gap:11px;align-items:flex-start}.lep-page .catalog-heading .icon{width:42px;height:42px;font-size:19px;border-radius:13px}.lep-page .catalog-heading h1{font-size:22px}.lep-page .catalog-heading p{font-size:11px}.lep-page .catalog-heading .heading-badge{align-items:flex-start;width:100%}.lep-page .catalog-heading .heading-badge .badge-pill{font-size:11px;padding:9px 12px}
 .lep-page .note{padding:10px 11px;margin:9px 0;font-size:11px}.lep-page .package-list{gap:7px}
 .lep-page .package-card{grid-template-columns:minmax(0,1fr) 94px;gap:8px;padding:10px;border-radius:11px}
 .lep-page .package-main{gap:7px}.lep-page .package-icon{width:29px;height:29px;font-size:13px}
 .lep-page .package-info h2{font-size:12px}.lep-page .package-duration{font-size:8px;padding:3px 6px}
 .lep-page .package-description,.lep-page .details{font-size:9px}.lep-page .details.collapsed{max-height:28px}
 .lep-page .price{font-size:17px}.lep-page .buy{font-size:9px;min-height:30px;padding:6px 5px}.lep-page .owned-note{font-size:8px}
 .lep-page .filters button{font-size:10px;padding:7px 10px}.lep-page .filters-label{font-size:12px}
}
@media(max-width:350px){.lep-page .package-card{grid-template-columns:minmax(0,1fr) 84px;padding:8px}.lep-page .price{font-size:15px}}
/* Premium boxed title header */
.lep-page .catalog-heading{justify-content:space-between;gap:18px;margin:8px 0 16px;padding:20px 22px;border:1px solid #e3d8b1;border-radius:20px;background:linear-gradient(135deg,#fffdfa 0%,#fff8e7 42%,#f2f8ff 100%);box-shadow:0 9px 24px #153c6a14;position:relative;overflow:hidden}
.lep-page .catalog-heading:before{content:"";position:absolute;right:-42px;top:-48px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,#ffe7a2 0,#fff0c7 44%,transparent 72%);pointer-events:none}
.lep-page .heading-main{display:flex;align-items:center;gap:14px;min-width:0;position:relative;z-index:1}
.lep-page .catalog-heading .icon{width:54px;height:54px;border-radius:16px;background:linear-gradient(135deg,#ffe7a3,#fff7dc);border:1px solid #f0d994;color:#b87905;font-size:23px;box-shadow:0 5px 14px #c78c2728}
.lep-page .heading-copy{min-width:0}
.lep-page .catalog-heading h1{font-size:clamp(24px,3vw,31px);font-weight:900;letter-spacing:-.02em}
.lep-page .catalog-heading p{font-size:12px;margin-top:6px}
.lep-page .heading-badge{display:flex;flex-direction:column;align-items:flex-end;gap:7px;position:relative;z-index:1;flex:none}
.lep-page .mini-label{font-size:10px;color:#69809d;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.lep-page .badge-pill{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:linear-gradient(135deg,#145fd5,#0785e9);color:#fff;font-size:11px;font-weight:800;box-shadow:0 5px 14px #145fd535}
@media(max-width:600px){
 .lep-page .catalog-heading{padding:15px;gap:12px;align-items:flex-start;flex-direction:column;border-radius:17px}
 .lep-page .heading-main{gap:11px;align-items:flex-start}
 .lep-page .catalog-heading .icon{width:43px;height:43px;font-size:19px;border-radius:13px}
 .lep-page .catalog-heading h1{font-size:22px}
 .lep-page .catalog-heading p{font-size:10px}
 .lep-page .heading-badge{align-items:flex-start;width:100%}
 .lep-page .badge-pill{font-size:10px;padding:8px 11px}
}

</style>
@endsection

@section('content')
<div class="lep-page">
  <div class="lep-container">
<div class="top"><a class="back" href="{{ route('student.exam-section.categories') }}">← ফিরে যান</a><strong>Live Education BD · Exam Packages</strong></div>
  <header class="catalog-heading">
    <div class="heading-main">
      <span class="icon" aria-hidden="true"><i class="fas fa-crown"></i></span>
      <div class="heading-copy">
        <h1>All Premium Packages</h1>
        <p>Exam Section-এর সকল Active Premium Package</p>
      </div>
    </div>
    <div class="heading-badge">
      <span class="mini-label">Live Education BD</span>
      <span class="badge-pill"><i class="fas fa-gem" aria-hidden="true"></i> Premium Packages</span>
    </div>
  </header>
  @if(in_array(request('notice'), ['limit','pdf','paid'], true))
    <a class="notice-buy" href="#package-cards"><i class="fas fa-shopping-cart" aria-hidden="true"></i> সকল প্যাকেজ দেখুন</a>
  @endif
  @if(session('success'))<div class="note" style="background:#e2f7e9;color:#065e35">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="note" style="background:#ffe5e5;color:#a00">{{ $errors->first() }}</div>@endif
  <div class="filters-label">প্যাকেজের মেয়াদ অনুযায়ী বেছে নিন</div>
  <nav class="filters" aria-label="প্যাকেজের মেয়াদ">
    <button type="button" class="is-selected" data-filter="all" aria-pressed="true">সব প্যাকেজ</button>
    <button type="button" data-filter="3" aria-pressed="false">৩ মাসের প্যাকেজ</button>
    <button type="button" data-filter="6" aria-pressed="false">৬ মাসের প্যাকেজ</button>
    <button type="button" data-filter="12" aria-pressed="false">১ বছরের প্যাকেজ</button>
  </nav>
  <section class="package-list" id="package-cards" aria-label="Exam Packages">
    @forelse($packages as $package)
      @php
        $unit = strtolower(trim((string)($package->duration_unit ?? '')));
        $duration = (int) $package->duration;
        $months = str_contains($unit, 'year') ? $duration * 12 : (str_contains($unit, 'week') ? 0 : (str_contains($unit, 'day') ? 0 : $duration));
        $packageScopes = $scopes->get($package->id, collect());
      @endphp
      <article class="package-card" data-months="{{ $months }}">
        <div class="package-main">
          <span class="package-icon" aria-hidden="true"><i class="fas fa-book-open"></i></span>
          <div class="package-info">
            <h2>{{ $package->name }}</h2>
            <span class="package-duration">মেয়াদ: {{ $package->duration }} {{ $package->duration_unit }}</span>
            @if(filled($package->description))
              <p class="package-description">{{ $package->description }}</p>
            @endif
            @if($packageScopes->isNotEmpty())
              <div class="details collapsed" id="package-details-{{ $package->id }}">
                <div class="scope"><b>অন্তর্ভুক্ত বিভাগ:</b>
                  @foreach($packageScopes as $scope)
                    <span>{{ $scope->category_name }}@if($scope->subcategory_name) → {{ $scope->subcategory_name }}@endif</span>@unless($loop->last), @endunless
                  @endforeach
                </div>
              </div>
              <button type="button" class="more" aria-expanded="false" aria-controls="package-details-{{ $package->id }}">See More</button>
            @endif
          </div>
        </div>
        <div class="package-side">
          <div class="price">৳{{ number_format((float)$package->final_price, 2) }}</div>
          @if($activeAccesses->has($package->id))
            <div class="buy owned"><i class="fas fa-check-circle" aria-hidden="true"></i> প্যাকেজটি কিনেছেন</div>
            <p class="owned-note">মেয়াদ: {{ \Carbon\Carbon::parse($activeAccesses->get($package->id)->ends_at)->format('d M Y, h:i A') }} পর্যন্ত।</p>
          @else
            <a class="buy" href="{{ route('student.exam-section.package.review', $package->id) }}">প্যাকেজ কিনুন →</a>
          @endif
        </div>
      </article>
    @empty
      <div class="empty-filter">বর্তমানে কোনো Active Exam Package নেই।</div>
    @endforelse
  </section>
  <div class="empty-filter" id="filter-empty" hidden>এই মেয়াদের কোনো প্যাকেজ বর্তমানে পাওয়া যায়নি।</div>
  <p class="muted">Referral Balance দিয়ে পরিশোধ করলে সফল Transaction-এর সঙ্গে Package Access চালু হবে। SSLCommerz-এর ক্ষেত্রে Gateway Verification সফল হওয়ার পর Access চালু হবে।</p>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function(){
  'use strict';
  var cards=Array.prototype.slice.call(document.querySelectorAll('#package-cards .package-card'));
  var empty=document.getElementById('filter-empty');
  document.querySelectorAll('.lep-page [data-filter]').forEach(function(button){
    button.addEventListener('click',function(){
      var filter=button.getAttribute('data-filter'), count=0;
      document.querySelectorAll('.lep-page [data-filter]').forEach(function(other){
        var active=other===button;
        other.classList.toggle('is-selected',active);
        other.setAttribute('aria-pressed',active?'true':'false');
      });
      cards.forEach(function(card){
        var visible=filter==='all'||card.getAttribute('data-months')===filter;
        card.hidden=!visible;
        if(visible)count++;
      });
      if(empty)empty.hidden=count!==0 || cards.length===0;
    });
  });
  document.querySelectorAll('.lep-page .more').forEach(function(button){
    button.addEventListener('click',function(){
      var details=document.getElementById(button.getAttribute('aria-controls'));
      if(!details)return;
      var expanded=button.getAttribute('aria-expanded')==='true';
      details.classList.toggle('collapsed',expanded);
      button.setAttribute('aria-expanded',expanded?'false':'true');
      button.textContent=expanded?'See More':'See Less';
    });
  });
})();
</script>
@endsection
