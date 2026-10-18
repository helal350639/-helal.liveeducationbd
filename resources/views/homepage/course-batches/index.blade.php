@extends('homepage')

@section('title', $course->name . ' — চলমান ব্যাচ')

@section('content')
<style>
.lebd-cbl-page{background:#f6f9fe;min-height:60vh;padding:28px 16px 56px;color:#14335f}
.lebd-cbl-shell{max-width:1160px;margin:0 auto}
.lebd-cbl-back{display:inline-flex;gap:7px;align-items:center;margin-bottom:18px;color:#075ecb;text-decoration:none;font-weight:700}
.lebd-cbl-heading{display:flex;align-items:center;gap:20px;padding:20px;background:#fff;border:1px solid #e0ebfa;border-radius:18px;box-shadow:0 7px 24px rgba(15,52,95,.06)}
.lebd-cbl-course-image{width:110px;height:110px;flex:0 0 110px;border-radius:14px;object-fit:contain;background:#f4f8ff;border:1px solid #ecf1f8;padding:6px}
.lebd-cbl-title{font-size:clamp(21px,3vw,30px);margin:0 0 7px;color:#0c2c63;font-weight:800}
.lebd-cbl-desc{font-size:14px;line-height:1.75;color:#526b8d;margin:0}
.lebd-cbl-subtitle{display:flex;align-items:center;gap:9px;margin:28px 0 15px;font-size:22px;font-weight:800;color:#13386a}
.lebd-cbl-subtitle i{color:#09a56c}
.lebd-cbl-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:17px}
.lebd-cbl-card{display:flex;flex-direction:column;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #dfe9f6;box-shadow:0 6px 18px rgba(18,54,90,.07)}
.lebd-cbl-banner{width:100%;height:160px;object-fit:cover;background:#eef5ff}
.lebd-cbl-card-body{padding:18px;display:flex;flex:1;flex-direction:column;gap:10px}
.lebd-cbl-card h3{color:#123467;font-size:19px;margin:0;line-height:1.5;font-weight:800}
.lebd-cbl-date{font-size:12px;color:#657d9a;line-height:1.75}
.lebd-cbl-btn{margin-top:auto;display:block;padding:11px 13px;text-align:center;background:#0967de;color:#fff!important;border-radius:10px;font-size:13px;font-weight:800;text-decoration:none!important}
.lebd-cbl-btn:hover{background:#034dba}
.lebd-cbl-empty{border:1px dashed #bbd1eb;background:#fff;padding:32px 18px;border-radius:15px;text-align:center;color:#637791;grid-column:1/-1}
@media(max-width:850px){.lebd-cbl-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.lebd-cbl-page{padding:16px 10px 35px}.lebd-cbl-grid{grid-template-columns:1fr}.lebd-cbl-heading{gap:12px;padding:12px}.lebd-cbl-course-image{width:75px;height:75px;flex-basis:75px}.lebd-cbl-desc{font-size:12px}.lebd-cbl-card{border-radius:12px}.lebd-cbl-banner{height:175px}}
</style>
<div class="lebd-cbl-page">
 <div class="lebd-cbl-shell">
  <a class="lebd-cbl-back" href="{{ route('homepage.courses') }}"><i class="fa fa-arrow-left"></i> সকল কোর্সে ফিরে যান</a>
  <div class="lebd-cbl-heading">
   @php
    $cblImage = $course->banner ? (preg_match('~^https?://~i', $course->banner) ? $course->banner : asset(ltrim($course->banner, '/'))) : asset('web/img/no-course.png');
   @endphp
   <img class="lebd-cbl-course-image" src="{{ $cblImage }}" alt="{{ $course->name }}" onerror="this.style.visibility='hidden'">
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
       <img class="lebd-cbl-banner" src="{{ $cblBatchImage }}" alt="{{ $batch->name }}" loading="lazy" onerror="this.style.visibility='hidden'">
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
