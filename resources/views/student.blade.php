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
    <link rel="stylesheet" href="{{ asset('web/css/lebd-global-site-layout-v79.css') }}?v=79">
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
    <link rel="stylesheet" href="{{ asset('web/css/lebd-global-night-mode-v62.css') }}?v=64">

    <link rel="stylesheet" href="{{ asset('web/css/lebd-support-chat-v58.css') }}?v=62">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; background: #ffffff; }
        .student-topbar { width: 100%; padding: 15px; display: flex; justify-content: flex-start; background: #ffffff; }
        .sidebar-toggle { width: 55px; height: 45px; border: 1px solid #ddd; background: #fff; border-radius: 8px; font-size: 22px; color: #555; display: flex; align-items: center; justify-content: center; }
        main { width: 100%; }
        @media (max-width: 768px) { body { overflow-x: hidden; } }
    </style>
    {{-- LEBD-06 Responsive fixed-header/content separation for desktop and mobile. --}}
    <style>
      #lebd-student-main {
        display: flow-root; /* prevent first-child margins from collapsing under the header */
        min-width: 0;
        max-width: 100%;
        padding-top: var(--lebd-student-header-offset, 0px);
        scroll-margin-top: 85px;
      }
      #lebd-student-main > * { max-width: 100%; }
      #lebd-student-main img, #lebd-student-main video, #lebd-student-main iframe {
        max-width: 100%;
      }
      #lebd-student-main .table-responsive { max-width: 100%; overflow-x: auto; }
      @media (max-width: 768px) {
        #lebd-student-main { scroll-margin-top: 75px; }
      }
    </style>
    <link rel="stylesheet" href="{{ asset('css/lebd-global-desktop-75-scale.css') }}?v=1">
