@extends('dashboard')
@section('title','Exam Question Details')
@section('content')
<style>.ex182{padding:19px;background:#f2f6fc;color:#183657;min-height:75vh}.ex182 *{box-sizing:border-box}.ex182 h2{font-size:24px;margin:0 0 12px}.ex182 .panel{background:#fff;border:1px solid #d7e3f1;border-radius:8px;padding:18px;margin:12px 0}.ex182 .q{border:1px solid #dce6f0;border-radius:7px;margin:14px 0;padding:15px}.ex182 .opts{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:12px}.ex182 .opt{border:1px solid #cfdeee;border-radius:6px;padding:10px}.ex182 .right{background:#e7f9ef;border-color:#91d9b0}.ex182 .btn{display:inline-block;padding:9px 12px;background:#0a85bd;color:white;border-radius:5px;text-decoration:none}.ex182 .meta{font-size:12px;color:#647d94}.ex182 .correct{margin:10px 0;color:#078044;font-weight:700}@media(max-width:700px){.ex182 .opts{grid-template-columns:1fr}}
</style>
<div class="ex182"><h2>Full Question Paper View — {{ $exam }}</h2>
<a class="btn" href="{{ route('admin.exam-section.auto-question.exams',['category'=>$cat->id]+($sub?['subcategory_id'=>$sub->id]:[])) }}">← Exam List</a>
<div class="panel"><b>Category:</b> {{ $cat->name }} @if($sub) / {{ $sub->name }} @endif &nbsp; <b>Routine:</b> {{ $r->name }} &nbsp; <b>Exam:</b> {{ $exam }} &nbsp; <b>Total:</b> {{ $links->count() }}<p class="meta">Read-only. Opening this page does not regenerate MCQs. Each original Question ID appears once in this Exam.</p></div>
<div class="panel">
@forelse($links as $link)
<div class="q"><b>{{ $loop->iteration }}. </b> {!! $link->title !!}
@if($link->image)<p><img style="max-width:100%;max-height:240px" src="{{ asset(ltrim($link->image,'/')) }}" alt="Question image"></p>@endif
@php $choices=$options->get($link->id,collect()); @endphp
@if($choices->count())<div class="opts">@foreach($choices as $op)<div class="opt {{ (string)$op->correct_answer==='1'?'right':'' }}"><b>{{ ['A','B','C','D'][$loop->index]??($loop->index+1) }})</b> {!! $op->item !!} @if((string)$op->correct_answer==='1') ✓ @endif @if($op->image)<img style="max-width:90%" src="{{ asset(ltrim($op->image,'/')) }}" alt="Option image">@endif</div>@endforeach</div>
<div class="correct">Correct Answer: @foreach($choices as $op)@if((string)$op->correct_answer==='1') {{ ['A','B','C','D'][$loop->index]??($loop->index+1) }} @endif @endforeach</div>
@else<p class="meta">No MCQ options found.</p>@endif
<div class="meta">Original Question ID: {{ $link->id }} · Topic: {{ $link->topic_key }}</div></div>
@empty<p>No questions saved for this Exam. Use Create Question first.</p>@endforelse
</div></div>
@endsection
