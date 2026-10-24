@extends('homepage')
@section('title', $video->title)
@section('content')
<style>
.lebd-watch111{max-width:980px;margin:25px auto;padding:14px}
.lebd-watch111 h2{font-size:clamp(19px,2vw,25px);color:#092b52;margin:0 0 14px}
.lebd-watch111-player{width:100%;aspect-ratio:16/9;background:#101727;position:relative;overflow:hidden;border-radius:8px}
.lebd-watch111-player iframe{position:absolute;inset:0;width:100%;height:100%;border:0;display:block}
/* Drive's external-open control is inside Google's cross-origin iframe.
   Mask the commonly placed top-right control rather than pretending to change Google's UI. */
.lebd-watch111-drive-mask{position:absolute;top:0;right:0;width:58px;height:57px;background:#101727;z-index:2;pointer-events:auto;cursor:default}
.lebd-watch111-note{font-size:12px;line-height:1.6;color:#617790;margin-top:12px}
@media(max-width:600px){.lebd-watch111{padding:9px;margin:13px auto}.lebd-watch111-drive-mask{width:42px;height:43px}}
</style>
<div class="lebd-watch111">
    <h2>{{ $video->title }}</h2>
    @if($embed)
        <div class="lebd-watch111-player">
            @if(in_array(strtolower((string)parse_url($embed, PHP_URL_HOST)), ['drive.google.com', 'www.drive.google.com'], true))
                <iframe src="{{ $embed }}" title="{{ $video->title }}" loading="lazy"
                    allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
                    allowfullscreen referrerpolicy="strict-origin-when-cross-origin"
                    sandbox="allow-scripts allow-same-origin allow-forms allow-presentation"></iframe>
                <span class="lebd-watch111-drive-mask" aria-hidden="true"></span>
            @else
                <iframe src="{{ $embed }}" title="{{ $video->title }}" loading="lazy"
                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                    allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
            @endif
        </div>
    @else
        <p>এই ভিডিওর লিংক YouTube বা Google Drive-এর সমর্থিত ফরম্যাটে নেই।</p>
    @endif
    <p class="lebd-watch111-note">{{ !empty($isFree) ? 'এটি ফ্রি প্রিভিউ।' : 'আপনার Premium Package দিয়ে এই ভিডিও দেখছেন।' }}</p>
</div>
@endsection
