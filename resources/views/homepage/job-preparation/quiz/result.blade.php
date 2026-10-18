@extends('homepage')
@section('title','Quiz Result')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/job-preparation-student.css') }}">
@php $result=$quiz['result']; $percent=$result['total']?round($result['correct']/$result['total']*100,1):0; @endphp
<main class="jp-st jp-exam-page">
 <div class="jp-st-hero jp-exam-hero"><div><span class="jp-exam-eyebrow"><i class="fa fa-chart-bar"></i> YOUR PERFORMANCE</span><h1>Quiz Result</h1><p>{{ $quiz['title'] }} · {{ $result['total'] }} Questions</p></div><div class="jp-score-circle">{{ $percent }}<small>%</small></div></div>
 <div class="jp-results-summary"><div class="jp-result-metric"><span>Total Questions</span><strong>{{ $result['total'] }}</strong></div><div class="jp-result-metric jp-metric-green"><span><i class="fa fa-check-circle"></i> Correct</span><strong>{{ $result['correct'] }}</strong></div><div class="jp-result-metric jp-metric-red"><span><i class="fa fa-times-circle"></i> Wrong</span><strong>{{ $result['wrong'] }}</strong></div><div class="jp-result-metric jp-metric-amber"><span><i class="fa fa-minus-circle"></i> Unanswered</span><strong>{{ $result['unanswered'] }}</strong></div></div>
 <div class="jp-result-section-head"><h2>Answer Review</h2><p>সঠিক উত্তর, আপনার উত্তর এবং ব্যাখ্যা একসঙ্গে দেখুন</p></div>
 <div class="jp-exam-grid jp-review-grid">
 @foreach($quiz['ids'] as $i=>$id)
 @php $question=$questions->get($id); $selected=$result['review'][$id]??[]; $chosen=$selected['chosen']??null; $right=$selected['correct']??null; $choices=$items->get($id,collect())->values(); @endphp
 @if($question)
 <section class="jp-exam-card jp-review-card"><div class="jp-exam-qhead"><span class="jp-exam-qnumber">প্রশ্ন {{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</span><span class="jp-review-status {{ !$chosen?'jp-unanswered':($chosen==$right?'jp-correct':'jp-wrong') }}">{{ !$chosen?'Unanswered':($chosen==$right?'Correct':'Wrong') }}</span></div>
 <div class="jp-exam-qtitle">{!! $question->title !!}</div>
 <div class="jp-exam-options">
 @foreach($choices as $ci=>$choice)
 <div class="jp-exam-choice jp-review-choice {{ $choice->id==$right?'is-correct':'' }} {{ $choice->id==$chosen?'is-selected':'' }}"><span class="jp-exam-choice-letter">{{ ['A','B','C','D','E'][$ci]??($ci+1) }}</span><span class="jp-exam-choice-text">{!! $choice->item !!}</span><span class="jp-review-tags">@if($choice->id==$right)<b>✓ Correct</b>@endif @if($choice->id==$chosen)<b>Your answer</b>@endif</span></div>
 @endforeach
 </div>
 <details class="jp-review-explanation"><summary><i class="fa fa-book-open"></i> সঠিক উত্তর ও ব্যাখ্যা দেখুন</summary><div>{!! $question->explanation?:($question->solution?:'এই প্রশ্নের ব্যাখ্যা এখনো যুক্ত করা হয়নি।') !!}</div></details>
 </section>
 @endif
 @endforeach
 </div>
 <div class="jp-exam-submit"><span>আরও অনুশীলন করে প্রস্তুতি বাড়াও</span><a class="jp-exam-submit-button" href="{{ route('student.jobprep.quiz.create',['kind'=>$quiz['kind'],'level'=>$quiz['level']??'category','entity'=>$quiz['entity']??$quiz['category_id']]) }}"><i class="fa fa-rotate-right"></i> Make Another Quiz</a></div>
</main>
@endsection
