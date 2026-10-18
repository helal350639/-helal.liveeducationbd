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
    @yield('stylesheets')
    <link rel="stylesheet" href="{{ asset('web/css/lebd-support-chat-v58.css') }}?v=58">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; background: #ffffff; }
        .student-topbar { width: 100%; padding: 15px; display: flex; justify-content: flex-start; background: #ffffff; }
        .sidebar-toggle { width: 55px; height: 45px; border: 1px solid #ddd; background: #fff; border-radius: 8px; font-size: 22px; color: #555; display: flex; align-items: center; justify-content: center; }
        main { width: 100%; }
        @media (max-width: 768px) { body { overflow-x: hidden; } }
    </style>
</head>
<body>
    <!-- Existing shared homepage header. Keep unchanged. -->
    <header id="Header">
        @include('homepage.header')
    </header>
    <main>
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
    <script src="{{ asset('web/js/lebd-support-chat-v58.js') }}?v=58" defer></script>
</body>
</html>
