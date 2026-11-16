@extends('student')
@section('title', 'Exam PDF — Live Education BD')
@section('stylesheets')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--blue:#0869d9;--navy:#102f59;--muted:#61799b;--line:#d6e4f5;--pale:#edf4ff;--green:#079b6a}
*{box-sizing:border-box}
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
.card-top{position:relative;display:grid;grid-template-columns:minmax(300px,1.35fr) minmax(210px,1fr);gap:22px;align-items:center;padding:0 150px 17px 4px}
.exam-ident{display:flex;align-items:center;gap:18px;min-width:0}
.exam-number{width:82px;height:68px;flex:0 0 82px;display:grid;place-items:center;border-radius:15px;background:var(--accent);color:#fff;font-size:32px;font-weight:850}
.exam-name{border-left:2px solid #b8cce7;padding-left:20px;min-width:0}
.exam-name small{display:block;color:var(--blue);font-size:14px;font-weight:800}
.exam-name strong{display:block;color:#102e58;font-size:27px;line-height:1.25;font-weight:850;overflow-wrap:anywhere}
.exam-count{display:flex;flex-direction:column;justify-content:center;align-items:center;min-width:54px;margin-left:4px;padding:5px 9px;border-left:1px solid #d4e2f4;color:#173b68}
.exam-count small{font-size:11px;color:#647da0;white-space:nowrap}.exam-count strong{font-size:16px;font-weight:850;white-space:nowrap}
.meta{display:flex;align-items:flex-start;gap:12px;min-width:0}
.meta-icon{color:var(--blue);font-size:23px;line-height:1.2;flex:0 0 auto}
.meta small{display:block;color:#647da0;font-size:13px}
.meta strong{display:block;color:#102e58;font-size:16px;font-weight:800;line-height:1.5;overflow-wrap:anywhere}
.status-wrap{position:absolute;top:2px;right:4px;display:flex;justify-content:flex-end}
.status{display:inline-flex;align-items:center;gap:7px;border-radius:30px;background:#d9f7e5;color:#087b49;padding:10px 17px;font-size:14px;font-weight:850;white-space:nowrap}
.status.pending{background:#fff0d2;color:#986000}
.status.ready{background:#d9f7e5;color:#087b49}
.publish-countdown{display:block;margin-top:4px;color:#a36a00;font-size:10px;font-weight:700;white-space:nowrap}
.publish-countdown.due{color:#07824c}
.publish-info-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:14px;align-items:center;min-width:0;width:100%}
.publish-date-column,.publish-countdown-column{display:flex;flex-direction:column;gap:3px;min-width:0}
.publish-countdown-column{border-left:1px solid #d5e2f1;padding-left:14px}
.publish-countdown{display:block;margin-top:0;color:#a36a00;font-size:12px;font-weight:800;white-space:normal;line-height:1.35}
.publish-countdown b{font-weight:850}
.publish-countdown.due{color:#07824c}
.publish-no-countdown{font-size:11px;color:#7186a2;font-weight:700}

.action-bar{display:grid;grid-template-columns:minmax(210px,1fr) minmax(360px,1.35fr);gap:16px;align-items:center;padding:13px 15px;background:#edf4ff;border:1px solid #d2e4ff;border-radius:16px}
.action-label{display:flex;align-items:center;gap:15px;padding:0 18px 0 5px;border-right:2px solid #9dbce6;min-height:48px}
.action-label .action-icon{font-size:31px;color:var(--blue);line-height:1}
.action-label strong{display:block;font-size:17px;color:#163b6b;line-height:1.3}
.action-label small{display:block;font-size:13px;color:#61799b}
.actions{display:grid;grid-template-columns:repeat(3,minmax(105px,1fr));gap:10px;min-width:0;justify-content:end}\n.actions-three .btn{width:100%}
.actions-admin{grid-template-columns:repeat(4,minmax(100px,1fr))}
.reset-form{margin:0;min-width:0}
.btn.reset{width:100%;background:#fff5f3;color:#c0392b;border-color:#f1b8ae}
.btn{min-width:0;min-height:55px;display:flex;justify-content:center;align-items:center;gap:10px;padding:10px 12px;border:1.5px solid var(--blue);border-radius:13px;text-decoration:none;background:#fff;color:var(--blue);font:800 16px 'Noto Sans Bengali',sans-serif;text-align:center;cursor:pointer;transition:filter .15s,transform .15s}
.btn:hover:not(:disabled){filter:brightness(.97);transform:translateY(-1px)}
.btn.create{background:linear-gradient(135deg,#1682f5,#0866d2);color:#fff;border-color:#0869d9}
.btn.download{background:linear-gradient(135deg,#1677dd,#0756b5);color:#fff;border-color:#0756b5}
.btn:disabled,.btn.disabled{background:#e8edf5;color:#8998ad;border-color:#d6e0ec;cursor:not-allowed;opacity:1;pointer-events:none}
.btn .btn-icon{font-size:21px;line-height:1}
.progress-row{display:flex;align-items:center;gap:28px;flex-wrap:wrap;margin:12px 1px 0;padding:0 4px;color:#64799a;font-size:12px}
.progress-row strong{color:#173b68;font-weight:800}
.progress-row .download-state{margin-left:auto}
.progress-row .working{color:#a56700}.progress-row .ready{color:#07824c}.progress-row .error{color:#c0392b}
.progress-wrap{display:none;flex:1 1 100%;min-width:180px;margin-top:3px}
.progress-wrap.active{display:block}
.progress-track{height:9px;border-radius:20px;background:#e3ebf5;overflow:hidden}
.progress-fill{height:100%;width:0;background:linear-gradient(90deg,#1677e8,#08a878);border-radius:20px;transition:width .12s}
.progress-caption{display:flex;justify-content:space-between;gap:10px;margin-top:4px;font-size:11px;color:#60748f}
.pdf-modal{position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(10,28,52,.48)}
.pdf-modal.open{display:flex}
.pdf-modal-panel{width:min(460px,100%);background:#fff;border-radius:18px;padding:22px;box-shadow:0 18px 60px #071c3880}
.pdf-modal-panel h2{font-size:20px;margin:0 0 6px;color:#123b70}
.pdf-modal-panel p{font-size:12px;line-height:1.6;color:#61799b;margin:0 0 16px}
.pdf-settings{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.pdf-settings label{display:flex;flex-direction:column;gap:6px;color:#29496e;font-size:12px;font-weight:750}
.pdf-settings select{height:42px;border:1px solid #cbd9ea;border-radius:9px;padding:0 10px;background:#fff;color:#183b65;font:600 13px 'Noto Sans Bengali',sans-serif}
.pdf-settings .full{grid-column:1/-1;display:flex;flex-direction:row;align-items:center}
.pdf-settings input{accent-color:#1374e8;width:17px;height:17px}
.modal-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:20px}
.modal-actions button{border:0;border-radius:10px;padding:11px 16px;font:750 13px 'Noto Sans Bengali',sans-serif;cursor:pointer}
.modal-cancel{background:#edf2f8;color:#405a79}.modal-download{background:#1374e8;color:#fff}
.shared-note{margin-top:13px!important;padding:9px 11px;background:#eef8f3;border-radius:9px;color:#087b4b!important}
.pdf-package-lock{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:0 0 18px;padding:16px 18px;border:1px solid #f2d49b;border-radius:15px;background:linear-gradient(110deg,#fff8e9,#fff);color:#704b08;box-shadow:0 5px 15px #805b1010}.pdf-package-lock strong{font-size:16px}.pdf-package-lock p{margin:4px 0 0;font-size:12px;line-height:1.6;color:#80652f}.pdf-package-lock a{flex:none;display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border-radius:10px;background:linear-gradient(120deg,#ff8418,#ff4c37);color:#fff!important;font-weight:800;font-size:13px;text-decoration:none;box-shadow:0 4px 0 #c94c1c}@media(max-width:600px){.pdf-package-lock{align-items:stretch;flex-direction:column;padding:12px}.pdf-package-lock strong{font-size:14px}.pdf-package-lock a{width:100%;font-size:12px}}

.details{border-top:1px dashed #bcd2ed;margin-top:14px;padding-top:10px}
.details summary{font-size:13px;font-weight:850;color:#0869d9;cursor:pointer;list-style:none}
.details summary::-webkit-details-marker{display:none}
.details summary:before{content:"▶";display:inline-block;margin-right:10px;font-size:11px}
.details[open] summary:before{content:"▼"}
.details-body{padding:8px 0 0 21px;color:#61799b;font-size:13px;line-height:1.7}
.empty{padding:30px;background:#fff;border:1px dashed #c8d8e8;border-radius:15px;text-align:center;color:#667e9c}
.page-topbar{display:flex;justify-content:flex-start;align-items:center;margin:0 0 14px}.back{display:inline-flex;align-items:center;gap:8px;margin-top:0;text-decoration:none;background:#fff;border:1px solid #b9d2f2;border-radius:12px;padding:11px 18px;color:#176bc0;font-weight:800;font-size:15px}
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
 .card-top{grid-template-columns:minmax(0,1fr);gap:9px;padding:0 86px 12px 0}
 .exam-ident{grid-column:1;gap:7px;min-width:0;align-items:center}
 .exam-number{width:48px;height:48px;flex:0 0 48px;font-size:21px;border-radius:10px}
 .exam-name{padding-left:9px;min-width:0}.exam-name small{font-size:10px}.exam-name strong{font-size:17px;line-height:1.15}
 .exam-count{min-width:42px;margin-left:0;padding:3px 5px}.exam-count small{font-size:9px}.exam-count strong{font-size:12px}
 .publish-meta{padding-left:2px}
 .publish-info-grid{grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:7px}
 .publish-date-column small,.publish-countdown-column small{font-size:8px;line-height:1.25}
 .publish-date-column strong{font-size:9px;line-height:1.3;white-space:normal}
 .publish-countdown-column{padding-left:7px}
 .publish-countdown{font-size:9px;line-height:1.35;white-space:normal;overflow-wrap:anywhere}
 .publish-no-countdown{font-size:9px}

 .meta{gap:6px}.meta-icon{font-size:15px}.meta small{font-size:9px}.meta strong{font-size:10px}
 .status-wrap{top:0;right:0;justify-content:flex-end}.status{padding:5px 8px;font-size:9px}
 .action-bar{padding:8px;gap:8px;border-radius:11px}
 .action-label{gap:8px;padding:0 0 7px;border-bottom:1px solid #bfd4f0;min-height:auto}
 .action-label .action-icon{font-size:20px}.action-label strong{font-size:12px}.action-label small{font-size:9px}
 .actions,.actions-admin,.actions-three{display:flex;flex-wrap:nowrap;align-items:center;justify-content:flex-start;gap:6px;width:100%;min-width:0}
 .actions .btn,.actions-three .btn,.actions-admin .btn{width:auto;min-width:0;min-height:38px;flex:0 0 auto;padding:5px 8px;gap:4px;font-size:9px;border-radius:8px;white-space:nowrap}
 .actions .reset-form,.actions-admin .reset-form{display:block;flex:0 0 auto;width:auto;min-width:0;margin:0}
 .actions .reset-form .btn{width:auto;min-width:0}
 .btn .btn-icon{font-size:13px}
 .progress-row{gap:6px 12px;font-size:10px}.progress-row .download-state{margin-left:0}
 .pdf-modal-panel{padding:17px}.pdf-settings{grid-template-columns:1fr 1fr}.progress-wrap{flex-basis:100%}
 .details summary{font-size:10px}.details-body{font-size:10px}
 .back{font-size:12px;padding:8px 12px}
}
.exam-pdf-page{min-height:calc(100vh - 70px);background:#f4f7fc;padding:1px 0 36px;color:var(--navy);font-family:'Noto Sans Bengali',system-ui,-apple-system,'Segoe UI',sans-serif}

</style>
@endsection

@section('content')
<div class="exam-pdf-page">
@php
 $webUser = \Illuminate\Support\Facades\Auth::guard('web')->user();
 $canResetPdf = $webUser && $webUser->authorizeRoles(['SuperAdmin', 'Admin']);
@endphp
<main class="page">
@if(session('status'))
 <div style="margin:0 0 14px;padding:12px 16px;border:1px solid #b8e5ce;border-radius:12px;background:#edfff5;color:#087b49;font-weight:700;">{{ session('status') }}</div>
@endif
<div class="page-topbar"><a class="back" href="{{ route('student.exam-section.browse-exams',['category'=>$cat->id,'subcategory_id'=>$sub?->id]) }}">← ফিরে যান</a></div>
<header class="heading">
 <h1 class="heading-title"><span class="doc-icon">▤</span><span>Exam PDF</span></h1>
 <p>BCS Question Bank — Routine অনুযায়ী সংরক্ষিত প্রশ্নপত্র</p>
</header>
@if(!$hasPdfAccess)
 <section class="pdf-package-lock" role="alert">
  <div><strong>PDF তৈরি ও Download করতে প্যাকেজ প্রয়োজন</strong><p>আপনার এই Category/Sub-category-এর জন্য কোনো সক্রিয় Exam Package পাওয়া যায়নি। প্যাকেজ কিনলে PDF Create, View ও Download করতে পারবেন।</p></div>
  <a href="{{ route('student.exam-section.packages', ['notice' => 'pdf']) }}">🛒 প্যাকেজ কিনুন</a>
 </section>
@endif
<div class="stats">
 <div class="stat"><span class="stat-icon">▱</span><div><small>মোট পরীক্ষা</small><strong>{{ $items->count() }}</strong></div></div>
 <div class="stat"><span class="stat-icon">▣</span><div><small>প্রকাশিত PDF</small><strong>{{ $items->where('shared_pdf_ready',true)->count() }}</strong></div></div>
 <div class="stat"><span class="stat-icon">⌛</span><div><small>প্রকাশের অপেক্ষায়</small><strong>{{ $items->filter(fn($row) => !$row->pdf_is_due && !empty($row->pdf_publish_timestamp))->count() }}</strong></div></div>
</div>
@forelse($items as $item)
<article class="card" data-exam-card="{{ $item->id }}">
 <div class="card-top">
  <div class="exam-ident">
   <div class="exam-number">{{ str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT) }}</div>
   <div class="exam-name"><small>{{ $item->routine_name }}</small><strong>{{ $item->exam_name }}</strong></div>
   <div class="exam-count"><small>প্রশ্ন</small><strong>{{ $item->question_count }} টি</strong></div>
  </div>
  <div class="meta publish-meta">
   <span class="meta-icon">▦</span>
   <div class="publish-info-grid">
    <div class="publish-date-column">
     <small>PDF Publish Date &amp; Time</small>
     <strong>{{ $item->pdf_create_at ? \Carbon\Carbon::parse($item->pdf_create_at, 'Asia/Dhaka')->timezone('Asia/Dhaka')->format('d M Y, h:i A') : 'Schedule নির্ধারিত নয়' }}</strong>
    </div>
    <div class="publish-countdown-column">
     <small>প্রকাশ হতে বাকি</small>
     @if($item->pdf_publish_timestamp)
      <div class="publish-countdown js-publish-countdown {{ $item->pdf_is_due ? 'due' : '' }}" data-publish-at="{{ $item->pdf_publish_timestamp }}">
       <b class="js-countdown-value">{{ $item->pdf_is_due ? 'প্রকাশের সময় হয়েছে' : 'সময় গণনা হচ্ছে…' }}</b>
      </div>
     @else
      <strong class="publish-no-countdown">সময় নির্ধারিত নয়</strong>
     @endif
    </div>
   </div>
  </div>
  <div class="status-wrap">
   <span class="status {{ !$item->pdf_is_due ? 'pending' : '' }} {{ $item->shared_pdf_ready ? 'ready' : '' }}">
    @if(!$item->pdf_is_due) ◷ Upcoming @elseif($item->shared_pdf_ready) ✓ Published @elseif($item->question_count > 0) ✓ Publish Time @else ◷ Preparing @endif
   </span>
  </div>
 </div>
 <div class="action-bar">
  <div class="action-label"><span class="action-icon">▱</span><div><strong>Actions</strong><small>একবার তৈরি হলে এই Routine-এর সব শিক্ষার্থী একই PDF পাবে</small></div></div>
  <div class="actions {{ $canResetPdf ? 'actions-admin' : 'actions-three' }}">
   @if(!$hasPdfAccess)
    <a class="btn create" href="{{ route('student.exam-section.packages', ['notice' => 'pdf']) }}"><span class="btn-icon">🔒</span><span>প্যাকেজ কিনুন</span></a>
   @else
   @if($item->question_count > 0 && !$item->shared_pdf_ready)
    <button class="btn create js-create-pdf {{ $item->pdf_is_due ? '' : 'disabled' }}" type="button"
      data-build-url="{{ route('student.exam-section.exam-pdf.build',['category'=>$cat->id,'item'=>$item->id]) }}"
      data-card="{{ $item->id }}" data-publish-at="{{ $item->pdf_publish_timestamp ?? '' }}"
      {{ $item->pdf_is_due ? '' : 'disabled' }}>
      <span class="btn-icon">{{ $item->pdf_is_due ? '＋' : '🔒' }}</span>
      <span>{{ $item->pdf_is_due ? 'Create PDF' : 'Upcoming' }}</span>
    </button>
   @elseif($item->shared_pdf_ready)
    <span class="btn disabled">✓ PDF Published</span>
   @else
    <button class="btn disabled" type="button" disabled>প্রশ্ন তৈরি হয়নি</button>
   @endif
   <a class="btn js-view-pdf {{ $item->shared_pdf_ready ? '' : 'disabled' }}"
      {{ $item->shared_pdf_ready ? 'target=_blank rel=noopener' : '' }}
      href="{{ $item->shared_pdf_ready ? route('student.exam-section.exam-pdf.view',['category'=>$cat->id,'item'=>$item->id]) : 'javascript:void(0)' }}"
      aria-disabled="{{ $item->shared_pdf_ready ? 'false' : 'true' }}"><span class="btn-icon">◉</span>View PDF</a>
   <button class="btn download js-download-pdf" type="button"
      data-download-url="{{ route('student.exam-section.exam-pdf.download',['category'=>$cat->id,'item'=>$item->id]) }}"
      data-card="{{ $item->id }}" {{ $item->shared_pdf_ready ? '' : 'disabled' }}>
      <span class="btn-icon">↓</span><span>Download PDF</span>
   </button>
   @if($canResetPdf)
    <form class="reset-form" method="POST" action="{{ route('student.exam-section.exam-pdf.reset',['category'=>$cat->id,'item'=>$item->id]) }}"
      style="{{ $item->shared_pdf_ready ? '' : 'display:none' }}"
      onsubmit="return confirm('এই Exam-এর বর্তমান Shared PDF মুছে Reset করবেন? এরপর আবার Create PDF করতে হবে।');">
      @csrf
      <button class="btn reset" type="submit"><span class="btn-icon">↻</span><span>Reset PDF</span></button>
    </form>
   @endif
   @endif
  </div>
 </div>
 @if($item->question_count > 0 && $hasPdfAccess)
 <div class="progress-row">
  <span>PDF Status: <strong class="js-download-state {{ $item->shared_pdf_ready ? 'ready' : '' }}">{{ $item->shared_pdf_ready ? 'Shared PDF প্রস্তুত — সবাই Download করতে পারবে' : 'এখনো PDF তৈরি হয়নি' }}</strong></span>
  <span>সময়: <strong class="js-download-time">—</strong></span>
  <div class="progress-wrap" aria-live="polite">
   <div class="progress-track"><div class="progress-fill js-progress-fill"></div></div>
   <div class="progress-caption"><span class="js-progress-label">ডাউনলোড অগ্রগতি</span><strong class="js-progress-percent">0%</strong></div>
  </div>
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
</main>
<div class="pdf-modal" id="pdf-settings-modal" role="dialog" aria-modal="true" aria-labelledby="pdf-modal-title">
 <div class="pdf-modal-panel">
  <h2 id="pdf-modal-title">PDF Download Settings</h2>
  <p>প্রথম শিক্ষার্থী এখানে PDF তৈরি করবে। তৈরি হলে এই Routine-এর সব শিক্ষার্থী একই PDF দেখতে ও Download করতে পারবে।</p>
  <div class="pdf-settings">
   <label>প্রশ্নের কলাম
    <select id="pdf-columns"><option value="1">১ কলাম</option><option value="2" selected>২ কলাম</option><option value="3">৩ কলাম</option></select>
   </label>
   <label>প্রতি পৃষ্ঠায় MCQ
    <select id="pdf-per-page"><option value="10">১০টি</option><option value="15">১৫টি</option><option value="20" selected>২০টি</option><option value="25">২৫টি</option><option value="30">৩০টি</option></select>
   </label>
   <label class="full"><input type="checkbox" id="pdf-outer-border"> প্রতিটি A4 পৃষ্ঠায় বাইরের Border রাখুন</label>
  </div>
  <p class="shared-note">এই Routine-এর জন্য PDF একবার তৈরি হবে। অন্য শিক্ষার্থীদের আলাদা করে PDF তৈরি করতে হবে না।</p>
  <div class="modal-actions">
   <button type="button" class="modal-cancel" id="pdf-modal-cancel">বাতিল</button>
   <button type="button" class="modal-download" id="pdf-modal-confirm">Create PDF তৈরি করুন</button>
  </div>
 </div>
</div>
<script>
(function(){
 const modal=document.getElementById('pdf-settings-modal');
 const columns=document.getElementById('pdf-columns');
 const perPage=document.getElementById('pdf-per-page');
 const outerBorder=document.getElementById('pdf-outer-border');
 const confirmButton=document.getElementById('pdf-modal-confirm');
 const csrf=@json(csrf_token());
 let activeCreate=null;
 const cardFor=id=>document.querySelector('[data-exam-card="'+id+'"]');
 const state=(card,message,kind)=>{
   const el=card&&card.querySelector('.js-download-state');
   if(el){el.textContent=message;el.className='js-download-state '+(kind||'');}
 };
 const progress=(card,loaded,total,indeterminate)=>{
   const wrap=card&&card.querySelector('.progress-wrap');
   const fill=card&&card.querySelector('.js-progress-fill');
   const label=card&&card.querySelector('.js-progress-label');
   const pct=card&&card.querySelector('.js-progress-percent');
   if(wrap)wrap.classList.add('active');
   const value=total>0?Math.min(100,Math.floor(loaded/total*100)):null;
   if(fill)fill.style.width=(indeterminate?35:(value===null?0:value))+'%';
   if(label)label.textContent=indeterminate?'PDF তৈরি হচ্ছে…':(total>0?Math.round(loaded/1024)+' KB / '+Math.round(total/1024)+' KB':'');
   if(pct)pct.textContent=indeterminate?'…':(value===null?'0%':value+'%');
 };
 const closeModal=()=>{modal.classList.remove('open');activeCreate=null;};

 // Update the countdown every second and unlock Create PDF exactly at publish time.
 const updatePublishCountdowns=()=>{
   const now=Date.now();
   document.querySelectorAll('.js-publish-countdown').forEach(el=>{
     const at=Number(el.dataset.publishAt||0);
     const value=el.querySelector('.js-countdown-value');
     if(!at||!value)return;
     const diff=at-now;
     const card=el.closest('[data-exam-card]');
     const create=card&&card.querySelector('.js-create-pdf');
     const badge=card&&card.querySelector('.status-wrap .status');
     if(diff<=0){
       el.classList.add('due');
       el.innerHTML='<b class="js-countdown-value">প্রকাশের সময় হয়েছে</b>';
       if(create){
         create.disabled=false;
         create.classList.remove('disabled');
         create.innerHTML='<span class="btn-icon">＋</span><span>Create PDF</span>';
       }
       if(badge&&!badge.classList.contains('ready')){
         badge.classList.remove('pending');
         badge.textContent='✓ Publish Time';
       }
       return;
     }
     const total=Math.floor(diff/1000);
     const days=Math.floor(total/86400);
     const hours=Math.floor((total%86400)/3600);
     const minutes=Math.floor((total%3600)/60);
     const seconds=total%60;
     value.textContent=(days?days+' দিন ':'')+hours+' ঘণ্টা '+minutes+' মিনিট '+seconds+' সেকেন্ড';
   });
 };
 updatePublishCountdowns();
 setInterval(updatePublishCountdowns,1000);

 document.querySelectorAll('.js-create-pdf').forEach(button=>{
   button.addEventListener('click',function(){
     if(this.disabled)return;
     activeCreate=this;
     modal.classList.add('open');
   });
 });
 document.getElementById('pdf-modal-cancel').addEventListener('click',closeModal);
 modal.addEventListener('click',e=>{if(e.target===modal)closeModal();});

 confirmButton.addEventListener('click',async function(){
   if(!activeCreate)return;
   const button=activeCreate;
   const card=cardFor(button.dataset.card);
   const started=performance.now();
   const timeEl=card.querySelector('.js-download-time');
   const original=button.innerHTML;
   const payload=new URLSearchParams({
     _token:csrf,
     columns:columns.value,
     per_page:perPage.value,
     outer_border:outerBorder.checked?'1':'0'
   });
   closeModal();
   button.disabled=true;
   button.innerHTML='<span class="btn-icon">◷</span><span>PDF তৈরি হচ্ছে…</span>';
   state(card,'PDF তৈরি হচ্ছে — প্রথমবার তৈরি হলে সবাই ব্যবহার করতে পারবে','working');
   progress(card,0,0,true);
   const timer=setInterval(()=>{if(timeEl)timeEl.textContent=((performance.now()-started)/1000).toFixed(1)+' সেকেন্ড';},150);
   try{
     const response=await fetch(button.dataset.buildUrl,{
       method:'POST',
       credentials:'same-origin',
       headers:{
         'Accept':'application/json',
         'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8',
         'X-CSRF-TOKEN':csrf,
         'X-Requested-With':'XMLHttpRequest'
       },
       body:payload.toString()
     });
     const data=await response.json().catch(()=>({}));
     if(!response.ok||!data.success)throw new Error(data.message||'PDF তৈরি করা যায়নি');
     const view=card.querySelector('.js-view-pdf');
     const download=card.querySelector('.js-download-pdf');
     if(view){view.href=data.view_url;view.classList.remove('disabled');view.setAttribute('target','_blank');view.setAttribute('rel','noopener');view.setAttribute('aria-disabled','false');}
     if(download)download.disabled=false;
     button.style.display='none';
     const resetForm=card.querySelector('.reset-form');
     if(resetForm)resetForm.style.display='block';
     state(card,data.message||'PDF তৈরি সম্পূর্ণ হয়েছে — সবাই Download করতে পারবে','ready');
     progress(card,1,1,false);
     const label=card.querySelector('.js-progress-label');
     const percent=card.querySelector('.js-progress-percent');
     if(label)label.textContent='Shared PDF প্রস্তুত — সব শিক্ষার্থী Download করতে পারবে';
     if(percent)percent.textContent='100%';
     if(timeEl)timeEl.textContent=((performance.now()-started)/1000).toFixed(2)+' সেকেন্ড';
     const createProgressWrap=card.querySelector('.progress-wrap');
     setTimeout(()=>{
       if(createProgressWrap)createProgressWrap.classList.remove('active');
       if(timeEl)timeEl.textContent='—';
     },2500);
   }catch(error){
     state(card,error.message||'PDF তৈরি ব্যর্থ হয়েছে','error');
     button.disabled=false;
     button.innerHTML=original;
     const failedProgress=card.querySelector('.progress-wrap');
     if(failedProgress)failedProgress.classList.remove('active');
     if(timeEl)timeEl.textContent='—';
   }finally{
     clearInterval(timer);
   }
 });

 document.querySelectorAll('.js-download-pdf').forEach(button=>{
   button.addEventListener('click',async function(){
     if(this.disabled)return;
     const card=cardFor(this.dataset.card);
     const started=performance.now();
     const timeEl=card.querySelector('.js-download-time');
     const original=this.innerHTML;
     this.disabled=true;
     this.innerHTML='<span class="btn-icon">◷</span><span>Download হচ্ছে…</span>';
     progress(card,0,0,true);
     state(card,'Shared PDF Download হচ্ছে…','working');
     const timer=setInterval(()=>{if(timeEl)timeEl.textContent=((performance.now()-started)/1000).toFixed(1)+' সেকেন্ড';},150);
     try{
       const response=await fetch(this.dataset.downloadUrl,{
         credentials:'same-origin',
         headers:{'Accept':'application/pdf','X-Requested-With':'XMLHttpRequest'}
       });
       if(!response.ok){
         const message=await response.text();
         throw new Error(message.replace(/<[^>]*>/g,' ').trim().slice(0,220)||'PDF Download করা যায়নি');
       }
       if(!(response.headers.get('Content-Type')||'').includes('application/pdf'))throw new Error('সার্ভার PDF ফাইল পাঠায়নি');
       const total=parseInt(response.headers.get('Content-Length')||'0',10);
       const reader=response.body&&response.body.getReader?response.body.getReader():null;
       let blob;
       if(reader){
         const chunks=[];let loaded=0;
         while(true){
           const part=await reader.read();if(part.done)break;
           chunks.push(part.value);loaded+=part.value.byteLength;
           progress(card,loaded,total,false);
         }
         blob=new Blob(chunks,{type:'application/pdf'});
       }else{blob=await response.blob();progress(card,blob.size,blob.size,false);}
       const disposition=response.headers.get('Content-Disposition')||'';
       const match=disposition.match(/filename="?([^";]+)"?/i);
       const filename=match?match[1]:'Exam-Question-Paper.pdf';
       const objectUrl=URL.createObjectURL(blob);
       const anchor=document.createElement('a');anchor.href=objectUrl;anchor.download=filename;
       document.body.appendChild(anchor);anchor.click();anchor.remove();
       setTimeout(()=>URL.revokeObjectURL(objectUrl),30000);
       progress(card,blob.size,blob.size,false);
       state(card,'ডাউনলোড সম্পূর্ণ হয়েছে — এই PDF সব শিক্ষার্থীর জন্য একই','ready');
       if(timeEl)timeEl.textContent=((performance.now()-started)/1000).toFixed(2)+' সেকেন্ড';
       this.innerHTML='<span class="btn-icon">↓</span><span>Download PDF</span>';
       const progressWrap=card.querySelector('.progress-wrap');
       setTimeout(()=>{
         if(progressWrap)progressWrap.classList.remove('active');
         if(timeEl)timeEl.textContent='—';
       },2500);
     }catch(error){
       state(card,error.message||'Download ব্যর্থ হয়েছে','error');
       this.innerHTML=original;
     }finally{
       clearInterval(timer);
       this.disabled=false;
     }
   });
 });
})();
</script>
</div>
@endsection
