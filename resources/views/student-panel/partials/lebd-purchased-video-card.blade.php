@php
$lebdWatchUrl = route('students.premium.video.watch',['order'=>$paid->id,'video'=>$item->id]);
$lebdThumb = trim((string)($item->thumbnail_url ?? ''));
$lebdThumbUrl = null;
$lebdFallbackUrl = null;

// YouTube preview is a fallback when the uploaded thumbnail is blank or broken.
$lebdVideoUrl = trim((string)($item->video_url ?? ''));
$lebdHost = strtolower((string)(parse_url($lebdVideoUrl, PHP_URL_HOST) ?? ''));
$lebdPath = (string)(parse_url($lebdVideoUrl, PHP_URL_PATH) ?? '');
$lebdVideoId = null;
if (in_array($lebdHost, ['youtube.com','www.youtube.com','m.youtube.com','youtube-nocookie.com','www.youtube-nocookie.com'],true)) {
    parse_str((string)parse_url($lebdVideoUrl, PHP_URL_QUERY),$lebdQuery);
    $lebdVideoId = $lebdQuery['v'] ?? null;
    if (!$lebdVideoId && preg_match('~^/(?:embed|shorts|live)/([a-zA-Z0-9_-]{11})~',$lebdPath,$m)) $lebdVideoId=$m[1];
} elseif ($lebdHost==='youtu.be') {
    $lebdVideoId=trim($lebdPath,'/');
}
if (is_string($lebdVideoId) && preg_match('/^[A-Za-z0-9_-]{11}$/',$lebdVideoId)) {
    $lebdFallbackUrl='https://i.ytimg.com/vi/'.$lebdVideoId.'/hqdefault.jpg';
}
// Drive preview thumbnails are provider dependent, so never treat them as guaranteed.
if (!$lebdFallbackUrl && in_array($lebdHost,['drive.google.com','www.drive.google.com'],true)
    && preg_match('~/file/d/([a-zA-Z0-9_-]+)~',$lebdPath,$m)) {
    $lebdFallbackUrl='https://drive.google.com/thumbnail?id='.$m[1].'&sz=w640';
}
if (preg_match('~^https?://[^\s]+$~i',$lebdThumb)) {
    $lebdThumbUrl=$lebdThumb;
} elseif ($lebdThumb!=='' && !str_contains($lebdThumb,'..') && !str_contains($lebdThumb,'?')
    && !str_contains($lebdThumb,'#') && !str_starts_with($lebdThumb,'//')
    && !str_contains($lebdThumb,chr(92)) && !preg_match('~^[a-z][a-z0-9+.-]*:~i',$lebdThumb)) {
    $lebdThumbUrl=asset(ltrim($lebdThumb,'/'));
}
$lebdThumbUrl=$lebdThumbUrl ?: $lebdFallbackUrl;
@endphp
<article class="lebd-pv-card">
 <a class="lebd-pv-thumb" href="{{ $lebdWatchUrl }}" aria-label="{{ $item->title }} ক্লাস দেখুন">
  @if($lebdThumbUrl)
   <img src="{{ $lebdThumbUrl }}" alt="{{ $item->title }}" loading="lazy"
    @if($lebdFallbackUrl && $lebdThumbUrl !== $lebdFallbackUrl)
      data-fallback="{{ $lebdFallbackUrl }}"
      onerror="if(this.dataset.fallback && this.src !== this.dataset.fallback){this.src=this.dataset.fallback;this.removeAttribute('data-fallback');}else{this.style.display='none';}"
    @else onerror="this.style.display='none';" @endif>
  @endif
  <span class="lebd-pv-play" aria-hidden="true">▶</span>
 </a>
 <div class="lebd-pv-cardbody"><a class="lebd-pv-cardtitle" href="{{ $lebdWatchUrl }}">{{ $item->title }}</a><div class="lebd-pv-cardmeta">{{ $item->duration ?: 'Recorded Class' }}</div></div>
 <div class="lebd-pv-actions"><a class="lebd-pv-watch" href="{{ $lebdWatchUrl }}">▶ ভিডিও দেখুন</a><a class="lebd-pv-complaint" href="{{ $lebdWatchUrl }}?complaint=1#lebd-video-complaint">অভিযোগ</a></div>
</article>
