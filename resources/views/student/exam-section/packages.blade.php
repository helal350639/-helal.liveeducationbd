<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Exam Packages — Live Education BD</title>
<style>
:root{--navy:#102c53;--blue:#086ac4;--line:#d8e5f6;--ink:#142d4f;--muted:#617a99}
*{box-sizing:border-box}body{margin:0;background:#eef3fb;color:var(--ink);font-family:Arial,"Noto Sans Bengali",sans-serif}main{max-width:990px;margin:22px auto;padding:12px}.top{display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:13px}.back{color:#1267b9;text-decoration:none}.top strong{font-size:12px;text-align:right}h1{font-size:clamp(24px,4vw,34px);margin:18px 0}.note{padding:13px 15px;background:#fff1c5;border-radius:12px;margin:15px 0;font-size:14px;line-height:1.6}.note p{margin:5px 0 0}.filters{display:flex;align-items:center;gap:7px;overflow-x:auto;padding:3px 1px 11px;scrollbar-width:thin}.filters button{font:inherit;font-size:13px;white-space:nowrap;flex:none;padding:9px 12px;border:1px solid #cadcf5;border-radius:999px;background:#fff;color:#13539a;cursor:pointer}.filters button.is-selected{background:#0c65bc;color:#fff;border-color:#0c65bc;box-shadow:0 2px 7px #0c65bc25}.filter-label{font-weight:700;margin:13px 0 6px;font-size:14px}.cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.card{background:#fff;border:1px solid var(--line);border-radius:15px;box-shadow:0 4px 16px #183d640c;padding:15px 17px;min-width:0;display:flex;flex-direction:column}.card-head{display:flex;align-items:start;justify-content:space-between;gap:8px}.card h2{font-size:17px;margin:0;line-height:1.35}.price{font-size:24px;color:var(--blue);font-weight:800;white-space:nowrap}.meta{font-size:12px;color:var(--muted);margin:4px 0 8px}.details{font-size:12px;color:#4c6685;line-height:1.6;min-width:0}.details.collapsed{max-height:35px;overflow:hidden;position:relative}.details.collapsed:after{content:"";position:absolute;bottom:0;left:0;right:0;height:15px;background:linear-gradient(transparent,#fff);pointer-events:none}.details p{margin:0 0 5px;overflow-wrap:anywhere}.scope{overflow-wrap:anywhere}.more{align-self:flex-start;border:0;background:none;color:#0967bf;font-size:12px;font-weight:700;padding:4px 0;margin:2px 0 8px;cursor:pointer}.buy{display:block;background:#1471ca;padding:10px 12px;color:#fff;border-radius:9px;text-align:center;font-weight:700;margin-top:auto;text-decoration:none;font-size:13px}.owned{background:#e2f7e9;color:#176c40;cursor:default}.owned-note{color:#176c40;font-size:11px;line-height:1.4;margin:6px 0 0}.muted{color:var(--muted);font-size:12px;line-height:1.6}.empty-filter{padding:25px 12px;text-align:center;border:1px dashed #b7cbe4;border-radius:12px;background:#fff;color:#607b9a;font-size:14px}.card[hidden],.empty-filter[hidden],.more[hidden]{display:none!important}
@media(max-width:600px){main{margin:6px auto;padding:10px}.top{font-size:12px}h1{font-size:24px;margin:12px 0}.note{padding:10px 12px;margin:10px 0;font-size:12px}.cards{grid-template-columns:1fr;gap:8px}.card{padding:11px 12px;border-radius:12px}.card-head{align-items:center}.card h2{font-size:15px}.price{font-size:19px}.meta{margin:2px 0 4px}.details.collapsed{max-height:28px}.more{margin:0 0 5px}.buy{padding:9px;font-size:12px}.owned-note{margin:4px 0 0}.filters button{font-size:12px;padding:8px 10px}.filter-label{margin:9px 0 4px}}
</style>
</head>
<body>
<main>
  <div class="top"><a class="back" href="{{ route('student.exam-section.categories') }}">← ফিরে যান</a><strong>Live Education BD · Exam Packages</strong></div>
  <h1>প্রিমিয়াম পরীক্ষার প্যাকেজ</h1>
  <div class="note">
    @if(request('notice')==='limit')
      <strong>আপনার এই মাসের ৩টি ফ্রি পরীক্ষা ব্যবহার হয়ে গেছে।</strong><p>নতুন পরীক্ষা দিতে প্রিমিয়াম প্যাকেজ প্রয়োজন।</p>
    @else
      প্রতি মাসে ৩টি পরীক্ষা ফ্রি। আরও পরীক্ষা দিতে প্যাকেজ দেখুন।
    @endif
    @if($used !== null)<p>এই মাসে শুরু করেছেন: {{ $used }} টি পরীক্ষা।</p>@endif
  </div>
  @if(session('success'))<div class="note" style="background:#e2f7e9;color:#065e35">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="note" style="background:#ffe5e5;color:#a00">{{ $errors->first() }}</div>@endif
  <div class="filter-label">প্যাকেজের মেয়াদ অনুযায়ী বেছে নিন</div>
  <nav class="filters" aria-label="প্যাকেজের মেয়াদ">
    <button type="button" class="is-selected" data-filter="all" aria-pressed="true">সব প্যাকেজ</button>
    <button type="button" data-filter="3" aria-pressed="false">৩ মাসের প্যাকেজ</button>
    <button type="button" data-filter="6" aria-pressed="false">৬ মাসের প্যাকেজ</button>
    <button type="button" data-filter="12" aria-pressed="false">১ বছরের প্যাকেজ</button>
  </nav>
  <div class="cards" id="package-cards">
    @forelse($packages as $package)
      @php
        $unit = strtolower(trim((string)($package->duration_unit ?? '')));
        $duration = (int) $package->duration;
        $months = str_contains($unit, 'year') ? $duration * 12 : (str_contains($unit, 'week') ? 0 : (str_contains($unit, 'day') ? 0 : $duration));
        $packageScopes = $scopes->get($package->id, collect());
        $hasExtra = filled(trim(strip_tags((string)($package->description ?? '')))) || $packageScopes->isNotEmpty();
      @endphp
      <article class="card" data-months="{{ $months }}">
        <div class="card-head"><h2>{{ $package->name }}</h2><div class="price">৳{{ number_format((float)$package->final_price, 2) }}</div></div>
        <div class="meta">মেয়াদ: {{ $package->duration }} {{ $package->duration_unit }}</div>
        @if($hasExtra)
          <div class="details collapsed" id="package-details-{{ $package->id }}">
            @if(filled($package->description))<p>{{ $package->description }}</p>@endif
            @if($packageScopes->isNotEmpty())
              <div class="scope"><b>অন্তর্ভুক্ত বিভাগ:</b>
                @foreach($packageScopes as $scope)
                  <span>{{ $scope->category_name }}@if($scope->subcategory_name) → {{ $scope->subcategory_name }}@endif</span>@unless($loop->last), @endunless
                @endforeach
              </div>
            @endif
          </div>
          <button type="button" class="more" aria-expanded="false" aria-controls="package-details-{{ $package->id }}">See More</button>
        @endif
        @if($activeAccesses->has($package->id))
          <div class="buy owned">✓ আপনি ইতিমধ্যে এই প্যাকেজ কিনেছেন</div>
          <p class="owned-note">মেয়াদ: {{ \Carbon\Carbon::parse($activeAccesses->get($package->id)->ends_at)->format('d M Y, h:i A') }} পর্যন্ত।</p>
        @else
          <a class="buy" href="{{ route('student.exam-section.package.review', $package->id) }}">প্যাকেজ কিনুন →</a>
        @endif
      </article>
    @empty
      <div class="empty-filter">বর্তমানে কোনো Active Exam Package নেই।</div>
    @endforelse
  </div>
  <div class="empty-filter" id="filter-empty" hidden>এই মেয়াদের কোনো প্যাকেজ বর্তমানে পাওয়া যায়নি।</div>
  <p class="muted">Referral Balance দিয়ে পরিশোধ করলে সফল Transaction-এর সঙ্গে Package Access চালু হবে। SSLCommerz-এর ক্ষেত্রে Gateway Verification সফল হওয়ার পর Access চালু হবে।</p>
</main>
<script>
(function(){
  'use strict';
  var cards=Array.prototype.slice.call(document.querySelectorAll('#package-cards .card'));
  var empty=document.getElementById('filter-empty');
  document.querySelectorAll('[data-filter]').forEach(function(button){
    button.addEventListener('click',function(){
      var filter=button.getAttribute('data-filter'), count=0;
      document.querySelectorAll('[data-filter]').forEach(function(other){
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
  document.querySelectorAll('.more').forEach(function(button){
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
</body>
</html>
