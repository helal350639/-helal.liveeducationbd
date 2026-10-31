{{-- Purchases are sourced only from the logged-in student's verified, unexpired premium orders. --}}
@php
    $lebdVideoStudentId = auth('student')->id();
    $lebdPurchasedOrders = collect();
    if ($lebdVideoStudentId && \Illuminate\Support\Facades\Schema::hasTable('lebd_premium_class_orders')
        && \Illuminate\Support\Facades\Schema::hasTable('lebd_premium_class_packages')) {
        $lebdPurchasedOrders = \Illuminate\Support\Facades\DB::table('lebd_premium_class_orders as o')
          ->leftJoin('lebd_premium_class_packages as p', 'p.id', '=', 'o.package_id')
          ->where('o.student_id', $lebdVideoStudentId)
          ->where('o.status', 'paid')
          ->whereNotNull('o.expires_at')
          ->where('o.expires_at', '>', now())
          ->select('o.id', 'o.package_id', 'o.expires_at', 'o.package_type', 'o.department_categories',
                   'o.non_department_categories', 'p.name as package_name')
          ->orderByDesc('o.paid_at')->get();
    }
@endphp
<style>
#lebd-purchased-video-classes{scroll-margin-top:120px}
#lebd-purchased-video-classes .lebd-video-owns{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px}
#lebd-purchased-video-classes .lebd-video-own{background:#fff;border:1px solid #dbe8fa;border-radius:13px;padding:18px;min-width:0}
#lebd-purchased-video-classes .lebd-video-own h4{font-size:17px;color:#0e3269;font-weight:800;margin:0 0 10px}
#lebd-purchased-video-classes .lebd-video-own small{color:#526c90}
#lebd-purchased-video-classes .lebd-video-own .lebd-active{display:inline-block;background:#e3faec;color:#077443;border-radius:20px;padding:4px 9px;font-weight:700;font-size:11px}
#lebd-purchased-video-classes .lebd-owned-empty{font-size:14px;color:#637b99;margin:0}
#lebd-purchased-video-classes .lebd-video-own{box-shadow:0 10px 26px #14477715;overflow:hidden;padding:22px;background:linear-gradient(145deg,#fff,#f5faff)}
#lebd-purchased-video-classes .lebd-card-actions{display:flex;gap:9px;margin-top:18px;flex-wrap:wrap}
#lebd-purchased-video-classes .lebd-card-actions a{flex:1;min-width:130px;text-align:center;padding:12px;border-radius:9px;text-decoration:none;font-weight:800}
#lebd-purchased-video-classes .lebd-class-link{background:#0872e7;color:white}
</style>
<section id="lebd-purchased-video-classes" class="container mt-4 lebd-section-panel" tabindex="-1">
 <div class="lebd-section-head">
  <span class="lebd-head-icon"><i class="fa fa-video"></i></span>
  <div class="lebd-head-text"><h3>Video Class — আমার কেনা প্যাকেজ</h3><p>আপনার সক্রিয় Premium Video Package সমূহ</p></div>
 </div>
 @if($lebdPurchasedOrders->isEmpty())
   <p class="lebd-owned-empty">আপনার কোনো সক্রিয় Premium Video Package পাওয়া যায়নি।</p>
 @else
 <div class="lebd-video-owns">
 @foreach($lebdPurchasedOrders as $lebdOwn)
 <article class="lebd-video-own">
  <h4>{{ $lebdOwn->package_name ?: 'Premium Video Package' }}</h4>
  <span class="lebd-active">Active</span>
  <p><small>মেয়াদ: {{ \Carbon\Carbon::parse($lebdOwn->expires_at)->format('d M Y, h:i A') }}</small></p>
  @php
    $lebdDept = json_decode((string)$lebdOwn->department_categories, true);
    $lebdOther = json_decode((string)$lebdOwn->non_department_categories, true);
    $lebdDept = is_array($lebdDept) ? $lebdDept : [];
    $lebdOther = is_array($lebdOther) ? $lebdOther : [];
  @endphp
  <p><small>Department: {{ $lebdDept ? implode(', ', $lebdDept) : '—' }}</small></p>
  <p><small>Non-Department: {{ $lebdOther ? implode(', ', $lebdOther) : '—' }}</small></p>
 <div class="lebd-card-actions"><a class="lebd-class-link" href="{{ route('students.premium.video.departments', ['order'=>$lebdOwn->id]) }}">▶ ক্লাস দেখুন</a></div>
 </article>
 @endforeach
 </div>
 @endif
</section>
