<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Exam PDF | {{ $cat->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--blue:#0869d9;--navy:#102f59;--muted:#61799b;--line:#d6e4f5;--pale:#edf4ff;--green:#079b6a}
*{box-sizing:border-box}
html,body{margin:0;min-height:100%;background:#f4f7fc;color:var(--navy);font-family:'Noto Sans Bengali',system-ui,-apple-system,'Segoe UI',sans-serif}
body{padding:0 0 36px}
.page{width:min(1280px,calc(100% - 40px));margin:0 auto;padding:18px 0 30px}
.heading{text-align:center;margin:0 0 24px}
.heading-title{display:flex;justify-content:center;align-items:center;gap:14px;margin:0 0 5px;color:#123b70;font-size:clamp(27px,3vw,39px);font-weight:850;line-height:1.25}
.heading-title .doc-icon{color:var(--blue);font-size:38px;line-height:1}
.heading p{margin:0;color:#647fa5;font-size:14px}
.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;margin-bottom:20px}
.stat{min-height:90px;display:flex;align-items:center;gap:20px;padding:14px 22px;border:1px solid var(--line);border-radius:17px;background:#fff;box-shadow:0 3px 12px #17345608}
.stat:nth-child(2){background:#effcf6}.stat:nth-child(3){background:#fff8ed}
.stat-icon{width:58px;height:58px;flex:0 0 58px;display:grid;place-items:center;border-radius:15px;background:#edf4ff;color:var(--blue);font-size:30px;font-weight:700}
.stat:nth-child(2) .stat-icon{background:#dff8ec;color:#079b6a}.stat:nth-child(3) .stat-icon{background:#fff0d6;color:#d99124}
.stat small{display:block;color:#647da0;font-size:13px}.stat strong{display:block;color:#102e58;font-size:29px;line-height:1.2;font-weight:850}
.card{--accent:#0fa7b6;position:relative;background:#fff;border:1.5px solid #cbdcf1;border-radius:20px;padding:20px 24px 15px;margin:0 0 18px;box-shadow:0 5px 16px #1734560b;overflow:hidden}
.card:nth-of-type(3n+1){--accent:#0869d9}
.card:nth-of-type(3n+2){--accent:#0fa7b6}
.card:nth-of-type(3n){--accent:#f5a51b}
.card:before{content:"";position:absolute;inset:0 auto 0 0;width:5px;background:var(--accent)}
.card-top{display:grid;grid-template-columns:minmax(260px,1.35fr) minmax(210px,1fr) minmax(145px,.7fr) minmax(125px,.65fr);gap:22px;align-items:center;padding:0 4px 17px}
.exam-ident{display:flex;align-items:center;gap:18px;min-width:0}
.exam-number{width:82px;height:68px;flex:0 0 82px;display:grid;place-items:center;border-radius:15px;background:var(--accent);color:#fff;font-size:32px;font-weight:850}
.exam-name{border-left:2px solid #b8cce7;padding-left:20px;min-width:0}
.exam-name small{display:block;color:var(--blue);font-size:14px;font-weight:800}
.exam-name strong{display:block;color:#102e58;font-size:27px;line-height:1.25;font-weight:850;overflow-wrap:anywhere}
.meta{display:flex;align-items:flex-start;gap:12px;min-width:0}
.meta-icon{color:var(--blue);font-size:23px;line-height:1.2;flex:0 0 auto}
.meta small{display:block;color:#647da0;font-size:13px}
.meta strong{display:block;color:#102e58;font-size:16px;font-weight:800;line-height:1.5;overflow-wrap:anywhere}
.status-wrap{display:flex;justify-content:center}
.status{display:inline-flex;align-items:center;gap:7px;border-radius:30px;background:#d9f7e5;color:#087b49;padding:10px 17px;font-size:14px;font-weight:850;white-space:nowrap}
.status.pending{background:#fff0d2;color:#986000}
.action-bar{display:grid;grid-template-columns:minmax(210px,1.1fr) repeat(3,minmax(150px,1fr));gap:16px;align-items:center;padding:13px 15px;background:#edf4ff;border:1px solid #d2e4ff;border-radius:16px}
.action-label{display:flex;align-items:center;gap:15px;padding:0 18px 0 5px;border-right:2px solid #9dbce6;min-height:48px}
.action-label .action-icon{font-size:31px;color:var(--blue);line-height:1}
.action-label strong{display:block;font-size:17px;color:#163b6b;line-height:1.3}
.action-label small{display:block;font-size:13px;color:#61799b}
.actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px;min-width:0}
.btn{min-width:0;min-height:55px;display:flex;justify-content:center;align-items:center;gap:10px;padding:10px 12px;border:1.5px solid var(--blue);border-radius:13px;text-decoration:none;background:#fff;color:var(--blue);font:800 16px 'Noto Sans Bengali',sans-serif;text-align:center;cursor:pointer;transition:filter .15s,transform .15s}
.btn:hover:not(:disabled){filter:brightness(.97);transform:translateY(-1px)}
.btn.create{background:linear-gradient(135deg,#1682f5,#0866d2);color:#fff;border-color:#0869d9}
.btn.download{background:linear-gradient(135deg,#1677dd,#0756b5);color:#fff;border-color:#0756b5}
.btn:disabled,.btn.disabled{background:#e8edf5;color:#8998ad;border-color:#d6e0ec;cursor:not-allowed;opacity:1}
.btn .btn-icon{font-size:21px;line-height:1}
.progress-row{display:flex;align-items:center;gap:28px;flex-wrap:wrap;margin:12px 1px 0;padding:0 4px;color:#64799a;font-size:12px}
.progress-row strong{color:#173b68;font-weight:800}
.progress-row .download-state{margin-left:auto}
.progress-row .working{color:#a56700}.progress-row .ready{color:#07824c}.progress-row .error{color:#c0392b}
.details{border-top:1px dashed #bcd2ed;margin-top:14px;padding-top:10px}
.details summary{font-size:13px;font-weight:850;color:#0869d9;cursor:pointer;list-style:none}
.details summary::-webkit-details-marker{display:none}
.details summary:before{content:"▶";display:inline-block;margin-right:10px;font-size:11px}
.details[open] summary:before{content:"▼"}
.details-body{padding:8px 0 0 21px;color:#61799b;font-size:13px;line-height:1.7}
.empty{padding:30px;background:#fff;border:1px dashed #c8d8e8;border-radius:15px;text-align:center;color:#667e9c}
.back{display:inline-flex;align-items:center;gap:8px;margin-top:4px;text-decoration:none;background:#fff;border:1px solid #b9d2f2;border-radius:12px;padding:11px 18px;color:#176bc0;font-weight:800;font-size:15px}
@media(max-width:1050px){
 .page{width:min(100% - 28px,900px)}
 .card-top{grid-template-columns:minmax(220px,1.2fr) minmax(190px,1fr) minmax(130px,.7fr);gap:15px}
 .status-wrap{grid-column:3;justify-content:flex-start}
 .action-bar{grid-template-columns:1fr}
 .action-label{border-right:0;border-bottom:1px solid #bfd4f0;padding:0 0 10px;min-height:auto}
}
@media(max-width:650px){
 .page{width:calc(100% - 20px);padding-top:14px}
 .heading{margin-bottom:17px}.heading-title{font-size:25px;gap:8px}.heading-title .doc-icon{font-size:29px}.heading p{font-size:11px}
 .stats{gap:7px;margin-bottom:13px}.stat{min-height:75px;padding:9px 8px;gap:7px;border-radius:12px;display:block;text-align:center}
 .stat-icon{width:31px;height:31px;margin:0 auto 4px;border-radius:9px;font-size:17px}.stat small{font-size:9px}.stat strong{font-size:20px}
 .card{padding:13px 12px 12px;border-radius:15px;margin-bottom:12px}
 .card-top{grid-template-columns:1fr 1fr;gap:12px;padding:0 0 12px}
 .exam-ident{grid-column:1/3;gap:11px}.exam-number{width:53px;height:52px;flex-basis:53px;font-size:23px;border-radius:11px}
 .exam-name{padding-left:12px}.exam-name small{font-size:11px}.exam-name strong{font-size:20px}
 .meta{gap:6px}.meta-icon{font-size:16px}.meta small{font-size:9px}.meta strong{font-size:11px}
 .status-wrap{grid-column:1/3;justify-content:flex-start}.status{padding:6px 10px;font-size:10px}
 .action-bar{padding:10px;gap:10px;border-radius:12px}
 .action-label{gap:10px;padding-bottom:8px}.action-label .action-icon{font-size:23px}.action-label strong{font-size:13px}.action-label small{font-size:10px}
 .actions{grid-template-columns:repeat(3,minmax(0,1fr));gap:6px}
 .btn{min-height:44px;padding:6px 3px;gap:4px;font-size:10px;border-radius:9px;white-space:normal}
 .btn .btn-icon{font-size:15px}
 .progress-row{gap:6px 12px;font-size:10px}.progress-row .download-state{margin-left:0}
 .details summary{font-size:10px}.details-body{font-size:10px}
 .back{font-size:12px;padding:8px 12px}
}
</style>
</head>
<body>
<main class="page">
<header class="heading">
 <h1 class="heading-title"><span class="doc-icon">▤</span><span>Exam PDF</span></h1>
 <p>BCS Question Bank — Routine অনুযায়ী সংরক্ষিত প্রশ্নপত্র</p>
</header>
<div class="stats">
 <div class="stat"><span class="stat-icon">▱</span><div><small>মোট পরীক্ষা</small><strong>{{ $items->count() }}</strong></div></div>
 <div class="stat"><span class="stat-icon">▣</span><div><small>প্রকাশিত PDF</small><strong>{{ $items->where('question_count','>',0)->count() }}</strong></div></div>
 <div class="stat"><span class="stat-icon">⌛</span><div><small>প্রকাশের অপেক্ষায়</small><strong>{{ $items->where('question_count',0)->count() }}</strong></div></div>
</div>
@forelse($items as $item)
<article class="card" data-exam-card="{{ $item->id }}">
 <div class="card-top">
  <div class="exam-ident">
   <div class="exam-number">{{ str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT) }}</div>
   <div class="exam-name"><small>{{ $item->routine_name }}</small><strong>{{ $item->exam_name }}</strong></div>
  </div>
  <div class="meta"><span class="meta-icon">▦</span><div><small>PDF Publish Date</small><strong>{{ $item->pdf_create_at ? \Carbon\Carbon::parse($item->pdf_create_at,'Asia/Dhaka')->format('d M Y, h:i A') : 'Routine অনুযায়ী' }}</strong></div></div>
  <div class="meta"><span class="meta-icon">▱</span><div><small>প্রশ্ন সংখ্যা</small><strong>{{ $item->question_count }} টি</strong></div></div>
  <div class="status-wrap"><span class="status {{ $item->question_count ? '' : 'pending' }}">{{ $item->question_count ? '✓ Published' : '◷ Preparing' }}</span></div>
 </div>
 <div class="action-bar">
  <div class="action-label"><span class="action-icon">▱</span><div><strong>Actions</strong><small>পরীক্ষার PDF সম্পর্কিত কার্যক্রম</small></div></div>
  <div class="actions">
   <a class="btn" href="{{ route('student.exam-section.browse-exams',['category'=>$cat->id,'subcategory_id'=>$sub?->id]) }}"><span class="btn-icon">◉</span>View</a>
   @if($item->question_count > 0)
    <button class="btn create js-create-pdf" type="button" data-url="{{ route('student.exam-section.exam-pdf.create',['category'=>$cat->id,'item'=>$item->id]) }}" data-card="{{ $item->id }}"><span class="btn-icon">♧</span><span>Create PDF</span></button>
    <button class="btn download js-download-pdf" type="button" data-card="{{ $item->id }}" disabled><span class="btn-icon">↓</span><span>Download</span></button>
   @else
    <button class="btn disabled" type="button" disabled>প্রশ্ন তৈরি হয়নি</button>
    <button class="btn disabled" type="button" disabled>Download</button>
   @endif
  </div>
 </div>
 @if($item->question_count > 0)
 <div class="progress-row">
  <span>PDF তৈরির সময়: <strong class="js-create-time">এখনো তৈরি হয়নি</strong></span>
  <span class="download-state">Download Status: <strong class="js-download-state">অপেক্ষমাণ</strong></span>
 </div>
 @endif
 <details class="details">
  <summary>Subject &amp; Syllabus / See More</summary>
  <div class="details-body">{{ $cat->name }}@if($sub) — {{ $sub->name }}@endif · {{ $item->routine_name }} · {{ $item->exam_name }}@if($item->notes)<br>{{ $item->notes }}@endif</div>
 </details>
</article>
@empty
<div class="empty">এই বিভাগে এখনো কোনো Published Routine Exam পাওয়া যায়নি।</div>
@endforelse
<a class="back" href="{{ route('student.exam-section.browse-exams',['category'=>$cat->id,'subcategory_id'=>$sub?->id]) }}">← ফিরে যান</a>
</main>
<script>
(function(){
 const previewTabs = new Map();
 const getCard = id => document.querySelector('[data-exam-card="'+id+'"]');
 const setState = (card,text,cls) => {
   const el=card.querySelector('.js-download-state');
   if(el){el.textContent=text;el.className='js-download-state '+(cls||'');}
 };
 document.querySelectorAll('.js-create-pdf').forEach(button=>{
   button.addEventListener('click',async function(){
     const id=this.dataset.card,card=getCard(id),url=this.dataset.url;
     const timeEl=card.querySelector('.js-create-time'),downloadBtn=card.querySelector('.js-download-pdf');
     const started=performance.now();
     const preview=window.open('about:blank','lebd_exam_pdf_'+id);
     if(!preview){setState(card,'Popup blocked — এই সাইটের popup allow করুন','error');return;}
     this.disabled=true;this.innerHTML='<span class="btn-icon">◷</span><span>তৈরি হচ্ছে…</span>';
     downloadBtn.disabled=true;setState(card,'প্রশ্নপত্র তৈরি হচ্ছে…','working');
     timeEl.textContent='0.0 সেকেন্ড';
     const timer=window.setInterval(()=>{timeEl.textContent=((performance.now()-started)/1000).toFixed(1)+' সেকেন্ড';},100);
     try{
       const response=await fetch(url,{method:'GET',credentials:'same-origin',headers:{'Accept':'text/html','X-Requested-With':'XMLHttpRequest'}});
       if(!response.ok||response.redirected)throw new Error('PDF তৈরি করা যায়নি (HTTP '+response.status+')');
       const html=await response.text();
       if(!/<!doctype html|<html/i.test(html))throw new Error('সঠিক PDF Preview পাওয়া যায়নি');
       preview.document.open();preview.document.write(html);preview.document.close();
       previewTabs.set(id,preview);
       window.clearInterval(timer);
       timeEl.textContent=((performance.now()-started)/1000).toFixed(2)+' সেকেন্ড';
       this.innerHTML='<span class="btn-icon">✓</span><span>Created</span>';this.disabled=false;
       downloadBtn.disabled=false;setState(card,'PDF প্রস্তুত — Download চাপুন','ready');
     }catch(error){
       window.clearInterval(timer);timeEl.textContent=((performance.now()-started)/1000).toFixed(2)+' সেকেন্ড';
       try{preview.close();}catch(e){}
       this.innerHTML='<span class="btn-icon">♧</span><span>Create PDF</span>';this.disabled=false;
       setState(card,error.message||'PDF তৈরি ব্যর্থ হয়েছে','error');
     }
   });
 });
 document.querySelectorAll('.js-download-pdf').forEach(button=>{
   button.addEventListener('click',function(){
     const id=this.dataset.card,card=getCard(id),preview=previewTabs.get(id);
     if(!preview||preview.closed){setState(card,'আগে Create PDF করুন','error');return;}
     setState(card,'Download / Print dialog খোলা হয়েছে — Save as PDF নির্বাচন করুন','working');
     preview.focus();preview.print();
   });
 });
})();
</script>
</body>
</html>
