@extends('dashboard')
@section('title','Auto Question Papers - All Courses')
@section('content')
<section class="content-header">
<a href="{{ route('admin.exam.activity') }}" class="btn btn-default btn-sm pull-right" style="margin-top:0"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
<h1>Auto Question Papers — All Courses</h1>
<ol class="breadcrumb"><li><a href="{{ route('admin.routine-auto-question.index') }}">Question Papers</a></li><li class="active">Courses</li></ol></section>
<section class="content"><div class="box box-primary"><div class="box-header with-border"><h3 class="box-title" style="color:#800">List of Courses</h3></div>
<div class="box-body table-responsive no-padding"><table class="table table-bordered table-hover">
<thead><tr><th>ID</th><th>Course Name</th><th>Active Batches</th><th>Routines</th><th>Exam Folders</th><th>Status</th><th>Action</th></tr></thead>
<tbody>@forelse($courses as $course)
<tr><td>{{ $course->id }}</td><td>{{ $course->name }}</td><td>{{ $course->active_batch_count }}</td>
<td>{{ $routineCounts[$course->id] ?? 0 }}</td><td>{{ $examCounts[$course->id] ?? 0 }}</td>
<td><span class="label {{ $course->status === 'Active' ? 'label-success' : 'label-default' }}">{{ $course->status }}</span></td>
<td><a class="btn btn-info btn-sm" href="{{ route('admin.routine-auto-question.course', ['courseId'=>$course->id]) }}" title="View Active Batches"><i class="fa fa-eye"></i> View</a></td></tr>
@empty<tr><td colspan="7" class="text-center">No courses found.</td></tr>@endforelse</tbody></table></div>
<div class="box-footer"><small>All courses appear here. Only batches active within their start and end date/time count as Active.</small></div></div></section>
@endsection
