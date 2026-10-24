@extends('dashboard')
@section('title', 'Exam Department Details')
@section('content')
<style>
.lebd-detail-row{display:flex;align-items:center;gap:15px;padding:13px 16px;margin-bottom:10px;background:#fff;border:1px solid #d9e3ec;border-radius:5px;flex-wrap:wrap}
.lebd-detail-name{flex:1 1 250px;font-size:16px;font-weight:600}.lebd-detail-name i{color:#d79b29;font-size:22px;margin-right:8px}
.lebd-detail-settings{flex:2 1 500px;display:flex;gap:10px 18px;flex-wrap:wrap;color:#526477;font-size:12px}
.lebd-detail-actions{flex:0 0 auto;margin-left:auto}.lebd-detail-actions .btn{min-width:150px}
.lebd-summary{background:#f8fbfd;padding:14px;border:1px solid #dce4eb;border-radius:5px;margin-bottom:16px}
@media(max-width:700px){.lebd-detail-actions{width:100%;margin-left:0}.lebd-detail-actions .btn{width:100%}}
</style>
<section class="content-header"><h1>Exam Details — {{ $exam->exam_name }}</h1></section>
<section class="content"><div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title" style="color:#900">{{ $course->name }} / {{ $batch->name }}</h3>
<a class="btn btn-primary btn-sm pull-right" style="margin-right:8px" href="{{ route('admin.routine-auto-question.exam-settings-view',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id]) }}"><i class="fa fa-cogs"></i> View Settings</a>
<a class="btn btn-default btn-sm pull-right" href="{{ route('admin.routine-auto-question.batch',['courseId'=>$course->id,'batchId'=>$batch->id]) }}"><i class="fa fa-arrow-left"></i> Exam Folders</a></div>
<div class="box-body">
<div class="lebd-summary">
<img src="{{ asset('img/logo.png?v=3008') }}" alt="Live Education BD" style="max-height:56px;max-width:190px;object-fit:contain;margin-bottom:10px">
<p><strong>Routine:</strong> {{ $exam->routine_name }} &nbsp; <strong>Exam:</strong> {{ $exam->exam_name }} &nbsp; <strong>Status:</strong> {{ $status }}</p>
<p><strong>Start:</strong> {{ $start ?: 'Not scheduled' }} &nbsp; <strong>End:</strong> {{ $end ?: 'Not scheduled' }}</p>
<p><strong>Next Exam:</strong> {{ $next->name ?? 'None' }} &nbsp; <strong>Next Date:</strong> {{ $nextDate ?: 'Not scheduled' }}</p>
@if(!$settings)
<div class="alert alert-warning" style="margin-bottom:0">Batch Paper Settings have not been saved yet. The department rows will display the saved settings once available.</div>
@else
<p class="text-muted" style="margin-bottom:0">One saved Batch Paper Setting applies to every department below. The full paper will combine the common Non-Department section and the department-specific section when questions are generated.</p>
@endif
</div>
<h4>Departments ({{ $departments->count() }})</h4>
@forelse($departments as $department)
<div class="lebd-detail-row">
<div class="lebd-detail-name"><i class="fa fa-folder-open"></i> {{ $department->name }}</div>
<div class="lebd-detail-settings">
<span><b>Limit:</b> {{ $settings->question_limit ?? 'Not set' }}</span>
<span><b>Time:</b> {{ $settings ? $settings->running_minutes.' min' : 'Not set' }}</span>
<span><b>Correct:</b> {{ $settings->correct_mark ?? '—' }}</span>
<span><b>Negative:</b> {{ $settings->negative_mark ?? '—' }}</span>
<span><b>Format:</b> {{ $settings->mcq_format ?? '—' }}</span>
<span><b>Attempts:</b> {{ $settings->attempt_limit ?? '—' }}</span>
<span><b>Random:</b> {{ $settings ? ($settings->random_questions ? 'Yes' : 'No') : '—' }}</span>
<span><b>Display:</b> {{ $settings->question_display ?? '—' }}</span>
<span><b>Paid:</b> {{ $settings ? ($settings->paid_exam ? 'Yes' : 'Free') : '—' }}</span>
</div>
<div class="lebd-detail-actions">
<a class="btn btn-default btn-sm" style="margin-right:5px" href="{{ route('admin.routine-auto-question.exam-settings-view',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id,'department'=>$department->id]) }}"><i class="fa fa-cog"></i> View Settings</a>
<a class="btn btn-info btn-sm" href="{{ route('admin.routine-auto-question.department-paper',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id,'departmentId'=>$department->id]) }}"><i class="fa fa-eye"></i> Full Question View</a></div>
</div>
@empty
<div class="alert alert-info">No departments are assigned to this batch.</div>
@endforelse
<div class="text-muted" style="margin-top:18px">Question counts and actual questions are intentionally not fabricated. They will appear after the question-generation module saves and links each selected question.</div>
</div></div></section>
@endsection
