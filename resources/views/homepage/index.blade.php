@php
    $user = Auth::guard('student')->user();
@endphp

@extends('homepage')

@section('title', 'হোম')

@section('content')

{{--
    The master 'homepage' layout already renders homepage.header.
    Do NOT @include('homepage.header') here; that caused the double logo/nav.
    Header Login / student Dashboard / notification and existing routes are not changed.
--}}

<link rel="stylesheet" href="{{ asset('web/css/lebd-home-course-logo-v32.css') }}">
<div class="lebd-homepage-canvas">

@php
    // Keep the site's original live course database filter.
    $courses = \App\Models\Course::where('status', 'Active')
        ->where('web', 'Yes')
        ->orderBy('id', 'ASC')
        ->get();
@endphp

{{-- ================================================
    HOME SHOWCASE — real course/batch data, existing routes
================================================ --}}
<section class="lebd-home-showcase" id="lebd-running-courses" aria-label="চলমান কোর্স ও ব্যাচ">
  <div class="lebd-home-shell">
    <div class="lebd-home-hero">
      <div class="lebd-home-hero-sheen" aria-hidden="true"></div>
      <div class="lebd-home-hero-copy">
        <span class="lebd-home-eyebrow"><i class="fa-solid fa-graduation-cap"></i> LIVE EDUCATION BD <span>· শেখার নির্ভরযোগ্য ঠিকানা</span></span>
        <h1>ঘরে বসেই <span class="lebd-home-highlight">মানসম্মত শিক্ষা</span><br>আপনার স্বপ্নের পথে</h1>
        <p>কোর্স, নিয়মিত ক্লাস, ব্যাচভিত্তিক প্রস্তুতি ও অনলাইন পরীক্ষার সব সুবিধা এক প্ল্যাটফর্মে।</p>
        <div class="lebd-home-feature-checks" aria-label="শিক্ষার সুবিধাসমূহ">
          <span><i class="fa-solid fa-circle-check"></i> অনলাইন কোর্স</span>
          <span><i class="fa-solid fa-circle-check"></i> ব্যাচভিত্তিক প্রস্তুতি</span>
          <span><i class="fa-solid fa-circle-check"></i> নিয়মিত পরীক্ষা</span>
        </div>
        <div class="lebd-home-hero-actions">
          <a href="{{ route('homepage.courses') }}" class="lebd-home-primary-link">কোর্সগুলো দেখুন <i class="fa-solid fa-arrow-right"></i></a>
          <a href="#lebd-running-batches" class="lebd-home-outline-link">চলমান ব্যাচ <i class="fa-solid fa-arrow-down"></i></a>
        </div>
      </div>
      <div class="lebd-home-hero-art" aria-hidden="true">
        <div class="lebd-home-portrait"></div>
        <div class="lebd-home-float lebd-home-float-one"><i class="fa-solid fa-video"></i><span>অনলাইন ক্লাস</span></div>
        <div class="lebd-home-float lebd-home-float-two"><i class="fa-solid fa-file-lines"></i><span>MCQ পরীক্ষা</span></div>
        <div class="lebd-home-float lebd-home-float-three"><i class="fa-solid fa-book-open"></i><span>পাঠ্য উপকরণ</span></div>
      </div>
    </div>

    <div class="lebd-home-quick-features" aria-label="Live Education BD-এর সুবিধা">
      <div><span class="lebd-home-quick-icon blue"><i class="fa-solid fa-graduation-cap"></i></span><span><strong>মানসম্মত কোর্স</strong><small>আপনার লক্ষ্যে এগিয়ে যান</small></span></div>
      <div><span class="lebd-home-quick-icon green"><i class="fa-solid fa-chalkboard-user"></i></span><span><strong>ব্যাচভিত্তিক ক্লাস</strong><small>গুছিয়ে শিখুন</small></span></div>
      <div><span class="lebd-home-quick-icon purple"><i class="fa-solid fa-clipboard-check"></i></span><span><strong>অনলাইন পরীক্ষা</strong><small>নিজেকে যাচাই করুন</small></span></div>
      <div><span class="lebd-home-quick-icon orange"><i class="fa-solid fa-book-open-reader"></i></span><span><strong>শিক্ষা উপকরণ</strong><small>সঠিক প্রস্তুতির সহায়তা</small></span></div>
    </div>

    <div class="lebd-home-section-title">
      <div class="lebd-home-section-title-copy">
        <span class="lebd-home-section-icon"><i class="fa-solid fa-graduation-cap"></i></span>
        <div><h2>চলমান কোর্স সমূহ</h2><p>আপনার জন্য উপলব্ধ কোর্সগুলো থেকে বেছে নিন</p></div>
      </div>
      @if($courses->count() > 3)
        <a href="{{ route('homepage.courses') }}" class="lebd-home-see-all">সবগুলো দেখুন <i class="fa-solid fa-arrow-right"></i></a>
      @endif
    </div>

    <div class="lebd-home-course-grid">
      @forelse($courses->take(3) as $course)
        <article class="lebd-home-course-card">
          <div class="lebd-home-course-image">
            @if($course->recruitmentLogo && $course->recruitmentLogo->logo_path)
              <div class="lebd-home-gov-logo" title="{{ $course->recruitmentLogo->name }}">
                <img src="{{ asset(ltrim($course->recruitmentLogo->logo_path, '/')) }}" alt="{{ $course->recruitmentLogo->name }} অফিসিয়াল লোগো" loading="lazy">
                <strong>{{ $course->recruitmentLogo->short_name }}</strong>
              </div>
            @else
              <img src="{{ $course->banner ? $course->banner : '/web/img/no-course.png' }}" alt="{{ $course->name }}" loading="lazy">
            @endif
            <span class="lebd-home-course-badge"><i class="fa-solid fa-circle-check"></i> চলমান</span>
          </div>
          <div class="lebd-home-course-body">
            <div class="lebd-home-card-kicker">LIVE EDUCATION BD</div>
            <h3 title="{{ $course->name }}">{{ $course->name }}</h3>
            <a href="{{ route('homepage.batches', $course->id) }}" class="lebd-home-course-button">বিস্তারিত দেখুন <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
      @empty
        <div class="lebd-home-empty">এই মুহূর্তে কোনো চলমান কোর্স পাওয়া যায়নি।</div>
      @endforelse
    </div>

    <div class="lebd-home-batch-area" id="lebd-running-batches">
      <div class="lebd-home-section-title lebd-home-batch-title">
        <div class="lebd-home-section-title-copy">
          <span class="lebd-home-section-icon lebd-home-section-green"><i class="fa-solid fa-users-rectangle"></i></span>
          <div><h2>চলমান সকল ব্যাচ</h2><p>যে ব্যাচটি আপনার প্রয়োজন, সেটির বিস্তারিত দেখুন</p></div>
        </div>
      </div>
      <div class="lebd-home-carousel-wrap">
        <button type="button" id="manualCoursePrev" class="lebd-home-arrow lebd-home-prev" aria-label="আগের ব্যাচ"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="lebd-home-batch-slider{{ $batches->count() <= 2 ? ' lebd-home-few-batches' : '' }}" id="manualCourseSlider">
          @forelse($batches as $batch)
            <article class="lebd-home-batch-card">
              <div class="lebd-home-batch-cover">
                <img src="{{ $batch->banner ? asset($batch->banner) : asset('/img/course.png') }}" alt="{{ $batch->name }}" loading="lazy">
                <span class="lebd-home-batch-badge"><i class="fa-solid fa-circle"></i> চলমান ব্যাচ</span>
              </div>
              <div class="lebd-home-batch-body">
                <h3 title="{{ $batch->name }}">{{ $batch->name }}</h3>
                @if($batch->course)
                  <p><i class="fa-solid fa-book-open"></i> কোর্স: {{ $batch->course->name }}</p>
                @endif
                <a href="{{ route('home.course.show', $batch->id) }}" class="lebd-home-batch-button">বিস্তারিত দেখুন <i class="fa-solid fa-arrow-right"></i></a>
              </div>
            </article>
          @empty
            <div class="lebd-home-empty">বর্তমানে কোনো চলমান ব্যাচ নেই।</div>
          @endforelse
        </div>
        <button type="button" id="manualCourseNext" class="lebd-home-arrow lebd-home-next" aria-label="পরের ব্যাচ"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
      <div class="lebd-home-batch-footer">
        <div class="lebd-home-slider-dots" id="manualCourseDots" aria-label="ব্যাচ স্লাইডার পেজ"></div>
        <a href="{{ route('homepage.running.batches') }}" class="lebd-home-batches-more">
          সব ব্যাচ দেখুন <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
      </div>
    </div>
  </div>
</section>

{{-- TWO-COLUMN PROGRAMS CATALOGUE: display-only, links to be connected later. --}}
<section class="lebd-program-section" id="lebd-programs" aria-label="লাইভ ক্লাস এবং রেকর্ড ক্লাস">
  <div class="lebd-program-wrap">
    <article class="lebd-program-panel lebd-program-panel-live">
      <div class="lebd-program-heading">
        <span class="lebd-program-pill"><i class="fa-solid fa-chalkboard-user"></i> লাইভ ক্লাস</span>
        <h2>অনলাইন ব্যাচ ২০২৬ এর সকল সেমিস্টারে ভর্তি চলছে</h2>
        <p>আপনার পছন্দের লাইভ ক্লাস ও সেমিস্টার বেছে নিন</p>
      </div>
      <div class="lebd-program-options" aria-label="লাইভ ক্লাসের বিভাগ">
        <span class="lebd-program-option"><i class="fa-solid fa-video"></i> লাইভ কোর্স</span>
        <span class="lebd-program-option"><i class="fa-solid fa-user-graduate"></i> ডিপ্লোমা অ্যাডমিশন</span>
        <span class="lebd-program-option"><i class="fa-solid fa-lightbulb"></i> সুপার সাজেশন</span>
        <span class="lebd-program-option"><i class="fa-solid fa-book-open"></i> ১ম সেমিস্টার</span>
        <span class="lebd-program-option"><i class="fa-solid fa-book-open"></i> ২য় সেমিস্টার</span>
        <span class="lebd-program-option"><i class="fa-solid fa-book-open"></i> ৩য় সেমিস্টার</span>
        <span class="lebd-program-option"><i class="fa-solid fa-book-open"></i> ৪র্থ সেমিস্টার</span>
        <span class="lebd-program-option"><i class="fa-solid fa-book-open"></i> ৫ম সেমিস্টার</span>
        <span class="lebd-program-option"><i class="fa-solid fa-book-open"></i> ৬ষ্ঠ সেমিস্টার</span>
        <span class="lebd-program-option"><i class="fa-solid fa-book-open"></i> ৭ম সেমিস্টার</span>
      </div>
      <p class="lebd-program-footnote"><i class="fa-solid fa-circle-info"></i> কোর্স লিংক শীঘ্রই যুক্ত করা হবে</p>
    </article>

    <article class="lebd-program-panel lebd-program-panel-recorded">
      <div class="lebd-program-heading">
        <span class="lebd-program-pill"><i class="fa-solid fa-play-circle"></i> রেকর্ড ক্লাস এবং সাজেশন বুক</span>
        <h2>রেকর্ড ক্লাস ও ডিপ্লোমা ব্যাচের ক্যাটাগরিগুলোর সমন্বিত তালিকা</h2>
        <p>নিজের সুবিধামতো পড়ুন, বারবার অনুশীলন করুন</p>
      </div>
      <div class="lebd-program-group">
        <h3><i class="fa-solid fa-circle-play"></i> রেকর্ড ক্লাস</h3>
        <div class="lebd-program-options">
          <span class="lebd-program-option"><i class="fa-solid fa-laptop"></i> নন ডিপার্টমেন্ট ক্লাস</span>
          <span class="lebd-program-option"><i class="fa-solid fa-video"></i> নন ডিপার্টমেন্ট রেকর্ড কোর্স</span>
          <span class="lebd-program-option"><i class="fa-solid fa-play"></i> ডিপার্টমেন্ট ক্লাস</span>
          <span class="lebd-program-option"><i class="fa-solid fa-photo-film"></i> ডিপার্টমেন্ট রেকর্ড কোর্স</span>
        </div>
      </div>
      <div class="lebd-program-group">
        <h3><i class="fa-solid fa-book-open"></i> সাজেশন বুক</h3>
        <div class="lebd-program-options">
          <span class="lebd-program-option"><i class="fa-solid fa-book"></i> 2nd Semester</span>
          <span class="lebd-program-option"><i class="fa-solid fa-book"></i> 3rd Semester</span>
        </div>
      </div>
      <p class="lebd-program-footnote"><i class="fa-solid fa-circle-info"></i> বই ও রেকর্ড ক্লাসের লিংক পরে যুক্ত হবে</p>
    </article>
  </div>
