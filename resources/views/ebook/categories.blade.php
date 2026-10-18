@extends('homepage')
@section('title', ($kind === 'department' ? 'Department' : 'Non-Department').' E-Books')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-student-v22.css') }}">
<main class="leb-v23-library">
  <nav class="leb-v23-breadcrumb"><a href="{{ url('/') }}">হোম</a><span>›</span><span>E-Book / PDF</span><span>›</span><b>{{ $kind === 'department' ? 'Department' : 'General' }}</b></nav>
  <section class="leb-v23-heading"><div class="leb-v23-heading-icon"><i class="fa-solid fa-book-open"></i></div><div><small>LIVE EDUCATION BD • DIGITAL LIBRARY</small><h1>{{ $kind === 'department' ? 'E-Book / PDF (Diploma)' : 'E-Book / PDF (General)' }}</h1><p>পছন্দের বই বেছে নিন, সূচিপত্র দেখুন এবং ফ্রি অধ্যায় পড়ে দেখুন।</p></div></section>
  <div class="leb-v23-toolbar"><h2>সকল {{ $kind === 'department' ? 'ডিপার্টমেন্ট' : 'জেনারেল' }} বই <span>({{ $categories->count() }})</span></h2><div class="leb-v23-switch"><a @class(['active'=>$kind==='department']) href="{{ route('student.ebook.categories',['kind'=>'department']) }}">Department</a><a @class(['active'=>$kind==='non-department']) href="{{ route('student.ebook.categories',['kind'=>'non-department']) }}">General</a></div></div>
  <div class="leb-v23-grid">
    @forelse($categories as $cat)
    <a class="leb-v23-card" href="{{ route('student.ebook.chapters',['kind'=>$kind,'category'=>$cat->id]) }}">
      <div class="leb-v23-card-art" style="--ebook-card-bg:{{ preg_match('/^#[0-9a-fA-F]{3,8}$/',(string)($cat->card_color ?? '')) ? $cat->card_color : '#ebf4ff' }}">
        @if(!empty($cat->front_cover))<img src="{{ asset($cat->front_cover) }}" alt="{{ $cat->name }} book cover" loading="lazy">
        @else <span class="leb-v23-icon"><i class="fa {{ $cat->icon ?: 'fa-book' }}" style="color:{{ preg_match('/^#[0-9a-fA-F]{3,8}$/',(string)($cat->icon_color ?? '')) ? $cat->icon_color : '#1674eb' }}"></i></span>@endif
        <span class="leb-v23-preview-label">E-BOOK</span>
      </div>
      <div class="leb-v23-card-copy"><strong>{{ $cat->name }}</strong><small>ফ্রি প্রিভিউ • সূচিপত্র • সম্পূর্ণ বই</small><span class="leb-v23-card-action">বিস্তারিত ও সূচিপত্র <i class="fa-solid fa-arrow-right"></i></span></div>
    </a>
    @empty <div class="leb-v23-empty">এই বিভাগে এখনো কোনো E-Book প্রকাশ করা হয়নি।</div>
    @endforelse
  </div>
</main>
@endsection
