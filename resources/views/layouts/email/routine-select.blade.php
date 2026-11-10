@extends('dashboard')
@section('title', 'Select Routine Exams for Email')
@section('content')
<section class="content-header">
    <h1>Routine Exam নির্বাচন করুন</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('email.index') }}"><i class="fa fa-dashboard"></i> Email</a></li>
        <li class="active">Select Routine Exams</li>
    </ol>
</section>

<section class="content">
    <style>
        .email-exam-select-shell{background:#fff;border:1px solid #dce5ef;border-top:3px solid #2878c7;border-radius:7px;box-shadow:0 3px 12px rgba(20,45,75,.05)}
        .email-exam-select-head{padding:16px 19px;border-bottom:1px solid #edf1f5;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
        .email-exam-select-body{padding:17px 19px}
        .email-exam-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:12px 14px;margin-bottom:18px;background:#f5f8fc;border:1px solid #e1e9f2;border-radius:7px}
        .email-exam-toolbar .count{margin-left:auto;color:#52677f;font-weight:600}
        .email-routine-group{border:1px solid #dce5ef;border-radius:7px;margin-bottom:16px;overflow:hidden}
        .email-routine-group-title{padding:12px 15px;background:#f3f7fc;border-bottom:1px solid #e2eaf3;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
        .email-routine-group-title strong{font-size:15px;color:#263e58}
        .email-routine-group-title small{color:#74869b}
        .email-exam-grid-head,.email-exam-card{display:grid;grid-template-columns:46px minmax(150px,1.1fr) minmax(190px,1.2fr) minmax(110px,.65fr);align-items:center;gap:12px}
        .email-exam-grid-head{padding:10px 15px;background:#fafbfd;color:#64758a;font-weight:700;font-size:12px;border-bottom:1px solid #edf1f5}
        .email-exam-card{padding:13px 15px;border-bottom:1px solid #edf1f5;cursor:pointer;transition:background .15s}
        .email-exam-card:last-child{border-bottom:0}
        .email-exam-card:hover{background:#f8fbff}
        .email-exam-card.selected{background:#f0f7ff}
        .email-exam-card input{width:17px;height:17px;margin:0 auto;cursor:pointer}
        .email-exam-no{font-weight:600;color:#263e58}
        .email-exam-date{color:#465b73;font-size:13px}
        .email-exam-status{justify-self:start;padding:4px 9px;border-radius:20px;font-size:12px;font-weight:700}
        .email-status-upcoming{background:#e8f2ff;color:#2469b1}
        .email-status-running{background:#fff3d8;color:#9a6900}
        .email-status-completed{background:#e8f7ef;color:#198653}
        .email-exam-empty{padding:16px;color:#8392a4;text-align:center}
        @media(max-width:767px){
            .email-exam-grid-head{display:none}
            .email-exam-card{grid-template-columns:30px minmax(0,1fr);gap:8px;padding:12px}
            .email-exam-card input{grid-row:1 / span 3}
            .email-exam-date,.email-exam-status{grid-column:2}
            .email-exam-status{margin-top:2px}
            .email-exam-toolbar .count{margin-left:0;width:100%}
        }
    </style>

    <form method="POST" action="{{ route('email.routines.done') }}" id="emailExamSelectionForm">
        @csrf
        @foreach($batchIds as $batchId)
            <input type="hidden" name="batch_ids[]" value="{{ $batchId }}">
        @endforeach

        <div class="email-exam-select-shell">
            <div class="email-exam-select-head">
                <div>
                    <h3 style="margin:0 0 5px;font-size:19px;color:#263e58">Routine-এর অধীনে Exam List</h3>
                    <small class="text-muted">প্রয়োজনীয় Exam নির্বাচন করে Done চাপুন।</small>
                </div>
                <a href="{{ route('email.create') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back to Email
                </a>
            </div>

            <div class="email-exam-select-body">
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="email-exam-toolbar">
                    <label style="display:flex;align-items:center;gap:8px;margin:0;font-weight:700;cursor:pointer">
                        <input type="checkbox" id="selectAllExams" style="width:18px;height:18px;margin:0" onchange="toggleAllExams(this.checked)">
                        Select All
                    </label>
                    <button type="button" class="btn btn-default btn-sm" onclick="clearAllExams()">সব বাতিল</button>
                    <span class="count" id="examSelectedCount">0টি Exam নির্বাচিত</span>
                </div>

                <div id="emailExamGroups">
                    @forelse($examGroups as $routineId => $exams)
                        @php $firstExam = $exams->first(); @endphp
                        <div class="email-routine-group">
                            <div class="email-routine-group-title">
                                <strong><i class="fa fa-book text-primary"></i> {{ $firstExam->routine_name }}</strong>
                                <small>Batch: {{ $firstExam->batch_name }}</small>
                            </div>
                            <div class="email-exam-grid-head">
                                <span>Select</span>
                                <span>Exam No</span>
                                <span>Exam Date &amp; Time</span>
                                <span>Status</span>
                            </div>
                            @foreach($exams as $exam)
                                @php
                                    $start = $exam->exam_at ? \Carbon\Carbon::parse($exam->exam_at) : null;
                                    $end = $exam->exam_end_at ? \Carbon\Carbon::parse($exam->exam_end_at) : null;
                                    $now = now('Asia/Dhaka');
                                    if (!$start || $now->lt($start)) {
                                        $statusText = 'Upcoming';
                                        $statusClass = 'email-status-upcoming';
                                    } elseif ($end && $now->lte($end)) {
                                        $statusText = 'Ongoing';
                                        $statusClass = 'email-status-running';
                                    } else {
                                        $statusText = 'Completed';
                                        $statusClass = 'email-status-completed';
                                    }
                                @endphp
                                <label class="email-exam-card">
                                    <input type="checkbox" name="exam_ids[]" value="{{ $exam->exam_id }}"
                                           {{ in_array($exam->exam_id, $selectedExamIds) ? 'checked' : '' }}
                                           onchange="updateExamSelection()">
                                    <span class="email-exam-no">{{ $exam->paper_name }}</span>
                                    <span class="email-exam-date">
                                        {{ $start ? $start->timezone('Asia/Dhaka')->format('d-m-Y h:i A') : 'তারিখ নির্ধারিত নয়' }}
                                    </span>
                                    <span class="email-exam-status {{ $statusClass }}">{{ $statusText }}</span>
                                </label>
                            @endforeach
                        </div>
                    @empty
                        <div class="email-exam-empty">
                            <i class="fa fa-inbox fa-2x"></i>
                            <p style="margin:8px 0 0">নির্বাচিত Batch-এর Routine-এ এখনো কোনো Exam যুক্ত করা হয়নি।</p>
                        </div>
                    @endforelse
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px">
                    <a href="{{ route('email.create') }}" class="btn btn-default">Cancel</a>
                    <button type="submit" class="btn btn-success" id="doneRoutineBtn" {{ $examGroups->flatten()->isEmpty() ? 'disabled' : '' }}>
                        <i class="fa fa-check"></i> Done — Email পেজে ফিরুন
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<script>
function getExamCheckboxes() {
    return Array.from(document.querySelectorAll('#emailExamGroups input[name="exam_ids[]"]'));
}
function updateExamSelection() {
    var boxes = getExamCheckboxes();
    var checked = boxes.filter(function(box) { return box.checked; }).length;
    var all = document.getElementById('selectAllExams');
    all.checked = boxes.length > 0 && checked === boxes.length;
    all.indeterminate = checked > 0 && checked < boxes.length;
    document.getElementById('examSelectedCount').textContent = checked + 'টি Exam নির্বাচিত';
    boxes.forEach(function(box) {
        var row = box.closest('.email-exam-card');
        if (row) row.classList.toggle('selected', box.checked);
    });
    document.getElementById('doneRoutineBtn').disabled = checked === 0;
}
function toggleAllExams(checked) {
    getExamCheckboxes().forEach(function(box) { box.checked = checked; });
    updateExamSelection();
}
function clearAllExams() {
    toggleAllExams(false);
}
updateExamSelection();
</script>
@endsection
