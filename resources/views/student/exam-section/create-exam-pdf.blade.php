<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $exam }} — Exam PDF | LiveEducationBD</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--ink:#18283e;--blue:#126bd5;--muted:#60748f;--line:#dce5f0;--cols:2}
*{box-sizing:border-box}
body{margin:0;background:#edf3fa;color:var(--ink);font-family:'Noto Sans Bengali',Arial,sans-serif;font-size:12px;line-height:1.5}
.toolbar{width:min(1120px,calc(100% - 28px));margin:14px auto 16px;padding:13px 15px;background:#fff;border:1px solid #d7e4f4;border-radius:13px;box-shadow:0 4px 16px #18385c0b;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.toolbar .back,.toolbar button{border:0;border-radius:9px;padding:10px 15px;background:#e7f0ff;color:#075db8;text-decoration:none;font:700 12px 'Noto Sans Bengali',sans-serif;cursor:pointer;white-space:nowrap}
.toolbar .print-btn{background:#1374e8;color:#fff;margin-left:auto}
.setting{display:flex;align-items:center;gap:7px;color:#29496e;font-weight:700;font-size:12px}
.setting select{height:37px;min-width:75px;border:1px solid #cbd9ea;border-radius:8px;background:#fff;padding:0 9px;color:#183b65;font:600 12px 'Noto Sans Bengali',sans-serif}
.setting input{width:16px;height:16px;accent-color:#1374e8}
.paper-wrap{width:100%;padding:0 12px 24px}
.print-page{position:relative;isolation:isolate;width:210mm;min-height:297mm;margin:0 auto 18px;padding:10mm;background:#fff;box-shadow:0 4px 20px #17345613;overflow:visible}
.print-page.side-border{border-left:1px solid #b8c9dd;border-right:1px solid #b8c9dd}
.print-page:before{content:'LiveEducationBD';position:absolute;z-index:-1;left:50%;top:50%;transform:translate(-50%,-50%) rotate(-34deg);font-size:48px;font-weight:800;letter-spacing:2px;white-space:nowrap;color:rgba(35,82,139,.075);pointer-events:none}
.paper-header{text-align:center;border-bottom:1.5px solid #173b66;padding:0 0 10px;margin:0 0 12px;position:relative}
.paper-header h1{font-size:21px;margin:0 0 3px;color:#12345b;font-weight:800}
.paper-header h2{font-size:15px;margin:0 0 5px;font-weight:700}
.paper-header p{font-size:10px;color:#60748f;margin:2px}
.question-grid{display:grid;grid-auto-flow:column;grid-template-columns:repeat(var(--cols),minmax(0,1fr));gap:0 12px;align-content:start;position:relative}
.question{min-width:0;padding:5px 0 6px;border-bottom:1px solid #e1e8f1;break-inside:avoid;page-break-inside:avoid;overflow-wrap:anywhere}
.topic{color:#145c9d;font-size:9.5px;font-weight:700;margin-bottom:2px;overflow-wrap:anywhere}
.qtitle{font-size:10.5px;font-weight:800;margin-bottom:4px;line-height:1.42;overflow-wrap:anywhere}
.options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:3px 8px}
.option{min-width:0;font-size:10px;line-height:1.4;overflow-wrap:anywhere}
.option b{color:#174d85;margin-right:3px}
.question img{max-width:100%;max-height:42mm;object-fit:contain;display:block;margin:3px 0}
.option img{max-height:22mm}
.page-footer{position:relative;text-align:center;color:#8795a8;font-size:8.5px;margin-top:8px;padding-top:4px}
#question-source{display:none}
@media(max-width:850px){.print-page{width:100%;min-height:0;padding:22px 18px}.paper-wrap{padding:0 10px 20px}.toolbar .print-btn{margin-left:0}}
@media(max-width:560px){.toolbar{width:calc(100% - 16px);margin:8px auto 12px;padding:10px}.setting{width:calc(50% - 8px);justify-content:space-between}.setting select{max-width:95px}.toolbar .print-btn{width:100%;margin-left:0}.print-page{padding:17px 12px}.paper-header h1{font-size:18px}.paper-header h2{font-size:13px}.question-grid{gap:0 8px}.qtitle{font-size:10px}.option{font-size:9px}.print-page:before{font-size:28px}}
@page{size:A4;margin:10mm}
@media print{
 html,body{background:#fff!important;margin:0!important;padding:0!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
 .toolbar{display:none!important}.paper-wrap{padding:0!important}
 .print-page{width:auto!important;min-height:277mm!important;margin:0!important;padding:0!important;box-shadow:none!important;break-after:page;page-break-after:always;overflow:visible!important}
 .print-page:last-child{break-after:auto;page-break-after:auto}
 .print-page.side-border{border-left:1px solid #9fb1c7!important;border-right:1px solid #9fb1c7!important;padding-left:3mm!important;padding-right:3mm!important}
 .question-grid{grid-template-columns:repeat(var(--cols),minmax(0,1fr))!important;gap:0 4mm!important}
 .question{padding:3px 0 4px;break-inside:avoid!important;page-break-inside:avoid!important}
 .topic{font-size:8.5pt}.qtitle{font-size:9pt;margin-bottom:2px}.option{font-size:8.2pt}.options{gap:2px 3mm}
 .paper-header{margin-bottom:7px;padding-bottom:6px}.paper-header h1{font-size:16pt}.paper-header h2{font-size:11pt}.paper-header p{font-size:8pt}
 .print-page:before{font-size:42pt;color:rgba(35,82,139,.075)!important}
 .page-footer{font-size:7pt;margin-top:5px}
}
</style>
</head>
<body>
<div class="toolbar" aria-label="PDF print settings">
 <a class="back" href="javascript:window.close()">← ফিরে যান</a>
 <label class="setting">প্রশ্নের কলাম
  <select id="question-columns"><option value="1">১ কলাম</option><option value="2" selected>২ কলাম</option><option value="3">৩ কলাম</option></select>
 </label>
 <label class="setting">প্রতি পৃষ্ঠায় MCQ
  <select id="questions-per-page"><option value="10">১০টি</option><option value="15">১৫টি</option><option value="20" selected>২০টি</option><option value="25">২৫টি</option><option value="30">৩০টি</option></select>
 </label>
 <label class="setting"><input type="checkbox" id="side-border"> পাশের Border</label>
 <button class="print-btn" type="button" id="print-paper">↓ Download / Print / Save as PDF</button>
</div>
<div id="question-source">
 <header class="paper-header" id="paper-header">
  <h1>BCS Question Bank</h1><h2>{{ $exam }} — প্রশ্নপত্র</h2>
  <p>{{ $category }}@if($subcategory) / {{ $subcategory }}@endif</p>
  <p>Routine: {{ $routine }} · মোট প্রশ্ন: {{ count($questions) }} টি</p>
  @if($examDate)<p>পরীক্ষার তারিখ: {{ \Carbon\Carbon::parse($examDate,'Asia/Dhaka')->format('d M Y, h:i A') }}</p>@endif
 </header>
 @foreach($questions as $index=>$q)
 <article class="question">
  <div class="topic">{{ $q['topic'] }}</div>
  <div class="qtitle">{{ $index+1 }}. {!! nl2br(e($q['title'])) !!}</div>
  @if($q['image'])<img src="{{ $q['image'] }}" alt="Question image">@endif
  <div class="options">
   @foreach($q['options'] as $option)
    <div class="option"><b>{{ $option['letter'] }}.</b>{{ $option['text'] }}@if($option['image'])<img src="{{ $option['image'] }}" alt="Option image">@endif</div>
   @endforeach
  </div>
 </article>
 @endforeach
</div>
<div class="paper-wrap" id="paper-pages" aria-live="polite"></div>
<script>
(function(){
 const source=document.getElementById('question-source');
 const examLabel=@json($exam);
 const header=document.getElementById('paper-header');
 const sourceQuestions=Array.from(source.querySelectorAll('.question'));
 const pages=document.getElementById('paper-pages');
 const columns=document.getElementById('question-columns');
 const perPage=document.getElementById('questions-per-page');
 const border=document.getElementById('side-border');
 const printButton=document.getElementById('print-paper');
 function renderPages(){
  const colCount=Math.max(1,Math.min(3,parseInt(columns.value,10)||2));
  const pageSize=Math.max(1,Math.min(30,parseInt(perPage.value,10)||20));
  pages.innerHTML='';
  const total=sourceQuestions.length;
  const pageCount=Math.max(1,Math.ceil(total/pageSize));
  for(let pageIndex=0;pageIndex<pageCount;pageIndex++){
   const page=document.createElement('section');
   page.className='print-page'+(border.checked?' side-border':'');
   page.style.setProperty('--cols',colCount);
   if(pageIndex===0) page.appendChild(header.cloneNode(true));
   else {
    const continuation=document.createElement('div');
    continuation.className='paper-header';
    const continuationTitle=document.createElement('h2');
    continuationTitle.textContent=examLabel+' — প্রশ্নপত্র (চলমান)';
    const continuationMeta=document.createElement('p');
    continuationMeta.textContent='LiveEducationBD · '+total+' টি প্রশ্ন';
    continuation.appendChild(continuationTitle);
    continuation.appendChild(continuationMeta);
    page.appendChild(continuation);
   }
   const grid=document.createElement('div');
   grid.className='question-grid';
   const start=pageIndex*pageSize;
   const chunk=sourceQuestions.slice(start,start+pageSize);
   const rows=Math.ceil(chunk.length/colCount);
   grid.style.gridTemplateColumns='repeat('+colCount+',minmax(0,1fr))';
   grid.style.gridTemplateRows='repeat('+rows+',auto)';
   grid.style.gridAutoFlow='column';
   chunk.forEach(question=>grid.appendChild(question.cloneNode(true)));
   page.appendChild(grid);
   const footer=document.createElement('div');
   footer.className='page-footer';
   footer.textContent='LiveEducationBD.com · '+(pageIndex+1)+' / '+pageCount;
   page.appendChild(footer);
   pages.appendChild(page);
  }
 }
 columns.addEventListener('change',renderPages);
 perPage.addEventListener('change',renderPages);
 border.addEventListener('change',renderPages);
 printButton.addEventListener('click',function(){renderPages();window.print();});
 window.addEventListener('beforeprint',renderPages);
 renderPages();
})();
</script>
</body>
</html>
