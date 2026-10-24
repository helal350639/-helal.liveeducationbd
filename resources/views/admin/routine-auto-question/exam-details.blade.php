@extends('dashboard')
@section('title', 'Exam Details')
@section('content')
<section class="content-header"><h1>Exam Details</h1></section>
<section class="content"><div class="box box-primary"><div class="box-header with-border">
<h3 class="box-title" style="color:#800">{{ $exam->exam_name }} — {{ $batch->name }}</h3>
<a class="btn btn-default btn-sm pull-right" href="{{ route('admin.routine-auto-question.batch',['courseId'=>$course->id,'batchId'=>$batch->id]) }}"><i class="fa fa-arrow-left"></i> Exam Folders</a>
</div><div class="box-body"><p><b>Course:</b> {{ $course->name }}</p>
<p><b>Routine:</b> {{ $exam->routine_name }}</p>
<div class="alert alert-info">Details View content will be configured after you provide the requirements.</div>
</div></div></section>
@endsection
