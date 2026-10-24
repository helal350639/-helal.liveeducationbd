@extends('dashboard')
@section('title','MCQ for Exam - Linked Questions')
@section('content')
@include('admin.exam-mcq._head')
<div class="emcq-panel"><div class="emcq-topline"><h2 class="emcq-h">Linked MCQ — {{ $category->name }} / {{ucfirst($level)}}</h2><div class="emcq-actions"><a class="btn btn-default" href="{{route('admin.exam-mcq.detail',['kind'=>$kind,'categoryId'=>$category->id])}}">← Subject / Chapter</a><a class="btn btn-primary" href="{{route('admin.exam-mcq.picker',['kind'=>$kind,'categoryId'=>$category->id,'subject_id'=>$subjectId?:null,'chapter_id'=>$chapterId?:null])}}">+ Add Main Database MCQ</a></div></div><span class="emcq-meta">{{$questions->total()}} linked questions</span></div>
<div class="emcq-panel">@forelse($questions as $q)
@php $serial=($questions->currentPage()-1)*$questions->perPage()+$loop->iteration; $opts=$questionOptions[$q->id] ?? collect(); @endphp
<div class="emcq-card"><div class="emcq-topline"><div class="emcq-qnum">Q-{{str_pad($serial,2,'0',STR_PAD_LEFT)}}</div><span class="label label-{{$q->status==='Active'?'success':'default'}}">{{$q->status}}</span></div>
<div class="emcq-q">{!!$q->title!!}</div>
@if($opts->count())<div class="emcq-opts">@foreach($opts as $oi=>$opt)<div class="emcq-opt {{ $opt->correct_answer?'correct':'' }}"><b>{{['ক','খ','গ','ঘ'][$oi] ?? ($oi+1)}}.</b> {!!$opt->item!!} @if($opt->image)<img src="{{asset(ltrim($opt->image,'/'))}}" alt="Option image">@endif @if($opt->correct_answer)<span style="color:#07854c">✓</span>@endif</div>@endforeach</div>@endif
@if($q->explanation)<details style="margin:12px 0"><summary>Explanation</summary><div class="emcq-q">{!!$q->explanation!!}</div></details>@endif
<form method="post" action="{{route('admin.exam-mcq.detach',['kind'=>$kind,'categoryId'=>$category->id,'linkId'=>$q->link_id])}}" onsubmit="return confirm('Remove this link? Main question remains unchanged.')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">Remove Link</button></form></div>
@empty<p>No questions linked yet.</p>@endforelse
{{$questions->links()}}</div></div>
@endsection
