@extends('student')
@section('title', 'MCQ Study — আমার কেনা প্যাকেজ')
@section('content')
<style>
.lebd-my-mcq{min-height:65vh;background:#edf5ff;padding:24px 14px 55px;color:#123363}.lebd-my-mcq .wrap{max-width:1140px;margin:auto}.lebd-my-mcq .top{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:15px}.lebd-my-mcq a{text-decoration:none}.lebd-my-mcq .back{color:#0860be;font-size:13px;font-weight:750}.lebd-my-mcq .shop{padding:10px 13px;border-radius:9px;background:#096fd6;color:white;font-weight:750;font-size:12px}.lebd-my-mcq .hero{padding:20px;border-radius:15px;background:#dcebff;margin-bottom:17px}.lebd-my-mcq .hero h1{margin:0;font-size:23px;font-weight:800}.lebd-my-mcq .hero p{margin:4px 0 0;font-size:12px;color:#56739a}.lebd-my-mcq .cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}.lebd-my-mcq .pkg{background:#fff;border:1px solid #cfdef5;border-radius:12px;padding:17px;box-shadow:0 7px 17px #15345610;min-width:0}.lebd-my-mcq .pkg h2{font-size:17px;margin:0 0 7px}.lebd-my-mcq .active{display:inline-block;background:#dcf9e7;color:#087a43;border-radius:20px;padding:4px 9px;font-size:11px;font-weight:800}.lebd-my-mcq .meta{margin:10px 0;color:#385b86;font-size:12px}.lebd-my-mcq .open{display:block;background:#0b72de;color:white;padding:11px 12px;text-align:center;border-radius:8px;font-weight:800;font-size:13px}.lebd-my-mcq .group{background:#fff2eb;border-radius:15px;padding:16px;margin-bottom:16px}.lebd-my-mcq .group.non{background:#ecedff}.lebd-my-mcq .banner{margin:0 auto 15px;display:block;width:max-content;max-width:100%;padding:12px 29px;border-radius:26px;background:linear-gradient(100deg,#ff9100,#ff414d);color:white;font-weight:850;text-align:center;font-size:20px}.lebd-my-mcq .group.non .banner{background:linear-gradient(100deg,#3988ff,#164ee0)}.lebd-my-mcq .grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px}.lebd-my-mcq .tile{min-height:95px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:8px;background:#eaf2ff;border:1px solid #d0e0f5;border-radius:10px;color:#103967;font-size:13px;font-weight:800}.lebd-my-mcq .tile:hover{background:#d9e9ff}.lebd-my-mcq .tile i{font-size:25px;color:#1375ff}.lebd-my-mcq .empty{background:white;padding:20px;border-radius:12px;color:#61748c}@media(max-width:860px){.lebd-my-mcq .cards{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-my-mcq .grid{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:550px){.lebd-my-mcq .cards{grid-template-columns:1fr}.lebd-my-mcq .grid{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-my-mcq .hero h1{font-size:18px}.lebd-my-mcq .banner{font-size:15px}}
</style>
<main class="lebd-my-mcq"><div class="wrap">
<div class="top"><a class="back" href="{{ $selectedPackage ? route('student.mcq.purchased') : route('students.dashboard') }}">← {{ $selectedPackage ? 'আমার কেনা প্যাকেজ' : 'Student Dashboard' }}</a>@if(\Illuminate\Support\Facades\Route::has('lebd.packages.index'))<a class="shop" href="{{ route('lebd.packages.index', ['section'=>'mcq']) }}">＋ নতুন প্রিমিয়াম প্যাকেজ দেখুন</a>@endif</div>
<header class="hero"><h1>▣ MCQ Study — {{ $selectedPackage ? $selectedPackage->name : 'আমার কেনা প্যাকেজ' }}</h1><p>{{ $selectedPackage ? 'প্যাকেজে অন্তর্ভুক্ত বিভাগ ও ক্যাটাগরি' : 'আপনার সক্রিয় MCQ Subscription Package সমূহ' }}</p></header>
@if(!$selectedPackage)
<div class="cards">@forelse($packages as $pkg)<article class="pkg"><h2>{{ $pkg->name }}</h2><span class="active">Active</span><div class="meta">মেয়াদ: {{ \Carbon\Carbon::parse($pkg->ends_at)->format('d M Y, h:i A') }}</div><a class="open" href="{{ route('student.mcq.purchased',['package'=>$pkg->package_id]) }}">প্যাকেজ দেখুন ▶</a></article>@empty<div class="empty">আপনার বর্তমানে কোনো সক্রিয় MCQ প্যাকেজ নেই।</div>@endforelse</div>
@else
@foreach(['department'=>'Department MCQ Study','non-department'=>'Non-MCQ Study'] as $kind=>$heading)
@php $groupCats=$categories->where('kind',$kind); @endphp
@if($groupCats->isNotEmpty())
<section class="group {{ $kind==='non-department' ? 'non' : '' }}"><h2 class="banner">{{ $heading }}</h2><div class="grid">
@foreach($groupCats as $cat)
<a class="tile" href="{{ \Illuminate\Support\Facades\Route::has('student.jobprep.category') ? route('student.jobprep.category',['kind'=>$cat->kind,'category'=>$cat->id]) : '#' }}"><i class="fa fa-folder-open" aria-hidden="true"></i><span>{{ $cat->name }}</span></a>
@endforeach
</div></section>
@endif
@endforeach
@if($categories->isEmpty())<div class="empty">এই প্যাকেজে কোনো Active Category Assignment পাওয়া যায়নি। Admin-এর Package Mapping যাচাই করুন।</div>@endif
@endif
</div></main>
@endsection
