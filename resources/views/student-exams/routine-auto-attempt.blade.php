<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#f5f8fd">
<title>পরীক্ষা চলছে | Live Education BD</title>

@php
    $endISO = \Carbon\Carbon::parse($attempt->deadline_at, 'Asia/Dhaka')->toIso8601String();
    $serverISO = \Carbon\Carbon::now('Asia/Dhaka')->toIso8601String();
    $questionTotal = (int) $attempt->question_count;
@endphp
<style>
    .lebd-exam-session{max-width:930px;margin:0 auto;padding:18px 14px 115px;color:#142b4e;font-family:Arial,'Noto Sans Bengali',sans-serif}
    .lebd-exam-session *{box-sizing:border-box}
    .lebd-session-header{position:sticky;top:76px;z-index:60;background:#fff;border:1px solid #dbe6f5;border-radius:15px;padding:12px 14px;box-shadow:0 6px 18px rgba(14,44,85,.10)}
    .lebd-session-title{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:10px;font-weight:800;font-size:15px}
    .lebd-session-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
    .lebd-stat{background:#edf5ff;border:1px solid #d8e9ff;border-radius:10px;text-align:center;padding:8px 4px;min-width:0}
    .lebd-stat small{display:block;font-size:11px;color:#526b8c;line-height:1.5}
    .lebd-stat strong{display:block;font-size:19px;color:#0a55bb;font-variant-numeric:tabular-nums}
    .lebd-stat.remaining{background:#fff6df;border-color:#ffebba}
    .lebd-stat.remaining strong{color:#aa6b05}
    .lebd-stat.timer{background:#e7faef;border-color:#d0f2df}
    .lebd-stat.timer strong{color:#087a4b}
    .lebd-stat.timer.is-urgent{background:#ffeded;border-color:#ffc4c4}
    .lebd-stat.timer.is-urgent strong{color:#c92828}
    .lebd-question-card{background:#fff;border:1px solid #d9e5f3;border-radius:14px;margin-top:14px;padding:18px}
    .lebd-question-card h3{font-size:17px;font-weight:700;line-height:1.6;margin:0 0 15px;overflow-wrap:anywhere}
    /* STEP-23: question number and content share one row; long text wraps naturally. */
    .lebd-question-heading{display:flex;align-items:flex-start;gap:10px;margin:0 0 13px;min-width:0;font-size:16px;font-weight:750;line-height:1.55;color:#142b4e}
    .lebd-question-number{flex:none;white-space:nowrap;color:#0767d8;font-size:15px;font-weight:850;padding:2px 0;font-variant-numeric:tabular-nums}
    .lebd-question-text{flex:1;min-width:0;overflow-wrap:anywhere;padding:1px 0}
    .lebd-question-text p{margin:0 0 6px}
    .lebd-question-text p:last-child{margin-bottom:0}
    .lebd-question-text img{max-width:100%;height:auto}
    .lebd-answer-option{min-height:46px;align-items:center;background:#fff;transition:border-color .15s,background-color .15s}
    .lebd-answer-option:hover{background:#f7fbff;border-color:#a7cdf4}
    .lebd-answer-option{display:flex;align-items:flex-start;gap:12px;padding:12px;margin-bottom:8px;border:1px solid #dce7f3;border-radius:10px;cursor:pointer;line-height:1.55;overflow-wrap:anywhere}
    .lebd-answer-option:has(input:checked){border-color:#1784eb;background:#edf6ff}
    .lebd-answer-option input{margin-top:4px;accent-color:#0875e1;flex-shrink:0}
    .lebd-answer-option span{min-width:0}
    .lebd-session-footer{position:fixed;bottom:0;left:0;right:0;z-index:100;background:#fff;border-top:1px solid #cbdff5;box-shadow:0 -5px 18px rgba(18,52,95,.10);padding:11px max(12px,env(safe-area-inset-right)) calc(11px + env(safe-area-inset-bottom))}
    .lebd-session-footer-inner{max-width:902px;margin:auto;display:flex;justify-content:space-between;align-items:center;gap:12px}
    .lebd-footer-count{font-size:13px;line-height:1.55;color:#304b70}
    .lebd-submit-btn,.lebd-confirm-btn{background:#1268db;color:#fff;font-weight:800;border:0;border-radius:10px;padding:12px 20px;cursor:pointer;min-height:44px}
    .lebd-submit-btn:disabled,.lebd-confirm-btn:disabled{opacity:.65;cursor:not-allowed}
    .lebd-warning-backdrop{position:fixed;inset:0;z-index:10000;background:rgba(11,28,51,.60);display:flex;align-items:center;justify-content:center;padding:18px}
    .lebd-warning-backdrop[hidden]{display:none!important}
    .lebd-warning-dialog{width:min(100%,440px);background:#fff;color:#172b4d;border-radius:18px;padding:22px;box-shadow:0 20px 65px rgba(0,0,0,.22)}
    .lebd-warning-dialog h2{font-size:20px;margin:0 0 10px;color:#b36906;line-height:1.5}
    .lebd-warning-dialog p{line-height:1.7;font-size:15px;margin:0 0 12px}
    .lebd-warning-totals{background:#f1f6ff;padding:13px;border-radius:12px;line-height:1.9;margin-bottom:18px;font-size:15px}
    .lebd-dialog-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .lebd-cancel-btn{background:#edf2f9;color:#1b406e;border:1px solid #d9e4f1;border-radius:10px;padding:11px 16px;font-weight:700;min-height:44px;cursor:pointer}
    @media(max-width:600px){
        .lebd-exam-session{padding:12px 10px 112px}
        .lebd-session-header{top:104px;padding:9px;border-radius:12px}
        .lebd-session-title{font-size:13px;margin-bottom:7px}
        .lebd-session-stats{gap:5px}
        .lebd-stat{padding:6px 2px;border-radius:8px}
        .lebd-stat small{font-size:10px}
        .lebd-stat strong{font-size:15px}
        .lebd-question-card{padding:13px;margin-top:10px}
        .lebd-question-card h3{font-size:15px}
        .lebd-question-heading{font-size:14px;gap:7px;margin-bottom:10px;line-height:1.5}
        .lebd-question-number{font-size:13px}
        .lebd-answer-option{min-height:42px;gap:9px;margin-bottom:7px;border-radius:9px}
        .lebd-answer-option input{margin-top:0}
        .lebd-answer-option{padding:11px;font-size:14px}
        .lebd-footer-count{font-size:12px}
        .lebd-submit-btn{font-size:13px;padding:11px 13px}
        .lebd-warning-dialog{padding:18px}
    }
    /* Independent exam-only document: no student layout, nav, footer or support widgets. */
    html, body {margin:0!important;padding:0!important;width:100%;min-height:100%;background:#f5f8fd;color:#142b4e;overflow-x:hidden}
    body {font-family:Arial,'Noto Sans Bengali',sans-serif;font-size:15px;-webkit-text-size-adjust:100%;text-size-adjust:100%}
    .lebd-exam-session {max-width:960px;padding-top:116px!important}
    .lebd-session-header {position:fixed!important;top:0!important;left:0;right:0;width:min(100%,960px);margin:0 auto;z-index:500;background:#fff;border-radius:0 0 14px 14px}
    .lebd-question-heading {align-items:flex-start;width:100%;text-align:left}
    .lebd-question-text {flex:1;min-width:0;width:100%;text-align:justify;text-justify:inter-word;line-height:1.75;white-space:normal;word-break:normal;overflow-wrap:break-word}
    .lebd-question-text p, .lebd-question-text div {max-width:100%;text-align:justify!important;text-justify:inter-word;white-space:normal!important}
    .lebd-question-text img, .lebd-answer-option img {max-width:100%!important;height:auto!important}
    .lebd-answer-option {width:100%;align-items:flex-start;text-align:justify;text-justify:inter-word;line-height:1.7;word-break:normal;overflow-wrap:break-word}
    .lebd-answer-option span {display:block;flex:1;min-width:0;text-align:justify;text-justify:inter-word;white-space:normal}
    .lebd-answer-option span p {margin:0; text-align:justify}
    @media(max-width:600px) {
        .lebd-exam-session{padding:110px 10px 126px!important;width:100%;max-width:100%}
        .lebd-session-header{top:0!important;padding:9px 10px!important;border-radius:0 0 12px 12px}
        .lebd-session-title{font-size:13px}
        .lebd-session-title > span:last-child{display:none}
        .lebd-question-card{padding:12px 10px}
        .lebd-question-heading{gap:7px;line-height:1.7}
        .lebd-question-text,.lebd-answer-option span{font-size:13.5px;line-height:1.75}
        .lebd-answer-option{padding:10px 9px;gap:9px}
        .lebd-session-footer{padding:8px 10px calc(8px + env(safe-area-inset-bottom))}
    }
    @media(max-width:360px){.lebd-stat small{font-size:9px}.lebd-stat strong{font-size:13px}}
    /* STEP-25: balanced horizontal gutters, without changing exam behavior. */
    :root { --lebd-exam-gutter: 14px; --lebd-exam-content-width: 930px; }
    .lebd-exam-session {
        width: 100% !important;
        max-width: var(--lebd-exam-content-width) !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: var(--lebd-exam-gutter) !important;
        padding-right: var(--lebd-exam-gutter) !important;
        box-sizing: border-box !important;
    }
    .lebd-session-header {
        width: calc(100% - 2 * var(--lebd-exam-gutter)) !important;
        max-width: calc(var(--lebd-exam-content-width) - 2 * var(--lebd-exam-gutter)) !important;
        left: 50% !important;
        right: auto !important;
        transform: translateX(-50%) !important;
        box-sizing: border-box !important;
    }
    .lebd-session-footer-inner {
        width: 100% !important;
        max-width: calc(var(--lebd-exam-content-width) - 2 * var(--lebd-exam-gutter)) !important;
        margin-left: auto !important;
        margin-right: auto !important;
        box-sizing: border-box !important;
    }
    .lebd-question-card { width: 100%; box-sizing: border-box; }
    @media (max-width: 600px) {
        :root { --lebd-exam-gutter: 12px; }
        .lebd-session-footer { padding-left: var(--lebd-exam-gutter) !important; padding-right: var(--lebd-exam-gutter) !important; }
    }

    /* STEP-26: polished reference-style exam UI. CSS only; keep form and scripts intact. */
    :root { --lebd-exam-gutter: 12px; }
    html,body {background:#f2f6fd!important;}
    .lebd-exam-session {--lebd-exam-content-width:930px; padding-bottom:125px!important;}
    .lebd-session-header {background:#fff!important;box-shadow:0 4px 19px rgba(13,50,113,.10)!important;border:1px solid #dbe8f9!important;}
    .lebd-session-title {font-size:17px;color:#082d65;margin-bottom:10px;}
    .lebd-session-stats {gap:9px;}
    .lebd-stat {border-radius:12px;padding:9px 5px;}
    .lebd-stat small {font-weight:700;}
    .lebd-stat strong {font-size:20px;}
    .lebd-stat:nth-child(2) {background:#eafaf0;border-color:#d4f2e0;}
    .lebd-stat:nth-child(2) strong {color:#00844d;}
    .lebd-question-card {border-radius:17px;border-color:#d6e4f6;box-shadow:0 3px 12px rgba(31,73,128,.055);padding:17px;margin-top:14px;}
    .lebd-question-number {color:#fff;background:#0969ed;border-radius:8px;padding:5px 9px;font-size:14px;line-height:1.5;}
    .lebd-question-heading {align-items:flex-start;gap:10px;line-height:1.55;}
    .lebd-question-text {padding-top:4px;color:#0b2449;font-weight:750;}
    .lebd-answer-option {border:1px solid #d4e3f6;border-radius:11px;background:#fff;min-height:49px;padding:11px 13px;gap:12px;margin-bottom:8px;align-items:center;}
    .lebd-answer-option input {width:17px;height:17px;margin:0;}
    .lebd-answer-option:has(input:checked) {background:#e9f4ff;border-color:#0b73ee;box-shadow:inset 0 0 0 1px rgba(11,115,238,.18);}
    .lebd-session-footer {border-top-color:#d3e2f5;box-shadow:0 -4px 14px rgba(21,69,129,.08);}
    .lebd-submit-btn {background:#075ce8;border-radius:12px;box-shadow:0 4px 9px rgba(0,84,220,.16);}
    @media(max-width:600px) {
      :root {--lebd-exam-gutter:12px;}
      .lebd-exam-session {padding-top:128px!important;padding-bottom:99px!important;}
      .lebd-session-header {padding:10px 9px!important;}
      .lebd-session-title {font-size:14px;line-height:1.4;margin-bottom:8px;}
      .lebd-session-stats {gap:5px;}
      .lebd-stat {padding:6px 2px;border-radius:9px;}
      .lebd-stat small {font-size:9px;line-height:1.3;}
      .lebd-stat strong {font-size:15px;line-height:1.5;}
      .lebd-question-card {margin-top:9px;padding:11px 10px;border-radius:13px;}
      .lebd-question-heading {margin-bottom:9px;gap:7px;line-height:1.5;}
      .lebd-question-number {font-size:12px;padding:4px 6px;border-radius:7px;}
      .lebd-question-text {font-size:13px;line-height:1.55;padding-top:2px;}
      .lebd-answer-option {min-height:41px;padding:7px 9px;margin-bottom:6px;border-radius:8px;gap:9px;line-height:1.5;}
      .lebd-answer-option input {height:15px;width:15px;}
      .lebd-answer-option span {font-size:12.5px!important;line-height:1.5!important;}
      .lebd-footer-count {font-size:11px;}
      .lebd-submit-btn {padding:10px 13px;font-size:12px;min-height:40px;}
      .lebd-session-footer {padding-top:8px;padding-bottom:calc(8px + env(safe-area-inset-bottom));}
    }
    @media(max-width:360px) { .lebd-exam-session{padding-top:125px!important;} .lebd-question-card{padding:10px 9px;} .lebd-answer-option{min-height:39px;padding:6px 8px;} }
    /* STEP-27: readable touch-size choices, radio only; no answer JS changes. */
    .lebd-answer-option {min-height:57px;padding:13px 14px;gap:12px;align-items:center;}
    .lebd-answer-option input[type="radio"] {width:21px;height:21px;min-width:21px;min-height:21px;margin:0;flex-shrink:0;cursor:pointer;}
    @media(max-width:600px) {
      .lebd-answer-option {min-height:51px;padding:10px 11px;margin-bottom:7px;gap:11px;border-radius:9px;}
      .lebd-answer-option input[type="radio"] {width:20px;height:20px;min-width:20px;min-height:20px;margin:0;}
      .lebd-answer-option span {font-size:13px!important;line-height:1.6!important;}
    }
    @media(max-width:360px) {
      .lebd-answer-option {min-height:49px;padding:9px 10px;}
    }
</style>
</head>
<body>
<div class="lebd-exam-session" id="lebdExamSession">
    <div class="lebd-session-header" role="region" aria-label="পরীক্ষার অগ্রগতি">
        <div class="lebd-session-title">
            <span>{{ $c['exam']->name }} — পরীক্ষা চলছে</span>
            <span style="font-size:11px;color:#617490">পরীক্ষা জমা না দেওয়া পর্যন্ত পেজে থাকুন</span>
        </div>
        <div class="lebd-session-stats">
            <div class="lebd-stat"><small>মোট প্রশ্ন</small><strong id="lebdTotal">{{ $questionTotal }}</strong></div>
            <div class="lebd-stat"><small>উত্তর দিয়েছেন</small><strong id="lebdAnswered">0</strong></div>
            <div class="lebd-stat remaining"><small>বাকি</small><strong id="lebdRemaining">{{ $questionTotal }}</strong></div>
            <div class="lebd-stat timer" id="lebdTimerBox"><small>সময় বাকি</small><strong id="lebdClock" role="timer">--:--</strong></div>
        </div>
    </div>
    <form method="POST" action="{{ $submitUrl }}" id="lebdRoutineForm">
        @csrf
        <input type="hidden" name="attempt_id" value="{{ $attempt->id }}">
        @foreach($questions as $n => $question)
            <section class="lebd-question-card">
                <div class="lebd-question-heading">
                    <span class="lebd-question-number">Q-{{ str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT) }})</span>
                    <div class="lebd-question-text">{!! $question->title !!}</div>
                </div>
                @foreach($options->get($question->id, collect()) as $i => $option)
                    <label class="lebd-answer-option">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}">
                        <span>{{ chr(65 + $i) }}. {!! $option->item !!}</span>
                    </label>
                @endforeach
            </section>
        @endforeach
    </form>
</div>
<div class="lebd-session-footer" aria-label="পরীক্ষা জমা">
    <div class="lebd-session-footer-inner">
        <div class="lebd-footer-count">উত্তর দিয়েছেন: <strong id="lebdFooterAnswered">0</strong> / {{ $questionTotal }}<div>বাকি: <strong id="lebdFooterRemaining">{{ $questionTotal }}</strong></div></div>
        <button class="lebd-submit-btn" id="lebdSubmitRequest" type="button">পরীক্ষা জমা দিন</button>
    </div>
</div>
<div class="lebd-warning-backdrop" id="lebdSubmitWarning" hidden>
    <div class="lebd-warning-dialog" role="dialog" aria-modal="true" aria-labelledby="lebdWarningTitle" aria-describedby="lebdWarningText">
        <h2 id="lebdWarningTitle">⚠️ পরীক্ষা জমা দেওয়ার আগে নিশ্চিত করুন</h2>
        <p id="lebdWarningText">আপনি কি প্রশ্নপত্র জমা দিতে চান?</p>
        <div class="lebd-warning-totals">
            মোট প্রশ্ন: <strong>{{ $questionTotal }}</strong><br>
            উত্তর দিয়েছেন: <strong id="lebdWarningAnswered">0</strong><br>
            উত্তর দেননি: <strong id="lebdWarningRemaining">{{ $questionTotal }}</strong>
        </div>
        <div class="lebd-dialog-actions">
            <button type="button" class="lebd-cancel-btn" id="lebdCancelSubmit">পরীক্ষায় ফিরে যান</button>
            <button type="button" class="lebd-confirm-btn" id="lebdConfirmSubmit">হ্যাঁ, জমা দিন</button>
        </div>
    </div>
</div>
<script>
(function () {
    'use strict';
    const form = document.getElementById('lebdRoutineForm');
    const modal = document.getElementById('lebdSubmitWarning');
    const submitRequest = document.getElementById('lebdSubmitRequest');
    const confirmButton = document.getElementById('lebdConfirmSubmit');
    const cancelButton = document.getElementById('lebdCancelSubmit');
    const timerBox = document.getElementById('lebdTimerBox');
    const clock = document.getElementById('lebdClock');
    const total = @json($questionTotal);
    const deadline = Date.parse(@json($endISO));
    const serverOffset = Date.parse(@json($serverISO)) - Date.now();
    let submitting = false;
    let expired = false;

    function tally() {
        const answered = form.querySelectorAll('input[type="radio"]:checked').length;
        const remaining = Math.max(0, total - answered);
        document.getElementById('lebdAnswered').textContent = answered;
        document.getElementById('lebdRemaining').textContent = remaining;
        document.getElementById('lebdFooterAnswered').textContent = answered;
        document.getElementById('lebdFooterRemaining').textContent = remaining;
        document.getElementById('lebdWarningAnswered').textContent = answered;
        document.getElementById('lebdWarningRemaining').textContent = remaining;
    }
    function submitNow() {
        if (submitting) return;
        submitting = true;
        modal.hidden = true;
        submitRequest.disabled = true;
        confirmButton.disabled = true;
        // Existing server-side submission and answer validation remain unchanged.
        form.submit();
    }
    function showWarning() {
        if (submitting || expired) return;
        tally();
        modal.hidden = false;
        cancelButton.focus();
    }
    function closeWarning() {
        modal.hidden = true;
        submitRequest.focus();
    }
    form.addEventListener('change', tally);
    form.addEventListener('submit', function (event) {
        if (!submitting) {
            event.preventDefault();
            showWarning();
        }
    });
    submitRequest.addEventListener('click', showWarning);
    confirmButton.addEventListener('click', submitNow);
    cancelButton.addEventListener('click', closeWarning);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeWarning();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            event.preventDefault();
            closeWarning();
        }
    });
    // Browser-native navigation confirmation: browsers ultimately control Back/Refresh.
    window.addEventListener('beforeunload', function (event) {
        if (submitting) return;
        event.preventDefault();
        event.returnValue = '';
    });
    function tick() {
        const left = Math.max(0, deadline - (Date.now() + serverOffset));
        const min = Math.floor(left / 60000);
        const sec = Math.floor((left % 60000) / 1000);
        clock.textContent = left > 0 ? min + ':' + String(sec).padStart(2, '0') : 'সময় শেষ';
        timerBox.classList.toggle('is-urgent', left <= 60000);
        if (left <= 0 && !submitting) {
            expired = true;
            submitNow(); // Automatic submission on time expiry, as before.
        }
    }
    tally();
    tick();
    setInterval(tick, 1000);
})();
</script>
</body>
</html>
