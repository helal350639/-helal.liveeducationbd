@extends('homepage')
@section('title','Make a Quiz')
@section('content')
<link rel="stylesheet" href="{{asset('web/css/job-preparation-student.css')}}">
<link rel="stylesheet" href="{{asset('web/css/lebd-quota-notice.css')}}">
<main class="jp-st">@include('homepage.job-preparation.partials.buy-package-button')<div class="jp-st-hero"><h1>Create Your MCQ Quiz</h1><p>{{$cat->name}} @if($sub) → {{$sub->name}} @endif @if($ch) → {{$ch->name}} @endif</p><a class="jp-st-back" href="{{url()->previous()}}">← ফিরে যান</a></div>
<div class="jp-st-panel"><h2>Make a Quiz — Filter ও Time Setup</h2>
@if($errors->any())<div class="jp-quiz-error">{{$errors->first()}}</div>@endif
@if(isset($remainingQuizzes) && $remainingQuizzes !== null && (int)$remainingQuizzes <= 0)
@include('homepage.job-preparation.partials.access-expired-notice', ['noticeType'=>'quiz'])
@elseif(isset($remainingQuizzes) && $remainingQuizzes !== null)
<p class="jp-access-notice">এই মাসে অবশিষ্ট Quiz: <strong>{{$remainingQuizzes}}</strong></p>
@endif
@if(!isset($remainingQuizzes) || $remainingQuizzes === null || (int)$remainingQuizzes > 0)
<form method="POST" action="{{route('student.jobprep.quiz.start',['kind'=>$kind,'level'=>$level,'entity'=>$entity])}}" class="jp-quiz-form">@csrf
<div class="jp-full"><strong>Group:</strong> {{$kind==='department'?'Department':'Non-Department'}} &nbsp; | &nbsp; <strong>Category:</strong> {{$cat->name}}</div>
<label>Subject<select name="subject_id" id="jp-subject" {{$sub?'disabled':''}}><option value="">All Subjects / Category + All</option>@foreach($subjects as $subject)<option value="{{$subject->id}}" {{($sub?->id)===$subject->id?'selected':''}}>{{$subject->name}}</option>@endforeach</select></label>
@if($sub)<input type="hidden" name="subject_id" value="{{$sub->id}}">@endif
<label>Chapter<select name="chapter_id" id="jp-chapter" {{$ch?'disabled':''}}><option value="">All Chapters / Direct + All</option>@foreach($chapters as $chapter)<option value="{{$chapter->id}}" data-subject="{{$chapter->subject_id}}" {{($ch?->id)===$chapter->id?'selected':''}}>{{$chapter->name}}</option>@endforeach</select></label>
@if($ch)<input type="hidden" name="chapter_id" value="{{$ch->id}}">@endif
<label>Number of Questions<input type="number" name="count" min="1" max="100" value="{{old('count',min(25,max(1,$available)))}}" required></label>
<label>Exam Duration (Minutes)<input type="number" name="minutes" min="1" max="180" value="{{old('minutes',20)}}" required></label>
<label>Selection<select name="order"><option value="random">Random Questions</option><option value="latest">Latest Questions</option></select></label>
<div class="jp-full">Available in current selection: <strong>{{$available}}</strong> (Count is checked again on Start)</div>
<button type="submit" class="jp-quiz-btn jp-full" @if(isset($remainingQuizzes) && $remainingQuizzes===0) disabled @endif><i class="fa fa-play"></i> Start Quiz</button></form>
@endif
</div></main>
<script>document.addEventListener('DOMContentLoaded',function(){const s=document.getElementById('jp-subject'),c=document.getElementById('jp-chapter');if(!s||!c)return;function update(){const v=s.value;for(const o of c.options){if(!o.value)continue;o.hidden=!v||o.dataset.subject!==v;}if(c.selectedOptions[0]?.hidden)c.value='';}s.addEventListener('change',update);update();});</script>
@endsection
