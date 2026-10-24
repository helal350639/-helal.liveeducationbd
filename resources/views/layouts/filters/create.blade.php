@extends('dashboard')
@section('title', 'Add New Filter')
@section('content')
<section class="content-header"><h1>Add a Filter</h1></section>
<section class="content"><div class="row"><div class="col-md-8"><div class="box box-primary">
<div class="box-header with-border"><h3 style="color:#800" class="box-title">Filter Details</h3></div>
<form action="{{ route('filter.store') }}" method="POST">@csrf
<div class="box-body">
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="form-group"><label>Course Name</label><select class="form-control select2" multiple name="course_id[]" id="course_id">@foreach($courses as $val)<option value="{{ $val->id }}" {{ in_array($val->id, old('course_id', [])) ? 'selected' : '' }}>{{ $val->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Filter Name</label><input type="text" class="form-control" name="name" value="{{ old('name') }}" required></div>
<div class="form-group"><label for="order_number">Filter Serial Number</label><input type="number" min="1" max="999" step="1" class="form-control" name="order_number" id="order_number" value="{{ old('order_number') }}" placeholder="e.g. 1 = first filter, 2 = second" required><p class="help-block">Choose a free serial manually. If it is in use, the existing filter name will be shown; no other serial is changed automatically.</p></div>
<div class="form-group"><label><input type="checkbox" name="label" value="Yes" {{ old('label') === 'Yes' ? 'checked' : '' }}> Use as Label</label></div>
<div class="form-group"><label>Details</label><textarea class="form-control" name="details" rows="5">{{ old('details') }}</textarea></div>
</div><div class="box-footer"><button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save</button></div>
</form></div></div></div></section>
@endsection
@section('scripts')<script>$(function () { $('.select2').select2(); });</script>@endsection
