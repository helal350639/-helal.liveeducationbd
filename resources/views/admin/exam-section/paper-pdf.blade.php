<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $cat->name }} — {{ $exam }}</title>
<style>
@font-face{font-family:SolaimanLipi;src:url('{{ asset('fonts/SolaimanLipi.ttf') }}') format('truetype');font-weight:400;font-style:normal;font-display:swap}
@font-face{font-family:SiyamRupali;src:url('{{ asset('fonts/Siyamrupali.ttf') }}') format('truetype');font-weight:400;font-style:normal;font-display:swap}
*{box-sizing:border-box}
html,body{margin:0;padding:0;background:#eef2f7;color:#17243a;font-family:SolaimanLipi,SiyamRupali,Arial,sans-serif;font-size:12pt;line-height:1.55}
.toolbar{max-width:210mm;margin:16px auto 10px;display:flex;justify-content:flex-end;gap:8px}
.toolbar button,.toolbar a{border:0;border-radius:6px;padding:9px 15px;background:#155fba;color:#fff;text-decoration:none;font:inherit;font-size:11pt;cursor:pointer}
.paper{width:210mm;min-height:297mm;margin:0 auto 18px;padding:15mm 16mm;background:#fff;box-shadow:0 2px 14px #18385b20}
.paper-head{text-align:center;border-bottom:1px solid #aebed0;padding-bottom:10px;margin-bottom:13px}
.paper-head h1{font-size:22pt;line-height:1.3;margin:0 0 4px;color:#173c70;font-weight:700}
.paper-head h2{font-size:15pt;margin:0;color:#31567e;font-weight:600}
.meta{border:1px solid #cbd7e5;border-radius:5px;padding:9px 12px;margin:12px 0 15px;display:grid;grid-template-columns:1fr 1fr;gap:4px 18px}
.meta div{min-width:0;overflow-wrap:anywhere}
.syllabus{background:#edf4fc;border-left:4px solid #2879ce;padding:7px 10px;margin:14px 0 12px;font-weight:700}
.question{margin:0 0 14px;padding:0 0 11px;border-bottom:1px solid #e3e8ef;break-inside:avoid;page-break-inside:avoid}
.q-title{font-size:12.5pt;font-weight:700;margin:0 0 7px;line-height:1.6;overflow-wrap:anywhere}
.q-number{font-weight:700;color:#153d70;margin-right:4px}
.q-image{max-width:100%;max-height:65mm;object-fit:contain;display:block;margin:7px 0}
.options{display:grid;grid-template-columns:1fr 1fr;gap:5px 12px;margin-top:6px}
.option{display:flex;align-items:flex-start;gap:6px;min-width:0;padding:3px 2px;overflow-wrap:anywhere}
.check{display:inline-block;flex:0 0 12px;width:12px;height:12px;border:1px solid #334155;margin-top:5px}
.letter{font-weight:700;min-width:17px}
.option img{max-width:100%;max-height:35mm;object-fit:contain;display:block;margin-top:4px}
.paper-foot{border-top:1px solid #d8e0e9;margin-top:18px;padding-top:7px;text-align:center;color:#61748b;font-size:9pt}
.empty{padding:28px;text-align:center;border:1px dashed #aebed0;border-radius:6px;color:#65758b}
@page{size:A4;margin:0}
@media print{
 html,body{background:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
 .toolbar{display:none!important}.paper{width:auto;min-height:0;margin:0;padding:13mm 15mm;box-shadow:none}
 .question{break-inside:avoid;page-break-inside:avoid}
 a{color:inherit;text-decoration:none}
}
@media screen and (max-width:760px){.paper{width:100%;min-height:100vh;padding:18px 15px;margin:0}.toolbar{padding:0 12px}.meta{grid-template-columns:1fr}.options{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="toolbar">
 <button type="button" onclick="printPaper()">Print / Save as PDF</button>
 <a href="{{ route('admin.exam-section.auto-question.exams',['category'=>$cat->id]+($sub?['subcategory_id'=>$sub->id]:[])) }}">Back to Exam List</a>
</div>
<main class="paper">
 <header class="paper-head">
  <h1>{{ $cat->name ?: 'Question Paper' }}</h1>
  <h2>{{ $exam }} — প্রশ্নপত্র</h2>
 </header>
 <section class="meta">
  <div><strong>Routine:</strong> {{ $routine->name }}</div>
  <div><strong>Category:</strong> {{ $cat->name }}@if($sub) / {{ $sub->name }}@endif</div>
  @if(!empty($item->exam_start_at))<div><strong>পরীক্ষার তারিখ ও সময়:</strong> {{ \Carbon\Carbon::parse($item->exam_start_at)->format('d M Y, h:i A') }}</div>@endif
  @if(!empty($item->exam_end_at))<div><strong>শেষ সময়:</strong> {{ \Carbon\Carbon::parse($item->exam_end_at)->format('d M Y, h:i A') }}</div>@endif
  <div><strong>মোট প্রশ্ন:</strong> {{ $links->count() }} টি</div>
  @if(!empty($item->notes))<div><strong>নির্দেশনা:</strong> {{ $item->notes }}</div>@endif
 </section>
 @if(count($topicNames))<div class="syllabus">Subject &amp; Syllabus: {{ implode(' · ', $topicNames) }}</div>@endif
 @forelse($links as $link)
  <article class="question">
   <div class="q-title"><span class="q-number">{{ $loop->iteration }}.</span>{!! $link->title !!}</div>
   @if($link->image)<img class="q-image" src="{{ asset(ltrim($link->image,'/')) }}" alt="Question image">@endif
   @php $choices=$options->get($link->id,collect()); @endphp
   @if($choices->count())
    <div class="options">
     @foreach($choices as $choice)
      <div class="option">
       <span class="check" aria-hidden="true"></span>
       <span class="letter">{{ ['A','B','C','D','E','F'][$loop->index] ?? ($loop->index+1) }}.</span>
       <span>{!! $choice->item !!}
        @if($choice->image)<img src="{{ asset(ltrim($choice->image,'/')) }}" alt="Option image">@endif
       </span>
      </div>
     @endforeach
    </div>
   @else
    <div style="font-size:10pt;color:#9b3b32">এই প্রশ্নের কোনো MCQ option পাওয়া যায়নি।</div>
   @endif
  </article>
 @empty
  <div class="empty">এই পরীক্ষার জন্য এখনো কোনো প্রশ্ন সংরক্ষণ করা হয়নি। আগে Create Question থেকে প্রশ্ন সংরক্ষণ করুন।</div>
 @endforelse
 <footer class="paper-foot">{{ config('app.name','LiveEducationBD') }} · {{ $exam }} · Total {{ $links->count() }} Questions</footer>
</main>
<script>
function printPaper(){
  const run=()=>window.print();
  if(document.fonts&&document.fonts.ready){document.fonts.ready.then(run).catch(run);}else{setTimeout(run,500);}
}
window.addEventListener('load',()=>{if(document.fonts&&document.fonts.load){Promise.all([document.fonts.load('12pt SolaimanLipi'),document.fonts.load('12pt SiyamRupali')]).catch(()=>{});}});
</script>
</body>
</html>
