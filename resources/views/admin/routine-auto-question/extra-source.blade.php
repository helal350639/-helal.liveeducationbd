@extends('dashboard')
@section('title','Add Question Source')
@section('content')
<section class="content-header"><h1>{{ $source==='syllabus' ? 'Add Syllabus Questions' : 'Add Database Questions' }}</h1></section>
<section class="content"><div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">{{ $exam->exam_name }} — {{ $batch->name }}</h3>
<a class="btn btn-default btn-sm pull-right" href="{{ route('admin.routine-auto-question.create-question',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id]) }}">Back to Auto Add Questions</a></div>
<div class="box-body"><p><strong>Course:</strong> {{ $course->name }} &nbsp; <strong>Routine:</strong> {{ $exam->routine_name }}</p>
<div class="alert alert-info">This separate {{ $source==='syllabus'?'Routine syllabus/PDF':'main question database' }} source page is ready. Selection and saving will be connected after you provide the rules. No source questions have been imported on this page.</div>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Category</th><th>Subject</th><th>Chapter</th><th>Available</th><th>Total Questions</th></tr></thead><tbody><tr><td colspan="5" class="text-center">Waiting for connection details</td></tr></tbody></table></div>
</div></div></section>
@endsection
