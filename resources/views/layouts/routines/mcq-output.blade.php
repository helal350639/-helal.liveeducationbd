@extends('dashboard')
@section('title', $mode === 'pdf' ? 'Create PDF' : 'Make Question')
@section('content')
@include('layouts.routines.partials.all-pages-navigation')

<style>
@media print { .content-header,.output-toolbar,.main-header,.main-sidebar,.control-sidebar,.main-footer { display:none !important; } .content-wrapper {margin:0!important} .box {border:0!important;box-shadow:none!important;} .question-card {break-inside:avoid;} }
.question-card{padding:12px 0;border-bottom:1px solid #ddd}.question-options{list-style-type:upper-alpha;padding-left:26px}.question-options li{margin:6px 0}
</style>
<section class="content-header"><h1>{{ $mode === 'pdf' ? 'Create PDF' : 'Make Question' }}</h1></section>
<section class="content"><div class="box box-primary"><div class="box-header with-border output-toolbar">
<a href="{{ route('routine.item.edit', $item->id) }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to Edit</a>
<button type="button" class="btn btn-info pull-right" onclick="window.print()"><i class="fa fa-print"></i> Print / Save as PDF</button>
</div><div class="box-body"><h3 style="text-align:center">{{ $item->paper_name }}</h3>
<p style="text-align:center">{{ $questions->count() }} Questions</p>
@if($questions->isEmpty())<div class="alert alert-warning">No linked questions were found in the saved Subject/Chapter ranges.</div>@endif
@foreach($questions as $question)
<div class="question-card"><strong>Question {{ $loop->iteration }}.</strong> {!! $question->title !!}
<ol class="question-options">@foreach($question->options as $option)<li>{!! $option->item !!}</li>@endforeach</ol>
</div>
@endforeach
</div></div></section>
@endsection
