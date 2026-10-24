@extends('dashboard')
@section('title','Batch Paper Settings')
@section('content')
<style>
.lebd-settings .form-group{margin-bottom:15px}.lebd-settings label{font-size:12px}
.lebd-settings .form-control{border-radius:4px}.lebd-settings .box-title{color:#900}
.lebd-preview{border:1px solid #dce4ea;padding:15px;border-radius:5px;background:#fafcff}
.lebd-preview .brand img{max-width:210px;max-height:90px;object-fit:contain}
.lebd-preview h4{margin:6px 0}.lebd-settings .help-block{font-size:12px}
</style>
<section class="content-header"><h1>Batch Question Paper Settings</h1></section>
<section class="content lebd-settings"><div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title">{{ $course->name }} / {{ $batch->name }}</h3>
<a class="btn btn-default btn-sm pull-right" href="{{ route('admin.routine-auto-question.course',['courseId'=>$course->id]) }}"><i class="fa fa-arrow-left"></i> Active Batches</a></div>
<div class="box-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><b>Settings not saved.</b><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<p class="alert alert-info"><b>One Batch Setting:</b> Settings saved here will be shared by every Exam, Revision, Model and Department in this Batch. Exam dates and header information are read from Routine.</p>
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.routine-auto-question.batch-settings-save',['courseId'=>$course->id,'batchId'=>$batch->id]) }}">
@csrf
<div class="row">
<div class="col-md-4 form-group"><label>Course (Routine-linked)</label><input class="form-control" readonly value="{{ $course->name }}"></div>
<div class="col-md-4 form-group"><label>Batch (Routine-linked)</label><input class="form-control" readonly value="{{ $batch->name }}"></div>
<div class="col-md-4 form-group"><label>Batch Date Range</label><input class="form-control" readonly value="{{ $batch->start_at }} — {{ $batch->end_at }}"></div>
</div>
<h4>Exam-wise Auto Information Preview</h4>
<div class="form-group"><label>Select Exam for Preview (does not change shared Batch Settings)</label>
<select class="form-control" onchange="window.location.href=this.value">
@foreach($exams as $ex)
<option value="{{ route('admin.routine-auto-question.batch-settings-preview',['courseId'=>$course->id,'batchId'=>$batch->id,'exam'=>$ex->exam_id]) }}" {{ $exam && $exam->exam_id==$ex->exam_id ? 'selected':'' }}>{{ $ex->routine_name }} — {{ $ex->exam_name }}</option>
@endforeach
</select></div>
@if($exam)
<div class="lebd-preview">
<div class="text-center brand"><img src="{{ asset('img/logo.png?v=3008') }}" alt="Live Education BD logo"><h4>{{ $course->name }}</h4><b>{{ $batch->name }}</b>
<p><b>{{ $exam->exam_name }}</b> | Exam Date: {{ $info['start'] ? $info['start']->format('d M Y, h:i A') : 'Not scheduled' }}</p></div>
<hr>
<p><b>Status:</b> <span class="label label-{{ $info['status']==='Published'?'success':($info['status']==='Scheduled'?'warning':'default') }}">{{ $info['status'] }}</span></p>
<p><b>Exam Start:</b> {{ $info['start'] ? $info['start']->format('d M Y, h:i A') : 'Not scheduled' }} &nbsp; <b>Exam End:</b> {{ $info['end'] ? $info['end']->format('d M Y, h:i A') : 'Not scheduled' }}</p>
<p><b>Exam Running Time:</b> {{ old('running_minutes',$effective->running_minutes) }} minutes. <b>Instruction:</b> পরীক্ষায় প্রবেশ করে Attempt শুরু করার সময় থেকে নির্ধারিত পরীক্ষার সময় গণনা শুরু হবে। তবে Routine-এর Exam End Date অতিক্রম করা যাবে না।</p>
<p><b>Question Sections:</b></p>
@if($info['isDerived'])
<p class="text-muted">Revision/Model: Linked Main Exam selections will be resolved for the exact question count in the question generation phase.</p>
@elseif(count($info['counts']))
<ul>@foreach($info['counts'] as $count)
<li>{{ $count['kind']==='subject'?'Non-Department':'Department' }} — Routine Column {{ $count['column_id'] }}:
@if($count['unbounded']) Question range not fully specified @else Selected range size {{ $count['estimated'] }} (may include overlapping questions) @endif</li>
@endforeach</ul>
@else<p class="text-muted">No MCQ selection range is saved for this exam.</p>@endif
@php
$nextLabel=$info['next']->name ?? null;
$nextDate=$info['nextRow']->exam_at ?? null;
$publish=$info['end'] ? $info['end']->format('d M Y, h:i A') : 'After exam end';
@endphp
<hr><b>Message After Exam (automatic preview)</b>
<div style="white-space:pre-line;margin-top:8px">আপনার উত্তরপত্রটি সঠিকভাবে জমা হয়েছে।
রেজাল্ট প্রকাশ করা হবে: {{ $publish }}
@if($nextLabel)পরবর্তী এক্সাম নং: {{ $nextLabel }}
পরবর্তী এক্সামের তারিখ: {{ $nextDate ?: 'এখনো নির্ধারিত হয়নি' }}
@endif
ধন্যবাদ।</div>
</div>
@else<div class="alert alert-warning">No Exam Names found in a Routine attached to this Batch.</div>@endif
<hr><h4>Shared Batch Question Paper Settings</h4>
<div class="row">
<div class="col-md-12 form-group"><label>Header — Auto Logo, Course, Batch, Exam No &amp; Date are shown above</label>
<textarea name="header_extra" class="form-control" rows="2" placeholder="Optional additional header text">{{ old('header_extra',$effective->header_extra) }}</textarea></div>
<div class="col-md-12 form-group"><label>Details — Optional Additional Note</label><textarea name="details_extra" class="form-control" rows="2">{{ old('details_extra',$effective->details_extra) }}</textarea></div>
<div class="col-md-12 form-group"><label>Banner (Optional; Recommended 800 × 100)</label><input class="form-control" type="file" name="banner" accept="image/png,image/jpeg,image/webp">
@if($effective->banner_path)<a target="_blank" href="{{ asset('storage/'.$effective->banner_path) }}">View Current Banner</a>@endif</div>
@php
$fields=[
['question_limit','Questions Limit','number',100],['running_minutes','Exam Running Time (minute)','number',40],
['correct_mark','Mark (Correct Answer)','number',1],['negative_mark','Mark (Wrong Answer — deducted)','number',0]
];
$selects=[
['mcq_format','MCQ List Format',['bn'=>'বাংলা: ক) খ) গ) ঘ)','en-lower'=>'English: a) b) c) d)','en-upper'=>'English: A) B) C) D)','bn-number'=>'বাংলা: ১) ২) ৩) ৪)','roman'=>'Roman: (i) (ii) (iii) (iv)']],
['attempt_limit','How Many Times Can Student Take Exam?',['1'=>'1','2'=>'2','3'=>'3','multiple'=>'Multiple']],
['random_questions','Show Random Questions?',['1'=>'Yes','0'=>'No']],
['question_display','Display Questions',['all'=>'All in One Paper','one'=>'One by One']],
['paid_exam','Paid Exam?',['0'=>'Free','1'=>'Yes']],
['email_result','Send Result to Account Email?',['0'=>'No','1'=>'Yes']],
['student_view_result','Student Can View Result After Exam?',['0'=>'No (publish on result date)','1'=>'Yes']]
];
@endphp
@foreach($fields as $field)
<div class="col-md-6 form-group"><label>{{ $field[1] }}</label><input class="form-control" type="number" name="{{ $field[0] }}" step="{{ in_array($field[0],['correct_mark','negative_mark'])?'0.01':'1' }}" min="0" value="{{ old($field[0],$effective->{$field[0]}) }}" required></div>
@endforeach
@foreach($selects as $field)
<div class="col-md-6 form-group"><label>{{ $field[1] }}</label><select name="{{ $field[0] }}" class="form-control">
@foreach($field[2] as $key=>$text)<option value="{{ $key }}" {{ (string)old($field[0],$effective->{$field[0]})===(string)$key?'selected':'' }}>{{ $text }}</option>@endforeach
</select></div>
@endforeach
<div class="col-md-6 form-group"><label>Status (Auto from Routine Date/Time)</label><input class="form-control" readonly value="{{ $info['status'] ?? 'Not Scheduled' }}"></div>
<div class="col-md-6 form-group"><label>Exam Start &amp; End (Auto from Routine)</label><input class="form-control" readonly value="{{ $info && $info['start'] ? $info['start']->format('Y-m-d H:i').' — '.($info['end']?$info['end']->format('Y-m-d H:i'):'Not set') : 'Not scheduled' }}"></div>
<div class="col-md-6 form-group"><label>Result Publish Date &amp; Time (Auto from Exam End)</label><input class="form-control" readonly value="{{ $info && $info['end'] ? $info['end']->format('Y-m-d H:i') : 'Not scheduled' }}"></div>
</div>
<div class="alert alert-warning"><b>Note:</b> Saving settings does not yet send emails, charge fees, publish student results, start the exam timer or generate any questions. These require student exam integration in a later step.</div>
<button class="btn btn-primary pull-right" type="submit"><i class="fa fa-save"></i> Save Batch Settings</button>
<a class="btn btn-default" href="{{ route('admin.routine-auto-question.batch',['courseId'=>$course->id,'batchId'=>$batch->id]) }}">Back to Exam Folders</a>
</form></div></div></section>
@endsection
