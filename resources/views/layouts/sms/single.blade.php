@extends('dashboard')
@section('title', 'Single SMS')
@section('content')
<section class="content-header">
    <h1>Single SMS <small>Send SMS to selected students</small></h1>
    <ol class="breadcrumb"><li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li><li><a href="{{ route('sms.index') }}">SMS</a></li><li class="active">Single SMS</li></ol>
</section>
<section class="content">
    @if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>সমস্যা হয়েছে:</strong><ul style="margin-bottom:0;padding-left:20px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="box box-primary single-sms-box">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-users text-primary"></i> Main Database থেকে Student নির্বাচন</h3><span class="label label-primary pull-right">{{ number_format($students->total()) }} Students</span></div>
        <div class="box-body">
            <form method="GET" action="{{ route('sms.single.index') }}" class="row single-sms-filter">
                <div class="col-md-4 col-sm-6"><label>Search Student</label><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name / Student ID / Mobile / Email"></div>
                <div class="col-md-2 col-sm-6"><label>Course</label><select name="course_id" class="form-control"><option value="">All Courses</option>@foreach($courses as $course)<option value="{{ $course->id }}" {{ (string)request('course_id') === (string)$course->id ? 'selected' : '' }}>{{ $course->name }}</option>@endforeach</select></div>
                <div class="col-md-2 col-sm-6"><label>Batch</label><select name="batch_id" class="form-control"><option value="">All Batches</option>@foreach($batches as $batch)<option value="{{ $batch->id }}" {{ (string)request('batch_id') === (string)$batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>@endforeach</select></div>
                <div class="col-md-2 col-sm-6"><label>Status</label><select name="status" class="form-control"><option value="all">All Students</option><option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option><option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option></select></div>
                <div class="col-md-2 col-sm-12"><label>Per Page</label><div class="input-group"><select name="per_page" class="form-control"><option {{ request('per_page',25)==25?'selected':'' }}>25</option><option {{ request('per_page')==50?'selected':'' }}>50</option><option {{ request('per_page')==100?'selected':'' }}>100</option></select><span class="input-group-btn"><button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Filter</button></span></div></div>
            </form>
            <form method="POST" action="{{ route('sms.single.send') }}" id="singleSmsForm">
                @csrf
                <div class="single-sms-toolbar">
                    <label class="checkbox-inline"><input type="checkbox" id="selectAllStudents"> <strong>এই পৃষ্ঠার সব Student নির্বাচন</strong></label>
                    <span class="single-sms-selected">Selected: <strong id="selectedStudentCount">0</strong></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover single-sms-table">
                        <thead><tr><th class="text-center" style="width:48px">Select</th><th style="width:65px">SL</th><th>Student Name / ID</th><th>Mobile Number</th><th>Email</th><th>Course</th><th>Batch</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td class="text-center"><input type="checkbox" class="student-checkbox" name="student_ids[]" value="{{ $student->id }}" {{ in_array((string)$student->id, old('student_ids', [])) ? 'checked' : '' }}></td>
                                <td>{{ $students->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $student->name }}</strong><br><small class="text-muted">ID: {{ $student->reg_id ?: $student->id }}</small></td>
                                <td>{{ $student->contact ?: 'No mobile number' }}</td>
                                <td>{{ $student->email ?: '—' }}</td>
                                <td>@forelse($student->courses as $course)<span class="single-sms-tag">{{ $course->name }}</span>@empty — @endforelse</td>
                                <td>@forelse($student->batches as $batch)<span class="single-sms-tag">{{ $batch->name }}</span>@empty — @endforelse</td>
                                <td><span class="label {{ strtolower($student->status ?? '') === 'active' ? 'label-success' : 'label-default' }}">{{ $student->status ?: 'Unknown' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted" style="padding:30px">কোনো Student পাওয়া যায়নি। Search বা Filter পরিবর্তন করুন।</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="single-sms-pagination">{{ $students->links() }}</div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <label for="singleSmsMessage">SMS Message</label>
                        <textarea name="details" id="singleSmsMessage" class="form-control" rows="4" maxlength="1000" placeholder="এখানে আপনার SMS লিখুন..." required>{{ old('details') }}</textarea>
                        <div class="single-sms-message-meta"><span>Character: <strong id="singleSmsCharCount">0</strong>/1000</span><span>Estimated SMS Parts: <strong id="singleSmsPartCount">0</strong></span></div>
                        <p class="help-block"><i class="fa fa-info-circle"></i> শুধু উপরে Checkbox দিয়ে নির্বাচিত Student-দের Mobile Number-এ SMS পাঠানো হবে। যাদের বৈধ Mobile Number নেই, তাদের বাদ দেওয়া হবে।</p>
                    </div>
                </div>
                <div class="single-sms-submit"><button type="submit" class="btn btn-success btn-lg" id="sendSingleSmsBtn"><i class="fa fa-paper-plane"></i> Send SMS to Selected Students</button></div>
            </form>
        </div>
    </div>

    <div class="box box-default single-sms-box">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Recent Single SMS History</h3></div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead><tr><th>SL</th><th>Date & Time</th><th>Message</th><th>Recipients</th><th>Status</th></tr></thead>
                <tbody>@forelse($history as $sms)<tr><td>{{ $loop->iteration }}</td><td>{{ $sms->datetime ? \Carbon\Carbon::parse($sms->datetime)->format('d M Y, h:i A') : '—' }}</td><td>{{ \Illuminate\Support\Str::limit($sms->details, 90) }}</td><td>{{ is_array(json_decode($sms->student_id, true)) ? count(json_decode($sms->student_id, true)) : 0 }}</td><td><span class="label label-success">{{ $sms->status ?: 'Sent' }}</span></td></tr>@empty<tr><td colspan="5" class="text-center text-muted">এখনো কোনো Single SMS পাঠানো হয়নি।</td></tr>@endforelse</tbody>
            </table>
        </div>
    </div>
</section>
@endsection
@section('stylesheets')
<style>
.single-sms-box{border:1px solid #dce5ef;border-top:3px solid #2789c7;box-shadow:0 2px 8px rgba(30,55,85,.05)}.single-sms-filter{margin-bottom:18px;padding-bottom:15px;border-bottom:1px solid #edf1f5}.single-sms-filter label{display:block;font-size:12px;margin-bottom:6px;color:#344b66}.single-sms-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;margin-bottom:10px;background:#f4f8fc;border:1px solid #e1eaf3;border-radius:4px}.single-sms-selected{color:#245b8a}.single-sms-table thead th{background:#f1f5f9;color:#344a64;white-space:nowrap}.single-sms-table td{vertical-align:middle;font-size:12px}.single-sms-tag{display:inline-block;padding:3px 7px;margin:2px;background:#eaf4ff;color:#28649b;border-radius:12px}.single-sms-pagination{text-align:right}.single-sms-message-meta{display:flex;justify-content:space-between;color:#71839a;font-size:12px;margin-top:6px}.single-sms-submit{text-align:right;margin-top:12px}.single-sms-submit .btn{font-weight:700}.single-sms-filter .form-control{height:36px}@media(max-width:767px){.single-sms-toolbar{align-items:flex-start;flex-direction:column}.single-sms-submit .btn{width:100%}}
</style>
@endsection
@section('scripts')
<script>
(function(){
    var selectAll=document.getElementById('selectAllStudents');
    var boxes=Array.prototype.slice.call(document.querySelectorAll('.student-checkbox'));
    var count=document.getElementById('selectedStudentCount');
    var message=document.getElementById('singleSmsMessage');
    var charCount=document.getElementById('singleSmsCharCount');
    var partCount=document.getElementById('singleSmsPartCount');
    function updateSelected(){var selected=boxes.filter(function(box){return box.checked;}).length; if(count) count.textContent=selected; if(selectAll){selectAll.checked=boxes.length>0&&selected===boxes.length;selectAll.indeterminate=selected>0&&selected<boxes.length;}}
    if(selectAll){selectAll.addEventListener('change',function(){boxes.forEach(function(box){box.checked=selectAll.checked;});updateSelected();});}
    boxes.forEach(function(box){box.addEventListener('change',updateSelected);});
    function updateMessage(){if(!message)return;var value=message.value||'';var chars=value.length;var unicode=/[^\x00-\x7F]/.test(value);var parts=0;if(chars){if(unicode){parts=chars<=70?1:Math.ceil(chars/67);}else{parts=chars<=160?1:Math.ceil(chars/153);}}charCount.textContent=chars;partCount.textContent=parts;}
    if(message){message.addEventListener('input',updateMessage);updateMessage();}
    updateSelected();
    var form=document.getElementById('singleSmsForm');
    if(form){form.addEventListener('submit',function(event){if(!boxes.some(function(box){return box.checked;})){event.preventDefault();alert('SMS পাঠানোর জন্য অন্তত একজন Student নির্বাচন করুন।');return;}if(!message.value.trim()){event.preventDefault();alert('SMS Message লিখুন।');return;}if(!window.confirm('নির্বাচিত Student-দের কাছে SMS পাঠাতে চান?'))event.preventDefault();});}
})();
</script>
@endsection
