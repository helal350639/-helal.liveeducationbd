@extends('homepage')
@section('title', 'Purchase Successful')
@section('content')
@php
    // Resolve the existing named student dashboard route if present.
    // Never redirect to a package purchase page or another payment gateway.
    $studentDashboardRoute = null;
    foreach (['students.dashboard', 'student.dashboard', 'students.home', 'student.home', 'student.dashboard.index', 'students.dashboard.index'] as $candidate) {
        if (\Illuminate\Support\Facades\Route::has($candidate)) {
            $studentDashboardRoute = $candidate;
            break;
        }
    }
    $dashboardUrl = $studentDashboardRoute ? route($studentDashboardRoute) : url('/students');
@endphp
<style>
.lebd-success-shade{position:fixed;inset:0;z-index:99999;background:rgba(7,20,55,.65);display:grid;place-items:center;padding:18px}
.lebd-success-dialog{background:#fff;color:#092a55;width:min(100%,460px);border-radius:18px;padding:32px 24px;text-align:center;box-shadow:0 24px 80px #001a4555;font-family:inherit}
.lebd-success-check{width:70px;height:70px;display:grid;place-items:center;margin:0 auto 14px;border-radius:50%;background:#e7fff2;color:#058345;font-size:40px}
.lebd-success-dialog h1{font-size:26px;margin:6px 0 12px}.lebd-success-dialog p{line-height:1.7}
.lebd-success-btn{display:block;margin-top:24px;background:#0875df;color:white!important;padding:13px;border-radius:9px;text-decoration:none;font-weight:700}
</style>
<div class="lebd-success-shade" role="presentation">
 <section class="lebd-success-dialog" role="dialog" aria-modal="true" aria-labelledby="lebd-success-title" aria-describedby="lebd-success-message">
  <div class="lebd-success-check" aria-hidden="true">✓</div>
  <h1 id="lebd-success-title">অভিনন্দন!</h1>
  <p id="lebd-success-message">আপনার কোর্সটি সফলভাবে কেনা হয়েছে।</p>
  <p><strong>{{ $packageName }}</strong></p>
  <p>Payment Successful — Premium Access Activated</p>
  <a class="lebd-success-btn" href="{{ route('students.premium.video.classes') }}" id="lebd-success-go">OK — Video Class-এ যান →</a>
 </section>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){var btn=document.getElementById('lebd-success-go');if(btn)btn.focus();});</script>
@endsection
