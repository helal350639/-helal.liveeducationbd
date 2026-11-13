@extends('homepage')
@section('title', 'Video Class — আমার কেনা প্যাকেজ')
@section('content')
@php
    // Keep each package once even if it has both paid and manually granted access.
    // Paid access is displayed first; a manual grant is added only if that package
    // does not already have a current paid order.
    $visiblePackages = collect();
    $seenPackageIds = [];
    foreach ($paid as $order) {
        $key = (int) $order->package_id;
        if (isset($seenPackageIds[$key])) continue;
        $seenPackageIds[$key] = true;
        $visiblePackages->push((object) [
            'name' => $order->package_name ?? 'Premium Package',
            'expires_at' => $order->expires_at,
            'method' => 'purchase',
            'order_id' => (int) $order->id,
            'department_categories' => $order->department_categories ?? '[]',
            'non_department_categories' => $order->non_department_categories ?? '[]',
        ]);
    }
    foreach ($manual as $grant) {
        $key = (int) $grant->package_id;
        if (isset($seenPackageIds[$key])) continue;
        $seenPackageIds[$key] = true;
        $visiblePackages->push((object) [
            'name' => $grant->package_name ?? 'Premium Package',
            'expires_at' => $grant->ends_at,
            'method' => 'manual',
            'order_id' => \App\Services\PremiumClass\PremiumManualAccess::ORDER_OFFSET + (int) $grant->id,
            'department_categories' => $grant->department_categories ?? '[]',
            'non_department_categories' => $grant->non_department_categories ?? '[]',
        ]);
    }
@endphp
<style>
.lebd-v16-page{max-width:1160px;margin:0 auto;padding:25px 18px 55px;color:#12345b}
.lebd-v16-top{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:19px}
.lebd-v16-top a{font-weight:600;text-decoration:none;color:#0769cd}
.lebd-v16-new{background:#096cce!important;color:#fff!important;border-radius:10px;padding:12px 17px}
.lebd-v16-hero{border-radius:16px;background:linear-gradient(110deg,#d8eafd,#f0f8ff);padding:25px 23px;margin-bottom:22px}
.lebd-v16-hero h1{margin:0 0 6px;font-size:26px;font-weight:800;color:#102f61}
.lebd-v16-hero p{margin:0;color:#456b94}
.lebd-v16-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
.lebd-v16-card{padding:19px 20px;border:1px solid #d8e7fa;border-radius:13px;background:#fff;box-shadow:0 7px 25px rgba(24,78,139,.05);min-width:0}
.lebd-v16-card h2{font-size:20px;margin:0 0 11px;color:#143b70}
.lebd-v16-status{display:inline-block;background:#e5f9ef;color:#067b43;border-radius:30px;padding:4px 10px;font-size:12px;font-weight:700}
.lebd-v16-method{display:inline-block;margin-left:6px;border-radius:30px;background:#e9f2fc;padding:4px 10px;font-size:12px;color:#245580}
.lebd-v16-expiry{font-size:13px;color:#476e96;margin:12px 0}
.lebd-v16-cat{margin:6px 0;font-size:13px;overflow-wrap:anywhere;color:#31587d}
.lebd-v16-watch{display:block;text-align:center;background:#0875df;color:#fff!important;text-decoration:none!important;font-weight:700;border-radius:8px;padding:11px 15px;margin-top:16px}
.lebd-v16-empty{border:1px dashed #bed6ef;padding:20px;border-radius:12px;background:#fff}
.lebd-v16-success{display:flex;flex-direction:column;gap:4px;margin:0 0 16px;padding:13px 16px;border:1px solid #9cddb5;border-left:5px solid #159653;border-radius:10px;background:#eaf9ef;color:#145b37;font-size:13px;line-height:1.55}
.lebd-v16-success strong{font-size:15px;color:#10663b}
@media(max-width:640px){.lebd-v16-page{padding:12px 12px 32px}.lebd-v16-hero{padding:18px 15px}.lebd-v16-hero h1{font-size:20px}.lebd-v16-grid{grid-template-columns:1fr}.lebd-v16-card{padding:15px}.lebd-v16-top{gap:10px}.lebd-v16-new{padding:9px 12px;font-size:13px}}
</style>
<main class="lebd-v16-page">
 <div class="lebd-v16-top">
  <a href="{{ route('students.dashboard') }}">← Student Dashboard</a>
  <a class="lebd-v16-new" href="{{ route('premium.recordings.packages') }}">＋ নতুন প্রিমিয়াম প্যাকেজ দেখুন</a>
 </div>
 <header class="lebd-v16-hero">
  <h1>Video Class — আমার কেনা প্যাকেজ</h1>
  <p>SSLCommerz, Referral Wallet ও Admin-এর দেওয়া সক্রিয় Premium Video Package সমূহ</p>
 </header>
 @if(session('success'))
  <div class="lebd-v16-success" role="status" aria-live="polite">
   <strong>✓ প্যাকেজ কেনা সফল হয়েছে!</strong>
   <span>{{session('success')}}</span>
   @if(session('purchased_package_name'))<b>{{session('purchased_package_name')}}</b>@endif
  </div>
 @endif
 @if(session('info'))<p>{{session('info')}}</p>@endif
 @if($visiblePackages->isEmpty())
   <div class="lebd-v16-empty">আপনার কোনো সক্রিয় Premium Video Package নেই।</div>
 @else
  <div class="lebd-v16-grid">
  @foreach($visiblePackages as $item)
   @php
    $dep = json_decode((string)$item->department_categories, true);
    $non = json_decode((string)$item->non_department_categories, true);
    $dep = is_array($dep) ? $dep : [];
    $non = is_array($non) ? $non : [];
   @endphp
   <article class="lebd-v16-card">
    <h2>{{ $item->name }}</h2>
    <span class="lebd-v16-status">Active — এই প্যাকেজে Access আছে</span>
    @if($item->method === 'manual')<span class="lebd-v16-method">Admin Added</span>@endif
    <p class="lebd-v16-expiry">মেয়াদ: {{ \Illuminate\Support\Carbon::parse($item->expires_at)->format('d M Y, h:i A') }}</p>
    @if(count($dep))<p class="lebd-v16-cat"><b>Department:</b> {{ implode(', ', $dep) }}</p>@endif
    @if(count($non))<p class="lebd-v16-cat"><b>Non-Department:</b> {{ implode(', ', $non) }}</p>@endif
    <a class="lebd-v16-watch" href="{{ route('students.premium.video.departments', ['order' => $item->order_id]) }}">▶ ক্লাস দেখুন</a>
   </article>
  @endforeach
  </div>
 @endif
</main>
@endsection
