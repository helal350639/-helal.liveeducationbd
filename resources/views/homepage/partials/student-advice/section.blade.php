<link rel="stylesheet" href="{{ asset('web/css/lebd-student-advice-fix125.css') }}?v=125">
@php
$adviceItems = \Illuminate\Support\Facades\Schema::hasTable('lebd_student_advices')
 ? \App\Models\StudentAdvice\StudentAdvice::query()->where('status','Published')->orderBy('sort_order')->orderByDesc('id')->limit(3)->get()
 : collect();
@endphp
@if($adviceItems->isNotEmpty())
<section class="success-students-section" id="lebd-student-advice">
 <div class="success-container">
  <div class="success-heading"><div class="heading-cap"><i class="fas fa-graduation-cap"></i></div><h2>সফল শিক্ষার্থীদের <span>পরামর্শ</span></h2><p>আপনার সাফল্য আমাদের গর্ব — সফলদের অভিজ্ঞতা থেকে শিখুন</p></div>
  {{-- Only this section is a carousel. Existing card content and links are unchanged. --}}
  <div class="lebd-advice-carousel" data-advice-carousel>
   <div class="lebd-advice-viewport">
    <div class="success-grid" data-advice-track>
     @foreach($adviceItems as $item)
      <article class="success-card">
       <div class="student-top"><div class="student-image">
         @if($item->photo_path)<img src="{{ asset($item->photo_path) }}" alt="{{ $item->name }}" loading="lazy" style="width:100%;height:100%;object-fit:cover">
         @else <i class="fa fa-user-circle" aria-hidden="true" style="font-size:54px;color:#b2b9c5"></i> @endif
        </div><div class="student-info"><h3>{{ $item->name }}</h3>
        @if($item->bb_id)<p><i class="fas fa-user"></i> BB ID: {{ $item->bb_id }}</p>@endif
        @if($item->university)<p><i class="fas fa-graduation-cap"></i> {{ $item->university }}</p>@endif
        </div></div>
        <div class="student-content"><div class="student-position"><div class="position-icon"><i class="fas fa-building"></i></div><div><h4>{{ $item->position }}</h4><p>{{ $item->organization }}</p></div></div>
         <div class="advice-title"><span></span><strong>পরামর্শ</strong><span></span></div><div class="lebd-advice-expand"><p class="advice-text lebd-advice-copy" id="lebd-advice-{{ $item->id }}">{{ $item->advice }}</p><button type="button" class="lebd-advice-toggle" aria-expanded="false" aria-controls="lebd-advice-{{ $item->id }}" hidden>See More <i class="fas fa-chevron-down" aria-hidden="true"></i></button></div>
        </div>
      </article>
     @endforeach
    </div>
   </div>
   @if($adviceItems->count() > 1)
    <div class="lebd-advice-nav" aria-label="পরামর্শ স্লাইডার নিয়ন্ত্রণ">
     <button type="button" class="lebd-advice-arrow" data-advice-prev aria-label="আগের পরামর্শ"><i class="fas fa-chevron-left"></i></button>
     <span class="lebd-advice-counter" data-advice-counter aria-live="off">১ / {{ $adviceItems->count() }}</span>
     <button type="button" class="lebd-advice-arrow" data-advice-next aria-label="পরের পরামর্শ"><i class="fas fa-chevron-right"></i></button>
    </div>
   @endif
  </div>
  @if(\App\Models\StudentAdvice\StudentAdvice::where('status','Published')->count()>3)
    <div class="success-more"><a href="{{ url('/student-advice') }}">আরও দেখুন <i class="fas fa-arrow-right"></i></a></div>
  @endif
 </div>
</section>
@endif

