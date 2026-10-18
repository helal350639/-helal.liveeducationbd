@extends('homepage')
@section('title', 'চলমান নিয়োগ বিজ্ঞপ্তি')
@section('content')
<style>
.lebd-jc-page{max-width:1440px;margin:0 auto 60px;padding:18px 18px 35px;color:#163564;font-family:inherit}
.lebd-jc-banner{background:linear-gradient(110deg,#075ac6,#139bf0);color:#fff;position:relative;overflow:hidden;padding:24px 28px;border-radius:15px;box-shadow:0 8px 25px rgba(6,84,172,.15)}
.lebd-jc-banner:after{content:'✦';position:absolute;right:6%;top:-45px;color:rgba(255,255,255,.1);font-size:170px}
.lebd-jc-banner h1{position:relative;margin:0 0 5px;color:#fff;font-size:clamp(23px,3vw,33px);font-weight:850}
.lebd-jc-banner p{position:relative;margin:0;color:#e9f5ff;font-size:14px}
.lebd-jc-crumb{display:flex;align-items:center;gap:9px;margin:18px 2px;color:#536b8b;font-size:13px}
.lebd-jc-crumb a{color:#086bd2;text-decoration:none}
.lebd-jc-layout{display:grid;grid-template-columns:265px minmax(0,1fr);gap:18px;align-items:start}
.lebd-jc-panel{padding:17px;background:white;border:1px solid #dfe9f5;border-radius:13px;box-shadow:0 6px 18px rgba(24,62,110,.06);margin-bottom:13px}
.lebd-jc-panel h2{margin:0 0 15px;font-size:16px;color:#153467;font-weight:800}
.lebd-jc-type{display:flex;align-items:center;gap:9px;width:100%;padding:10px;border-radius:9px;color:#364f73;text-decoration:none!important;font-size:13px;font-weight:650;overflow-wrap:anywhere}
.lebd-jc-type:hover,.lebd-jc-type.active{background:#e8f3ff;color:#075fc6}
.lebd-jc-radio{width:17px;height:17px;flex:0 0 17px;border:2px solid #9fb1c7;border-radius:50%;display:inline-flex;align-items:center;justify-content:center}
.lebd-jc-type.active .lebd-jc-radio{border-color:#0874e2}.lebd-jc-type.active .lebd-jc-radio:after{content:'';background:#0874e2;width:9px;height:9px;border-radius:50%}
.lebd-jc-panel input{width:100%;min-width:0;min-height:38px;border:1px solid #d6e1ee;border-radius:8px;padding:7px 9px;color:#213f66;background:#fff}
.lebd-jc-date-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.lebd-jc-date-grid label{font-size:12px;color:#617897}
.lebd-jc-action{display:block;width:100%;margin-top:13px;border:0;background:#076ed6;color:#fff!important;text-align:center;padding:11px;border-radius:9px;font-weight:750;text-decoration:none!important;cursor:pointer}
.lebd-jc-reset{display:block;margin-top:12px;text-align:center;text-decoration:none;color:#647f9d;font-size:12px}
.lebd-jc-main{min-width:0}
.lebd-jc-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:0 0 12px;padding:11px 14px;border:1px solid #e7eef7;border-radius:12px;background:#fff}
.lebd-jc-toolbar h2{font-size:15px;font-weight:800;color:#264267;margin:0}
.lebd-jc-search{display:flex;gap:7px;flex:1;justify-content:flex-end;max-width:430px}.lebd-jc-search input{min-width:0;flex:1;border:1px solid #d9e5f1;border-radius:9px;padding:9px 11px}.lebd-jc-search button{border:0;border-radius:9px;padding:8px 13px;background:#0872dd;color:#fff;cursor:pointer}
.lebd-jc-sort{display:flex;justify-content:flex-end;align-items:center;gap:7px;color:#526c8d;font-size:12px;margin-bottom:11px}
.lebd-jc-sort span{padding:7px 10px;border:1px solid #dce7f3;border-radius:8px;background:white;color:#193967}
.lebd-jc-list{display:grid;gap:11px}
.lebd-jc-card{display:flex;align-items:center;gap:13px;padding:15px;border:1px solid #dce8f4;border-radius:13px;background:#fff;box-shadow:0 5px 17px rgba(28,66,115,.065);min-width:0}
.lebd-jc-icon{flex:0 0 86px;height:82px;border-radius:11px;background:#e9f4ff;display:flex;justify-content:center;align-items:center;color:#0974db;font-size:32px}
.lebd-jc-icon i{display:flex;align-items:center;justify-content:center;width:55px;height:55px;border-radius:50%;background:#fff;box-shadow:0 4px 12px rgba(0,44,111,.09)}
.lebd-jc-content{flex:1;min-width:0}.lebd-jc-content h3{font-size:18px;font-weight:800;line-height:1.45;margin:0 0 7px;color:#133366;overflow-wrap:anywhere}
.lebd-jc-meta{display:flex;flex-wrap:wrap;gap:7px 17px;font-size:12px;color:#4e6584}.lebd-jc-meta span{display:inline-flex;align-items:center;gap:5px}.lebd-jc-meta i{color:#0874db}
.lebd-jc-deadline{font-size:12px;color:#66809d;margin:9px 0 0}
.lebd-jc-right{flex:0 0 160px;display:flex;align-items:center;flex-direction:column;gap:12px}
.lebd-jc-status{color:#078344;background:#e2fbea;border-radius:25px;padding:6px 13px;font-size:12px;font-weight:800;white-space:nowrap}
.lebd-jc-btn{display:block;width:100%;max-width:170px;text-align:center;background:#056fdb;border-radius:25px;padding:10px 12px;color:#fff!important;font-size:13px;font-weight:800;text-decoration:none!important;box-shadow:0 4px 11px rgba(3,110,220,.18)}
.lebd-jc-btn:hover{background:#0359b8}.lebd-jc-empty{padding:55px 20px;text-align:center;border:1px dashed #cbddee;border-radius:13px;background:#f8fbff;color:#516e90}
.lebd-jc-pagination{margin:24px 0;overflow-x:auto}
@media(max-width:900px){.lebd-jc-layout{grid-template-columns:215px minmax(0,1fr)}.lebd-jc-card{flex-wrap:wrap}.lebd-jc-right{flex-basis:100%;flex-direction:row;justify-content:space-between}.lebd-jc-btn{max-width:180px}}
@media(max-width:650px){.lebd-jc-page{padding:10px 10px 25px}.lebd-jc-banner{padding:20px 15px}.lebd-jc-layout{grid-template-columns:1fr}.lebd-jc-sidebar{display:grid;grid-template-columns:1fr;gap:0}.lebd-jc-toolbar{align-items:stretch}.lebd-jc-toolbar h2{width:100%}.lebd-jc-search{max-width:none;justify-content:stretch}.lebd-jc-card{padding:10px;gap:10px}.lebd-jc-icon{flex-basis:61px;height:65px;font-size:22px}.lebd-jc-icon i{width:42px;height:42px}.lebd-jc-content h3{font-size:15px}.lebd-jc-meta{font-size:11px;gap:4px 9px}.lebd-jc-right{gap:9px}.lebd-jc-btn{font-size:12px;width:auto;min-width:145px}}
</style>
<main class="lebd-jc-page">
  <header class="lebd-jc-banner"><h1><i class="fas fa-bullhorn"></i> চলমান নিয়োগ বিজ্ঞপ্তি</h1><p>শুধুমাত্র আবেদন চলমান রয়েছে এমন বিজ্ঞপ্তি — সর্বশেষ আপলোড ও আপডেট আগে</p></header>
  <nav class="lebd-jc-crumb" aria-label="Breadcrumb"><a href="{{ route('homepage') }}"><i class="fas fa-home"></i> হোম</a><span>›</span><span>নিয়োগ বিজ্ঞপ্তি</span></nav>
  <div class="lebd-jc-layout">
    <aside class="lebd-jc-sidebar" aria-label="বিজ্ঞপ্তি ফিল্টার">
      <section class="lebd-jc-panel"><h2>বিজ্ঞপ্তির ধরন</h2>
        <a class="lebd-jc-type {{ !$selectedType ? 'active' : '' }}" href="{{ route('home.circular', array_filter(['search'=>request('search'),'from'=>request('from'),'to'=>request('to')])) }}"><span class="lebd-jc-radio"></span> সব ({{ $allCircularCount }})</a>
        @foreach($counts as $category)
        <a class="lebd-jc-type {{ $selectedType && $selectedType->id == $category->id ? 'active' : '' }}" href="{{ route('home.circular', array_filter(['type'=>$category->id,'search'=>request('search'),'from'=>request('from'),'to'=>request('to')])) }}"><span class="lebd-jc-radio"></span> {{ $category->name }} ({{ $category->active_circulars_count }})</a>
        @endforeach
      </section>
      <form class="lebd-jc-panel" action="{{ route('home.circular') }}" method="GET"><h2>আবেদন শেষের তারিখ</h2>
        @if($selectedType)<input type="hidden" name="type" value="{{ $selectedType->id }}">@endif
        @if($keyword !== '')<input type="hidden" name="search" value="{{ $keyword }}">@endif
        <div class="lebd-jc-date-grid"><label>থেকে<input type="date" name="from" value="{{ request('from') }}"></label><label>পর্যন্ত<input type="date" name="to" value="{{ request('to') }}"></label></div>
        <button class="lebd-jc-action" type="submit"><i class="fas fa-filter"></i> ফিল্টার করুন</button>
        <a class="lebd-jc-reset" href="{{ route('home.circular') }}">সব ফিল্টার মুছুন</a>
      </form>
    </aside>
    <section class="lebd-jc-main" aria-label="নিয়োগ বিজ্ঞপ্তির তালিকা">
      <div class="lebd-jc-toolbar"><h2>মোট {{ $circulars->total() }}টি চলমান নিয়োগ বিজ্ঞপ্তি পাওয়া গেছে</h2>
        <form class="lebd-jc-search" method="GET" action="{{ route('home.circular') }}">
          @if($selectedType)<input type="hidden" name="type" value="{{ $selectedType->id }}">@endif
          @if(request('from'))<input type="hidden" name="from" value="{{ request('from') }}">@endif
          @if(request('to'))<input type="hidden" name="to" value="{{ request('to') }}">@endif
          <input type="search" name="search" value="{{ $keyword }}" placeholder="নিয়োগ বিজ্ঞপ্তি খুঁজুন..." aria-label="নিয়োগ বিজ্ঞপ্তি খুঁজুন"><button type="submit" aria-label="খুঁজুন"><i class="fas fa-search"></i></button>
        </form>
      </div>
      <div class="lebd-jc-sort"><i class="fas fa-sort-amount-down"></i> সাজানো: <span>সর্বশেষ আপলোড/আপডেট আগে</span></div>
      @if($circulars->count())
      <div class="lebd-jc-list">
        @foreach($circulars as $circular)
          <article class="lebd-jc-card">
            <div class="lebd-jc-icon"><i class="fas fa-landmark"></i></div>
            <div class="lebd-jc-content">
              <h3>{{ $circular->title }}</h3>
              <div class="lebd-jc-meta">
                @if($circular->admit_to)<span><i class="fas fa-building"></i> {{ $circular->admit_to }}</span>@endif
                @if($circular->position)<span><i class="fas fa-briefcase"></i> পদ: {{ $circular->position }}</span>@endif
                @if($circular->published_at)<span><i class="far fa-calendar-alt"></i> প্রকাশিত: {{ \Carbon\Carbon::parse($circular->published_at)->format('d/m/Y') }}</span>@endif
              </div>
              <p class="lebd-jc-deadline"><i class="far fa-clock"></i> আবেদনের শেষ সময়: {{ \Carbon\Carbon::parse($circular->end_at)->format('d/m/Y') }}</p>
            </div>
            <div class="lebd-jc-right"><span class="lebd-jc-status"><i class="fas fa-circle" style="font-size:7px"></i> আবেদন চলছে</span><a class="lebd-jc-btn" href="{{ route('home.circular.show', $circular->id) }}">বিস্তারিত দেখুন →</a></div>
          </article>
        @endforeach
      </div><div class="lebd-jc-pagination">{{ $circulars->links() }}</div>
      @else
      <div class="lebd-jc-empty"><i class="far fa-folder-open"></i> এই মুহূর্তে শর্ত অনুযায়ী কোনো চলমান নিয়োগ বিজ্ঞপ্তি পাওয়া যায়নি।</div>
      @endif
    </section>
  </div>
</main>
@endsection
