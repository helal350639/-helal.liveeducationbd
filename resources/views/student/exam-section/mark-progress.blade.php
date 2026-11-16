@extends('student')
@section('title', 'Exam Mark Progress')
@section('stylesheets')
<style>
.lebd-mark-page{background:#f2f6fc;min-height:70vh;padding:18px 12px 42px;color:#172b4d;font-family:inherit}
.lebd-mark-wrap{max-width:1400px;margin:0 auto}
.lebd-mark-cta{display:flex;align-items:center;justify-content:center;gap:14px;min-height:62px;margin:0 0 18px;padding:12px 20px;border:1px solid #70b6ff;border-radius:14px;background:linear-gradient(105deg,#0756c7,#087de9 56%,#1648d8);color:#fff;text-decoration:none;font-size:22px;font-weight:850;box-shadow:0 5px 0 #06439f,0 12px 22px rgba(9,83,183,.18)}
.lebd-mark-cta:hover{color:#fff;transform:translateY(-1px)}.lebd-mark-cta .cta-icon{width:38px;height:38px;display:grid;place-items:center;border-radius:11px;background:rgba(255,255,255,.18)}
.lebd-mark-banner{display:flex;align-items:center;justify-content:space-between;gap:18px;overflow:hidden;position:relative;padding:22px 26px;margin-bottom:16px;border-radius:14px;background:linear-gradient(105deg,#0750b8,#087be5 65%,#269cf5);color:#fff;box-shadow:0 6px 18px rgba(13,82,160,.12)}
.lebd-mark-banner h1{font-size:clamp(22px,3vw,30px);font-weight:850;margin:0 0 4px}.lebd-mark-banner p{margin:0;font-size:14px;color:#e4f3ff}.lebd-mark-banner-icon{width:58px;height:58px;flex:0 0 58px;border-radius:15px;background:rgba(255,255,255,.18);display:grid;place-items:center;font-size:27px}
.lebd-mark-stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px}.lebd-mark-stat{min-width:0;display:flex;align-items:center;gap:12px;padding:15px;border:1px solid #e1eafa;border-radius:14px;background:#fff;box-shadow:0 3px 12px rgba(16,59,110,.06)}.lebd-mark-stat-icon{width:44px;height:44px;flex:0 0 44px;display:grid;place-items:center;border-radius:12px;background:#e6f2ff;color:#0968d6;font-size:20px}.lebd-mark-stat:nth-child(2) .lebd-mark-stat-icon{background:#e2faed;color:#07965c}.lebd-mark-stat:nth-child(3) .lebd-mark-stat-icon{background:#fff4d9;color:#d98a00}.lebd-mark-stat:nth-child(4) .lebd-mark-stat-icon{background:#f0eaff;color:#7045d5}.lebd-mark-stat:nth-child(5) .lebd-mark-stat-icon{background:#e4f5ff;color:#0875c9}.lebd-mark-stat small{display:block;color:#71839c;font-size:11px}.lebd-mark-stat strong{display:block;color:#102e59;font-size:22px;font-weight:900;line-height:1.25;overflow-wrap:anywhere}.lebd-mark-stat .stat-sub{font-size:10px;color:#7b8da5}
.lebd-mark-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:16px}.lebd-mark-panel{min-width:0;background:#fff;border:1px solid #dfe8f5;border-radius:15px;padding:16px;box-shadow:0 3px 12px rgba(15,55,100,.05)}.lebd-mark-panel h2{display:flex;align-items:center;gap:10px;margin:0 0 13px;padding-bottom:10px;border-bottom:1px solid #e6edf7;color:#123b72;font-size:18px;font-weight:850}.lebd-mark-panel h2 i{color:#0870df}
.lebd-subject-row{display:grid;grid-template-columns:minmax(95px,145px) 1fr 62px;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #edf1f7}.lebd-subject-row:last-child{border-bottom:0}.lebd-subject-name{font-size:13px;font-weight:750;color:#233b5c;overflow-wrap:anywhere}.lebd-subject-track{height:13px;border-radius:20px;background:#e8edf5;overflow:hidden}.lebd-subject-fill{height:100%;border-radius:20px;background:linear-gradient(90deg,#1685f5,#075bd4)}.lebd-subject-score{text-align:right;font-size:12px;font-weight:800;color:#203b62}

.lebd-chart-empty{padding:36px 10px;text-align:center;color:#8090a7;font-size:13px}
.lebd-trend-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.lebd-trend-tabs{display:flex;gap:7px;flex-wrap:wrap;margin-left:auto}
.lebd-trend-tabs button{border:1px solid #b9cbe3;background:#fff;color:#24466f;border-radius:22px;padding:7px 14px;font-size:11px;font-weight:850;cursor:pointer;transition:.18s ease}
.lebd-trend-tabs button.active{background:linear-gradient(100deg,#0866d9,#0789ee);border-color:#0874df;color:#fff;box-shadow:0 3px 8px rgba(8,105,215,.2)}
.lebd-bar-chart-scroll{width:100%;overflow-x:auto;overflow-y:hidden;padding:4px 0 2px;scrollbar-width:thin;scrollbar-color:#b8cce6 #f1f5fb}
.lebd-bar-chart{display:block;width:100%;min-width:720px;height:auto}
.lebd-bar-grid{stroke:#dce5f0;stroke-width:1;stroke-dasharray:4 5}
.lebd-bar-axis{font-size:12px;fill:#60738e}
.lebd-bar-xlabel{font-size:11px;fill:#304967;font-weight:750}
.lebd-bar-score{font-size:11px;fill:#fff;font-weight:900;text-anchor:middle}
.lebd-bar-percent{font-size:10px;fill:#fff;font-weight:750;text-anchor:middle}
.lebd-trend-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin-top:10px}
.lebd-trend-summary-item{display:flex;align-items:center;gap:10px;min-width:0;padding:10px 12px;border-radius:11px;background:#eaf5ff;color:#075fc4}
.lebd-trend-summary-item:nth-child(2){background:#e9faef;color:#079650}.lebd-trend-summary-item:nth-child(3){background:#fff6df;color:#c48700}
.lebd-trend-summary-item i{font-size:20px;flex:0 0 auto}.lebd-trend-summary-item small{display:block;font-size:10px;color:#63758d}.lebd-trend-summary-item strong{display:block;font-size:17px;font-weight:900;color:#17365e;line-height:1.25}
.lebd-trend-summary-item span{display:block;font-size:10px;color:#6c7f96}
@media(max-width:700px){.lebd-trend-head{align-items:flex-start}.lebd-trend-tabs{width:100%;margin:0}.lebd-trend-tabs button{flex:1;padding:7px 8px}.lebd-bar-chart{min-width:680px}.lebd-trend-summary{gap:6px}.lebd-trend-summary-item{padding:8px 7px;gap:6px}.lebd-trend-summary-item i{font-size:15px}.lebd-trend-summary-item strong{font-size:13px}.lebd-trend-summary-item small,.lebd-trend-summary-item span{font-size:9px}}

.lebd-mark-bottom{display:grid;grid-template-columns:1.55fr 1fr;gap:15px;margin-bottom:15px}.lebd-mark-table-wrap{overflow-x:auto}.lebd-mark-table{width:100%;border-collapse:collapse;min-width:650px;font-size:12px}.lebd-mark-table th{padding:10px 8px;text-align:left;background:#e8f1ff;color:#16467f;font-weight:850;white-space:nowrap}.lebd-mark-table td{padding:9px 8px;border-bottom:1px solid #edf1f7;color:#29415f;white-space:nowrap}.lebd-mark-table tr:last-child td{border-bottom:0}.lebd-mark-badge{display:inline-flex;padding:4px 8px;border-radius:30px;background:#e0f8e9;color:#07884c;font-weight:800;font-size:10px}.lebd-mark-analysis-row{display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid #edf1f7;font-size:12px}.lebd-mark-analysis-row:last-child{border:0}.lebd-mark-analysis-row i{width:18px;flex:0 0 18px;color:#0a70d9;text-align:center}.lebd-mark-analysis-row strong{display:block;color:#25415f}.lebd-mark-analysis-row span{color:#6b7f98}.lebd-mark-advice{margin-top:10px;padding:12px;border-radius:10px;background:#f0f6ff;color:#385779;font-size:12px;line-height:1.6}
.lebd-mark-actions{display:flex;gap:12px;flex-wrap:wrap}.lebd-mark-actions button{min-height:43px;border:0;border-radius:9px;padding:0 18px;background:linear-gradient(100deg,#0759c8,#087ee8);color:#fff;font-weight:800}.lebd-mark-actions .secondary{background:#fff;color:#16529a;border:1px solid #9cb6d9}
.lebd-mark-empty{padding:35px 15px;text-align:center;color:#6f829c;background:#fff;border:1px dashed #cbd9ec;border-radius:13px}
@media(max-width:1050px){.lebd-mark-stats{grid-template-columns:repeat(3,minmax(0,1fr))}.lebd-mark-bottom{grid-template-columns:1fr}}
@media(max-width:700px){.lebd-mark-page{padding:10px 8px 28px}.lebd-mark-cta{min-height:49px;font-size:16px;gap:9px;padding:9px 12px}.lebd-mark-cta .cta-icon{width:29px;height:29px}.lebd-mark-banner{padding:15px;border-radius:11px}.lebd-mark-banner-icon{width:44px;height:44px;flex-basis:44px;font-size:20px}.lebd-mark-banner p{font-size:11px}.lebd-mark-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.lebd-mark-stat{padding:10px;gap:8px}.lebd-mark-stat-icon{width:34px;height:34px;flex-basis:34px;font-size:15px}.lebd-mark-stat strong{font-size:17px}.lebd-mark-stat small{font-size:9px}.lebd-mark-grid{grid-template-columns:1fr;gap:10px}.lebd-mark-panel{padding:12px}.lebd-mark-panel h2{font-size:15px}.lebd-subject-row{grid-template-columns:90px 1fr 48px;gap:7px}.lebd-subject-name,.lebd-subject-score{font-size:10px}.lebd-subject-track{height:10px}.lebd-mark-actions button{flex:1;font-size:12px;padding:0 10px}}
@media print{.student-header,.navbar,.lebd-mark-cta,.lebd-mark-actions{display:none!important}.lebd-mark-page{background:#fff;padding:0}.lebd-mark-panel,.lebd-mark-stat{box-shadow:none;break-inside:avoid}.lebd-mark-wrap{max-width:none}.lebd-mark-banner{print-color-adjust:exact;-webkit-print-color-adjust:exact}}
</style>



@endsection
@section('content')
@php
  $chartCount = count($rows);
  $chartPoints = [];
  if ($chartCount > 0) {
      foreach ($rows as $idx => $row) {
          $x = $chartCount === 1 ? 450 : 52 + ($idx * (796 / max(1, $chartCount - 1)));
          $y = 190 - (max(0,min(100,(float)$row['percent'])) * 1.55);
          $chartPoints[] = round($x,1).','.round($y,1);
      }
  }
  $trendRows = array_map(function($row) {
      return [
          'name' => (string)($row['name'] ?? 'Exam'),
          'score' => (float)($row['score'] ?? 0),
          'possible' => (float)($row['possible'] ?? 0),
          'percent' => max(0, min(100, (float)($row['percent'] ?? 0))),
      ];
  }, $rows);
  $trendMinimum = $totalExams ? min(array_column($trendRows, 'percent')) : 0;
  $trendMaximum = $totalExams ? max(array_column($trendRows, 'percent')) : 0;
  $trendAverage = $totalExams ? round(array_sum(array_column($trendRows, 'percent')) / $totalExams, 1) : 0;
  $bestSubject = $subjectRows->sortByDesc('percent')->first();
  $weakSubject = $subjectRows->sortBy('percent')->first();
@endphp
<div class="lebd-mark-page"><div class="lebd-mark-wrap">
  <header class="lebd-mark-banner" id="lebd-mark-report"><div><h1>Exam Mark Progress</h1><p>পরীক্ষার নম্বর, অগ্রগতি ও পারফরম্যান্স বিশ্লেষণ</p></div><span class="lebd-mark-banner-icon"><i class="fa fa-chart-simple"></i></span></header>

  <div class="lebd-mark-scope-label" style="margin:0 0 16px;padding:11px 15px;border:1px solid #dce7f6;border-radius:11px;background:#fff;color:#23466f;font-size:13px"><strong>বর্তমান বিভাগ:</strong> {{ $scopeTitle }} @if($subcategory) <span style="color:#71839c">({{ $category->name }} / {{ $subcategory->name }})</span> @else <span style="color:#71839c">(এই Category-র সব Sub-category)</span> @endif</div>

  <section class="lebd-mark-stats">
    <article class="lebd-mark-stat"><span class="lebd-mark-stat-icon"><i class="fa fa-clipboard-list"></i></span><div><small>মোট পরীক্ষা</small><strong>{{ $totalExams }}টি</strong></div></article>
    <article class="lebd-mark-stat"><span class="lebd-mark-stat-icon"><i class="fa fa-trophy"></i></span><div><small>গড় নম্বর</small><strong>{{ number_format($average,1) }}%</strong></div></article>
    <article class="lebd-mark-stat"><span class="lebd-mark-stat-icon"><i class="fa fa-award"></i></span><div><small>সর্বোচ্চ নম্বর</small><strong>{{ number_format($highest,2) }}</strong></div></article>
    <article class="lebd-mark-stat"><span class="lebd-mark-stat-icon"><i class="fa fa-calendar-check"></i></span><div><small>সর্বশেষ পরীক্ষা</small><strong style="font-size:14px">{{ $latest['name'] ?? '—' }}</strong><span class="stat-sub">{{ $latest['date'] ?? 'ফলাফল নেই' }}</span></div></article>
    <article class="lebd-mark-stat"><span class="lebd-mark-stat-icon"><i class="fa fa-chart-pie"></i></span><div><small>গড় পারফরম্যান্স</small><strong>{{ number_format($average,1) }}%</strong><span class="stat-sub">প্রকাশিত ফলাফলের ভিত্তিতে</span></div></article>
  </section>

  @if($totalExams)
  <div class="lebd-mark-grid">
    <section class="lebd-mark-panel"><h2><i class="fa fa-chart-column"></i> বিষয়ভিত্তিক নম্বর অগ্রগতি</h2>
      @forelse($subjectRows as $subject)
      <div class="lebd-subject-row"><span class="lebd-subject-name">{{ $subject['subject'] }}</span><div class="lebd-subject-track"><div class="lebd-subject-fill" style="width:{{ max(0,min(100,$subject['percent'])) }}%"></div></div><span class="lebd-subject-score">{{ number_format($subject['percent'],1) }}%</span></div>
      @empty <div class="lebd-chart-empty">বিষয়ভিত্তিক নম্বরের তথ্য পাওয়া যায়নি।</div> @endforelse
    </section>
    <section class="lebd-mark-panel lebd-trend-panel">
      <div class="lebd-trend-head">
        <h2 style="margin-bottom:0;border:0;padding-bottom:0"><i class="fa fa-chart-column"></i> মার্কস ট্রেন্ড (Exam Mark Trend)</h2>
        <div class="lebd-trend-tabs" role="group" aria-label="পরীক্ষা নির্বাচন">
          <button type="button" class="active" data-trend-range="all">All Exam ({{ $totalExams }}টি)</button>
          <button type="button" data-trend-range="last10">Last 10 Exam</button>
        </div>
      </div>
      <div class="lebd-bar-chart-scroll" aria-label="পরীক্ষাভিত্তিক নম্বরের বার চার্ট">
        <svg id="lebdExamMarkBarChart" class="lebd-bar-chart" viewBox="0 0 1000 390" role="img" aria-label="প্রতিটি পরীক্ষার প্রাপ্ত নম্বর ও শতকরা হারের বার চার্ট"></svg>
      </div>
      <div class="lebd-trend-summary" id="lebdTrendSummary">
        <div class="lebd-trend-summary-item"><i class="fa fa-chart-column"></i><div><small>সর্বনিম্ন নম্বর</small><strong>{{ number_format($trendMinimum,1) }}%</strong><span>প্রকাশিত পরীক্ষার মধ্যে</span></div></div>
        <div class="lebd-trend-summary-item"><i class="fa fa-trophy"></i><div><small>সর্বোচ্চ নম্বর</small><strong>{{ number_format($trendMaximum,1) }}%</strong><span>প্রকাশিত পরীক্ষার মধ্যে</span></div></div>
        <div class="lebd-trend-summary-item"><i class="fa fa-sigma"></i><div><small>গড় নম্বর</small><strong>{{ number_format($trendAverage,1) }}%</strong><span>নির্বাচিত পরীক্ষার গড়</span></div></div>
      </div>
    </section>
  </div>

  <div class="lebd-mark-bottom">
    <section class="lebd-mark-panel"><h2><i class="fa fa-clipboard-list"></i> পরীক্ষার ফলাফল বিস্তারিত</h2><div class="lebd-mark-table-wrap"><table class="lebd-mark-table"><thead><tr><th>Exam Name</th><th>Date</th><th>Marks</th><th>Correct</th><th>Wrong</th><th>Accuracy</th><th>Status</th></tr></thead><tbody>
      @foreach($rows as $row)<tr><td><strong>{{ $row['name'] }}</strong><br><small>{{ $row['category'] }} @if($row['subcategory']) / {{ $row['subcategory'] }} @endif</small></td><td>{{ $row['date'] }}</td><td>{{ number_format($row['score'],2) }} / {{ number_format($row['possible'],2) }}</td><td>{{ $row['correct'] }}</td><td>{{ $row['wrong'] }}</td><td>{{ number_format($row['accuracy'],1) }}%</td><td><span class="lebd-mark-badge">✓ সম্পন্ন</span></td></tr>@endforeach
    </tbody></table></div></section>
    <section class="lebd-mark-panel"><h2><i class="fa fa-medal"></i> র‍্যাংক ও বিশ্লেষণ</h2>
      <div class="lebd-mark-analysis-row"><i class="fa fa-trophy"></i><div><strong>সর্বোচ্চ পরীক্ষার নম্বর</strong><span>{{ number_format($highest,2) }} নম্বর</span></div></div>
      <div class="lebd-mark-analysis-row"><i class="fa fa-users"></i><div><strong>ব্যাচ র‍্যাংক</strong><span>ব্যাচের অন্যান্য শিক্ষার্থীর ফলাফল সংযুক্ত না থাকায় র‍্যাংক দেখানো যাচ্ছে না।</span></div></div>
      <div class="lebd-mark-analysis-row"><i class="fa fa-circle-check"></i><div><strong>শক্তিশালী বিষয়</strong><span>{{ $bestSubject['subject'] ?? 'তথ্য নেই' }} @if($bestSubject) ({{ number_format($bestSubject['percent'],1) }}%) @endif</span></div></div>
      <div class="lebd-mark-analysis-row"><i class="fa fa-triangle-exclamation"></i><div><strong>উন্নতির বিষয়</strong><span>{{ $weakSubject['subject'] ?? 'তথ্য নেই' }} @if($weakSubject) ({{ number_format($weakSubject['percent'],1) }}%) @endif</span></div></div>
      <div class="lebd-mark-advice"><strong>পরামর্শ</strong><br>@if($weakSubject && $weakSubject['percent']<70) {{ $weakSubject['subject'] }} বিষয়ে নিয়মিত অনুশীলন করুন এবং ভুল প্রশ্নগুলো পুনরায় সমাধান করুন। @else আপনার ফলাফল নিয়মিত পর্যালোচনা করুন এবং প্রতিটি পরীক্ষার ভুলগুলো রিভিশন করুন। @endif</div>
    </section>
  </div>
  @else
    <div class="lebd-mark-empty"><i class="fa fa-chart-line" style="font-size:28px;color:#7da5d5"></i><h3 style="font-size:17px;margin:10px 0 5px">এখনো কোনো প্রকাশিত পরীক্ষার ফলাফল পাওয়া যায়নি</h3><p style="margin:0;font-size:12px">পরীক্ষা সম্পন্ন করুন এবং Admin ফলাফল প্রকাশ করলে এখানে আপনার নম্বর ও অগ্রগতি দেখা যাবে।</p></div>
  @endif
  <div class="lebd-mark-actions" style="margin-top:15px"><button type="button" onclick="window.print()"><i class="fa fa-file-pdf"></i> PDF ডাউনলোড / প্রিন্ট</button><button type="button" class="secondary" onclick="window.location.href='{{ route('student.exam-section.browse-exams', ['category'=>$category->id, 'subcategory_id'=>$subcategory?->id]) }}'">Exam Page-এ ফিরে যান</button></div>
</div></div>
<script>
(function(){
  const svg = document.getElementById('lebdExamMarkBarChart');
  if (!svg || svg.dataset.ready === '1') return;
  svg.dataset.ready = '1';
  const allRows = @json($trendRows);
  const buttons = document.querySelectorAll('[data-trend-range]');
  const NS = 'http://www.w3.org/2000/svg';
  const palette = [
    ['#1688f5','#0860d2'],['#20c6c4','#079c9b'],['#52d99a','#20b879'],
    ['#c7df3e','#8ebd13'],['#ffd34d','#e7a900'],['#ff9c45','#f07820'],
    ['#ff8965','#ed594f'],['#ff6d91','#e83d70'],['#ed62c8','#c52da5'],
    ['#b16be8','#8540cb'],['#7d70ed','#5548d2'],['#4d9cf5','#176cda'],
    ['#22c5e5','#0799c2'],['#20cdb8','#08a58f'],['#50d879','#20b64d']
  ];
  function node(tag, attrs, text){
    const el=document.createElementNS(NS,tag);
    Object.keys(attrs||{}).forEach(k=>el.setAttribute(k,attrs[k]));
    if(text!==undefined) el.textContent=text;
    return el;
  }
  function render(rows){
    while(svg.firstChild) svg.removeChild(svg.firstChild);
    if(!rows.length){
      svg.setAttribute('viewBox','0 0 1000 390');
      svg.appendChild(node('text',{x:500,y:190,'text-anchor':'middle',fill:'#8090a7','font-size':15},'কোনো পরীক্ষার ফলাফল পাওয়া যায়নি'));
      return;
    }
    const width=Math.max(760, rows.length*64+110), height=390;
    const left=58, right=width-22, top=38, bottom=300, plotH=bottom-top;
    svg.setAttribute('viewBox',`0 0 ${width} ${height}`);
    const defs=node('defs',{});
    svg.appendChild(defs);
    [0,20,40,60,80,100].forEach(t=>{
      const y=bottom-(t/100)*plotH;
      svg.appendChild(node('line',{x1:left,y1:y,x2:right,y2:y,class:'lebd-bar-grid'}));
      svg.appendChild(node('text',{x:left-12,y:y+4,'text-anchor':'end',class:'lebd-bar-axis'},t+'%'));
    });
    svg.appendChild(node('line',{x1:left,y1:top,x2:left,y2:bottom,stroke:'#bdcce0','stroke-width':1.4}));
    svg.appendChild(node('line',{x1:left,y1:bottom,x2:right,y2:bottom,stroke:'#bdcce0','stroke-width':1.4}));
    const slot=(right-left)/rows.length;
    const barW=Math.min(42,Math.max(22,slot*.66));
    rows.forEach((r,i)=>{
      const pct=Math.max(0,Math.min(100,Number(r.percent)||0));
      const score=Number(r.score)||0, possible=Number(r.possible)||0;
      const barH=(pct/100)*plotH, x=left+i*slot+(slot-barW)/2, y=bottom-barH;
      const colors=palette[i%palette.length], gid='lebdBarGradient'+i;
      const grad=node('linearGradient',{id:gid,x1:'0',y1:'0',x2:'0',y2:'1'});
      grad.appendChild(node('stop',{offset:'0%','stop-color':colors[0]}));
      grad.appendChild(node('stop',{offset:'100%','stop-color':colors[1]}));
      defs.appendChild(grad);
      svg.appendChild(node('rect',{x,y,width:barW,height:Math.max(1,barH),rx:7,fill:`url(#${gid})`}));
      const labelY=Math.max(4,y-49), labelW=Math.min(62,slot-3), labelX=x+(barW-labelW)/2;
      svg.appendChild(node('rect',{x:labelX,y:labelY,width:labelW,height:43,rx:8,fill:colors[1],opacity:.98}));
      const scoreText=possible>0?score.toFixed(1):score.toFixed(1);
      svg.appendChild(node('text',{x:x+barW/2,y:labelY+17,class:'lebd-bar-score'},scoreText));
      svg.appendChild(node('text',{x:x+barW/2,y:labelY+33,class:'lebd-bar-percent'},Math.round(pct)+'%'));
      const shortName=String(r.name||('Exam-'+(i+1))).length>11?String(r.name).slice(0,10)+'…':String(r.name||('Exam-'+(i+1)));
      svg.appendChild(node('text',{x:x+barW/2,y:bottom+25,'text-anchor':'middle',class:'lebd-bar-xlabel'},shortName));
      const title=node('title',{},`${r.name}: ${score.toFixed(2)} / ${possible.toFixed(2)} (${pct.toFixed(1)}%)`);
      svg.appendChild(title);
    });
    // axis caption
    svg.appendChild(node('text',{x:18,y:top+plotH/2,transform:`rotate(-90 18 ${top+plotH/2})`,'text-anchor':'middle',class:'lebd-bar-axis'},'প্রাপ্ত নম্বর (%)'));
  }
  function updateSummary(rows){
    const box=document.getElementById('lebdTrendSummary'); if(!box || !rows.length) return;
    const values=rows.map(r=>Number(r.percent)||0);
    const min=Math.min(...values), max=Math.max(...values), avg=values.reduce((a,b)=>a+b,0)/values.length;
    const nums=box.querySelectorAll('.lebd-trend-summary-item strong');
    if(nums[0]) nums[0].textContent=min.toFixed(1)+'%';
    if(nums[1]) nums[1].textContent=max.toFixed(1)+'%';
    if(nums[2]) nums[2].textContent=avg.toFixed(1)+'%';
  }
  function choose(mode){
    const rows=mode==='last10'?allRows.slice(-10):allRows;
    render(rows); updateSummary(rows);
    buttons.forEach(b=>b.classList.toggle('active',b.dataset.trendRange===mode));
  }
  buttons.forEach(b=>b.addEventListener('click',()=>choose(b.dataset.trendRange)));
  choose('all');
})();
</script>
@endsection
