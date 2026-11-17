@php
  // This partial is included only by the main homepage branch.
  $lebdPopupNotice = $notice ?? null;
@endphp
@if($lebdPopupNotice)
<div class="lebd-site-notice-overlay" id="lebdSiteNoticeOverlay" hidden>
  <section class="lebd-site-notice" role="dialog" aria-modal="true" aria-labelledby="lebdSiteNoticeTitle">
    <button type="button" class="lebd-site-notice-close" id="lebdSiteNoticeClose" aria-label="Close notice"><i class="fa fa-times"></i></button>
    @if(($lebdPopupNotice->notice_type ?? 'text_image') !== 'image')
      <h2 id="lebdSiteNoticeTitle">{{ $lebdPopupNotice->title }}</h2>
    @endif
    @if($lebdPopupNotice->image)
      @if($lebdPopupNotice->url)<a href="{{ $lebdPopupNotice->url }}" @if($lebdPopupNotice->open_new_tab) target="_blank" rel="noopener noreferrer" @endif>@endif
        <img class="lebd-site-notice-image" src="{{ asset($lebdPopupNotice->image) }}" alt="{{ $lebdPopupNotice->title }}">
      @if($lebdPopupNotice->url)</a>@endif
    @endif
    @if(($lebdPopupNotice->notice_type ?? 'text_image') !== 'image' && $lebdPopupNotice->details)
      <div class="lebd-site-notice-details">{!! $lebdPopupNotice->details !!}</div>
    @endif
    @if($lebdPopupNotice->url)
      <a class="lebd-site-notice-link" href="{{ $lebdPopupNotice->url }}" @if($lebdPopupNotice->open_new_tab) target="_blank" rel="noopener noreferrer" @endif>বিস্তারিত দেখুন <i class="fa fa-arrow-right"></i></a>
    @endif
    <div class="lebd-site-notice-progress"><span id="lebdSiteNoticeProgress"></span></div>
  </section>
</div>
<style>
.lebd-site-notice-overlay{position:fixed;inset:0;z-index:100700;background:rgba(8,18,42,.58);display:flex;align-items:center;justify-content:center;padding:18px;backdrop-filter:blur(3px)}.lebd-site-notice-overlay[hidden]{display:none!important}.lebd-site-notice{position:relative;width:min(560px,100%);max-height:88vh;overflow:auto;background:#fff;border:1px solid #e5ebf5;border-radius:18px;padding:25px;box-shadow:0 24px 80px rgba(0,0,0,.28);animation:lebdNoticeIn .25s ease-out}.lebd-site-notice h2{font-size:23px;font-weight:800;color:#152b59;margin:0 42px 15px 0;line-height:1.35}.lebd-site-notice-close{position:absolute;right:13px;top:13px;width:36px;height:36px;border:0;border-radius:50%;background:#edf3ff;color:#526783;font-size:17px;cursor:pointer}.lebd-site-notice-image{display:block;width:100%;max-height:60vh;object-fit:contain;border-radius:11px;background:#f6f8fc}.lebd-site-notice-details{font-size:15px;line-height:1.7;color:#334155;margin-top:13px;overflow-wrap:anywhere}.lebd-site-notice-link{display:inline-flex;gap:8px;align-items:center;margin-top:16px;padding:10px 17px;border-radius:9px;background:#1263dc;color:#fff!important;text-decoration:none!important;font-weight:700}.lebd-site-notice-progress{height:3px;background:#edf1f7;border-radius:9px;margin-top:20px;overflow:hidden}.lebd-site-notice-progress span{display:block;height:100%;width:100%;background:#1769e8;transform-origin:left;animation:lebdNoticeCountdown var(--lebd-notice-duration,2s) linear forwards}@keyframes lebdNoticeCountdown{to{transform:scaleX(0)}}@keyframes lebdNoticeIn{from{opacity:0;transform:translateY(12px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}@media(max-width:600px){.lebd-site-notice{padding:20px 15px;border-radius:15px}.lebd-site-notice h2{font-size:19px}.lebd-site-notice-details{font-size:14px}}
</style>
<script>
(function(){
  'use strict';
  var overlay=document.getElementById('lebdSiteNoticeOverlay');
  if(!overlay) return;
  var duration=Math.max(1, {{ (int)($lebdPopupNotice->display_duration ?? 2) }})*1000;
  var close=document.getElementById('lebdSiteNoticeClose');
  var timer=null;
  function hide(){
    overlay.hidden=true;
    if(timer) window.clearTimeout(timer);
  }
  function show(){
    overlay.hidden=false;
    overlay.style.setProperty('--lebd-notice-duration',(duration/1000)+'s');
    timer=window.setTimeout(hide,duration);
  }
  if(close) close.addEventListener('click',hide);
  overlay.addEventListener('click',function(e){if(e.target===overlay) hide();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&!overlay.hidden) hide();});
  window.setTimeout(show,500);
})();
</script>
@endif
