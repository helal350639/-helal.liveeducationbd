@extends('homepage')
@section('title', 'চলমান সকল ব্যাচ — Live Education BD')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-running-batches-fix132.css') }}?v=132">
<section class="lebd-rb132">
  <div class="lebd-rb132-wrap">
    <a class="lebd-rb132-back" href="{{ route('homepage') }}#lebd-running-batches">← হোমপেজে ফিরে যান</a>
    <div class="lebd-rb132-hero"><h1>🎓 চলমান সকল ব্যাচ</h1><p>হোমপেজে চলমান হিসেবে প্রদর্শিত ব্যাচগুলো এখানে দেখুন</p></div>
    @if($batches->count())
      <div class="lebd-rb132-grid">
        @foreach($batches as $batch)
          <article class="lebd-rb132-card">
            <div class="lebd-rb132-cover"><img src="{{ $batch->banner ? asset($batch->banner) : asset('/img/course.png') }}" alt="{{ $batch->name }}" loading="lazy"><span>● চলমান ব্যাচ</span></div>
            <div class="lebd-rb132-body">
              <h2>{{ $batch->name }}</h2>
              @if($batch->course)<p>📚 কোর্স: {{ $batch->course->name }}</p>@endif
              <a href="{{ route('home.course.show', $batch->id) }}">বিস্তারিত দেখুন →</a>
            </div>
          </article>
        @endforeach
      </div>
      @if(method_exists($batches, 'links'))<div class="lebd-rb132-pages">{{ $batches->links() }}</div>@endif
    @else
      <div class="lebd-rb132-empty">বর্তমানে কোনো চলমান ব্যাচ পাওয়া যায়নি।</div>
    @endif
  </div>
</section>
@endsection
