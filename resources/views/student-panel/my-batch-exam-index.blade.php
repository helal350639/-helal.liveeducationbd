@extends('student')
@section('title', 'My Batch — আমার ব্যাচের পরীক্ষা')
@section('content')
<style>
/* LEBD-05 My Batch: scoped presentation ONLY. All controller queries and routes preserved. */
.lebd-mb-v2,.lebd-mb-v2 *{box-sizing:border-box}
.lebd-mb-v2{max-width:1150px;width:calc(100% - 32px);margin:16px auto 56px;color:#102b5c;font-family:inherit}
.lebd-mb-v2 a{text-decoration:none}
.lebd-mb-v2 .crumb{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#0871dc;margin-bottom:14px}
.lebd-mb-v2 .crumb:hover{text-decoration:underline}
.lebd-mb-v2 .hero{position:relative;overflow:hidden;display:flex;align-items:center;min-height:188px;border-radius:23px;padding:29px 37px;background:linear-gradient(106deg,#083c9e 0%,#0764d4 51%,#13a7e8 100%);box-shadow:0 12px 30px rgba(21,87,173,.14);color:white}
.lebd-mb-v2 .hero:before{content:"";position:absolute;width:340px;height:340px;border:2px solid #ffffff19;border-radius:50%;right:-65px;top:-190px}
.lebd-mb-v2 .hero:after{content:"";position:absolute;width:240px;height:240px;border-radius:50%;background:#ffffff0b;right:15%;bottom:-160px}
.lebd-mb-v2 .hero-text{position:relative;z-index:1;max-width:75%}
.lebd-mb-v2 .eyebrow{font-size:12px;font-weight:700;letter-spacing:.6px;opacity:.95}
.lebd-mb-v2 .hero h1{font-size:clamp(23px,3vw,34px);color:#fff;line-height:1.35;font-weight:850;margin:8px 0}
.lebd-mb-v2 .hero h1 em{font-style:normal;color:#fff0a6}
.lebd-mb-v2 .hero p{font-size:14px;line-height:1.6;margin:0;color:#f0f7ff}
.lebd-mb-v2 .hero-decoration{position:absolute;right:42px;top:50%;transform:translateY(-50%);font-size:98px;color:#fff;opacity:.25;pointer-events:none}
.lebd-mb-v2 .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:19px;margin-top:22px}
.lebd-mb-v2 .card{min-width:0;display:flex;gap:20px;align-items:center;border:1px solid #d5e5fa;background:linear-gradient(135deg,#fff,#f7fbff);border-radius:21px;padding:22px;min-height:176px;box-shadow:0 8px 25px rgba(20,75,140,.065);transition:transform .18s ease,box-shadow .18s ease}
.lebd-mb-v2 .card:hover{transform:translateY(-2px);box-shadow:0 12px 27px rgba(20,75,140,.11)}
.lebd-mb-v2 .symbol{width:96px;height:107px;flex:0 0 96px;display:flex;align-items:center;justify-content:center;border-radius:19px;background:#e8f3ff;color:#187bdd;font-size:41px}
.lebd-mb-v2 .card-body{min-width:0;flex:1}
.lebd-mb-v2 .card h2{font-size:19px;font-weight:850;line-height:1.3;margin:0 0 8px;color:#092858;overflow-wrap:anywhere}
.lebd-mb-v2 .card p{font-size:12px;line-height:1.6;margin:0 0 13px;color:#5c769e}
.lebd-mb-v2 .btn{display:flex;align-items:center;justify-content:space-between;gap:8px;border-radius:12px;min-height:40px;background:linear-gradient(95deg,#e5f0ff,#d7eaff);color:#0667cb!important;padding:7px 8px 7px 14px;font-size:13px;font-weight:850;white-space:normal}
.lebd-mb-v2 .btn .round{width:29px;height:29px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:#096edf;color:white;flex-shrink:0}
.lebd-mb-v2 .card:nth-child(6n+2){border-color:#cbeee2;background:linear-gradient(135deg,#fff,#f1fff9)}
.lebd-mb-v2 .card:nth-child(6n+2) .symbol{background:#def7ed;color:#079968}
.lebd-mb-v2 .card:nth-child(6n+2) .btn{background:linear-gradient(95deg,#e4f9ef,#d4f6e8);color:#06895b!important}
.lebd-mb-v2 .card:nth-child(6n+2) .round{background:#089b6c}
.lebd-mb-v2 .card:nth-child(6n+3){border-color:#f7d9ea;background:linear-gradient(135deg,#fff,#fff6fb)}
.lebd-mb-v2 .card:nth-child(6n+3) .symbol{background:#fce2f2;color:#d91a79}
.lebd-mb-v2 .card:nth-child(6n+3) .btn{background:#fce5f2;color:#bb1368!important}
.lebd-mb-v2 .card:nth-child(6n+3) .round{background:#d91a79}
.lebd-mb-v2 .card:nth-child(6n+4){border-color:#f8e4ca;background:linear-gradient(135deg,#fff,#fffcf5)}
.lebd-mb-v2 .card:nth-child(6n+4) .symbol{background:#fff0d7;color:#f18b0a}
.lebd-mb-v2 .card:nth-child(6n+4) .btn{background:#fff0d9;color:#d06b09!important}
.lebd-mb-v2 .card:nth-child(6n+4) .round{background:#f18b0a}
.lebd-mb-v2 .card:nth-child(6n+5){border-color:#ceeee8;background:linear-gradient(135deg,#fff,#f3fffb)}
.lebd-mb-v2 .card:nth-child(6n+5) .symbol{background:#ddf9ed;color:#07996b}
.lebd-mb-v2 .card:nth-child(6n+5) .btn{background:#e2f9ee;color:#07875e!important}
.lebd-mb-v2 .card:nth-child(6n+5) .round{background:#07996b}
.lebd-mb-v2 .card:nth-child(6n+6){border-color:#e7d8ff;background:linear-gradient(135deg,#fff,#fbf7ff)}
.lebd-mb-v2 .card:nth-child(6n+6) .symbol{background:#efe4ff;color:#7539cc}
.lebd-mb-v2 .card:nth-child(6n+6) .btn{background:#eee4ff;color:#7139b5!important}
.lebd-mb-v2 .card:nth-child(6n+6) .round{background:#7539cc}
.lebd-mb-v2 .empty{grid-column:1/-1;padding:25px;border:1px solid #d9e6f7;border-radius:17px;background:white;text-align:center;color:#55739a}
@media(max-width:700px){
.lebd-mb-v2{width:calc(100% - 24px);margin:12px auto 34px}
.lebd-mb-v2 .hero{min-height:158px;padding:24px 20px;border-radius:19px}
.lebd-mb-v2 .hero-text{max-width:90%}
.lebd-mb-v2 .hero h1{font-size:22px}
.lebd-mb-v2 .hero p{font-size:12px}
.lebd-mb-v2 .hero-decoration{right:12px;font-size:67px;opacity:.15}
.lebd-mb-v2 .grid{grid-template-columns:1fr;gap:12px;margin-top:14px}
.lebd-mb-v2 .card{padding:15px;gap:13px;min-height:128px;border-radius:17px}
.lebd-mb-v2 .symbol{width:74px;height:86px;flex-basis:74px;font-size:34px;border-radius:15px}
.lebd-mb-v2 .card h2{font-size:16px;margin-bottom:4px}
.lebd-mb-v2 .card p{font-size:11px;margin-bottom:8px}
.lebd-mb-v2 .btn{font-size:12px;min-height:35px;padding:5px 5px 5px 10px}
.lebd-mb-v2 .btn .round{height:27px;width:27px}
}
@media(max-width:360px){.lebd-mb-v2 .card{padding:11px;gap:10px}.lebd-mb-v2 .symbol{width:61px;flex-basis:61px;height:75px;font-size:28px}.lebd-mb-v2 .card h2{font-size:15px}.lebd-mb-v2 .hero h1{font-size:20px}}

/* LEBD-05 FIX: four compact cards per desktop row, two per phone row. */
.lebd-mb-v2 .grid{grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:18px}
.lebd-mb-v2 .card{display:flex;flex-direction:column;align-items:center;text-align:center;justify-content:flex-start;gap:8px;min-height:186px;padding:15px 10px 12px;border-radius:16px}
.lebd-mb-v2 .symbol{width:56px;height:56px;flex:0 0 56px;border-radius:14px;font-size:27px}
.lebd-mb-v2 .card-body{width:100%;min-width:0;display:flex;flex:1;flex-direction:column;align-items:stretch;padding:0!important}
.lebd-mb-v2 .card h2{font-size:15px;line-height:1.35;font-weight:800;margin:0 0 4px;min-height:20px;overflow-wrap:anywhere}
.lebd-mb-v2 .card p{font-size:10px;line-height:1.35;margin:0 0 8px;color:#5c769e;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:27px}
.lebd-mb-v2 .btn{margin-top:auto;width:100%;border-radius:9px;min-height:34px;font-size:11px;line-height:1.25;padding:5px 5px 5px 8px;gap:3px;text-align:center}
.lebd-mb-v2 .btn span:first-child{flex:1;min-width:0}
.lebd-mb-v2 .btn .round{width:22px;height:22px;font-size:10px;flex:0 0 22px}
@media (min-width:701px) and (max-width:1050px){.lebd-mb-v2 .grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (max-width:700px){
 .lebd-mb-v2{width:calc(100% - 20px);margin:8px auto 30px}
 .lebd-mb-v2 .hero{min-height:112px;padding:15px 14px;border-radius:15px}
 .lebd-mb-v2 .hero-text{max-width:95%}
 .lebd-mb-v2 .hero h1{font-size:18px;line-height:1.3;margin:6px 0}
 .lebd-mb-v2 .hero p{font-size:10px;line-height:1.45}
 .lebd-mb-v2 .eyebrow{font-size:10px}
 .lebd-mb-v2 .grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin-top:11px}
 .lebd-mb-v2 .card{padding:10px 7px 8px;gap:7px;min-height:159px;border-radius:13px}
 .lebd-mb-v2 .symbol{width:44px;height:44px;flex:0 0 44px;border-radius:11px;font-size:22px}
 .lebd-mb-v2 .card h2{font-size:12px;line-height:1.3;margin:0 0 4px;min-height:16px}
 .lebd-mb-v2 .card p{font-size:9px;line-height:1.35;min-height:24px;margin-bottom:6px}
 .lebd-mb-v2 .btn{min-height:30px;border-radius:8px;font-size:9px;padding:4px 4px 4px 5px;gap:1px}
 .lebd-mb-v2 .btn .round{width:19px;height:19px;flex-basis:19px;font-size:9px}
}
@media (max-width:360px){
 .lebd-mb-v2 .grid{gap:7px}
 .lebd-mb-v2 .card{padding:9px 5px 7px;min-height:155px}
 .lebd-mb-v2 .card h2{font-size:11px}
 .lebd-mb-v2 .card p{font-size:8.5px}
 .lebd-mb-v2 .btn{font-size:8px}
}
</style>
<main class="lebd-mb-v2">
 <a class="crumb" href="{{ route('students.dashboard') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Student Dashboard</a>
 <header class="hero">
  <div class="hero-text">
   <div class="eyebrow">LIVE EDUCATION BD • Student</div>
   <h1>My Batch — <em>আমার ব্যাচের পরীক্ষা</em></h1>
   <p>নিচের ব্যাচ গুলো আপনে কিনেছেন। পরীক্ষা/pdf/রেজাল্ট দেখতে ব্যাচের পরীক্ষা দেখুন বাটুনে  ক্লিক করুন</p>
  </div>
  <i class="fa fa-graduation-cap hero-decoration" aria-hidden="true"></i>
 </header>
 <section class="grid" aria-label="আমার ব্যাচসমূহ">
  @forelse($batches as $batch)
   <article class="card">
    <div class="symbol" aria-hidden="true"><i class="fa fa-graduation-cap"></i></div>
    <div class="card-body">
     <h2>{{ $batch->name }}</h2>
     <p>এই ব্যাচের সকল পরীক্ষা দেখতে নিচের বাটুনে ক্লিক করুন।</p>
     <a class="btn" href="{{ route('students.batch.purchased-exams', ['batch'=>$batch->id]) }}">
      <span>ব্যাচের পরীক্ষা দেখুন</span><span class="round" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
     </a>
    </div>
   </article>
  @empty
   <div class="empty">আপনার অ্যাকাউন্টে এখনো কোনো Enrolled Batch পাওয়া যায়নি।</div>
  @endforelse
 </section>
</main>
@endsection
