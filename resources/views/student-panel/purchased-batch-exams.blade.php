@extends('student')
@section('title', 'My Batch — Exam Dashboard')
@section('content')
<style>
/* LEBD-05 Mobile App-like Exam Dashboard: isolated stylesheet */
.lebdce,.lebdce *{box-sizing:border-box}
.lebdce{--ink:#102c5d;--muted:#637da1;--blue:#126be3;width:min(100% - 32px,880px);margin:0 auto 45px;padding:18px 0;color:var(--ink);font-family:"Noto Sans Bengali",system-ui,sans-serif;font-size:14px;line-height:1.55}
.lebdce a{text-decoration:none}
.lebdce .back{display:inline-flex;color:#0e66cb;align-items:center;min-height:32px;font-size:13px;font-weight:600;margin-bottom:11px}
.lebdce .hero{position:relative;text-align:center;padding:15px 12px 18px;background:linear-gradient(130deg,#edf7ff,#fff 74%);border-radius:16px;margin-bottom:12px}
.lebdce .hero:after{content:'🎓';position:absolute;right:18px;top:8px;font-size:31px;opacity:.8}
.lebdce h1{font-size:24px;line-height:1.38;font-weight:800;margin:0 30px 4px;color:#102b5a;overflow-wrap:anywhere}
.lebdce .lede{font-size:13px;color:var(--muted);margin:0}
.lebdce .notice{background:#ffe6a1;color:#704e0d;padding:12px 15px;border-radius:12px;margin:0 0 14px;line-height:1.6;font-weight:600;font-size:13px}
.lebdce .card{border:1px solid #e1ecfa;border-radius:17px;padding:17px;background:#fff;box-shadow:0 8px 22px rgba(19,62,118,.065)}
.lebdce .top{display:flex;align-items:center;justify-content:space-between;gap:9px}
.lebdce .cap{display:flex;align-items:center;gap:10px;font-size:17px;font-weight:800;line-height:1.3}
.lebdce .cap-icon{width:42px;height:42px;flex:0 0 42px;display:grid;place-items:center;background:#e5f2ff;border-radius:11px;color:#126be3;font-size:22px}
.lebdce .status{padding:6px 10px;border-radius:999px;background:#e0f6eb;color:#067653;font-weight:700;font-size:12px;max-width:46%;text-align:center;overflow-wrap:anywhere}
.lebdce .upcoming h3{font-size:18px;margin:13px 0 5px;font-weight:800;overflow-wrap:anywhere}
.lebdce .meta{color:var(--muted);font-size:13px;line-height:1.75;overflow-wrap:anywhere}
.lebdce .actions{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:15px}
.lebdce .button{display:flex;justify-content:center;align-items:center;text-align:center;min-height:49px;border-radius:11px;background:#10a76d;color:#fff!important;font-weight:800;font-size:14px;padding:9px;line-height:1.35}
.lebdce .inactive{background:#fff8ec;border:1px solid #f9ddb5;color:#a98758!important}
.lebdce h2{font-size:19px;color:#0c2c5c;font-weight:800;margin:21px 1px 12px}
.lebdce .menu{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}
.lebdce .menu-item{display:flex;align-items:center;gap:10px;min-width:0;min-height:83px;background:#fff;border:1px solid #dfebf9;border-radius:15px;padding:13px;box-shadow:0 5px 13px rgba(14,55,111,.045);color:#0a2958}
.lebdce .menu-item .ic{display:grid;place-items:center;flex:0 0 37px;width:37px;height:37px;border-radius:11px;background:#e7f3ff;color:#1272de;font-size:18px}
.lebdce .menu-item strong{display:block;font-size:14px;line-height:1.35;font-weight:800;overflow-wrap:anywhere}
.lebdce .menu-item small{display:block;font-size:12px;line-height:1.4;color:#7184a1;margin-top:3px;overflow-wrap:anywhere}
.lebdce .menu-item[aria-disabled]{cursor:default}
.lebdce .papers-section{display:none;margin-top:18px;scroll-margin-top:30px}
.lebdce .papers-section:target{display:block}
.lebdce .papers{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.lebdce .paper{background:#fff;border:1px solid #e1eafa;border-radius:12px;padding:13px;min-width:0}
.lebdce .paper strong{display:block;font-size:14px;margin-bottom:7px;overflow-wrap:anywhere}
.lebdce .paper small{display:block;color:#7184a1;font-size:12px;margin-bottom:8px}
.lebdce .paper a{display:block;padding:10px;background:#126be3;color:#fff;text-align:center;border-radius:8px;font-size:13px;font-weight:700}
.lebdce .empty{padding:13px;font-size:13px;color:#7184a1}
.lebdce .progress{margin-top:19px}
.lebdce .progress-title{display:flex;justify-content:space-between;align-items:center;font-size:18px;font-weight:800}
.lebdce .progress p{font-size:13px;color:#647c9d;line-height:1.7;margin:11px 0 0}
@media(max-width:600px){
 .lebdce{width:calc(100% - 22px);max-width:430px;margin-bottom:25px;padding:8px 0 25px;font-size:14px}
 .lebdce .hero{padding:14px 10px 16px}
 .lebdce h1{font-size:22px}
 .lebdce .hero:after{font-size:24px;right:8px}
 .lebdce .notice{font-size:13px;padding:11px 13px}
 .lebdce .card{padding:13px;border-radius:15px}
 .lebdce .cap{font-size:16px}
 .lebdce .cap-icon{height:39px;width:39px;flex-basis:39px}
 .lebdce .upcoming h3{font-size:17px}
 .lebdce .status{font-size:11px}
 .lebdce .actions{gap:7px}
 .lebdce .button{min-height:47px;font-size:13px;padding:8px 5px}
 .lebdce h2{font-size:18px;margin:19px 1px 11px}
 .lebdce .menu{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
 .lebdce .menu-item{min-height:87px;padding:11px 9px;gap:8px;align-items:flex-start}
 .lebdce .menu-item .ic{width:33px;height:33px;flex-basis:33px;font-size:16px}
 .lebdce .menu-item strong{font-size:13px;line-height:1.35}
 .lebdce .menu-item small{font-size:11px}
 .lebdce .papers{grid-template-columns:1fr}
 .lebdce .progress-title{font-size:16px}
}
@media(max-width:350px){
 .lebdce{width:calc(100% - 16px)}
 .lebdce .menu{gap:7px}
 .lebdce .menu-item{padding:9px 7px;gap:6px;min-height:88px}
 .lebdce .menu-item .ic{width:29px;height:29px;flex-basis:29px;font-size:14px}
 .lebdce .menu-item strong{font-size:12px}
 .lebdce .menu-item small{font-size:11px}
 .lebdce .button{font-size:12px}
}
/* Full-page mobile scroll fix: allow the entire Exam Progress section to appear. */
html:has(.lebdce),body:has(.lebdce){height:auto!important;min-height:100%!important;overflow-y:auto!important;overscroll-behavior-y:auto}
body:has(.lebdce) main,body:has(.lebdce) .content,body:has(.lebdce) .main-content,body:has(.lebdce) .page-content,body:has(.lebdce) .container-fluid{max-height:none!important;overflow-y:visible!important}
.lebdce{height:auto!important;max-height:none!important;min-height:0!important;overflow:visible!important;display:block!important;clear:both;padding-bottom:120px!important}
.lebdce .progress{display:block!important;position:relative;min-height:108px;overflow:visible!important;margin-bottom:30px}
.lebdce .progress p{display:block!important;white-space:normal!important;overflow-wrap:anywhere}
@media(max-width:600px){.lebdce{padding-bottom:160px!important}.lebdce .progress{margin-bottom:45px}.lebdce .menu-item{align-items:center}}

/* Critical: do not use Bootstrap .progress on outer card. Bootstrap forces 1rem height. */
.lebdce .lebd-progress-card{display:block!important;position:relative!important;height:auto!important;min-height:195px!important;max-height:none!important;overflow:hidden!important;margin:18px 0 30px!important;clear:both}
.lebdce .lebd-progress-body{min-height:140px;position:relative;float:none!important}
.lebdce .lebd-progress-stats,.lebdce .lebd-progress-ring{position:relative;float:none!important}
@media(max-width:600px){.lebdce .lebd-progress-card{min-height:155px!important;padding:15px!important}.lebdce .lebd-progress-body{min-height:100px}}
/* My Batch exam progress: screenshot-matched, responsive and scope isolated. */
.lebdce .lebd-progress-card{padding:20px!important;background:#fff;border-radius:20px;overflow:hidden!important}
.lebdce .lebd-progress-body{display:grid;grid-template-columns:140px minmax(0,1fr);gap:20px;align-items:center;margin-top:17px}
.lebdce .lebd-progress-ring{width:140px;height:140px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:conic-gradient(#1482f6 calc(var(--lebd-pct)*1%),#e5edf7 0);padding:13px;flex-shrink:0}
.lebdce .lebd-progress-ring>div{display:flex;align-items:center;justify-content:center;flex-direction:column;background:white;width:100%;height:100%;border-radius:50%;text-align:center}
.lebdce .lebd-progress-ring strong{font-size:31px;line-height:1.2;color:#0876e3;font-weight:800}
.lebdce .lebd-progress-ring small{font-size:12px;color:#6985a9;margin-top:3px}
.lebdce .lebd-progress-stats{min-width:0;width:100%}
.lebdce .lebd-progress-stats>div{display:flex;align-items:center;justify-content:space-between;gap:10px;min-height:41px;border-bottom:1px solid #dfe8f4;font-size:13px}
.lebdce .lebd-progress-stats>div:last-child{border-bottom:0}
.lebdce .lebd-progress-stats strong{color:#0875e1;font-size:15px}
.lebdce .lebd-progress-card .lebd-progress-note{font-size:11px;line-height:1.65;margin:16px 0 0;color:#7185a4}
@media(max-width:600px){.lebdce .lebd-progress-card{padding:15px!important;border-radius:16px}.lebdce .lebd-progress-body{grid-template-columns:100px minmax(0,1fr);gap:12px;margin-top:14px}.lebdce .lebd-progress-ring{width:100px;height:100px;padding:10px}.lebdce .lebd-progress-ring strong{font-size:24px}.lebdce .lebd-progress-ring small{font-size:9px}.lebdce .lebd-progress-stats>div{font-size:11px;min-height:32px}.lebdce .lebd-progress-stats strong{font-size:12px}.lebdce .lebd-progress-card .lebd-progress-note{font-size:10px}}
@media(max-width:340px){.lebdce .lebd-progress-body{grid-template-columns:84px minmax(0,1fr);gap:8px}.lebdce .lebd-progress-ring{width:84px;height:84px;padding:9px}.lebdce .lebd-progress-ring strong{font-size:20px}}
</style>
<main class="lebdce">
<a class="back" href="{{ route('students.my-batch.exam-index') }}">← My Batch-এ ফিরে যান</a>
<header class="hero"><h1>{{ $batch->name }}</h1><p class="lede">আপনার কেনা ব্যাচের পরীক্ষার ড্যাশবোর্ড</p></header>
<div class="notice">পরীক্ষার সময় ও অংশগ্রহণের অনুমতি মূল Exam System যাচাই করবে।</div>
<section class="card lebd-routine-feature" aria-label="Routine-based exam status">
@if(!empty($featuredRoutine))
  @php
    $isLive = $featuredRoutine['state'] === 'live';
    $canStart = !empty($featuredRoutine['paper_id']);
    $startISO = $featuredRoutine['starts']->toIso8601String();
    $endISO = $featuredRoutine['ends'] ? $featuredRoutine['ends']->toIso8601String() : '';
  @endphp
  <div class="lr-head">
    <div>
      <h2 class="lr-title">{{ $featuredRoutine['title'] }}</h2>
      <p class="lr-sub">{{ $isLive ? 'পরীক্ষা এখন চলছে' : 'পরীক্ষা শুরু হতে বাকি' }}</p>
    </div>
    <span class="lr-badge {{ $isLive ? 'lr-live':'lr-wait' }}">● {{ $isLive ? 'LIVE NOW' : 'Upcoming' }}</span>
  </div>
  @if($isLive)
    <div class="lr-detail">শেষ হবে: {{ $featuredRoutine['ends']->format('d M Y, h:i A') }}</div>
    @if($canStart)
      <a class="lr-cta" href="{{ route('students.check', $featuredRoutine['paper_id']) }}">▶ এক্সাম দিন</a>
    @else
      <div class="lr-notice">পরীক্ষার সময় চলছে; তবে অনুমোদিত Published প্রশ্নপত্রের সংযোগ পাওয়া যায়নি।</div>
    @endif
  @else
    <div class="lr-timer" data-lebd-exam-start="{{ $startISO }}" data-lebd-server-now="{{ now('Asia/Dhaka')->toIso8601String() }}" aria-live="off">
      <span><strong data-h>--</strong><small>ঘণ্টা</small></span><b>:</b>
      <span><strong data-m>--</strong><small>মিনিট</small></span><b>:</b>
      <span><strong data-s>--</strong><small>সেকেন্ড</small></span>
    </div>
    <div class="lr-foot">শুরু: {{ $featuredRoutine['starts']->format('d M Y, h:i A') }}</div>
    <p class="lr-note">পরীক্ষা শুরু হলে এবং প্রশ্নপত্র Published থাকলে এক্সাম দিন বাটন সক্রিয় হবে।</p>
  @endif
@else
  <div class="lr-head"><h2 class="lr-title">Upcoming Exam</h2><span class="lr-badge lr-wait">Schedule Pending</span></div>
  <p class="lr-note">এই ব্যাচ ও ডিপার্টমেন্টের ভবিষ্যৎ রুটিন পাওয়া যায়নি।</p>
@endif
</section>
<style>
/* LEBD-05 routine status card; scoped to this exam dashboard */
.lebdce .lebd-routine-feature{padding:23px!important;border-radius:22px;background:#fff;box-shadow:0 14px 35px rgba(28,87,160,.10);height:auto!important;max-height:none!important;overflow:visible!important}
.lebdce .lr-head{display:flex;align-items:start;justify-content:space-between;gap:14px}
.lebdce .lr-title{color:#112d63;font-size:30px!important;font-weight:850;line-height:1.25;margin:0 0 8px!important}
.lebdce .lr-sub{color:#687d9e;font-size:18px;line-height:1.5;margin:0}
.lebdce .lr-badge{white-space:nowrap;padding:9px 15px;border-radius:99px;font-size:14px;font-weight:800}
.lebdce .lr-live{color:#058448;background:#dcfae9}.lebdce .lr-wait{color:#078b46;background:#dff9ec}
.lebdce .lr-detail{margin:25px 0 16px;font-size:15px;color:#506987}
.lebdce .lr-cta{display:block;background:linear-gradient(110deg,#009b85,#25d671);color:#fff!important;text-align:center;font-weight:850;font-size:22px;line-height:1.4;padding:17px 12px;border-radius:14px;box-shadow:0 8px 20px rgba(14,184,114,.18)}
.lebdce .lr-notice{background:#fff7e8;color:#8e6734;padding:13px;border-radius:10px;font-size:13px;margin-top:16px}
.lebdce .lr-timer{display:flex;align-items:center;justify-content:center;gap:13px;margin:20px 0 17px}
.lebdce .lr-timer>span{display:flex;flex-direction:column;align-items:center;justify-content:center;min-width:88px;min-height:95px;border:1px solid #e0e9fa;border-radius:16px;background:#f4f8ff;padding:9px}
.lebdce .lr-timer>span:nth-of-type(2){background:#fff8f2}.lebdce .lr-timer>span:nth-of-type(3){background:#f8f4ff}
.lebdce .lr-timer strong{font-size:37px;line-height:1.1;color:#0964c9}.lebdce .lr-timer small{font-size:12px;color:#7e8ba4;margin-top:5px}.lebdce .lr-timer>b{font-size:25px;color:#98a3b5}
.lebdce .lr-foot{text-align:center;font-size:13px;color:#6c81a0}
.lebdce .lr-note{text-align:center;font-size:12px;color:#768aa8;line-height:1.5;margin:12px 0 0}
@media(max-width:600px){.lebdce .lebd-routine-feature{padding:14px!important;border-radius:15px}.lebdce .lr-title{font-size:20px!important}.lebdce .lr-sub{font-size:13px}.lebdce .lr-badge{font-size:10px;padding:6px 9px}.lebdce .lr-timer{gap:5px;margin:14px 0}.lebdce .lr-timer>span{min-width:0;flex:1;min-height:75px;padding:6px 2px;border-radius:10px}.lebdce .lr-timer strong{font-size:25px}.lebdce .lr-timer small{font-size:10px}.lebdce .lr-timer>b{font-size:16px}.lebdce .lr-cta{font-size:17px;padding:13px 8px}.lebdce .lr-detail{font-size:12px;margin:15px 0}.lebdce .lr-foot,.lebdce .lr-note{font-size:11px}}
</style>
<script>
(function(){
  const el=document.querySelector('.lebdce [data-lebd-exam-start]');if(!el)return;
  const target=Date.parse(el.dataset.lebdExamStart),serverAt=Date.parse(el.dataset.lebdServerNow),clientAt=Date.now();
  if(!Number.isFinite(target)||!Number.isFinite(serverAt))return;
  function refresh(){
    const secs=Math.max(0,Math.ceil((target-(serverAt+Date.now()-clientAt))/1000));
    const h=Math.floor(secs/3600),m=Math.floor((secs%3600)/60),s=secs%60;
    el.querySelector('[data-h]').textContent=String(h).padStart(2,'0');
    el.querySelector('[data-m]').textContent=String(m).padStart(2,'0');
    el.querySelector('[data-s]').textContent=String(s).padStart(2,'0');
    if(secs===0)window.location.reload();
  }
  refresh();setInterval(refresh,1000);
})();
</script>
<h2>Exam Menu</h2>
<div class="menu">
  <a class="menu-item" href="#lebd-batch-routine"><span class="ic">▦</span><span><strong>Routine</strong><small>আমার ব্যাচ ও ডিপার্টমেন্ট</small></span></a>
  <div class="menu-item" aria-disabled="true"><span class="ic">▣</span><span><strong>Result</strong><small>Route যাচাই বাকি</small></span></div>
  <a class="menu-item" href="#lebd-my-batch-papers"><span class="ic">▤</span><span><strong>All Exam Paper</strong><small>এই ব্যাচের পরীক্ষাসমূহ</small></span></a>
  <div class="menu-item" aria-disabled="true"><span class="ic">▧</span><span><strong>Missing Exam List</strong><small>Coming soon</small></span></div>
  <a class="menu-item" href="#lebd-batch-pdfs"><span class="ic">▤</span><span><strong>Exam PDF</strong><small>প্রকাশিত PDF রেকর্ড</small></span></a>
  <div class="menu-item" aria-disabled="true"><span class="ic">◉</span><span><strong>Merit List</strong><small>Coming soon</small></span></div>
  <div class="menu-item" aria-disabled="true"><span class="ic">◎</span><span><strong>Wrong &amp; Unanswered</strong><small>Coming soon</small></span></div>
  <div class="menu-item" aria-disabled="true"><span class="ic">✉</span><span><strong>Complain</strong><small>Route যাচাই বাকি</small></span></div>
</div>
<section class="papers-section" id="lebd-my-batch-papers"><h2>All Exam Paper</h2>
<div class="papers">
 @forelse($papers as $paper)
 <article class="paper"><strong>{{ $paper->name }}</strong><small>{{ $paper->status }}</small><a href="{{ route('students.check', $paper->id) }}">পরীক্ষার বিস্তারিত / অংশগ্রহণ →</a></article>
 @empty
 <div class="card empty">এই ব্যাচে আপনার জন্য কোনো পরীক্ষা প্রকাশ করা হয়নি।</div>
 @endforelse
</div></section>

<style>
/* Read-only routine and PDF sections; no route/controller state mutations. */
.lebdce .lebd-resource-section{margin-top:20px;scroll-margin-top:16px}
.lebdce .lebd-resource-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.lebdce .lebd-resource-item{background:#fff;border:1px solid #dae7fa;border-radius:12px;padding:12px;min-width:0}
.lebdce .lebd-resource-item strong{display:block;font-size:13px;color:#12386b;overflow-wrap:anywhere}
.lebdce .lebd-resource-item p{margin:5px 0 0;font-size:12px;color:#667e9c;overflow-wrap:anywhere}
.lebdce .lebd-resource-note{font-size:11px;line-height:1.55;color:#6a7894;margin:9px 0}
@media(max-width:600px){.lebdce .lebd-resource-list{grid-template-columns:1fr}.lebdce .lebd-resource-item{padding:11px}}
</style>
<section class="lebd-resource-section" id="lebd-batch-routine">
<h2>আমার ব্যাচের রুটিন</h2>
<div class="lebd-resource-list">
@forelse($routineRows as $row)
<div class="lebd-resource-item"><strong>{{ $row->paper_name ?? ('Exam #'.$row->id) }}</strong>
<p>শুরু: {{ $row->exam_at ?? '—' }}</p>
@if(!empty($row->exam_end_at))<p>শেষ: {{ $row->exam_end_at }}</p>@endif
</div>
@empty <div class="card empty">এই ব্যাচের আপনার Department-এর প্রকাশিত Routine পাওয়া যায়নি।</div>
@endforelse
</div>
<p class="lebd-resource-note">পরীক্ষা দিতে All Exam Paper থেকে পরীক্ষাটি খুলুন। বিদ্যমান Exam System সময় ও অনুমতি যাচাই করবে।</p>
</section>
<section class="lebd-resource-section" id="lebd-batch-pdfs">
<h2>Exam PDF / Published Resources</h2>
<div class="lebd-resource-list">
@forelse($pdfRecords as $pdf)
<div class="lebd-resource-item"><strong>{{ $pdf->name ?? $pdf->title ?? ('Resource #'.$pdf->id) }}</strong>
<p>Published</p><p>ফাইলের নির্ভরযোগ্য Download Route যাচাই বাকি।</p></div>
@empty <div class="card empty">এই ব্যাচ ও Department-এর কোনো প্রকাশিত PDF Resource পাওয়া যায়নি।</div>
@endforelse
</div>
</section>

<section class="card lebd-progress-card">
 <div class="progress-title"><strong>My Exam Progress</strong><span aria-hidden="true" style="color:#1173da">◉</span></div>
 @php
  $progressTotal = (int)($progress['total'] ?? $papers->count());
  $progressCompleted = (int)($progress['completed'] ?? 0);
  $progressRemaining = max(0, $progressTotal - $progressCompleted);
  $progressPercent = $progressTotal > 0 ? (int)round(($progressCompleted / $progressTotal) * 100) : 0;
 @endphp
 <div class="lebd-progress-body">
  <div class="lebd-progress-ring" role="img" aria-label="{{ $progressPercent }}% completed" style="--lebd-pct:{{ $progressPercent }}">
    <div><strong>{{ $progressPercent }}%</strong><small>Completed</small></div>
  </div>
  <div class="lebd-progress-stats">
   <div><span>Total Exams</span><strong>{{ $progressTotal }}</strong></div>
   <div><span>Completed</span><strong>{{ $progressCompleted }}</strong></div>
   <div><span>Remaining</span><strong>{{ $progressRemaining }}</strong></div>
  </div>
 </div>
 <p class="lebd-progress-note">এই হিসাব এই ব্যাচে আপনার জন্য প্রকাশিত পরীক্ষার তালিকা এবং রেকর্ড করা অংশগ্রহণের ভিত্তিতে তৈরি।</p>
</section>
</main>
@endsection
