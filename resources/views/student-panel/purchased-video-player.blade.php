@extends('student')
@section('title','Premium Video Class')
@section('content')
<style>
.lebd-pv-player-page{max-width:1080px;margin:22px auto 48px;padding:0 14px;color:#13396a}
.lebd-pv-iframe{width:100%;aspect-ratio:16/9;background:#061632;border-radius:13px;overflow:hidden;position:relative}
.lebd-pv-iframe:fullscreen{width:100vw;height:100vh;aspect-ratio:auto;border-radius:0;background:#000}
.lebd-pv-iframe:-webkit-full-screen{width:100vw;height:100vh;aspect-ratio:auto;border-radius:0;background:#000}
.lebd-pv-fullscreen{position:absolute;right:12px;bottom:12px;z-index:6;background:#052c58e8;color:#fff;border:1px solid #ffffff6e;border-radius:8px;padding:9px 13px;cursor:pointer;font-weight:700;font-size:14px}
.lebd-pv-fullscreen:focus-visible{outline:3px solid #ffd354;outline-offset:3px}
.lebd-pv-iframe iframe{width:100%;height:100%;border:0;display:block}
.lebd-pv-cover{width:100%;height:100%;position:absolute;inset:0;border:0;background:linear-gradient(120deg,#103e7e,#287bc2);color:#fff;cursor:pointer;display:grid;place-items:center}
.lebd-pv-cover img{position:absolute;width:100%;height:100%;object-fit:cover;inset:0}
.lebd-pv-cover .lebd-pv-largeplay{z-index:1;display:grid;place-items:center;width:70px;height:70px;border-radius:50%;background:#0065dacc;border:2px solid #fff;font-size:28px}
.lebd-pv-drive-guard{position:absolute;right:0;top:0;width:92px;height:45px;z-index:2;background:transparent;pointer-events:auto}
.lebd-pv-complaint-panel{margin:14px 0;background:white;border:1px solid #d4e4fa;border-radius:13px;padding:20px}
.lebd-pv-complaint-panel[hidden]{display:none!important}
.lebd-pv-complaint-panel label{display:block;font-weight:700;margin:12px 0 5px}
.lebd-pv-complaint-panel select,.lebd-pv-complaint-panel textarea{width:100%;border:1px solid #c7d9f1;border-radius:8px;padding:11px}
.lebd-pv-complaint-panel button{background:#0d6fd8;color:white;border:0;border-radius:9px;padding:11px 24px;margin-top:12px;font-weight:800}
.lebd-pv-note{color:#547491;font-size:13px;margin-top:10px}
.lebd-pv-complaint-toggle{border:1px solid #c7d9f1;background:#fff;color:#0b5cb4;padding:9px 16px;border-radius:8px;font-weight:700;cursor:pointer}
</style>
<div class="lebd-pv-player-page">
<a href="{{ url()->previous() }}" style="color:#086bd6;font-weight:700">← ক্লাস তালিকায় ফিরে যান</a>
<h2 style="margin:18px 0;color:#0a376d">{{ $videoModel->title }}</h2>
@php
 $pvThumb=trim((string)($videoModel->thumbnail_url??''));
 $pvThumbUrl=null;
 if (preg_match('~^https://[^\s]+$~i',$pvThumb)) {
   $pvThumbUrl=$pvThumb;
 } elseif ($pvThumb!=='' && !str_contains($pvThumb,'..') && !str_contains($pvThumb,'?') && !str_contains($pvThumb,'#') && !preg_match('~^[a-z][a-z0-9+.-]*:~i',$pvThumb) && !str_starts_with($pvThumb,'//') && !str_contains($pvThumb,chr(92))) {
   $pvThumbUrl=asset(ltrim($pvThumb,'/'));
 }
@endphp
<div class="lebd-pv-iframe" id="lebd-premium-player">
 <button type="button" id="lebd-pv-start" class="lebd-pv-cover" aria-label="{{ $videoModel->title }} চালু করুন">
  @if($pvThumbUrl)<img src="{{ $pvThumbUrl }}" alt="{{ $videoModel->title }} ভিডিও থাম্বনেইল">@endif
  <span class="lebd-pv-largeplay" aria-hidden="true">▶</span>
 </button>
 <button type="button" id="lebd-pv-fullscreen" class="lebd-pv-fullscreen" aria-label="ভিডিও ফুল স্ক্রিন করুন" title="Full Screen">⛶ Full Screen</button>
</div>
<p class="lebd-pv-note">liveeducationbd.com</p>
<button type="button" id="lebd-pv-complaint-toggle" class="lebd-pv-complaint-toggle" aria-expanded="false" aria-controls="lebd-video-complaint">⚑ অভিযোগ করুন</button>
@if(session('success'))<div role="status" style="padding:12px;background:#e3f9ed;color:#06683d;border-radius:9px">{{ session('success') }}</div>@endif
@if($errors->any())<div role="alert" style="padding:12px;background:#fff0f0;color:#a12222;border-radius:9px">{{ $errors->first() }}</div>@endif
<section id="lebd-video-complaint" class="lebd-pv-complaint-panel" hidden><h3 style="margin:0">ভিডিও সম্পর্কে অভিযোগ করুন</h3><p class="lebd-pv-note">ক্লাসে সমস্যা থাকলে কারণ নির্বাচন করে জানান।</p>
<form method="POST" action="{{ route('premium.recordings.complaints.store') }}">@csrf
<input type="hidden" name="video_id" value="{{ $videoModel->id }}">
<label for="lebd-complaint-reason">অভিযোগের কারণ</label>
<select id="lebd-complaint-reason" name="reason" required><option value="unclear_video">ভিডিও অস্পষ্ট</option><option value="audio_problem">অডিও সমস্যা</option><option value="wrong_content">ভুল কনটেন্ট</option><option value="video_not_playing">ভিডিও চলছে না</option><option value="other">অন্যান্য</option></select>
<label for="lebd-complaint-comment">বিস্তারিত (ঐচ্ছিক)</label><textarea id="lebd-complaint-comment" name="comment" rows="3" maxlength="1000" placeholder="সমস্যাটি লিখুন"></textarea>
<button type="submit">অভিযোগ জমা দিন</button></form></section>
</div>
<script>
(function(){
 var player=document.getElementById('lebd-premium-player');var start=document.getElementById('lebd-pv-start');
 var embedded=@json($embed);var drive=@json($isDrive);
 var fsButton=document.getElementById('lebd-pv-fullscreen');
 function updateFullscreen(){var active=document.fullscreenElement===player;fsButton.textContent=active?'⛶ Exit Full Screen':'⛶ Full Screen';fsButton.setAttribute('aria-label',active?'ফুল স্ক্রিন বন্ধ করুন':'ভিডিও ফুল স্ক্রিন করুন');}
 fsButton.addEventListener('click',async function(){try{if(document.fullscreenElement===player){await document.exitFullscreen();}else if(!document.fullscreenElement&&player.requestFullscreen){await player.requestFullscreen();}}catch(e){fsButton.title='এই ব্রাউজারে ফুল স্ক্রিন চালু করা যায়নি';}});
 document.addEventListener('fullscreenchange',updateFullscreen);
 start.addEventListener('click',function(){
  var frame=document.createElement('iframe');frame.src=embedded;frame.title=@json($videoModel->title);
  frame.setAttribute('allow','autoplay; encrypted-media; picture-in-picture; fullscreen');
  frame.setAttribute('allowfullscreen','');
  frame.setAttribute('referrerpolicy','strict-origin-when-cross-origin');
  frame.setAttribute('sandbox','allow-scripts allow-same-origin allow-forms allow-presentation');
  start.remove();player.appendChild(frame);
  if(drive){var guard=document.createElement('div');guard.className='lebd-pv-drive-guard';guard.setAttribute('aria-hidden','true');player.appendChild(guard);}
 });
 var button=document.getElementById('lebd-pv-complaint-toggle');var panel=document.getElementById('lebd-video-complaint');
 function setOpen(value){panel.hidden=!value;button.setAttribute('aria-expanded',value?'true':'false');if(value)panel.scrollIntoView({behavior:'smooth',block:'start'});}
 button.addEventListener('click',function(){setOpen(panel.hidden);});
 if(new URLSearchParams(window.location.search).get('complaint')==='1'||@json($errors->any()))setOpen(true);
})();
</script>
@endsection
