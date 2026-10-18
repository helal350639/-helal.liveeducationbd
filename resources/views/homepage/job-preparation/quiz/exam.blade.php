@extends('homepage')
@section('title','MCQ Quiz Exam')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/job-preparation-student.css') }}">
<main class="jp-st jp-exam-page">
  <div class="jp-st-hero jp-exam-hero"><div><span class="jp-exam-eyebrow"><i class="fa fa-graduation-cap"></i> LIVE EDUCATION BD · PRACTICE EXAM</span><h1>MCQ Quiz Exam</h1><p>{{ $quiz['title'] }} · {{ count($quiz['ids']) }} Questions · {{ $quiz['minutes'] }} Minutes</p></div><div class="jp-exam-pill">{{ count($quiz['ids']) }} <small>MCQs</small></div></div>
  <div class="jp-exam-toolbar"><div class="jp-timer-label"><i class="fa fa-clock"></i><span>সময় বাকি</span><strong id="jp-timer" data-seconds="{{ $remaining }}">--:--</strong></div><div class="jp-exam-progress"><span><b id="jp-answered">0</b> / {{ count($quiz['ids']) }} উত্তর দেওয়া হয়েছে</span><div class="jp-progress-track"><span id="jp-progress-fill"></span></div></div></div>
  <form id="jp-exam-form" action="{{ route('student.jobprep.quiz.submit',['token'=>$token]) }}" method="POST">@csrf
   <div class="jp-exam-grid">
   @foreach($quiz['ids'] as $i=>$id)
    @php $question=$questions->get($id); $choices=$items->get($id,collect())->values(); @endphp
    @if($question)
    <section class="jp-exam-card" id="quiz-question-{{ $i+1 }}">
      <div class="jp-exam-qhead"><span class="jp-exam-qnumber">প্রশ্ন {{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</span><span class="jp-exam-qbadge">MCQ</span></div>
      <div class="jp-exam-qtitle">{!! $question->title !!}</div>
      @if(!empty($question->image))<div class="jp-exam-image"><img loading="lazy" src="{{ asset(ltrim($question->image,'/')) }}" alt="Question image"></div>@endif
      <div class="jp-exam-options">
      @foreach($choices as $ci=>$choice)
       <label class="jp-exam-choice"><input type="radio" name="answer[{{ $id }}]" value="{{ $choice->id }}"><span class="jp-exam-choice-letter">{{ ['A','B','C','D','E'][$ci]??($ci+1) }}</span><span class="jp-exam-choice-text">{!! $choice->item !!} @if(!empty($choice->image))<img loading="lazy" src="{{ asset(ltrim($choice->image,'/')) }}" alt="Option image">@endif</span><i class="fa fa-check-circle jp-exam-tick"></i></label>
      @endforeach
      </div>
    </section>
    @endif
   @endforeach
   </div>
   <div class="jp-exam-submit"><span><i class="fa fa-circle-info"></i> সময় শেষ হলে পরীক্ষা স্বয়ংক্রিয়ভাবে জমা হবে।</span><button type="submit" class="jp-exam-submit-button"><i class="fa fa-paper-plane"></i> Submit Exam</button></div>
  </form>
</main>
<script src="{{ asset('web/js/job-preparation-quiz.js') }}"></script>
@endsection
