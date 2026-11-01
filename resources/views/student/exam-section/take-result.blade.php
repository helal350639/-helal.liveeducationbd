@extends('student')
@section('title', 'Exam Result — Live Education BD')
@section('stylesheets')
<style>
.lebd-r26,.lebd-r26 *{box-sizing:border-box}.lebd-r26{background:#f3f6fc;min-height:70vh;padding:23px 14px 50px;font-family:system-ui,-apple-system,'Segoe UI','Noto Sans Bengali',sans-serif;color:#0b2b54}
.lebd-r26 a{text-decoration:none}.lebd-r26-shell{max-width:920px;margin:auto;padding:16px;background:#fff;border:1px solid #dbe8f9;border-radius:22px;box-shadow:0 12px 32px #173b6311}
.lebd-r26-identity{display:grid;grid-template-columns:1fr 1fr;gap:10px}.lebd-r26-box{border:1px solid #cfe2fb;border-radius:15px;padding:17px;min-width:0;background:#fff}.lebd-r26-box h3{font-size:17px;margin:0 0 12px}.lebd-r26-box p{font-size:13px;margin:5px 0;overflow-wrap:anywhere;line-height:1.6}.lebd-r26-muted{color:#7186a5}
.lebd-r26-banner{background:#e8fbf0;border-radius:15px;padding:18px;display:flex;justify-content:center;align-items:center;gap:15px;text-align:center;margin:12px 0 15px;color:#08733e}.lebd-r26-banner.pending{background:#fff6de;color:#80580d}.lebd-r26-banner h2{font-size:clamp(18px,2.8vw,25px);margin:0 0 4px;font-weight:850}.lebd-r26-banner p{margin:0;font-size:13px;color:#66809b}.lebd-r26-trophy{font-size:32px}
.lebd-r26-exam{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:6px 8px 12px;flex-wrap:wrap}.lebd-r26-examleft{display:flex;align-items:center;gap:14px}.lebd-r26-examicon{background:#e5f0ff;color:#126fdf;padding:15px;border-radius:14px;font-size:24px}.lebd-r26-exam h2{margin:0 0 4px;font-size:23px}.lebd-r26-exam p{margin:0;color:#6180ad;font-size:12px}.lebd-r26-pill{padding:8px 13px;border-radius:11px;background:#def8e9;color:#007646;font-weight:800;font-size:12px}.lebd-r26-pill.pending{background:#fff0d0;color:#99630b}
.lebd-r26-date{font-size:12px;color:#7388a2;padding:0 8px 12px}.lebd-r26-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px}.lebd-r26-stat{border-radius:13px;padding:15px 13px;display:flex;gap:12px;align-items:center;min-width:0;background:#e8f2ff}.lebd-r26-stat:nth-child(2){background:#e9faef}.lebd-r26-stat:nth-child(3){background:#ffeff1}.lebd-r26-stat:nth-child(4){background:#fff4dd}.lebd-r26-stat-icon{font-size:22px;flex:none}.lebd-r26-stat span{display:block;font-size:12px;font-weight:750}.lebd-r26-stat strong{display:block;font-size:24px;margin-top:3px;color:#0d57b0;overflow-wrap:anywhere}.lebd-r26-stat:nth-child(2) strong{color:#00864b}.lebd-r26-stat:nth-child(3) strong{color:#c12f47}.lebd-r26-stat:nth-child(4) strong{color:#b36a00}
.lebd-r26-summary{border:1px solid #d3e5fc;border-radius:15px;padding:15px;margin-top:12px}.lebd-r26-summary h3{margin:0 0 12px;font-size:17px}.lebd-r26-srow{display:flex;justify-content:space-between;gap:14px;background:#e8f3ff;border-radius:9px;padding:10px 12px;margin-top:6px;color:#075bbc;font-size:13px;font-weight:700}.lebd-r26-srow:nth-of-type(1){background:#e9f9ee;color:#007543}.lebd-r26-srow:nth-of-type(2){background:#fff0f1;color:#b72741}.lebd-r26-srow:last-child{background:#fff3df;color:#aa6400}
.lebd-r26-wait{background:#fff7e6;border:1px solid #f3d8a0;padding:15px;border-radius:13px;font-size:14px;line-height:1.7;color:#875d10}.lebd-r26-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px}.lebd-r26-btn{display:flex;align-items:center;justify-content:center;text-align:center;min-height:46px;border:1px solid #c8dffb;border-radius:10px;color:#165ba4!important;font-weight:800;font-size:13px;padding:10px}.lebd-r26-btn.primary{color:#fff!important;background:#116dea;border-color:#116dea}.lebd-r26-btn.disabled{background:#f2f5fa;color:#8293a9!important;border-color:#e1e8f1;cursor:not-allowed}.lebd-r26-footer{text-align:center;font-size:11px;color:#7890ac;margin:12px 0 2px}
@media(max-width:600px){.lebd-r26{padding:9px 7px 30px}.lebd-r26-shell{padding:10px;border-radius:16px}.lebd-r26-identity{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.lebd-r26-box{padding:10px 8px;border-radius:12px}.lebd-r26-box h3{font-size:12px;line-height:1.35;margin:0 0 8px}.lebd-r26-box p{font-size:10px;line-height:1.45;margin:4px 0;overflow-wrap:anywhere}.lebd-r26-banner{padding:13px 8px}.lebd-r26-trophy{font-size:22px}.lebd-r26-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-r26-stat{padding:11px 9px;gap:7px}.lebd-r26-stat-icon{font-size:19px}.lebd-r26-stat strong{font-size:21px}.lebd-r26-actions{grid-template-columns:1fr 1fr}.lebd-r26-btn{font-size:12px;padding:9px 5px}}
@media print{.lebd-r26{background:white;padding:0}.lebd-r26-shell{border:none;box-shadow:none}.lebd-r26-actions{display:none!important}}

/* LEBD-05 scoped mobile visibility hotfix: don't alter the result data or controls. */
.lebd-r26{position:relative;display:block;width:100%;height:auto!important;min-height:calc(100vh - 120px);overflow:visible!important;clear:both}
.lebd-r26-shell{height:auto!important;max-height:none!important;overflow:visible!important}
.lebd-r26-identity,.lebd-r26-box{min-width:0}
.lebd-r26-identity{align-items:stretch}
@media(max-width:600px){
  .lebd-r26{padding:100px 9px 42px!important;margin:0!important;overflow:visible!important}
  .lebd-r26-shell{width:100%;padding:10px!important;margin:0 auto;overflow:visible!important}
  .lebd-r26-identity{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:7px!important}
  .lebd-r26-box{padding:9px 7px!important;min-width:0!important}
  .lebd-r26-box h3{font-size:12px!important;line-height:1.4;margin-bottom:7px!important}
  .lebd-r26-box p{font-size:10px!important;line-height:1.5;overflow-wrap:anywhere;word-break:break-word}
  .lebd-r26-banner{margin-top:12px!important}
}
/* Hide the global dark footer on this result page only, not other student pages. */
body:has(.lebd-r26) > footer,
body:has(.lebd-r26) .main-footer,
body:has(.lebd-r26) .footer-area,
body:has(.lebd-r26) .footer-section,
body:has(.lebd-r26) #footer{display:none!important}
</style>
@endsection
@section('content')
@php
    // Preserve admin visibility rules: an attempt is never sufficient on its own to reveal scores.
    $canShowResult = (bool) $visible && (bool) $ended;
    $student = \Illuminate\Support\Facades\Auth::guard('student')->user();
    $categoryId = (int) ($row->category_id ?? 0);
    $subcategoryId = $row->subcategory_id ?? null;
    $dashboardParams = ['category' => $categoryId];
    if ($subcategoryId !== null) $dashboardParams['subcategory_id'] = $subcategoryId;
    $dashboardUrl = $categoryId ? route('student.exam-section.browse-exams', $dashboardParams) : route('student.exam-section.categories');
    $examListParams = $dashboardParams + ['paper' => 1];
    $examListUrl = $categoryId ? route('student.exam-section.browse-exams', $examListParams) : route('student.exam-section.categories');
    $correct = (int) ($attempt->correct_count ?? 0);
    $wrong = (int) ($attempt->wrong_count ?? 0);
    $score = (float) ($attempt->score ?? 0);
    $submittedAt = !empty($attempt->submitted_at) ? \Carbon\Carbon::parse($attempt->submitted_at, 'Asia/Dhaka')->format('d M Y, h:i A') : null;
    $startedAt = !empty($attempt->started_at) ? \Carbon\Carbon::parse($attempt->started_at, 'Asia/Dhaka')->format('d M Y, h:i A') : null;
    // Generated-question count is read-only and scoped to this routine and exam.
    $questionTotal = \Illuminate\Support\Facades\DB::table('lebd_exam_section_generated_questions')
        ->where('routine_id', $row->routine_id)->where('exam_name', $row->exam_name)
        ->distinct()->count('question_id');
@endphp
<div class="lebd-r26"><div class="lebd-r26-shell">
  <section class="lebd-r26-identity" aria-label="Student and exam information">
    <div class="lebd-r26-box"><h3>👤 শিক্ষার্থীর তথ্য</h3>
      <p><strong>নাম:</strong> {{ $student->name ?? $student->student_name ?? '—' }}</p>
      <p><strong>ইমেইল:</strong> {{ $student->email ?? '—' }}</p>
    </div>
    <div class="lebd-r26-box"><h3>📚 Exam Batch তথ্য</h3>
      <p><strong>বিভাগ:</strong> {{ $row->category_name ?? 'Exam Section' }}</p>
      @if(!empty($row->routine_name))<p><strong>রুটিন:</strong> {{ $row->routine_name }}</p>@endif
      <p><strong>পরীক্ষা:</strong> {{ $row->exam_name ?? '—' }}</p>
    </div>
  </section>
  @if($canShowResult)
    <section class="lebd-r26-banner"><span class="lebd-r26-trophy">🏆</span><div><h2>পরীক্ষার ফলাফল প্রকাশিত হয়েছে</h2><p>আপনার দেওয়া পরীক্ষার ফলাফল নিচে দেখানো হলো।</p></div><span class="lebd-r26-trophy" aria-hidden="true">🏆</span></section>
  @else
    <section class="lebd-r26-banner pending"><div><h2>ফলাফল এখনো প্রকাশিত হয়নি</h2><p>Admin অনুমোদন ও নির্ধারিত Exam End Time অনুসারে ফলাফল প্রকাশ হবে।</p></div></section>
  @endif
  <section class="lebd-r26-exam" aria-label="Exam details">
    <div class="lebd-r26-examleft"><span class="lebd-r26-examicon">▤</span><div><h2>{{ $row->exam_name ?? 'Exam' }}</h2><p>{{ $row->category_name ?? 'Exam Section' }} @if(!empty($row->routine_name)) — {{ $row->routine_name }} @endif</p></div></div>
    <span class="lebd-r26-pill {{ $canShowResult ? '' : 'pending' }}">{{ $canShowResult ? '✓ Completed' : '◷ Result Pending' }}</span>
  </section>
  <div class="lebd-r26-date">প্রবেশ: {{ $startedAt ?? '—' }} &nbsp; | &nbsp; জমা: {{ $submittedAt ?? '—' }}</div>
  @if($canShowResult)
    <section class="lebd-r26-stats" aria-label="Result statistics">
      <div class="lebd-r26-stat"><span class="lebd-r26-stat-icon">▤</span><div><span>মোট প্রশ্ন</span><strong>{{ $questionTotal }}</strong></div></div>
      <div class="lebd-r26-stat"><span class="lebd-r26-stat-icon">✓</span><div><span>সঠিক উত্তর</span><strong>{{ $correct }}</strong></div></div>
      <div class="lebd-r26-stat"><span class="lebd-r26-stat-icon">✕</span><div><span>ভুল উত্তর</span><strong>{{ $wrong }}</strong></div></div>
      <div class="lebd-r26-stat"><span class="lebd-r26-stat-icon">★</span><div><span>প্রাপ্ত নম্বর</span><strong>{{ number_format($score, 2) }}</strong></div></div>
    </section>
    <section class="lebd-r26-summary"><h3>📊 ফলাফল সারাংশ</h3>
      <div class="lebd-r26-srow"><span>✓ সঠিক উত্তর</span><strong>{{ $correct }}</strong></div>
      <div class="lebd-r26-srow"><span>✕ ভুল উত্তর</span><strong>{{ $wrong }}</strong></div>
      <div class="lebd-r26-srow"><span>▤ মোট প্রশ্ন</span><strong>{{ $questionTotal }}</strong></div>
      <div class="lebd-r26-srow"><span>★ প্রাপ্ত নম্বর</span><strong>{{ number_format($score,2) }}</strong></div>
    </section>
  @else
    <div class="lebd-r26-wait">আপনার পরীক্ষার Attempt সংরক্ষিত আছে। ফলাফল প্রকাশের অনুমতি পাওয়া গেলে নম্বর ও বিস্তারিত সারাংশ এখানে দেখানো হবে।</div>
  @endif
  <div class="lebd-r26-actions"><span class="lebd-r26-btn disabled" aria-disabled="true">🏅 Merit List (Soon)</span><span class="lebd-r26-btn disabled" aria-disabled="true">📊 Summary</span></div>
  <div class="lebd-r26-actions"><a class="lebd-r26-btn primary" href="{{ $dashboardUrl }}">▤ Exam Dashboard</a><a class="lebd-r26-btn" href="{{ $examListUrl }}">← All Exam Paper</a></div>
  <p class="lebd-r26-footer">Live Education BD · Exam Section Result</p>
</div></div>
@endsection
