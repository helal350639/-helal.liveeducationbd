@php
    $value = $circular;
    $images = [];
    if (!empty($value->image)) {
        $decoded = json_decode($value->image, true);
        if (is_array($decoded)) {
            $images = array_values(array_filter($decoded, 'is_string'));
        } elseif (is_string($value->image)) {
            $images = [$value->image];
        }
    }
    $imageUrls = array_values(array_map(function ($path) {
        return preg_match('~^https?://~i', $path) ? $path : asset(ltrim($path, '/'));
    }, $images));
    $shareUrl = route('home.circular.show', $value->id);
    $shareTitle = $value->title ?: 'নিয়োগ বিজ্ঞপ্তি';
    $shareDescription = implode(' | ', array_filter([
        $value->admit_to ? 'প্রতিষ্ঠান: '.$value->admit_to : null,
        $value->position ? 'পদ: '.$value->position : null,
        $value->published_at ? 'প্রকাশিত: '.\Carbon\Carbon::parse($value->published_at)->format('d/m/Y') : null,
        $value->end_at ? 'আবেদনের শেষ তারিখ: '.\Carbon\Carbon::parse($value->end_at)->format('d/m/Y') : null,
    ]));
    $applicationUrl = $value->url && filter_var($value->url, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($value->url, PHP_URL_SCHEME) ?: ''), ['https','http'], true) ? $value->url : null;
    $externalPdfUrl = !empty($value->pdf_link) && filter_var($value->pdf_link, FILTER_VALIDATE_URL)
        && in_array(strtolower(parse_url($value->pdf_link, PHP_URL_SCHEME) ?: ''), ['https', 'http'], true)
        ? $value->pdf_link : null;
    $uploadedPdfUrl = $value->pdf ? (preg_match('~^https?://~i', $value->pdf) ? $value->pdf : asset(ltrim($value->pdf, '/'))) : null;
    $pdfUrl = $externalPdfUrl ?: $uploadedPdfUrl;
    $formatDate = function ($date) { return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : 'উল্লেখ নেই'; };
