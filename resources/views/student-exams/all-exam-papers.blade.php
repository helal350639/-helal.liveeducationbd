@php
    // View-only STEP-12: keep all incoming controller keys and links unchanged.
    // Route-driven presentation mode only. Controller and authorization stay unchanged.
    $resultMode = request()->routeIs('students.batch.exam-results');
    $missingMode = request()->routeIs('students.batch.missing-exams');
    // Missing is based on actual submitted attempts, never on the routine's end date.
    // Derived exams without tracked attempts are NOT asserted as missing.
    $examRows = collect($exams ?? []);
    if ($missingMode) {
        $examRows = $examRows->filter(static fn ($e) =>
            ($e['kind'] ?? '') === 'Main Exam' && empty($e['attempted'])
        )->values();
    }
    $examCount = $examRows->count();
    $liveCount = $examRows->where('status', 'Live Now')->count();
    $completedCount = $resultMode ? $examRows->filter(fn ($e) => !empty($e['attempted']))->count() : $examRows->where('status', 'Completed')->count();
    $upcomingCount = $examRows->where('status', 'Upcoming')->count();
    $notTakenCount = $examRows->filter(static fn ($e) => empty($e['attempted']))->count();
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $missingMode ? 'Missing Exam List' : ($resultMode ? 'Exam Result' : 'All Exam Paper') }} | {{ $batch->name }}</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
:root{--bg:#f3f6fb;--card:#fff;--ink:#122544;--muted:#647899;--line:#dfe8f5;--blue:#0b76eb;--shadow:0 5px 16px rgba(25,51,88,.045)}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--bg);color:var(--ink);font-family:Arial,'Noto Sans Bengali',sans-serif}a{text-decoration:none}button{font:inherit}
.page{max-width:1510px;margin:auto;min-height:100vh;padding:20px 28px 36px}.top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:13px}.back{background:white;padding:10px 15px;border-radius:11px;color:var(--blue);font-weight:700;font-size:13px;box-shadow:var(--shadow)}.count{font-size:12px;color:var(--muted)}
.hero{text-align:center;margin:0 0 15px}.hero h1{font-size:clamp(22px,3vw,37px);margin:2px 0 5px;line-height:1.3}.hero h1 i{color:var(--blue);margin-right:8px}.sub{margin:0;color:var(--muted);font-size:14px;overflow-wrap:anywhere}
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:20px 0 15px}.stat{display:flex;align-items:center;gap:12px;border:1px solid var(--line);border-radius:15px;background:#fff;padding:15px;min-width:0}.stat .ico{width:55px;height:55px;border-radius:15px;display:grid;place-items:center;font-size:25px;flex:none;background:#e6f2ff;color:#0875e1}.stat strong{display:block;font-size:27px;margin-top:3px;font-variant-numeric:tabular-nums}.stat small{font-size:13px;font-weight:700}.stat.completed{background:#f0fbf4}.stat.completed .ico{background:#d5f6e2;color:#07984e}.stat.completed strong{color:#087f43}.stat.live{background:#fffbf0}.stat.live .ico{background:#fff0cc;color:#eb9000}.stat.live strong{color:#b86904}.stat.upcoming{background:#f4f8ff}.stat.upcoming .ico{background:#e0ecff;color:#0d70d7}
.notice{border:1px solid #f3d893;border-radius:13px;background:#fff7df;padding:14px;line-height:1.7;font-size:13px;margin:12px 0}.choices{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0}.choice{display:inline-block;background:white;color:var(--blue);padding:11px 14px;border:1px solid #cbdffb;border-radius:11px;font-size:13px;font-weight:700}.choice.active{background:#e7f2ff}
.cards{display:grid;gap:12px}.card{--accent:#11a7b5;display:grid;grid-template-columns:minmax(230px,1.25fr) minmax(200px,.85fr) minmax(200px,.85fr) minmax(180px,1fr) minmax(175px,.9fr);align-items:center;gap:14px;background:linear-gradient(110deg,#f5fcff,#fff);border:1px solid #bee9ef;border-left:5px solid var(--accent);border-radius:15px;padding:14px 16px;min-width:0;box-shadow:var(--shadow)}.card:nth-child(4n+2){--accent:#ffa91e;background:linear-gradient(110deg,#fffbf3,#fff);border-color:#ffe0a9}.card:nth-child(4n+3){--accent:#0cae76;background:linear-gradient(110deg,#f2fff9,#fff);border-color:#b8f0d5}.card:nth-child(4n+4){--accent:#8359f5;background:linear-gradient(110deg,#f8f5ff,#fff);border-color:#ddcfff}.number{flex:none;display:grid;place-items:center;width:53px;height:61px;background:var(--accent);border-radius:12px;color:white;font-size:26px;font-weight:800}.identity{display:flex;align-items:center;gap:12px;min-width:0}.identity-copy{min-width:0}.name{font-size:18px;margin:4px 0;line-height:1.35;overflow-wrap:anywhere}.kind{display:inline-block;color:#246fcd;background:#e8f1ff;padding:4px 8px;border-radius:7px;font-size:11px;font-weight:800}.dateblock{display:flex;gap:9px;align-items:center;min-width:0}.dateblock>i{font-size:17px;padding:11px;color:#0875e1;background:#e9f4ff;border-radius:50%;flex:none}.dateblock.clock>i{color:#773de9;background:#efe9ff}.dateblock small{display:block;color:var(--muted);font-size:11px;margin-bottom:4px}.dateblock strong{display:block;font-size:13px;line-height:1.45;overflow-wrap:anywhere}.state{min-width:0}.status{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border-radius:10px;font-weight:800;font-size:13px;background:#e5efff;color:#0068c8}.status.done{background:#dcf7e6;color:#087f43}.status.not-taken{background:#fff0f0;color:#c93445}.status.live{background:#fff0ca;color:#aa6100}.state-note{display:block;margin-top:5px;color:var(--muted);font-size:11px}.action{display:flex;align-items:center;justify-content:center;gap:9px;background:#0875ef;color:#fff;font-size:13px;font-weight:800;line-height:1.35;min-height:47px;padding:9px 12px;border-radius:10px;text-align:center}.action:hover{background:#0066d8}.unready{background:#e7eaf0;color:#73819a;padding:11px;border-radius:10px;text-align:center;font-size:12px;line-height:1.6}.details{grid-column:1/-1;border-top:1px dashed #cedbed;padding-top:8px}.details summary{cursor:pointer;font-size:12px;font-weight:800;color:var(--blue);list-style:none}.details summary::-webkit-details-marker{display:none}.details summary:before{content:'▸ ';}.details[open] summary:before{content:'▾ '}.topics{border-collapse:collapse;background:white;width:100%;margin:10px 0 2px;font-size:12px}th,td{border:1px solid var(--line);padding:9px;text-align:left;vertical-align:top;overflow-wrap:anywhere}th{background:#eaf3ff}.bottom{text-align:center;margin:23px 0 4px}.bottom a{display:inline-block;background:#ffb600;color:#15213d;border-radius:12px;padding:12px 27px;font-weight:800}
@media(max-width:1120px){.card{grid-template-columns:minmax(200px,1.4fr) minmax(150px,1fr) minmax(150px,1fr)}.state{grid-column:1/2}.action,.unready{grid-column:2/4}.details{grid-column:1/-1}}
@media(max-width:650px){.page{padding:9px 9px 23px}.top{margin-bottom:7px}.back{padding:8px 10px;font-size:11px}.hero{margin-bottom:9px}.hero h1{font-size:21px}.sub{font-size:11px}.stats{grid-template-columns:repeat(4,minmax(0,1fr));gap:5px;margin:10px 0}.stat{display:block;padding:7px 3px;text-align:center;border-radius:10px}.stat .ico{width:25px;height:25px;font-size:13px;border-radius:8px;margin:0 auto 4px}.stat small{font-size:9px;display:block;line-height:1.25}.stat strong{font-size:17px;margin-top:2px}.cards{gap:7px}.card{grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:6px 8px;padding:8px 9px;border-radius:11px;border-left-width:4px}.identity{gap:8px}.number{width:33px;height:37px;border-radius:8px;font-size:16px}.name{font-size:12px;margin:2px 0}.kind{font-size:9px;padding:2px 5px}.dateblock{gap:5px}.dateblock>i{display:none}.dateblock small{font-size:9px;margin:0}.dateblock strong{font-size:10px}.dateblock.clock{grid-column:2}.state{grid-column:1;grid-row:2;align-self:center}.status{padding:4px 6px;font-size:10px;border-radius:7px}.state-note{display:none}.action,.unready{grid-column:2;min-height:29px;padding:5px;font-size:10px;border-radius:7px}.details{grid-column:1/-1;padding-top:4px}.details summary{font-size:10px}.topics{font-size:10px}.topics td,.topics th{padding:5px}.bottom{margin-top:13px}.bottom a{padding:9px 21px;font-size:12px}}
@media print{body{background:white}.page{max-width:100%;padding:0}.top,.bottom,.choices,.action,.unready{display:none!important}.card{break-inside:avoid;box-shadow:none}.details{display:block}.stats{margin:10px 0}}
</style>
</head>
<body><main class="page">
<div class="top"><a class="back" href="{{ route('students.batch.purchased-exams',['batch'=>$batch->id]) }}"><i class="fa-solid fa-arrow-left"></i> Exam Dashboard</a><span class="count">{{ $examCount }} Exams</span></div>
<header class="hero"><h1><i class="fa-regular fa-clipboard"></i>{{ $missingMode ? 'Missing Exam List' : ($resultMode ? 'Exam Result' : 'All Exam Paper') }}</h1><p class="sub">{{ $batch->name }}@if($selectedDepartment) — {{ $selectedDepartment->name }}@endif</p></header>
@if(!$selectedDepartment)
<div class="notice">আপনি একাধিক Department কিনেছেন। নিজের কেনা Department নির্বাচন করলে শুধু সেই Department-এর Exam দেখাবে।</div>
<div class="choices">@foreach($departments as $d)<a class="choice" href="{{ route($missingMode ? 'students.batch.missing-exams' : ($resultMode ? 'students.batch.exam-results' : 'students.batch.all-exam-papers'),['batchId'=>$batch->id,'department_id'=>$d->id]) }}"><i class="fa-solid fa-graduation-cap"></i> {{ $d->name }}</a>@endforeach</div>
@else
@if($departments->count()>1)<div class="choices">@foreach($departments as $d)<a class="choice {{ $selectedDepartment->id==$d->id?'active':'' }}" href="{{ route($missingMode ? 'students.batch.missing-exams' : ($resultMode ? 'students.batch.exam-results' : 'students.batch.all-exam-papers'),['batchId'=>$batch->id,'department_id'=>$d->id]) }}">{{ $d->name }} {{ $selectedDepartment->id==$d->id?'✓':'' }}</a>@endforeach</div>@endif
@if(!$routine)<div class="notice">এই Batch ও Department-এর Published Routine পাওয়া যায়নি।</div>@elseif(!$examCount)<div class="notice">{{ $missingMode ? 'এই Department-এর Main Exam-গুলোর মধ্যে কোনো অনুপস্থিত পরীক্ষা পাওয়া যায়নি।' : 'Published Routine-এ এখনো Exam যুক্ত হয়নি।' }}</div>@endif
@if($examCount)
<div class="stats">
<div class="stat"><span class="ico"><i class="fa-solid fa-clipboard-list"></i></span><div><small>{{ $missingMode ? 'মিসিং পরীক্ষা' : 'মোট পরীক্ষা' }}</small><strong>{{ $examCount }}</strong></div></div>
<div class="stat completed"><span class="ico"><i class="fa-solid fa-circle-check"></i></span><div><small>{{ $missingMode ? 'সময়সীমা শেষ' : ($resultMode ? 'পরীক্ষা দিয়েছেন' : 'সময়সীমা শেষ') }}</small><strong>{{ $completedCount }}</strong></div></div>
<div class="stat live"><span class="ico"><i class="fa-solid fa-clock"></i></span><div><small>{{ $resultMode ? 'লাইভ পরীক্ষা' : 'লাইভ পরীক্ষা' }}</small><strong>{{ $liveCount }}</strong></div></div>
<div class="stat upcoming"><span class="ico"><i class="fa-solid fa-calendar-days"></i></span><div><small>আসন্ন পরীক্ষা</small><strong>{{ $upcomingCount }}</strong></div></div>
</div>
@if($missingMode)
<div class="notice">এখানে শুধু যেসব Main Exam-এর Submitted Attempt পাওয়া যায়নি, সেগুলো দেখানো হচ্ছে। Revision/Model Exam-এর Attempt সংযোগ নিশ্চিত না হওয়ায় ভুলভাবে Missing হিসেবে দেখানো হয়নি। পরীক্ষা দেওয়ার সুযোগ Routine-এর সময় ও ২৪ ঘণ্টার নিয়ম অনুযায়ী নির্ধারিত হবে।</div>
@endif
<div class="cards">
@foreach($missingMode ? $examRows : $exams as $exam)
@php
    $statusText = $missingMode ? 'Not Taken' : ($resultMode ? (!empty($exam['attempted']) ? 'Completed' : 'Not Taken') : ($exam['status'] ?? 'Upcoming'));
    $statusCss = $statusText === 'Live Now' ? 'live' : ($statusText === 'Completed' ? 'done' : ($statusText === 'Not Taken' ? 'not-taken' : ''));
@endphp
<article class="card">
<div class="identity"><span class="number">{{ str_pad((string)($loop->iteration),2,'0',STR_PAD_LEFT) }}</span><div class="identity-copy"><span class="kind">{{ $exam['kind'] }}</span><h2 class="name">{{ $exam['name'] }}</h2></div></div>
<div class="dateblock"><i class="fa-regular fa-calendar-days"></i><div><small>তারিখ</small><strong>{{ $exam['date'] }}</strong></div></div>
<div class="dateblock clock"><i class="fa-regular fa-clock"></i><div><small>সময়</small><strong>{{ $exam['time'] }}</strong></div></div>
<div class="state"><span class="status {{ $statusCss }}"><i class="fa-solid {{ $statusText==='Completed'?'fa-circle-check':($statusText==='Not Taken'?'fa-circle-xmark':($statusText==='Live Now'?'fa-circle-play':'fa-clock')) }}"></i>{{ $statusText }}</span><small class="state-note">{{ $missingMode ? 'এই পরীক্ষায় আপনার কোনো Submitted Attempt নেই' : ($resultMode ? (!empty($exam['attempted']) ? 'আপনি পরীক্ষায় অংশ নিয়েছেন' : 'আপনি পরীক্ষায় অংশ নেননি') : ($statusText==='Completed'?'পরীক্ষার নির্ধারিত সময়সীমা শেষ':'Routine অনুযায়ী অবস্থা')) }}</small></div>
@if($resultMode)
    @if(!empty($exam['attempted']) && !empty($exam['result_url']))
        <a class="action" href="{{ $exam['result_url'] }}"><i class="fa-solid fa-chart-column"></i> ফলাফল দেখুন</a>
    @elseif(!empty($exam['attempted']))
        <div class="unready"><i class="fa-solid fa-circle-info"></i> ফলাফলের লিংক এখন উপলব্ধ নয়</div>
    @else
        <div class="unready"><i class="fa-solid fa-circle-xmark"></i> আপনি পরীক্ষায় অংশ নেননি</div>
    @endif
@elseif(!empty($exam['result_url']) && empty($exam['can_take']))
<a class="action" href="{{ $exam['result_url'] }}"><i class="fa-solid fa-chart-column"></i> ফলাফল দেখুন</a>
@elseif(!empty($exam['info_url']) && !empty($exam['can_take']))
<a class="action" href="{{ $exam['info_url'] }}"><i class="fa-solid fa-file-lines"></i> {{ !empty($exam['attempted']) ? 'পরীক্ষার তথ্য দেখুন' : 'পরীক্ষা দিন' }}</a>
@elseif(!empty($exam['info_url']))
<div class="unready"><i class="fa-solid fa-clock"></i> {{ ($exam['status'] ?? '') === 'Completed' ? 'নির্ধারিত সময় শেষ — ২৪ ঘণ্টা পরে অনুপস্থিতদের জন্য খুলবে' : 'পরীক্ষা এখনো শুরু হয়নি' }}</div>
@else<div class="unready"><i class="fa-solid fa-lock"></i> Question Paper সংযুক্ত হয়নি</div>@endif
@if(!empty($exam['topics']))<details class="details"><summary>Subject &amp; Syllabus / See More</summary><table class="topics"><thead><tr><th>Subject</th><th>Syllabus / Topic</th></tr></thead><tbody>@foreach($exam['topics'] as $topic)<tr><td>{{ $topic['name'] }}</td><td>{{ $topic['details'] }}</td></tr>@endforeach</tbody></table></details>@endif
</article>
@endforeach
</div>
@endif
@endif
<div class="bottom"><a href="{{ route('students.batch.purchased-exams',['batch'=>$batch->id]) }}"><i class="fa-solid fa-arrow-left"></i> Back</a></div>
</main></body></html>
