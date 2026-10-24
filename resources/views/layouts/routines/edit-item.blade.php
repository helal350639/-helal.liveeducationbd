@extends('dashboard')
@section('title', 'Edit Routine Item')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
<style>.text-right{text-align:right}.routine-mcq-selector select{width:100%}.routine-mcq-selector .mcq-selected{max-height:180px;overflow-y:auto}</style>
@endsection
@section('content')
@include('layouts.routines.partials.all-pages-navigation')

<section class="content-header"><h1>Edit a Routine Item</h1><ol class="breadcrumb"><li><a href="#"><i class="fa fa-dashboard"></i> Routines</a></li><li class="active">Edit a Routine Item</li></ol></section>
<section class="content"><div class="row"><div class="col-md-12"><div class="box box-primary">
<div class="box-header with-border"><h3 style="color:#800" class="box-title">{{ $routine->name }}</h3>
<div class="toolbar-icon pull-right text-right"><a href="{{ route('routine.index') }}" title="View" class="label label-success"><i class="fa fa-list"></i></a></div></div>
<form action="{{ route('routine.item-update', $routine_row) }}" method="POST">@csrf @method('PUT')
<input type="hidden" name="routine_id" value="{{ $routine->id }}">
<div class="box-body">
@php $activeRoutineMode = 'exam'; @endphp
@include('layouts.routines.partials.routine-type-nav')
<div class="row"><div class="col-md-6">
<div class="form-group row"><label class="col-md-4 text-right" for="paper_name">Exam No</label><div class="col-md-8"><select name="paper_name" id="paper_name" class="form-control" required>
<option value="">Select Exam</option>
@forelse($exams as $exam)<option value="{{ $exam->name }}" {{ old('paper_name', $routine_row->paper_name) == $exam->name ? 'selected' : '' }}>{{ $exam->name }}</option>
@empty<option value="" disabled>No Exam Name Added</option>@endforelse
</select></div></div>
@foreach($subjects as $subject)
<div class="form-group row"><label class="col-md-4 text-right">{{ $subject->name }}</label><div class="col-md-8">
<input type="hidden" name="subject_id[]" value="{{ $subject->id }}">
@php $columnKind='subject'; $columnId=$subject->id; $existingRow = $subject_rows->where('subject_id', $subject->id)->first(); $existingText = $existingRow ? $existingRow->chapter : '';  @endphp
@include('layouts.routines.partials.mcq-column')
</div></div>
@endforeach
</div><div class="col-md-6">
@foreach($departments as $department)
<div class="form-group row"><label class="col-md-4 text-right">{{ $department->name }}</label><div class="col-md-8">
<input type="hidden" name="department_id[]" value="{{ $department->id }}">
@php $columnKind='department'; $columnId=$department->id; $existingRow = $department_rows->where('department_id', $department->id)->first(); $existingText = $existingRow ? $existingRow->details : '';  @endphp
@include('layouts.routines.partials.mcq-column')
</div></div>
@endforeach
<div class="form-group row"><label class="col-md-4 text-right" for="exam_at">Date &amp; Time</label><div class="col-md-8">
<input type="datetime-local" class="form-control" name="exam_at" id="exam_at" value="{{ old('exam_at', $routine_row->exam_at ? \Carbon\Carbon::parse($routine_row->exam_at)->format('Y-m-d\TH:i') : '') }}" required></div></div>
<div class="form-group row"><label class="col-md-4 text-right" for="exam_end_at">Exam End Date &amp; Time</label><div class="col-md-8">
<input type="datetime-local" class="form-control" name="exam_end_at" id="exam_end_at" value="{{ old('exam_end_at', $routine_row->exam_end_at ? \Carbon\Carbon::parse($routine_row->exam_end_at)->format('Y-m-d\TH:i') : '') }}"></div></div>
<div class="form-group row"><label class="col-md-4 text-right" for="create_pdf_at">Create PDF Date & Time</label><div class="col-md-8">
<input type="datetime-local" class="form-control" id="create_pdf_at" readonly title="Calculated automatically from routine and previous exam end" value="{{ old('create_pdf_at', $routine_row->create_pdf_at ? \Carbon\Carbon::parse($routine_row->create_pdf_at)->format('Y-m-d\TH:i') : '') }}"></div></div>
<div class="form-group row"><label class="col-md-4 text-right" for="question_create_at">Question Create Date & Time</label><div class="col-md-8">
<input type="datetime-local" class="form-control" id="question_create_at" readonly title="Calculated automatically from routine and previous exam end" value="{{ old('question_create_at', $routine_row->question_create_at ? \Carbon\Carbon::parse($routine_row->question_create_at)->format('Y-m-d\TH:i') : '') }}"></div></div>
</div></div></div>
<div class="box-footer">
    <div class="pull-right" style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;">
        <button type="submit" class="btn btn-default" name="routine_action" value="make_question"><i class="fa fa-list-alt"></i> Make Question</button>
        <button type="submit" class="btn btn-info" name="routine_action" value="create_pdf"><i class="fa fa-file-pdf-o"></i> Create PDF</button>
        <button type="submit" class="btn btn-primary" name="routine_action" value="save"><i class="fa fa-save"></i> Update</button>
    </div><div class="clearfix"></div>
</div>
</form></div></div></div></section>
@endsection
@section('scripts')
@include('layouts.routines.partials.mcq-selector-script')
@endsection
