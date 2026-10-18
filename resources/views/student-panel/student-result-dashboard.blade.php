{{--
    Standalone design. Data is supplied by App\Services\StudentResultDashboard.
    This view NEVER queries an exam by student ID taken from the URL.
    Original resources/views/student-panel/result.blade.php is NOT changed.
--}}
<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $paper->name }} — Student Result | Live Education BD</title>
    <style>
        :root {
            --blue: #1864dc; --navy: #142844; --muted: #687a93;
            --light: #f3f7fc; --line: #dfe8f2; --green: #0f9d66;
            --red: #ea4555; --violet: #7553dd; --orange: #f69b20;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--light); color: var(--navy);
            font-family: 'Noto Sans Bengali', 'Hind Siliguri', Arial, sans-serif; }
        button, a { font: inherit; }
        .result-container { width: min(1380px, 100% - 32px); margin: 26px auto 44px; }
        .result-card { border: 1px solid var(--line); background: #fff;
            box-shadow: 0 5px 21px rgba(28, 56, 99, .035); border-radius: 15px; }
        .result-header { padding: 20px 24px; display: flex; align-items: center;
            justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 16px; }
        .brand-line { display: flex; align-items: center; gap: 13px; }
        .header-icon { background: #e6f0ff; color: #1767da; border-radius: 12px;
            width: 51px; height: 51px; display: grid; place-items: center; font-size: 25px; }
        h1 { font-size: 23px; margin: 0 0 5px; }
        .subtle { color: var(--muted); font-size: 13px; }
        h2 { font-size: 17px; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
        .small-icon { color: var(--blue); font-size: 21px; }
        .result-actions { display: flex; gap: 9px; flex-wrap: wrap; }
        .btn { cursor: pointer; border: 1px solid #cbdaf0; color: #245586;
            background: #f5f9ff; border-radius: 9px; padding: 10px 14px;
            font-weight: 700; font-size: 13px; text-decoration: none; }
        .btn-primary { background: var(--blue); color: white; border-color: var(--blue); }
        .btn-green { background: #079c62; color: white; border-color: #079c62; }
        .btn:hover { filter: brightness(.97); }
        .hero { padding: 22px 24px; margin-bottom: 16px; }
        .hero-top { display: flex; justify-content: space-between; align-items: center;
            gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
        .hero-title { font-weight: 800; font-size: 23px; margin: 0; }
        .hero-meta { display: flex; flex-wrap: wrap; gap: 9px; }
        .chip { border: 1px solid #dbe6f4; background: #f5f8ff; border-radius: 999px;
            padding: 6px 11px; font-size: 12px; font-weight: 700; color: #506684; }
        .chip.success { background: #e3f8ed; color: #087744; border-color: #c4efd8; }
        .chip.danger { background: #ffecee; color: #b52f44; border-color: #f8d6dc; }
        .chip.neutral { background: #f5f6f8; color: #5d6b7b; }
        .hero-details { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 13px; background: #f7faff; border: 1px solid #e2ecfb;
            border-radius: 12px; padding: 15px; }
        .hero-detail small { display: block; font-size: 12px; color: #73839c; margin-bottom: 5px; }
        .hero-detail b { overflow-wrap: anywhere; font-size: 14px; }
        .score-block { display: grid; grid-template-columns: 245px 1fr; gap: 16px;
            margin-top: 17px; background: #ebf5ff; border-radius: 13px; padding: 16px; }
        .main-gauge { min-height: 220px; display: grid; place-items: center; }
        .score-ring { --value: 0%; width: 205px; height: 205px; border-radius: 50%;
            background: conic-gradient(#287df2 var(--value), #d7e9fd 0);
            display: grid; place-items: center; transform: rotate(-90deg); }
        .score-ring-inner { background: #fff; width: 155px; height: 155px; border-radius: 50%;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            transform: rotate(90deg); box-shadow: inset 0 0 0 1px #dbe8f7; }
        .score-ring-inner strong { font-size: 39px; color: var(--navy); }
        .score-ring-inner small { font-size: 12px; color: var(--muted); }
        .score-ring-inner em { font-style: normal; font-size: 12px; font-weight: 800;
            padding: 3px 11px; background: #dff8eb; color: #11875c;
            border-radius: 999px; margin-top: 7px; }
        .score-ring-inner em.failed { background: #ffe3e7; color: #cb3246; }
        .score-metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .score-stat { background: #fff; border: 1px solid #eef3fa; border-radius: 12px;
            padding: 14px 12px; display: flex; flex-direction: column; justify-content: center; }
        .score-stat small { font-size: 12px; color: #6f8096; margin-bottom: 9px; }
        .score-stat b { font-size: 22px; }
        .score-stat.green b { color: var(--green); }
        .score-stat.red b { color: var(--red); }
        .score-stat.blue b { color: var(--blue); }
        .score-stat.orange b { color: #c97511; }
        .policy-note { background: #fff8e8; border: 1px solid #ffe0a4; padding: 11px 16px;
            color: #85561c; border-radius: 9px; font-size: 13px; margin-top: 14px; line-height: 1.65; }
        .overview-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 13px; margin-bottom: 15px; }
        .overview-stat { padding: 15px 13px; border-radius: 13px; border: 1px solid #dfebf8;
            background: #f0f8ff; display: flex; gap: 12px; align-items: center; min-height: 107px; }
        .overview-stat:nth-child(2) { background: #effbf4; border-color: #cceede; }
        .overview-stat:nth-child(3) { background: #f6f0ff; border-color: #e3d8f9; }
        .overview-stat:nth-child(4) { background: #fff1f2; border-color: #f6d9de; }
        .overview-stat:nth-child(5) { background: #fff7ed; border-color: #fee7cb; }
        .overview-icon { flex: none; background: #2377e8; color: #fff; border-radius: 50%;
            width: 44px; height: 44px; display: grid; place-items: center; font-size: 20px; }
        .overview-stat:nth-child(2) .overview-icon { background: #12a467; }
        .overview-stat:nth-child(3) .overview-icon { background: #7553dd; }
        .overview-stat:nth-child(4) .overview-icon { background: #ec4c5c; }
        .overview-stat:nth-child(5) .overview-icon { background: #fa9d1c; }
        .overview-stat small { font-size: 11px; font-weight: 700; color: #5d718a; }
        .overview-stat strong { display: block; font-size: 24px; margin-top: 7px; }
        .overview-stat span { font-size: 11px; color: #72839d; }
        .dash-grid { display: grid; grid-template-columns: 1.08fr 1fr .72fr;
            gap: 14px; align-items: start; }
        .dash-column { display: flex; flex-direction: column; gap: 14px; min-width: 0; }
        .section { padding: 17px; min-width: 0; }
        .data-table-wrap { overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; font-size: 12px; text-align: left; }
        th { background: #f0f5fc; color: #50617a; font-weight: 800; }
        th, td { padding: 10px 9px; border: 1px solid #e4ebf3; white-space: nowrap; }
        tbody tr:nth-child(even) { background: #fbfdff; }
        .subject-name, .department-name { white-space: normal; min-width: 135px; }
        .subject-badge { display: inline-block; background: #e6f2ff; color: #1464b9;
            border-radius: 5px; padding: 4px 7px; font-size: 11px; font-weight: 700; }
        .subject-badge.alt1 { background: #def9e9; color: #068555; }
        .subject-badge.alt2 { background: #fff0d8; color: #ab6114; }
        .subject-badge.alt3 { background: #f0e8ff; color: #7553c8; }
        .highlight-number { color: #0d874e; font-weight: 800; }
        .empty { color: #74859b; padding: 16px; font-size: 13px; }
        .question-breakdown { margin-top: 12px; display: flex; justify-content: space-around;
            text-align: center; flex-wrap: wrap; gap: 10px; }
        .question-breakdown b { display: block; font-size: 20px; }
        .question-breakdown span { font-size: 11px; color: var(--muted); }
        .donut-layout { display: flex; align-items: center; justify-content: space-evenly;
            gap: 16px; flex-wrap: wrap; }
        .donut { width: 142px; height: 142px; flex: none; border-radius: 50%;
            display: grid; place-items: center; position: relative; }
        .donut-inner { width: 98px; height: 98px; background: white;
            border-radius: 50%; display: grid; place-content: center; text-align: center; }
        .donut-inner b { font-size: 22px; }
        .donut-inner span { color: var(--muted); font-size: 10px; }
        .legend { font-size: 12px; }
        .legend div { margin: 8px 0; white-space: nowrap; }
        .swatch { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
        .bar-chart { height: 180px; display: flex; align-items: end; justify-content: space-around;
            padding: 10px 0 22px; gap: 7px; border-bottom: 1px solid #e1e8f3; }
        .bar-item { min-width: 0; width: 20%; display: flex; flex-direction: column;
            align-items: center; justify-content: end; height: 100%; }
        .bar-item b { font-size: 11px; margin-bottom: 6px; }
        .bar { min-height: 2px; width: min(42px, 75%); border-radius: 5px 5px 0 0; }
        .bar-item span { font-size: 10px; white-space: nowrap; margin-top: 6px; }
        .bar-1 { background: #ec5260; } .bar-2 { background: #fba23d; }
        .bar-3 { background: #f8c02d; } .bar-4 { background: #45c795; }
        .bar-5 { background: #2696e0; }
        .rank-me { color: #0967ce; font-weight: 800; }
        .performance-track { background: #e7eef7; border-radius: 999px; height: 6px; width: 100%; overflow: hidden; }
        .performance-fill { background: #20a6d2; border-radius: 999px; height: 100%; }
        .review-section { margin-top: 15px; }
        .review-tools { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
        .filter-btn { padding: 8px 13px; border: 1px solid #d2dfee; background: white;
            color: #516480; cursor: pointer; border-radius: 8px; font-size: 12px; font-weight: 700; }
        .filter-btn.active { background: #1769d5; color: #fff; border-color: #1769d5; }
        .question-review { border: 1px solid #dfebf7; border-radius: 11px; padding: 14px;
            margin: 10px 0; background: #fcfdff; }
        .review-head { display: flex; justify-content: space-between; gap: 8px;
            flex-wrap: wrap; align-items: center; margin-bottom: 9px; }
        .review-label { font-size: 12px; font-weight: 800; color: #125eaa; }
        .question-review p { line-height: 1.7; font-size: 13px; margin: 6px 0; overflow-wrap: anywhere; }
        .answer-row { background: #fff; border: 1px solid #e4ebf4; border-radius: 8px;
            padding: 8px 10px; font-size: 12px; margin: 7px 0; overflow-wrap: anywhere; }
        .answer-row strong { margin-right: 5px; }
        .answer-row.good { background: #effaf2; border-color: #cbeed4; }
        .answer-row.bad { background: #fff1f2; border-color: #f8d7dc; }
        .empty-state { text-align: center; margin: 60px auto; width: min(550px, 100%);
            padding: 36px 23px; }
        .empty-state h2 { justify-content: center; }
        .footer-note { text-align: center; color: #8391a6; font-size: 12px; margin: 15px 0; }
        @media (max-width: 1180px) {
            .dash-grid { grid-template-columns: 1fr 1fr; }
            .dash-column:last-child { grid-column: 1/-1; display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); }
            .overview-stats { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 780px) {
            .score-block { grid-template-columns: 1fr; }
            .score-metrics { grid-template-columns: repeat(2,minmax(0,1fr)); }
            .hero-details { grid-template-columns: repeat(2,minmax(0,1fr)); }
            .dash-grid { grid-template-columns: 1fr; }
            .dash-column:last-child { grid-column: auto; display: flex; }
            .overview-stats { grid-template-columns: repeat(2,minmax(0,1fr)); }
        }
        @media (max-width: 520px) {
            .result-container { width: calc(100% - 16px); margin-top: 9px; }
            .result-header, .hero, .section { padding: 15px 12px; }
            .hero-title { font-size: 19px; }
            h1 { font-size: 20px; }
            .overview-stats { gap: 7px; }
            .overview-stat { min-height: 90px; padding: 10px 7px; gap: 8px; }
            .overview-icon { width: 33px; height: 33px; font-size: 15px; }
            .overview-stat strong { font-size: 19px; }
            .score-stat b { font-size: 18px; }
            .hero-details { gap: 9px; padding: 10px; }
        }
        @media print {
            body { background: white; }
            .result-container { width: 100%; margin: 0; }
            .result-card { box-shadow: none; break-inside: avoid; }
            .no-print, .review-tools { display: none!important; }
            .dash-grid { grid-template-columns: 1fr 1fr; }
            .dash-column:last-child { grid-column: 1/-1; }
            .question-review { break-inside: avoid; }
        }

/* ================================================
   LEBD STUDENT RESULT - ORDERED RESPONSIVE LAYOUT
   Paste at the END of existing <style> in Blade.
   Data, routes and calculations remain unchanged.
   ================================================ */
html, body { max-width: 100%; overflow-x: clip; }
.result-container { width: min(1160px, calc(100% - 24px)); margin: 14px auto 40px; }
.result-container, .result-container * { box-sizing: border-box; }
.result-card, .section, .hero, .dash-grid { min-width: 0; max-width: 100%; }
.result-header { background: linear-gradient(118deg, #073e81, #126de4); color: #fff; border: 0; }
.result-header .subtle { color: #e5f0ff; }
.result-header .header-icon { background: rgba(255,255,255,.15); color: #fff; }
.result-header .btn { background: rgba(255,255,255,.14); color: #fff; border-color: rgba(255,255,255,.36); }
.result-header .btn-primary { background: #fff; color: #1456b6; }
.result-header .btn-green { background: #058b64; color: #fff; }
.hero { overflow: hidden; }
.hero-details, .score-block, .score-metrics, .overview-stats { min-width: 0; }
.overview-stats { grid-template-columns: repeat(5, minmax(0, 1fr)); }
.overview-stat { min-width: 0; }
.overview-stat > div:last-child { min-width: 0; overflow-wrap: anywhere; }

/* Flatten the previous three columns so the sections can be
   placed in the requested sequential order. */
.dash-grid {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 14px !important;
    align-items: stretch !important;
}
.dash-grid > .dash-column { display: contents !important; }
.dash-grid > .dash-column > section { min-width: 0; width: 100%; margin: 0; }

/* The combined Subject + Department card comes first, full width.
   These remaining sections follow the requested mobile reference order. */
.dash-grid .top-performers-card { order: 1; }
.dash-grid .my-attempts-card { order: 2; }
.dash-grid .score-distribution-card { order: 3; }
.dash-grid .question-solve-card { order: 4; }
.dash-grid .overall-result-card { order: 5; }
.dash-grid .answer-breakdown-card { order: 6; }
.dash-grid .quick-actions-card { order: 7; }

.section { overflow: hidden; }
.section h2 { color: #103b73; line-height: 1.45; padding-bottom: 9px;
    border-bottom: 1px solid #e5edf7; margin-bottom: 14px; }
.data-table-wrap { width: 100%; max-width: 100%; overflow-x: auto !important;
    overflow-y: hidden; -webkit-overflow-scrolling: touch; border-radius: 9px; }
.data-table-wrap table { min-width: max-content; max-width: none; width: 100%; }
.data-table-wrap th, .data-table-wrap td { font-size: 12px; padding: 9px 8px; }
.data-table-wrap::-webkit-scrollbar { height: 5px; }
.data-table-wrap::-webkit-scrollbar-thumb { background: #b7ccec; border-radius: 10px; }
.question-breakdown { gap: 8px; }
.bar-chart { min-width: 0; }
.bar-item { min-width: 0; flex: 1 1 0; }
.bar-item span { overflow-wrap: anywhere; white-space: normal; text-align: center; line-height: 1.2; }
.review-section { clear: both; }

@media (max-width: 1000px) {
    .score-block { grid-template-columns: 205px minmax(0, 1fr); }
    .overview-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .score-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 760px) {
    .dash-grid { grid-template-columns: minmax(0, 1fr) !important; }
    .score-block { grid-template-columns: minmax(0, 1fr); }
    .overview-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .hero-details { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .result-actions { width: 100%; }
    .result-actions .btn { flex: 1 1 120px; text-align: center; }
}
@media (max-width: 520px) {
    .result-container { width: calc(100% - 16px); margin: 8px auto 24px; }
    .result-header, .hero, .section { padding: 14px 12px; }
    .result-header { border-radius: 12px; }
    .hero-details { gap: 9px; padding: 10px; }
    .hero-top { gap: 9px; }
    .hero-title { font-size: 20px; }
    .score-block { padding: 12px 9px; }
    .score-ring { width: 174px; height: 174px; }
    .score-ring-inner { width: 132px; height: 132px; }
    .score-ring-inner strong { font-size: 32px; }
    .main-gauge { min-height: 184px; }
    .score-metrics { gap: 7px; }
    .score-stat { padding: 10px 9px; }
    .score-stat b { font-size: 19px; }
    .overview-stats { gap: 8px; }
    .overview-stat { padding: 11px 9px; min-height: 90px; gap: 8px; }
    .overview-stat:nth-child(5) { grid-column: 1 / -1; }
    .overview-icon { width: 35px; height: 35px; font-size: 16px; }
    .overview-stat strong { font-size: 19px; margin-top: 3px; }
    .dash-grid { gap: 10px !important; }
    .section h2 { font-size: 15px; }
    .donut-layout { justify-content: center; }
    .bar-chart { gap: 3px; }
    .bar-item b, .bar-item span { font-size: 9px; }
    .chip { padding: 5px 8px; }
    .review-tools { gap: 6px; }
    .filter-btn { padding: 7px 10px; }
}
@media print {
    .dash-grid { grid-template-columns: repeat(2, minmax(0,1fr)) !important; }
    .result-header { background: #fff !important; color: #172b4d !important; }
    .result-header .subtle { color: #6b798d !important; }
}


/* ===== ONE BOX: PAPER SUBJECT + DEPARTMENT SUMMARY ===== */
.subject-department-combined { margin-bottom: 14px; padding: 20px; overflow: hidden; }
.combined-summary-head { display: flex; align-items: center; justify-content: space-between;
    gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
.combined-summary-head h2 { border-bottom: 0; margin: 0 0 5px; padding-bottom: 0; }
.combined-summary-head .subtle { margin: 0; line-height: 1.7; }
.combined-summary-stats { display: grid; grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 9px; margin: 0 0 14px; }
.combined-summary-stats > div { background: #f2f7ff; border: 1px solid #e0eaf8;
    border-radius: 10px; padding: 11px 13px; min-width: 0; }
.combined-summary-stats small { display: block; color: #627790; font-size: 11px; margin-bottom: 5px; }
.combined-summary-stats strong { display: block; color: #1457ac; font-size: 21px; }
.combined-summary-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 14px; }
.combined-summary-panel { min-width: 0; overflow: hidden; border: 1px solid #dde8f5;
    border-radius: 12px; padding: 13px; background: #fcfdff; }
.combined-panel-heading { display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap: 7px; margin-bottom: 11px; }
.combined-panel-heading h3 { margin: 0; color: #134a8b; font-size: 15px; font-weight: 800; }
.combined-summary-panel .data-table-wrap { background: #fff; border: 1px solid #e4edf7; }
.combined-summary-panel table { width: 100%; }
.combined-summary-panel th, .combined-summary-panel td { padding: 10px 8px; }
.combined-summary-panel .summary-total-row { background:#e8f2ff; font-weight: 800; }
.combined-summary-note { font-size: 12px; color: #61758e; line-height: 1.65;
    margin: 12px 0 0; padding: 10px 12px; border-radius: 9px; background: #f5f9ff; }
@media (max-width: 1050px) {
    .combined-summary-grid { grid-template-columns: minmax(0,1fr); }
}
@media (max-width: 760px) {
    .subject-department-combined { padding: 13px 11px; }
    .combined-summary-stats { grid-template-columns: repeat(2,minmax(0,1fr)); gap: 7px; }
    .combined-summary-panel { padding: 10px; }
    .combined-summary-stats strong { font-size: 18px; }
}
@media print {
    .combined-summary-grid { grid-template-columns: minmax(0,1fr); }
    .combined-summary-panel { break-inside:avoid; }
}

/* ===== PAPER-BASED CATEGORY PASS/FAIL + COLLAPSIBLE QUESTION REVIEW ===== */
.subject-department-combined .combined-summary-stats { grid-template-columns:repeat(6,minmax(0,1fr)); }
.subject-department-combined .combined-summary-stats strong { font-size:20px; }
.combined-pass-key { color:#516480; font-size:12px; line-height:1.65; margin:0 0 12px;
    background:#f3f8ff; border:1px solid #e0ebf9; padding:10px 12px; border-radius:10px; }
.group-mark { display:flex; flex-direction:column; gap:3px; line-height:1.35; }
.group-mark b { font-size:13px; }
.group-mark small { color:#77869a; font-size:10px; }
.group-percent { font-weight:800; color:#224c79; }
.group-result-badge { display:inline-flex; align-items:center; justify-content:center;
    white-space:nowrap; border-radius:99px; padding:5px 9px;
    font-weight:800; font-size:11px; }
.group-result-badge.pass { color:#087548; background:#dcf8ea; border:1px solid #b9e9cd; }
.group-result-badge.fail { color:#bc3145; background:#ffebee; border:1px solid #fad6dd; }
.group-result-badge.none { color:#63738a; background:#f0f3f7; border:1px solid #e1e6eb; }
.review-accordion { margin-top:14px; overflow:hidden; }
.review-accordion > summary { list-style:none; cursor:pointer; display:flex;
    justify-content:space-between; align-items:center; padding:18px 20px; gap:12px;
    color:#123b73; font-weight:800; font-size:16px; }
.review-accordion > summary::-webkit-details-marker { display:none; }
.review-accordion > summary .review-open-hint { font-size:12px; color:#1357b6;
    padding:8px 11px; border-radius:8px; background:#eaf3ff; white-space:nowrap; }
.review-accordion[open] > summary .review-open-hint { background:#ecf2f7; }
.review-body { border-top:1px solid #e4edf7; padding:16px 18px 18px; }
.question-review { padding:0; overflow:hidden; }
.question-review > summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:8px; padding:13px 14px; }
.question-review > summary::-webkit-details-marker { display:none; }
.question-review > summary::after { content:'+'; font-size:20px; color:#1768d7; margin-left:auto; }
.question-review[open] > summary::after { content:'−'; }
.question-review .review-head { margin:0; }
.question-review > summary .review-head { display:flex; flex:1; justify-content:space-between;
    flex-wrap:wrap; gap:8px; align-items:center; }
.question-review .review-detail { border-top:1px solid #e4edf7; padding:11px 14px; }
@media(max-width:1000px){
    .subject-department-combined .combined-summary-stats { grid-template-columns:repeat(3,minmax(0,1fr)); }
}
@media(max-width:520px){
    .subject-department-combined .combined-summary-stats { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .review-accordion > summary { padding:14px 12px; font-size:14px; }
    .review-accordion > summary .review-open-hint { font-size:11px; padding:7px; }
    .review-body { padding:12px 10px; }
}
@media print {
    .review-accordion > summary .review-open-hint { display:none; }
}
    /* LEBD Result Dashboard – full-width subject/department tables */
/* Keep both reports in one box, but give each the entire available width. */
.subject-department-combined .combined-summary-grid {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 16px !important;
}
.subject-department-combined .combined-summary-panel {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow: visible;
    padding: 16px;
}
.subject-department-combined .data-table-wrap {
    overflow: visible !important;
    max-width: 100%;
    width: 100%;
}
.subject-department-combined .data-table-wrap table {
    width: 100% !important;
    min-width: 0 !important;
    table-layout: fixed;
    border-collapse: collapse;
}
.subject-department-combined .data-table-wrap th,
.subject-department-combined .data-table-wrap td {
    white-space: normal !important;
    overflow-wrap: anywhere;
    word-break: normal;
    padding: 10px 6px;
    font-size: 12px;
    line-height: 1.45;
    vertical-align: middle;
}
.subject-department-combined .data-table-wrap td.subject-name,
.subject-department-combined .data-table-wrap td.department-name {
    min-width: 0 !important;
    width: auto;
}
.subject-department-combined .data-table-wrap .subject-badge {
    white-space: normal;
    overflow-wrap: anywhere;
}
.subject-department-combined .group-mark b { font-size: 12px; }
.subject-department-combined .group-mark small { font-size: 10px; }
.subject-department-combined .group-result-badge {
    white-space: nowrap;
    padding: 5px 9px;
}
/* Percent widths sum to 100%, including the final Result column. */
.subject-department-combined .data-table-wrap th:nth-child(1) { width: 4%; }
.subject-department-combined .data-table-wrap th:nth-child(2) { width: 20%; }
.subject-department-combined .data-table-wrap th:nth-child(3) { width: 10%; }
.subject-department-combined .data-table-wrap th:nth-child(4) { width: 7%; }
.subject-department-combined .data-table-wrap th:nth-child(5) { width: 7%; }
.subject-department-combined .data-table-wrap th:nth-child(6) { width: 11%; }
.subject-department-combined .data-table-wrap th:nth-child(7) { width: 18%; }
.subject-department-combined .data-table-wrap th:nth-child(8) { width: 10%; }
.subject-department-combined .data-table-wrap th:nth-child(9) { width: 13%; }

/* On a phone nine tiny columns would be unreadable. Each row becomes
   one compact 2-column result card with ALL fields visible, no sideways scroll. */
@media (max-width: 720px) {
    .subject-department-combined { padding: 12px 10px !important; }
    .subject-department-combined .combined-summary-panel { padding: 10px; }
    .subject-department-combined .data-table-wrap { border: 0; background: transparent; }
    .subject-department-combined .data-table-wrap table,
    .subject-department-combined .data-table-wrap tbody {
        display: block;
        width: 100% !important;
        min-width: 0 !important;
    }
    .subject-department-combined .data-table-wrap thead { display: none; }
    .subject-department-combined .data-table-wrap tbody tr {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 5px 9px;
        margin: 0 0 10px;
        padding: 11px;
        border: 1px solid #dce8f7;
        border-radius: 12px;
        background: #fff;
    }
    .subject-department-combined .data-table-wrap tbody tr.summary-total-row {
        background: #eaf3ff;
        border-color: #bed6f6;
    }
    .subject-department-combined .data-table-wrap tbody tr td {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        gap: 3px;
        width: 100% !important;
        min-width: 0 !important;
        padding: 6px;
        border: 0;
        border-radius: 6px;
        text-align: left;
        font-size: 13px;
        background: transparent;
    }
    .subject-department-combined .data-table-wrap tbody tr td::before {
        display: block;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.35;
        color: #697b94;
        margin-bottom: 2px;
    }
    .subject-department-combined .data-table-wrap tbody tr:not(.summary-total-row) td:first-child { display: none; }
    .subject-department-combined .data-table-wrap tbody tr:not(.summary-total-row) td:nth-child(2) {
        grid-column: 1 / -1;
        background: #f3f8ff;
        border-radius: 8px;
        padding: 9px 10px;
    }
    .subject-department-combined .data-table-wrap tbody tr:not(.summary-total-row) td:nth-child(2)::before { content: 'বিষয় / বিভাগ'; }
    .subject-department-combined .data-table-wrap tbody tr:not(.summary-total-row) td:nth-child(3)::before { content: 'মোট প্রশ্ন'; }
    .subject-department-combined .data-table-wrap tbody tr:not(.summary-total-row) td:nth-child(4)::before { content: 'সঠিক'; }
    .subject-department-combined .data-table-wrap tbody tr:not(.summary-total-row) td:nth-child(5)::before { content: 'ভুল'; }
    .subject-department-combined .data-table-wrap tbody tr:not(.summary-total-row) td:nth-child(6)::before { content: 'উত্তর দেননি'; }
    .subject-department-combined .data-table-wrap tbody tr:not(.summary-total-row) td:nth-child(7)::before { content: 'প্রাপ্ত নম্বর / পাশ নম্বর'; }
    .subject-department-combined .data-table-wrap tbody tr:not(.summary-total-row) td:nth-child(8)::before { content: 'নম্বর %'; }
    .subject-department-combined .data-table-wrap tbody tr:not(.summary-total-row) td:nth-child(9)::before { content: 'ফলাফল'; }
    .subject-department-combined .data-table-wrap tbody tr.summary-total-row td:first-child {
        display: block;
        grid-column: 1 / -1;
        padding: 9px 10px;
        background: #d9eaff;
        font-weight: 800;
    }
    .subject-department-combined .data-table-wrap tbody tr.summary-total-row td:nth-child(2)::before { content: 'মোট প্রশ্ন'; }
    .subject-department-combined .data-table-wrap tbody tr.summary-total-row td:nth-child(3)::before { content: 'সঠিক'; }
    .subject-department-combined .data-table-wrap tbody tr.summary-total-row td:nth-child(4)::before { content: 'ভুল'; }
    .subject-department-combined .data-table-wrap tbody tr.summary-total-row td:nth-child(5)::before { content: 'উত্তর দেননি'; }
    .subject-department-combined .data-table-wrap tbody tr.summary-total-row td:nth-child(6)::before { content: 'প্রাপ্ত নম্বর / পাশ নম্বর'; }
    .subject-department-combined .data-table-wrap tbody tr.summary-total-row td:nth-child(7)::before { content: 'নম্বর %'; }
    .subject-department-combined .data-table-wrap tbody tr.summary-total-row td:nth-child(8)::before { content: 'ফলাফল'; }
    .subject-department-combined .data-table-wrap tbody tr td[colspan] { grid-column: 1 / -1; }
    .subject-department-combined .data-table-wrap .group-mark b { font-size: 13px; }
}
@media print {
    .subject-department-combined .combined-summary-grid { grid-template-columns: 1fr !important; }
    .subject-department-combined .combined-summary-panel { break-inside: avoid; }
}


/* LEBD compact Subject + Department single report (desktop + mobile). */
.subject-department-combined.compact-summary { max-width: 100%; }
.subject-department-combined .compact-table-wrap { overflow: visible !important; border: 1px solid #e1eafa; border-radius: 11px; }
.subject-department-combined .compact-group-table { width:100%; table-layout: fixed; border-collapse:collapse; }
.subject-department-combined .compact-group-table th,
.subject-department-combined .compact-group-table td { width:auto !important; min-width:0 !important; white-space:normal; vertical-align:middle; }
.subject-department-combined .compact-group-table th:nth-child(1) { width:4% !important; }
.subject-department-combined .compact-group-table th:nth-child(2) { width:26% !important; }
.subject-department-combined .compact-group-table th:nth-child(3) { width:11% !important; }
.subject-department-combined .compact-group-table th:nth-child(4) { width:9% !important; }
.subject-department-combined .compact-group-table th:nth-child(5) { width:9% !important; }
.subject-department-combined .compact-group-table th:nth-child(6) { width:9% !important; }
.subject-department-combined .compact-group-table th:nth-child(7) { width:15% !important; }
.subject-department-combined .compact-group-table th:nth-child(8) { width:17% !important; }
.subject-department-combined .compact-group-table td.cell-mark { font-weight:800; font-variant-numeric: tabular-nums; }
.subject-department-combined .compact-group-table td.cell-name { font-weight:750; }
.subject-department-combined .compact-group-table td.cell-result { text-align:center; }
.subject-department-combined .compact-group-table .compact-category-divider td { background:#ecf4ff; font-weight:800; color:#164f94; padding:9px 13px; }
.subject-department-combined .compact-group-table .compact-total-row td { background:#e6f1ff; font-weight:800; }
.subject-department-combined .compact-group-table .dept-type { display:inline-block; margin-left:5px; font-size:10px; color:#667a94; }
@media (max-width: 720px) {
  /* No horizontal scroll: one complete compact card with two lines per row. */
  .subject-department-combined.compact-summary { padding:12px 9px !important; }
  .subject-department-combined .compact-table-wrap { border:0 !important; background:transparent; }
  .subject-department-combined .compact-group-table,
  .subject-department-combined .compact-group-table tbody { display:block; width:100% !important; }
  .subject-department-combined .compact-group-table thead { display:none; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row {
      display:grid !important;
      grid-template-columns:repeat(5, minmax(0, 1fr)) !important;
      grid-template-rows:auto auto;
      gap:6px 3px;
      width:100%;
      margin:0 0 8px;
      padding:10px 8px;
      border-radius:11px;
      background:#fff;
      border:1px solid #dae5f3;
      box-shadow:0 2px 6px rgba(18,49,89,.035);
      align-items:stretch;
  }
  .subject-department-combined .compact-group-table tbody tr.compact-department-row { border-color:#c9d9fa; background:#fbfdff; }
  .subject-department-combined .compact-group-table tbody tr.compact-total-row { border-color:#aacaf0; background:#edf5ff; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td {
      display:flex !important;
      flex-direction:column !important;
      justify-content:center;
      align-items:center;
      min-width:0 !important;
      width:auto !important;
      margin:0;
      padding:5px 2px !important;
      border:0;
      border-radius:6px;
      background:#f4f8fd;
      text-align:center;
      font-size:12px;
      line-height:1.2;
      white-space:normal;
  }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td::before { display:block; color:#61738d; font-weight:650; font-size:9px; line-height:1.2; margin:0 0 3px; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-index { display:none !important; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-name {
      grid-column:1 / 4 !important; grid-row:1;
      align-items:flex-start; text-align:left; padding:3px 4px !important;
      background:transparent !important; gap:0;
      overflow-wrap:anywhere; font-size:13px;
  }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-name::before,
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-result::before { display:none !important; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-result {
      grid-column:4 / 6; grid-row:1;
      justify-self:end; align-self:center;
      padding:2px 0 !important; background:transparent !important;
  }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-total { grid-column:1; grid-row:2; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-correct { grid-column:2; grid-row:2; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-wrong { grid-column:3; grid-row:2; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-unanswered { grid-column:4; grid-row:2; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-mark { grid-column:5; grid-row:2; font-size:12px; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-total::before { content:'মোট' !important; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-correct::before { content:'সঠিক' !important; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-wrong::before { content:'ভুল' !important; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-unanswered::before { content:'বাকি' !important; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-mark::before { content:'মার্ক' !important; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row .group-result-badge { padding:4px 7px; font-size:11px; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td.cell-name .subject-badge { font-size:12px; padding:4px 7px; }
  .subject-department-combined .compact-group-table tbody tr.compact-category-divider { display:block !important; margin:10px 0 7px; padding:0 !important; border:0 !important; background:transparent !important; box-shadow:none; }
  .subject-department-combined .compact-group-table tbody tr.compact-category-divider td { display:block !important; padding:5px 2px !important; border:0; background:transparent !important; font-size:12px; font-weight:800; color:#12549e; }
  .subject-department-combined .compact-group-table tbody tr.compact-category-divider td::before { display:none !important; }
  .subject-department-combined .compact-group-table tbody tr.compact-empty-row { display:block !important; }
  .subject-department-combined .compact-group-table tbody tr.compact-empty-row td { display:block !important; border:0; }
  .subject-department-combined .compact-group-table tbody tr.compact-empty-row td::before { display:none !important; }
  .subject-department-combined .combined-summary-stats { grid-template-columns:repeat(3,minmax(0,1fr)) !important; }
  .subject-department-combined .combined-summary-stats strong { font-size:16px !important; }
  .subject-department-combined .compact-group-table .dept-type { margin:2px 0 0; }
}
@media (max-width: 370px) {
  .subject-department-combined .compact-group-table tbody tr.compact-result-row { padding:8px 5px; gap:5px 2px; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td { font-size:11px; }
  .subject-department-combined .compact-group-table tbody tr.compact-result-row td::before { font-size:9px; }
}


/* v3 profile, full-width batch merit, all circular graphics removed */
.result-header {gap:12px}
.student-profile {display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;max-width:100%;min-width:0}
.student-profile-text {text-align:right;min-width:0;display:flex;flex-direction:column;gap:3px}
.student-profile-text b {font-size:13px;max-width:185px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.student-profile-text small {font-size:11px;opacity:.82;white-space:nowrap}
.student-avatar {width:40px;height:40px;object-fit:cover;border-radius:50%;border:2px solid rgba(255,255,255,.65);flex:none}
.avatar-fallback {display:grid;place-items:center;color:#18549b;background:#fff;font-weight:800;font-size:18px}
.score-block {grid-template-columns:1fr!important}
.score-metrics {grid-template-columns:repeat(4,minmax(0,1fr))}
.main-gauge,.score-ring,.donut,.donut-inner {display:none!important}
.bar-summary {padding:8px 0 4px}
.bar-summary-head {display:flex;justify-content:space-between;gap:12px;font-size:13px;align-items:center;margin-bottom:12px}
.bar-summary-head strong {font-size:18px;color:#125ba3}
.bar-summary-track {height:13px;background:#ffe2e7;border-radius:9px;overflow:hidden}
.bar-summary-track span {height:100%;display:block;border-radius:9px}
.stacked-answers {display:flex;width:100%;height:14px;background:#edf2f7;border-radius:12px;overflow:hidden}
.stacked-answers span {height:100%;display:block}
.bar-summary-meta {display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-top:12px;font-size:12px;color:#546782}
.dash-grid .batch-merit-card {order:2;grid-column:1/-1;width:100%}
.dash-grid .my-attempts-card {order:3}
.dash-grid .score-distribution-card {order:4}
.dash-grid .question-solve-card {order:5}
.dash-grid .overall-result-card {order:6}
.dash-grid .answer-breakdown-card {order:7}
.dash-grid .quick-actions-card {order:8;grid-column:1/-1}
.batch-merit-card {overflow:hidden}
.merit-head {display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.merit-head h2 {margin-bottom:6px}
.merit-head p {margin:0 0 13px}
.merit-filters {display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.3fr);gap:10px;margin:10px 0 13px}
.merit-filters label {display:flex;flex-direction:column;gap:4px;font-size:11px;font-weight:800;color:#62778f}
.merit-filters select,.merit-filters input {width:100%;min-width:0;border:1px solid #d6e2f2;border-radius:9px;background:#fbfdff;padding:10px;font:inherit;font-size:13px;color:#193b65}
.merit-table-wrap {width:100%;overflow-x:auto}
.merit-table {min-width:600px;width:100%;table-layout:auto}
.merit-table th,.merit-table td {padding:10px 9px;white-space:normal;font-size:12px}
.merit-table td:nth-child(5){white-space:nowrap}
.merit-percent {display:block;color:#5f7896;font-size:10px}
.merit-me {background:#edf6ff!important}
.you-tag {display:inline-block;padding:2px 5px;background:#d2e5ff;color:#1665c5;font-weight:800;border-radius:4px}
.merit-mobile {display:none}
.merit-pagination {display:flex;justify-content:space-between;gap:12px;align-items:center;margin-top:13px;color:#64768e;font-size:12px;flex-wrap:wrap}
.merit-pagination>div {display:flex;align-items:center;gap:10px}
.merit-pagination button {background:#eff5ff;border:1px solid #d2e2f7;border-radius:7px;color:#1755a2;font-weight:800;padding:7px 12px;cursor:pointer}
.merit-pagination button:disabled{opacity:.4;cursor:not-allowed}
.merit-note {margin:12px 0 0;line-height:1.65}
[hidden] {display:none!important}
@media(max-width:760px){
 .result-header {flex-wrap:nowrap;justify-content:space-between;align-items:center}
 .result-header .brand-line {min-width:0;gap:8px}
 .result-header h1 {font-size:15px}
 .result-header .header-icon {width:32px;height:32px;font-size:17px}
 .result-header .subtle {font-size:9px;max-width:190px}
 .student-profile-text b {max-width:110px;font-size:11px}
 .student-profile-text small {font-size:9px}
 .student-avatar {width:33px;height:33px}
 .score-metrics {grid-template-columns:repeat(2,minmax(0,1fr))}
 .merit-table-wrap {display:none}
 .merit-mobile {display:flex;flex-direction:column;gap:7px}
 .merit-mobile-card {display:grid;grid-template-columns:30px minmax(0,1fr) 52px 45px;align-items:center;gap:6px;border:1px solid #dfe9f6;border-radius:9px;padding:9px 7px;min-width:0}
 .merit-mobile-rank {font-weight:800;color:#1468cc;font-size:12px}
 .merit-mobile-info {min-width:0;display:flex;flex-direction:column;gap:4px}
 .merit-mobile-info b {font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
 .merit-mobile-info small {font-size:10px;color:#62738b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
 .merit-mobile-score {display:flex;flex-direction:column;gap:2px;text-align:right}
 .merit-mobile-score b {font-size:12px}
 .merit-mobile-score small {color:#66809d;font-size:10px}
 .merit-mobile-card .group-result-badge {padding:4px;font-size:10px}
}
@media(max-width:375px){
 .result-header .subtle {display:none}
 .student-profile-text b {max-width:88px}
 .merit-mobile-card {grid-template-columns:27px minmax(0,1fr) 46px 43px;gap:4px;padding:8px 5px}
}
@media print {
 .merit-filters,.merit-pagination {display:none!important}
 .merit-table-wrap {display:block!important;overflow:visible!important}
 .merit-mobile {display:none!important}
 .merit-table .merit-entry[hidden] {display:table-row!important}
 .student-profile {color:#172b4d}
 .result-header .student-profile-text small {color:#172b4d}
}
    </style>
</head>
<body>

@if(!$exam)
    {{-- No Completed exam: no result, chart, rank or private data. --}}
    <main class="result-container">
        <div class="result-card empty-state">
            <h2>এই পরীক্ষার কোনো সম্পন্ন ফলাফল নেই</h2>
            <p class="subtle">পরীক্ষা সম্পন্ন করার পর, ফলাফল প্রকাশিত হলে এখানে দেখতে পারবেন।</p>
            <a class="btn btn-primary" href="{{ route('students.dashboard') }}">ড্যাশবোর্ডে ফিরুন</a>
        </div>
    </main>
@elseif(!$published)
    <main class="result-container">
        <div class="result-card empty-state">
            <h2>ফলাফল এখনো প্রকাশিত হয়নি</h2>
            <p class="subtle">আপনার পরীক্ষা সম্পন্ন হয়েছে। Question Paper-এ নির্ধারিত প্রকাশের সময় পর্যন্ত অপেক্ষা করুন।</p>
            <a class="btn btn-primary" href="{{ route('students.dashboard') }}">ড্যাশবোর্ডে ফিরুন</a>
        </div>
    </main>
@else
<main class="result-container" id="student-result">
    <header class="result-card result-header">
        <div class="brand-line">
            <div class="header-icon">▤</div>
            <div>
                <h1>MCQ Exam Results</h1>
                <div class="subtle">Live Education BD · Student Result &amp; Performance Analysis</div>
            </div>
        </div>
        @php
            $studentProfile = \Illuminate\Support\Facades\Auth::guard('student')->user();
            $rawPhoto = $studentProfile?->image;
            $studentPhoto = $rawPhoto
                ? (preg_match('~^https?://~i', $rawPhoto) ? $rawPhoto : asset(ltrim($rawPhoto, '/')))
                : null;
        @endphp
        <a class="student-profile" href="{{ route('students.dashboard') }}" aria-label="Student Dashboard">
            <span class="student-profile-text"><b>{{ $studentProfile?->name ?? 'Student' }}</b>
                <small>{{ $studentProfile?->reg_id ?: ('Student #' . ($studentProfile?->id ?? '')) }}</small></span>
            @if($studentPhoto)
                <img class="student-avatar" src="{{ $studentPhoto }}" alt="Profile" referrerpolicy="no-referrer"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='grid'">
                <span class="student-avatar avatar-fallback" style="display:none">{{ mb_substr($studentProfile?->name ?? 'S',0,1) }}</span>
            @else
                <span class="student-avatar avatar-fallback">{{ mb_substr($studentProfile?->name ?? 'S',0,1) }}</span>
            @endif
        </a>
    </header>

    <section class="result-card hero">
        <div class="hero-top">
            <div>
                <h2 class="hero-title">{{ $paper->name }}</h2>
                <div class="subtle">আপনার পরীক্ষার ফলাফল, প্রশ্নভিত্তিক বিশ্লেষণ এবং অন্যান্য শিক্ষার্থীদের সামগ্রিক পরিসংখ্যান</div>
            </div>
            <div class="hero-meta">
                <span class="chip">Attempt #{{ $exam->id }}</span>
                <span class="chip">{{ $exam->end_at ? \Carbon\Carbon::parse($exam->end_at)->format('d M Y, h:i A') : 'সম্পন্ন' }}</span>
                @if($passed === true)<span class="chip success">✓ Passed</span>
                @elseif($passed === false)<span class="chip danger">✕ Failed</span>
                @else<span class="chip neutral">পাস মার্ক নির্ধারিত নয়</span>@endif
            </div>
        </div>
        <div class="hero-details">
            <div class="hero-detail"><small>Course</small><b>{{ $courseName ?: '—' }}</b></div>
            <div class="hero-detail"><small>Batch</small><b>{{ $batchName ?: '—' }}</b></div>
            <div class="hero-detail"><small>Department</small><b>{{ $departmentName ?: '—' }}</b></div>
            <div class="hero-detail"><small>Exam No</small><b>{{ $paper->name }}</b></div>
        </div>

        <div class="score-block">
            <div class="score-metrics">
                <div class="score-stat blue"><small>মোট পূর্ণমান</small><b>{{ number_format($maximumMarks,2) }}</b></div>
                <div class="score-stat green"><small>প্রাপ্ত নম্বর</small><b>{{ number_format($score,2) }}</b></div>
                <div class="score-stat green"><small>সঠিক উত্তর</small><b>{{ $correct }}</b></div>
                <div class="score-stat red"><small>ভুল উত্তর</small><b>{{ $wrong }}</b></div>
                <div class="score-stat"><small>উত্তর না করা</small><b>{{ $unanswered }}</b></div>
                <div class="score-stat orange"><small>সময় লেগেছে</small><b>{{ $durationMinutes === null ? '—' : number_format((float) $durationMinutes, 3) . ' মি.' }}</b></div>
                <div class="score-stat blue"><small>আপনার সেরা র‍্যাঙ্ক</small><b>{{ $ownRank }}</b></div>
                <div class="score-stat green"><small>পাসের জন্য প্রয়োজন</small><b>{{ $passMark !== null ? number_format($passMark,2) : '—' }}</b></div>
            </div>
        </div>
        <div class="policy-note">
            <b>Pass Mark (Question Paper):</b> {{ $customPassMark === null ? '— (এখনও সেট করা হয়নি)' : number_format($customPassMark,2) }}
            &nbsp; | &nbsp; <b>বর্তমান নিয়ম:</b> {{ $passPolicy }}
            &nbsp; | &nbsp; <b>পূর্ণমান:</b> {{ number_format($maximumMarks,2) }}
            &nbsp; | &nbsp; <b>প্রশ্নপ্রতি নম্বর:</b> {{ number_format($perQuestionMark,2) }}
            &nbsp; | &nbsp; <b>নেগেটিভ মার্ক:</b> {{ number_format($minusMark,2) }}
        </div>
    </section>

    <section class="overview-stats" aria-label="All students aggregate statistics">
        <article class="overview-stat"><div class="overview-icon">♟</div><div><small>অংশগ্রহণকারী</small><strong>{{ number_format($participants) }}</strong><span>স্বতন্ত্র শিক্ষার্থী</span></div></article>
        <article class="overview-stat"><div class="overview-icon">✓</div><div><small>Attempted</small><strong>{{ number_format($participants) }}</strong><span>Completed Students</span></div></article>
        <article class="overview-stat"><div class="overview-icon">☑</div><div><small>Passed</small><strong>{{ $passedCount === null ? '—' : number_format($passedCount) }}</strong><span>প্রকাশিত ফলাফলের ভিত্তিতে</span></div></article>
        <article class="overview-stat"><div class="overview-icon">✕</div><div><small>Failed</small><strong>{{ $failedCount === null ? '—' : number_format($failedCount) }}</strong><span>প্রকাশিত ফলাফলের ভিত্তিতে</span></div></article>
        <article class="overview-stat"><div class="overview-icon">◷</div><div><small>Average Score</small><strong>{{ $maximumMarks > 0 ? number_format($averagePercent,1) . '%' : '—' }}</strong><span>প্রতিজনের সেরা Attempt</span></div></article>
    </section>

    {{-- Compact single-table paper result. Subject and selected paper department are rows in one table. --}}
    @php
        $paperDepartmentRows = collect($departmentRows)->filter(function ($row) use ($departmentName) {
            return $departmentName
                && mb_strtolower(trim((string) ($row['name'] ?? ''))) === mb_strtolower(trim((string) $departmentName));
        });
        // If the paper's department is not specifically mapped to questions,
        // preserve mapped department breakdown instead of inventing data.
        $visibleDepartmentRows = $paperDepartmentRows->isNotEmpty()
            ? $paperDepartmentRows->values()->all()
            : $departmentRows;
    @endphp
    <section class="result-card section subject-department-combined compact-summary" id="subject-department-summary">
        <div class="combined-summary-head">
            <div>
                <h2><span class="small-icon">▦</span> Subject &amp; Department Result</h2>
                <p class="subtle">{{ $paper->name }} — আপনার Completed Attempt #{{ $exam->id }}-এর ফলাফল</p>
            </div>
            <span class="chip">{{ count($subjectRows) }} Subjects · {{ count($visibleDepartmentRows) }} Departments</span>
        </div>
        <p class="combined-pass-key">পাসের শর্ত: প্রতিটি অংশের পূর্ণমানের {{ number_format($groupPassPercent,0) }}%। মার্কে প্রশ্নপ্রতি নম্বর ও নেগেটিভ মার্কিং ধরা হয়েছে।</p>
        <div class="data-table-wrap compact-table-wrap">
            <table class="compact-group-table">
                <thead>
                    <tr><th>#</th><th>বিষয় / বিভাগ</th><th>মোট প্রশ্ন</th><th>সঠিক</th><th>ভুল</th><th>বাকি</th><th>মার্ক</th><th>ফলাফল</th></tr>
                </thead>
                <tbody>
                    <tr class="compact-category-divider"><td colspan="8">বিষয়ভিত্তিক ফলাফল</td></tr>
                    @forelse($subjectRows as $i => $row)
                        <tr class="compact-result-row">
                            <td class="cell-index">{{ $i+1 }}</td>
                            <td class="cell-name"><span class="subject-badge alt{{ $i%4 }}">{{ $row['name'] }}</span></td>
                            <td class="cell-total">{{ $row['total'] }}</td>
                            <td class="cell-correct highlight-number">{{ $row['correct'] }}</td>
                            <td class="cell-wrong">{{ $row['wrong'] }}</td>
                            <td class="cell-unanswered">{{ $row['unanswered'] }}</td>
                            <td class="cell-mark">{{ number_format($row['score'],2) }}</td>
                            <td class="cell-result">
                                @if($row['passed'] === true)<span class="group-result-badge pass">✓ Pass</span>
                                @elseif($row['passed'] === false)<span class="group-result-badge fail">✕ Fail</span>
                                @else<span class="group-result-badge none">—</span>@endif
                            </td>
                        </tr>
                    @empty
                        <tr class="compact-empty-row"><td colspan="8">এই Question Paper-এ Subject Mapping পাওয়া যায়নি।</td></tr>
                    @endforelse
                    @if(count($visibleDepartmentRows))
                        <tr class="compact-category-divider"><td colspan="8">বিভাগভিত্তিক ফলাফল @if($departmentName) · {{ $departmentName }} @endif</td></tr>
                        @foreach($visibleDepartmentRows as $i => $row)
                            <tr class="compact-result-row compact-department-row">
                                <td class="cell-index">{{ $i+1 }}</td>
                                <td class="cell-name"><span class="subject-badge alt{{ ($i+2)%4 }}">{{ $row['name'] }}</span><small class="dept-type">Department</small></td>
                                <td class="cell-total">{{ $row['total'] }}</td>
                                <td class="cell-correct highlight-number">{{ $row['correct'] }}</td>
                                <td class="cell-wrong">{{ $row['wrong'] }}</td>
                                <td class="cell-unanswered">{{ $row['unanswered'] }}</td>
                                <td class="cell-mark">{{ number_format($row['score'],2) }}</td>
                                <td class="cell-result">
                                    @if($row['passed'] === true)<span class="group-result-badge pass">✓ Pass</span>
                                    @elseif($row['passed'] === false)<span class="group-result-badge fail">✕ Fail</span>
                                    @else<span class="group-result-badge none">—</span>@endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    <tr class="compact-result-row compact-total-row">
                        <td class="cell-index">—</td>
                        <td class="cell-name">সামগ্রিক ফলাফল</td>
                        <td class="cell-total">{{ $total }}</td>
                        <td class="cell-correct highlight-number">{{ $correct }}</td>
                        <td class="cell-wrong">{{ $wrong }}</td>
                        <td class="cell-unanswered">{{ $unanswered }}</td>
                        <td class="cell-mark">{{ number_format($score,2) }}</td>
                        <td class="cell-result">
                            @if($passed === true)<span class="group-result-badge pass">✓ Pass</span>
                            @elseif($passed === false)<span class="group-result-badge fail">✕ Fail</span>
                            @else<span class="group-result-badge none">—</span>@endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="combined-summary-note">বিভাগের ফলাফল ওই বিভাগের প্রশ্নভিত্তিক। বিষয় ও বিভাগের সংখ্যা একসঙ্গে যোগ করা হয়নি। বিস্তারিত প্রশ্ন দেখতে নিচের Answer Review খুলুন।</p>
    </section>

    <div class="dash-grid">
        <div class="dash-column">


            <section class="result-card section my-attempts-card">
                <h2><span class="small-icon">▤</span> Exam Result Details (My Attempts)</h2>
                <div class="data-table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Attempt</th><th>তারিখ</th><th>সঠিক</th><th>ভুল</th><th>প্রাপ্ত নম্বর</th><th>Result</th></tr></thead>
                        <tbody>
                        @foreach($myAttempts as $i => $attempt)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>#{{ $attempt['id'] }} @if($attempt['id'] === $exam->id) ★ @endif</td>
                                <td>{{ $attempt['end_at'] ? \Carbon\Carbon::parse($attempt['end_at'])->format('d M Y') : '—' }}</td>
                                <td>{{ $attempt['correct'] }}</td>
                                <td>{{ $attempt['wrong'] }}</td>
                                <td><b>{{ number_format($attempt['score'],2) }}</b></td>
                                <td>
                                    @if($attempt['passed'] === true)<span class="chip success">Pass</span>
                                    @elseif($attempt['passed'] === false)<span class="chip danger">Fail</span>
                                    @else — @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="subtle" style="margin-bottom:0">একই Question Paper-এর আপনার Completed Attempts দেখানো হচ্ছে; অন্য শিক্ষার্থীর ব্যক্তিগত ফলাফল নয়।</p>
            </section>
        </div>

        <div class="dash-column">


            <section class="result-card section question-solve-card">
                <h2><span class="small-icon">❔</span> Question &amp; Solve Summary</h2>
                <div class="data-table-wrap">
                    <table>
                        <thead><tr><th>বিষয়</th><th>মোট প্রশ্ন</th><th>সঠিক</th><th>ভুল</th><th>উত্তর দেননি</th></tr></thead>
                        <tbody>
                            @foreach($subjectRows as $i => $row)
                            <tr>
                                <td><span class="subject-badge alt{{ $i%4 }}">{{ $row['name'] }}</span></td>
                                <td>{{ $row['total'] }}</td><td>{{ $row['correct'] }}</td>
                                <td>{{ $row['wrong'] }}</td><td>{{ $row['unanswered'] }}</td>
                            </tr>
                            @endforeach
                            <tr style="font-weight:800;background:#eff5ff">
                                <td>Total</td><td>{{ $total }}</td><td>{{ $correct }}</td>
                                <td>{{ $wrong }}</td><td>{{ $unanswered }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="question-breakdown">
                    <div><b style="color:#16835d">{{ $answered }}</b><span>উত্তর দিয়েছেন</span></div>
                    <div><b style="color:#16835d">{{ $correct }}</b><span>সঠিক</span></div>
                    <div><b style="color:#d84d58">{{ $wrong }}</b><span>ভুল</span></div>
                    <div><b>{{ $unanswered }}</b><span>উত্তর দেননি</span></div>
                </div>
            </section>

            <section class="result-card section score-distribution-card">
                <h2><span class="small-icon">▥</span> Score Distribution (All Students)</h2>
                @php
                    $barMax = max(array_merge([1], $distribution));
                @endphp
                <div class="bar-chart">
                    @foreach($distribution as $i => $count)
                    <div class="bar-item">
                        <b>{{ $count }}</b>
                        <div class="bar bar-{{ $i+1 }}" style="height:{{ round($count/$barMax*100) }}%"></div>
                        <span>{{ ['0–20%','21–40%','41–60%','61–80%','81–100%'][$i] }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="subtle" style="margin-top:14px">প্রতিজন শিক্ষার্থীর সর্বোচ্চ Completed Score দিয়ে হিসাব।</div>
            </section>
        </div>

        <div class="dash-column">
            <section class="result-card section overall-result-card">
                <h2><span class="small-icon">◉</span> Overall Result (All Students)</h2>
                <div class="bar-summary">
                    <div class="bar-summary-head"><b>Pass Rate</b><strong>{{ $passRate === null ? '—' : number_format($passRate,1).'%' }}</strong></div>
                    <div class="bar-summary-track"><span style="width:{{ max(0,min(100,$passRate ?? 0)) }}%;background:#0ba86b"></span></div>
                    <div class="bar-summary-meta"><span>✓ Passed: <b>{{ $passedCount ?? '—' }}</b></span>
                        <span>✕ Failed: <b>{{ $failedCount ?? '—' }}</b></span>
                        <span>Completed: <b>{{ $participants }}</b></span></div>
                </div>
            </section>
            <section class="result-card section answer-breakdown-card">
                <h2><span class="small-icon">◉</span> My Answer Breakdown</h2>
                <div class="bar-summary">
                    <div class="bar-summary-head"><b>প্রশ্নের অবস্থা</b><strong>{{ $total }}টি প্রশ্ন</strong></div>
                    <div class="stacked-answers" role="img" aria-label="সঠিক {{ $correct }}, ভুল {{ $wrong }}, বাকি {{ $unanswered }}">
                        <span style="width:{{ $total ? $correct/$total*100 : 0 }}%;background:#12a66e"></span>
                        <span style="width:{{ $total ? $wrong/$total*100 : 0 }}%;background:#e84b58"></span>
                        <span style="width:{{ $total ? $unanswered/$total*100 : 0 }}%;background:#b7c7db"></span>
                    </div>
                    <div class="bar-summary-meta"><span>✓ সঠিক: <b>{{ $correct }}</b></span>
                        <span>✕ ভুল: <b>{{ $wrong }}</b></span>
                        <span>বাকি: <b>{{ $unanswered }}</b></span></div>
                </div>
            </section>
            <section class="result-card section top-performers-card">
                <h2><span class="small-icon" style="color:#e5a500">★</span> Top Performers (Top 5)</h2>
                <div class="data-table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>শিক্ষার্থী</th><th>প্রাপ্ত নম্বর</th></tr></thead>
                        <tbody>
                        @foreach($topFive as $row)
                            <tr class="{{ $row['is_mine'] ? 'rank-me' : '' }}">
                                <td>{{ $row['position'] }}</td>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ number_format($row['mark'],2) }} <small style="color:#657c95">({{ number_format($row['percent'],1) }}%)</small></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="subtle" style="margin-bottom:0">সব বিভাগের প্রকাশিত ফলাফল থেকে সেরা শিক্ষার্থীদের তালিকা।</p>
            </section>
            <section class="result-card section batch-merit-card" id="batch-merit">
                <div class="merit-head">
                    <div>
                        <h2><span class="small-icon">🏆</span> {{ $batchName ?: 'Exam' }} — All Departments Merit List</h2>
                        <p class="subtle">{{ $paper->name }} · প্রতিজনের সেরা Completed Attempt · সব বিভাগ</p>
                    </div>
                    <span class="chip">{{ count($meritRows) }} জন · Rank {{ $ownRank ?? '—' }}</span>
                </div>
                <div class="merit-filters no-print">
                    <label>Department
                        <select id="merit-department" aria-label="Filter department">
                            <option value="">All Departments</option>
                            @foreach($meritDepartments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Search
                        <input id="merit-search" type="search" placeholder="নাম বা Roll / Student ID" autocomplete="off" aria-label="Search students">
                    </label>
                </div>
                @if(count($meritRows))
                <div class="merit-table-wrap">
                    <table class="merit-table">
                        <thead><tr><th>Rank</th><th>Roll / ID</th><th>Name</th><th>Department</th><th>Score</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($meritRows as $i => $person)
                            <tr class="merit-entry {{ $person['is_mine'] ? 'merit-me' : '' }}" data-pos="{{ $i }}" data-dept="{{ $person['department'] }}" data-search="{{ mb_strtolower($person['name'] . ' ' . $person['roll']) }}">
                                <td><b>#{{ $person['rank'] }}</b></td>
                                <td>{{ $person['roll'] }}</td>
                                <td>{{ $person['name'] }} @if($person['is_mine'])<span class="you-tag">আপনি</span>@endif</td>
                                <td>{{ $person['department'] }}</td>
                                <td><b>{{ number_format($person['mark'],2) }}</b> / {{ number_format($person['maximum'],2) }}<small class="merit-percent">{{ number_format($person['percent'],1) }}%</small></td>
                                <td><span class="group-result-badge {{ $person['passed'] ? 'pass' : 'fail' }}">{{ $person['passed'] ? 'Pass' : 'Fail' }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="merit-mobile">
                    @foreach($meritRows as $i => $person)
                        <div class="merit-mobile-card {{ $person['is_mine'] ? 'merit-me' : '' }}" data-pos="{{ $i }}">
                            <span class="merit-mobile-rank">#{{ $person['rank'] }}</span>
                            <div class="merit-mobile-info"><b>{{ $person['name'] }} @if($person['is_mine'])· আপনি@endif</b>
                                <small>{{ $person['roll'] }} · {{ $person['department'] }}</small></div>
                            <div class="merit-mobile-score"><b>{{ number_format($person['mark'],2) }}</b><small>{{ number_format($person['percent'],1) }}%</small></div>
                            <span class="group-result-badge {{ $person['passed'] ? 'pass' : 'fail' }}">{{ $person['passed'] ? 'Pass' : 'Fail' }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="merit-pagination no-print">
                    <span id="merit-page-message" aria-live="polite"></span>
                    <div><button type="button" id="merit-prev">← Prev</button><span id="merit-page-number">1</span><button type="button" id="merit-next">Next →</button></div>
                </div>
                @else
                    <p class="empty">এই Batch ও Exam-এর কোনো প্রকাশিত Completed Result পাওয়া যায়নি।</p>
                @endif
                <p class="subtle merit-note">Rank = সর্বোচ্চ প্রাপ্ত নম্বরের শতাংশ; একাধিক Department-এর প্রশ্নপত্রে পূর্ণমান ভিন্ন হলেও তুলনা ন্যায্য থাকবে। একই শিক্ষার্থীর সেরা Attempt একবার গণনা করা হয়েছে।</p>
            </section>
            <section class="result-card section quick-actions-card no-print">
                <h2><span class="small-icon">▣</span> Quick Actions</h2>
                <div style="display:grid;gap:9px">
                    <button type="button" class="btn btn-primary" onclick="window.print()">⎙ Print / Save PDF</button>
                    <button type="button" class="btn btn-green" id="download-result-csv">↓ Download Result CSV</button>
                    <a class="btn" href="#review-questions" style="text-align:center">☷ Question Answer Review</a>
                    <button type="button" class="btn" id="copy-summary">▤ Copy My Result</button>
                </div>
            </section>
        </div>
    </div>

    <details class="result-card review-accordion no-print" id="review-questions">
        <summary>
            <span>☷ Question-by-Question Answer Review ({{ $total }}টি প্রশ্ন)</span>
            <span class="review-open-hint">দেখতে ক্লিক করুন ▾</span>
        </summary>
        <div class="review-body">
        <p class="subtle">শুধু আপনার Submitted Attempt #{{ $exam->id }}-এর উত্তর দেখানো হচ্ছে।</p>
        <div class="review-tools no-print">
            <button class="filter-btn active" type="button" data-review-filter="all">সব ({{ $total }})</button>
            <button class="filter-btn" type="button" data-review-filter="correct">সঠিক ({{ $correct }})</button>
            <button class="filter-btn" type="button" data-review-filter="wrong">ভুল ({{ $wrong }})</button>
            <button class="filter-btn" type="button" data-review-filter="skipped">উত্তর দেননি ({{ $unanswered }})</button>
        </div>
        @forelse($questionRows as $row)
            <details class="question-review" data-answer-status="{{ $row['status'] }}">
                <summary><div class="review-head">
                    <span class="review-label">প্রশ্ন {{ $row['number'] }} · {{ $row['subject'] }}</span>
                    @if($row['status'] === 'correct')<span class="chip success">✓ সঠিক</span>
                    @elseif($row['status'] === 'wrong')<span class="chip danger">✕ ভুল</span>
                    @else<span class="chip neutral">— উত্তর দেননি</span>@endif
                </div></summary>
                <div class="review-detail">
                <p style="font-weight:700">{{ $row['question'] ?: '[চিত্রভিত্তিক প্রশ্ন]' }}</p>
                @if($row['question_image'])
                    <img src="{{ asset(ltrim($row['question_image'], '/')) }}" alt="Question image" style="max-width:100%;max-height:260px;object-fit:contain">
                @endif
                <div class="answer-row {{ $row['status']==='correct' ? 'good' : ($row['status']==='wrong' ? 'bad' : '') }}"><strong>আপনার উত্তর:</strong> {{ $row['selected'] ?? 'উত্তর দেননি' }}</div>
                <div class="answer-row good"><strong>সঠিক উত্তর:</strong> {{ $row['correct_option'] ?? 'উত্তর পাওয়া যায়নি' }}</div>
                @if($row['explanation'])<div class="answer-row"><strong>ব্যাখ্যা:</strong> {{ $row['explanation'] }}</div>@endif
                </div>
            </details>
        @empty
            <p class="empty">প্রশ্ন পাওয়া যায়নি।</p>
        @endforelse
        </div>
    </details>
    <p class="footer-note">Live Education BD · Read-only Result Preview · Pass policy: {{ $passPolicy }}</p>
</main>
<script>
(function () {
    // Batch merit filtering + ten students per page; works on desktop and mobile.
    const meritRows = Array.from(document.querySelectorAll('.merit-table .merit-entry'));
    const meritCards = Array.from(document.querySelectorAll('.merit-mobile .merit-mobile-card'));
    const meritSearch = document.getElementById('merit-search');
    const meritDepartment = document.getElementById('merit-department');
    const meritPrev = document.getElementById('merit-prev');
    const meritNext = document.getElementById('merit-next');
    let meritPage = 1;
    const meritPageSize = 10;
    function showMerit() {
        const q = (meritSearch?.value || '').toLocaleLowerCase().trim();
        const dept = meritDepartment?.value || '';
        const filtered = meritRows.filter(row =>
            (!dept || row.dataset.dept === dept) &&
            (!q || row.dataset.search.includes(q))
        );
        const totalPages = Math.max(1, Math.ceil(filtered.length / meritPageSize));
        meritPage = Math.min(Math.max(meritPage, 1), totalPages);
        const visible = new Set(filtered.slice((meritPage-1)*meritPageSize, meritPage*meritPageSize)
            .map(row => row.dataset.pos));
        meritRows.forEach(row => { row.hidden = !visible.has(row.dataset.pos); });
        meritCards.forEach(card => { card.hidden = !visible.has(card.dataset.pos); });
        const msg = document.getElementById('merit-page-message');
        if (msg) msg.textContent = filtered.length ?
            `${(meritPage-1)*meritPageSize+1}–${Math.min(meritPage*meritPageSize, filtered.length)} / ${filtered.length} শিক্ষার্থী`
            : 'কোনো শিক্ষার্থী পাওয়া যায়নি';
        const number = document.getElementById('merit-page-number');
        if(number) number.textContent = `${meritPage} / ${totalPages}`;
        if(meritPrev) meritPrev.disabled = meritPage<=1;
        if(meritNext) meritNext.disabled = meritPage>=totalPages;
    }
    if (meritRows.length) {
        meritSearch?.addEventListener('input', () => { meritPage=1; showMerit(); });
        meritDepartment?.addEventListener('change', () => { meritPage=1; showMerit(); });
        meritPrev?.addEventListener('click', () => { meritPage--; showMerit(); });
        meritNext?.addEventListener('click', () => { meritPage++; showMerit(); });
        showMerit();
    }

    // Keep the question list closed by default. Open it only on demand.
    document.querySelectorAll('a[href="#review-questions"]').forEach(link => {
        link.addEventListener('click', () => {
            const review = document.getElementById('review-questions');
            if (review) review.open = true;
        });
    });
    document.querySelectorAll('[data-review-filter]').forEach(button => {
        button.addEventListener('click', () => {
            const status = button.dataset.reviewFilter;
            document.querySelectorAll('[data-review-filter]').forEach(b => b.classList.toggle('active', b === button));
            document.querySelectorAll('.question-review').forEach(card => {
                card.style.display = status === 'all' || card.dataset.answerStatus === status ? '' : 'none';
            });
        });
    });

    const csvButton = document.getElementById('download-result-csv');
    if (csvButton) csvButton.addEventListener('click', () => {
        const subjectRows = @json($subjectRows);
        const departmentRows = @json($departmentRows);
        const firstRows = [
            ['Live Education BD - Student Exam Result'],
            ['Exam', @json($paper->name)],
            ['Attempt ID', @json($exam->id)],
            ['Obtained Mark', @json($score)],
            ['Maximum Mark', @json($maximumMarks)],
            ['Pass Mark (configured)', @json($customPassMark)],
            ['Pass Policy', @json($passPolicy)],
            ['Required To Pass', @json($passMark)],
            ['Status', @json($passed === null ? 'N/A' : ($passed ? 'Pass' : 'Fail'))],
            [], ['Subject', 'Total', 'Correct', 'Wrong', 'Not Answered', 'Score', 'Maximum Mark', 'Pass Mark', 'Score Percent', 'Result']
        ];
        subjectRows.forEach(r => firstRows.push([r.name,r.total,r.correct,r.wrong,r.unanswered,r.score,r.max_marks,r.pass_mark,r.score_percent === null ? '' : r.score_percent+'%',r.passed === null ? 'N/A' : (r.passed ? 'Pass' : 'Fail')]));
        firstRows.push([], ['Department','Total','Correct','Wrong','Not Answered','Score','Maximum Mark','Pass Mark','Score Percent','Result']);
        departmentRows.forEach(r => firstRows.push([r.name,r.total,r.correct,r.wrong,r.unanswered,r.score,r.max_marks,r.pass_mark,r.score_percent === null ? '' : r.score_percent+'%',r.passed === null ? 'N/A' : (r.passed ? 'Pass' : 'Fail')]));
        const escapeCell = c => '"' + String(c ?? '').replace(/"/g, '""') + '"';
        const csv = '\ufeff' + firstRows.map(row => row.map(escapeCell).join(',')).join('\r\n');
        const url = URL.createObjectURL(new Blob([csv], {type:'text/csv;charset=utf-8'}));
        const link = document.createElement('a');
        link.href = url; link.download = 'LEBD_Result_' + @json($paper->id) + '_' + @json($exam->id) + '.csv';
        document.body.appendChild(link); link.click(); link.remove();
        setTimeout(() => URL.revokeObjectURL(url), 500);
    });
    const copyBtn = document.getElementById('copy-summary');
    if (copyBtn) copyBtn.addEventListener('click', async () => {
        const summary = @json($paper->name) + '\nScore: ' + @json($score) + '/' + @json($maximumMarks)
            + '\nResult: ' + @json($passed === null ? 'N/A' : ($passed ? 'Passed' : 'Failed'));
        try { await navigator.clipboard.writeText(summary); copyBtn.textContent = '✓ Copied'; }
        catch (_) { window.prompt('Copy Result:', summary); }
    });
})();
</script>
@endif
</body>
</html>
