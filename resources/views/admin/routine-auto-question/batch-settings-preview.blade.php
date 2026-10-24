@extends('dashboard')
@section('title', 'Batch Paper Settings Preview')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
<style>
.lebd-batch-preview label{font-size:12px}.lebd-batch-preview .form-group{margin-bottom:16px}
.lebd-batch-preview .form-control{border-radius:4px}.lebd-batch-preview .box-title{color:#800}
.lebd-batch-preview .preview-note{margin:0 0 18px}
</style>
@endsection
@section('content')
<section class="content-header">
<h1>Batch Question Paper Settings</h1>
<ol class="breadcrumb"><li><a href="{{ route('admin.routine-auto-question.index') }}">Courses</a></li>
<li><a href="{{ route('admin.routine-auto-question.course',['courseId'=>$course->id]) }}">Active Batches</a></li>
<li class="active">Paper Settings Preview</li></ol></section>
<section class="content lebd-batch-preview"><div class="box box-primary">
<div class="box-header with-border">
<h3 class="box-title">{{ $course->name }} / {{ $batch->name }}</h3>
<a href="{{ route('admin.routine-auto-question.batch',['courseId'=>$course->id,'batchId'=>$batch->id]) }}" class="btn btn-default btn-sm pull-right"><i class="fa fa-arrow-left"></i> Exam Folders</a>
</div><div class="box-body">
<div class="alert alert-info preview-note"><b>Design Preview Only.</b> These fields are not saved or applied yet. You will decide how each setting works in the next step.</div>
<div class="row">
<div class="col-md-4 form-group"><label>Course</label><input class="form-control" value="{{ $course->name }}" readonly></div>
<div class="col-md-4 form-group"><label>Batch</label><input class="form-control" value="{{ $batch->name }}" readonly></div>
<div class="col-md-4 form-group"><label>Batch Schedule</label><input class="form-control" value="{{ $batch->start_at }} — {{ $batch->end_at }}" readonly></div>
<div class="col-md-12 form-group"><label>Header</label><textarea class="form-control" id="batch-preview-header" rows="5"></textarea></div>
<div class="col-md-12 form-group"><label>Details</label><textarea class="form-control" rows="4" placeholder="Details"></textarea></div>
<div class="col-md-12 form-group"><label>Banner</label><input class="form-control" type="file" accept="image/*"><p class="text-danger" style="margin-top:8px">Image size: 8:1 / 800px X 100px</p></div>
</div><hr>
<div class="row">
<div class="col-md-6"><div class="form-group"><label>Questions Limit</label><input class="form-control" type="number" value="100"></div></div>
<div class="col-md-6"><div class="form-group"><label>MCQ List Format</label><select class="form-control"><option selected>(i), (ii), (iii), (iv), (v)</option><option>(A), (B), (C), (D)</option><option>(১), (২), (৩), (৪)</option><option>(ক), (খ), (গ), (ঘ)</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Status</label><select class="form-control"><option selected>Published</option><option>Unpublished</option><option>Scheduled</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Exam Running Time (in minute)</label><input class="form-control" type="number" value="40"></div></div>
<div class="col-md-6"><div class="form-group"><label>Exam Start Time</label><input class="form-control" type="datetime-local"></div></div>
<div class="col-md-6"><div class="form-group"><label>Exam Finished Time</label><input class="form-control" type="datetime-local"></div></div>
<div class="col-md-6"><div class="form-group"><label>Mark (For Correct Answer)</label><input class="form-control" type="number" value="1.00"></div></div>
<div class="col-md-6"><div class="form-group"><label>Mark (Negative for wrong Answer)</label><input class="form-control" type="number" value="0.50"></div></div>
<div class="col-md-6"><div class="form-group"><label>How many times can a student take the exam?</label><select class="form-control"><option selected>Select One</option><option>1</option><option>2</option><option>3</option><option>Unlimited</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Show Random Questions?</label><select class="form-control"><option selected>Yes</option><option>No</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Display Question?</label><select class="form-control"><option selected>All in One Paper</option><option>One by One</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Exam Permission For Candidates?</label><select class="form-control"><option selected>Department</option><option>All</option><option>Batch</option><option>Course</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Paid exam?</label><select class="form-control"><option selected>Yes</option><option>No</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Send Result to the Email?</label><select class="form-control"><option selected>No</option><option>Yes</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Student Can View Result After Exam?</label><select class="form-control"><option selected>No</option><option>Yes</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Max Candidates</label><input class="form-control" type="number"></div></div>
<div class="col-md-6"><div class="form-group"><label>Result Publish Date & Time</label><input class="form-control" type="datetime-local"></div></div>
<div class="col-md-6"><div class="form-group"><label>Message After Exam</label><textarea class="form-control" rows="3"></textarea></div></div>
</div></div>
<div class="box-footer"><a class="btn btn-default" href="{{ route('admin.routine-auto-question.course',['courseId'=>$course->id]) }}">Back to Batches</a>
<button type="button" class="btn btn-primary pull-right" disabled title="Save will be enabled after settings logic is agreed"><i class="fa fa-save"></i> Save (Not Active)</button></div>
</div></section>
@endsection
@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
<script>$(function(){if($.fn.summernote){$('#batch-preview-header').summernote({height:150});}});</script>
@endsection
