<!DOCTYPE html>
<html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('page_title', 'Premium Packages') | Live Education BD</title>
<link rel="stylesheet" href="{{ asset('web/css/lebd-universal-packages.css') }}">
</head><body class="lebd-package-page">
<div class="up-shell">
<header class="up-header"><a href="{{ url('/') }}" class="up-back" aria-label="Back to homepage">←</a>
<a href="{{ url('/') }}" class="up-logo"><img src="{{ asset('web/images/live-education-bd-official-logo.png') }}" alt="LIVE EducationBD official logo"></a>
<a href="{{ url('/') }}" class="up-home">Home</a></header>
<main>@yield('body')</main>
<footer class="up-footer">© {{ date('Y') }} Live Education BD</footer>
</div>
</body></html>
