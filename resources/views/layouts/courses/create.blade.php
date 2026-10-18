@extends('dashboard')
@section('title', 'Add New Course')
@section('stylesheets')<link href="/assets/summernote/summernote.min.css" rel="stylesheet">@endsection
@section('content')
<section class="content-header"><h1>Add a Course</h1><ol class="breadcrumb"><li><a href="#"><i class="fa fa-dashboard"></i> Courses</a></li><li class="active">Add Course</li></ol></section>
<section class="content"><div class="row"><div class="col-md-10"><div class="box box-primary"><div class="box-header with-border"><h3 style="color:#800" class="box-title">Course Details</h3></div>
<form action="{{ route('course.store') }}" method="POST" enctype="multipart/form-data">@csrf
<div class="box-body">
<div class="col-md-12"><div class="form-group"><label for="name">Course Name</label><input class="form-control" name="name" id="name" value="{{old('name')}}" maxlength="255" required>@error('name')<p class="text-danger">{{$message}}</p>@enderror</div></div>
<div class="clearfix"></div><div class="col-md-12"><div class="form-group"><label for="details">Details</label><textarea class="form-control editor" name="details" id="details" rows="5">{{old('details')}}</textarea></div></div>
@include('layouts.courses.partials.recruitment-logo-picker')
<div class="col-md-12"><div class="form-group"><label for="banner">Banner</label><input type="file" accept="image/jpeg,image/png,image/webp" class="form-control" name="banner" id="banner"><p class="help-block">Optional fallback banner · JPEG / PNG / WebP · max 2 MB · recommended 400×600</p></div></div>
</div><div class="box-footer"><button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save</button></div>
</form></div></div></div></section>
@endsection
@section('scripts')<script src="/assets/summernote/summernote.min.js"></script><script>$(document).ready(function(){$('.editor').summernote({height:150});});</script>@endsection
