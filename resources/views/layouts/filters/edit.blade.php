@extends('dashboard')
@section('title', 'Edit Filter')
@section('content')
<section class="content-header"><h1>Edit Filter</h1></section>
<section class="content"><div class="row"><div class="col-md-8"><div class="box box-primary">
<div class="box-header with-border"><h3 style="color:#800" class="box-title">Filter Details</h3></div>
<form action="{{ route('filter.update', $filter) }}" method="POST">@csrf @method('PUT')
<div class="box-body">
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@php $course_ids = $filter->courses->pluck('id')->all(); @endphp
<div class="form-group"><label>Course Name</label><select class="form-control select2" multiple name="course_id[]" id="course_id">@foreach($courses as $val)<option value="{{ $val->id }}" {{ in_array($val->id, old('course_id', $course_ids)) ? 'selected' : '' }}>{{ $val->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Filter Name</label><input type="text" class="form-control" name="name" value="{{ old('name', $filter->name) }}" required></div>
<div class="form-group"><label>Filter Slug</label><input type="text" class="form-control" name="slug" value="{{ old('slug', $filter->slug) }}" {{ $filter->slug ? 'readonly' : '' }}></div>
<div class="form-group"><label for="order_number">Filter Serial Number</label><input type="number" min="1" max="999" step="1" class="form-control" name="order_number" id="order_number" value="{{ old('order_number', $filter->order_number) }}" required><p class="help-block">Serial 1 appears first, 2 second, etc. Must be unique; you choose the number manually. To exchange positions, move one filter to a spare number first.</p></div>
<div class="form-group"><label><input type="checkbox" name="label" value="Yes" {{ old('label', $filter->label) === 'Yes' ? 'checked' : '' }}> Use as Label</label></div>
<div class="form-group"><label>Details</label><textarea class="form-control" name="details" rows="5">{{ old('details', $filter->details) }}</textarea></div>
</div><div class="box-footer"><button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save</button></div>
</form></div></div></div></section>
@endsection
@section('scripts')<script>$(function () { $('.select2').select2(); });</script>@endsection
