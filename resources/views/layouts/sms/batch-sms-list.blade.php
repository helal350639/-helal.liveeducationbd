@extends('dashboard')
@section('title', $smsType . ' — ' . $batch->name)
@section('content')
<section class="content-header">
    <h1>{{ $smsType }} <small>Batch SMS Details</small></h1>
    <ol class="breadcrumb"><li><a href="#">Dashboard</a></li><li><a href="{{ route('sms.index') }}">SMS</a></li><li class="active">{{ $smsType }}</li></ol>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-users"></i> {{ $batch->name }} <small>({{ optional($batch->course)->name ?: 'Course / Category not linked' }})</small></h3>
            <div class="box-tools"><a href="{{ route('sms.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to Batch List</a></div>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead><tr><th class="text-center">SL</th><th>SMS Title</th><th>Exam No / Details</th><th>Exam Date & Time</th><th>SMS Status</th><th>Scheduled / Created At</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                @forelse($smsList as $sms)
                    @php
                        $examIds = json_decode($sms->exam_ids, true); $examIds = is_array($examIds) ? $examIds : [];
                        $exam = count($examIds) ? \Illuminate\Support\Facades\DB::table('routine_item_rows')->where('id', reset($examIds))->first() : null;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td><strong>{{ $sms->title ?: $smsType }}</strong><br><small class="text-muted">{{ $smsType }}</small></td>
                        <td>{{ $exam ? $exam->paper_name : \Illuminate\Support\Str::limit(strip_tags($sms->details), 90) }}</td>
                        <td>{{ $exam && $exam->exam_at ? \Carbon\Carbon::parse($exam->exam_at)->format('d-m-Y h:i A') : '—' }}</td>
                        <td>@if($sms->status === 'Scheduled')<span class="label label-success">Scheduled</span>@elseif($sms->status === 'Draft')<span class="label label-default">Draft</span>@else<span class="label label-info">{{ $sms->status ?: 'Saved' }}</span>@endif</td>
                        <td>{{ $sms->datetime ? \Carbon\Carbon::parse($sms->datetime)->format('d M Y h:i A') : ($sms->created_at ? \Carbon\Carbon::parse($sms->created_at)->format('d M Y h:i A') : '—') }}</td>
                        <td class="text-center">
                            <a href="{{ route('sms.show', $sms->id) }}" class="btn btn-info btn-xs" title="View"><i class="fa fa-eye"></i></a>
                            <a href="{{ route('sms.edit', $sms->id) }}" class="btn btn-warning btn-xs" title="Edit"><i class="fa fa-pencil"></i></a>
                            <form action="{{ route('sms.destroy', $sms->id) }}" method="POST" style="display:inline" onsubmit="return confirm('এই SMS record মুছে ফেলতে চান?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs" type="submit" title="Delete"><i class="fa fa-trash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted" style="padding:35px">এই Batch-এর জন্য কোনো {{ $smsType }} SMS পাওয়া যায়নি।</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
