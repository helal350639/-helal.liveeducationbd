@extends('student')
@section('title','Exam PDF — '.$cat->name)
@section('stylesheets')
<style>
.lebd-exam-pdf-page{max-width:1120px;margin:0 auto;padding:18px 16px 55px;color:#17253b;font-family:system-ui,-apple-system,'Segoe UI','Noto Sans Bengali',sans-serif}
.lebd-exam-pdf-page *{box-sizing:border-box}.lebd-exam-pdf-page a{text-decoration:none}
.lebd-ep-back{display:inline-flex;align-items:center;padding:10px 16px;border:1px solid #cbdcf1;border-radius:11px;background:#fff;color:#126bd0;font-weight:800;margin-bottom:15px}
.lebd-ep-heading{text-align:center;margin:6px 0 23px}.lebd-ep-heading h1{font-size:clamp(24px,4vw,34px);font-weight:850;color:#102e57;margin:0 0 5px}.lebd-ep-heading p{color:#71839c;margin:0;font-size:13px}
.lebd-ep-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:15px}.lebd-ep-stat{display:flex;align-items:center;gap:11px;min-height:70px;padding:12px 14px;border:1px solid #d7e4f5;border-radius:13px;background:#fff}.lebd-ep-stat:nth-child(2){background:#f1fbf6}.lebd-ep-stat:nth-child(3){background:#fff9ed}.lebd-ep-stat:nth-child(4){background:#f1f6ff}.lebd-ep-stat i{display:grid;place-items:center;width:40px;height:40px;border-radius:11px;background:#e5f1ff;color:#0c70df;font-style:normal;font-size:20px}.lebd-ep-stat:nth-child(2) i{background:#d8f8e7;color:#07965a}.lebd-ep-stat:nth-child(3) i{background:#ffefc9;color:#d98b00}.lebd-ep-stat small{display:block;color:#526985;font-size:11px;font-weight:700}.lebd-ep-stat b{font-size:22px;color:#142f54;line-height:1.2}
.lebd-ep-tools{display:flex;gap:10px;align-items:center;padding:13px;background:#fff;border:1px solid #d7e4f5;border-radius:13px;margin-bottom:14px}.lebd-ep-tools input,.lebd-ep-tools select{height:42px;border:1px solid #cbd9eb;border-radius:9px;padding:0 12px;color:#183655;background:#fff;min-width:0}.lebd-ep-tools input{flex:1}.lebd-ep-refresh{height:42px;width:44px;border:1px solid #a9c9f1;border-radius:10px;background:#fff;color:#086bd8;font-size:19px;cursor:pointer}
.lebd-ep-card{background:#f8fdff;border:1px solid #cfe4f1;border-left:4px solid #0fa5b4;border-radius:14px;padding:14px 16px 11px;margin:10px 0}.lebd-ep-card:nth-of-type(3n+2){background:#fffcf7;border-color:#ffdda1;border-left-color:#ffa61e}.lebd-ep-card:nth-of-type(3n){background:#f5fff9;border-color:#b6efd2;border-left-color:#0bb981}.lebd-ep-main{display:grid;grid-template-columns:50px minmax(120px,1.25fr) minmax(150px,1.2fr) minmax(130px,1fr) minmax(145px,1fr);gap:12px;align-items:center}.lebd-ep-number{display:grid;place-items:center;min-height:55px;border-radius:10px;background:#0ea5b6;color:#fff;font-size:22px;font-weight:850}.lebd-ep-card:nth-of-type(3n+2) .lebd-ep-number{background:#ffa61b}.lebd-ep-card:nth-of-type(3n) .lebd-ep-number{background:#08ac78}.lebd-ep-name{font-size:14px;font-weight:850;color:#0b2547;overflow-wrap:anywhere}.lebd-ep-name small{display:block;font-size:10px;color:#1675ce;margin-bottom:4px}.lebd-ep-meta small{display:block;color:#6d829d;font-size:11px}.lebd-ep-meta strong{display:block;color:#142f54;font-size:12px;line-height:1.5;overflow-wrap:anywhere}.lebd-ep-status{display:inline-block;padding:6px 10px;border-radius:9px;background:#e0f8e9;color:#07804a;font-size:11px;font-weight:850}.lebd-ep-status.pending{background:#fff1d4;color:#9a6500}.lebd-ep-actions{display:flex;gap:7px}.lebd-ep-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:8px 12px;border-radius:9px;background:#116fe4;color:#fff!important;font-size:12px;font-weight:850;white-space:nowrap}.lebd-ep-btn.alt{background:#e7f1ff;color:#075dc1!important}.lebd-ep-btn.disabled{background:#e9edf3;color:#8190a3!important;cursor:not-allowed}.lebd-ep-more{border-top:1px dashed #cbdff5;margin-top:11px;padding-top:9px;color:#0872dc;font-size:11px;font-weight:800}.lebd-ep-more summary{cursor:pointer;list-style:none}.lebd-ep-more summary::-webkit-details-marker{display:none}.lebd-ep-detail{padding:8px 0 2px;color:#526985;font-weight:500;line-height:1.7}.lebd-ep-empty{text-align:center;padding:30px 16px;background:#fff;border:1px dashed #cbd9e9;border-radius:13px;color:#71839b}
@media(max-width:850px){.lebd-ep-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-ep-main{grid-template-columns:48px minmax(0,1fr) minmax(0,1fr);gap:9px}.lebd-ep-name{grid-column:2/4}.lebd-ep-actions{grid-column:3;grid-row:3}.lebd-ep-status-wrap{grid-column:1/3;grid-row:3}}
@media(max-width:520px){.lebd-exam-pdf-page{padding:12px 10px 35px}.lebd-ep-stats{gap:7px}.lebd-ep-stat{padding:9px 7px;min-height:62px;gap:7px}.lebd-ep-stat i{width:31px;height:31px;font-size:16px}.lebd-ep-stat small{font-size:9px}.lebd-ep-stat b{font-size:19px}.lebd-ep-tools{padding:9px;gap:6px}.lebd-ep-tools input{font-size:11px}.lebd-ep-tools select{max-width:116px;font-size:10px;padding:0 5px}.lebd-ep-card{padding:11px 9px}.lebd-ep-main{grid-template-columns:43px minmax(0,1fr) minmax(0,1fr);gap:7px}.lebd-ep-number{min-height:43px;font-size:18px}.lebd-ep-name{font-size:12px;grid-column:2/4}.lebd-ep-meta small{font-size:9px}.lebd-ep-meta strong{font-size:10px}.lebd-ep-status-wrap{grid-column:1/3;grid-row:3}.lebd-ep-status{font-size:9px;padding:5px 6px}.lebd-ep-actions{grid-column:3;grid-row:3;flex-direction:column}.lebd-ep-btn{min-height:34px;padding:6px 4px;font-size:9px}.lebd-ep-more{font-size:10px}}
</style>
@endsection
@section('content')
<div class="lebd-exam-pdf-page">
 <a class="lebd-ep-back" href="{{ route('student.exam-section.browse-exams',['category'=>$cat->id,'subcategory_id'=>$sub?->id]) }}">← ফিরে যান</a>
 <header class="lebd-ep-heading"><h1>▤ Exam PDF</h1><p>{{ $cat->name }} @if($sub) — {{ $sub->name }} @endif | Routine অনুযায়ী স্বয়ংক্রিয় প্রশ্নপত্র</p></header>
 <section class="lebd-ep-stats" aria-label="PDF summary">
  <div class="lebd-ep-stat"><i>▤</i><div><small>মোট পরীক্ষা</small><b>{{ $total }}</b></div></div>
  <div class="lebd-ep-stat"><i>✓</i><div><small>প্রকাশিত PDF</small><b>{{ $published }}</b></div></div>
  <div class="lebd-ep-stat"><i>◷</i><div><small>প্রস্তুত হচ্ছে</small><b>{{ $pending }}</b></div></div>
  <div class="lebd-ep-stat"><i>▦</i><div><small>আসন্ন পরীক্ষা</small><b>{{ $upcoming }}</b></div></div>
 </section>
 <div class="lebd-ep-tools">
  <input id="lebdEpSearch" type="search" placeholder="Exam No বা Topic অনুসন্ধান..." aria-label="Search Exam PDF">
  <select id="lebdEpStatus" aria-label="PDF status"><option value="all">সকল স্ট্যাটাস</option><option value="published">Published</option><option value="pending">Pending</option></select>
  <button class="lebd-ep-refresh" type="button" onclick="window.location.reload()" title="Refresh">↻</button>
 </div>
 <section id="lebdEpCards">
 @forelse($rows as $row)
  @php $isPublished=$row->published; $pub=$row->record?->published_at ? \Carbon\Carbon::parse($row->record->published_at,'Asia/Dhaka')->format('d M Y, h:i A') : 'প্রকাশের অপেক্ষায়'; @endphp
  <article class="lebd-ep-card" data-status="{{ $isPublished?'published':'pending' }}" data-search="{{ strtolower($row->item->exam_name.' '.$row->item->routine_name.' '.implode(' ',$row->topics)) }}">
   <div class="lebd-ep-main">
    <div class="lebd-ep-number">{{ str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT) }}</div>
    <div class="lebd-ep-name"><small>{{ $row->item->routine_name }}</small>{{ $row->item->exam_name }}</div>
    <div class="lebd-ep-meta"><small>▦ PDF Publish Date</small><strong>{{ $pub }}</strong></div>
    <div class="lebd-ep-meta"><small>▣ প্রশ্ন সংখ্যা</small><strong>{{ (int)($row->record->question_count ?? 0) }} টি</strong></div>
    <div class="lebd-ep-status-wrap"><span class="lebd-ep-status {{ $isPublished?'':'pending' }}">{{ $isPublished?'✓ Published':'◷ প্রশ্ন প্রস্তুত নয়' }}</span></div>
    <div class="lebd-ep-actions">
     @if($isPublished)
      <a class="lebd-ep-btn alt" href="{{ route('student.exam-section.exam-pdf.view',['item'=>$row->item->id]) }}" target="_blank" rel="noopener">◉ দেখুন</a>
      <a class="lebd-ep-btn" href="{{ route('student.exam-section.exam-pdf.download',['item'=>$row->item->id]) }}">↓ Download</a>
     @else
      <span class="lebd-ep-btn disabled">PDF Pending</span>
     @endif
    </div>
   </div>
   <details class="lebd-ep-more"><summary>▹ Subject &amp; Syllabus / See More</summary><div class="lebd-ep-detail">{{ $row->topics ? implode(' • ',$row->topics) : 'Routine-এর Topic এখনো যুক্ত করা হয়নি।' }} @if($row->item->notes)<br>{{ $row->item->notes }}@endif</div></details>
  </article>
 @empty
  <div class="lebd-ep-empty">এই Category / Sub-category-এর Routine-এ এখনো কোনো Exam যুক্ত হয়নি।</div>
 @endforelse
 <div id="lebdEpNoMatch" class="lebd-ep-empty" hidden>আপনার অনুসন্ধানের সঙ্গে কোনো Exam PDF মেলেনি।</div>
 </section>
</div>
<script>
(function(){var q=document.getElementById('lebdEpSearch'),s=document.getElementById('lebdEpStatus'),cards=[].slice.call(document.querySelectorAll('.lebd-ep-card')),empty=document.getElementById('lebdEpNoMatch');function filter(){var term=(q.value||'').toLowerCase().trim(),status=s.value,shown=0;cards.forEach(function(card){var okText=(card.getAttribute('data-search')||'').indexOf(term)!==-1,okStatus=status==='all'||card.getAttribute('data-status')===status;card.hidden=!(okText&&okStatus);if(!card.hidden)shown++;});empty.hidden=shown!==0;}q.addEventListener('input',filter);s.addEventListener('change',filter);})();
</script>
@endsection
