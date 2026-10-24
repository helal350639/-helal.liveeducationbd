@extends('dashboard')
@section('title','Edit '.ucfirst($type).' Routine Item')
@section('content')
@include('layouts.routines.partials.all-pages-navigation')

<section class="content-header"><h1>Edit a {{ ucfirst($type) }} Routine Item</h1></section>
<section class="content"><div class="row"><div class="col-md-12"><div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title" style="color:#800">{{ $routine->name }} — {{ $item->exam_name }}</h3><a class="pull-right btn btn-success btn-xs" href="{{ route('routine.item.show',$routine->id) }}"><i class="fa fa-list"></i> Show Routine</a></div>
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form action="{{ route('routine.derived.update',$item->id) }}" method="POST">@csrf @method('PUT')
<div class="box-body"><div class="row"><div class="col-md-6">
<div class="form-group row"><label class="col-md-4 text-right">Exam No</label><div class="col-md-8"><input class="form-control" value="{{ $item->exam_name }}" readonly></div></div>
@foreach($subjects as $subject)
@php $key='subject_'.$subject->id; $ids=array_map('intval',(array)old('source.'.$key,$selected[$key]??[])); @endphp
<div class="form-group row derived-column"><label class="col-md-4 text-right">{{ $subject->name }}</label><div class="col-md-8"><select multiple class="form-control derived-source" name="source[{{ $key }}][]">@foreach($sources as $source)<option value="{{ $source->id }}" {{ in_array((int)$source->id,$ids,true)?'selected':'' }}>{{ $source->paper_name }}</option>@endforeach</select><small>Selected Main Exam Nos only; full syllabus remains linked.</small></div></div>
@endforeach
</div><div class="col-md-6">
@foreach($departments as $department)
@php $key='department_'.$department->id; $ids=array_map('intval',(array)old('source.'.$key,$selected[$key]??[])); @endphp
<div class="form-group row derived-column"><label class="col-md-4 text-right">{{ $department->name }}</label><div class="col-md-8"><select multiple class="form-control derived-source" name="source[{{ $key }}][]">@foreach($sources as $source)<option value="{{ $source->id }}" {{ in_array((int)$source->id,$ids,true)?'selected':'' }}>{{ $source->paper_name }}</option>@endforeach</select></div></div>
@endforeach
<div class="form-group row"><label class="col-md-4 text-right">Date &amp; Time</label><div class="col-md-8"><input class="form-control" type="datetime-local" name="exam_at" required value="{{ old('exam_at', $item->exam_at ? \Carbon\Carbon::parse($item->exam_at)->format('Y-m-d\TH:i') : '') }}"></div></div>
<div class="form-group row"><label class="col-md-4 text-right">Exam End Date &amp; Time</label><div class="col-md-8"><input class="form-control" type="datetime-local" name="exam_end_at" value="{{ old('exam_end_at', $item->exam_end_at ? \Carbon\Carbon::parse($item->exam_end_at)->format('Y-m-d\TH:i') : '') }}"></div></div>
<div class="form-group row"><label class="col-md-4 text-right">Create PDF Date &amp; Time</label><div class="col-md-8"><input class="form-control" readonly value="{{ $item->create_pdf_at }}"></div></div>
<div class="form-group row"><label class="col-md-4 text-right">Question Create Date &amp; Time</label><div class="col-md-8"><input class="form-control" readonly value="{{ $item->question_create_at }}"></div></div>
</div></div></div><div class="box-footer"><button class="btn btn-primary pull-right" type="submit"><i class="fa fa-save"></i> Update</button></div></form>
</div></div></div></section>
@endsection
@section('scripts')<script>$(function(){if($.fn.select2){$('.derived-source').select2({width:'100%',placeholder:'Select Main Exam Nos'});}});</script>@endsection
