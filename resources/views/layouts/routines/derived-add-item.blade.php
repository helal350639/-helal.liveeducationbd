@extends('dashboard')
@section('title', ucfirst($type).' Routine Item')
@section('stylesheets')
<style>.derived-column select{margin-bottom:6px}.derived-help{font-size:11px;color:#777;margin-top:5px}.derived-preview{border:1px solid #ddd;background:#fafafa;padding:8px;min-height:48px;margin-top:7px}.text-right{text-align:right}</style>
@endsection
@section('content')
@include('layouts.routines.partials.all-pages-navigation')

<section class="content-header"><h1>Add a {{ ucfirst($type) }} Routine Item</h1><ol class="breadcrumb"><li><a href="{{ route('routine.index') }}">Routines</a></li><li class="active">{{ ucfirst($type) }}</li></ol></section>
<section class="content"><div class="row"><div class="col-md-12"><div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title" style="color:#800">{{ $routine->name }} — {{ ucfirst($type) }}</h3><div class="pull-right"><a class="label label-info" href="{{ route('routine.add-item',['id'=>$routine->id]) }}">Exam Types</a> <a class="label label-success" href="{{ route('routine.derived.list',['id'=>$routine->id]) }}"><i class="fa fa-list"></i></a></div></div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('routine.derived.store',['id'=>$routine->id,'type'=>$type]) }}">@csrf
<div class="box-body">
@php $activeRoutineMode = $type; @endphp
@include('layouts.routines.partials.routine-type-nav')
<div class="row">
<div class="col-md-6">
<div class="form-group row"><label class="col-md-4 text-right">Exam No</label><div class="col-md-8"><select class="form-control" name="exam_name" required><option value="">Select {{ ucfirst($type) }} Exam</option>@foreach($typeNames as $exam)<option value="{{ $exam->name }}" {{ old('exam_name')===$exam->name?'selected':'' }}>{{ $exam->name }}</option>@endforeach</select>@if($typeNames->isEmpty())<span class="text-warning">Add {{ ucfirst($type) }} names in Edit Routine first.</span>@endif</div></div>
@foreach($subjects as $subject)
<div class="form-group row derived-column"><label class="col-md-4 text-right">{{ $subject->name }}</label><div class="col-md-8">
<select name="source[subject_{{ $subject->id }}][]" multiple class="form-control derived-source" style="min-height:90px" data-placeholder="Select previous Main Exam Nos">@foreach($sources as $source)<option value="{{ $source->id }}" {{ in_array($source->id,(array)old('source.subject_'.$subject->id,[]))?'selected':'' }}>{{ $source->paper_name }}</option>@endforeach</select>
<div class="derived-help">Ctrl+click or Select2 for multiple exams. Linked Main Exam syllabus and MCQ ranges stay in the database.</div><div class="derived-preview">Selected Exams: <span class="selected-source">—</span></div></div></div>
@endforeach</div>
<div class="col-md-6">
@foreach($departments as $department)
<div class="form-group row derived-column"><label class="col-md-4 text-right">{{ $department->name }}</label><div class="col-md-8"><select name="source[department_{{ $department->id }}][]" multiple class="form-control derived-source" style="min-height:90px">@foreach($sources as $source)<option value="{{ $source->id }}" {{ in_array($source->id,(array)old('source.department_'.$department->id,[]))?'selected':'' }}>{{ $source->paper_name }}</option>@endforeach</select><div class="derived-help">Exam links only; the full Routine text stays hidden.</div><div class="derived-preview">Selected Exams: <span class="selected-source">—</span></div></div></div>
@endforeach
<div class="form-group row"><label class="col-md-4 text-right">Date &amp; Time</label><div class="col-md-8"><input type="datetime-local" class="form-control" name="exam_at" value="{{ old('exam_at') }}" required></div></div>
<div class="form-group row"><label class="col-md-4 text-right">Exam End Date &amp; Time</label><div class="col-md-8"><input type="datetime-local" class="form-control" name="exam_end_at" value="{{ old('exam_end_at') }}"></div></div>
<div class="form-group row"><label class="col-md-4 text-right">Create PDF Date &amp; Time</label><div class="col-md-8"><input type="datetime-local" class="form-control" readonly placeholder="Auto from previous Exam End"></div></div>
<div class="form-group row"><label class="col-md-4 text-right">Question Create Date &amp; Time</label><div class="col-md-8"><input type="datetime-local" class="form-control" readonly placeholder="Auto from previous Exam End"></div></div>
</div></div></div><div class="box-footer"><button type="submit" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add {{ ucfirst($type) }} Exam</button></div></form>
</div></div></div></section>
@endsection
@section('scripts')<script>$(function(){function refresh(s){var names=$(s).find('option:selected').map(function(){return $(this).text()}).get();$(s).closest('.derived-column').find('.selected-source').text(names.join(', ')||'—')}$('.derived-source').each(function(){if($.fn.select2)$(this).select2({width:'100%',placeholder:'Select Main Exam Nos'});refresh(this)}).on('change',function(){refresh(this)})});</script>@endsection
