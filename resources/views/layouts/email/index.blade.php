@extends('dashboard')
@section('title', 'Email Management')
@section('content')
<section class="content-header">
    <h1>Email Management <small>Batch-wise Email Management</small></h1>
    <ol class="breadcrumb"><li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li><li class="active">Email</li></ol>
</section>
<section class="content">
    @if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @php $today = \Carbon\Carbon::today('Asia/Dhaka')->toDateString(); @endphp
    <div class="box box-primary email-batch-table-box">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-users text-primary"></i> Batch List</h3><div class="box-tools"><span class="label label-primary">{{ $batches->count() }} Batches</span></div></div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-hover email-batch-table">
                <thead><tr><th class="text-center email-sl">SL</th><th>Batch Name</th><th>Course / Category</th><th>Start Date</th><th>End Date</th><th>Batch Status</th><th>Email Status</th><th class="text-center email-action-col">Actions</th></tr></thead>
                <tbody>
                @forelse($batches as $batch)
                    @php
                        $isBatchSource = ($batch->email_source_type ?? 'batch_list') === 'batch_list';
                        $withinDates = $batch->start_at && $batch->end_at && \Carbon\Carbon::parse($batch->start_at)->toDateString() <= $today && \Carbon\Carbon::parse($batch->end_at)->toDateString() >= $today;
                        $isAvailable = $isBatchSource && $batch->status === 'Active' && $withinDates;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="email-batch-name"><span class="email-batch-name-icon"><i class="fa {{ $isBatchSource ? 'fa-users' : 'fa-calendar' }}"></i></span><strong>{{ $batch->name }}</strong></div>
                            @if(!$isBatchSource && !empty($batch->email_routines))
                                <div class="email-routine-list">
                                    @foreach($batch->email_routines as $routine)
                                        <div class="email-routine-item">
                                            <strong><i class="fa fa-list-alt"></i> {{ $routine->name }}</strong>
                                            <span>{{ $routine->exam_count }} Exam</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($isBatchSource)
                                <span class="email-course-tag"><i class="fa fa-book"></i> {{ optional($batch->course)->name ?: 'Course not linked' }}</span>
                            @else
                                <span class="email-course-tag"><i class="fa fa-folder"></i> {{ $batch->email_category_name ?? 'Exam Section' }}</span>
                                @if(!empty($batch->email_subcategory_name))
                                    <br><small class="text-muted">↳ {{ $batch->email_subcategory_name }}</small>
                                @endif
                                @if(!empty($batch->email_routines))
                                    @foreach($batch->email_routines as $routine)
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
                                <span class="email-status email-status-active">● Active</span>
                            @elseif($batch->status !== 'Active')
                                <span class="email-status email-status-inactive">● Inactive</span>
                            @else
                                <span class="email-status email-status-expired">● Expired / Not Started</span>
                            @endif
                        </td>
                        <td>
                            @if(!$isBatchSource)
                                <div class="email-state-line"><strong>Routine:</strong> <span class="email-mini-status scheduled">{{ $batch->email_routine_count ?? 0 }} Published</span></div>
                                <div class="email-state-line"><strong>Exam:</strong> <span class="email-mini-status partial">{{ $batch->email_exam_count ?? 0 }} Listed</span></div>
                            @else
                            <div class="email-state-line"><strong>Exam Alert:</strong> @if($batch->exam_alert_total && $batch->exam_alert_scheduled === $batch->exam_alert_total)<span class="email-mini-status scheduled">All Scheduled</span>@elseif($batch->exam_alert_total)<span class="email-mini-status partial">{{ $batch->exam_alert_scheduled }}/{{ $batch->exam_alert_total }} Scheduled</span>@else<span class="email-mini-status none">Not Created</span>@endif</div>
                            <div class="email-state-line"><strong>Exam Link:</strong> @if($batch->exam_link_total && $batch->exam_link_scheduled === $batch->exam_link_total)<span class="email-mini-status scheduled">All Scheduled</span>@elseif($batch->exam_link_total)<span class="email-mini-status partial">{{ $batch->exam_link_scheduled }}/{{ $batch->exam_link_total }} Scheduled</span>@else<span class="email-mini-status none">Not Created</span>@endif</div>
                            @endif
                        </td>
                        <td class="text-center email-actions">
                            @if($isBatchSource)
                                @if($isAvailable)
                                    <a class="btn btn-success btn-xs email-add-button" href="{{ route('email.create', ['batch_id' => $batch->id, 'source_type' => 'batch_list']) }}"><i class="fa fa-plus-circle"></i> Add Email</a>
                                @else
                                    <button class="btn btn-default btn-xs email-add-button" type="button" disabled title="শুধুমাত্র মেয়াদ থাকা Active Batch-এ Email যোগ করা যাবে"><i class="fa fa-plus-circle"></i> Add Email</button>
                                @endif
                                <a class="btn btn-info btn-xs" href="{{ route('email.batch.list', [$batch->id, 'link']) }}"><i class="fa fa-list"></i> View Exam Link Email</a>
                                <a class="btn btn-primary btn-xs" href="{{ route('email.batch.list', [$batch->id, 'alert']) }}"><i class="fa fa-list"></i> View Exam Alert Email</a>
                            @else
                                <a class="btn btn-success btn-xs email-add-button" href="{{ route('email.create', ['source_type' => 'exam_section', 'source_id' => $batch->email_saved_id ?? 0]) }}"><i class="fa fa-plus-circle"></i> Add Email</a>
                                <a class="btn btn-info btn-xs" href="{{ route('email.batch.list', [$batch->email_link_batch_id ?? ($batch->email_saved_id ?? 0), 'link']) }}"><i class="fa fa-list"></i> View Exam Link Email</a>
                                <a class="btn btn-primary btn-xs" href="{{ route('email.batch.list', [$batch->email_link_batch_id ?? ($batch->email_saved_id ?? 0), 'alert']) }}"><i class="fa fa-list"></i> View Exam Alert Email</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center email-empty"><i class="fa fa-inbox"></i><div>আপনি এখনো কোনো Batch / Category / Subcategory Create করেননি, অথবা সেগুলোর মেয়াদ/Status আর Active নেই।</div></td></tr>
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
.email-batch-table-box{border:1px solid #dce4ed;border-top:3px solid #1685c5;border-radius:4px;box-shadow:0 2px 8px rgba(25,49,78,.05)}.email-batch-table-box>.box-header{padding:14px 16px;background:#fff}.email-batch-table-box .box-title{font-size:16px;font-weight:700;color:#263d58}.email-batch-table{margin-bottom:0;background:#fff}.email-batch-table thead th{background:#f3f6fa;color:#344a64;font-size:13px;font-weight:700;vertical-align:middle;padding:12px 9px;white-space:nowrap;border-color:#e3e9f0}.email-batch-table tbody td{padding:10px 9px;vertical-align:middle;color:#40536b;font-size:12px;border-color:#edf0f4}.email-batch-table tbody tr:hover{background:#f8fbff}.email-sl{width:48px}.email-batch-name{display:flex;align-items:center;gap:9px;min-width:190px}.email-batch-name strong{font-size:13px;color:#203955}.email-batch-name-icon{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 34px;border-radius:8px;background:#eaf2ff;color:#2478c8;font-size:15px}.email-course-tag{display:inline-block;padding:6px 9px;background:#eaf4ff;color:#28649b;border-radius:14px;white-space:nowrap}.email-status{display:inline-block;padding:6px 10px;border-radius:15px;font-size:11px;font-weight:700;white-space:nowrap}.email-status-active{background:#e4f6ec;color:#12814e}.email-status-inactive{background:#f0f2f5;color:#687789}.email-status-expired{background:#fff2e4;color:#b66a12}.email-state-line{white-space:nowrap;margin:3px 0}.email-state-line strong{display:inline-block;min-width:82px;color:#52657c}.email-mini-status{display:inline-block;padding:4px 7px;border-radius:12px;font-size:10px;font-weight:700}.email-mini-status.scheduled{background:#e4f6ec;color:#12814e}.email-mini-status.partial{background:#fff2e4;color:#ad6815}.email-mini-status.none{background:#f0f2f5;color:#738196}.email-actions{min-width:180px}.email-actions .btn{margin:2px 0;display:block;width:100%;font-weight:600}.email-actions .email-add-button{margin-bottom:5px}.email-routine-list{margin:7px 0 0 43px;max-width:430px}.email-routine-item{display:flex;justify-content:space-between;gap:10px;padding:4px 7px;margin:3px 0;background:#f7faff;border:1px solid #e5edf7;border-radius:4px;font-size:11px}.email-routine-item strong{color:#31516f;font-weight:600}.email-routine-item span{white-space:nowrap;color:#16804d;font-weight:600}.email-empty{padding:40px!important;color:#7d8da1!important}.email-empty>i{display:block;font-size:28px;margin-bottom:8px}@media(max-width:1100px){.email-batch-table{min-width:1120px}}
</style>
@endsection
