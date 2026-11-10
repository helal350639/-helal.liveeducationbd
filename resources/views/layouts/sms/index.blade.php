@extends('dashboard')
@section('title', 'SMS Management')
@section('content')
<section class="content-header">
    <h1>SMS Management <small>Batch-wise SMS Management</small></h1>
    <ol class="breadcrumb"><li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li><li class="active">SMS</li></ol>
</section>
<section class="content">
    @if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @php $today = \Carbon\Carbon::today('Asia/Dhaka')->toDateString(); @endphp
    <div class="box box-primary sms-batch-table-box">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-users text-primary"></i> Batch List</h3><div class="box-tools"><span class="label label-primary">{{ $batches->count() }} Batches</span></div></div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-hover sms-batch-table">
                <thead><tr><th class="text-center sms-sl">SL</th><th>Batch Name</th><th>Course / Category</th><th>Start Date</th><th>End Date</th><th>Batch Status</th><th>SMS Status</th><th class="text-center sms-action-col">Actions</th></tr></thead>
                <tbody>
                @forelse($batches as $batch)
                    @php
                        $isBatchSource = ($batch->sms_source_type ?? 'batch_list') === 'batch_list';
                        $withinDates = $batch->start_at && $batch->end_at && \Carbon\Carbon::parse($batch->start_at)->toDateString() <= $today && \Carbon\Carbon::parse($batch->end_at)->toDateString() >= $today;
                        $isAvailable = $isBatchSource && $batch->status === 'Active' && $withinDates;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="sms-batch-name"><span class="sms-batch-name-icon"><i class="fa {{ $isBatchSource ? 'fa-users' : 'fa-calendar' }}"></i></span><strong>{{ $batch->name }}</strong></div>
                            @if(!$isBatchSource && !empty($batch->sms_routines))
                                <div class="sms-routine-list">
                                    @foreach($batch->sms_routines as $routine)
                                        <div class="sms-routine-item">
                                            <strong><i class="fa fa-list-alt"></i> {{ $routine->name }}</strong>
                                            <span>{{ $routine->exam_count }} Exam</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($isBatchSource)
                                <span class="sms-course-tag"><i class="fa fa-book"></i> {{ optional($batch->course)->name ?: 'Course not linked' }}</span>
                            @else
                                <span class="sms-course-tag"><i class="fa fa-folder"></i> {{ $batch->sms_category_name ?? 'Exam Section' }}</span>
                                @if(!empty($batch->sms_subcategory_name))
                                    <br><small class="text-muted">↳ {{ $batch->sms_subcategory_name }}</small>
                                @endif
                                @if(!empty($batch->sms_routines))
                                    @foreach($batch->sms_routines as $routine)
                                        @if($routine->course_name || $routine->batch_name)
                                            <br><small class="text-muted">
                                                @if($routine->course_name)Course: {{ $routine->course_name }}@endif
                                                @if($routine->batch_name) · Batch: {{ $routine->batch_name }}@endif
                                            </small>
                                        @endif
                                    @endforeach
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($batch->start_at)
                                {{ \Carbon\Carbon::parse($batch->start_at)->format($isBatchSource ? 'd M Y' : 'd M Y, h:i A') }}
                            @else — @endif
                        </td>
                        <td>
                            @if($batch->end_at)
                                {{ \Carbon\Carbon::parse($batch->end_at)->format($isBatchSource ? 'd M Y' : 'd M Y, h:i A') }}
                            @else — @endif
                        </td>
                        <td class="text-center">
                            @if(!$isBatchSource || $isAvailable)
                                <span class="sms-status sms-status-active">● Active</span>
                            @elseif($batch->status !== 'Active')
                                <span class="sms-status sms-status-inactive">● Inactive</span>
                            @else
                                <span class="sms-status sms-status-expired">● Expired / Not Started</span>
                            @endif
                        </td>
                        <td>
                            @if(!$isBatchSource)
                                <div class="sms-state-line"><strong>Routine:</strong> <span class="sms-mini-status scheduled">{{ $batch->sms_routine_count ?? 0 }} Published</span></div>
                                <div class="sms-state-line"><strong>Exam:</strong> <span class="sms-mini-status partial">{{ $batch->sms_exam_count ?? 0 }} Listed</span></div>
                            @else
                            <div class="sms-state-line"><strong>Exam Alert:</strong> @if($batch->exam_alert_total && $batch->exam_alert_scheduled === $batch->exam_alert_total)<span class="sms-mini-status scheduled">All Scheduled</span>@elseif($batch->exam_alert_total)<span class="sms-mini-status partial">{{ $batch->exam_alert_scheduled }}/{{ $batch->exam_alert_total }} Scheduled</span>@else<span class="sms-mini-status none">Not Created</span>@endif</div>
                            <div class="sms-state-line"><strong>Exam Link:</strong> @if($batch->exam_link_total && $batch->exam_link_scheduled === $batch->exam_link_total)<span class="sms-mini-status scheduled">All Scheduled</span>@elseif($batch->exam_link_total)<span class="sms-mini-status partial">{{ $batch->exam_link_scheduled }}/{{ $batch->exam_link_total }} Scheduled</span>@else<span class="sms-mini-status none">Not Created</span>@endif</div>
                            @endif
                        </td>
                        <td class="text-center sms-actions">
                            @if($isBatchSource)
                                @if($isAvailable)
                                    <a class="btn btn-success btn-xs sms-add-button" href="{{ route('sms.create', ['batch_id' => $batch->id, 'source_type' => 'batch_list']) }}"><i class="fa fa-plus-circle"></i> Add SMS</a>
                                @else
                                    <button class="btn btn-default btn-xs sms-add-button" type="button" disabled title="শুধুমাত্র মেয়াদ থাকা Active Batch-এ SMS যোগ করা যাবে"><i class="fa fa-plus-circle"></i> Add SMS</button>
                                @endif
                                <a class="btn btn-info btn-xs" href="{{ route('sms.batch.list', [$batch->id, 'link']) }}"><i class="fa fa-list"></i> View Exam Link SMS</a>
                                <a class="btn btn-primary btn-xs" href="{{ route('sms.batch.list', [$batch->id, 'alert']) }}"><i class="fa fa-list"></i> View Exam Alert SMS</a>
                            @else
                                <a class="btn btn-success btn-xs sms-add-button" href="{{ route('sms.create', ['source_type' => 'exam_section', 'source_id' => $batch->sms_saved_id ?? 0]) }}"><i class="fa fa-plus-circle"></i> Add SMS</a>
                                <a class="btn btn-info btn-xs" href="{{ route('sms.batch.list', [$batch->sms_link_batch_id ?? ($batch->sms_saved_id ?? 0), 'link']) }}"><i class="fa fa-list"></i> View Exam Link SMS</a>
                                <a class="btn btn-primary btn-xs" href="{{ route('sms.batch.list', [$batch->sms_link_batch_id ?? ($batch->sms_saved_id ?? 0), 'alert']) }}"><i class="fa fa-list"></i> View Exam Alert SMS</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center sms-empty"><i class="fa fa-inbox"></i><div>আপনি এখনো কোনো Batch / Category / Subcategory Create করেননি, অথবা সেগুলোর মেয়াদ/Status আর Active নেই।</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="box-footer"><small class="text-muted"><i class="fa fa-info-circle"></i> শুধু Create Batch Name থেকে সংরক্ষিত Active Batch / Category / Subcategory দেখানো হয়। Batch-এর মেয়াদ শেষ হলে সেটি তালিকা থেকে স্বয়ংক্রিয়ভাবে বাদ যাবে।</small></div>
    </div>
