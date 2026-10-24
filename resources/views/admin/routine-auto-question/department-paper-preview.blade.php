@extends('dashboard')
@section('title', 'Department Full Question Paper')
@section('content')
<section class="content-header"><h1>{{ $exam->exam_name }} — {{ $department->name }}</h1></section>
<section class="content"><div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Full Question Paper View</h3>
        <a class="btn btn-default btn-sm pull-right" href="{{ route('admin.routine-auto-question.exam-details', ['courseId'=>$course->id, 'batchId'=>$batch->id, 'examId'=>$exam->exam_id]) }}">
            <i class="fa fa-arrow-left"></i> Department Details
        </a>
    </div>
    <div class="box-body">
        <p><strong>Course:</strong> {{ $course->name }} &nbsp; <strong>Batch:</strong> {{ $batch->name }}</p>
        <p><strong>Exam:</strong> {{ $exam->exam_name }} &nbsp; <strong>Department:</strong> {{ $department->name }}</p>
        <div class="alert alert-info">
            Saved Question Selection: <strong>{{ count($commonIds) }}</strong> Common +
            <strong>{{ count($departmentIds) }}</strong> Department =
            <strong>{{ count($commonIds) + count($departmentIds) }}</strong> Unique Questions.
            This is a read-only preview; opening it does not generate or change questions.
        </div>
        @php $paperNumber = 0; @endphp
        @foreach([['heading'=>'Non-Department Common Questions','ids'=>$commonIds], ['heading'=>$department->name.' — Department Questions','ids'=>$departmentIds]] as $groupIndex => $group)
            <h4>{{ $groupIndex + 1 }}. {{ $group['heading'] }} ({{ count($group['ids']) }})</h4>
            @forelse($group['ids'] as $questionId)
                @php $question = $questions->get($questionId); @endphp
                @if($question)
                    @php $paperNumber++; @endphp
                    <div class="panel panel-default" style="margin:12px 0">
                        <div class="panel-heading"><strong>{{ $paperNumber }}.</strong> {!! $question->title !!}</div>
                        <div class="panel-body">
                            <div class="row">
                                @foreach(($options[$questionId] ?? []) as $option)
                                    <div class="col-sm-6" style="margin:5px 0;min-height:34px">
                                        <div style="border:1px solid #dce3ec;border-radius:5px;padding:8px">
                                            <strong>{{ $option['letter'] }})</strong>
                                            <span style="display:inline-block;vertical-align:middle">{!! $option['text'] !!}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if(isset($correct[$questionId]))
                                <p class="text-success"><strong>Correct Answer:</strong> {{ $correct[$questionId] }}</p>
                            @endif
                            @if(!empty($question->explanation))
                                <p><strong>Explanation:</strong> {!! $question->explanation !!}</p>
                            @endif
                            <small class="text-muted">Question ID: {{ $questionId }}</small>
                        </div>
                    </div>
                @else
                    <p class="text-warning">Saved Question ID {{ $questionId }} is not available in the Question Bank.</p>
                @endif
            @empty
                <p class="text-muted">No saved questions in this section yet.</p>
            @endforelse
            <hr>
        @endforeach
        <p><strong>Batch Running Time:</strong> {{ $settings ? ($settings->running_minutes ?? 'Not configured').' minutes' : 'Not configured' }}
        &nbsp; <strong>Question Limit:</strong> {{ $settings->question_limit ?? 'Not configured' }}</p>
    </div>
</div></section>
@include('shared.mathjax-renderer')
@endsection
