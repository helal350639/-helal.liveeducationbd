@extends('homepage')

@section('title', $course->name . ' — চলমান ব্যাচ')

@section('content')
<style>
/* Fix230: one authoritative stylesheet; card sizes do not depend on legacy grids. */
.lebd-cbl-page{background:#f6f9fe;min-height:60vh;padding:20px 14px 45px;color:#14335f}
.lebd-cbl-shell{max-width:1160px;margin:auto}
.lebd-cbl-back{display:inline-flex;gap:6px;align-items:center;margin-bottom:13px;color:#075ecb;text-decoration:none;font-weight:700;font-size:13px}
.lebd-cbl-heading{display:flex;align-items:center;gap:13px;padding:13px;background:white;border:1px solid #e0ebfa;border-radius:13px}
.lebd-cbl-course-image{width:76px;height:76px;flex:0 0 76px;object-fit:contain;border-radius:9px;background:#f4f8ff}
.lebd-cbl-title{font-size:22px;margin:0 0 5px;color:#0c2c63;font-weight:800}
.lebd-cbl-desc{font-size:12px;line-height:1.5;color:#526b8d;margin:0}
.lebd-cbl-subtitle{display:flex;align-items:center;gap:7px;margin:19px 0 12px;font-size:19px;font-weight:800;color:#13386a}
.lebd-cbl-subtitle i{color:#09a56c}
/* Flex is deliberate: external styles previously forced the grid to one column. */
.lebd-cbl-page .lebd-cbl-grid{display:flex!important;flex-wrap:wrap!important;align-items:stretch!important;gap:12px!important;grid-template-columns:none!important;width:100%!important}
.lebd-cbl-page .lebd-cbl-grid>.lebd-cbl-card{box-sizing:border-box!important;display:flex!important;flex-direction:column!important;flex:0 0 calc((100% - 36px)/4)!important;max-width:calc((100% - 36px)/4)!important;min-width:0!important;align-self:stretch!important;margin:0!important;padding:0!important;float:none!important;overflow:hidden!important;background:#fff!important;border:1px solid #dfe9f6!important;border-radius:11px!important;box-shadow:0 3px 12px rgba(18,54,90,.07)!important}
.lebd-cbl-page .lebd-cbl-grid>.lebd-cbl-card>.lebd-cbl-banner{box-sizing:border-box!important;display:block!important;width:100%!important;min-width:0!important;max-width:100%!important;height:123px!important;min-height:0!important;max-height:123px!important;aspect-ratio:auto!important;object-fit:contain!important;object-position:center!important;background:#eaf2fc!important;margin:0!important;padding:0!important;border:0!important}
.lebd-cbl-page .lebd-cbl-card-body{box-sizing:border-box!important;padding:9px!important;display:flex!important;flex:1!important;flex-direction:column!important;gap:5px!important;min-width:0!important;min-height:0!important}
.lebd-cbl-page .lebd-cbl-card h3{color:#123467!important;font-size:13px!important;line-height:1.35!important;min-height:35px!important;max-height:35px!important;margin:0!important;font-weight:750!important;display:-webkit-box!important;-webkit-box-orient:vertical!important;-webkit-line-clamp:2!important;overflow:hidden!important;overflow-wrap:anywhere!important}
.lebd-cbl-page .lebd-cbl-date{font-size:10px!important;color:#657d9a!important;line-height:1.4!important;display:flex!important;flex-direction:column!important;gap:2px!important}
.lebd-cbl-page .lebd-cbl-date>div{white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;padding:0!important;margin:0!important;background:none!important}
.lebd-cbl-page .lebd-cbl-btn{margin-top:auto!important;display:block!important;padding:7px 4px!important;text-align:center!important;background:#0967de!important;color:#fff!important;border-radius:7px!important;font-size:10px!important;line-height:1.25!important;font-weight:800!important;text-decoration:none!important;white-space:normal!important}
.lebd-cbl-page .lebd-cbl-empty{width:100%;border:1px dashed #bbd1eb;background:#fff;padding:20px;border-radius:12px;text-align:center;color:#637791}
@media(max-width:850px){.lebd-cbl-page .lebd-cbl-grid>.lebd-cbl-card{flex-basis:calc((100% - 12px)/2)!important;max-width:calc((100% - 12px)/2)!important}}
@media(max-width:600px){
 .lebd-cbl-page{padding:10px 7px 25px}
 .lebd-cbl-back{font-size:11px;margin-bottom:9px}
 .lebd-cbl-heading{padding:8px;gap:8px}
 .lebd-cbl-course-image{width:46px;height:46px;flex-basis:46px}
 .lebd-cbl-title{font-size:14px;line-height:1.25;margin-bottom:3px}
 .lebd-cbl-desc{font-size:10px;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
 .lebd-cbl-subtitle{font-size:15px;margin:12px 0 8px;gap:5px}
 .lebd-cbl-page .lebd-cbl-grid{gap:8px!important}
 .lebd-cbl-page .lebd-cbl-grid>.lebd-cbl-card{flex:0 0 calc((100% - 8px)/2)!important;max-width:calc((100% - 8px)/2)!important;border-radius:9px!important}
 .lebd-cbl-page .lebd-cbl-grid>.lebd-cbl-card>.lebd-cbl-banner{height:81px!important;max-height:81px!important;object-fit:contain!important}
 .lebd-cbl-page .lebd-cbl-card-body{padding:6px!important;gap:4px!important}
 .lebd-cbl-page .lebd-cbl-card h3{font-size:10.5px!important;line-height:1.26!important;min-height:27px!important;max-height:27px!important}
 .lebd-cbl-page .lebd-cbl-date{font-size:8.5px!important;line-height:1.25!important;gap:1px!important}
 .lebd-cbl-page .lebd-cbl-btn{font-size:9px!important;padding:6px 2px!important;min-height:26px!important}
}
@media(max-width:350px){.lebd-cbl-page .lebd-cbl-card h3{font-size:9.5px!important}.lebd-cbl-page .lebd-cbl-date{font-size:8px!important}}
</style>
<div class="lebd-cbl-page">
 <div class="lebd-cbl-shell">
  <a class="lebd-cbl-back" href="{{ route('homepage.courses') }}"><i class="fa fa-arrow-left"></i> সকল কোর্সে ফিরে যান</a>
  <div class="lebd-cbl-heading">
   @php
    $cblImage = $course->banner ? (preg_match('~^https?://~i', $course->banner) ? $course->banner : asset(ltrim($course->banner, '/'))) : asset('web/img/no-course.png');
   @endphp
   <img class="lebd-cbl-course-image" src="{{ $cblImage }}" alt="{{ $course->name }}" onerror="this.onerror=null;this.src='{{ $cblImage }}'">
   <div>
    <h1 class="lebd-cbl-title">{{ $course->name }}</h1>
    @if($course->details)
      <div class="lebd-cbl-desc">{{ \Illuminate\Support\Str::limit(strip_tags($course->details), 240) }}</div>
    @else
      <p class="lebd-cbl-desc">এই কোর্সের বর্তমানে চলমান ব্যাচসমূহ দেখুন।</p>
    @endif
   </div>
  </div>
  <h2 class="lebd-cbl-subtitle"><i class="fa fa-calendar-check-o"></i> চলমান ব্যাচসমূহ</h2>
  <div class="lebd-cbl-grid">
   @forelse($batches as $batch)
     @php
       $cblBatchImage = $batch->banner ? (preg_match('~^https?://~i', $batch->banner) ? $batch->banner : asset(ltrim($batch->banner, '/'))) : $cblImage;
     @endphp
     <article class="lebd-cbl-card">
       <img class="lebd-cbl-banner" src="{{ $cblBatchImage }}" alt="{{ $batch->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $cblImage }}'">
       <div class="lebd-cbl-card-body">
        <h3>{{ $batch->name }}</h3>
        <div class="lebd-cbl-date">
          @if($batch->start_at) <div><i class="fa fa-calendar"></i> শুরু: {{ \Carbon\Carbon::parse($batch->start_at)->format('d/m/Y') }}</div> @endif
          @if($batch->end_at) <div><i class="fa fa-calendar-check-o"></i> শেষ: {{ \Carbon\Carbon::parse($batch->end_at)->format('d/m/Y') }}</div> @endif
          @if($batch->reg_end_at) <div><i class="fa fa-clock-o"></i> নিবন্ধনের শেষ: {{ \Carbon\Carbon::parse($batch->reg_end_at)->format('d/m/Y') }}</div> @endif
        </div>
        <a class="lebd-cbl-btn" href="{{ route('home.course.show', $batch->id) }}">ব্যাচের বিস্তারিত দেখুন <i class="fa fa-arrow-right"></i></a>
       </div>
     </article>
   @empty
     <div class="lebd-cbl-empty"><i class="fa fa-info-circle"></i> এই কোর্সে বর্তমানে কোনো সক্রিয় চলমান ব্যাচ নেই।</div>
   @endforelse
  </div>
 </div>
</div>
@endsection
