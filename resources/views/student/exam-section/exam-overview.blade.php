@php
  $titles = ['routine'=>'Routine','results'=>'My Results','papers'=>'All Exam Paper','missing'=>'Missing Exam List'];
  $label = $sub?->name ?? $cat->name;
  $back = route('student.exam-section.browse-exams', ['category'=>$cat->id] + ($sub ? ['subcategory_id'=>$sub->id] : []));
@endphp
<!doctype html><html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $titles[$section] }} — {{ $label }}</title>
<style>*{box-sizing:border-box}body{margin:0;background:#eef3fa;font-family:Arial,'Noto Sans Bengali',sans-serif;color:#193556}.top{background:#fff;padding:16px;border-bottom:1px solid #d9e6f4}.container{max-width:970px;margin:25px auto;padding:0 16px}.back{display:inline-block;padding:9px 14px;background:#e6effa;color:#0755a0;border-radius:9px;text-decoration:none;font-size:13px}h1{font-size:26px;margin:17px 0 6px}.muted{color:#687b91}.panel{background:#fff;border-radius:16px;padding:17px;box-shadow:0 4px 18px #203f6010;margin:12px 0}.cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.card{background:#fff;border:1px solid #d9e5f3;border-radius:15px;padding:17px;line-height:1.8}.card h3{margin:0 0 8px;font-size:19px}.small{font-size:13px;color:#61768a}.status{background:#edf3fb;padding:4px 10px;border-radius:10px;font-size:12px}.actions{display:flex;gap:9px;flex-wrap:wrap;margin-top:11px}.action{border:0;display:inline-block;background:#116fc2;color:#fff;border-radius:9px;text-decoration:none;padding:9px 13px;font-size:13px}.action.secondary{background:#e7f1fa;color:#1267a9}.score{color:#0c7e55;font-size:21px;font-weight:800}.notice{padding:11px 15px;background:#fff4d9;border-radius:10px;font-size:13px}.empty{padding:30px;text-align:center;background:#fff;border-radius:16px;color:#677c90}@media(max-width:650px){.cards{grid-template-columns:1fr}h1{font-size:22px}}</style></head><body>
{{-- Fix215: shared package CTA, matching requested top-right orange pill design. --}}
<div class="lebd215-package-bar" style="display:flex;justify-content:flex-end;align-items:center;max-width:1180px;margin:12px auto 6px;padding:0 13px;box-sizing:border-box;">
    <a href="{{ route('student.exam-section.packages') }}" class="lebd215-package-button" style="display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:36px;padding:8px 18px;background:linear-gradient(115deg,#ff7a13,#ff4239);border-radius:999px;color:#fff!important;font-size:13px;font-weight:800;line-height:1.2;text-decoration:none;box-shadow:0 6px 15px rgba(235,81,31,.23);white-space:nowrap;">
        <span aria-hidden="true" style="font-size:13px">▣</span><span>প্যাকেজ কিনুন</span>
    </a>
</div>


<div class="top"><a class="back" href="{{ $back }}">← Exam Dashboard-এ ফিরুন</a></div>
<main class="container"><h1>{{ $titles[$section] }}</h1><p class="muted">{{ $label }} — পরীক্ষার তথ্য</p>
@if($section==='missing')<p class="notice">যেসব পরীক্ষার সময় শেষ হয়েছে কিন্তু আপনি জমা দেননি, শুধু সেগুলো দেখানো হচ্ছে।</p>@elseif($section==='results')<p class="notice">Result দেখানো হবে Paper Settings-এ অনুমতি থাকলে এবং Exam End Time পার হলে।</p>@endif
@if($section === 'routine')
<style>
.routine-shell{max-width:620px;margin:0 auto}.routine-banner{border-radius:13px;padding:15px;background:#fff1bf;color:#624b16;font-size:13px;line-height:1.65;margin:12px 0}.routine-tools{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:15px 0}.routine-tools button{border:1px solid #bcd9ff;border-radius:12px;background:#e9f3ff;color:#1265b9;padding:13px;font-weight:700;cursor:pointer}.routine-tools button:last-child{background:#fff;color:#24344d;border-color:#dce4ef}.routine-item{background:#fff;padding:18px 17px;border-radius:19px;margin:13px 0;box-shadow:0 5px 18px rgba(30,55,95,.06)}.routine-item h3{font-size:18px;margin:5px 0 13px;color:#0f2140}.routine-subtitle{color:#7486a0;font-size:11px;text-transform:uppercase;font-weight:700}.routine-state{float:right;background:#e7f1ff;color:#1260b0;padding:5px 10px;border-radius:15px;font-size:11px;font-weight:800}.routine-state.live{background:#d9f8e9;color:#04754b}.routine-state.completed{background:#e4f4e8;color:#0a704f}.routine-times{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px}.routine-times>div{background:#edf2f9;padding:11px;border-radius:10px;min-width:0;font-size:12px}.routine-times span{display:block;color:#7587a0;font-size:11px}.routine-table{width:100%;border-collapse:collapse;font-size:12px;border:1px solid #dce7f5}.routine-table th{text-align:left;background:#e8f3ff}.routine-table td,.routine-table th{padding:10px;border-bottom:1px solid #dce7f5}.routine-details{margin-top:14px;border-top:1px dashed #dce4ef;padding-top:11px}.routine-details summary{color:#086ece;font-weight:700;font-size:13px;cursor:pointer}.routine-details p{font-size:13px;line-height:1.7}.routine-quiet{color:#72839a;font-size:12px}.routine-shell .routine-no{margin:13px 0;color:#59708b;font-size:13px}@media print{.top,.routine-tools,.back{display:none!important}body{background:white}.routine-item{break-inside:avoid;box-shadow:none;border:1px solid #ddd}}@media(max-width:460px){.routine-item{padding:14px}.routine-times{gap:7px}}
</style>
<div class="routine-shell">
  <div class="routine-banner">পরীক্ষার নাম, সময় ও Subject / Syllabus নিচে সংরক্ষিত Routine থেকে দেখানো হচ্ছে।</div>
  <div class="routine-tools"><button type="button" onclick="if(navigator.share){navigator.share({title:document.title,url:location.href}).catch(()=>{});}else if(navigator.clipboard){navigator.clipboard.writeText(location.href);}">↗ Share Routine</button><button type="button" onclick="window.print()">▣ Print / Save PDF</button></div>
  <div class="routine-no"><strong>Exam Schedule</strong> — {{ $records->count() }} Exams</div>
  @forelse($records as $entry)
  @php $e=$entry->exam; $topicList=$routineTopics->get($e->id,collect()); @endphp
  <article class="routine-item">
    <span class="routine-state {{ $entry->live?'live':($entry->ended?'completed':'') }}">{{ $entry->live?'Live':($entry->ended?'Completed':'Upcoming') }}</span>
    <div class="routine-subtitle">EXAM NO: {{ $e->exam_name }}</div>
    <h3>{{ $e->exam_name }}</h3>
    <div class="routine-times"><div><span>▣ Exam Date</span><strong>{{ $e->exam_start_at ? \Carbon\Carbon::parse($e->exam_start_at)->format('d M Y') : 'Not scheduled' }}</strong></div><div><span>◷ Exam Time</span><strong>{{ $e->exam_start_at ? \Carbon\Carbon::parse($e->exam_start_at)->format('h:i A') : '—' }} – {{ $e->exam_end_at ? \Carbon\Carbon::parse($e->exam_end_at)->format('h:i A') : '—' }}</strong></div></div>
    <div style="font-size:13px;font-weight:800;margin-bottom:8px;color:#10284b">▣ Subject &amp; Syllabus</div>
    <table class="routine-table"><thead><tr><th>Subject</th><th>Syllabus / Topic</th></tr></thead><tbody>
    @forelse($topicList as $topic)<tr><td>{{ $topic->subject }}</td><td>{{ $topic->syllabus }} @if($topic->range_from || $topic->range_to)<small>({{ $topic->range_from ?? '—' }}–{{ $topic->range_to ?? '—' }})</small>@endif</td></tr>
    @empty<tr><td colspan="2" class="routine-quiet">এই Exam-এর Routine Topic এখনো যুক্ত হয়নি.।</td></tr>@endforelse
    </tbody></table>
    <details class="routine-details"><summary>ⓘ See More / Instructions</summary><p><strong>Routine:</strong> {{ $e->routine_name }}</p><p><strong>শুরু:</strong> {{ $e->exam_start_at ?? '—' }}<br><strong>শেষ:</strong> {{ $e->exam_end_at ?? '—' }}</p>@if($e->notes)<p>{{ $e->notes }}</p>@endif @if($e->pdf_path)<p><a href="{{ asset('storage/'.$e->pdf_path) }}" target="_blank" rel="noopener">Routine PDF দেখুন</a></p>@endif</details>
  </article>
  @empty<div class="empty">এই Category/Sub-category-এর Routine-তে কোনো Exam পাওয়া যায়নি।</div>@endforelse
</div>
@else
<div class="cards">
@forelse($records as $entry)
@php $e=$entry->exam; @endphp
<article class="card">
<h3>{{ $e->exam_name }}</h3>
<div class="small">Routine: {{ $e->routine_name }}</div>
<div class="small">শুরু: {{ $e->exam_start_at ? \Carbon\Carbon::parse($e->exam_start_at)->format('d M Y h:i A') : '—' }}</div>
<div class="small">শেষ: {{ $e->exam_end_at ? \Carbon\Carbon::parse($e->exam_end_at)->format('d M Y h:i A') : '—' }}</div>
@if($section==='results')
  @if($entry->result_visible)<p class="score">প্রাপ্ত নম্বর: {{ $entry->attempt->score }}</p><p class="small">সঠিক: {{ $entry->attempt->correct_count }} | ভুল: {{ $entry->attempt->wrong_count }}</p>
  @else<p class="notice">ফলাফল এখনো প্রকাশযোগ্য নয়।</p>@endif
@elseif($section==='missing')<p><span class="status">Missed</span></p>
@else <p><span class="status">{{ $entry->live ? 'Live' : ($entry->ended ? 'Completed' : 'Upcoming') }}</span></p> @endif
<div class="actions">
@if($section==='papers' && $entry->live)<a class="action" href="{{ route('student.exam-section.take', ['item'=>$e->id]) }}">পরীক্ষা দিন →</a>@endif
@if($section==='routine' || $section==='papers')
@if($e->pdf_path)<a class="action secondary" href="{{ asset('storage/'.$e->pdf_path) }}" target="_blank" rel="noopener">Routine PDF</a>@endif
@endif
@if($section==='results' && $entry->result_visible)<a class="action secondary" href="{{ route('student.exam-section.take.result',['item'=>$e->id]) }}">Result Details</a>@endif
</div></article>
@empty <div class="empty">এই বিভাগে দেখানোর মতো কোনো পরীক্ষা পাওয়া যায়নি।</div>
@endforelse
</div>@endif</main></body></html>
