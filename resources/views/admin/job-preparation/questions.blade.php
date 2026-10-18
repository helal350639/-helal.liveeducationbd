@extends('dashboard')
@section('title', 'MCQ Job Preparation - Questions')
@section('content')
<style>
.jobprep-question-wrap{font-family:inherit}
.jobprep-question-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:14px}
.jobprep-question-header h3{color:#a40000;margin:0;font-weight:700;font-size:20px}
.jobprep-qcard{border:1px solid #d8dee6;background:#fff;border-radius:5px;margin-bottom:16px;overflow:hidden}
.jobprep-qhead{padding:12px 14px;border-bottom:1px solid #e3e7ed;display:flex;align-items:flex-start;gap:12px;justify-content:space-between}
.jobprep-qtitle{flex:1;min-width:0;font-size:15px;font-weight:600;overflow-wrap:anywhere;color:#1e293b;line-height:1.7}
.jobprep-qnum{font-weight:800;color:#b30000;margin-right:7px}
.jobprep-qbuttons{display:flex;gap:5px;flex-shrink:0;flex-wrap:wrap}
.jobprep-qbody{padding:14px 18px}
.jobprep-choices{margin:4px 0 15px 24px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 24px}
.jobprep-choice{font-size:15px;color:#243349;line-height:1.7;overflow-wrap:anywhere}
.jobprep-choice.is-correct{font-weight:700;color:#00852d}
.jobprep-choice img,.jobprep-qtitle img,.jobprep-explanation img{max-width:100%;height:auto}
.jobprep-qimg{max-width:100%;max-height:240px;object-fit:contain;margin:8px 0}
.jobprep-toggle{background:#04b5dc;border-color:#04b5dc;color:white}
.jobprep-explanation{margin-top:12px;background:#f3fafc;border:1px solid #d3eaf1;border-radius:5px;padding:12px;line-height:1.8}
.jobprep-qbottom{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.jobprep-qpagination{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.jobprep-qpagination .pagination{margin:4px 0}
@media(max-width:650px){.jobprep-choices{grid-template-columns:1fr;margin-left:4px}.jobprep-qhead{flex-wrap:wrap}.jobprep-qbuttons{width:100%;justify-content:flex-end}.jobprep-qbody{padding:12px}}
<style>
/* Full-width management tools: stays inside the existing AdminLTE dashboard */
.jp-fullwide{width:100%!important;max-width:none!important;margin:0!important}
.jp-filters{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:18px;padding:14px;background:#f5f9fd;border:1px solid #e0eaf2;border-radius:7px}
.jp-filters .jp-search{flex:2;min-width:210px}.jp-filters .jp-select{flex:1;min-width:145px}.jp-filters label{display:block;margin-bottom:5px;font-size:12px;color:#536579}
.jp-filters .jp-submit{display:flex;gap:7px}.jp-filters .jp-submit .btn{min-height:35px}
.jp-pretty-actions{display:flex;align-items:center;justify-content:center;gap:7px;flex-wrap:wrap}
.jp-pretty-actions .btn{border-radius:5px!important;min-height:34px;min-width:76px;padding:7px 10px!important;display:inline-flex;align-items:center;justify-content:center;gap:5px;font-weight:600;white-space:nowrap}
.jp-pretty-actions form{margin:0!important;display:inline-flex}
.jp-wide-table{width:100%;margin-bottom:14px}.jp-wide-table th{background:#f4f8fc!important;color:#31445b}.jp-wide-table td,.jp-wide-table th{vertical-align:middle!important;padding:11px 10px!important}
.jp-wide-table tbody tr:hover{background:#f7fbff}
.jp-wide-table .jp-action-cell{min-width:330px;text-align:center}
@media(max-width:880px){.jp-wide-table{min-width:980px}.jp-filters .jp-search{flex-basis:100%}}
.jobprep-question-header .btn-success{min-height:42px;padding:10px 18px;font-weight:700}.jobprep-qbuttons .btn{min-height:30px;min-width:34px}.jobprep-question-wrap{width:100%;max-width:none}
</style>
</style>
<section class="content-header">
  <h1>MCQ Job Preparation <small>{{ ucfirst($level) }} / {{ $target->name }}</small></h1>
  <ol class="breadcrumb"><li><a href="{{ route('admin.job-preparation.categories',['kind'=>$kind]) }}">{{ $kind==='department'?'Department':'Non-Department' }}</a></li><li class="active">Questions</li></ol>
</section>
<section class="content jobprep-question-wrap jp-fullwide">
<div class="box box-primary">
<div class="box-body">
  <div class="jobprep-question-header">
    <div><h3>Questions ({{ $rows->total() }})</h3><small class="text-muted">Latest added first · 25 questions per page · Main Database live sync</small></div>
    <div>
      <a class="btn btn-success btn-lg" href="{{ route('admin.job-preparation.questions.picker',['kind'=>$kind,'level'=>$level,'entity'=>$entity]) }}"><i class="fa fa-plus"></i> Add Questions</a>
      <a class="btn btn-default btn-sm" href="{{ route('admin.job-preparation.categories',['kind'=>$kind]) }}"><i class="fa fa-arrow-left"></i> Categories</a>
    </div>
  </div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<form method="GET" class="jp-filters" action="{{ url()->current() }}"><div class="jp-search"><label>Question Title / ID</label><input class="form-control" name="search" value="{{request('search','')}}" placeholder="Search question title or ID"></div><div class="jp-select"><label>Question Status</label><select class="form-control" name="status"><option value="">All Status</option><option value="Active" {{request('status')==='Active'?'selected':''}}>Active</option><option value="Inactive" {{request('status')==='Inactive'?'selected':''}}>Inactive</option></select></div><div class="jp-submit"><button class="btn btn-info" type="submit"><i class="fa fa-filter"></i> Filter</button><a href="{{url()->current()}}" class="btn btn-default">Reset</a></div></form>
  <div class="jobprep-qpagination">
    <span class="text-muted">Showing {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }}</span>
    {{ $rows->links() }}
  </div>
  @forelse($rows as $row)
    @php
      $number = ($rows->firstItem() ?? 1) + $loop->index;
      $options = $items->get($row->id, collect());
      $letters = ['a','b','c','d','e','f','g','h'];
      $explainId = 'jobprep-answer-' . $row->link_id;
    @endphp
    <article class="jobprep-qcard">
      <div class="jobprep-qhead">
        <label class="checkbox-inline" style="padding-left:0;margin:2px 0 0"><input type="checkbox" class="jobprep-select" data-id="{{ $row->id }}" aria-label="Select question {{ $number }}"></label>
        <div class="jobprep-qtitle"><span class="jobprep-qnum">প্রশ্ন নং-{{ $number }}:</span> {!! $row->title !!}</div>
        <div class="jobprep-qbuttons">
          <a class="btn btn-info btn-xs" title="View in Main Database" target="_blank" rel="noopener" href="{{ route('question.show',$row->id) }}"><i class="fa fa-eye"></i></a>
          <a class="btn btn-warning btn-xs" title="Edit in Main Database" target="_blank" rel="noopener" href="{{ route('question.edit',$row->id) }}"><i class="fa fa-pencil"></i></a>
          <form method="POST" style="display:inline" action="{{ route('admin.job-preparation.questions.detach',['kind'=>$kind,'level'=>$level,'entity'=>$entity,'question'=>$row->id]) }}" onsubmit="return confirm('Remove only this question link? The main question will NOT be deleted.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-xs" title="Unlink this single question only (keeps main database question)"><i class="fa fa-trash"></i></button>
          </form>
        </div>
      </div>
      <div class="jobprep-qbody">
        @if($row->image)
          <div><img class="jobprep-qimg" src="{{ \Illuminate\Support\Str::startsWith($row->image,['http://','https://']) ? $row->image : asset(ltrim($row->image,'/')) }}" alt="Question image"></div>
        @endif
        @if($options->count())
          <div class="jobprep-choices">
            @foreach($options as $i => $option)
              @php
                $isCorrect = in_array(strtolower(trim((string)$option->correct_answer)), ['1','true','yes','on'], true);
                $letter = $letters[$i] ?? ($i+1);
              @endphp
              <div class="jobprep-choice {{ $isCorrect ? 'is-correct' : '' }}">
                <span>{{ $letter }})</span> {!! $option->item !!}
                @if($option->image)
                  <div><img style="max-width:100%;max-height:150px" src="{{ \Illuminate\Support\Str::startsWith($option->image,['http://','https://']) ? $option->image : asset(ltrim($option->image,'/')) }}" alt="Option image"></div>
                @endif
              </div>
            @endforeach
          </div>
        @else
          <p class="text-muted">No MCQ options saved for this question.</p>
        @endif
        <div class="jobprep-qbottom">
          <button class="btn btn-info btn-sm jobprep-toggle" type="button" data-toggle="collapse" data-target="#{{ $explainId }}" aria-expanded="false" aria-controls="{{ $explainId }}">Show Answer &amp; Explanation: <i class="fa fa-chevron-down"></i></button>
          <small class="text-muted">Question ID: {{ $row->id }} · {{ $row->status }}</small>
        </div>
        <div class="collapse" id="{{ $explainId }}">
          <div class="jobprep-explanation">
            <strong>সঠিক উত্তর:</strong>
            @php $correctOptions = $options->filter(fn($opt) => in_array(strtolower(trim((string)$opt->correct_answer)), ['1','true','yes','on'], true)); @endphp
            @forelse($correctOptions as $opt)
              <div class="text-success">{!! $opt->item !!}</div>
            @empty
              <div>{!! $row->answer ?: 'Correct answer not specified.' !!}</div>
            @endforelse
            <hr style="margin:10px 0">
            <strong>ব্যাখ্যা:</strong>
            <div>{!! $row->explanation ?: ($row->solution ?: 'কোনো ব্যাখ্যা দেওয়া হয়নি।') !!}</div>
          </div>
        </div>
      </div>
    </article>
  @empty
    <div class="alert alert-info">No MCQ questions linked here yet. Click <strong>Add Questions</strong> to select from the main database.</div>
  @endforelse
  <div class="jobprep-qpagination"><span class="text-muted">25 questions per page</span>{{ $rows->links() }}</div>
</div>
</div>
</section>
@endsection
