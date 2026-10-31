@extends('student')
@section('title','Exam Information')
@section('content')
<style>
/* STEP-21: VIEW ONLY: header spacing + status display. STEP-20 baseline preserved. */
/* STEP-20: VIEW ONLY. All existing form actions and conditionals are retained. */
body:has(#lebd-routine-auto-info) > footer,
body:has(#lebd-routine-auto-info) footer,
body:has(#lebd-routine-auto-info) .main-footer,
body:has(#lebd-routine-auto-info) .footer,
body:has(#lebd-routine-auto-info) #footer {display:none !important;}
#lebd-routine-auto-info {--r-navy:#102e58;--r-blue:#0768e8;--r-line:#dceafa;--r-muted:#627996; max-width:860px;margin:96px auto 38px !important;padding:0 14px;position:relative;z-index:1;clear:both;font-family:system-ui,'Noto Sans Bengali',Arial,sans-serif;color:var(--r-navy);box-sizing:border-box;}
#lebd-routine-auto-info *,#lebd-routine-auto-info *::before,#lebd-routine-auto-info *::after {box-sizing:border-box;}
#lebd-routine-auto-info .r-card {background:#fff;border:1px solid #d9e8f9;border-radius:23px;padding:24px;box-shadow:0 12px 36px rgba(16,66,131,.09);}
#lebd-routine-auto-info .r-head {display:flex;align-items:center;gap:14px;margin-bottom:18px;}
#lebd-routine-auto-info .r-icon {display:flex;align-items:center;justify-content:center;flex:none;width:55px;height:55px;border-radius:17px;background:#e4f1ff;color:#0768e8;}
#lebd-routine-auto-info .r-icon svg {width:29px;height:29px;}
#lebd-routine-auto-info .r-title {font-size:clamp(20px,3vw,28px);font-weight:850;letter-spacing:-.025em;line-height:1.35;margin:0;color:#102e58;}
#lebd-routine-auto-info .r-batch {font-size:13px;line-height:1.55;color:#587497;margin:4px 0 0;overflow-wrap:anywhere;}
#lebd-routine-auto-info .r-grid {display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:0 0 12px;}
#lebd-routine-auto-info .r-metric {display:flex;align-items:center;gap:13px;border-radius:15px;padding:16px;background:#eafaf2;min-width:0;}
#lebd-routine-auto-info .r-metric.time {background:#fff7e2;}
#lebd-routine-auto-info .r-metric-icon {display:flex;align-items:center;justify-content:center;width:39px;height:39px;flex:none;border-radius:12px;background:#d6f4e4;color:#0c7a51;font-size:21px;}
#lebd-routine-auto-info .r-metric.time .r-metric-icon {background:#ffebc1;color:#c76809;}
#lebd-routine-auto-info .r-metric-label {display:block;font-size:12px;font-weight:700;color:#536b83;}
#lebd-routine-auto-info .r-metric-value {display:block;font-size:23px;line-height:1.4;font-weight:850;color:#102e58;}
#lebd-routine-auto-info .r-dates {display:grid;grid-template-columns:1fr 1fr;gap:0;background:#eaf3ff;border-radius:15px;margin-bottom:14px;padding:15px 11px;}
#lebd-routine-auto-info .r-date {padding:0 14px;min-width:0;}
#lebd-routine-auto-info .r-date+.r-date {border-left:2px solid #bed7f8;}
#lebd-routine-auto-info .r-date-label {display:block;font-size:12px;color:#526f95;margin-bottom:5px;}
#lebd-routine-auto-info .r-date-value {display:block;font-size:16px;font-weight:800;color:#13335d;line-height:1.4;}
#lebd-routine-auto-info .r-date-time {display:block;font-size:13px;color:#086ce8;font-weight:750;margin-top:3px;}
#lebd-routine-auto-info .r-note {display:flex;align-items:flex-start;gap:12px;background:#f2f8ff;border:1px solid #dfedff;border-radius:14px;padding:13px 14px;margin-bottom:12px;font-size:14px;line-height:1.8;color:#365d89;}
#lebd-routine-auto-info .r-note-mark {font-size:21px;font-weight:800;line-height:1.2;color:#126cdf;}
#lebd-routine-auto-info .r-status {display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:13px 15px;background:#e9f9ee;border-radius:13px;margin-bottom:13px;font-size:14px;font-weight:750;color:#0b6545;}
#lebd-routine-auto-info .r-status-pill {border-radius:999px;padding:5px 14px;background:#d6f4e6;color:#137a54;font-weight:850;}
#lebd-routine-auto-info .r-status-pill.schedule {background:#fff0c4;color:#865800;}
#lebd-routine-auto-info .r-status-pill.completed {background:#dbeafa;color:#17588a;}
#lebd-routine-auto-info .r-attempt {font-size:14px;color:#365f98;margin:0 0 16px;font-weight:600;}
#lebd-routine-auto-info .r-attempt strong {color:#0868eb;}
#lebd-routine-auto-info .r-actions {margin-top:8px;}
#lebd-routine-auto-info .r-actions form {margin:0;}
#lebd-routine-auto-info .r-actions a,#lebd-routine-auto-info .r-actions button {display:flex !important;align-items:center;justify-content:center;gap:8px;width:100%;max-width:370px;min-height:49px;padding:11px 18px !important;background:linear-gradient(110deg,#0871f9,#0755d9) !important;color:#fff !important;text-decoration:none;font-size:16px !important;font-weight:800;border:0 !important;border-radius:13px !important;box-shadow:0 5px 12px #0871f91c;cursor:pointer;line-height:1.4;}
#lebd-routine-auto-info .r-actions button:disabled {background:#e2e8f0 !important;color:#667b95 !important;box-shadow:none;cursor:not-allowed;}
#lebd-routine-auto-info .r-actions p {font-size:13px;line-height:1.7;}
@media(min-width:601px) and (max-width:900px){#lebd-routine-auto-info{margin-top:108px !important;}}
@media(max-width:600px){
 #lebd-routine-auto-info {padding:0 10px;margin:108px auto 18px !important;}
 #lebd-routine-auto-info .r-card {border-radius:17px;padding:15px 12px;box-shadow:0 5px 19px #0a44800d;}
 #lebd-routine-auto-info .r-head {gap:9px;margin-bottom:14px;align-items:flex-start;}
 #lebd-routine-auto-info .r-icon {width:43px;height:43px;border-radius:12px;}
 #lebd-routine-auto-info .r-icon svg {width:24px;height:24px;}
 #lebd-routine-auto-info .r-title {font-size:19px;line-height:1.4;}
 #lebd-routine-auto-info .r-batch {font-size:11.5px;}
 #lebd-routine-auto-info .r-grid {gap:8px;margin-bottom:9px;}
 #lebd-routine-auto-info .r-metric {padding:10px 8px;gap:7px;border-radius:11px;}
 #lebd-routine-auto-info .r-metric-icon {width:29px;height:29px;font-size:16px;border-radius:9px;}
 #lebd-routine-auto-info .r-metric-label {font-size:10px;}
 #lebd-routine-auto-info .r-metric-value {font-size:17px;white-space:nowrap;}
 #lebd-routine-auto-info .r-dates {padding:11px 5px;margin-bottom:10px;border-radius:11px;}
 #lebd-routine-auto-info .r-date {padding:0 8px;}
 #lebd-routine-auto-info .r-date-label {font-size:10px;}
 #lebd-routine-auto-info .r-date-value {font-size:13px;}
 #lebd-routine-auto-info .r-date-time {font-size:11px;}
 #lebd-routine-auto-info .r-note {font-size:12px;line-height:1.6;padding:10px;margin-bottom:10px;gap:8px;}
 #lebd-routine-auto-info .r-status {padding:10px;font-size:12px;margin-bottom:10px;}
 #lebd-routine-auto-info .r-attempt {font-size:12px;margin-bottom:12px;}
 #lebd-routine-auto-info .r-actions a,#lebd-routine-auto-info .r-actions button {max-width:100%;min-height:48px;font-size:15px !important;}
}
@media(max-width:340px){#lebd-routine-auto-info .r-date-value {font-size:11px;}#lebd-routine-auto-info .r-date-time{font-size:10px;}#lebd-routine-auto-info .r-metric-value{font-size:15px;}}
@media(prefers-reduced-motion:reduce){#lebd-routine-auto-info *{animation:none!important;transition:none!important;}}
</style>
<div id="lebd-routine-auto-info">
 <section class="r-card" aria-label="পরীক্ষার তথ্য">
  <div class="r-head">
   <div class="r-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5M9 12h6M9 16h6"/></svg></div>
   <div><h1 class="r-title">{{ $c['exam']->name }} — পরীক্ষার তথ্য</h1><p class="r-batch">ব্যাচ: {{ $c['batch']->name }}</p></div>
  </div>
  <div class="r-grid">
   <div class="r-metric"><span class="r-metric-icon" aria-hidden="true">☷</span><div><span class="r-metric-label">মোট প্রশ্ন</span><strong class="r-metric-value">{{ $count }} টি</strong></div></div>
   <div class="r-metric time"><span class="r-metric-icon" aria-hidden="true">◷</span><div><span class="r-metric-label">নির্ধারিত সময়</span><strong class="r-metric-value">{{ $c['duration'] }} মিনিট</strong></div></div>
  </div>
  <div class="r-dates">
   <div class="r-date"><span class="r-date-label">শুরু</span><strong class="r-date-value">{{ $c['start']->format('d M Y') }}</strong><span class="r-date-time">{{ $c['start']->format('h:i A') }}</span></div>
   <div class="r-date"><span class="r-date-label">শেষ</span><strong class="r-date-value">{{ $c['end']->format('d M Y') }}</strong><span class="r-date-time">{{ $c['end']->format('h:i A') }}</span></div>
  </div>
  <div class="r-note"><span class="r-note-mark" aria-hidden="true">ⓘ</span><span>আপনি যখন এক্সাম লিংকে প্রবেশ করবেন, তখন থেকে <strong>{{ $c['duration'] }} মিনিট</strong> সময় পাবেন।</span></div>
  @php
    $viewStatusText = $attempt && $attempt->status === 'Completed'
      ? 'Completed'
      : ($live ? 'Live Now' : (
          \Carbon\Carbon::now('Asia/Dhaka')->greaterThanOrEqualTo($c['end']) ? 'ReSchedule' : 'Schedule'
        ));
    $viewStatusClass = $viewStatusText === 'Completed' ? 'completed' : (in_array($viewStatusText, ['Schedule','ReSchedule'], true) ? 'schedule' : '');
  @endphp
  <div class="r-status"><span>বর্তমান অবস্থা:</span><strong class="r-status-pill {{ $viewStatusClass }}">{{ $viewStatusText }}</strong></div>
  <p class="r-attempt">আপনার Attempt: <strong>{{ $attemptCount }} / {{ $attemptLimit >= 100 ? 'Multiple' : $attemptLimit }}</strong></p>
  <div class="r-actions">
    @if(!$ready)
      <p style="color:#b23434">Exam Attempt Storage প্রস্তুত নয়।</p>
    @elseif($running)
      <a href="{{ $attemptUrl }}?attempt_id={{ $attempt->id }}" style="display:inline-block;background:#1768d7;color:#fff;padding:12px 20px;border-radius:10px">পরীক্ষা চালিয়ে যান</a>
    @elseif($canStart)
      <form method="POST" action="{{ $startUrl }}">@csrf
        <button type="submit" style="background:#1768d7;border:0;color:#fff;padding:13px 24px;border-radius:10px;cursor:pointer">{{ $attemptCount ? 'আবার পরীক্ষা দিন' : 'পরীক্ষা দিন' }}</button>
      </form>
    @elseif($attempt && $attempt->status === 'Completed')
      <a href="{{ $resultUrl }}?attempt_id={{ $attempt->id }}" style="display:inline-block;background:#1768d7;color:white;padding:12px 20px;border-radius:10px">ফলাফল দেখুন</a>
    @elseif($attempt)
      <a href="{{ $attemptUrl }}?attempt_id={{ $attempt->id }}" style="display:inline-block;background:#1768d7;color:white;padding:12px 20px;border-radius:10px">পরীক্ষা জমা দিন</a>
    @else
      <button disabled style="padding:13px 24px;border:0;border-radius:10px">{{ $live ? 'Attempt Limit শেষ' : 'পরীক্ষা এখন চালু নেই' }}</button>
    @endif
  </div>
 </section>
</div>
@endsection
