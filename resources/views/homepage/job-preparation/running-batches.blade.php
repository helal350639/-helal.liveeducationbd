@extends('homepage')

@section('title', 'চলমান সকল ব্যাচ — Live Education BD')

@section('content')
<style>
/* LEBD Fix135: scoped exclusively to the public Running Batches list. */
.lebd-running135{background:#fff;min-height:45vh;padding:20px 12px 46px;font-family:inherit}
.lebd-running135 *{box-sizing:border-box}
.lebd-running135__wrap{max-width:1190px;margin:0 auto}
.lebd-running135__heading{padding:16px 12px 15px;background:linear-gradient(110deg,#0960d4,#196ad0);color:#fff;text-align:center;border-radius:14px;box-shadow:0 7px 15px #145ab322;margin-bottom:18px}
.lebd-running135__heading h1{font-size:24px;line-height:1.3;font-weight:800;margin:0 0 3px;color:#fff}
.lebd-running135__heading p{font-size:12px;line-height:1.4;margin:0;color:#fff}
.lebd-running135__grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;align-items:stretch}
.lebd-running135__card{min-width:0;display:flex;flex-direction:column;overflow:hidden;border:1px solid #d8e4f4;border-radius:12px;background:#fff;box-shadow:0 5px 13px #0a2b5912}
.lebd-running135__media{position:relative;width:100%;aspect-ratio:16/8;background:#edf5fd;overflow:hidden}
.lebd-running135__media img{display:block;width:100%;height:100%;object-fit:contain}
.lebd-running135__badge{position:absolute;left:6px;top:6px;background:#00aa62;color:#fff;font-size:9px;line-height:1.2;border-radius:5px;padding:4px 6px;font-weight:700}
.lebd-running135__body{display:flex;flex-direction:column;flex:1;min-width:0;padding:10px}
.lebd-running135__name{font-size:14px;line-height:1.4;font-weight:750;margin:0 0 7px;color:#0a316f;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:39px;overflow-wrap:anywhere}
.lebd-running135__course{font-size:10px;color:#506c8c;margin:0 0 7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lebd-running135__dates{display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-top:auto;margin-bottom:8px}
.lebd-running135__date{text-align:center;background:#f0f6ff;border-radius:6px;padding:5px 2px;min-width:0}
.lebd-running135__date span{display:block;font-size:9px;line-height:1.2;color:#65758c}
.lebd-running135__date strong{display:block;margin-top:2px;font-size:10px;line-height:1.3;color:#0e3975;overflow-wrap:anywhere}
.lebd-running135__action{display:block;text-align:center;text-decoration:none!important;background:#0867d8;color:#fff!important;border-radius:6px;padding:8px 5px;font-size:11px;font-weight:750;line-height:1.3;transition:background .15s}
.lebd-running135__action:hover,.lebd-running135__action:focus{background:#034caa;color:#fff!important}
.lebd-running135__empty{background:#f4f8ff;padding:30px;border-radius:12px;text-align:center;color:#304a6d;border:1px solid #d7e5f8}
.lebd-running135__pages{margin-top:20px}
@media(max-width:900px){.lebd-running135__grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:600px){
.lebd-running135{padding:12px 8px 28px}
.lebd-running135__heading{padding:12px 7px;margin-bottom:12px;border-radius:10px}
.lebd-running135__heading h1{font-size:19px}.lebd-running135__heading p{font-size:10px}
/* Fix136: mobile card grid 20% narrower, still exactly two cards per row. */
.lebd-running135__grid{width:80%;max-width:100%;margin-inline:auto;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.lebd-running135__card{border-radius:9px}
.lebd-running135__media{aspect-ratio:16/8}
.lebd-running135__badge{font-size:7px;top:4px;left:4px;padding:3px 5px}
.lebd-running135__body{padding:7px}
.lebd-running135__name{font-size:11px;min-height:30px;margin-bottom:5px;line-height:1.35}
.lebd-running135__course{font-size:8px;margin-bottom:6px}
.lebd-running135__dates{gap:4px;margin-bottom:6px}
.lebd-running135__date{padding:4px 1px}
.lebd-running135__date span{font-size:7px}.lebd-running135__date strong{font-size:8px}
.lebd-running135__action{font-size:9px;padding:7px 2px;border-radius:5px}
}
/* Fix137 — mobile app-like Bengali typography; desktop completely unchanged. */
@media(max-width:600px){
.lebd-running135{font-family:'Noto Sans Bengali','Hind Siliguri',system-ui,sans-serif;-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility}
.lebd-running135__heading h1{font-family:inherit;font-weight:750}
.lebd-running135__card{font-family:inherit}
.lebd-running135__body{padding:6px 5px 7px}
.lebd-running135__name{font-family:inherit;font-size:10px;font-weight:700;line-height:1.52;letter-spacing:0;min-height:31px;margin:0 0 4px;word-break:normal;overflow-wrap:anywhere}
.lebd-running135__course{font-family:inherit;font-size:8px;line-height:1.4;font-weight:450;margin-bottom:5px}
.lebd-running135__date{padding:4px 1px}
.lebd-running135__date span{font-family:inherit;font-size:8px;line-height:1.4;font-weight:500}
.lebd-running135__date strong{font-family:'Poppins','Noto Sans Bengali',sans-serif;font-size:8px;line-height:1.4;font-weight:650;white-space:normal}
.lebd-running135__action{font-family:inherit;font-size:9px;line-height:1.4;font-weight:700;padding:6px 2px}
.lebd-running135__badge{font-family:inherit;font-size:7px;font-weight:700}
}
@media(max-width:350px){.lebd-running135__body{padding:5px 4px}.lebd-running135__grid{gap:6px}.lebd-running135__name{font-size:9.5px}.lebd-running135__date strong{font-size:7.5px}.lebd-running135__action{font-size:8.5px}}
/* LEBD Fix138: visual rhythm only. No data, card size, or footer changes. */
.lebd-running135{
  min-height:max(610px,calc(100vh - 240px));
  padding-top:34px;
  padding-bottom:92px;
}
.lebd-running135__heading{margin-bottom:30px}
.lebd-running135__grid{row-gap:20px}
@media(max-width:600px){
  .lebd-running135{
    min-height:max(510px,calc(100svh - 160px));
    padding-top:20px;
    padding-bottom:70px;
  }
  .lebd-running135__heading{margin-bottom:23px}
  .lebd-running135__grid{row-gap:14px}
}
</style>
<section class="lebd-running135" aria-label="চলমান সকল ব্যাচ">
  <div class="lebd-running135__wrap">
    <header class="lebd-running135__heading">
      <h1>🎓 চলমান সকল ব্যাচ</h1>
      <p>রেজিস্ট্রেশনের মেয়াদ থাকা সকল সক্রিয় ব্যাচ এখানে দেখানো হচ্ছে</p>
    </header>
    @forelse($batches as $batch)
      @if($loop->first)<div class="lebd-running135__grid">@endif
      <article class="lebd-running135__card">
        <div class="lebd-running135__media">
          <img src="{{ $batch->banner ? asset($batch->banner) : asset('img/course.png') }}" alt="{{ $batch->name }}" loading="lazy">
          <span class="lebd-running135__badge">● চলমান ব্যাচ</span>
        </div>
        <div class="lebd-running135__body">
          <h2 class="lebd-running135__name" title="{{ $batch->name }}">{{ $batch->name }}</h2>
          @if($batch->course)
            <p class="lebd-running135__course" title="{{ $batch->course->name }}">📘 কোর্স: {{ $batch->course->name }}</p>
          @endif
          <div class="lebd-running135__dates">
            <div class="lebd-running135__date"><span>শুরু</span><strong>{{ $batch->start_at ? \Carbon\Carbon::parse($batch->start_at)->format('d M Y') : '—' }}</strong></div>
            <div class="lebd-running135__date"><span>শেষ</span><strong>{{ $batch->end_at ? \Carbon\Carbon::parse($batch->end_at)->format('d M Y') : '—' }}</strong></div>
          </div>
          <a class="lebd-running135__action" href="{{ route('home.course.show', $batch->id) }}">বিস্তারিত দেখুন →</a>
        </div>
      </article>
      @if($loop->last)</div>@endif
    @empty
      <div class="lebd-running135__empty">বর্তমানে কোনো চলমান ব্যাচ পাওয়া যায়নি।</div>
    @endforelse
    @if(method_exists($batches, 'links'))
      <div class="lebd-running135__pages">{{ $batches->links() }}</div>
    @endif
  </div>
</section>
@endsection
