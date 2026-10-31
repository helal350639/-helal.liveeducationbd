@extends('student')
@section('title','Video Class — আমার কেনা প্যাকেজ')
@section('content')
<style>.lebd-studio{background:#eef6ff;min-height:75vh;padding:24px 14px 55px;color:#0b3268}.lebd-studio-inner{max-width:1160px;margin:auto}.lebd-studio-heading{padding:35px 20px;background:linear-gradient(115deg,#d7ebff,#eff8ff);border-radius:18px;margin:15px 0 22px}.lebd-studio-heading h1{font-size:30px;font-weight:850;margin:0;color:#0b2f63}.lebd-studio-heading p{color:#4b607d;margin:5px 0}.lebd-studio-back{font-weight:700;text-decoration:none;color:#0a6cda}.lebd-video-topline{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:15px 0 16px}
.lebd-video-topline .lebd-studio-back{display:inline-flex;align-items:center}
.lebd-new-premium-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;background:#0b62c9;color:#fff!important;padding:12px 18px;border:1px solid #0a58b4;border-radius:11px;text-decoration:none!important;font-size:14px;font-weight:800;box-shadow:0 5px 12px #124c9b1c}
.lebd-new-premium-btn:hover,.lebd-new-premium-btn:focus{background:#084d9d;color:#fff!important}
@media(max-width:540px){.lebd-new-premium-btn{width:100%}}
</style>
<div class="lebd-studio"><div class="lebd-studio-inner">
 <div class="lebd-video-topline">
  <a class="lebd-studio-back" href="{{ route('students.dashboard') }}">← Student Dashboard</a>
  <a class="lebd-new-premium-btn" href="{{ route('premium.recordings.packages') }}"><i class="fa fa-plus-circle" aria-hidden="true"></i> নতুন প্রিমিয়াম প্যাকেজ দেখুন</a>
 </div>
 <div class="lebd-studio-heading"><h1>▣ Video Class — আমার কেনা প্যাকেজ</h1><p>আপনার সক্রিয় Premium Video Package সমূহ</p></div>
 @include('student-panel.partials.lebd-purchased-video-packages')
</div></div>
@endsection
