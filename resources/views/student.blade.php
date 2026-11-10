<?php

use \App\Http\Controllers\SourceCtrl;
use App\Models\Notification;

$source = new SourceCtrl;

$notifications = Notification::select('id', 'title', 'route', 'type_id')->get();
$user = Auth::guard('student')->user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title', 'LiveEducationBD') | Live Education BD</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('web/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('web/assets/ajax/libs/font-awesome/6.6.0/css/all.min.css') }}">
    {{-- Same navigation stylesheet as homepage; supports desktop and mobile menu. --}}
    <link rel="stylesheet" href="{{ asset('web/assets/css/style2b24.css') }}?v=3.8.40">
    <link rel="stylesheet" href="{{ asset('web/assets/css/responsive2b24.css') }}?v=3523">
    <link rel="stylesheet" href="{{ asset('web/css/style.css') }}?v=1.3.4">
    <style>
        #Header .logo-img { max-width:170px; }
        #Header .user-image { max-width:40px; border-radius:25px; }
        #Header .mobile-user-image { max-width:30px; border-radius:50%; }
        #Header .img-circle { border-radius:50%; max-width:100px; margin:auto; }
        #Header .user-header {text-align:center;}
        @media(max-width:768px) { #Header .logo-img {max-width:120px;} }
    </style>
    @yield('stylesheets')
    <!-- LEBD v62: apply saved theme before page paints -->
    <script>(function(){try{var t=localStorage.getItem('lebd-site-theme');if(t==='dark'){document.documentElement.setAttribute('data-lebd-theme','dark');}}catch(e){}})();</script>
    <link rel="stylesheet" href="{{ asset('web/css/lebd-global-night-mode-v62.css') }}?v=63">

    <link rel="stylesheet" href="{{ asset('web/css/lebd-support-chat-v58.css') }}?v=58">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; background: #ffffff; }
        .student-topbar { width: 100%; padding: 15px; display: flex; justify-content: flex-start; background: #ffffff; }
        .sidebar-toggle { width: 55px; height: 45px; border: 1px solid #ddd; background: #fff; border-radius: 8px; font-size: 22px; color: #555; display: flex; align-items: center; justify-content: center; }
        main { width: 100%; }
        @media (max-width: 768px) { body { overflow-x: hidden; } }
    </style>
    {{-- LEBD-05 Mobile header/content separation: limited to this shared student layout. --}}
    <style>
      @media (max-width: 768px) {
        #lebd-student-main {
          display: flow-root; /* prevent first child's collapsed top margin sliding under the header */
          min-width: 0;
          max-width: 100%;
          padding-top: var(--lebd-student-mobile-header-offset, 0px);
          scroll-margin-top: 75px;
        }
        #lebd-student-main > * { max-width: 100%; }
        #lebd-student-main img, #lebd-student-main video, #lebd-student-main iframe {
          max-width: 100%;
        }
        #lebd-student-main .table-responsive { max-width: 100%; overflow-x: auto; }
      }
    </style>
</head>
<body>
    <!-- Existing shared homepage header. Keep unchanged. -->
    @include('homepage.partials.global-header')
    <main id="lebd-student-main">
        @yield('content')
    </main>
    <footer id="Footer"></footer>

    <!-- LEBD v57: one reusable Support Chat widget, linked to existing channels. -->
    @include('homepage.partials.support-chat')

    <script src="{{ asset('web/assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('web/assets/js/bootstrap.bundle.min.js') }}"></script>
    @yield('scripts')
    @if(Auth::guard('student')->check())
        @include('student-panel.partials.activity-tracker')
    @endif
    <script src="{{ asset('web/js/lebd-support-chat-v60.js') }}?v=60" defer></script>
    <script src="{{ asset('web/js/lebd-global-night-mode-v62.js') }}?v=62" defer></script>
    {{-- Header may be fixed only at mobile widths; reserve space only when it overlays main. --}}
    <script>
    (function () {
      'use strict';
      var main = document.getElementById('lebd-student-main');
      if (!main) return;
      var query = window.matchMedia('(max-width: 768px)');
      var scheduled = false;
      function isFixed(el) {
        if (!el || el.nodeType !== 1) return false;
        var position = window.getComputedStyle(el).position;
        return position === 'fixed';
      }
      function syncOffset() {
        scheduled = false;
        main.style.removeProperty('--lebd-student-mobile-header-offset');
        if (!query.matches) return;
        var header = document.getElementById('Header') || document.querySelector('header');
        if (!header) return;
        var candidates = [header];
        header.querySelectorAll('nav, .navbar, .mobile-header, .mobile-nav, .header-main, .header-sticky').forEach(function (el) {
          candidates.push(el);
        });
        var bottom = 0;
        candidates.forEach(function (el) {
          if (!isFixed(el)) return;
          var rect = el.getBoundingClientRect();
          // Exclude off-screen drawers, floating controls and overlays not crossing the top edge.
          if (rect.top <= 2 && rect.bottom > 0 && rect.height <= 200 && rect.width >= window.innerWidth * .5) {
            bottom = Math.max(bottom, rect.bottom);
          }
        });
        if (!bottom) return;
        var mainTop = main.getBoundingClientRect().top;
        if (mainTop < bottom) {
          main.style.setProperty('--lebd-student-mobile-header-offset', Math.ceil(bottom - mainTop + 6) + 'px');
        }
      }
      function schedule() {
        if (scheduled) return;
        scheduled = true;
        window.requestAnimationFrame(syncOffset);
      }
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', schedule);
      else schedule();
      window.addEventListener('load', schedule);
      window.addEventListener('resize', schedule, {passive:true});
      window.addEventListener('orientationchange', schedule);
      if (query.addEventListener) query.addEventListener('change', schedule);
      var header = document.getElementById('Header') || document.querySelector('header');
      if (header && window.ResizeObserver) {
        new ResizeObserver(schedule).observe(header);
      }
    })();
    </script>
</body>
</html>
