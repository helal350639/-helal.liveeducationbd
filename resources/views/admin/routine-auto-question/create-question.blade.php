@extends('dashboard')
@section('title','Auto Add Questions')
@section('content')
<style>
.lebd-auto-q .box{border-top:2px solid #3c8dbc;margin-bottom:18px}.lebd-auto-q .box-title{color:#a00000}
.lebd-auto-q .meta{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}
.lebd-auto-q .meta strong{display:block;font-size:12px}.lebd-auto-q .meta span{font-size:13px}
.lebd-auto-q .table-responsive{overflow-x:auto}.lebd-auto-q .table{min-width:1050px}
.lebd-auto-q .table td{vertical-align:middle}.lebd-auto-q .table th{font-size:12px;white-space:nowrap}
.lebd-auto-q .qty{width:94px;max-width:100%}.lebd-auto-q .summary{padding:13px;color:white;border-radius:4px;min-height:80px}
.lebd-auto-q .summary strong{display:block;font-size:24px;margin-top:9px}
@media(max-width:900px){.lebd-auto-q .meta{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
<section class="content-header">
<a href="{{ route('admin.routine-auto-question.batch',['courseId'=>$course->id,'batchId'=>$batch->id]) }}" class="btn btn-default btn-sm pull-right" style="margin-top:0"><i class="fa fa-arrow-left"></i> Back to Exam Folders</a>
<h1><i class="fa fa-magic"></i> Auto Add Questions</h1></section>
<section class="content lebd-auto-q">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Question Paper Information</h3><div class="box-tools pull-right">
<a class="btn btn-success btn-sm" href="{{ route('admin.routine-auto-question.question-extra-source',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id,'source'=>'syllabus']) }}"><i class="fa fa-book"></i> Add Syllabus</a>
<a class="btn btn-info btn-sm" href="{{ route('admin.routine-auto-question.question-extra-source',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id,'source'=>'database']) }}"><i class="fa fa-database"></i> Add Database</a>
<a class="btn btn-default btn-sm" href="{{ route('admin.routine-auto-question.batch',['courseId'=>$course->id,'batchId'=>$batch->id]) }}">Exam Folders</a></div></div>
<div class="box-body"><div class="meta">
<div><strong>Exam Paper No</strong><span>{{ $exam->exam_name }}</span></div><div><strong>Course</strong><span>{{ $course->name }}</span></div><div><strong>Batch</strong><span>{{ $batch->name }}</span></div>
<div><strong>Departments</strong><span>@foreach($departments as $d)<span class="label label-info">{{ $d->name }}</span> @endforeach</span></div>
<div><strong>Max (per Department)</strong><span class="label label-primary">{{ $settings->question_limit ?? 100 }}</span></div>
<div><strong>Current (Unique Pool Saved)</strong><span class="label label-success">{{ $count }}</span></div>
</div><p class="text-muted" style="margin:12px 0 0">Routine: {{ $exam->routine_name }}. Category, Subject, Chapter and Range come directly from saved Routine filters. All filter fields are read-only; difficulty filters will be enabled later.</p></div></div>
<form method="POST" action="{{ route('admin.routine-auto-question.question-plan-save',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id]) }}">@csrf
@foreach(['common'=>'Non Department','department'=>'Department'] as $scope=>$section)
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title"><i class="fa fa-list"></i> {{ $section }}</h3></div><div class="box-body table-responsive"><table class="table table-bordered table-striped">
<thead><tr><th>Category (Auto)</th><th>Department / Routine Column (Auto)</th><th>Subject</th><th>Chapter</th><th>Routine Range</th><th>Available</th><th>Total Questions (Set Manually)</th><th>Difficult</th><th>Medium</th><th>Easy</th></tr></thead><tbody>
@forelse($rows as $i=>$row)
@if($row['scope']===$scope)
<tr><td><strong>{{ $row['category'] }}</strong></td><td>@if($scope==='common')Common @else {{ optional($departments->firstWhere('id',$row['department_id']))->name ?: ('Department #'.$row['department_id']) }} @endif</td>
<td>{{ $row['subject'] ?: 'No Subject — Direct Category MCQs' }}</td>
<td>{{ $row['chapter'] ?: ($row['subject'] ? 'No Chapter — Direct Subject MCQs' : '—') }}</td>
<td>
@if($row['range_start'] !== null && $row['range_end'] !== null)
<span class="label label-warning">{{ $row['range_start'] }} – {{ $row['range_end'] }}</span>
@else <span class="text-muted">All Available</span> @endif
<br><small class="text-muted">{{ $row['routine_column'] }}</small>
</td>
<td><span class="label label-info">{{ $row['available'] }}</span></td>
<td><input type="number" class="form-control qty js-qty" name="qty[{{ $i }}]" min="0" max="{{ $row['available'] }}" value="{{ old('qty.'.$i, optional($saved->get($row['key']))->requested ?? 0) }}" data-scope="{{ $scope }}" data-dept="{{ $row['department_id'] }}" data-avail="{{ $row['available'] }}" required></td>
<td><input class="form-control" value="Later" disabled></td><td><input class="form-control" value="Later" disabled></td><td><input class="form-control" value="Later" disabled></td></tr>
@endif
@empty
<tr><td colspan="10">No Routine selections found.</td></tr>
@endforelse
</tbody></table>
<div class="lebd-quantity-count" style="margin:12px 0 4px;padding:12px 15px;border:1px solid #b9dced;background:#f0f9ff;border-radius:5px;font-size:15px;">
    <strong><i class="fa fa-check-square-o"></i> এই সেকশনে নির্বাচিত প্রশ্ন:</strong>
    <strong class="js-section-qty-total" data-scope="{{ $scope }}" style="font-size:21px;color:#14649b;margin-left:8px;">0</strong>
    <span style="color:#667085;margin-left:6px;">টি</span>
</div>
@if(!collect($rows)->contains(fn($r)=>$r['scope']===$scope))<p class="text-muted">No {{ $section }} selections in this Exam's linked Routine.</p>@endif
</div></div>
@endforeach
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Department-wise Full Paper Preview</h3></div><div class="box-body">
@foreach($departments as $d)
<a class="btn btn-default" style="margin:4px" href="{{ route('admin.routine-auto-question.question-department-preview',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id,'departmentId'=>$d->id]) }}"><i class="fa fa-eye"></i> {{ $d->name }} — Common + Department</a>
@endforeach
<p class="text-muted">After Save, click each Department to inspect the combined questions.</p></div></div>
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Selected Question Summary</h3></div><div class="box-body"><div class="row">
<div class="col-md-3"><div class="summary" style="background:#00b5df">Total Selected <strong id="selected-total">0</strong></div></div>
<div class="col-md-3"><div class="summary" style="background:#00a65a">Available <strong>{{ collect($rows)->sum('available') }}</strong></div></div>
<div class="col-md-3"><div class="summary" style="background:#3c8dbc">Common Selected <strong id="common-total">0</strong></div></div>
<div class="col-md-3"><div class="summary" style="background:#f39c12">Department Selected <strong id="dept-total">0</strong></div></div>
</div><p class="text-muted" style="margin-top:12px">Category/Subject/Chapter filters and question ranges are set only in Routine. Total Selected sums requested row quantities; duplicate MCQs across paths are removed when saving. Saved question IDs use routine ranges and are shared Common + Department-specific.</p>
<button class="btn btn-success pull-right" type="submit"><i class="fa fa-check"></i> DONE — Save Question Selection</button><div class="clearfix"></div></div></div>
</form></section>
<script>
(function(){function update(){let a=0,b=0;document.querySelectorAll('.js-qty').forEach(x=>{let n=Math.max(0,Number(x.value)||0);if(x.dataset.scope==='common')a+=n;else b+=n});document.getElementById('selected-total').textContent=a+b;document.getElementById('common-total').textContent=a;document.getElementById('dept-total').textContent=b;document.querySelectorAll('.js-section-qty-total').forEach(el=>{el.textContent=el.dataset.scope==='common'?a:b;});}document.querySelectorAll('.js-qty').forEach(x=>x.addEventListener('input',update));update();})();
</script>
@endsection
