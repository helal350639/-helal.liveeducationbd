@extends('homepage')
@section('title','Premium Recorded Class Packages')
@section('content')
@php
 $tabs=['all'=>'সব প্যাকেজ','combined'=>'Combined','department'=>'Department','non_department'=>'Non-Department'];
@endphp
<style>
.lebd117{max-width:1160px;margin:22px auto 45px;padding:0 14px;color:#123765;font-family:inherit}
.lebd117-hero{border-radius:15px;background:linear-gradient(110deg,#d8eafd,#eef7ff);padding:21px 22px;margin-bottom:15px}
.lebd117-hero .kicker{font-size:10px;font-weight:800;letter-spacing:.08em;color:#1265b5}
.lebd117-hero h1{margin:5px 0 4px;font-size:25px;font-weight:850;color:#102f61}
.lebd117-hero p{margin:0;color:#456b94;font-size:12px}
.lebd117-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:14px 0}
.lebd117-tab{display:inline-flex;align-items:center;border:1px solid #c8dcf5;border-radius:22px;padding:7px 13px;color:#174c8b!important;text-decoration:none!important;font-size:12px;font-weight:700;background:#fff}
.lebd117-tab.active{background:#1425ca;color:#fff!important;border-color:#1425ca}
.lebd117-list{display:flex;flex-direction:column;gap:12px}
.lebd117-card{display:grid;grid-template-columns:minmax(0,1fr) 178px;gap:16px;border:1px solid #d4e3f7;border-radius:14px;background:#fff;box-shadow:0 5px 17px rgba(24,78,139,.055);padding:14px 15px;min-width:0}
.lebd117-main{display:flex;gap:12px;min-width:0}
.lebd117-icon{flex:0 0 38px;width:38px;height:38px;border-radius:11px;background:#eaf2ff;color:#1768cf;display:grid;place-items:center;font-size:18px}
.lebd117-info{min-width:0}
.lebd117-info h2{font-size:17px;line-height:1.35;margin:0 0 5px;color:#12396e;font-weight:800;overflow-wrap:anywhere}
.lebd117-kind{display:inline-block;border-radius:5px;background:#edf4ff;color:#37628e;padding:3px 7px;font-size:10px;font-weight:700;margin-bottom:5px}
.lebd117-info p{font-size:12px;line-height:1.55;color:#456b94;margin:3px 0}
.lebd117-details{margin-top:5px}
.lebd117-details summary{font-size:11px;color:#075cc1;font-weight:700;cursor:pointer}
.lebd117-description{font-size:12px;line-height:1.6;color:#476987;padding:7px 0}
.lebd117-side{border-left:1px solid #d9e6f7;padding:3px 0 3px 14px;display:flex;flex-direction:column;gap:7px;justify-content:center;min-width:0}
.lebd117-price{font-size:22px;font-weight:900;color:#0b4d91;line-height:1.2}
.lebd117-owned{display:block;text-align:center;padding:8px 7px;border:1px solid #a7e2bd;border-radius:8px;background:#eaf9ef;color:#08783f!important;text-decoration:none!important;font-size:11px;font-weight:800;line-height:1.35}
.lebd117-buy{display:block;text-align:center;padding:9px 8px;border:1px solid #075fe0;border-radius:8px;background:#096fe0;color:#fff!important;text-decoration:none!important;font-size:12px;font-weight:800}
.lebd117-buy:hover{background:#0757b5}
.lebd117-empty{padding:22px;border:1px dashed #b9d2ef;border-radius:12px;text-align:center;color:#58728d;background:#fff}
.lebd117-back{display:inline-block;margin-top:17px;color:#0868c9!important;text-decoration:none!important;font-size:12px;font-weight:700}
.lebd117-alert{padding:12px 14px;border:1px solid #9bdab2;border-radius:10px;background:#eaf9ef;color:#11623a;margin-bottom:12px;font-size:13px}
@media(max-width:640px){.lebd117{margin:13px auto 30px;padding:0 10px}.lebd117-hero{padding:17px 15px}.lebd117-hero h1{font-size:20px}.lebd117-card{grid-template-columns:1fr;gap:10px;padding:12px}.lebd117-side{border-left:0;border-top:1px solid #d9e6f7;padding:10px 0 0;display:grid;grid-template-columns:1fr 1fr;align-items:center}.lebd117-price{grid-column:1/-1;font-size:21px}.lebd117-owned,.lebd117-buy{font-size:11px}.lebd117-info h2{font-size:15px}}
</style>
<main class="lebd117">
 <header class="lebd117-hero">
  <span class="kicker">LIVE EDUCATION BD • PREMIUM</span>
  <h1>আপনার পছন্দের প্যাকেজ নির্বাচন করুন</h1>
  <p>প্রয়োজন অনুযায়ী Department, Non-Department অথবা Combined Package কিনুন।</p>
 </header>
 @if(session('success'))<div class="lebd117-alert" role="status">{{session('success')}}</div>@endif
 @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
 <nav class="lebd117-tabs" aria-label="Package categories">
  @foreach($tabs as $slug=>$label)
   <a href="{{route('premium.recordings.packages',array_filter(['type'=>$slug,'premium_group'=>$group,'premium_topic'=>$topic]))}}" class="lebd117-tab {{ $type===$slug?'active':'' }}" @if($type===$slug) aria-current="page" @endif>{{$label}}</a>
  @endforeach
 </nav>
 <div class="lebd117-list">
 @forelse($packages as $package)
 @php
  $kind=str_replace(['-',' '],'_',(string)$package->package_type);
  if($kind==='combine')$kind='combined';
  $price=(float)$package->price;
  $final=(float)($package->final_price??$package->price);
  $saving=max(0,$price-$final);
  $duration=(int)($package->duration_value??$package->validity_days);
  $unit=(string)($package->duration_unit??'days');
  $dept=(array)($package->department_categories??[]);
  $nondept=(array)($package->non_department_categories??[]);
  $owned=$ownedPackages[(int)$package->id]??null;
 @endphp
 <article class="lebd117-card">
  <div class="lebd117-main">
   <span class="lebd117-icon" aria-hidden="true"><i class="fa-solid fa-book-open"></i></span>
   <div class="lebd117-info">
    <h2>{{ $package->name }}</h2>
    <span class="lebd117-kind">{{ $kind==='combined'?'Combined':($kind==='department'?'Department':'Non-Department') }}</span>
    <p>মেয়াদ: {{ $duration }} {{ $unit }}</p>
    @if(count($dept))<p><b>Department:</b> {{implode(', ',array_map('ucfirst',$dept))}}</p>@endif
    @if(count($nondept))<p><b>Non-Department:</b> {{implode(', ',array_map(fn($x)=>str_replace('-',' ',$x),$nondept))}}</p>@endif
    <details class="lebd117-details"><summary>প্যাকেজের বিস্তারিত দেখুন</summary>
     <div class="lebd117-description">{{ $package->description ?: 'নির্বাচিত বিভাগ ও বিষয়গুলোর Premium Recorded Classes দেখার সুবিধা।' }}<br>কেনা প্যাকেজের মেয়াদ ও নির্বাচিত Category অনুযায়ী Access প্রযোজ্য হবে।</div>
    </details>
   </div>
  </div>
  <aside class="lebd117-side">
   <div class="lebd117-price">৳{{number_format($final,2)}}</div>
   @if($saving>0)<small style="color:#6a829c;text-decoration:line-through">৳{{number_format($price,2)}}</small>@endif
   @if($owned)
    <span class="lebd117-owned">✓ আপনি প্যাকেজ কিনেছেন</span>
    <a class="lebd117-buy" href="{{ route('students.premium.video.classes') }}">ক্লাস দেখুন →</a>
   @else
    <a class="lebd117-buy" href="{{ route('premium.payment.review',$package) }}">প্যাকেজ কিনুন →</a>
   @endif
  </aside>
 </article>
 @empty<div class="lebd117-empty">এই ধরনের কোনো Active Premium Package নেই। উপরের অন্য Category নির্বাচন করুন।</div>
 @endforelse
 </div>
 <a class="lebd117-back" href="{{route('homepage',array_filter(['premium_group'=>$group,'premium_topic'=>$topic,'premium_view'=>'recorded']))}}">← রেকর্ড ক্লাসে ফিরে যান</a>
</main>
@endsection
