@extends('student')
@section('title','Premium Video Classes')
@section('content')
<style>
.lebd-pv-page{background:#f2f6ff;min-height:75vh;padding:16px 14px 42px;color:#10376c}.lebd-pv-inner{max-width:1090px;margin:auto}.lebd-pv-back{color:#075fd0;font-weight:700;text-decoration:none}.lebd-pv-banner{background:linear-gradient(110deg,#111d52,#086a9c);color:#fff;border-radius:18px;padding:26px 30px;margin:15px 0 18px}.lebd-pv-banner h1{font-size:26px;font-weight:800;margin:5px 0}.lebd-pv-banner small{letter-spacing:.05em}.lebd-pv-stage{font-size:20px;font-weight:800;color:#0b3568;margin:20px 0 15px}.lebd-pv-navgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(235px,1fr));gap:13px}.lebd-pv-navcard{display:block;text-decoration:none;color:#0c3770;border:1px solid #cfe0fa;background:#fff;border-radius:13px;padding:20px;font-weight:750;box-shadow:0 4px 12px #17466a0b}.lebd-pv-navcard small{display:block;color:#5e7b9b;margin-top:7px;font-weight:500}.lebd-pv-chapter{background:#fff;border:1px solid #d5e5fc;border-radius:16px;margin:14px 0;padding:15px}.lebd-pv-chapterhead{font-size:17px;font-weight:800;border-bottom:1px solid #e2ebf7;padding:0 2px 11px;margin-bottom:12px}.lebd-pv-cards{display:flex;gap:12px;overflow-x:auto;scroll-snap-type:x mandatory;scroll-behavior:smooth;padding:3px 2px 12px;scrollbar-width:thin}.lebd-pv-cards>.lebd-pv-card{flex:0 0 225px;scroll-snap-align:start}.lebd-pv-slidebar{display:flex;justify-content:space-between;align-items:center;gap:8px}.lebd-pv-slide-controls{display:flex;gap:6px}.lebd-pv-slide-controls button{padding:5px 10px;border:1px solid #b8d3f7;border-radius:7px;background:#f7fbff;color:#075bd0;cursor:pointer}.lebd-pv-card{border:1px solid #d5e5f9;background:#fff;border-radius:12px;overflow:hidden;min-width:0;display:flex;flex-direction:column}.lebd-pv-thumb{width:100%;aspect-ratio:16 / 9;background:linear-gradient(135deg,#0c4d9b,#47a2dc);position:relative;overflow:hidden;display:block}.lebd-pv-thumb img{width:100%;height:100%;object-fit:cover;display:block}.lebd-pv-play{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#075dcbdc;color:#fff;border:2px solid white;border-radius:50%;width:44px;height:44px;display:grid;place-items:center;font-size:18px}.lebd-pv-cardbody{padding:10px 12px 8px;flex:1}.lebd-pv-cardtitle{font-weight:800;color:#0b3367;text-decoration:none;display:block;overflow-wrap:anywhere;min-height:23px}.lebd-pv-cardmeta{color:#6b819c;font-size:12px;margin-top:4px}.lebd-pv-actions{display:grid;grid-template-columns:1fr auto;gap:7px;border-top:1px solid #e6eef8;padding:8px 10px}.lebd-pv-actions a{text-decoration:none;border-radius:7px;padding:7px 8px;text-align:center;font-size:12px;font-weight:700}.lebd-pv-watch{background:#096bd7;color:#fff!important}.lebd-pv-complaint{color:#1764b8!important;border:1px solid #bad5f5}.lebd-pv-empty{padding:18px;background:#fff;border-radius:10px;color:#687d97}.lebd-pv-backrow{display:flex;gap:15px;flex-wrap:wrap;margin:12px 0}@media(max-width:550px){.lebd-pv-banner{padding:20px}.lebd-pv-cards{gap:8px}.lebd-pv-cards>.lebd-pv-card{flex-basis:205px}.lebd-pv-cardbody{padding:8px}.lebd-pv-actions{grid-template-columns:1fr}.lebd-pv-actions a{font-size:11px}.lebd-pv-stage{font-size:18px}}
</style>
@php
$lebdStageName = ['departments'=>'Department বেছে নিন','subjects'=>'Subject বেছে নিন','chapters'=>'Chapter ও Video Classes','videos'=>'Recorded Video Classes'][$stage] ?? 'ভিডিও ক্লাস';
@endphp
<div class="lebd-pv-page"><div class="lebd-pv-inner">
<div class="lebd-pv-backrow"><a class="lebd-pv-back" href="{{ route('students.premium.video.classes') }}">← আমার কেনা প্যাকেজ</a><a class="lebd-pv-back" href="javascript:history.back()">← আগের পেজ</a></div>
<header class="lebd-pv-banner"><small>LIVE EDUCATION BD • Premium Video Classes</small><h1>{{ $paid->package_name ?: 'Premium Video Package' }}</h1><div>Department → Subject → Chapter → Video</div></header>
<h2 class="lebd-pv-stage">{{ $lebdStageName }}</h2>
@if($stage==='departments')
 <div class="lebd-pv-navgrid">@foreach($groups as $group=>$topics)@foreach($topics as $topic)
 <a class="lebd-pv-navcard" href="{{ route('students.premium.video.subjects',['order'=>$paid->id,'group'=>$group,'topic'=>$topic]) }}">{{ $group==='department' ? 'Department' : 'Non-Department' }}: {{ ucwords(str_replace('-',' ',$topic)) }}<small>Subject দেখুন →</small></a>
 @endforeach @endforeach</div>
@elseif($stage==='subjects')
 <div class="lebd-pv-navgrid">@foreach($subjects as $subject)<a class="lebd-pv-navcard" href="{{ route('students.premium.video.chapters',['order'=>$paid->id,'subject'=>$subject->id]) }}">📁 {{ $subject->title }}<small>Chapter ও Video Classes দেখুন →</small></a>@endforeach</div>
@elseif($stage==='chapters')
 @if($standalone->isNotEmpty())<section class="lebd-pv-chapter"><div class="lebd-pv-slidebar"><div class="lebd-pv-chapterhead">📁 {{ $selected->title }} — ভিডিও ক্লাস</div><div class="lebd-pv-slide-controls"><button type="button" class="lebd-slide-prev" aria-label="আগের ভিডিও">❮</button><button type="button" class="lebd-slide-next" aria-label="পরের ভিডিও">❯</button></div></div><div class="lebd-pv-cards">@foreach($standalone as $item)@include('student-panel.partials.lebd-purchased-video-card',['item'=>$item,'paid'=>$paid])@endforeach</div></section>@endif
 @foreach($chapters as $chapter)
 @php $chapterVideos = $chapterVideoGroups[$chapter->id] ?? collect(); @endphp
 @if($chapterVideos->isNotEmpty())
 <section class="lebd-pv-chapter"><div class="lebd-pv-slidebar"><div class="lebd-pv-chapterhead">📁 {{ $chapter->title }}</div><div class="lebd-pv-slide-controls"><button type="button" class="lebd-slide-prev" aria-label="আগের ভিডিও">❮</button><button type="button" class="lebd-slide-next" aria-label="পরের ভিডিও">❯</button></div></div>
 <div class="lebd-pv-cards">@foreach($chapterVideos as $item)@include('student-panel.partials.lebd-purchased-video-card',['item'=>$item,'paid'=>$paid])@endforeach</div>
 </section>
 @endif
 @endforeach

@elseif($stage==='videos')
 <section class="lebd-pv-chapter"><div class="lebd-pv-slidebar"><div class="lebd-pv-chapterhead">📁 {{ $selected->title }}</div><div class="lebd-pv-slide-controls"><button type="button" class="lebd-slide-prev" aria-label="আগের ভিডিও">❮</button><button type="button" class="lebd-slide-next" aria-label="পরের ভিডিও">❯</button></div></div><div class="lebd-pv-cards">@foreach($videos as $item)@include('student-panel.partials.lebd-purchased-video-card',['item'=>$item,'paid'=>$paid])@endforeach</div></section>
@endif
@if(($stage==='departments' && !collect($groups)->flatten()->count()) || ($stage==='subjects' && $subjects->isEmpty()) || ($stage==='chapters' && $standalone->isEmpty() && collect($chapterVideoGroups)->flatten(1)->isEmpty()) || ($stage==='videos' && $videos->isEmpty()))<p class="lebd-pv-empty">এই বিভাগে প্রকাশিত ক্লাস এখনো পাওয়া যায়নি।</p>@endif
</div></div>
<script>document.querySelectorAll('.lebd-pv-chapter').forEach(function(section){var slider=section.querySelector('.lebd-pv-cards');if(!slider)return;section.querySelectorAll('.lebd-slide-prev,.lebd-slide-next').forEach(function(button){button.addEventListener('click',function(){slider.scrollBy({left:(button.classList.contains('lebd-slide-next')?1:-1)*Math.max(220,slider.clientWidth*.75),behavior:'smooth'});});});});</script>
@endsection
