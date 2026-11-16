{{-- LEBD-05 Fix199: Replace only the existing student dashboard exam-card block with this partial. --}}
@php
    $le191Categories = collect();
    $le191LiveCategoryIds = [];
    try {
        $le191Categories = \Illuminate\Support\Facades\DB::table('lebd_exam_section_categories')
            ->where('status', 'Active')->orderBy('sort_order')->orderBy('id')->get(['id','name']);
        $le191Now = now()->format('Y-m-d H:i:s');
        $le191LiveCategoryIds = \Illuminate\Support\Facades\DB::table('lebd_exam_section_routine_items as i')
            ->join('lebd_exam_section_routines as r','r.id','=','i.routine_id')
            ->where('i.type','exam')
            ->where('i.exam_start_at','<=',$le191Now)
            ->where('i.exam_end_at','>',$le191Now)
            ->distinct()->pluck('r.category_id')->map(fn ($id) => (int) $id)->all();
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning('LEBD student exam cards could not load', ['error'=>$e->getMessage()]);
    }
@endphp
<style>
.le191-exam{width:100%;max-width:1240px;margin:22px auto;padding:20px;border-radius:22px;background:linear-gradient(115deg,#102552,#15285f);color:white;box-sizing:border-box}
.le191-exam *{box-sizing:border-box}.le191-head{display:flex;gap:12px;align-items:center;border-bottom:1px solid #ffffff29;padding-bottom:12px;margin-bottom:14px}
.le191-icon{width:44px;height:44px;background:#3455fb;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:21px;flex-shrink:0}
.le191-title{min-width:0;flex:1}.le191-title h2{font-size:24px;line-height:1.2;font-weight:750;color:#fff;margin:0 0 3px}.le191-title p{font-size:12px;line-height:1.4;color:#c6d9fd;margin:0}
.le191-package-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;flex-shrink:0;padding:9px 14px;border:1px solid #7da7ff;border-radius:22px;background:linear-gradient(135deg,#3567ff,#1648d7);color:#fff!important;text-decoration:none!important;font-size:11px;font-weight:800;line-height:1;box-shadow:0 3px 8px #071d523d,inset 0 1px 0 #ffffff55;transition:transform .15s,box-shadow .15s}.le191-package-btn:hover,.le191-package-btn:focus{color:#fff!important;transform:translateY(-1px);box-shadow:0 5px 12px #071d5255,inset 0 1px 0 #ffffff66}.le191-package-btn i{font-size:11px}

.le191-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.le191-link{display:flex;align-items:center;position:relative;min-height:46px;padding:9px 32px 9px 15px;border-radius:11px;background:white;color:#0f326b!important;border:1px solid #e0e7fa;text-decoration:none!important;font-size:13px;font-weight:700;line-height:1.3;transition:background .15s}
.le191-link:hover,.le191-link:focus{background:#eaf2ff}.le191-mini-icon{color:#1269e0;margin-right:8px}.le191-live-badge{position:absolute;right:9px;top:50%;transform:translateY(-50%);display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:22px;padding:0 7px;border-radius:20px;background:#e60023;color:#fff;font-size:9px;font-weight:900;letter-spacing:.35px;line-height:1;box-shadow:0 2px 7px #e600234d;border:1px solid #ff7588;animation:le191livepulse 1.5s ease-in-out infinite}
.le191-empty{grid-column:1/-1;color:#d9e5ff;font-size:13px;padding:8px}
.le191-free-quota{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:13px;padding:10px 13px;border:1px solid #ffffff35;border-radius:11px;background:#ffffff12;color:#e9f1ff;font-size:12px}.le191-free-quota strong{color:#fff;background:#0b9c72;padding:4px 9px;border-radius:20px}.le191-free-quota small{color:#c9d8f7;font-size:10px}.le191-link{opacity:1!important;pointer-events:auto!important;cursor:pointer!important;background:#fff!important}.le191-link:hover,.le191-link:focus{background:#eef5ff!important}
@keyframes le191livepulse{0%,100%{box-shadow:0 2px 7px #e600234d}50%{box-shadow:0 2px 10px 3px #e6002355}}
@media(max-width:700px){.le191-exam{padding:14px;border-radius:16px}.le191-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.le191-title h2{font-size:20px}.le191-link{font-size:12px;padding:9px 22px 9px 10px}.le191-package-btn{padding:8px 10px;font-size:9px;gap:5px}}
@media(max-width:370px){.le191-grid{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){.le191-live-badge{animation:none}}
</style>
<section class="le191-exam" aria-label="পরীক্ষা বিভাগ">
  <div class="le191-head">
    <div class="le191-icon" aria-hidden="true">▤</div>
    <div class="le191-title"><h2>পরীক্ষা বিভাগ</h2><p>আপনার জন্য উপলব্ধ কুইজভিত্তিক পরীক্ষা</p></div>
    <a class="le191-package-btn" href="{{ route('student.exam-section.packages') }}" aria-label="All Premium Packages">
      <i class="fas fa-crown" aria-hidden="true"></i><span>All Premium Packages</span>
    </a>
  </div>
  <div class="le191-grid">
  @forelse($le191Categories as $le191Cat)
    <a class="le191-link" href="{{ route('student.exam-section.browse-category',['category'=>$le191Cat->id]) }}">
      <span class="le191-mini-icon" aria-hidden="true">▣</span><span>{{ $le191Cat->name }}</span>
      @if(in_array((int)$le191Cat->id,$le191LiveCategoryIds,true))<span class="le191-live-badge" title="Live Exam" aria-label="Live Exam">LIVE</span>@endif
    </a>
  @empty
    <div class="le191-empty">বর্তমানে কোনো Active Exam Category নেই।</div>
  @endforelse
  </div>
  @if(!empty($lebdShowOwnedExamPackages))
    @include('student.partials.exam-section-owned-packages')
  @endif
</section>