</head>
<body>
    @php
        // Timed practice attempt: keep the exam workspace distraction-free.
        $lebdTimedExamMode = request()->routeIs('student.exam-section.wrong-unanswered.quiz.take');
    @endphp
    @unless($lebdTimedExamMode)
        @include('homepage.partials.global-header')
    @endunless
    <main id="lebd-student-main">
        @yield('content')
    </main>
    @unless($lebdTimedExamMode)
  <footer id="Footer">
    <div class="container">
      <div class="row g-4">
          <div class="col-sm-12 col-md-4">
              <div class="footer-logo-content mb-lg-5">
                  <div class="footer-logo">
                      <div class="footer-logo-image">
                          <img src="/web/img/logo.png?v=1.1.2" alt="">
                      </div>
                  </div>
                  <div class="footer-logo-text">
                      <p>For countless students across the country,<br>
                        LiveEducationBD is the leading online education platform — a symbol of excellence and inspiration.
                        Begin your journey with confidence and experience dedicated support at every step.</p>
                  </div>
                  <div class="footer-social-media-area">
                      <div class="social-media-icon">
                          <div class="facebook-icon">
                              <a href="javascript:void(0)" target="_blank">
                                <i class="fa-brands fa-facebook"></i>
                              </a>
                          </div>
                          <div class="youtube-icon">
                            <a href="javascript:void(0)" target="_blank">
                              <i class="fa-brands fa-youtube"></i>
                            </a>
                          </div>
                          <div class="instagram-icon">
                            <a href="javascript:void(0)" target="_blank">
                              <i class="fa-brands fa-instagram"></i>
                            </a>
                          </div>
                          <div class="linkedin-icon">
                            <a href="javascript:void(0)" target="_blank">
                              <i class="fa-brands fa-linkedin"></i>
                            </a>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
          <div class="col-md-8">
              <div class="footer-others-div">
                  <div class="row g-4">
                      <div class="col-6 col-md-4">
                          <div class="about-us">
                              <h3>About Us</h3>
                              <ul>
                                <li>
                                  <a href="javascript:void(0)">Our Success</a>
                                </li>
                                <li>
                                  <a href="javascript:void(0)">Instructors</a>
                                </li>
                                <li>
                                  <a href="javascript:void(0)">Our Services</a>
                                </li>
                                <li>
                                  <a href="javascript:void(0)">Book Storage</a>
                                </li>
                                <li>
                                  <a href="javascript:void(0)">Circular</a>
                                </li>
                              </ul>
                          </div>
                      </div>
                      <div class="col-6 col-md-4">
                          <div class="resources">
                            <h3>Resources</h3>
                            <ul>
                              <li><a href="javascript:void(0)">Courses</a></li>
                              <li><a href="javascript:void(0)">Exams</a></li>
                              <li><a href="javascript:void(0)">Terms & Conditions</a></li>
                              <li><a href="javascript:void(0)">Privacy Policy</a></li>
                              <li><a href="javascript:void(0)">Return Policy</a></li>
                            </ul>
                          </div>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <div class="official-info">
                          <h3>Official Info</h3>
                          <ul>
                            <li>
                              <i class="fa-regular fa-envelope"></i>
                              <a href="mailto:liveeducationbd24@gmail.com">liveeducationbd24@gmail.com</a>
                            </li>
                            <li>
                                <i class="fa-solid fa-phone"></i>
                                <a href="tel:+01718778184">01718778184</a>
                            </li>
                            <li>
                                <i class="fa-solid fa-location-dot"></i>
                                <a href="javascript:void(0)">Address: Jogendranagar, Sabgari-6450, Gurudaspur, Natore, Bangladesh<br>
                                  Trade License No. 69141130786</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <div class="col-sm-12 col-md-12">
        <img class="img-fluid" src="/web/img/pay-buttons.png" alt="SSL Image" srcset="" style="width: 100%;">
      </div>
      <br>
    </div>
    <div class="copy-right-section">
      <div class="container">
        <p>Copyright © 2025 <span><a href="/">LiveEuducationBD</a></span> All rights reserved</p>
      </div>
    </div>
  </footer>
    @endunless

    @unless($lebdTimedExamMode)
        <!-- LEBD v57: one reusable Support Chat widget, linked to existing channels. -->
        @include('homepage.partials.support-chat')
    @endunless

    <script src="{{ asset('web/assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('web/assets/js/bootstrap.bundle.min.js') }}"></script>
    @yield('scripts')
    @if(Auth::guard('student')->check())
        @include('student-panel.partials.activity-tracker')
    @endif
    <script src="{{ asset('web/js/lebd-support-chat-v60.js') }}?v=60" defer></script>
    <script src="{{ asset('web/js/lebd-live-chat-v1.js') }}?v=3" defer></script>
    <script src="{{ asset('web/js/lebd-global-night-mode-v62.js') }}?v=62" defer></script>
    {{-- LEBD-06: measure fixed/sticky site header and reserve its real height on every viewport. --}}
    <script>
    (function () {
      'use strict';
      var main = document.getElementById('lebd-student-main');
      if (!main) return;

      var scheduled = false;
      function schedule() {
        if (scheduled) return;
        scheduled = true;
        window.requestAnimationFrame(syncOffset);
      }
      function syncOffset() {
        scheduled = false;
        main.style.removeProperty('--lebd-student-header-offset');

        var header = document.getElementById('Header') || document.querySelector('header');
        if (!header) return;

        var candidates = [header];
        header.querySelectorAll('nav, .navbar, .mobile-header, .mobile-nav, .header-main, .header-sticky')
          .forEach(function (el) { candidates.push(el); });

        var bottom = 0;
        candidates.forEach(function (el) {
          if (!el || el.nodeType !== 1) return;
          var style = window.getComputedStyle(el);
          var position = style.position;
          if (position !== 'fixed' && position !== 'sticky') return;

          var rect = el.getBoundingClientRect();
          // Only count a visible top header, not dropdowns, drawers or floating widgets.
          if (rect.top <= 2 && rect.bottom > 0 &&
              rect.height > 0 && rect.height <= 220 &&
              rect.width >= window.innerWidth * 0.5) {
            bottom = Math.max(bottom, rect.bottom);
          }
        });

        if (!bottom) return;
        var mainTop = main.getBoundingClientRect().top;
        if (mainTop < bottom) {
          main.style.setProperty('--lebd-student-header-offset',
            Math.ceil(bottom - mainTop + 8) + 'px');
        }
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', schedule);
      } else {
        schedule();
      }
      window.addEventListener('load', schedule);
      window.addEventListener('resize', schedule, { passive: true });
      window.addEventListener('orientationchange', schedule);

      var header = document.getElementById('Header') || document.querySelector('header');
      if (header && window.ResizeObserver) {
        new ResizeObserver(schedule).observe(header);
      }
      // Re-check after late-loading fonts/images and when responsive navigation changes.
      if (window.matchMedia) {
        var mobileQuery = window.matchMedia('(max-width: 768px)');
        if (mobileQuery.addEventListener) mobileQuery.addEventListener('change', schedule);
      }
    })();
    </script>
</body>
</html>
