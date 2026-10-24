@extends('dashboard')
@section('title','View Exam Paper Settings')
@section('content')
<style>
.lebd-esv .box-title{color:#900}
.lebd-esv .lebd-header{background:#f8fbfe;border:1px solid #dce5ec;border-radius:5px;padding:20px;margin-bottom:18px}
.lebd-esv .lebd-header img{max-width:215px;max-height:80px;object-fit:contain}
.lebd-esv .form-group{margin-bottom:16px}
.lebd-esv label{font-size:12px}
.lebd-esv .read-value{border:1px solid #d9e2e9;background:#f7f9fc;border-radius:4px;padding:9px 12px;min-height:37px;white-space:pre-wrap;overflow-wrap:anywhere}
.lebd-esv .read-value.long{min-height:65px}
.lebd-esv .section-title{font-size:17px;border-bottom:1px solid #e5eaf0;padding-bottom:9px;margin:24px 0 15px}
</style>
@php
$fmt=['bn'=>'বাংলা: ক) খ) গ) ঘ)','en-lower'=>'English: a) b) c) d)','en-upper'=>'English: A) B) C) D)','bn-number'=>'বাংলা: ১) ২) ৩) ৪)','roman'=>'Roman: (i) (ii) (iii) (iv)'];
$start=$info['start'] ?? null;
$end=$info['end'] ?? null;
$nextName=$info['next']->name ?? null;
$nextAt=$info['nextRow']->exam_at ?? null;
$displayDate=fn($date)=>$date ? \Carbon\Carbon::parse($date,'Asia/Dhaka')->format('d M Y, h:i A') : 'Not scheduled';
$boolLabel=fn($x)=>$x ? 'Yes' : 'No';
@endphp
<section class="content-header"><h1>View Question Paper Settings — {{ $exam->exam_name }}</h1></section>
<section class="content lebd-esv"><div class="box box-primary">
<div class="box-header with-border">
<h3 class="box-title">{{ $course->name }} / {{ $batch->name }}</h3>
<a class="btn btn-default btn-sm pull-right" href="{{ route('admin.routine-auto-question.exam-details',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id]) }}"><i class="fa fa-arrow-left"></i> Exam Details</a>
</div>
<div class="box-body">
@if(!$settings)
<div class="alert alert-warning">Batch settings have NOT been saved. The values below are display defaults only, not saved values.</div>
@else
<div class="alert alert-info"><b>Read-only view.</b> These settings were saved for the Batch and apply to every linked Exam and Department. Routine-dependent dates and exam details change for this Exam.</div>
@endif
<div class="lebd-header text-center">
<img src="{{ asset('img/logo.png?v=3008') }}" alt="Live Education BD logo">
<h3 style="margin:9px 0 6px">Live Education BD</h3>
<h4>{{ $course->name }}</h4>
<p><b>Batch:</b> {{ $batch->name }}</p>
<p><b>Exam No:</b> {{ $exam->exam_name }} @if($department) | <b>Department:</b> {{ $department->name }} @endif</p>
<p><b>Exam Date:</b> {{ $displayDate($start) }}</p>
@if($effective->header_extra)<div style="white-space:pre-wrap">{{ $effective->header_extra }}</div>@endif
</div>
<h4 class="section-title">Header, Details &amp; Banner</h4>
<div class="row">
<div class="col-md-12 form-group"><label>Details — Auto Information</label>
<div class="read-value long">Exam No: {{ $exam->exam_name }}
Exam Running Time: {{ $effective->running_minutes }} minute(s)
Instruction: এই পরীক্ষায় প্রবেশ করে Attempt শুরু করলে নির্ধারিত সময় গণনা শুরু হবে। তবে Routine-এর Exam End Time অতিক্রম করা যাবে না।
@if($info['isDerived'])
Revision/Model: Linked Main Exam Question Count will be confirmed after question generation.
@elseif(count($info['counts']))
@foreach($info['counts'] as $item)
{{ $item['kind']==='subject' ? 'Non-Department' : 'Department' }} — Routine Column {{ $item['column_id'] }}: {{ $item['unbounded'] ? 'Range not fully specified' : 'Selected range estimate: '.$item['estimated'].' (duplicates may overlap)' }}
@endforeach
@else
Question Count: No saved MCQ selection range for this exam.
@endif</div>
</div>
<div class="col-md-12 form-group"><label>Additional Details (Saved for Batch)</label><div class="read-value long">{{ $effective->details_extra ?: '—' }}</div></div>
<div class="col-md-12 form-group"><label>Banner</label>
@if($effective->banner_path)
<div><img src="{{ asset('storage/'.$effective->banner_path) }}" alt="Saved batch banner" style="max-width:100%;max-height:160px;object-fit:contain"></div>
@else
<div class="read-value">No banner uploaded</div>
@endif
</div>
</div>
<h4 class="section-title">Question Paper Settings</h4>
<div class="row">
@php
$values=[
['Questions Limit',$effective->question_limit],
['MCQ List Format',$fmt[$effective->mcq_format] ?? $effective->mcq_format],
['Status (Auto from Routine)',$info['status']],
['Exam Running Time',$effective->running_minutes.' minute(s)'],
['Exam Start Time (Auto)',$displayDate($start)],
['Exam Finished Time (Auto)',$displayDate($end)],
['Mark (For Correct Answer)',number_format((float)$effective->correct_mark,2)],
['Mark (Negative for Wrong Answer)',number_format((float)$effective->negative_mark,2)],
['How Many Times Can a Student Take Exam?',$effective->attempt_limit==='multiple'?'Multiple':$effective->attempt_limit],
['Show Random Questions?',$boolLabel($effective->random_questions)],
['Display Question?',$effective->question_display==='one'?'One by One':'All in One Paper'],
['Paid Exam?',$effective->paid_exam?'Yes':'Free'],
['Send Result to Account Email?',$boolLabel($effective->email_result)],
['Student Can View Result After Exam?',$boolLabel($effective->student_view_result)],
['Result Publish Date & Time (Auto)',$displayDate($end)],
];
@endphp
@foreach($values as $field)
<div class="col-md-6 form-group"><label>{{ $field[0] }}</label><div class="read-value">{{ $field[1] }}</div></div>
@endforeach
</div>
<h4 class="section-title">Message After Exam (Automatic Preview)</h4>
<div class="read-value long">আপনার উত্তরপত্রটি সঠিকভাবে জমা হয়েছে।
রেজাল্ট প্রকাশ করা হবে: {{ $displayDate($end) }}
@if($nextName)
পরবর্তী এক্সাম নং: {{ $nextName }}
পরবর্তী এক্সামের তারিখ: {{ $displayDate($nextAt) }}
@endif
ধন্যবাদ।</div>
<p class="text-muted" style="margin-top:15px">This is an Admin settings preview, not the student paper. Actual question totals and student timing/result/email behavior will be integrated when those modules are implemented.</p>
<a class="btn btn-default" href="{{ route('admin.routine-auto-question.exam-details',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id]) }}"><i class="fa fa-arrow-left"></i> Back to Exam Details</a>
<a class="btn btn-primary pull-right" href="{{ route('admin.routine-auto-question.batch-settings-preview',['courseId'=>$course->id,'batchId'=>$batch->id,'exam'=>$exam->exam_id]) }}"><i class="fa fa-pencil"></i> Edit Batch Settings</a>
</div></div></section>
@endsection
