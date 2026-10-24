@extends('dashboard')
@section('title', 'Bulk MCQ CSV Import')
@section('content')
<section class="content-header"><h1>Bulk MCQ CSV Import</h1></section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Upload &amp; Preview</h3></div>
        <div class="box-body">
            <p>First select Course, Department, Subject, Chapter, Filters and Level on Add Question. Then upload a UTF-8 CSV file (maximum 2 MB, 500 questions) containing only Question, Options A-D, Correct Answer and Explanation.</p>
            <p><a class="btn btn-default" href="{{ route('admin.question-csv.template') }}"><i class="fa fa-download"></i> Download CSV Template</a>
            <a class="btn btn-info" href="{{ route('question.create') }}">Manual Add Question</a></p>
            @if($context)
            <div class="alert alert-info"><strong>Selected settings (applies to every CSV question):</strong>
               Course: {{ $context['course_ids'] }} | Department: {{ $context['department_ids'] }} |
               Subject: {{ $context['subject_ids'] ?: '—' }} | Chapter: {{ $context['chapter_ids'] ?: '—' }} |
               Filter / Level IDs: {{ $context['sub_filter_ids'] ?: '—' }} | Labels: {{ $context['labels'] ?: '—' }}
               <p style="margin-top:8px"><a href="{{ route('question.create') }}">Change selections on Add Question</a></p>
            </div>
            @else
            <div class="alert alert-warning">No settings selected. Go to <a href="{{ route('question.create') }}">Add Question</a>, select Course/Department/Filters/Level and click <strong>Bulk CSV Upload</strong>.</div>
            @endif
            @if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <form method="POST" action="{{ route('admin.question-csv.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group"><label for="question_csv">Select CSV File</label>
                    <input type="file" name="csv" id="question_csv" accept=".csv,text/csv,text/plain" class="form-control" required></div>
                <button type="submit" class="btn btn-primary" @if(!$context) disabled @endif><i class="fa fa-eye"></i> Preview &amp; Validate</button>
            </form>
        </div>
    </div>
    @if($report)
    <div class="box box-success"><div class="box-header with-border"><h3 class="box-title">Import Report</h3></div><div class="box-body">
        <p><strong>Created:</strong> {{ $report['created'] }} &nbsp; <strong>Skipped:</strong> {{ $report['skipped'] }}</p>
        @if($report['errors'])<div class="alert alert-warning"><strong>Failed rows:</strong><ul>@foreach($report['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <a href="{{ route('question.view') }}" class="btn btn-info">View Questions</a>
    </div></div>
    @endif
    @if($preview)
    <div class="box box-info"><div class="box-header with-border"><h3 class="box-title">CSV Preview</h3></div><div class="box-body">
        <p><strong>Total:</strong> {{ count($preview['rows']) }} &nbsp;
            <strong>Ready:</strong> <span class="text-success">{{ $preview['valid_count'] }}</span> &nbsp;
            <strong>Invalid / Duplicate:</strong> <span class="text-danger">{{ $preview['invalid_count'] }}</span></p>
        <p class="text-muted">Only valid rows will be imported. Duplicate questions and invalid rows will be skipped. Preview expires after one hour.</p>
        @if($preview['valid_count'] > 0)
        <form action="{{ route('admin.question-csv.import') }}" method="POST" onsubmit="return confirm('Import {{ $preview['valid_count'] }} validated MCQ questions?');">
            @csrf
            <input type="hidden" name="token" value="{{ $preview['token'] }}">
            <button class="btn btn-success" type="submit"><i class="fa fa-check"></i> Confirm Import ({{ $preview['valid_count'] }})</button>
        </form>
        @endif
        <div class="table-responsive" style="margin-top:15px;max-height:650px;overflow:auto">
            <table class="table table-bordered table-striped">
                <thead><tr><th>CSV Row</th><th>Question</th><th>Options A / B / C / D</th><th>Answer</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($preview['rows'] as $row)
                <tr>
                    <td>{{ $row['line'] }}</td><td style="min-width:180px">{{ $row['data']['title'] }}</td>
                    <td style="min-width:180px"><ol type="A" style="padding-left:20px">@foreach(['option_a','option_b','option_c','option_d'] as $f)<li>{{ $row['data'][$f] }}</li>@endforeach</ol></td>
                    <td>{{ $row['data']['correct_answer'] }}</td>
                    <td>@if(!$row['errors'])<span class="label label-success">Ready</span>@else<span class="label label-danger">Skipped</span><ul>@foreach($row['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul>@endif</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
    @endif
</section>
@endsection
