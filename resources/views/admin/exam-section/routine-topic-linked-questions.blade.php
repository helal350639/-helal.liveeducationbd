@extends('dashboard')
@section('title','Exam Section — Linked Questions')
@section('content')
<style>
.ex156{background:#eef3f9;min-height:65vh;padding:20px;font-family:'Noto Sans Bengali',Arial,sans-serif;color:#173454}
.ex156 *{box-sizing:border-box}.ex156-panel{background:white;border:1px solid #d4e3f2;border-radius:11px;padding:20px;margin-bottom:16px}
.ex156-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.ex156 h2{font-size:20px;font-weight:750;margin:0 0 6px}.ex156-muted{font-size:12px;color:#59708d}
.ex156-btn{display:inline-block;border-radius:5px;background:#1269c9;color:white!important;padding:9px 12px;text-decoration:none;font-size:12px}
.ex156-question{border:1px solid #dbe5f4;border-radius:9px;padding:15px;margin-top:13px;background:#fbfdff}
.ex156-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin-top:12px}
.ex156-option{padding:11px;border-radius:7px;border:1px solid #cfdfec;background:white;font-size:13px}
.ex156-correct{background:#e1f8ea;border-color:#9de0b8}.ex156-answer{background:#e1f8ea;color:#08603b;border-radius:5px;margin-top:10px;padding:9px;font-size:13px}
.ex156-warning{background:#fff3de;color:#88540e}.ex156-questions{display:flex;flex-direction:column;gap:6px}
.ex156-pages{margin-top:15px}.ex156-remove{background:#dd483f;color:white;border:0;border-radius:5px;padding:7px 10px;cursor:pointer;font-size:12px}
@media(max-width:650px){.ex156{padding:8px}.ex156-panel{padding:12px}.ex156-options{grid-template-columns:1fr}}
</style>
<div class="ex156">
<div class="ex156-panel ex156-head">
<div><h2>Linked Questions — {{ ucfirst($level) }}: {{ $topic->name }}</h2>
<div class="ex156-muted">এখানে শুধু এই {{ ucfirst($level) }}-এ সরাসরি যুক্ত প্রশ্ন দেখানো হচ্ছে। মূল Question Bank-এর প্রশ্ন Copy করা হয়নি।</div></div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a class="ex156-btn" href="{{ route('admin.exam-section.routine-topic.index',['level'=>$level,'kind'=>$kind]) }}">← Back to {{ ucfirst($level) }}</a>
<a class="ex156-btn" href="{{ route('admin.exam-section.routine-topic.questions',['level'=>$level,'id'=>$topic->id,'kind'=>$kind]) }}">+ Add MCQ</a>
</div></div>
<div class="ex156-panel">
<h3 style="font-size:16px;font-weight:700;margin:0">Total Linked Questions: {{ $questions->total() }}</h3>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@forelse($questions as $q)
<article class="ex156-question">
<div class="ex156-head"><strong>Q-{{ ($questions->currentPage()-1)*25+$loop->iteration }} <span class="ex156-muted">(Original ID: {{ $q->id }})</span></strong>
<form method="POST" action="{{ route('admin.exam-section.routine-topic.questions.detach',[$level,$topic->id,$q->id]) }}" onsubmit="return confirm('Remove this question link? Original question will NOT be deleted.')">@csrf @method('DELETE')<button class="ex156-remove" type="submit">Remove Link</button></form></div>
<div style="margin-top:8px;font-size:14px">{!! $q->title !!}</div>
@if(!empty($q->image))<img loading="lazy" src="{{ asset(ltrim($q->image,'/')) }}" alt="Question image" style="max-width:100%;max-height:220px;margin-top:7px">@endif
@php $items=$optionItems->get($q->id,collect()); $right=$items->filter(fn($op)=>(string)$op->correct_answer==='1'); @endphp
@if($items->count())
<div class="ex156-options">
@foreach($items as $i=>$op)
<div class="ex156-option {{ (string)$op->correct_answer==='1'?'ex156-correct':'' }}">
<strong>{{ ['ক','খ','গ','ঘ'][$i] ?? ($i+1) }}.</strong> {!! $op->item !!}
@if((string)$op->correct_answer==='1') <strong style="color:#087b43">✓</strong> @endif
</div>
@endforeach
</div>
@if($right->count())<div class="ex156-answer">✓ সঠিক উত্তর:
@foreach($right as $op){{ ['ক','খ','গ','ঘ'][$items->search($op)] ?? '?' }}. {!! $op->item !!}@if(!$loop->last), @endif @endforeach
</div>@else<div class="ex156-answer ex156-warning">এই প্রশ্নের সঠিক উত্তর নির্ধারিত নেই।</div>@endif
@else<div class="ex156-answer ex156-warning">এই প্রশ্নের Options পাওয়া যায়নি।</div>@endif
</article>
@empty<div style="padding:25px;text-align:center;color:#6b7992">এই {{ ucfirst($level) }}-এ এখনো কোনো প্রশ্ন যুক্ত করা হয়নি। Add MCQ থেকে প্রশ্ন যুক্ত করুন।</div>
@endforelse
<div class="ex156-pages">{{ $questions->links() }}</div>
</div></div>
@endsection
