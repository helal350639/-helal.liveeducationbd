@extends('homepage')
@section('title','MCQ Reading')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/job-preparation-student.css') }}">
<link rel="stylesheet" href="{{ asset('web/css/lebd-quota-notice.css') }}">
<main class="jp-st">
@include('homepage.job-preparation.partials.buy-package-button')
@if(isset($remainingReading) && $remainingReading !== null && (int)$remainingReading <= 0)
@include('homepage.job-preparation.partials.access-expired-notice', ['noticeType'=>'reading'])
@elseif(isset($remainingReading) && $remainingReading !== null)<div class="jp-access-notice">আজ আরও {{$remainingReading}}টি নতুন প্রশ্ন পড়তে পারবেন।</div>@endif
 <div class="jp-st-hero {{ $kind==='non-department'?'purple':'' }}">
  <h1>{{ $title }}</h1><p>MCQ Reading · {{ $rows->total() }} Questions</p>
  <a class="jp-st-back" href="{{ $back }}">← ফিরে যান</a>
 </div>
 @if(session('success'))<div class="jp-alert">{{session('success')}}</div>@endif
 @if($errors->any())<div class="jp-quiz-error">{{ $errors->first() }}</div>@endif
 <section class="jp-toolbar">
  <form method="GET" class="jp-qfilter" action="{{route('student.jobprep.questions',['kind'=>$kind,'level'=>$level,'entity'=>$entity])}}">
   <label>আমার Reading Filter
    <select name="difficulty"><option value="">সব প্রশ্ন</option>
     @foreach(['easy'=>'Easy','medium'=>'Medium','difficult'=>'Difficult'] as $val=>$label)
      <option value="{{$val}}" {{($difficulty??'')===$val?'selected':''}}>{{$label}} (আমার Mark)</option>
     @endforeach
    </select>
   </label>
   <button class="jp-filter-btn" type="submit">Filter</button>
   <a class="jp-reset" href="{{route('student.jobprep.questions',['kind'=>$kind,'level'=>$level,'entity'=>$entity])}}">Reset</a>
  </form>
  <a class="jp-quiz-btn" href="{{route('student.jobprep.quiz.create',['kind'=>$kind,'level'=>$level,'entity'=>$entity])}}"><i class="fa fa-plus-circle"></i> Make a Quiz</a>
 </section>
 <div class="jp-question-grid">
 @forelse($rows as $question)
  @include('homepage.job-preparation.partials.question-card',['question'=>$question,'choices'=>$items->get($question->id,collect()),'idx'=>($rows->firstItem()??1)+$loop->index])
 @empty
  <div class="jp-st-panel jp-st-empty">এই Filter-এ কোনো প্রশ্ন পাওয়া যায়নি।</div>
 @endforelse
 </div>
 <div class="jp-st-pages">{{ $rows->links() }}</div>
</main>
<script src="{{asset('web/js/job-preparation-student.js')}}" defer></script>
@endsection
