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
  <div class="success-grid">
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
  @if(\App\Models\StudentAdvice\StudentAdvice::where('status','Published')->count()>3)
    <div class="success-more"><a href="{{ url('/student-advice') }}">আরও দেখুন <i class="fas fa-arrow-right"></i></a></div>
  @endif
 </div>
</section>
@endif

<script src="{{ asset('web/js/lebd-student-advice-fix125.js') }}?v=125" defer></script>
