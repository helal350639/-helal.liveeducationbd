@extends('student')
@section('title','Wrong & Unanswered — '.$scopeTitle)
@section('stylesheets')
<style>
.lebd-wu,.lebd-wu *{box-sizing:border-box}
.lebd-wu{background:#f1f6fd;min-height:75vh;padding:22px 12px 45px;color:#17345c;font-family:system-ui,-apple-system,'Segoe UI','Noto Sans Bengali',sans-serif}
.lebd-wu .wrap{max-width:1100px;margin:auto}
.lebd-wu .back{display:inline-flex;margin-bottom:12px;text-decoration:none;color:#1767c8;font-size:13px;font-weight:750}
.lebd-wu .head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:4px 2px 13px;margin-bottom:12px;border-bottom:1px solid #d8e5f5}
.lebd-wu h1{font-size:22px;line-height:1.35;margin:0;color:#173d75;font-weight:850}
.lebd-wu .muted{font-size:12px;color:#7286a3;margin:3px 0 0}
.lebd-wu .total{font-size:12px;color:#7185a0;white-space:nowrap}
.lebd-wu .cards{display:grid;grid-template-columns:1fr;gap:10px}
.lebd-wu .exam-card{display:flex;align-items:center;gap:14px;min-width:0;padding:14px 16px;background:#fff;border:1px solid #d6e4f6;border-radius:15px;box-shadow:0 5px 16px #1b47700b}
.lebd-wu .number{width:44px;height:44px;flex:0 0 44px;display:grid;place-items:center;border-radius:12px;background:#e8f2ff;color:#0870df;font-size:18px;font-weight:900}
.lebd-wu .exam-info{flex:1;min-width:0}
.lebd-wu .exam-title{font-size:16px;font-weight:850;margin:0 0 3px;color:#173d75;overflow-wrap:anywhere}
.lebd-wu .exam-meta{font-size:11px;line-height:1.6;color:#71839c}
.lebd-wu .counts{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:8px}
.lebd-wu .count{display:inline-flex;align-items:center;gap:5px;padding:5px 9px;border-radius:8px;background:#e9f8ef;color:#087b48;font-size:11px;font-weight:750}
.lebd-wu .count.wrong{background:#fff0f0;color:#bd3447}
.lebd-wu .count.unanswered{background:#fff5df;color:#a56a00}
.lebd-wu .count b{font-size:13px}
.lebd-wu .view{display:inline-flex;align-items:center;justify-content:center;gap:7px;flex:0 0 auto;min-height:40px;padding:9px 15px;border-radius:10px;background:linear-gradient(100deg,#075ac8,#087fe8);color:#fff;text-decoration:none;font-size:12px;font-weight:850;box-shadow:0 4px 10px #0875db20}
.lebd-wu .view:hover{filter:brightness(1.06);color:#fff}
.lebd-wu .empty{padding:32px;text-align:center;background:#fff;border:1px dashed #c8d9ef;border-radius:14px;color:#7286a3;line-height:1.8}
@media(max-width:620px){
 .lebd-wu{padding:12px 8px 30px}
 .lebd-wu h1{font-size:19px}
 .lebd-wu .exam-card{align-items:flex-start;gap:9px;padding:12px}
 .lebd-wu .number{width:38px;height:38px;flex-basis:38px;font-size:16px}
 .lebd-wu .exam-title{font-size:14px}
 .lebd-wu .counts{gap:5px}
 .lebd-wu .count{padding:5px 7px;font-size:10px}
 .lebd-wu .view{min-height:36px;padding:8px 10px;font-size:11px;align-self:center}
}
@media(max-width:390px){
 .lebd-wu .exam-card{flex-wrap:wrap}
 .lebd-wu .exam-info{min-width:calc(100% - 52px)}
 .lebd-wu .view{margin-left:47px;width:calc(100% - 47px)}
}
</style>
@endsection
@section('content')
<div class="lebd-wu"><main class="wrap">
<a class="back" href="{{ route('student.exam-section.browse-exams',['category'=>$category->id,'subcategory_id'=>$subcategory?->id]) }}">← Exam Dashboard-এ ফিরুন</a>
<header class="head">
  <div><h1>⊗ Wrong &amp; Unanswered</h1><p class="muted">{{ $scopeTitle }} — পরীক্ষা নির্বাচন করুন এবং সেই পরীক্ষার ভুল ও উত্তরহীন প্রশ্ন দেখুন।</p></div>
  <span class="total">মোট {{ count($cards) }} টি পরীক্ষা</span>
</header>
@if(count($cards))
<section class="cards" aria-label="আপনার জমা দেওয়া পরীক্ষা">
@foreach($cards as $card)
<article class="exam-card">
  <span class="number">{{ $loop->iteration }}</span>
  <div class="exam-info">
    <h2 class="exam-title">{{ $card['name'] }}</h2>
    <div class="exam-meta">Routine: {{ $card['routine'] }}<br>জমা: {{ $card['date'] }}</div>
    <div class="counts" aria-label="পরীক্ষার ফলাফল">
      <span class="count"><b>{{ $card['correct'] }}</b> সঠিক</span>
      <span class="count wrong"><b>{{ $card['wrong'] }}</b> ভুল</span>
      <span class="count unanswered"><b>{{ $card['unanswered'] }}</b> উত্তরহীন</span>
    </div>
  </div>
  <a class="view" href="{{ route('student.exam-section.wrong-unanswered.show',['category'=>$category->id,'item'=>$card['id'],'subcategory_id'=>$subcategory?->id]) }}">প্রশ্ন দেখুন <span aria-hidden="true">→</span></a>
</article>
@endforeach
</section>
@else
<div class="empty">এই Category / Sub-category-তে আপনি এখনো কোনো পরীক্ষা জমা দেননি।<br>পরীক্ষায় অংশগ্রহণ করে Submit করলে শুধু সেই পরীক্ষার কার্ড এখানে দেখা যাবে।</div>
@endif
</main></div>
@endsection