@endphp
@extends('homepage')
@section('title', $shareTitle.' | Live Education BD')
{{-- Master layout should output @yield('circular_social_meta') inside its <head>. --}}
@section('circular_social_meta')
<meta property="og:type" content="article">
<meta property="og:locale" content="bn_BD">
<meta property="og:site_name" content="Live Education BD">
<meta property="og:title" content="{{ $shareTitle }}">
<meta property="og:description" content="{{ $shareDescription }}">
<meta property="og:url" content="{{ $shareUrl }}">
@if(count($imageUrls))
<meta property="og:image" content="{{ $imageUrls[0] }}">
<meta property="og:image:alt" content="{{ $shareTitle }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $shareTitle }}">
<meta name="twitter:description" content="{{ $shareDescription }}">
@if(count($imageUrls))<meta name="twitter:image" content="{{ $imageUrls[0] }}">@endif
@endsection
@section('content')
<style>
.lebd-circular-read{max-width:1150px;margin:25px auto 55px;padding:0 16px;font-family:inherit;color:#17345b}
.lebd-circular-read *{box-sizing:border-box}
.lebd-circular-read .cr-hero{background:linear-gradient(110deg,#0747a6,#1189e8);padding:25px;border-radius:18px;color:#fff;margin-bottom:19px}
.lebd-circular-read .cr-hero h1{font-size:clamp(22px,3vw,31px);color:#fff;font-weight:800;margin:0 0 6px;line-height:1.5}
.lebd-circular-read .cr-hero p{color:#e3f3ff;margin:0;font-size:13px}
.lebd-circular-read .cr-card{background:#fff;border:1px solid #dce7f3;border-radius:17px;box-shadow:0 7px 24px rgba(9,48,104,.07);padding:22px;margin-bottom:17px}
.lebd-circular-read .cr-top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:15px}
.lebd-circular-read .cr-badge{display:inline-flex;background:#dff8e9;color:#087246;border-radius:20px;padding:6px 12px;font-weight:750;font-size:12px}
.lebd-circular-read h2{color:#113365;font-size:clamp(21px,2.8vw,28px);line-height:1.45;font-weight:800;margin:10px 0 0}
.lebd-circular-read h3{font-size:19px;font-weight:800;color:#123e78;margin:0 0 15px}
.lebd-circular-read .cr-actions{display:flex;gap:9px;flex-wrap:wrap}
.lebd-circular-read .cr-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#0a70d9;color:#fff!important;padding:11px 18px;border-radius:10px;font-size:14px;font-weight:750;text-decoration:none!important;border:0;cursor:pointer}
.lebd-circular-read .cr-btn:hover{background:#0758aa}.lebd-circular-read .cr-btn.secondary{background:#ecf5ff;color:#075ab7!important}.lebd-circular-read .cr-btn.secondary:hover{background:#d6eafe}
.lebd-circular-read .cr-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.lebd-circular-read .cr-field{padding:13px 15px;border:1px solid #e0eaf4;border-radius:11px;background:#f8fbff;min-width:0}
.lebd-circular-read .cr-field small{display:block;color:#6c8099;font-size:12px;margin-bottom:4px}.lebd-circular-read .cr-field strong{display:block;overflow-wrap:anywhere;font-size:15px;color:#143461}
.lebd-circular-read .cr-details{white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.85;color:#294764;font-size:15px}
.lebd-circular-read .cr-images{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,280px),1fr));gap:12px}
.lebd-circular-read .cr-images img{width:100%;height:auto;max-height:680px;object-fit:contain;border:1px solid #e3eaf4;border-radius:11px;background:#f9fbff}
.lebd-circular-read .cr-empty{color:#78869a;font-size:14px;margin:0}.lebd-circular-read .cr-footer{text-align:center;margin-top:22px}
@media(max-width:650px){.lebd-circular-read{padding:0 10px}.lebd-circular-read .cr-card{padding:15px}.lebd-circular-read .cr-hero{padding:19px 15px}.lebd-circular-read .cr-grid{grid-template-columns:1fr}.lebd-circular-read .cr-actions{width:100%}.lebd-circular-read .cr-actions .cr-btn{flex:1}}
</style>
<main class="lebd-circular-read">
    <header class="cr-hero">
        <h1>নিয়োগ বিজ্ঞপ্তি</h1>
        <p>Live Education BD — বিজ্ঞপ্তির বিস্তারিত তথ্য ও আবেদন নির্দেশনা</p>
    </header>
    <article class="cr-card">
        <div class="cr-top">
            <div>
                @if($value->start_at && $value->end_at && $value->status && in_array(strtolower($value->status), ['active','published']) && \Carbon\Carbon::today('Asia/Dhaka')->betweenIncluded(\Carbon\Carbon::parse($value->start_at), \Carbon\Carbon::parse($value->end_at)))
                    <span class="cr-badge">● আবেদন চলছে</span>
                @endif
                <h2>{{ $shareTitle }}</h2>
            </div>
            <div class="cr-actions">
                <a class="cr-btn" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}"><i class="fab fa-facebook-f" aria-hidden="true"></i> Facebook-এ শেয়ার করুন</a>
                <button type="button" class="cr-btn secondary" id="lebdCircularCopyLink" data-copy-url="{{ $shareUrl }}">লিংক কপি</button>
            </div>
        </div>
        <div class="cr-grid">
            <div class="cr-field"><small>নিয়োগ বিজ্ঞপ্তি প্রকাশের তারিখ</small><strong>{{ $formatDate($value->published_at) }}</strong></div>
            <div class="cr-field"><small>প্রতিষ্ঠানের নাম</small><strong>{{ $value->admit_to ?: 'উল্লেখ নেই' }}</strong></div>
            <div class="cr-field"><small>পদের নাম</small><strong>{{ $value->position ?: 'উল্লেখ নেই' }}</strong></div>
            <div class="cr-field"><small>আবেদন ফি</small><strong>{{ $value->fee ?: 'উল্লেখ নেই' }}</strong></div>
            <div class="cr-field"><small>আবেদন শুরু</small><strong>{{ $formatDate($value->start_at) }}</strong></div>
            <div class="cr-field"><small>আবেদনের শেষ তারিখ</small><strong>{{ $formatDate($value->end_at) }}</strong></div>
            <div class="cr-field"><small>আবেদন লিংক</small><strong>@if($applicationUrl)<a href="{{ $applicationUrl }}" target="_blank" rel="noopener noreferrer">আবেদন করুন ↗</a>@else উল্লেখ নেই @endif</strong></div>
            <div class="cr-field"><small>নিয়োগ বিজ্ঞপ্তির PDF</small><strong>@if($pdfUrl)<a href="{{ $pdfUrl }}" target="_blank" rel="noopener noreferrer">PDF দেখুন / ডাউনলোড করুন ↗</a>@else সংযুক্ত নেই @endif</strong></div>
        </div>
    </article>
    <section class="cr-card" aria-labelledby="cr-details-heading">
        <h3 id="cr-details-heading">বিস্তারিত তথ্য</h3>
        @if(filled($value->details))<div class="cr-details">{{ $value->details }}</div>@else<p class="cr-empty">অতিরিক্ত বিবরণ দেওয়া হয়নি।</p>@endif
    </section>
    <section class="cr-card" aria-labelledby="cr-images-heading">
        <h3 id="cr-images-heading">নিয়োগ বিজ্ঞপ্তির ছবি</h3>
        @forelse($imageUrls as $imageUrl)
            <div class="cr-images" style="margin-bottom:12px"><a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer"><img src="{{ $imageUrl }}" alt="{{ $shareTitle }}" loading="lazy"></a></div>
        @empty
            <p class="cr-empty">কোনো ছবি সংযুক্ত করা হয়নি।</p>
        @endforelse
    </section>
    <div class="cr-footer"><a class="cr-btn secondary" href="{{ route('home.circular') }}">← সকল চলমান নিয়োগ বিজ্ঞপ্তি</a></div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('lebdCircularCopyLink');
    if (!button) return;
    button.addEventListener('click', async function () {
        const link = button.getAttribute('data-copy-url') || window.location.href;
        try {
            if (!navigator.clipboard || !window.isSecureContext) throw new Error('Clipboard unavailable');
            await navigator.clipboard.writeText(link);
            button.textContent = 'লিংক কপি হয়েছে ✓';
        } catch (error) {
            window.prompt('লিংকটি কপি করুন:', link);
        }
    });
});
</script>
@endsection
