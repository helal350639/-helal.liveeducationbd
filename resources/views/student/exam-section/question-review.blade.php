@extends('student')
@section('title','Question Review — '.$attempt->exam_name)
@section('stylesheets')
<style>
#Footer{display:none!important}.lebd-review,.lebd-review *{box-sizing:border-box}.lebd-review{background:#f1f6fd;min-height:75vh;padding:20px 12px 45px;color:#17345c;font-family:system-ui,-apple-system,'Segoe UI','Noto Sans Bengali',sans-serif}.lebd-review .wrap{max-width:1000px;margin:auto}.lebd-review a{text-decoration:none}.lebd-review .back{display:inline-block;margin-bottom:12px;color:#1767c8;font-size:13px;font-weight:750}.lebd-review .head,.lebd-review .qcard{background:#fff;border:1px solid #dce8f7;border-radius:15px;box-shadow:0 5px 18px #1b47700b}.lebd-review .head{padding:17px;margin-bottom:14px}.lebd-review h1{font-size:22px;color:#104d9b;margin:0 0 5px}.lebd-review .muted{font-size:12px;color:#7185a0;margin:0}.lebd-review .filter-wrap{display:flex;align-items:center;gap:12px;margin:14px 0 18px;padding:12px 14px;background:#fff;border:1px solid #dce8f7;border-radius:12px;box-shadow:0 4px 14px #1b477008}.lebd-review .filter-actions{display:flex;align-items:center;gap:10px;flex:1;min-width:0}.lebd-review .make-quiz{display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;min-height:42px;padding:9px 16px;border-radius:9px;background:linear-gradient(110deg,#075ac8,#1685ed);box-shadow:0 3px 0 #064b9d;color:#fff;font-size:13px;font-weight:850;text-decoration:none}.lebd-review .filter-label{font-size:13px;font-weight:800;color:#24466f;white-space:nowrap}.lebd-review .filter-select{width:100%;max-width:420px;min-height:42px;padding:9px 38px 9px 12px;border:1px solid #bfd3ee;border-radius:9px;background:#f8fbff;color:#174d91;font-size:13px;font-weight:750;outline:none;cursor:pointer}.lebd-review .filter-select:focus{border-color:#1670db;box-shadow:0 0 0 3px #1670db1c}.lebd-review .review-section[hidden]{display:none}.lebd-review h2{font-size:17px;margin:20px 0 10px}.lebd-review .qcard{padding:15px;margin:10px 0}.lebd-review .qhead{display:flex;justify-content:space-between;gap:10px;align-items:flex-start;margin-bottom:10px}.lebd-review .qno{font-size:12px;font-weight:850;color:#155db2;background:#eaf3ff;padding:5px 9px;border-radius:7px;white-space:nowrap}.lebd-review .status{font-size:11px;font-weight:850;padding:5px 9px;border-radius:20px}.lebd-review .status.correct{background:#e3f8eb;color:#087b48}.lebd-review .status.wrong{background:#fff0f0;color:#b52d42}.lebd-review .status.unanswered{background:#fff5df;color:#9a6800}.lebd-review .question{font-size:15px;line-height:1.8;font-weight:700;overflow-wrap:anywhere;margin-bottom:10px}.lebd-review .option{padding:10px 12px;margin:6px 0;border:1px solid #e2eaf4;border-radius:9px;font-size:13px;line-height:1.65;overflow-wrap:anywhere}.lebd-review .option.chosen{border-color:#e69b32;background:#fff8e9}.lebd-review .option.answer{border-color:#20a36a;background:#edfff5}.lebd-review .legend{font-size:11px;color:#7487a1;margin-top:9px}.lebd-review .empty{padding:18px;text-align:center;color:#7b8da4;background:#fff;border:1px dashed #ccd9eb;border-radius:12px}
@media(max-width:600px){#Footer{display:none!important}.lebd-review{padding:12px 8px 30px}.lebd-review h1{font-size:19px}.lebd-review .qcard{padding:12px}.lebd-review .question{font-size:14px}.lebd-review .filter-wrap{display:block;padding:10px}.lebd-review .filter-label{display:block;margin-bottom:7px}.lebd-review .filter-select{max-width:none;width:100%}.lebd-review .filter-actions{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:7px}.lebd-review .make-quiz{padding:9px 10px;font-size:12px}.lebd-review~#Footer,#Footer:has(+ .lebd-review){display:none!important}}
</style>
@endsection
@section('content')
<div class="lebd-review"><main class="wrap">
<a class="back" href="{{ route('student.exam-section.wrong-unanswered',['category'=>$category->id,'subcategory_id'=>$subcategory?->id]) }}">← সব পরীক্ষায় ফিরুন</a>
<header class="head"><h1>{{ $attempt->exam_name }} — Question Review</h1><p class="muted">{{ $scopeTitle }} · {{ $attempt->routine_name }} · {{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at,'Asia/Dhaka')->format('d M Y, h:i A') : '—' }}</p></header>
<div class="filter-wrap">
<label class="filter-label" for="questionTypeFilter">প্রশ্নের ধরন নির্বাচন করুন</label>
<div class="filter-actions"><select class="filter-select" id="questionTypeFilter" aria-label="প্রশ্নের ধরন নির্বাচন করুন">
<option value="wrong">ভুল উত্তর ({{ count($groups['wrong']) }})</option>
<option value="unanswered">উত্তরহীন ({{ count($groups['unanswered']) }})</option>
<option value="correct">সঠিক উত্তর ({{ count($groups['correct']) }})</option>
</select><a class="make-quiz" href="{{ route('student.exam-section.wrong-unanswered.quiz.setup',['category'=>$category->id,'subcategory_id'=>$subcategory?->id,'mode'=>'wrong']) }}">✦ Make a Quiz</a></div>
</div>
@foreach([['wrong','ভুল উত্তর প্রশ্ন','wrong'],['unanswered','উত্তর না দেওয়া প্রশ্ন','unanswered'],['correct','সঠিক উত্তর প্রশ্ন','correct']] as [$key,$heading,$class])
<section id="{{ $key }}" class="review-section" data-review-section="{{ $key }}" @if($key !== 'wrong') hidden @endif><h2>{{ $heading }} ({{ count($groups[$key]) }})</h2>
@if(count($groups[$key]))
@foreach($groups[$key] as $q)
<article class="qcard"><div class="qhead"><span class="qno">প্রশ্ন {{ $loop->iteration }}</span><span class="status {{ $class }}">{{ $class==='wrong'?'ভুল উত্তর':($class==='unanswered'?'উত্তর দেননি':'সঠিক উত্তর') }}</span></div>
<div class="question">{!! $q['title'] !!}</div>
@foreach($q['options'] as $opt)
<div class="option @if((string)$opt->id===(string)$q['correct_id'])answer @elseif($q['chosen_id']!==null && (string)$opt->id===(string)$q['chosen_id'])chosen @endif">
{!! $opt->item !!}
@if((string)$opt->id===(string)$q['correct_id']) <strong>✓ সঠিক উত্তর</strong> @endif
@if($q['chosen_id']!==null && (string)$opt->id===(string)$q['chosen_id'] && (string)$opt->id!==(string)$q['correct_id']) <strong>← আপনার উত্তর</strong> @endif
</div>
@endforeach
@if($q['chosen_id']===null)<p class="legend">আপনি এই প্রশ্নের কোনো উত্তর নির্বাচন করেননি।</p>@endif
</article>
@endforeach
@else<div class="empty">এই বিভাগে কোনো প্রশ্ন নেই।</div>@endif
</section>
@endforeach
</main></div>
<script>
(function () {
    const selector = document.getElementById('questionTypeFilter');
    if (!selector) return;
    const sections = document.querySelectorAll('[data-review-section]');
    function showSelectedSection() {
        const selected = selector.value;
        sections.forEach(function (section) {
            section.hidden = section.dataset.reviewSection !== selected;
        });
    }
    selector.addEventListener('change', showSelectedSection);
    showSelectedSection();
})();
</script>
@endsection
