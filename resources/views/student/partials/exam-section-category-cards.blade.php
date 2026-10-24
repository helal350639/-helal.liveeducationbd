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
.le191-tag{border:1px solid #ffffff40;border-radius:20px;background:#ffffff1b;padding:5px 13px;font-size:11px;font-weight:700}
.le191-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.le191-link{display:flex;align-items:center;position:relative;min-height:46px;padding:9px 32px 9px 15px;border-radius:11px;background:white;color:#0f326b!important;border:1px solid #e0e7fa;text-decoration:none!important;font-size:13px;font-weight:700;line-height:1.3;transition:background .15s}
.le191-link:hover,.le191-link:focus{background:#eaf2ff}.le191-mini-icon{color:#1269e0;margin-right:8px}.le191-dot{position:absolute;right:11px;top:9px;width:9px;height:9px;border-radius:50%;background:#ff2254;animation:le191blink 1.1s ease-in-out infinite}
.le191-empty{grid-column:1/-1;color:#d9e5ff;font-size:13px;padding:8px}.le191-link{opacity:1!important;pointer-events:auto!important;cursor:pointer!important;background:#fff!important}.le191-link:hover,.le191-link:focus{background:#eef5ff!important}
@keyframes le191blink{0%,100%{opacity:1;box-shadow:0 0 0 0 #ff225466}50%{opacity:.3;box-shadow:0 0 0 5px #ff22541a}}
@media(max-width:700px){.le191-exam{padding:14px;border-radius:16px}.le191-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.le191-title h2{font-size:20px}.le191-link{font-size:12px;padding:9px 22px 9px 10px}}
@media(max-width:370px){.le191-grid{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){.le191-dot{animation:none}}
</style>
<section class="le191-exam" aria-label="পরীক্ষা বিভাগ">
  <div class="le191-head">
    <div class="le191-icon" aria-hidden="true">▤</div>
    <div class="le191-title"><h2>পরীক্ষা বিভাগ</h2><p>আপনার জন্য উপলব্ধ কুইজভিত্তিক পরীক্ষা</p></div>
    <span class="le191-tag">EXAM</span>
  </div>
  <div class="le191-grid">
  @forelse($le191Categories as $le191Cat)
    <a class="le191-link" href="{{ route('student.exam-section.browse-category',['category'=>$le191Cat->id]) }}">
      <span class="le191-mini-icon" aria-hidden="true">▣</span><span>{{ $le191Cat->name }}</span>
      @if(in_array((int)$le191Cat->id,$le191LiveCategoryIds,true))<span class="le191-dot" title="Live Exam" aria-label="Live Exam"></span>@endif
    </a>
  @empty
    <div class="le191-empty">বর্তমানে কোনো Active Exam Category নেই।</div>
  @endforelse
  </div>
</section>
