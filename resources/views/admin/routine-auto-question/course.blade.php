@extends('dashboard')
@section('title','Auto Question Papers - Active Batches')
@section('content')
<section class="content-header"><h1>{{ $course->name }} — Active Batches</h1></section>
<section class="content"><div class="box box-primary"><div class="box-header with-border"><h3 class="box-title" style="color:#800">List of Active Batches</h3>
<a class="btn btn-default btn-sm pull-right" href="{{ route('admin.routine-auto-question.index') }}"><i class="fa fa-arrow-left"></i> All Courses</a></div>
<div class="box-body table-responsive no-padding"><table class="table table-bordered table-hover"><thead><tr><th>ID</th><th>Batch Name</th><th>Start Date &amp; Time</th><th>End Date &amp; Time</th><th>Time Remaining</th><th>Routines</th><th>Exam Folders</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($batches as $batch)
@php $seconds = max(0, $now->diffInSeconds(\Carbon\Carbon::parse($batch->end_at, 'Asia/Dhaka'), false)); $days = intdiv((int)$seconds,86400); $hours = intdiv(((int)$seconds)%86400,3600); @endphp
<tr><td>{{ $batch->id }}</td><td>{{ $batch->name }}</td><td>{{ $batch->start_at }}</td><td>{{ $batch->end_at }}</td>
<td>{{ $days }} day(s), {{ $hours }} hour(s)</td><td>{{ $routineCounts[$batch->id] ?? 0 }}</td><td>{{ $examCounts[$batch->id] ?? 0 }}</td>
<td><span class="label label-success">Active</span></td><td><a class="btn btn-info btn-sm" href="{{ route('admin.routine-auto-question.batch',['courseId'=>$course->id,'batchId'=>$batch->id]) }}"><i class="fa fa-eye"></i> View</a> <a class="btn btn-warning btn-sm" href="{{ route('admin.routine-auto-question.batch-settings-preview',['courseId'=>$course->id,'batchId'=>$batch->id]) }}"><i class="fa fa-cogs"></i> Paper Settings</a></td></tr>
@empty<tr><td colspan="9" class="text-center">No batches currently active for this course.</td></tr>@endforelse
</tbody></table></div><div class="box-footer"><small>Bangladesh time: {{ $now->format('d M Y, h:i A') }}. Active = status Active, start_at ≤ now, end_at ≥ now.</small></div></div></section>
@endsection