</section>

<style>
/* Premium presentation is restricted to the new home showcase and free course area. */
.lebd-home-portrait{background-image:url("data:image/webp;base64,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");}

.lebd-home-showcase,.lebd-free-section{width:100%;box-sizing:border-box;font-family:inherit}
.lebd-home-showcase{background:linear-gradient(180deg,#f4f8ff 0%,#f8fbff 73%,#fff 100%);padding:16px 22px 8px}
.lebd-home-shell,.lebd-free-shell{max-width:1440px;margin:0 auto;box-sizing:border-box}
.lebd-home-hero{position:relative;isolation:isolate;display:grid;grid-template-columns:56% 44%;align-items:center;min-height:385px;padding:38px clamp(25px,4vw,64px);border-radius:24px;background:radial-gradient(ellipse at 85% 10%,rgba(12,151,255,.34),transparent 38%),radial-gradient(circle at 70% 100%,rgba(2,65,174,.5),transparent 42%),linear-gradient(110deg,#06132f 0%,#082658 53%,#054b9b 100%);box-shadow:0 16px 42px rgba(7,35,88,.18);color:#fff;overflow:hidden}
.lebd-home-hero:before{content:"";position:absolute;z-index:-1;left:-75px;bottom:-190px;width:410px;height:410px;border:1px solid rgba(91,204,255,.15);border-radius:50%}
.lebd-home-hero:after{content:"";position:absolute;z-index:-1;right:-130px;top:-140px;width:500px;height:500px;border:1px solid rgba(255,255,255,.08);border-radius:50%}
.lebd-home-hero-sheen{position:absolute;inset:0;z-index:-1;opacity:.36;pointer-events:none;background-image:radial-gradient(rgba(48,190,255,.7) 1.5px,transparent 1.5px);background-size:33px 33px;mask-image:linear-gradient(90deg,transparent 6%,transparent 57%,black 100%)}
.lebd-home-hero-copy{position:relative;z-index:3;min-width:0}
.lebd-home-eyebrow{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border:1px solid rgba(255,255,255,.24);background:rgba(0,83,188,.33);border-radius:30px;color:#eff7ff;font-weight:750;font-size:12px;line-height:1.4;max-width:100%;flex-wrap:wrap}
.lebd-home-eyebrow i{color:#facc15}.lebd-home-eyebrow span{font-size:11px;color:#d8e9ff;font-weight:500}
.lebd-home-hero h1{margin:19px 0 11px;color:white;font-size:clamp(29px,3.1vw,45px);line-height:1.38;font-weight:900;letter-spacing:-.035em}
.lebd-home-highlight{background:linear-gradient(90deg,#38ffce,#67d7ff);-webkit-background-clip:text;background-clip:text;color:transparent}
.lebd-home-hero p{margin:0;max-width:610px;color:#dceafa;line-height:1.8;font-size:clamp(14px,1.12vw,17px)}
.lebd-home-feature-checks{display:flex;flex-wrap:wrap;gap:8px 16px;margin-top:16px;color:#edf8ff;font-size:12px;font-weight:700}
.lebd-home-feature-checks span{display:inline-flex;gap:6px;align-items:center}.lebd-home-feature-checks i{color:#22dd8b}
.lebd-home-hero-actions{display:flex;gap:11px;flex-wrap:wrap;margin-top:21px}
.lebd-home-primary-link,.lebd-home-outline-link{display:inline-flex;align-items:center;justify-content:center;gap:11px;min-height:43px;padding:10px 19px;border-radius:11px;font-size:14px;font-weight:800;text-decoration:none!important;transition:transform .2s,background-color .2s}
.lebd-home-primary-link{background:linear-gradient(120deg,#13cb81,#059c67);box-shadow:0 7px 20px rgba(0,202,125,.22);color:#fff!important}.lebd-home-primary-link:hover{background:#08a973;transform:translateY(-2px)}
.lebd-home-outline-link{border:1px solid rgba(255,255,255,.58);background:rgba(255,255,255,.07);color:#fff!important}.lebd-home-outline-link:hover{background:rgba(255,255,255,.16);transform:translateY(-2px)}
.lebd-home-hero-art{position:relative;height:320px;min-width:0;z-index:1}
.lebd-home-portrait{position:absolute;bottom:-38px;right:5%;height:360px;width:75%;background-size:cover;background-position:center 16%;-webkit-mask-image:linear-gradient(to right,transparent 0%,black 17%,black 96%);mask-image:linear-gradient(to right,transparent 0%,black 17%,black 96%);filter:drop-shadow(0 8px 24px rgba(0,0,0,.25))}
.lebd-home-float{position:absolute;z-index:3;display:flex;gap:9px;align-items:center;padding:10px 13px;border:1px solid rgba(255,255,255,.3);border-radius:11px;color:#f5faff;background:rgba(6,26,60,.79);font-size:12px;font-weight:700;box-shadow:0 10px 22px rgba(0,0,0,.2);backdrop-filter:blur(8px)}
.lebd-home-float i{font-size:17px;color:#4cf4c4}.lebd-home-float-one{top:13px;right:0}.lebd-home-float-two{right:-2%;bottom:22px}.lebd-home-float-three{left:0;top:41%}
.lebd-home-quick-features{position:relative;z-index:5;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:0;max-width:1310px;width:94%;margin:-24px auto 0;border:1px solid #e7eef7;border-radius:17px;background:#fff;box-shadow:0 10px 31px rgba(12,65,140,.09);overflow:hidden}
.lebd-home-quick-features>div{display:flex;align-items:center;gap:11px;padding:17px 18px;min-width:0;border-right:1px solid #e8eff7}.lebd-home-quick-features>div:last-child{border:0}
.lebd-home-quick-icon{width:43px;height:43px;flex:0 0 43px;display:flex;justify-content:center;align-items:center;border-radius:11px;color:#fff;font-size:19px}
.lebd-home-quick-icon.blue{background:#147bf2}.lebd-home-quick-icon.green{background:#0fbf70}.lebd-home-quick-icon.purple{background:#8050ed}.lebd-home-quick-icon.orange{background:#fb9f16}
.lebd-home-quick-features strong{display:block;color:#0e2752;font-size:14px;font-weight:800;line-height:1.3}
.lebd-home-quick-features small{display:block;color:#71839b;font-size:11px;margin-top:4px;line-height:1.4}
.lebd-home-section-title{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:32px 3px 16px}
.lebd-home-section-title-copy{display:flex;align-items:center;gap:11px;min-width:0}
.lebd-home-section-icon{width:47px;height:47px;flex:0 0 47px;display:flex;align-items:center;justify-content:center;border-radius:13px;color:#fff;font-size:20px;background:#096de1;box-shadow:0 6px 17px rgba(0,99,221,.19)}
.lebd-home-section-title h2{margin:0;font-size:clamp(20px,1.6vw,25px);font-weight:850;line-height:1.4;color:#092754}
.lebd-home-section-title p{margin:2px 0 0;font-size:12px;line-height:1.4;color:#697f9b}
.lebd-home-see-all{flex-shrink:0;padding:9px 16px;border:1px solid #c2dcff;border-radius:22px;background:#fff;color:#0969d9!important;font-size:12px;font-weight:800;text-decoration:none!important}.lebd-home-see-all:hover{background:#eaf4ff}
.lebd-home-course-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.lebd-home-course-card{display:flex;align-items:stretch;overflow:hidden;min-height:145px;min-width:0;border:1px solid #dce9f8;border-radius:15px;background:#fff;box-shadow:0 5px 18px rgba(24,72,120,.08);transition:transform .23s,box-shadow .23s}
.lebd-home-course-card:hover{transform:translateY(-3px);box-shadow:0 12px 27px rgba(24,72,120,.14)}
.lebd-home-course-image{position:relative;flex:0 0 37%;min-width:0;overflow:hidden;background:linear-gradient(120deg,#d5ebff,#f0f8ff)}
.lebd-home-course-card:nth-child(2) .lebd-home-course-image{background:linear-gradient(120deg,#e9e1ff,#f5f1ff)}
.lebd-home-course-card:nth-child(3) .lebd-home-course-image{background:linear-gradient(120deg,#d7f7ec,#edfff8)}
.lebd-home-course-image img{width:100%;height:100%;max-height:240px;object-fit:cover;display:block}
.lebd-home-course-badge{position:absolute;left:7px;top:8px;padding:4px 7px;display:inline-flex;align-items:center;gap:4px;border-radius:7px;font-size:10px;font-weight:800;background:#0879ed;color:#fff}
.lebd-home-course-body{min-width:0;flex:1;display:flex;flex-direction:column;padding:14px}
.lebd-home-card-kicker{font-size:10px;letter-spacing:.04em;font-weight:800;color:#0575c7}
.lebd-home-course-body h3{margin:8px 0 12px;font-size:clamp(15px,1.2vw,19px);line-height:1.45;font-weight:850;color:#112d5c;overflow-wrap:anywhere}
.lebd-home-course-button{display:flex;align-items:center;justify-content:center;gap:7px;margin-top:auto;padding:9px 9px;border-radius:10px;background:#0868dd;color:#fff!important;text-decoration:none!important;font-size:12px;font-weight:800}.lebd-home-course-button:hover{background:#034fab}
.lebd-home-batch-area{padding:2px 0 17px}.lebd-home-section-green{background:#06a86c;box-shadow:0 5px 14px rgba(0,147,93,.17)}
.lebd-home-carousel-wrap{position:relative;padding:0 2px}
.lebd-home-batch-slider,.lebd-free-slider{display:flex;gap:16px;overflow-x:auto;scroll-snap-type:x mandatory;scroll-behavior:smooth;scrollbar-width:none;overscroll-behavior-x:contain;padding:4px 3px 12px}
.lebd-home-batch-slider::-webkit-scrollbar,.lebd-free-slider::-webkit-scrollbar{display:none}
.lebd-home-batch-card{flex:0 0 calc((100% - 32px)/3);min-width:0;scroll-snap-align:start;border:1px solid #dce8f5;border-radius:15px;background:#fff;overflow:hidden;box-shadow:0 6px 21px rgba(13,56,110,.08)}
.lebd-home-batch-cover{position:relative;height:165px;background:#ebf5fd;overflow:hidden}.lebd-home-batch-cover img{height:100%;width:100%;object-fit:cover;display:block}
.lebd-home-batch-badge{position:absolute;top:9px;left:9px;padding:6px 9px;display:inline-flex;align-items:center;gap:6px;border-radius:7px;background:#0aa963;color:#fff;font-size:11px;font-weight:800}.lebd-home-batch-badge i{font-size:7px}
.lebd-home-batch-body{display:flex;flex-direction:column;padding:13px 14px 15px;min-height:133px}
.lebd-home-batch-body h3{margin:0 0 6px;font-size:17px;font-weight:850;color:#112d5b;line-height:1.45;overflow-wrap:anywhere}
.lebd-home-batch-body p{margin:0 0 13px;font-size:12px;color:#637f9e;line-height:1.5}.lebd-home-batch-body p i{color:#0b74e8}
.lebd-home-batch-button{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:auto;padding:10px;border-radius:10px;background:#08a75e;color:white!important;text-decoration:none!important;font-size:12px;font-weight:800}.lebd-home-batch-button:hover{background:#06894e}
.lebd-home-arrow{position:absolute;top:45%;z-index:4;transform:translateY(-50%);width:39px;height:39px;display:flex;align-items:center;justify-content:center;border:1px solid #dde9f5;border-radius:50%;background:#fff;color:#0869d3;box-shadow:0 5px 18px rgba(0,38,94,.13);cursor:pointer}
.lebd-home-arrow:hover{background:#096bdf;color:#fff}.lebd-home-prev{left:-15px}.lebd-home-next{right:-15px}
.lebd-home-slider-dots{display:flex;justify-content:center;gap:7px;align-items:center;min-height:20px;margin:3px auto 0}
.lebd-home-slider-dot{width:8px;height:8px;padding:0;border:0;border-radius:6px;background:#c3d3e5;cursor:pointer;transition:.2s}
.lebd-home-slider-dot.active{width:22px;background:#1279e9}
.lebd-home-empty{grid-column:1/-1;width:100%;padding:26px;text-align:center;border:1px dashed #c9dced;border-radius:13px;background:#f9fcff;color:#5d7290}
.lebd-free-section{padding:9px 22px 26px;background:#fff}
.lebd-free-shell{padding:24px 26px;border-radius:21px;border:1px solid #e4eef4;background:linear-gradient(120deg,#f1fbff,#f4fff9 65%,#f8f5ff);box-shadow:0 7px 23px rgba(5,62,100,.05)}
.lebd-free-header{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:15px}
.lebd-free-header-copy{display:flex;gap:11px;align-items:center}.lebd-free-header-icon{flex:0 0 46px;width:46px;height:46px;display:flex;align-items:center;justify-content:center;border-radius:13px;background:#07ac65;color:#fff;font-size:19px}
.lebd-free-header h2{margin:0;color:#102d59;font-size:clamp(20px,1.6vw,25px);font-weight:850}.lebd-free-header p{margin:3px 0 0;font-size:12px;color:#68829b}
.lebd-free-flag{padding:8px 11px;border-radius:22px;color:#056c3c;background:#def9ea;white-space:nowrap;font-size:11px;font-weight:800}
.lebd-free-feature-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.lebd-free-feature-card{display:flex;min-height:135px;min-width:0;overflow:hidden;border:1px solid #dcece6;border-radius:13px;background:#fff;box-shadow:0 6px 16px rgba(20,75,93,.06)}
.lebd-free-feature-image{width:30%;flex-shrink:0;background:#e4f6ed;overflow:hidden}.lebd-free-feature-image img{width:100%;height:100%;object-fit:cover;display:block}
.lebd-free-feature-content{flex:1;min-width:0;display:flex;flex-direction:column;align-items:flex-start;padding:15px}
.lebd-free-tag{padding:4px 9px;border-radius:7px;color:#06824a;background:#dbf8e8;font-size:10px;font-weight:800}
.lebd-free-feature-content h3{margin:8px 0 9px;color:#11315e;font-size:17px;font-weight:850;line-height:1.45;overflow-wrap:anywhere}
.lebd-free-feature-link{margin-top:auto;display:inline-flex;align-items:center;gap:8px;color:#0870d3!important;font-size:12px;font-weight:800;text-decoration:none!important}
.lebd-free-subtitle h3{margin:21px 0 12px;color:#133561;font-size:17px;font-weight:800}.lebd-free-subtitle h3 i{color:#08a564}
.lebd-free-mini-card{flex:0 0 calc((100% - 48px)/4);min-width:0;overflow:hidden;scroll-snap-align:start;border-radius:13px;border:1px solid #e2eee8;background:#fff;box-shadow:0 5px 15px rgba(12,60,92,.06)}
.lebd-free-mini-image{height:128px;overflow:hidden;background:#ecf8f3}.lebd-free-mini-image img{width:100%;height:100%;object-fit:cover;display:block}
.lebd-free-mini-body{min-height:119px;padding:12px;display:flex;flex-direction:column;align-items:flex-start}
.lebd-free-mini-body h4{margin:7px 0 10px;color:#15365f;font-size:15px;line-height:1.45;font-weight:800;overflow-wrap:anywhere}
.lebd-free-mini-body a{width:100%;margin-top:auto;padding:8px 10px;border-radius:8px;background:#08a95e;color:#fff!important;text-align:center;text-decoration:none!important;font-size:12px;font-weight:800}
@media(max-width:1150px){
.lebd-home-hero{grid-template-columns:56% 44%;padding:28px 36px;min-height:360px}.lebd-home-portrait{height:340px}.lebd-home-hero h1{font-size:34px}
.lebd-home-quick-features>div{padding:14px 12px}.lebd-home-quick-features strong{font-size:12px}.lebd-home-quick-features small{font-size:10px}
.lebd-home-course-grid{gap:11px}.lebd-home-course-card{min-height:140px}.lebd-home-course-image{flex-basis:33%}
}
@media(max-width:900px){
.lebd-home-hero{grid-template-columns:1fr 0.7fr}.lebd-home-float{font-size:10px}.lebd-home-float-three{display:none}
.lebd-home-quick-features{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-home-quick-features>div:nth-child(2){border-right:0}.lebd-home-quick-features>div:nth-child(-n+2){border-bottom:1px solid #e8eff7}
.lebd-home-course-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-home-batch-card{flex-basis:calc((100% - 16px)/2)}.lebd-free-mini-card{flex-basis:calc((100% - 32px)/3)}
}
@media(max-width:680px){
.lebd-home-showcase,.lebd-free-section{padding:10px 10px 12px}
.lebd-home-hero{display:block;min-height:auto;border-radius:18px;padding:23px 17px 19px}.lebd-home-hero h1{font-size:29px;line-height:1.45;margin:15px 0 9px}.lebd-home-hero p{font-size:13px}.lebd-home-eyebrow{font-size:10px}.lebd-home-eyebrow span{font-size:10px}
.lebd-home-feature-checks{gap:7px 11px;font-size:10px}.lebd-home-hero-actions{margin-top:16px}.lebd-home-primary-link,.lebd-home-outline-link{font-size:12px;min-height:39px;padding:8px 13px}
.lebd-home-hero-art{display:none}
.lebd-home-quick-features{width:95%;margin:-8px auto 0;border-radius:12px}.lebd-home-quick-features>div{padding:10px;gap:8px}.lebd-home-quick-icon{width:33px;height:33px;flex-basis:33px;border-radius:9px;font-size:15px}.lebd-home-quick-features strong{font-size:11px}.lebd-home-quick-features small{font-size:9px}
.lebd-home-section-title{gap:9px;margin:23px 0 13px}.lebd-home-section-title h2{font-size:19px}.lebd-home-section-title p{font-size:10px}.lebd-home-section-icon{width:40px;height:40px;flex-basis:40px;font-size:18px}
.lebd-home-see-all{font-size:10px;padding:7px 9px}.lebd-home-course-grid{grid-template-columns:1fr;gap:10px}
.lebd-home-course-card{min-height:111px;border-radius:12px}.lebd-home-course-image{flex-basis:34%}.lebd-home-course-badge{font-size:8px;padding:4px 5px}.lebd-home-card-kicker{font-size:8px}
.lebd-home-course-body{padding:10px}.lebd-home-course-body h3{font-size:15px;margin:5px 0 7px}.lebd-home-course-button{font-size:11px;padding:8px}
.lebd-home-batch-card{flex:0 0 85%}.lebd-home-batch-cover{height:165px}.lebd-home-batch-body{min-height:126px}.lebd-home-batch-body h3{font-size:16px}
.lebd-home-arrow{width:32px;height:32px}.lebd-home-prev{left:-6px}.lebd-home-next{right:-6px}
.lebd-free-shell{padding:16px 11px;border-radius:15px}.lebd-free-header h2{font-size:19px}.lebd-free-header p{font-size:10px}.lebd-free-header-icon{width:38px;height:38px;flex-basis:38px;font-size:17px}.lebd-free-flag{font-size:9px;padding:6px 8px}
.lebd-free-feature-grid{grid-template-columns:1fr;gap:10px}.lebd-free-feature-card{min-height:110px}.lebd-free-feature-content{padding:11px}.lebd-free-feature-content h3{font-size:14px}.lebd-free-feature-link{font-size:11px}.lebd-free-feature-image{width:34%}
.lebd-free-mini-card{flex:0 0 80%}.lebd-free-mini-image{height:145px}.lebd-free-mini-body h4{font-size:14px}
}
@media(max-width:370px){.lebd-home-hero h1{font-size:25px}.lebd-home-section-title h2{font-size:17px}.lebd-free-flag{display:none}}
@media(prefers-reduced-motion:reduce){.lebd-home-batch-slider,.lebd-free-slider{scroll-behavior:auto}.lebd-home-course-card,.lebd-home-primary-link,.lebd-home-outline-link{transition:none}}
</style>

<script>
// Live Education BD: infinite carousel, including when only TWO running batches exist.
// Only the existing batch cards and their URLs are duplicated for visual continuity.
document.addEventListener('DOMContentLoaded', function () {
  const track = document.getElementById('manualCourseSlider');
  const prev = document.getElementById('manualCoursePrev');
  const next = document.getElementById('manualCourseNext');
  const dots = document.getElementById('manualCourseDots');

  if (track && prev && next && dots) {
    const originals = Array.from(track.children).filter(el => el.matches('article.lebd-home-batch-card'));
    const count = originals.length;
    let ticker = null;
    let positionTimer = null;

    function cardStep() {
      if (!originals.length) return 0;
      const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 0;
      return originals[0].getBoundingClientRect().width + gap;
    }

    if (count > 1) {
      // Repeat the existing records solely within the carousel (no new database records).
      // The extra slides enable movement even when there are only two actual batch cards.
      const visibleCards = Math.max(1, Math.ceil(track.clientWidth / Math.max(1, cardStep())));
      const required = count * 2 + visibleCards + 2;
      for (let index = count; index < required; index++) {
        const clone = originals[(index - count) % count].cloneNode(true);
        clone.classList.add('lebd-home-batch-clone');
        clone.setAttribute('aria-hidden', 'true');
        clone.querySelectorAll('a,button,[tabindex]').forEach(el => el.setAttribute('tabindex','-1'));
        track.appendChild(clone);
      }
    }

    if (!count) {
      prev.hidden = true; next.hidden = true; dots.hidden = true;
    } else {
      for (let index = 0; index < count; index++) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'lebd-home-slider-dot';
        dot.setAttribute('aria-label', 'ব্যাচ ' + (index + 1));
        dot.addEventListener('click', function () {
          stop();
          track.scrollTo({left: index * cardStep(), behavior:'smooth'});
          start();
        });
        dots.appendChild(dot);
      }
      const dotButtons = [...dots.querySelectorAll('button')];

      function teleport(left) {
        // Same batch sequence repeats here; reset without a visible animation.
        const snap = track.style.scrollSnapType;
        const behavior = track.style.scrollBehavior;
        track.style.scrollSnapType = 'none';
        track.style.scrollBehavior = 'auto';
        track.scrollLeft = left;
        track.style.scrollBehavior = behavior;
        track.style.scrollSnapType = snap;
      }
      function normalize() {
        const cycle = cardStep() * count;
        if (!cycle) return;
        if (track.scrollLeft >= cycle - 3) teleport(track.scrollLeft - cycle);
      }
      function updateDots() {
        if (!count) return;
        const step = Math.max(1, cardStep());
        const current = ((Math.round(track.scrollLeft / step) % count) + count) % count;
        dotButtons.forEach((dot,index) => {
          dot.classList.toggle('active', index === current);
          dot.setAttribute('aria-current', index === current ? 'true' : 'false');
        });
      }
      function move(direction) {
        if (count < 2) return;
        const step = cardStep();
        const cycle = step * count;
        if (!step) return;
        if (direction > 0 && track.scrollLeft >= cycle - step / 3) {
          teleport(track.scrollLeft - cycle);
        } else if (direction < 0 && track.scrollLeft <= step / 3) {
          teleport(track.scrollLeft + cycle);
        }
        track.scrollBy({left: direction * step, behavior:'smooth'});
        clearTimeout(positionTimer);
        positionTimer = setTimeout(function(){ normalize(); updateDots(); }, 650);
      }
      function stop() {
        if (ticker) { clearInterval(ticker); ticker = null; }
      }
      function start() {
        if (ticker || count < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        ticker = setInterval(function () { if (!document.hidden) move(1); }, 3200);
      }
      prev.hidden = count < 2;
      next.hidden = count < 2;
      dots.hidden = count < 2;
      prev.addEventListener('click', function(){ stop(); move(-1); start(); });
      next.addEventListener('click', function(){ stop(); move(1); start(); });
      track.addEventListener('scroll', updateDots, {passive:true});
      track.addEventListener('mouseenter', stop);
      track.addEventListener('mouseleave', start);
      track.addEventListener('focusin', stop);
      track.addEventListener('focusout', start);
      track.addEventListener('touchstart', stop, {passive:true});
      track.addEventListener('touchend', start, {passive:true});
      window.addEventListener('resize', function(){ normalize(); updateDots(); });
      updateDots();
      start();
    }
  }

  // Preserve the existing signed-in Student Dashboard menu enhancement.
  const homeAnchors = Array.from(document.querySelectorAll('a')).filter(function(a){
    const label=(a.textContent||'').replace(/\s+/g,' ').trim();
    return label==='হোম' && !a.closest('.lebd-home-showcase,.lebd-program-section');
  });
  homeAnchors.forEach(function(homeAnchor){
    const menu=homeAnchor.closest('ul') || homeAnchor.parentElement;
    if(!menu) return;
    if(Array.from(menu.querySelectorAll('a')).some(function(a){return /^(dashboard|ড্যাশবোর্ড)$/i.test((a.textContent||'').trim());})) return;
    const a=document.createElement('a');
    a.href=@json(url('/students'));
    a.textContent='ড্যাশবোর্ড';
    a.className=(homeAnchor.className||'').replace(/\b(active|current|selected)\b/g,'').trim();
    a.setAttribute('aria-label','স্টুডেন্ট ড্যাশবোর্ড');
    const parent=homeAnchor.parentElement;
    if(parent && parent.tagName.toLowerCase()==='li'){
      const li=document.createElement('li');
      li.className=(parent.className||'').replace(/\b(active|current|selected)\b/g,'').trim();
      li.appendChild(a);parent.insertAdjacentElement('afterend',li);
    } else {homeAnchor.insertAdjacentElement('afterend',a);}
  });
});
</script>

{{-- =========================================================
     DAILY UPDATE + DAILY FREE QUIZ
========================================================= --}}

<section class="daily-feature-section">

    <div class="daily-feature-grid">

        {{-- ================= DAILY UPDATE ================= --}}
        <div class="daily-feature-card daily-update-card">

            <div class="daily-feature-icon">
                📰
            </div>

            <div class="daily-feature-content">

                <h2>Daily Update</h2>

                <p>
                    সর্বশেষ আপডেট পেতে চোখ রাখুন
                </p>

                <a href="{{ url('/daily-update') }}"
                   class="daily-feature-btn">
                    সব আপডেট দেখুন →
                </a>

            </div>

        </div>


        {{-- ================= DAILY FREE QUIZ ================= --}}
        <div class="daily-feature-card daily-quiz-card">

            <div class="daily-feature-icon">
                💡
            </div>

            <div class="daily-feature-content">

                <h2>Daily Free Quiz</h2>

                <p>
                    নিজেকে যাচাই করুন, আরও এগিয়ে যান
                </p>

                {{-- Quiz route পরে connection করবেন --}}
                <a href="#"
                   class="daily-feature-btn">
                    এখনই কুইজ দিন →
                </a>

            </div>

        </div>

    </div>

</section>


<style>

/* =========================================================
   DAILY UPDATE + DAILY FREE QUIZ
========================================================= */

.daily-feature-section{
    width:100%;
    max-width:1480px;
    margin:25px auto;
    padding:0 20px;
}

.daily-feature-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:22px;
}

.daily-feature-card{
    min-height:195px;
    border-radius:20px;
    padding:25px 32px;

    display:flex;
    align-items:center;
    gap:25px;

    position:relative;
    overflow:hidden;

    box-shadow:
        0 8px 25px rgba(0,0,0,.08);

    transition:.3s ease;
}

.daily-feature-card:hover{
    transform:translateY(-4px);
    box-shadow:
        0 14px 30px rgba(0,0,0,.12);
}


/* Daily Update */

.daily-update-card{
    background:
        linear-gradient(
            135deg,
            #e9f2ff,
            #d6e7ff
        );
}


/* Daily Quiz */

.daily-quiz-card{
    background:
        linear-gradient(
            135deg,
            #e8fff4,
            #cef5e2
        );
}


.daily-feature-icon{
    width:130px;
    height:130px;

    border-radius:25px;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:65px;

    flex-shrink:0;

    background:rgba(255,255,255,.65);

    box-shadow:
        0 8px 20px rgba(0,0,0,.06);
}


.daily-feature-content{
    flex:1;
}


.daily-feature-content h2{
    margin:0 0 8px;

    font-size:38px;
    font-weight:800;

    color:#123c78;
}


.daily-quiz-card h2{
    color:#087044;
}


.daily-feature-content p{
    margin:0 0 20px;

    font-size:22px;

    color:#314b6b;
}


.daily-quiz-card p{
    color:#27604a;
}


.daily-feature-btn{
    display:inline-flex;
    align-items:center;

    padding:12px 28px;

    border-radius:30px;

    background:#0878ed;
    color:#fff !important;

    text-decoration:none !important;

    font-size:20px;
    font-weight:700;

    box-shadow:
        0 5px 15px rgba(0,100,220,.20);

    transition:.25s ease;
}


.daily-feature-btn:hover{
    transform:translateY(-2px);
    opacity:.92;
}


.daily-quiz-card .daily-feature-btn{
    background:#159b63;
}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:768px){

    .daily-feature-section{
        padding:0 12px;
        margin:18px auto;
    }

    .daily-feature-grid{
        grid-template-columns:1fr;
        gap:14px;
    }

    .daily-feature-card{
        min-height:125px;
        padding:15px;

        border-radius:16px;

        gap:15px;
    }

    .daily-feature-icon{
        width:75px;
        height:75px;

        border-radius:16px;

        font-size:38px;
    }

    .daily-feature-content h2{
        font-size:22px;
        margin-bottom:4px;
    }

    .daily-feature-content p{
        font-size:13px;
        margin-bottom:10px;
    }

    .daily-feature-btn{
        padding:7px 15px;
        font-size:13px;
        border-radius:20px;
    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media(max-width:400px){

    .daily-feature-card{
        min-height:110px;
        padding:12px;
    }

    .daily-feature-icon{
        width:62px;
        height:62px;
        font-size:30px;
    }

    .daily-feature-content h2{
        font-size:19px;
    }

    .daily-feature-content p{
        font-size:11px;
    }

    .daily-feature-btn{
        padding:6px 12px;
        font-size:11px;
    }

}

</style>



{{-- E-Book homepage: dynamically loaded from Admin E-Book Categories. --}}
@include('homepage.partials.ebook.section')

{{-- MCQ lives in an isolated partial; all unrelated homepage sections are preserved. --}}
@include('homepage.job-preparation.partials.home-sections')

{{-- =========================================================
     নিয়োগ বিজ্ঞপ্তি + নিয়োগ বিজ্ঞপ্তি স্লাইডার
========================================================= --}}

@php
    // Live Circulars: Bangladesh calendar dates, latest edited first.
    $lebdCircularToday = \Carbon\Carbon::today('Asia/Dhaka')->toDateString();
    $lebdCircularQuery = \App\Models\Circular::query()
        ->whereIn('status', ['Active', 'Published'])
        ->whereNotNull('start_at')
        ->whereNotNull('end_at')
        ->whereDate('start_at', '<=', $lebdCircularToday)
        ->whereDate('end_at', '>=', $lebdCircularToday)
        ->orderByDesc('updated_at')
        ->orderByDesc('id');
    $lebdCircularCount = (clone $lebdCircularQuery)->count();
    $lebdMainCirculars = (clone $lebdCircularQuery)->limit(3)->get();
    $lebdSliderCirculars = (clone $lebdCircularQuery)->limit(10)->get();
@endphp
<section class="job-notice-wrapper">
    <div class="job-notice-main">
        <div class="job-dots left-dots" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
        <div class="job-megaphone" aria-hidden="true"><i class="fas fa-bullhorn"></i></div>
        <div class="job-notice-title">
            <div class="title-line left-line"></div>
            <div class="job-title-pill"><i class="fas fa-bullhorn"></i><span>নিয়োগ বিজ্ঞপ্তি</span></div>
            <div class="title-line right-line"></div>
        </div>
        <div class="job-notice-top">
            <h2>বর্তমানে আবেদন চলমান নিয়োগ বিজ্ঞপ্তি</h2>
            <a href="{{ route('home.circular') }}" class="all-job-button"><i class="far fa-calendar-alt"></i> সব দেখুন <span>→</span></a>
        </div>
        @forelse($lebdMainCirculars as $lebdCircular)
            @php
                $lebdImages = json_decode($lebdCircular->image ?? '', true);
                $lebdImage = is_array($lebdImages) ? ($lebdImages[0] ?? null) : $lebdCircular->image;
                $lebdImageUrl = $lebdImage ? (preg_match('~^https?://~i', $lebdImage) ? $lebdImage : asset(ltrim($lebdImage, '/'))) : null;
            @endphp
            <article class="featured-job-card">
                <div class="job-logo-box"><div class="job-logo-placeholder"><i class="fas fa-landmark"></i></div></div>
                <div class="job-divider"></div>
                <div class="job-information">
                    <h3>{{ $lebdCircular->title }}</h3>
                    <div class="job-meta">
                        <span><i class="fas fa-briefcase"></i> {{ $lebdCircular->type ?: 'নিয়োগ বিজ্ঞপ্তি' }}</span>
                        @if($lebdCircular->published_at)
                        <span><i class="far fa-calendar-alt"></i> প্রকাশিত: {{ \Carbon\Carbon::parse($lebdCircular->published_at)->format('d/m/Y') }}</span>
                        @endif
                        @if($lebdCircular->position)
                        <span><i class="far fa-file-alt"></i> পদ: {{ $lebdCircular->position }}</span>
                        @endif
                    </div>
                    <p>আবেদনের শেষ সময়: {{ \Carbon\Carbon::parse($lebdCircular->end_at)->format('d/m/Y') }}</p>
                </div>
                <div class="job-feature-image">
                    @if($lebdImageUrl)
                        <img src="{{ $lebdImageUrl }}" alt="{{ $lebdCircular->title }}" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="job-image-placeholder" style="display:none"><i class="fas fa-building"></i></div>
                    @else
                        <div class="job-image-placeholder"><i class="fas fa-building"></i></div>
                    @endif
                </div>
                <a href="{{ route('home.circular.show', $lebdCircular->id) }}" class="job-details-button">বিস্তারিত দেখুন <span>→</span></a>
            </article>
        @empty
            <div class="lebd-home-empty">এই মুহূর্তে আবেদন চলমান কোনো নিয়োগ বিজ্ঞপ্তি নেই।</div>
        @endforelse
        <div class="job-info-bar">
            <div class="job-info-left"><div class="job-light-icon"><i class="fas fa-lightbulb"></i></div><p>বিজ্ঞপ্তির বিস্তারিত জানতে <strong>বিস্তারিত দেখুন</strong> ক্লিক করুন।</p></div>
            <div class="job-info-right"><i class="fas fa-paper-plane"></i><p>আপনার স্বপ্নের চাকরির প্রস্তুতি আজই শুরু করুন!</p></div>
        </div>
    </div>
    <div class="job-slider-section">
        <div class="job-slider-title"><div></div><h2><span></span> নিয়োগ বিজ্ঞপ্তি স্লাইডার</h2><div></div></div>
        <div class="job-slider-container">
            <button class="job-slider-arrow job-prev" type="button" onclick="jobSlide(-1)" aria-label="আগের বিজ্ঞপ্তি"><i class="fas fa-chevron-left"></i></button>
            <div class="job-slider-track" id="jobSliderTrack">
                @forelse($lebdSliderCirculars as $lebdCircular)
                <a href="{{ route('home.circular.show', $lebdCircular->id) }}" class="job-slider-card {{ ['slider-pink','slider-blue','slider-green','slider-orange','slider-purple','slider-yellow'][$loop->index % 6] }}">
                    <div class="slider-logo"><i class="fas fa-briefcase"></i></div>
                    <h3>{{ $lebdCircular->title }}</h3>
                    <p><i class="far fa-calendar-alt"></i> শেষ: {{ \Carbon\Carbon::parse($lebdCircular->end_at)->format('d/m/Y') }}</p>
                    @if($lebdCircular->position)<p><i class="far fa-file-alt"></i> {{ $lebdCircular->position }}</p>@endif
                </a>
                @empty
                    <div class="lebd-home-empty">এই মুহূর্তে কোনো চলমান বিজ্ঞপ্তি নেই।</div>
                @endforelse
            </div>
            <button class="job-slider-arrow job-next" type="button" onclick="jobSlide(1)" aria-label="পরের বিজ্ঞপ্তি"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="job-slider-dots" id="jobSliderDots"></div>
    </div>
</section>



{{-- =========================================================
     CSS
========================================================= --}}

<style>

/* =========================================================
   MAIN WRAPPER
========================================================= */

.job-notice-wrapper{

    width:100%;

    max-width:1700px;

    margin:30px auto;

    padding:0 24px;

    box-sizing:border-box;

    font-family:
        "Noto Sans Bengali",
        "Noto Sans",
        Arial,
        sans-serif;

}


/* =========================================================
   MAIN NOTICE AREA
========================================================= */

.job-notice-main{

    position:relative;

    padding:20px 28px 30px;

    border-radius:25px;

    background:
        linear-gradient(
            135deg,
            #fff9df 0%,
            #fffdf2 45%,
            #f8fcff 100%
        );

    border:1px solid #e8edf5;

    box-shadow:
        0 10px 35px rgba(23,75,130,.08);

    overflow:hidden;

}


/* =========================================================
   DECORATIONS
========================================================= */

.job-dots{

    position:absolute;

    top:25px;
    left:30px;

    display:grid;

    grid-template-columns:
        repeat(4,8px);

    gap:9px;

    opacity:.5;

}

.job-dots span{

    width:8px;
    height:8px;

    border-radius:50%;

    background:#f6c72e;

}


.job-megaphone{

    position:absolute;

    right:35px;
    top:25px;

    font-size:100px;

    color:#f4ca45;

    opacity:.10;

    transform:rotate(-10deg);

}


/* =========================================================
   TITLE
========================================================= */

.job-notice-title{

    display:flex;

    align-items:center;

    justify-content:center;

    gap:25px;

    position:relative;

    z-index:2;

    margin-bottom:22px;

}


.title-line{

    width:90px;

    height:4px;

    border-radius:10px;

    background:#1888e5;

}


.right-line{

    background:#f4c21e;

}


.job-title-pill{

    min-width:560px;

    height:110px;

    padding:0 50px;

    border-radius:60px;

    background:
        linear-gradient(
            135deg,
            #168de8,
            #1767cf
        );

    color:#fff;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:22px;

    box-shadow:
        0 12px 25px rgba(13,93,188,.20);

}


.job-title-pill i{

    font-size:48px;

}


.job-title-pill span{

    font-size:46px;

    font-weight:800;

}


/* =========================================================
   TOP LINE
========================================================= */

.job-notice-top{

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:20px;

    margin:10px 8px 20px;

}


.job-notice-top h2{

    margin:0;

    color:#112f68;

    font-size:30px;

    font-weight:700;

}


.all-job-button{

    flex-shrink:0;

    padding:16px 30px;

    border-radius:35px;

    background:#e8f3ff;

    color:#0765d1;

    text-decoration:none !important;

    font-size:21px;

    font-weight:700;

    display:flex;

    align-items:center;

    gap:12px;

    transition:.25s;

}


.all-job-button:hover{

    background:#d7eaff;

    transform:translateY(-2px);

}


/* =========================================================
   FEATURED CARD
========================================================= */

.featured-job-card{

    position:relative;

    z-index:2;

    min-height:200px;

    margin:18px 0;

    padding:22px 25px;

    display:flex;

    align-items:center;

    gap:25px;

    background:
        linear-gradient(
            100deg,
            #ffffff,
            #f7fbff
        );

    border:2px solid #dceaf9;

    border-radius:20px;

    box-shadow:
        0 6px 18px rgba(33,93,150,.07);

    box-sizing:border-box;

}


.job-logo-box{

    width:210px;

    height:150px;

    flex-shrink:0;

    border-radius:15px;

    background:#edf6ff;

    display:flex;

    align-items:center;

    justify-content:center;

}


.job-logo-placeholder{

    width:115px;
    height:115px;

    border-radius:50%;

    display:flex;

    align-items:center;
    justify-content:center;

    font-size:60px;

    color:#1689d9;

    background:#fff;

    box-shadow:
        0 4px 15px rgba(0,0,0,.08);

}


.water-logo{

    color:#087bb8;

}


.job-divider{

    width:2px;

    height:130px;

    background:#c9def3;

    flex-shrink:0;

}


.job-information{

    flex:1;

    min-width:0;

}


.job-information h3{

    margin:0 0 18px;

    color:#102e68;

    font-size:34px;

    font-weight:800;

}


.job-meta{

    display:flex;

    align-items:center;

    flex-wrap:wrap;

    gap:28px;

}


.job-meta span{

    color:#17315d;

    font-size:19px;

    font-weight:600;

}


.job-meta i{

    color:#0b5fc9;

    margin-right:7px;

}


.job-information p{

    margin:14px 0 0;

    color:#77849a;

    font-size:20px;

}


.job-feature-image{

    width:280px;

    height:150px;

    flex-shrink:0;

    border-radius:14px;

    overflow:hidden;

}


.job-image-placeholder{

    width:100%;
    height:100%;

    display:flex;

    align-items:center;
    justify-content:center;

    font-size:65px;

    color:#8797aa;

    background:
        linear-gradient(
            135deg,
            #dcecff,
            #f2f6fa
        );

}


.water-image{

    color:#1685c4;

    background:
        linear-gradient(
            135deg,
            #d9f1ff,
            #b8dcf0
        );

}


.job-details-button{

    min-width:235px;

    padding:19px 25px;

    border-radius:40px;

    background:
        linear-gradient(
            135deg,
            #087be4,
            #0d66cc
        );

    color:#fff !important;

    text-decoration:none !important;

    font-size:23px;

    font-weight:800;

    text-align:center;

    box-shadow:
        0 7px 15px rgba(0,102,210,.18);

    transition:.25s;

}


.job-details-button:hover{

    transform:translateY(-3px);

    box-shadow:
        0 12px 20px rgba(0,102,210,.25);

}


.job-details-button span{

    margin-left:8px;

}


.green-job-button{

    background:
        linear-gradient(
            135deg,
            #16b765,
            #0ca65a
        );

}


/* =========================================================
   INFO BAR
========================================================= */

.job-info-bar{

    min-height:105px;

    margin-top:28px;

    padding:15px 28px;

    border-radius:20px;

    background:
        linear-gradient(
            90deg,
            #dceeff,
            #eaf5ff
        );

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:30px;

}


.job-info-left,
.job-info-right{

    display:flex;

    align-items:center;

    gap:18px;

}


.job-light-icon{

    width:58px;
    height:58px;

    border-radius:50%;

    background:#ffd20a;

    display:flex;

    align-items:center;
    justify-content:center;

    font-size:28px;

    color:#fff;

}


.job-info-bar p{

    margin:0;

    color:#163668;

    font-size:23px;

    font-weight:600;

}


.job-info-bar strong{

    color:#096ad4;

    font-size:27px;

}


.job-info-right i{

    color:#086fd7;

    font-size:35px;

}


/* =========================================================
   SLIDER
========================================================= */

.job-slider-section{

    position:relative;

    padding:10px 0 5px;

}


.job-slider-title{

    display:flex;

    align-items:center;

    gap:20px;

    margin:12px 10px 20px;

}


.job-slider-title > div{

    height:3px;

    flex:1;

    background:#7db8f0;

}


.job-slider-title h2{

    margin:0;

    white-space:nowrap;

    color:#102f6a;

    font-size:30px;

    font-weight:800;

}


.job-slider-title h2 span{

    display:inline-block;

    width:12px;
    height:12px;

    margin:0 12px;

    border-radius:50%;

    background:#278ce9;

}


/* =========================================================
   SLIDER CONTAINER
========================================================= */

.job-slider-container{

    position:relative;

    display:flex;

    align-items:center;

}


.job-slider-track{

    width:100%;

    display:flex;

    gap:16px;

    overflow:hidden;

    scroll-behavior:smooth;

    padding:4px 25px 18px;

    box-sizing:border-box;

}


.job-slider-card{

    flex:0 0 calc((100% - 64px) / 5);

    min-height:215px;

    padding:22px 16px;

    border-radius:17px;

    text-align:center;

    text-decoration:none !important;

    border:1px solid rgba(0,0,0,.05);

    box-shadow:
        0 5px 15px rgba(0,0,0,.07);

    transition:.25s;

    box-sizing:border-box;

}


.job-slider-card:hover{

    transform:translateY(-6px);

    box-shadow:
        0 12px 24px rgba(0,0,0,.12);

}


.slider-logo{

    width:75px;
    height:75px;

    margin:0 auto 12px;

    border-radius:50%;

    display:flex;

    align-items:center;
    justify-content:center;

    background:#fff;

    font-size:37px;

}


.job-slider-card h3{

    margin:0 0 14px;

    color:#102e68;

    font-size:19px;

    font-weight:800;

}


.job-slider-card p{

    margin:7px 0;

    color:#19366b;

    font-size:15px;

    font-weight:600;

}


.job-slider-card p i{

    margin-right:6px;

    color:#075fc6;

}


/* CARD BACKGROUNDS */

.slider-pink{
    background:#fff0f4;
}

.slider-blue{
    background:#e9f5ff;
}

.slider-green{
    background:#edfaed;
}

.slider-orange{
    background:#fff3e5;
}

.slider-purple{
    background:#f5edff;
}

.slider-yellow{
    background:#fffbe8;
}


/* =========================================================
   ARROWS
========================================================= */

.job-slider-arrow{

    position:absolute;

    z-index:10;

    width:65px;
    height:65px;

    border:0;

    border-radius:50%;

    background:#fff;

    color:#0769d4;

    font-size:28px;

    box-shadow:
        0 5px 18px rgba(0,0,0,.12);

    cursor:pointer;

    display:flex;

    align-items:center;
    justify-content:center;

}


.job-prev{

    left:-5px;

}


.job-next{

    right:-5px;

}


.job-slider-arrow:hover{

    background:#f2f8ff;

    transform:scale(1.05);

}


/* =========================================================
   DOTS
========================================================= */

.job-slider-dots{

    display:flex;

    justify-content:center;

    align-items:center;

    gap:12px;

    margin-top:4px;

}


.job-slider-dots span{

    width:12px;
    height:12px;

    border-radius:50%;

    background:#ccd4de;

    transition:.25s;

}


.job-slider-dots span.active{

    width:22px;

    border-radius:10px;

    background:#0879e5;

}


/* =========================================================
   TABLET
========================================================= */

@media(max-width:1100px){

    .job-title-pill{

        min-width:440px;

        height:90px;

    }


    .job-title-pill span{

        font-size:36px;

    }


    .job-notice-top h2{

        font-size:23px;

    }


    .featured-job-card{

        flex-wrap:wrap;

    }


    .job-information{

        min-width:50%;

    }


    .job-feature-image{

        margin-left:235px;

    }


    .job-details-button{

        position:absolute;

        right:20px;

        bottom:25px;

    }


    .job-slider-card{

        flex:0 0 calc((100% - 32px) / 3);

    }

}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:768px){

    .job-notice-wrapper{

        padding:0 8px;

        margin:18px auto;

    }


    .job-notice-main{

        padding:12px 10px 18px;

        border-radius:17px;

    }


    .job-dots,
    .job-megaphone{

        display:none;

    }


    .job-notice-title{

        gap:8px;

        margin-bottom:14px;

    }


    .title-line{

        width:25px;

        height:2px;

    }


    .job-title-pill{

        min-width:0;

        width:75%;

        height:55px;

        padding:0 12px;

        border-radius:30px;

        gap:7px;

    }


    .job-title-pill i{

        font-size:20px;

    }


    .job-title-pill span{

        font-size:22px;

    }


    .job-notice-top{

        margin:8px 3px 12px;

    }


    .job-notice-top h2{

        font-size:14px;

        line-height:1.5;

    }


    .all-job-button{

        padding:8px 10px;

        font-size:11px;

        gap:5px;

    }


    .featured-job-card{

        min-height:auto;

        padding:10px;

        gap:10px;

        border-radius:12px;

        display:grid;

        grid-template-columns:65px 1fr;

        align-items:center;

    }


    .job-logo-box{

        width:65px;

        height:65px;

        grid-row:span 2;

        border-radius:10px;

    }


    .job-logo-placeholder{

        width:48px;
        height:48px;

        font-size:24px;

    }


    .job-divider{

        display:none;

    }


    .job-information{

        min-width:0;

    }


    .job-information h3{

        font-size:15px;

        margin:0 0 5px;

        line-height:1.3;

    }


    .job-meta{

        gap:4px 8px;

    }


    .job-meta span{

        font-size:9px;

    }


    .job-meta i{

        margin-right:2px;

    }


    .job-information p{

        font-size:9px;

        margin:5px 0 0;

    }


    .job-feature-image{

        display:none;

    }


    .job-details-button{

        position:static;

        min-width:0;

        padding:8px 12px;

        font-size:11px;

        border-radius:20px;

        justify-self:end;

        width:max-content;

    }


    .job-info-bar{

        min-height:55px;

        padding:8px 10px;

        border-radius:12px;

        gap:8px;

    }


    .job-info-left,
    .job-info-right{

        gap:7px;

    }


    .job-light-icon{

        width:32px;
        height:32px;

        font-size:16px;

        flex-shrink:0;

    }


    .job-info-bar p{

        font-size:10px;

    }


    .job-info-bar strong{

        font-size:12px;

    }


    .job-info-right i{

        font-size:18px;

    }


    /* SLIDER */

    .job-slider-title{

        gap:7px;

        margin:10px 3px 12px;

    }


    .job-slider-title h2{

        font-size:17px;

    }


    .job-slider-title h2 span{

        width:7px;
        height:7px;

        margin:0 5px;

    }


    .job-slider-track{

        gap:8px;

        padding:3px 20px 12px;

    }


    .job-slider-card{

        flex:0 0 calc((100% - 8px) / 2);

        min-height:145px;

        padding:12px 7px;

        border-radius:11px;

    }


    .slider-logo{

        width:45px;
        height:45px;

        margin-bottom:7px;

        font-size:22px;

    }


    .job-slider-card h3{

        font-size:12px;

        margin-bottom:7px;

        line-height:1.3;

    }


    .job-slider-card p{

        font-size:9px;

        margin:4px 0;

    }


    .job-slider-arrow{

        width:38px;
        height:38px;

        font-size:16px;

    }


    .job-prev{

        left:-2px;

    }


    .job-next{

        right:-2px;

    }


    .job-slider-dots{

        gap:7px;

    }


    .job-slider-dots span{

        width:8px;
        height:8px;

    }


    .job-slider-dots span.active{

        width:15px;

    }

}


/* =========================================================
   VERY SMALL MOBILE
========================================================= */

@media(max-width:380px){

    .job-title-pill span{

        font-size:19px;

    }


    .job-notice-top h2{

        font-size:12px;

    }


    .all-job-button{

        font-size:9px;

        padding:7px 8px;

    }


    .job-slider-card{

        min-height:135px;

    }

}

</style>



{{-- =========================================================
     JAVASCRIPT — SLIDER
========================================================= --}}

<script>

(function(){

    const track = document.getElementById('jobSliderTrack');
    const dotsWrap = document.getElementById('jobSliderDots');
    const slideCards = Array.from(track ? track.querySelectorAll('.job-slider-card') : []);
    if (dotsWrap) {
        dotsWrap.innerHTML = '';
        slideCards.forEach((card, index) => {
            const dot = document.createElement('span');
            if (index === 0) dot.classList.add('active');
            dotsWrap.appendChild(dot);
        });
    }
    const dots = document.querySelectorAll('#jobSliderDots span');

    if(!track){
        return;
    }
    if (slideCards.length <= 1) {
        document.querySelectorAll('.job-slider-arrow').forEach(button => { button.hidden = true; });
    }


    window.jobSlide = function(direction){

        const card = track.querySelector('.job-slider-card');

        if(!card){
            return;
        }

        const gap = 16;

        const scrollAmount =
            card.offsetWidth + gap;

        track.scrollBy({

            left: direction * scrollAmount,

            behavior: 'smooth'

        });


        updateJobDots();

    };


    function updateJobDots(){

        const maxScroll =
            track.scrollWidth - track.clientWidth;

        if(maxScroll <= 0){
            return;
        }


        const percentage =
            track.scrollLeft / maxScroll;


        const index =
            Math.round(
                percentage * (dots.length - 1)
            );


        dots.forEach(function(dot, i){

            dot.classList.toggle(
                'active',
                i === index
            );

        });

    }


    track.addEventListener(
        'scroll',
        updateJobDots
    );


})();

</script>

{{-- Our Success: admin-managed modular section. --}}
@include('homepage.partials.success.section')

{{-- =========================
    সফল শিক্ষার্থীদের পরামর্শ
========================= --}}

<section class="success-students-section">

    <div class="success-container">

        {{-- Section Header --}}
        <div class="success-heading">

            <div class="heading-cap">
                <i class="fas fa-graduation-cap"></i>
            </div>

            <h2>
                সফল শিক্ষার্থীদের <span>পরামর্শ</span>
            </h2>

            <p>
                আপনার সাফল্য আমাদের গর্ব — সফলদের অভিজ্ঞতা থেকে শিখুন
            </p>

        </div>


        {{-- Students Cards --}}
        <div class="success-grid">


            {{-- Student 01 --}}
            <div class="success-card">

                <div class="student-top">

                    <div class="student-image">
                        <img src="/img/student-1.jpg"
                             alt="নিউটন মজুমদার">
                    </div>

                    <div class="student-info">

                        <h3>নিউটন মজুমদার</h3>

                        <p>
                            <i class="fas fa-user"></i>
                            BB ID: 547 446
                        </p>

                        <p>
                            <i class="fas fa-graduation-cap"></i>
                            গোপালগঞ্জ বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয়
                        </p>

                    </div>

                </div>


                <div class="student-content">

                    <div class="student-position">

                        <div class="position-icon">
                            <i class="fas fa-building"></i>
                        </div>

                        <div>
                            <h4>
                                প্রভাষক, সমাজবিজ্ঞান বিভাগ
                            </h4>

                            <p>
                                গাজীপুর চাঁদ উদ্যান মহিলা কলেজ, সাভার
                            </p>
                        </div>

                    </div>


                    <div class="advice-title">
                        <span></span>
                        <strong>পরামর্শ</strong>
                        <span></span>
                    </div>


                    <p class="advice-text">
                        সঠিক গাইডলাইন থেকে শুরু করে ধৈর্য ধরে পড়াশোনা করতে হবে।
                        কারণ NT​RCA-এর পরীক্ষার প্রস্তুতি বিষয়ভিত্তিক ব্যাপক পড়াশোনা
                        থাকে। প্রতিটি পরীক্ষার প্রস্তুতি সঠিকভাবে নিতে হবে।
                    </p>

                </div>

            </div>



            {{-- Student 02 --}}
            <div class="success-card">

                <div class="student-top">

                    <div class="student-image">
                        <img src="/img/student-2.jpg"
                             alt="সঞ্জিত মন্ডল">
                    </div>

                    <div class="student-info">

                        <h3>সঞ্জিত মন্ডল</h3>

                        <p>
                            <i class="fas fa-user"></i>
                            BB ID: 443 856
                        </p>

                        <p>
                            <i class="fas fa-graduation-cap"></i>
                            জাতীয় বিশ্ববিদ্যালয়
                        </p>

                    </div>

                </div>


                <div class="student-content">

                    <div class="student-position">

                        <div class="position-icon blue">
                            <i class="fas fa-train"></i>
                        </div>

                        <div>

                            <h4>
                                টি.সি. (গ্রেড-২)
                            </h4>

                            <p>
                                বাংলাদেশ রেলওয়ে
                            </p>

                        </div>

                    </div>


                    <div class="advice-title">
                        <span></span>
                        <strong>পরামর্শ</strong>
                        <span></span>
                    </div>


                    <p class="advice-text">
                        বিগত বছরের প্রশ্ন সমাধান, বেসিক মজবুত করা, নিয়মিত
                        কারেন্ট অ্যাফেয়ার্স পড়া, নিয়মিত মডেল টেস্ট দেওয়া এবং
                        নিয়োগ সংক্রান্ত আপডেট অনুসরণ করা — এই পাঁচ অভ্যাসই
                        চাকরি পরীক্ষায় সফলতার মূল চাবিকাঠি।
                    </p>

                </div>

            </div>



            {{-- Student 03 --}}
            <div class="success-card">

                <div class="student-top">

                    <div class="student-image">
                        <img src="/img/student-3.jpg"
                             alt="আসিফ আহমেদ">
                    </div>

                    <div class="student-info">

                        <h3>আসিফ আহমেদ</h3>

                        <p>
                            <i class="fas fa-user"></i>
                            BB ID: 200 533
                        </p>

                        <p>
                            <i class="fas fa-graduation-cap"></i>
                            ঢাকা বিশ্ববিদ্যালয়
                        </p>

                    </div>

                </div>


                <div class="student-content">

                    <div class="student-position">

                        <div class="position-icon green">
                            <i class="fas fa-book-open"></i>
                        </div>

                        <div>

                            <h4>
                                প্রাইমারি প্রধান শিক্ষক
                                ৪০তম (বিসিএস)
                            </h4>

                            <p>
                                প্রাথমিক ও গণশিক্ষা মন্ত্রণালয়
                            </p>

                        </div>

                    </div>


                    <div class="advice-title">
                        <span></span>
                        <strong>পরামর্শ</strong>
                        <span></span>
                    </div>


                    <p class="advice-text">
                        আমি কখনো হাল ছাড়িনি। যত কঠিনই লাগুক চেষ্টা চালিয়ে
                        গেছি। প্রতিদিন নিজেকে একটু করে উন্নত করেছি। নিয়মিত
                        ক্লাস, নোট ও টেস্ট করেছি। এতে প্রস্তুতি সম্পূর্ণ হয়েছে
                        এবং সফলতা এসেছে।
                    </p>

                </div>

            </div>

        </div>


        {{-- More Button --}}
        <div class="success-more">

            <a href="#">
                আরও দেখুন
                <i class="fas fa-arrow-right"></i>
            </a>

        </div>

    </div>

</section>


{{-- =========================
        SUCCESS CSS
========================= --}}

<style>

.success-students-section {
    width: 100%;
    padding: 70px 20px;
    background:
        radial-gradient(circle at 10% 15%, rgba(255, 215, 220, .45), transparent 25%),
        radial-gradient(circle at 90% 20%, rgba(255, 225, 230, .45), transparent 25%),
        linear-gradient(180deg, #ffffff 0%, #fffafa 100%);
    overflow: hidden;
}

.success-container {
    max-width: 1450px;
    margin: auto;
}


/* =========================
   Heading
========================= */

.success-heading {
    text-align: center;
    margin-bottom: 45px;
}

.heading-cap {
    width: 55px;
    height: 30px;
    margin: 0 auto 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #e21d2f;
    font-size: 25px;
}

.success-heading h2 {
    margin: 0;
    font-size: 46px;
    font-weight: 800;
    color: #10284d;
}

.success-heading h2 span {
    color: #e21d2f;
}

.success-heading p {
    margin-top: 10px;
    font-size: 20px;
    color: #52627a;
}


/* =========================
   Cards Grid
========================= */

.success-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}


/* =========================
   Card
========================= */

.success-card {
    background: #ffffff;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid #eadede;
    box-shadow: 0 8px 25px rgba(0,0,0,.10);
    transition: all .3s ease;
}

.success-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 15px 35px rgba(0,0,0,.15);
}


/* =========================
   Student Top
========================= */

.student-top {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 18px;
    background: linear-gradient(
        135deg,
        #f5dddd,
        #f0d4d4
    );
}

.student-image {
    width: 95px;
    height: 95px;
    flex: 0 0 95px;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    border: 3px solid #ffffff;
    box-shadow: 0 3px 10px rgba(0,0,0,.15);
}

.student-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.student-info {
    min-width: 0;
}

.student-info h3 {
    margin: 0 0 8px;
    font-size: 22px;
    font-weight: 800;
    color: #17243b;
}

.student-info p {
    margin: 4px 0;
    font-size: 14px;
    line-height: 1.4;
    color: #34425a;
}

.student-info i {
    width: 18px;
    margin-right: 5px;
    color: #d7192c;
}


/* =========================
   Card Content
========================= */

.student-content {
    padding: 22px 18px 25px;
}

.student-position {
    display: flex;
    align-items: center;
    gap: 15px;
    min-height: 80px;
}

.position-icon {
    width: 58px;
    height: 58px;
    flex: 0 0 58px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff1cf;
    color: #df202f;
    font-size: 25px;
}

.position-icon.blue {
    background: #e4efff;
    color: #1675e8;
}

.position-icon.green {
    background: #e1f6e8;
    color: #0a9b54;
}

.student-position h4 {
    margin: 0;
    font-size: 20px;
    line-height: 1.35;
    font-weight: 800;
    color: #182b4f;
}

.student-position p {
    margin: 4px 0 0;
    font-size: 14px;
    color: #68758a;
}


/* =========================
   Advice Title
========================= */

.advice-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 20px 0 14px;
}

.advice-title span {
    height: 1px;
    flex: 1;
    background: #cfcfcf;
}

.advice-title strong {
    color: #1c2637;
    font-size: 19px;
    white-space: nowrap;
}


/* =========================
   Advice Text
========================= */

.advice-text {
    margin: 0;
    font-size: 15px;
    line-height: 1.75;
    text-align: justify;
    color: #333d4d;
}


/* =========================
   More Button
========================= */

.success-more {
    text-align: center;
    margin-top: 38px;
}

.success-more a {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 13px 32px;
    border-radius: 50px;
    background: linear-gradient(
        135deg,
        #ef313d,
        #d91c2b
    );
    color: #ffffff;
    font-size: 17px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 7px 18px rgba(220, 30, 50, .25);
    transition: all .3s ease;
}

.success-more a:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(220, 30, 50, .35);
}


/* =========================
   Tablet
========================= */

@media (max-width: 991px) {

    .success-heading h2 {
        font-size: 36px;
    }

    .success-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}


/* =========================
   Mobile
========================= */

@media (max-width: 767px) {

    .success-students-section {
        padding: 45px 12px;
    }

    .success-heading {
        margin-bottom: 28px;
    }

    .success-heading h2 {
        font-size: 28px;
    }

    .success-heading p {
        font-size: 15px;
    }

    .success-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .student-top {
        padding: 14px;
        gap: 14px;
    }

    .student-image {
        width: 75px;
        height: 75px;
        flex-basis: 75px;
    }

    .student-info h3 {
        font-size: 18px;
    }

    .student-info p {
        font-size: 12px;
    }

    .student-content {
        padding: 18px 14px 20px;
    }

    .student-position h4 {
        font-size: 17px;
    }

    .student-position p {
        font-size: 12px;
    }

    .position-icon {
        width: 48px;
        height: 48px;
        flex-basis: 48px;
        font-size: 20px;
    }

    .advice-text {
        font-size: 14px;
        line-height: 1.65;
    }

    .success-more a {
        font-size: 15px;
        padding: 11px 25px;
    }

}

</style>

{{-- Scoped responsive refinements: no changes to site's master header/footer. --}}
<style>
/* FIX: Never use zoom: .75 + width:133%, which pushed the page to the right.
   Full-bleed within any centered parent, with a centered max-width for each section.
   Chrome zoom stays under the visitor's own control. */
.lebd-homepage-canvas {
  position:relative;
  width:100vw;
  max-width:100vw;
  margin-left:calc(50% - 50vw);
  margin-right:0;
  padding:0;
  min-width:0;
  zoom:1;
  overflow-x:clip;
}
.lebd-homepage-canvas, .lebd-homepage-canvas * { box-sizing:border-box; }
/* Match the visual width of every homepage section, including the formerly full-width ebooks. */
.lebd-homepage-canvas .lebd-home-shell,
.lebd-homepage-canvas .lebd-program-wrap,
.lebd-homepage-canvas .daily-feature-section,
.lebd-homepage-canvas .new-ebook-section,
.lebd-homepage-canvas .mcq-main-wrapper,
.lebd-homepage-canvas .job-notice-wrapper,
.lebd-homepage-canvas .success-container { width:min(94%,1420px); max-width:1420px; margin-left:auto; margin-right:auto; }
.lebd-homepage-canvas .success-students-section > .success-container {width:min(94%,1420px);}
.lebd-homepage-canvas .daily-feature-section {padding:0;}
.lebd-homepage-canvas .mcq-main-wrapper,
.lebd-homepage-canvas .job-notice-wrapper {padding-left:0; padding-right:0;}
.lebd-homepage-canvas .lebd-home-showcase {padding:18px 0 24px;}
/* Refined course cards: poster at top, equal-height body and fixed action below. */
.lebd-homepage-canvas .lebd-home-course-grid {grid-template-columns:repeat(3,minmax(0,1fr));gap:21px;}
.lebd-homepage-canvas .lebd-home-course-card {display:flex;flex-direction:column;min-height:330px;border-radius:20px;border:1px solid #e0e9f6;box-shadow:0 9px 27px rgba(8,48,100,.10);background:#fff;overflow:hidden;}
.lebd-homepage-canvas .lebd-home-course-card:hover {transform:translateY(-5px);box-shadow:0 18px 33px rgba(10,53,100,.16);}
.lebd-homepage-canvas .lebd-home-course-image {width:100%;height:190px;flex:0 0 190px;background:#e8f4ff;}
.lebd-homepage-canvas .lebd-home-course-image img {height:100%;max-height:none;width:100%;object-fit:cover;object-position:center;}
.lebd-homepage-canvas .lebd-home-course-badge {font-size:12px;padding:6px 11px;top:13px;left:13px;border-radius:9px;box-shadow:0 4px 12px rgba(0,0,0,.12)}
.lebd-homepage-canvas .lebd-home-course-body {padding:18px 20px 19px;min-height:137px;}
.lebd-homepage-canvas .lebd-home-course-body h3 {font-size:22px;line-height:1.45;margin:6px 0 16px;}
.lebd-homepage-canvas .lebd-home-course-button {padding:11px 15px;font-size:15px;border-radius:11px;}
/* Classier, compact batch cards; all original batch links remain untouched. */
.lebd-homepage-canvas .lebd-home-batch-slider {gap:18px;padding:5px 4px 18px;}
.lebd-homepage-canvas .lebd-home-batch-card {flex:0 0 calc((100% - 36px)/3);border-radius:18px;box-shadow:0 8px 24px rgba(13,54,110,.10);}
.lebd-homepage-canvas .lebd-home-batch-slider.lebd-home-few-batches .lebd-home-batch-card {flex-basis:calc((100% - 18px)/2);}
.lebd-homepage-canvas .lebd-home-batch-cover {height:170px;}
.lebd-homepage-canvas .lebd-home-batch-cover img {width:100%;height:100%;object-fit:cover;}
.lebd-homepage-canvas .lebd-home-batch-body {min-height:152px;padding:15px 16px 17px;}
.lebd-homepage-canvas .lebd-home-batch-body h3 {font-size:19px;min-height:40px;}
.lebd-homepage-canvas .lebd-home-batch-button {font-size:14px;padding:12px;border-radius:10px;}
/* Screenshot-inspired Live/Recorded 2-column panels (intentionally not linked yet). */
.lebd-homepage-canvas .lebd-program-section {width:100%;padding:32px 0 38px;background:linear-gradient(180deg,#f8faff 0%,#edf4fc 100%);}
.lebd-homepage-canvas .lebd-program-wrap {display:grid;grid-template-columns:1fr 1fr;align-items:stretch;gap:24px;}
.lebd-homepage-canvas .lebd-program-panel {position:relative;isolation:isolate;overflow:hidden;min-height:480px;border:1px solid rgba(255,255,255,.35);border-radius:24px;padding:30px 32px 22px;color:#fff;box-shadow:0 16px 34px rgba(20,32,70,.17);display:flex;flex-direction:column;}
.lebd-homepage-canvas .lebd-program-panel::before {content:"";position:absolute;z-index:-1;width:340px;height:340px;border-radius:50%;top:-180px;right:-130px;background:rgba(255,255,255,.055);}
.lebd-homepage-canvas .lebd-program-panel::after {content:"";position:absolute;z-index:-1;width:280px;height:280px;border-radius:50%;bottom:-190px;left:-120px;border:1px solid rgba(255,255,255,.09);}
.lebd-homepage-canvas .lebd-program-panel-live {background:linear-gradient(128deg,#0b1142,#191162 60%,#241a7c);}
.lebd-homepage-canvas .lebd-program-panel-recorded {background:linear-gradient(122deg,#091d46,#0b4266 60%,#08758c);}
.lebd-homepage-canvas .lebd-program-heading {text-align:center;margin-bottom:20px;}
.lebd-homepage-canvas .lebd-program-pill {display:inline-flex;gap:9px;align-items:center;border-radius:999px;padding:9px 19px;color:#123b76;background:#fff;font-size:14px;font-weight:850;box-shadow:0 5px 16px rgba(0,0,0,.12);}
.lebd-homepage-canvas .lebd-program-pill i {color:#05a46f;}
.lebd-homepage-canvas .lebd-program-heading h2 {color:#fff;margin:19px auto 7px;font-weight:850;font-size:23px;line-height:1.5;max-width:650px;}
.lebd-homepage-canvas .lebd-program-heading p {color:#c8e7fd;margin:0;font-size:14px;line-height:1.5;}
.lebd-homepage-canvas .lebd-program-options {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
.lebd-homepage-canvas .lebd-program-option {min-height:48px;padding:12px 14px;border-radius:12px;display:flex;align-items:center;justify-content:center;gap:9px;text-align:center;color:#fff;border:1px solid rgba(255,255,255,.37);background:rgba(255,255,255,.085);font-size:14px;font-weight:750;line-height:1.4;}
.lebd-homepage-canvas .lebd-program-option i {color:#93e4ff;font-size:13px;}
.lebd-homepage-canvas .lebd-program-panel-live .lebd-program-option {background:rgba(121,108,255,.13);}
.lebd-homepage-canvas .lebd-program-panel-recorded .lebd-program-option {background:rgba(93,210,243,.12);}
.lebd-homepage-canvas .lebd-program-group {border:1px solid rgba(255,255,255,.3);border-radius:16px;padding:18px 18px 20px;margin-bottom:14px;}
.lebd-homepage-canvas .lebd-program-group h3 {display:flex;gap:7px;align-items:center;justify-content:center;max-width:max-content;margin:0 auto 15px;padding:8px 18px;border-radius:99px;background:white;color:#133764;font-size:15px;font-weight:850;}
.lebd-homepage-canvas .lebd-program-footnote {margin:auto 0 0;padding-top:17px;opacity:.75;font-size:12px;text-align:center;}
/* Keep E-Book within the same centered content width; the header and cards scale together. */
.lebd-homepage-canvas .new-ebook-section {overflow:hidden;border:1px solid #dbe7fa;border-radius:22px;margin-top:24px;margin-bottom:24px;box-shadow:0 10px 27px rgba(10,52,105,.10);}
.lebd-homepage-canvas .new-ebook-header {padding:20px 29px;min-height:98px;}
.lebd-homepage-canvas .new-ebook-icon {width:56px;height:56px;}
.lebd-homepage-canvas .new-ebook-icon i {font-size:28px;}
.lebd-homepage-canvas .new-ebook-header h2 {font-size:29px;}
.lebd-homepage-canvas .new-ebook-header p {font-size:14px;}
.lebd-homepage-canvas .new-ebook-body {padding:22px;background:#f4f8ff;}
.lebd-homepage-canvas .new-ebook-body.general-body {background:#f8f5ff;}
.lebd-homepage-canvas .new-ebook-grid {display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:18px;}
.lebd-homepage-canvas .new-ebook-card {min-width:0;min-height:170px;border-radius:16px;border:1px solid #e5ecfa;box-shadow:0 6px 15px rgba(22,65,113,.07);}
.lebd-homepage-canvas .new-ebook-card-icon {width:72px;height:72px;margin-bottom:11px;}
.lebd-homepage-canvas .new-ebook-card span {font-size:19px;}
.lebd-homepage-canvas .new-ebook-card:hover {transform:translateY(-4px);box-shadow:0 12px 23px rgba(18,67,117,.15);}
.lebd-homepage-canvas .lebd-home-batch-footer {
  margin:12px auto 6px; display:flex; align-items:center; justify-content:center;
  gap:18px; min-height:45px; position:relative;
}
.lebd-homepage-canvas .lebd-home-batch-footer .lebd-home-slider-dots {
  margin:0; flex:1; justify-content:center; padding-left:140px;
}
.lebd-homepage-canvas .lebd-home-batches-more {
  display:inline-flex; align-items:center; justify-content:center; gap:9px;
  flex-shrink:0; border-radius:999px; background:#e4f0ff; color:#0865c8 !important;
  padding:11px 19px; font-size:14px; font-weight:800; text-decoration:none !important;
  border:1px solid #cddfff; transition:background-color .2s ease;
}
.lebd-homepage-canvas .lebd-home-batches-more:hover {background:#cfe5ff;}
@media (max-width:680px) {
  .lebd-homepage-canvas .lebd-home-batch-slider.lebd-home-few-batches .lebd-home-batch-card {flex-basis:85%;}
  .lebd-homepage-canvas .lebd-home-batch-footer {flex-wrap:wrap;gap:6px;}
  .lebd-homepage-canvas .lebd-home-batch-footer .lebd-home-slider-dots {padding-left:0;flex-basis:100%;}
  .lebd-homepage-canvas .lebd-home-batches-more {font-size:12px;padding:9px 14px;}
}

/* Other existing sections keep their markup, footer remains owned by homepage layout. */
@media (max-width:1199.98px) {
 .lebd-homepage-canvas .lebd-home-batch-card {flex-basis:calc((100% - 36px)/3);}
 .lebd-homepage-canvas .new-ebook-grid {grid-template-columns:repeat(3,minmax(0,1fr));}
}
@media (max-width:850px) {
 .lebd-homepage-canvas .lebd-home-course-grid {grid-template-columns:repeat(2,minmax(0,1fr));}
 .lebd-homepage-canvas .lebd-program-wrap {grid-template-columns:1fr;}
 .lebd-homepage-canvas .lebd-home-batch-card {flex-basis:calc((100% - 18px)/2);}
 .lebd-homepage-canvas .new-ebook-grid {grid-template-columns:repeat(3,minmax(0,1fr));}
}
@media (max-width:680px) {
 .lebd-homepage-canvas .lebd-home-course-grid {grid-template-columns:1fr;gap:12px;}
 .lebd-homepage-canvas .lebd-home-course-card {flex-direction:row;min-height:138px;}
 .lebd-homepage-canvas .lebd-home-course-image {width:38%;height:auto;flex:0 0 38%;min-height:140px;}
 .lebd-homepage-canvas .lebd-home-course-body {min-height:0;padding:12px;}
 .lebd-homepage-canvas .lebd-home-course-body h3 {font-size:16px;margin:5px 0 9px;}
 .lebd-homepage-canvas .lebd-home-course-button {font-size:12px;padding:9px;}
 .lebd-homepage-canvas .lebd-home-batch-card {flex:0 0 85%;}
 .lebd-homepage-canvas .lebd-program-section {padding:18px 0 25px;}
 .lebd-homepage-canvas .lebd-program-wrap {width:calc(100% - 20px);gap:16px;}
 .lebd-homepage-canvas .lebd-program-panel {padding:22px 15px;min-height:auto;border-radius:17px;}
 .lebd-homepage-canvas .lebd-program-heading h2 {font-size:18px;}
 .lebd-homepage-canvas .lebd-program-heading p {font-size:12px;}
 .lebd-homepage-canvas .lebd-program-options {gap:9px;}
 .lebd-homepage-canvas .lebd-program-option {font-size:12px;padding:10px 7px;min-height:49px;}
 .lebd-homepage-canvas .lebd-program-option i {display:none;}
 .lebd-homepage-canvas .lebd-program-group {padding:13px 11px;}
 .lebd-homepage-canvas .new-ebook-section {border-radius:15px;}
 .lebd-homepage-canvas .new-ebook-header {min-height:76px;padding:12px;}
 .lebd-homepage-canvas .new-ebook-header h2 {font-size:18px;}
 .lebd-homepage-canvas .new-ebook-header p {font-size:10px;}
 .lebd-homepage-canvas .new-ebook-icon {width:43px;height:43px;}
 .lebd-homepage-canvas .new-ebook-icon i {font-size:23px;}
 .lebd-homepage-canvas .new-ebook-all {padding:6px 9px;font-size:10px;}
 .lebd-homepage-canvas .new-ebook-body {padding:10px;}
 .lebd-homepage-canvas .new-ebook-grid {grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;}
 .lebd-homepage-canvas .new-ebook-card {min-height:124px;border-radius:12px;}
 .lebd-homepage-canvas .new-ebook-card-icon {width:48px;height:48px;margin-bottom:7px;}
 .lebd-homepage-canvas .new-ebook-card span {font-size:13px;}
}
@media (prefers-reduced-motion:reduce) {
 .lebd-homepage-canvas .new-ebook-card, .lebd-homepage-canvas .lebd-home-course-card {transition:none;}
}
</style>

</div> {{-- /.lebd-homepage-canvas ; master-layout footer stays untouched --}}

@endsection
