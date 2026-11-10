@extends('dashboard')
@section('title', 'Select Routine Exams for SMS')
@section('content')
<section class="content-header">
    <h1>Routine Exam নির্বাচন করুন</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('sms.index') }}"><i class="fa fa-dashboard"></i> SMS</a></li>
        <li class="active">Select Routine Exams</li>
    </ol>
</section>

<section class="content">
    <style>
        .sms-exam-select-shell{background:#fff;border:1px solid #dce5ef;border-top:3px solid #2878c7;border-radius:7px;box-shadow:0 3px 12px rgba(20,45,75,.05)}
        .sms-exam-select-head{padding:16px 19px;border-bottom:1px solid #edf1f5;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
        .sms-exam-select-body{padding:17px 19px}
        .sms-exam-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:12px 14px;margin-bottom:18px;background:#f5f8fc;border:1px solid #e1e9f2;border-radius:7px}
        .sms-exam-toolbar .count{margin-left:auto;color:#52677f;font-weight:600}
        .sms-routine-group{border:1px solid #dce5ef;border-radius:7px;margin-bottom:16px;overflow:hidden}
        .sms-routine-group-title{padding:12px 15px;background:#f3f7fc;border-bottom:1px solid #e2eaf3;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
        .sms-routine-group-title strong{font-size:15px;color:#263e58}
        .sms-routine-group-title small{color:#74869b}
        .sms-exam-grid-head,.sms-exam-card{display:grid;grid-template-columns:46px minmax(150px,1.1fr) minmax(190px,1.2fr) minmax(110px,.65fr);align-items:center;gap:12px}
        .sms-exam-grid-head{padding:10px 15px;background:#fafbfd;color:#64758a;font-weight:700;font-size:12px;border-bottom:1px solid #edf1f5}
        .sms-exam-card{padding:13px 15px;border-bottom:1px solid #edf1f5;cursor:pointer;transition:background .15s}
        .sms-exam-card:last-child{border-bottom:0}
        .sms-exam-card:hover{background:#f8fbff}
        .sms-exam-card.selected{background:#f0f7ff}
        .sms-exam-card input{width:17px;height:17px;margin:0 auto;cursor:pointer}
        .sms-exam-no{font-weight:600;color:#263e58}
        .sms-exam-date{color:#465b73;font-size:13px}
        .sms-exam-status{justify-self:start;padding:4px 9px;border-radius:20px;font-size:12px;font-weight:700}
        .sms-status-upcoming{background:#e8f2ff;color:#2469b1}
        .sms-status-running{background:#fff3d8;color:#9a6900}
        .sms-status-completed{background:#e8f7ef;color:#198653}
        .sms-exam-empty{padding:16px;color:#8392a4;text-align:center}
        @media(max-width:767px){
            .sms-exam-grid-head{display:none}
            .sms-exam-card{grid-template-columns:30px minmax(0,1fr);gap:8px;padding:12px}
            .sms-exam-card input{grid-row:1 / span 3}
            .sms-exam-date,.sms-exam-status{grid-column:2}
            .sms-exam-status{margin-top:2px}
            .sms-exam-toolbar .count{margin-left:0;width:100%}
        }
    </style>

    <form method="POST" action="{{ route('sms.routines.done') }}" id="smsExamSelectionForm">
        @csrf
        @foreach($batchIds as $batchId)
            <input type="hidden" name="batch_ids[]" value="{{ $batchId }}">
        @endforeach

        <div class="sms-exam-select-shell">
            <div class="sms-exam-select-head">
                <div>
                    <h3 style="margin:0 0 5px;font-size:19px;color:#263e58">Routine-এর অধীনে Exam List</h3>
                    <small class="text-muted">প্রয়োজনীয় Exam নির্বাচন করে Done চাপুন।</small>
                </div>
                <a href="{{ route('sms.create') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back to SMS
                </a>
            </div>

            <div class="sms-exam-select-body">
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="sms-exam-toolbar">
                    <label style="display:flex;align-items:center;gap:8px;margin:0;font-weight:700;cursor:pointer">
                        <input type="checkbox" id="selectAllExams" style="width:18px;height:18px;margin:0" onchange="toggleAllExams(this.checked)">
                        Select All
                    </label>
                    <button type="button" class="btn btn-default btn-sm" onclick="clearAllExams()">সব বাতিল</button>
                    <span class="count" id="examSelectedCount">0টি Exam নির্বাচিত</span>
                </div>

                <div id="smsExamGroups">
                    @forelse($examGroups as $routineId => $exams)
                        @php $firstExam = $exams->first(); @endphp
                        <div class="sms-routine-group">
                            <div class="sms-routine-group-title">
                                <strong><i class="fa fa-book text-primary"></i> {{ $firstExam->routine_name }}</strong>
                                <small>Batch: {{ $firstExam->batch_name }}</small>
                            </div>
                            <div class="sms-exam-grid-head">
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
                                        $statusClass = 'sms-status-upcoming';
                                    } elseif ($end && $now->lte($end)) {
                                        $statusText = 'Ongoing';
                                        $statusClass = 'sms-status-running';
                                    } else {
                                        $statusText = 'Completed';
                                        $statusClass = 'sms-status-completed';
                                    }
                                @endphp
                                <label class="sms-exam-card">
                                    <input type="checkbox" name="exam_ids[]" value="{{ $exam->exam_id }}"
                                           {{ in_array($exam->exam_id, $selectedExamIds) ? 'checked' : '' }}
                                           onchange="updateExamSelection()">
                                    <span class="sms-exam-no">{{ $exam->paper_name }}</span>
                                    <span class="sms-exam-date">
                                        {{ $start ? $start->timezone('Asia/Dhaka')->format('d-m-Y h:i A') : 'তারিখ নির্ধারিত নয়' }}
                                    </span>
                                    <span class="sms-exam-status {{ $statusClass }}">{{ $statusText }}</span>
                                </label>
                            @endforeach
                        </div>
                    @empty
                        <div class="sms-exam-empty">
                            <i class="fa fa-inbox fa-2x"></i>
                            <p style="margin:8px 0 0">নির্বাচিত Batch-এর Routine-এ এখনো কোনো Exam যুক্ত করা হয়নি।</p>
                        </div>
                    @endforelse
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px">
                    <a href="{{ route('sms.create') }}" class="btn btn-default">Cancel</a>
                    <button type="submit" class="btn btn-success" id="doneRoutineBtn" {{ $examGroups->flatten()->isEmpty() ? 'disabled' : '' }}>
                        <i class="fa fa-check"></i> Done — SMS পেজে ফিরুন
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<script>
function getExamCheckboxes() {
    return Array.from(document.querySelectorAll('#smsExamGroups input[name="exam_ids[]"]'));
}
function updateExamSelection() {
    var boxes = getExamCheckboxes();
    var checked = boxes.filter(function(box) { return box.checked; }).length;
    var all = document.getElementById('selectAllExams');
    all.checked = boxes.length > 0 && checked === boxes.length;
    all.indeterminate = checked > 0 && checked < boxes.length;
    document.getElementById('examSelectedCount').textContent = checked + 'টি Exam নির্বাচিত';
    boxes.forEach(function(box) {
        var row = box.closest('.sms-exam-card');
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
