@extends('student')
@section('title','Practice Quiz Result')
@section('stylesheets')
<style>
.lebd-qr,.lebd-qr *{box-sizing:border-box}.lebd-qr{background:#f1f6fd;min-height:75vh;padding:20px 12px 40px;color:#17345c}.lebd-qr .wrap{max-width:850px;margin:auto}.lebd-qr .panel,.lebd-qr .q{background:white;border:1px solid #dce8f7;border-radius:14px;padding:16px;margin-bottom:12px}.lebd-qr h1{color:#1055a5;margin:0 0 8px;font-size:23px}.lebd-qr .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}.lebd-qr .stat{background:#edf5ff;padding:12px;border-radius:10px;text-align:center}.lebd-qr .stat b{display:block;font-size:21px}.lebd-qr .qtitle{font-weight:800;line-height:1.8;margin-bottom:10px}.lebd-qr .opt{padding:9px 11px;border:1px solid #e0e9f3;border-radius:8px;margin:6px 0}.lebd-qr .good{background:#e9fff2;border-color:#36ae75}.lebd-qr .bad{background:#fff0f0;border-color:#df7883}.lebd-qr .back{display:inline-block;padding:11px 16px;background:#0866d5;color:white;border-radius:9px;text-decoration:none;font-weight:800}
@media(max-width:600px){.lebd-qr{padding:12px 8px}.lebd-qr .stats{grid-template-columns:repeat(2,1fr)}.lebd-qr h1{font-size:20px}}
</style>
@endsection
@section('content')
<div class="lebd-qr"><main class="wrap"><section class="panel"><h1>Practice Quiz — ফলাফল</h1><p>{{ $categoryRow->name }} @if($subcategoryRow) · {{ $subcategoryRow->name }} @endif</p>
@if($attempt->show_result)<div class="stats"><div class="stat"><b>{{ number_format((float)$attempt->score,2) }}</b>প্রাপ্ত নম্বর</div><div class="stat"><b>{{ $attempt->correct_count }}</b>সঠিক</div><div class="stat"><b>{{ $attempt->wrong_count }}</b>ভুল</div><div class="stat"><b>{{ $attempt->unanswered_count }}</b>উত্তরহীন</div></div><p style="margin-top:12px">মোট প্রশ্ন: {{ $attempt->question_count }} · সঠিক উত্তরে {{ $attempt->correct_mark }} · নেগেটিভ মার্ক {{ $attempt->negative_mark }}</p>@else<p>আপনার পরীক্ষা সফলভাবে জমা হয়েছে। ফলাফল দেখার অপশন বন্ধ থাকায় এই মুহূর্তে মার্ক দেখানো হচ্ছে না।</p>@endif
</section>
@if($attempt->show_result)@foreach($review as $i=>$row)<article class="q"><div class="qtitle">প্রশ্ন {{ $i+1 }}. {!! $row['question']->title??'প্রশ্ন পাওয়া যায়নি' !!}</div>@foreach($row['options'] as $opt)<div class="opt @if($row['right'] && $row['right']->id==$opt->id)good @elseif($row['chosen'] && $row['chosen']->id==$opt->id)bad @endif">{!! $opt->item !!} @if($row['right'] && $row['right']->id==$opt->id)<b>✓ সঠিক উত্তর</b>@endif @if($row['chosen'] && $row['chosen']->id==$opt->id)<b>← আপনার উত্তর</b>@endif</div>@endforeach</article>@endforeach @endif
<a class="back" href="{{ route('student.exam-section.wrong-unanswered',['category'=>$categoryRow->id,'subcategory_id'=>$subcategoryRow?->id]) }}">Question Review-এ ফিরুন</a>
</main></div>
@endsection