</section>
@endsection
@section('stylesheets')
<style>
.sms-batch-table-box{border:1px solid #dce4ed;border-top:3px solid #1685c5;border-radius:4px;box-shadow:0 2px 8px rgba(25,49,78,.05)}.sms-batch-table-box>.box-header{padding:14px 16px;background:#fff}.sms-batch-table-box .box-title{font-size:16px;font-weight:700;color:#263d58}.sms-batch-table{margin-bottom:0;background:#fff}.sms-batch-table thead th{background:#f3f6fa;color:#344a64;font-size:13px;font-weight:700;vertical-align:middle;padding:12px 9px;white-space:nowrap;border-color:#e3e9f0}.sms-batch-table tbody td{padding:10px 9px;vertical-align:middle;color:#40536b;font-size:12px;border-color:#edf0f4}.sms-batch-table tbody tr:hover{background:#f8fbff}.sms-sl{width:48px}.sms-batch-name{display:flex;align-items:center;gap:9px;min-width:190px}.sms-batch-name strong{font-size:13px;color:#203955}.sms-batch-name-icon{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 34px;border-radius:8px;background:#eaf2ff;color:#2478c8;font-size:15px}.sms-course-tag{display:inline-block;padding:6px 9px;background:#eaf4ff;color:#28649b;border-radius:14px;white-space:nowrap}.sms-status{display:inline-block;padding:6px 10px;border-radius:15px;font-size:11px;font-weight:700;white-space:nowrap}.sms-status-active{background:#e4f6ec;color:#12814e}.sms-status-inactive{background:#f0f2f5;color:#687789}.sms-status-expired{background:#fff2e4;color:#b66a12}.sms-state-line{white-space:nowrap;margin:3px 0}.sms-state-line strong{display:inline-block;min-width:82px;color:#52657c}.sms-mini-status{display:inline-block;padding:4px 7px;border-radius:12px;font-size:10px;font-weight:700}.sms-mini-status.scheduled{background:#e4f6ec;color:#12814e}.sms-mini-status.partial{background:#fff2e4;color:#ad6815}.sms-mini-status.none{background:#f0f2f5;color:#738196}.sms-actions{min-width:180px}.sms-actions .btn{margin:2px 0;display:block;width:100%;font-weight:600}.sms-actions .sms-add-button{margin-bottom:5px}.sms-routine-list{margin:7px 0 0 43px;max-width:430px}.sms-routine-item{display:flex;justify-content:space-between;gap:10px;padding:4px 7px;margin:3px 0;background:#f7faff;border:1px solid #e5edf7;border-radius:4px;font-size:11px}.sms-routine-item strong{color:#31516f;font-weight:600}.sms-routine-item span{white-space:nowrap;color:#16804d;font-weight:600}.sms-empty{padding:40px!important;color:#7d8da1!important}.sms-empty>i{display:block;font-size:28px;margin-bottom:8px}@media(max-width:1100px){.sms-batch-table{min-width:1120px}}
</style>
@endsection
