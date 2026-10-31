@php
    // VIEW ONLY: use the existing, already-authorized controller payload.
    $total = max(0, (int) ($attempt->question_count ?? 0));
    $correct = max(0, (int) ($attempt->correct_count ?? 0));
    $wrong = max(0, (int) ($attempt->wrong_count ?? 0));
    $unanswered = max(0, (int) ($attempt->unanswered_count ?? 0));
    $score = $attempt->score ?? 0;
    $displayScore = (int) round((float) $score); // Display only: keep persisted score unchanged.
    $student = auth('student')->user();
    $name = $student->name ?? null;
    $studentCode = $student->student_id ?? $student->roll ?? null;
    $phone = $student->phone ?? $student->mobile ?? null;
    $email = $student->email ?? null;
    $batchName = data_get($c, 'batch.name') ?? data_get($c, 'batchName');
    $deptName = data_get($c, 'department.name') ?? data_get($c, 'departmentName');
    $submitted = $attempt->submitted_at ?? null;
    $examName = data_get($c, 'exam.name', 'Exam');
    $dashboardUrl = route('students.batch.purchased-exams', ['batch' => $c['batchId']]);
    // Link only to an already-registered Merit List route; never invent a destination.
    $meritUrl = null;
    if (\Illuminate\Support\Facades\Route::has('students.batch.merit-list')) {
        $meritUrl = route('students.batch.merit-list', ['batch' => $c['batchId']]);
    }
