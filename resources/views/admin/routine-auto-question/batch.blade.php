@extends('dashboard')
@section('title', 'Auto Question Papers - Exam Folders')
@section('stylesheets')
<style>
.lebd-exam-list {margin-top:12px;}
.lebd-exam-row {display:flex;align-items:center;gap:16px;padding:10px 14px;margin-bottom:8px;border:1px solid #d9e3ec;border-radius:4px;background:#fff;min-height:56px;box-shadow:0 1px 2px rgba(0,0,0,.04);}
.lebd-exam-row-name {display:flex;align-items:center;gap:9px;min-width:170px;flex:0 0 200px;}
.lebd-exam-row-name .fa-folder-open {font-size:21px;color:#d99b25;}
.lebd-exam-row-name strong {font-size:15px;overflow-wrap:anywhere;}
.lebd-exam-row-type {flex:0 0 105px;}
.lebd-exam-row-schedule {flex:0 1 260px;min-width:185px;font-size:12px;line-height:1.65;color:#4f6271;}
.lebd-exam-row-schedule .label{margin-left:6px;}
.lebd-exam-row-routine {flex:1 1 auto;min-width:0;overflow-wrap:anywhere;color:#687987;}
.lebd-exam-row-actions {display:flex;gap:8px;flex:0 0 auto;margin-left:auto;flex-wrap:wrap;justify-content:flex-end;}
.lebd-exam-row-actions .btn {min-width:130px;}
@media(max-width:800px){.lebd-exam-row{flex-wrap:wrap;gap:8px}.lebd-exam-row-name{flex:1 1 180px}.lebd-exam-row-type{flex:0 0 auto}.lebd-exam-row-routine{flex:1 1 100%}.lebd-exam-row-schedule{flex:1 1 100%}.lebd-exam-row-actions{margin-left:0;width:100%;justify-content:flex-end}.lebd-exam-row-actions .btn{min-width:0;flex:1 1 120px}}
</style>
@endsection
@section('content')
<section class="content-header"><h1>Exam Folders</h1>
<ol class="breadcrumb"><li><a href="{{ route('admin.routine-auto-question.index') }}">Courses</a></li><li><a href="{{ route('admin.routine-auto-question.course',['courseId'=>$course->id]) }}">Active Batches</a></li><li class="active">Exam Folders</li></ol></section>
<section class="content"><div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title" style="color:#800">{{ $course->name }} / {{ $batch->name }}</h3>
<a class="btn btn-warning btn-sm pull-right" style="margin-right:8px" href="{{ route('admin.routine-auto-question.batch-settings-preview',['courseId'=>$course->id,'batchId'=>$batch->id]) }}"><i class="fa fa-cogs"></i> Paper Settings</a>
<a class="btn btn-default btn-sm pull-right" href="{{ route('admin.routine-auto-question.course',['courseId'=>$course->id]) }}"><i class="fa fa-arrow-left"></i> Batches</a></div>
<div class="box-body">
<p><strong>Batch:</strong> {{ $batch->start_at }} — {{ $batch->end_at }} <span class="label label-success">Active</span></p>
<p class="text-muted">Exam folders appear automatically from saved Routine Exam Names, in Routine order. No questions are generated yet.</p>
@php
    $examSchedules = app(\App\Services\RoutineAutoQuestion\ExamFolderScheduleService::class)->forExams($exams);
@endphp
<div class="lebd-exam-list">
@forelse($exams as $exam)
@php
$type = preg_match('/^(revision|revition)/i', trim($exam->exam_name)) ? 'Revision'
: (preg_match('/^model/i',trim($exam->exam_name)) ? 'Model' : 'Main Exam');
@endphp
<div class="lebd-exam-row">
<div class="lebd-exam-row-name"><i class="fa fa-folder-open"></i><strong>{{ $exam->exam_name }}</strong></div>
<div class="lebd-exam-row-type"><span class="label label-{{ $type === 'Model' ? 'info' : ($type === 'Revision' ? 'warning' : 'primary') }}">{{ $type }}</span></div>
<div class="lebd-exam-row-routine" title="{{ $exam->routine_name }}"><i class="fa fa-calendar"></i> {{ $exam->routine_name }}</div>
@php
  $schedule = $examSchedules[(int)$exam->exam_id] ?? ['start'=>'Not scheduled','end'=>'Not scheduled','status'=>'Not Scheduled'];
  $statusColor = ['Scheduled'=>'warning','Published'=>'success','Ended'=>'default',
        'Not Scheduled'=>'default','End Not Set'=>'warning','Invalid Schedule'=>'danger'][$schedule['status']] ?? 'default';
@endphp
<div class="lebd-exam-row-schedule">
    <div><i class="fa fa-calendar-check-o"></i> <strong>Start:</strong> {{ $schedule['start'] }}</div>
    <div><i class="fa fa-clock-o"></i> <strong>End:</strong> {{ $schedule['end'] }}</div>
    <div><strong>Status:</strong> <span class="label label-{{ $statusColor }}">{{ $schedule['status'] }}</span></div>
</div>
<div class="lebd-exam-row-actions">
<a class="btn btn-success btn-sm" href="{{ route('admin.routine-auto-question.create-question',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id]) }}"><i class="fa fa-plus-circle"></i> Create Question</a>
<a class="btn btn-info btn-sm" href="{{ route('admin.routine-auto-question.exam-details',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id]) }}"><i class="fa fa-eye"></i> Details View</a>
<form method="POST" action="{{ route('admin.routine-auto-question.question-plan-reset', ['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id]) }}" style="display:inline-block;margin:0;" onsubmit="return confirm('Reset saved auto-generated questions for {{ addslashes($exam->exam_name) }}? This cannot be undone. The original question bank and Routine will remain unchanged.');">
@csrf
<button type="submit" class="btn btn-danger btn-sm" title="Reset only this exam's saved auto questions"><i class="fa fa-refresh"></i> Reset Questions</button>
</form>
</div>
</div>
@empty
<div class="alert alert-info">No Routine Exam Names have been created for this batch.</div>
@endforelse
</div></div></div></section>
@endsection