<style>
/* LEBD-05: ONLY #lebd-student-advice; other homepage sections untouched. */
#lebd-student-advice .lebd-advice-carousel { width:100%; min-width:0; --advice-visible:3; --advice-gap:22px; }
#lebd-student-advice .lebd-advice-viewport { width:100%; overflow:hidden; touch-action:pan-y pinch-zoom; }
#lebd-student-advice .success-grid {
  display:flex !important; flex-wrap:nowrap !important; width:100%; min-width:0;
  gap:var(--advice-gap) !important; grid-template-columns:none !important;
  transform:translate3d(0,0,0); will-change:transform;
}
#lebd-student-advice .success-grid > .success-card {
  flex:0 0 calc((100% - (var(--advice-visible) - 1) * var(--advice-gap)) / var(--advice-visible)) !important;
  min-width:0; max-width:none; margin:0; box-sizing:border-box;
}
#lebd-student-advice .lebd-advice-nav { display:flex; justify-content:center; align-items:center; gap:16px; margin:17px auto 0; }
#lebd-student-advice .lebd-advice-arrow { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; padding:0; border-radius:50%; border:1px solid #d6e3f2; background:#fff; color:#1255a5; cursor:pointer; }
#lebd-student-advice .lebd-advice-arrow:hover { background:#e9f3ff; }
#lebd-student-advice .lebd-advice-counter {font-size:12px;color:#52627a;font-weight:700;min-width:40px;text-align:center;}
@media(max-width:991px) { #lebd-student-advice .lebd-advice-carousel {--advice-visible:2; --advice-gap:14px;} }
@media(max-width:767px) { #lebd-student-advice .lebd-advice-carousel {--advice-visible:1; --advice-gap:12px;} }
@media(prefers-reduced-motion:reduce) { #lebd-student-advice .success-grid {scroll-behavior:auto;} }
</style>
<script>
/* Self-contained slider, scoped to this single advice section. No external dependencies. */
document.addEventListener('DOMContentLoaded', function () {
  const section = document.getElementById('lebd-student-advice');
  if (!section) return;
  const root = section.querySelector('[data-advice-carousel]');
  const track = root && root.querySelector('[data-advice-track]');
  if (!track) return;
  const cards = Array.from(track.children).filter(el => el.classList.contains('success-card'));
  if (cards.length < 2) return;
  const prev = root.querySelector('[data-advice-prev]');
  const next = root.querySelector('[data-advice-next]');
  const counter = root.querySelector('[data-advice-counter]');
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let busy = false, timer = null, active = 0, startX = null;
  const duration = 430;
  function step() {
    const first = track.querySelector('.success-card');
    return first ? first.getBoundingClientRect().width + (parseFloat(getComputedStyle(track).gap) || 0) : 0;
  }
  function updateCounter() { if (counter) counter.textContent = (active + 1) + ' / ' + cards.length; }
  function reset() { track.style.transition = 'none'; track.style.transform = 'translate3d(0,0,0)'; void track.offsetWidth; }
  function move(dir) {
    if (busy) return;
    const width = step(); if (!width) return;
    busy = true;
    const animate = !motion.matches;
    if (dir < 0) {
      track.insertBefore(track.lastElementChild, track.firstElementChild);
      track.style.transition = 'none';
      track.style.transform = 'translate3d(' + (-width) + 'px,0,0)';
      void track.offsetWidth;
      track.style.transition = animate ? 'transform ' + duration + 'ms ease' : 'none';
      track.style.transform = 'translate3d(0,0,0)';
    } else {
      track.style.transition = animate ? 'transform ' + duration + 'ms ease' : 'none';
      track.style.transform = 'translate3d(' + (-width) + 'px,0,0)';
    }
    active = (active + dir + cards.length) % cards.length;
    updateCounter();
    window.setTimeout(function () {
      if (dir > 0) track.appendChild(track.firstElementChild);
      reset(); busy = false;
    }, animate ? duration + 25 : 0);
  }
  function pause(){ if(timer){clearInterval(timer);timer=null;} }
  function play(){ if(timer || motion.matches || document.hidden) return; timer=window.setInterval(function(){move(1);},3500); }
  function interact(dir){pause();move(dir);play();}
  if(prev) prev.addEventListener('click',function(){interact(-1);});
  if(next) next.addEventListener('click',function(){interact(1);});
  root.addEventListener('mouseenter',pause);
  root.addEventListener('mouseleave',play);
  root.addEventListener('focusin',pause);
  root.addEventListener('focusout',play);
  root.addEventListener('touchstart',function(e){ if(e.touches.length===1){startX=e.touches[0].clientX;pause();} },{passive:true});
  root.addEventListener('touchend',function(e){
    if(startX!==null && e.changedTouches.length) {
      const delta=e.changedTouches[0].clientX-startX;
      if(Math.abs(delta)>45) move(delta<0?1:-1);
    }
    startX=null;play();
  },{passive:true});
  document.addEventListener('visibilitychange',function(){document.hidden?pause():play();});
  window.addEventListener('resize',function(){ if(!busy)reset(); });
  updateCounter();play();
});
</script>
<script src="{{ asset('web/js/lebd-student-advice-fix125.js') }}?v=125" defer></script>