@endphp
<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#f1f6fd">
<title>{{ $examName }} — পরীক্ষার ফলাফল | Live Education BD</title>
<style>
:root{--ink:#10244b;--muted:#617594;--line:#dce8f7;--blue:#0868ef;--green:#058749;--red:#d81937;--amber:#c76c00;--violet:#6530d9}
*{box-sizing:border-box}html{background:#f1f6fd}body{margin:0;background:radial-gradient(circle at 50% 0,#fff0ec 0,#f4f8ff 50%,#f1f6fd 100%);color:var(--ink);font:15px/1.55 system-ui,-apple-system,'Noto Sans Bengali','Hind Siliguri',Arial,sans-serif;overflow-x:hidden;-webkit-text-size-adjust:100%}
button,a{font:inherit}.shell{width:min(calc(100% - 28px),1070px);margin:26px auto 32px;padding:18px;background:white;border:1px solid var(--line);border-radius:25px;box-shadow:0 12px 40px rgba(13,45,100,.07)}
.hero{position:relative;display:flex;align-items:center;gap:19px;border-radius:18px;padding:19px 25px;background:linear-gradient(110deg,#e7fff1,#f5fffb);overflow:hidden;min-height:114px}.hero-icon{display:grid;place-items:center;flex:0 0 76px;width:76px;height:76px;border-radius:50%;background:#cbf7df;font-size:41px}.hero h1{margin:0;color:#087435;font-size:clamp(20px,2.8vw,30px);line-height:1.35}.hero p{margin:4px 0 0;color:var(--muted);font-weight:600}.trophy{margin-left:auto;font-size:67px;line-height:1}
.exam-summary{display:flex;align-items:center;gap:16px;padding:24px 11px 19px;flex-wrap:wrap}.exam-icon{display:grid;place-items:center;width:78px;height:78px;border-radius:17px;background:#dfedff;color:#0665de;font-size:39px;flex:0 0 78px}.exam-name{flex:1;min-width:180px}.exam-name h2{font-size:clamp(20px,3vw,30px);line-height:1.25;margin:0 0 5px}.exam-name p{margin:0;color:var(--muted)}.completed{background:#e0faea;color:#068443;padding:8px 14px;border-radius:14px;font-weight:800;white-space:nowrap}.submitted-line{color:var(--muted);font-size:12px;margin:3px 12px 18px;overflow-wrap:anywhere}
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:0 0 15px}.stat{display:flex;gap:12px;align-items:center;min-width:0;padding:17px 15px;border-radius:15px;background:#eaf3ff}.stat:nth-child(2){background:#e9faef}.stat:nth-child(3){background:#fff0f2}.stat:nth-child(4){background:#fff5e0}.stat-icon{flex:0 0 47px;width:47px;height:47px;display:grid;place-items:center;font-size:28px;border-radius:12px;background:#d4e7ff}.stat:nth-child(2) .stat-icon{background:#c9f5dd}.stat:nth-child(3) .stat-icon{background:#ffdce0}.stat:nth-child(4) .stat-icon{background:#ffebbb}.stat small{display:block;font-weight:700;color:#39516f}.stat strong{display:block;font-size:28px;line-height:1.25;color:#084ab0;font-variant-numeric:tabular-nums}.stat:nth-child(2) strong{color:#087e40}.stat:nth-child(3) strong{color:#d71c31}.stat:nth-child(4) strong{color:#af6100}
.info{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:15px}.panel{border:1px solid var(--line);border-radius:17px;padding:19px;background:#fff}.panel h3{margin:0 0 12px;font-size:19px;display:flex;align-items:center;gap:9px}.panel h3 span{font-size:23px}.info-line{margin:7px 0;overflow-wrap:anywhere}.info-line b{color:#536b90}.info-empty{color:var(--muted);font-size:13px;margin:8px 0 0}
.analysis{display:block}.summary-list{display:grid;gap:8px}.summary-row{display:flex;justify-content:space-between;gap:8px;padding:9px 13px;background:#e9faef;border-radius:10px;color:#086f3e;font-weight:750}.summary-row:nth-child(2){background:#fff0f2;color:#bd172d}.summary-row:nth-child(3){background:#eaf3ff;color:#075ab9}.summary-row:nth-child(4){background:#fff4de;color:#af6500}.summary-row strong{font-variant-numeric:tabular-nums}
.actions{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:18px 1px 0}.action{display:flex;align-items:center;justify-content:center;min-height:52px;border-radius:12px;text-decoration:none;text-align:center;padding:10px 12px;font-weight:800}.action.blue{background:#0769ee;color:white}.action.violet{background:#f1eaff;color:#642bd1;border:1px solid #ddcafa}.action.green{background:#e8f9f0;color:#087c46;border:1px solid #bdebd1}.action.is-disabled{background:#f5f7fb;color:#71819d;border:1px solid #e1e7f0;cursor:not-allowed;filter:none}.action.white{background:white;color:var(--ink);border:1px solid #cdddf1}.action:hover{filter:brightness(.96)}.footer-note{text-align:center;color:var(--muted);font-size:11px;margin:13px 0 0}
@media(max-width:760px){.shell{width:calc(100% - 16px);margin:8px auto 16px;padding:10px;border-radius:17px}.hero{gap:10px;padding:14px 12px;min-height:86px}.hero-icon{flex-basis:52px;width:52px;height:52px;font-size:28px}.trophy{font-size:36px}.hero h1{font-size:17px}.hero p{font-size:11px}.exam-summary{gap:10px;padding:17px 4px 10px}.exam-icon{width:53px;height:53px;flex-basis:53px;font-size:27px}.exam-name{min-width:135px}.exam-name h2{font-size:20px}.exam-name p{font-size:12px}.completed{padding:5px 8px;font-size:11px}.stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.stat{padding:11px 9px;gap:8px}.stat-icon{width:37px;height:37px;flex-basis:37px;font-size:23px}.stat small{font-size:11px}.stat strong{font-size:23px}.info{grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}.panel{padding:10px 8px;min-width:0}.panel h3{font-size:clamp(11px,2.8vw,16px);gap:4px;line-height:1.35}.panel h3 span{font-size:17px}.info-line{font-size:clamp(10px,2.5vw,12px);line-height:1.55;margin:6px 0;overflow-wrap:anywhere}.analysis{grid-template-columns:1fr;gap:12px}.actions{gap:8px}.action{font-size:12px;min-height:46px}.submitted-line{margin:0 5px 12px}}
@media(max-width:340px){.trophy{display:none}.stats{gap:6px}.stat{padding:9px 6px}.stat small{font-size:10px}}
</style>
</head>
<body>
<main class="shell">
  <section class="info">
    <div class="panel">
      <h3><span aria-hidden="true">👤</span> শিক্ষার্থীর তথ্য</h3>
      @if($name)<p class="info-line"><b>নাম:</b> {{ $name }}</p>@endif
      @if($studentCode)<p class="info-line"><b>রোল/আইডি:</b> {{ $studentCode }}</p>@endif
      @if($phone)<p class="info-line"><b>মোবাইল:</b> {{ $phone }}</p>@endif
      @if($email)<p class="info-line"><b>ইমেইল:</b> {{ $email }}</p>@endif
      @if(!$name && !$studentCode && !$phone && !$email)<p class="info-empty">শিক্ষার্থীর বিস্তারিত তথ্য পাওয়া যায়নি।</p>@endif
    </div>
    <div class="panel">
      <h3><span aria-hidden="true">📚</span> Exam Batch তথ্য</h3>
      @if($batchName)<p class="info-line"><b>ব্যাচ:</b> {{ $batchName }}</p>@endif
      @if($deptName)<p class="info-line"><b>বিভাগ:</b> {{ $deptName }}</p>@endif
      <p class="info-line"><b>পরীক্ষা:</b> {{ $examName }}</p>
      @if(!$batchName && !$deptName)<p class="info-empty">অতিরিক্ত Batch তথ্য এই Result Page-এ পাঠানো হয়নি।</p>@endif
    </div>
  </section>
  <section class="hero">
    <span class="hero-icon" aria-hidden="true">🏆</span>
    <div><h1>পরীক্ষার ফলাফল প্রকাশিত হয়েছে</h1><p>আপনার দেওয়া পরীক্ষার ফলাফল নিচে দেখানো হলো।</p></div>
    <span class="trophy" aria-hidden="true">🏆</span>
  </section>
  <div class="exam-summary">
    <span class="exam-icon" aria-hidden="true">▤</span>
    <div class="exam-name"><h2>{{ $examName }}</h2><p>{{ $deptName ?: ($batchName ?: 'পরীক্ষার ফলাফল') }}</p></div>
    <span class="completed">✓ Completed</span>
  </div>
  @if($submitted)<p class="submitted-line">◷ জমা দেওয়া হয়েছে: {{ $submitted }}</p>@endif
  <section class="stats" aria-label="ফলাফলের সংখ্যা">
    <div class="stat"><span class="stat-icon" aria-hidden="true">▤</span><div><small>মোট প্রশ্ন</small><strong>{{ $total }}</strong></div></div>
    <div class="stat"><span class="stat-icon" aria-hidden="true">✓</span><div><small>সঠিক উত্তর</small><strong>{{ $correct }}</strong></div></div>
    <div class="stat"><span class="stat-icon" aria-hidden="true">✕</span><div><small>ভুল উত্তর</small><strong>{{ $wrong }}</strong></div></div>
    <div class="stat"><span class="stat-icon" aria-hidden="true">★</span><div><small>প্রাপ্ত নম্বর</small><strong>{{ $displayScore }}<small style="display:inline;color:inherit;font-size:12px">/{{ $total }}</small></strong></div></div>
  </section>
  <section class="panel analysis" id="resultSummary" aria-label="ফলাফল সারাংশ">
    <div>
      <h3><span aria-hidden="true">📊</span> ফলাফল সারাংশ</h3>
      <div class="summary-list">
        <div class="summary-row"><span>✓ সঠিক উত্তর</span><strong>{{ $correct }}</strong></div>
        <div class="summary-row"><span>✕ ভুল উত্তর</span><strong>{{ $wrong }}</strong></div>
        <div class="summary-row"><span>▤ মোট প্রশ্ন</span><strong>{{ $total }}</strong></div>
        <div class="summary-row"><span>🏆 প্রাপ্ত নম্বর</span><strong>{{ $displayScore }}/{{ $total }}</strong></div>
      </div>
    </div>
  </section>
  <nav class="actions" aria-label="Merit list and summary actions">
    @if($meritUrl)
      <a class="action violet" href="{{ $meritUrl }}">🏅 &nbsp; Merit List</a>
    @else
      <button class="action is-disabled" type="button" disabled title="Merit List-এর Route এখনো সংযুক্ত নেই">🏅 &nbsp; Merit List</button>
    @endif
    <a class="action green" href="#resultSummary">📊 &nbsp; Summary</a>
  </nav>
  <nav class="actions" aria-label="Result actions">
    <a class="action blue" href="{{ $dashboardUrl }}">▥ &nbsp; Exam Dashboard</a>
    <a class="action white" href="{{ $dashboardUrl }}">← &nbsp; ব্যাচে ফিরে যান</a>
  </nav>
  <p class="footer-note">Live Education BD · Routine Auto Exam Result</p>
</main>
</body>
</html>
