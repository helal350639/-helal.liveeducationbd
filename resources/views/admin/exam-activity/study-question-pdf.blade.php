{{-- Standalone printable study handout; intentionally not the existing exam-paper view. --}}
<!doctype html>
<html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Study Questions — {{ $exam->exam_name }} — {{ $routine->name }}</title>
<style>
@page{size:A4;margin:14mm 13mm}
*{box-sizing:border-box}body{font-family:'Noto Sans Bengali','SolaimanLipi','Kalpurush',Arial,sans-serif;color:#18263d;background:#edf3f9;margin:0;font-size:13px;line-height:1.6}
.toolbar{max-width:880px;margin:16px auto;display:flex;justify-content:space-between;align-items:center;gap:10px}.toolbar button,.toolbar a{font-family:inherit;border:0;border-radius:6px;padding:10px 16px;background:#095fba;color:white;text-decoration:none;cursor:pointer}.toolbar .back{background:#596b80}
.paper{max-width:880px;margin:0 auto 30px;background:white;padding:27px 35px;box-shadow:0 1px 12px #bbc8d6}
header{text-align:center;border-bottom:2px solid #18355a;padding-bottom:12px;margin-bottom:18px}header h1{font-size:21px;margin:0 0 5px}header h2{font-size:15px;margin:0 0 4px}.meta{color:#45536b;font-size:12px}
.section{margin-top:20px;page-break-inside:avoid;border-bottom:1px solid #d7e1ec;padding-bottom:6px}.section h3{font-size:15px;margin:0;color:#103c6c}.section small{color:#66758c}
.question-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:22px;row-gap:0;align-items:stretch}
.question{position:relative;page-break-inside:avoid;break-inside:avoid;min-width:0;margin:0;border-bottom:1px solid #ecedf0;padding:10px 0;overflow-wrap:anywhere}
/* Divider sits in the center gutter, repeated per grid row so it survives pagination. */
.question-grid>.question:nth-child(odd)::after{content:"";position:absolute;top:0;bottom:0;right:-11px;border-right:1px solid #aebdcc;pointer-events:none}.qtitle{font-weight:700;margin-bottom:5px}.options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:10px;row-gap:5px;align-items:start}.option-item{min-width:0;overflow-wrap:anywhere}.option-item strong{white-space:nowrap}.answer{font-weight:700;color:#11613c;margin-top:5px}.explain{font-size:12px;margin-top:5px;color:#46546a}.question img{max-width:220px;max-height:170px;object-fit:contain;vertical-align:middle}
.empty{background:#fff8e9;padding:10px;border:1px solid #e5cf9b}.footer{text-align:center;color:#8b95a3;font-size:11px;margin-top:20px}
@media print{body{background:white;font-size:11px;line-height:1.45}.paper{margin:0;padding:0;max-width:none;box-shadow:none}.toolbar{display:none}.question-grid{grid-template-columns:repeat(2,minmax(0,1fr));column-gap:12mm;row-gap:0}.question{break-inside:avoid;page-break-inside:avoid}.question-grid>.question:nth-child(odd)::after{right:-6mm;border-right:1px solid #aebdcc}.options{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;column-gap:6px;row-gap:3px}.option-item{min-width:0;break-inside:avoid}.question img{max-width:100%;height:auto}.section{break-after:avoid}a[href]:after{content:none!important}}
@media screen and (max-width:650px){.question-grid{grid-template-columns:1fr}.question-grid>.question:nth-child(odd)::after{display:none}.paper{padding:18px}}
</style></head><body>
<div class="toolbar"><a class="back" href="{{ url('/exam-activity-dashboard') }}">← Exam Activity Dashboard</a><button type="button" onclick="window.print()">Print / Save as PDF</button></div>
<main class="paper"><header><h1>Live Education BD</h1><h2>Study Question Collection — {{ $exam->exam_name }}</h2>
<div class="meta">{{ $routine->category_name }}@if($routine->subcategory_name) / {{ $routine->subcategory_name }}@endif · Routine: {{ $routine->name }} · Available Questions: {{ $count }}</div>
<div class="meta">For Student Study &amp; Preparation · Not an Examination Paper</div></header>
@if($count===0)<p class="empty">এই Exam-এর Routine Topic/Range অনুযায়ী কোনো Active MCQ পাওয়া যায়নি। Topic selection ও Question Range যাচাই করুন।</p>@endif
@php $serial=0; @endphp
@foreach($sections as $section)
<section class="section"><h3>{{ collect([$section['department'],$section['subject'],$section['chapter']])->filter()->implode(' / ') ?: $section['topic'] }}</h3><small>Routine Question Range: {{ $section['range'] }} · এই Section-এ {{ count($section['questions']) }}টি প্রশ্ন</small></section>
<div class="question-grid">
@foreach($section['questions'] as $q)
<article class="question"><div class="qtitle">{{ ++$serial }}. {{ $q['title'] }}</div>
@if($q['image'])<div><img src="{{ $q['image'] }}" alt="Question illustration"></div>@endif
<div class="options">@foreach($q['choices'] as $choice)<div class="option-item"><strong>{{ $choice['letter'] }})</strong> {{ $choice['text'] }} @if($choice['image'])<img src="{{ $choice['image'] }}" alt="Option image">@endif</div>@endforeach</div>
@php $answers=collect($q['choices'])->filter(fn($c)=>$c['correct'])->pluck('letter')->implode(', '); @endphp
@if($answers)<div class="answer">Correct Answer: {{ $answers }}</div>@endif
@if($q['explanation'])<div class="explain">Explanation: {{ $q['explanation'] }}</div>@endif
</article>
@endforeach
</div>
@endforeach
<div class="footer">Live Education BD · Study Material · {{ $count }} unique available MCQs</div></main>
</body></html>
