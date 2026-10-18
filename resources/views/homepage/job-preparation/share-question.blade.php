<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ \Illuminate\Support\Str::limit(strip_tags($q->title),85) }} | LiveEducationBD</title>
<meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($q->title),155) }} — MCQ practice on LiveEducationBD">
<meta property="og:type" content="article">
<meta property="og:site_name" content="LiveEducationBD">
<meta property="og:title" content="{{ \Illuminate\Support\Str::limit(strip_tags($q->title),85) }} | LiveEducationBD">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($q->title),155) }}">
<meta property="og:url" content="{{ $shareUrl }}">
<meta property="og:image" content="{{ $imageUrl }}">
<meta property="og:image:secure_url" content="{{ $imageUrl }}">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ $imageUrl }}">
<style>
*{box-sizing:border-box}body{margin:0;background:#f3f7fd;color:#12315c;font-family:system-ui,'Noto Sans Bengali',sans-serif}.wrap{max-width:840px;margin:35px auto;padding:15px}.card{background:white;border:1px solid #d9e6f7;border-radius:16px;overflow:hidden;box-shadow:0 10px 28px #183b7020}.card header{background:#16488f;color:white;padding:20px 25px;font-weight:800;font-size:23px}.body{padding:23px}.body h1{font-size:24px;line-height:1.7;margin:12px 0}.options{display:grid;grid-template-columns:1fr 1fr;gap:12px}.option{padding:12px;background:#f1f6fd;border:1px solid #dbe7f5;border-radius:8px;line-height:1.6}.actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}.btn{padding:11px 18px;border-radius:9px;background:#176bd5;color:#fff;text-decoration:none;border:0;cursor:pointer;font-size:14px}.btn.alt{background:#e8f1fe;color:#1555a5}@media(max-width:600px){.options{grid-template-columns:1fr}.body h1{font-size:18px}}
</style>
</head>
<body><main class="wrap"><article class="card"><header>LIVE EDUCATION BD · MCQ Preparation</header><div class="body">
<small>{{ $category->name }}</small><h1>{!! $q->title !!}</h1>
<div class="options">@foreach($options as $index=>$choice)<div class="option"><b>{{ ['a','b','c','d','e','f'][$index]??($index+1) }})</b> {!! $choice->item !!}</div>@endforeach</div>
<div class="actions"><a class="btn" href="{{ $readingUrl }}">Read Question</a><a class="btn alt" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener noreferrer">Share on Facebook</a><a class="btn alt" href="{{ $imageUrl }}" target="_blank">View JPG Card</a></div>
</div></article></main></body></html>
