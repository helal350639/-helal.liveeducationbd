@extends('student')
@section('title','Wrong & Unanswered — '.$scopeTitle)
@section('stylesheets')
<style>
.lebd-wu,.lebd-wu *{box-sizing:border-box}.lebd-wu{background:#f1f6fd;min-height:75vh;padding:22px 12px 45px;color:#17345c;font-family:system-ui,-apple-system,'Segoe UI','Noto Sans Bengali',sans-serif}.lebd-wu .wrap{max-width:1050px;margin:auto}.lebd-wu .head,.lebd-wu .exam-card{background:#fff;border:1px solid #dce8f7;border-radius:16px;box-shadow:0 5px 18px #1b47700b}.lebd-wu .head{padding:19px 21px;margin-bottom:15px;background:linear-gradient(110deg,#e8f3ff,#fff)}.lebd-wu h1{font-size:24px;margin:0 0 5px;color:#104d9b}.lebd-wu .muted{font-size:12px;color:#7286a3;margin:0}.lebd-wu .back{display:inline-flex;margin-bottom:12px;text-decoration:none;color:#1767c8;font-size:13px;font-weight:750}.lebd-wu .stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:14px 0}.lebd-wu .stat{padding:13px;border-radius:12px;background:#fff;border:1px solid #e0eafa}.lebd-wu .stat small{display:block;color:#70839d;font-size:11px}.lebd-wu .stat strong{display:block;font-size:22px;color:#155eb5}.lebd-wu .cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.lebd-wu .exam-card{padding:16px;min-width:0}.lebd-wu .exam-title{font-size:16px;font-weight:850;margin:0 0 5px;overflow-wrap:anywhere}.lebd-wu .exam-meta{font-size:11px;color:#71839c}.lebd-wu .counts{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:7px;margin:13px 0}.lebd-wu .count{padding:9px 6px;text-align:center;border-radius:9px;font-size:11px;background:#e9f8ef;color:#087b48}.lebd-wu .count.wrong{background:#fff0f0;color:#bd3447}.lebd-wu .count.unanswered{background:#fff5df;color:#a56a00}.lebd-wu .count b{display:block;font-size:19px}.lebd-wu .view{display:flex;align-items:center;justify-content:center;min-height:40px;border-radius:9px;background:linear-gradient(100deg,#075ac8,#087fe8);color:#fff;text-decoration:none;font-size:13px;font-weight:850}.lebd-wu .empty{padding:32px;text-align:center;background:#fff;border:1px dashed #c8d9ef;border-radius:14px;color:#7286a3;line-height:1.8}
@media(max-width:620px){.lebd-wu{padding:12px 8px 30px}.lebd-wu h1{font-size:20px}.lebd-wu .head{padding:15px}.lebd-wu .stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}.lebd-wu .stat{padding:10px}.lebd-wu .stat strong{font-size:19px}.lebd-wu .cards{grid-template-columns:1fr}.lebd-wu .exam-card{padding:13px}}
</style>
@endsection
@section('content')
<div class="lebd-wu"><main class="wrap">
<a class="back" href="{{ route('student.exam-section.browse-exams',['category'=>$category->id,'subcategory_id'=>$subcategory?->id]) }}">← Exam Dashboard-এ ফিরুন</a>
<header class="head"><h1>⊗ Wrong &amp; Unanswered</h1><p class="muted">{{ $scopeTitle }} — শুধু আপনি যে পরীক্ষাগুলো জমা দিয়েছেন, সেগুলোর ফলাফল এখানে দেখানো হচ্ছে।</p></header>
<div class="stats">
<div class="stat"><small>অংশগ্রহণ করা পরীক্ষা</small><strong>{{ count($cards) }}</strong></div>
<div class="stat"><small>সঠিক উত্তর</small><strong>{{ collect($cards)->sum('correct') }}</strong></div>
<div class="stat"><small>ভুল উত্তর</small><strong>{{ collect($cards)->sum('wrong') }}</strong></div>
<div class="stat"><small>উত্তর না দেওয়া</small><strong>{{ collect($cards)->sum('unanswered') }}</strong></div>
</div>
@if(count($cards))<section class="cards" aria-label="আপনার দেওয়া পরীক্ষা">
@foreach($cards as $card)
<article class="exam-card"><h2 class="exam-title">{{ $card['name'] }}</h2><div class="exam-meta">{{ $card['routine'] }} · {{ $card['date'] }}</div>
<div class="counts"><div class="count"><b>{{ $card['correct'] }}</b>সঠিক</div><div class="count wrong"><b>{{ $card['wrong'] }}</b>ভুল</div><div class="count unanswered"><b>{{ $card['unanswered'] }}</b>উত্তরহীন</div></div>
<a class="view" href="{{ route('student.exam-section.wrong-unanswered.show',['category'=>$category->id,'item'=>$card['id'],'subcategory_id'=>$subcategory?->id]) }}">বিস্তারিত দেখুন →</a></article>
@endforeach
</section>@else
<div class="empty">এই Category / Sub-category-তে আপনি এখনো কোনো পরীক্ষা জমা দেননি।<br>পরীক্ষায় অংশগ্রহণ করে Submit করলে সেই পরীক্ষার কার্ড এখানে দেখা যাবে।</div>
@endif
</main></div>
@endsection
