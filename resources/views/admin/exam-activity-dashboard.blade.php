@extends('dashboard')
@section('title', 'Exam Activity Dashboard')
@section('content')
@php
    // The enclosing admin layout supplies its existing sidebar, navbar and authorization.
    $makePoints = static function ($points) {
        $max = max(1, ...array_map(fn ($p) => (int) ($p['value'] ?? 0), $points));
        $count = max(1, count($points) - 1);
        return implode(' ', array_map(function ($i, $p) use ($max, $count) {
            return round(25 + ($i * 520 / $count), 1) . ','
                . round(176 - (max(0, (int)$p['value']) * 145 / $max), 1);
        }, array_keys($points), $points));
    };
    $hourPoints = $makePoints($hourChart);
    $dayPoints = $makePoints($dailyTrend);
    $totalStatuses = array_sum($statusSplit);
    $livePercent = $totalStatuses > 0 ? $statusSplit['live'] * 100 / $totalStatuses : 0;
    $completedPercent = $totalStatuses > 0 ? $statusSplit['completed'] * 100 / $totalStatuses : 0;
    $passTotal = $passFail['pass'] + $passFail['fail'];
    $passPct = $passTotal > 0 ? $passFail['pass'] * 100 / $passTotal : 0;
    $scoreMax = max(1, ...array_column($batchRows ?: [['attempted'=>0]], 'attempted'));
@endphp
<style>
/* Intentionally prefixed, so the existing AdminLTE/Bootstrap dashboard stays intact. */
.lebd-exam-activity {font-family:Arial,'Noto Sans Bengali',sans-serif;color:#183257;background:#f3f7fd;padding:16px 16px 28px;margin:-12px -15px -18px;--ead-blue:#1177f3;--ead-line:#e3ebf7;--ead-muted:#6f83a3;}
.lebd-exam-activity *{box-sizing:border-box}
.lebd-exam-activity .ead-head{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:16px}
.lebd-exam-activity .ead-title h1{font-size:23px;font-weight:800;margin:0 0 6px;color:#12335e}
.lebd-exam-activity .ead-title p{margin:0;color:var(--ead-muted);font-size:12px}
.lebd-exam-activity .ead-head-controls{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
.lebd-exam-activity .ead-input{border:1px solid #d6e4f6;border-radius:10px;background:#fff;padding:10px 13px;color:#47618a;font-size:12px;min-width:220px;outline:none}
.lebd-exam-activity .ead-button{display:inline-flex;justify-content:center;align-items:center;gap:7px;background:#0876ee;color:#fff!important;border:1px solid #0876ee;border-radius:10px;font-weight:750;font-size:12px;text-decoration:none;padding:10px 13px;white-space:nowrap}
.lebd-exam-activity .ead-button:hover{background:#055ac0;text-decoration:none}
.lebd-exam-activity .ead-button.secondary{color:#245f9f!important;background:#f3f8ff;border-color:#cbdcf6}
.lebd-exam-activity .ead-card{border:1px solid var(--ead-line);background:#fff;border-radius:13px;box-shadow:0 5px 14px rgba(31,72,125,.035);min-width:0;overflow:hidden}
.lebd-exam-activity .ead-stats{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;margin-bottom:14px}
.lebd-exam-activity .ead-stat{padding:15px 12px;display:flex;gap:12px;align-items:center;min-height:101px}
.lebd-exam-activity .ead-stat-icon{display:grid;place-items:center;flex:0 0 42px;height:42px;border-radius:12px;background:#e8f2ff;color:#176dec;font-size:23px;font-weight:800}
.lebd-exam-activity .ead-stat:nth-child(2) .ead-stat-icon{background:#d9f9e7;color:#029b55}
.lebd-exam-activity .ead-stat:nth-child(3) .ead-stat-icon{background:#eaf1ff;color:#075bda}
.lebd-exam-activity .ead-stat:nth-child(4) .ead-stat-icon{background:#eee9ff;color:#7446d9}
.lebd-exam-activity .ead-stat:nth-child(5) .ead-stat-icon{background:#fff0dc;color:#eb8b12}
.lebd-exam-activity .ead-stat:nth-child(6) .ead-stat-icon{background:#dbf8f3;color:#078e84}
.lebd-exam-activity .ead-stat small{display:block;font-size:11px;color:#566e94;font-weight:700;line-height:1.4}
.lebd-exam-activity .ead-stat strong{display:block;font-size:24px;line-height:1.23;color:#152e53;margin:3px 0;font-variant-numeric:tabular-nums}
.lebd-exam-activity .ead-stat em{font-size:10px;color:#7c8eaa;font-style:normal;display:block}
.lebd-exam-activity .ead-top-grid{display:grid;grid-template-columns:minmax(0,1.22fr) minmax(0,1.45fr) minmax(0,.94fr);gap:12px;align-items:stretch}
.lebd-exam-activity .ead-section-head{padding:13px 15px;display:flex;justify-content:space-between;align-items:center;gap:8px;border-bottom:1px solid var(--ead-line)}
.lebd-exam-activity .ead-section-head h2{font-size:14px;font-weight:800;margin:0;color:#12335e;line-height:1.4}
.lebd-exam-activity .ead-section-head a{font-size:11px;color:var(--ead-blue);white-space:nowrap;font-weight:700}
.lebd-exam-activity .ead-section-body{padding:13px}
.lebd-exam-activity .ead-tablewrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}
.lebd-exam-activity table.ead-table{width:100%;border-collapse:collapse;font-size:11px;min-width:410px}
.lebd-exam-activity .ead-table th{background:#f2f7fe;color:#547097;text-align:left;font-weight:800}
.lebd-exam-activity .ead-table th,.lebd-exam-activity .ead-table td{padding:10px 7px;border-bottom:1px solid #eaf0f8;vertical-align:middle}
.lebd-exam-activity .ead-table tr:last-child td{border-bottom:0}
.lebd-exam-activity .ead-strong{font-weight:750;color:#1f3556}
.lebd-exam-activity .ead-pill{display:inline-block;padding:4px 8px;border-radius:7px;background:#eaf2ff;color:#1e6dda;font-size:10px;font-weight:750;white-space:nowrap}
.lebd-exam-activity .ead-pill.live{background:#dff9e8;color:#07884d}
.lebd-exam-activity .ead-pill.completed{background:#e4f7ee;color:#0a8651}
.lebd-exam-activity .ead-pill.upcoming{background:#e9efff;color:#386ce5}
.lebd-exam-activity .ead-pill.alert{background:#fff0e4;color:#ae6420}
.lebd-exam-activity .ead-mini-link{padding:5px 8px;border:1px solid #b9d5fa;border-radius:7px;color:#176ade;font-size:10px;text-decoration:none;white-space:nowrap}
.lebd-exam-activity .ead-mini-link:hover{background:#edf5ff;text-decoration:none}
.lebd-exam-activity .ead-inner-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;padding:10px}
.lebd-exam-activity .ead-mini-card{border:1px solid #e5edf8;border-radius:10px;padding:11px;min-width:0;overflow:hidden}
.lebd-exam-activity .ead-mini-card h3{font-size:12px;font-weight:800;margin:0 0 10px;color:#1e3e68}
.lebd-exam-activity svg.ead-chart{width:100%;height:auto;display:block;overflow:visible}
.lebd-exam-activity .ead-labels{display:flex;justify-content:space-between;color:#8596ac;font-size:10px;margin-top:4px}
.lebd-exam-activity .ead-bars{height:140px;display:flex;gap:12px;align-items:flex-end;justify-content:space-around;border-bottom:1px solid #dce7f5;padding:0 5px}
.lebd-exam-activity .ead-bar-wrap{flex:1;min-width:0;text-align:center;font-size:10px;color:#637b9a}
.lebd-exam-activity .ead-bar{height:var(--h,0%);min-height:2px;background:linear-gradient(#4ca5fd,#176feb);border-radius:5px 5px 0 0;margin:auto;width:min(34px,75%);transition:.2s}
.lebd-exam-activity .ead-bar-value{display:block;color:#254c80;font-size:11px;font-weight:800}
.lebd-exam-activity .ead-bar-label{display:block;margin-top:6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lebd-exam-activity .ead-donut{width:130px;height:130px;flex:none;border-radius:50%;display:grid;place-items:center;background:conic-gradient(#09b981 0 var(--live),#1676ec var(--live) var(--complete),#ffa42a var(--complete) 100%)}
.lebd-exam-activity .ead-donut-inside{width:91px;height:91px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#254064;font-size:10px;text-align:center}
.lebd-exam-activity .ead-donut-inside b{font-size:19px;color:#183d66}
.lebd-exam-activity .ead-donut-layout{display:flex;align-items:center;gap:11px;justify-content:center;flex-wrap:wrap}
.lebd-exam-activity .ead-legend{font-size:10px;line-height:2.1;color:#5c7291}
.lebd-exam-activity .ead-legend b{color:#1c385e}
.lebd-exam-activity .ead-dot{display:inline-block;width:9px;height:9px;border-radius:3px;margin-right:4px}
.lebd-exam-activity .ead-pass-grid{display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-bottom:9px}
.lebd-exam-activity .ead-metric-box{background:#f5f9ff;border-radius:10px;text-align:center;padding:10px 4px}
.lebd-exam-activity .ead-metric-box b{font-size:20px;display:block;color:#1a4e82}
.lebd-exam-activity .ead-metric-box small{font-size:10px;color:#6a81a0}
.lebd-exam-activity .ead-progress{height:8px;background:#e7effa;border-radius:20px;overflow:hidden;display:flex}
.lebd-exam-activity .ead-progress>span{display:block;height:100%;background:#11b981}
.lebd-exam-activity .ead-live-list{padding:10px}
.lebd-exam-activity .ead-live-item{border:1px solid #e0eaf6;border-left:3px solid #1577ee;border-radius:10px;padding:11px;margin-bottom:9px}
.lebd-exam-activity .ead-live-item b{font-size:12px;color:#1d3c63;display:block;margin-bottom:4px}
.lebd-exam-activity .ead-live-item small{font-size:11px;color:#6480a0;display:block;line-height:1.5}
.lebd-exam-activity .ead-live-item .ead-remaining{background:#fff0f0;color:#cf414c;font-size:11px;padding:5px 8px;border-radius:8px;display:inline-block;margin-top:7px;font-weight:750}
.lebd-exam-activity .ead-foot-grid{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(0,1.15fr) minmax(0,1fr) minmax(0,.94fr);gap:12px;margin-top:12px}
.lebd-exam-activity .ead-foot-grid .ead-card{min-height:220px}
.lebd-exam-activity .ead-recents{list-style:none;margin:0;padding:11px}
.lebd-exam-activity .ead-recents li{display:flex;gap:9px;padding:9px 2px;border-bottom:1px solid #eef3f9}
.lebd-exam-activity .ead-recents li:last-child{border-bottom:0}
.lebd-exam-activity .ead-recents .ead-action-icon{display:grid;place-items:center;flex:none;width:28px;height:28px;border-radius:8px;color:#146de4;background:#eaf2ff}
.lebd-exam-activity .ead-recents .ead-entry{min-width:0;flex:1;font-size:11px;color:#2a4d79;line-height:1.6}
.lebd-exam-activity .ead-recents .ead-entry small{display:block;color:#8a9bb4;font-size:10px}
.lebd-exam-activity .ead-quick-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px;padding:12px}
.lebd-exam-activity .ead-quick-grid a{border-radius:9px;text-decoration:none;text-align:center;padding:12px 5px;color:#fff;font-weight:750;font-size:11px;background:#087af6}
.lebd-exam-activity .ead-quick-grid a:nth-child(2){background:#7154e8}.lebd-exam-activity .ead-quick-grid a:nth-child(3){background:#10a66c}.lebd-exam-activity .ead-quick-grid a:nth-child(4){background:#ed9146}.lebd-exam-activity .ead-quick-grid a:nth-child(5){background:#cc5971}.lebd-exam-activity .ead-quick-grid a:nth-child(6){background:#2d87bd}
.lebd-exam-activity .ead-batch-rank{padding:10px 13px}
.lebd-exam-activity .ead-rank-item{margin:9px 0;font-size:11px;color:#506c94}
.lebd-exam-activity .ead-rank-item .ead-rank-title{display:flex;justify-content:space-between;gap:4px;margin-bottom:5px}
.lebd-exam-activity .ead-rank-item .ead-rank-title strong{color:#183c68}
.lebd-exam-activity .ead-rank-bar{height:6px;border-radius:9px;background:#e8eef7;overflow:hidden}
.lebd-exam-activity .ead-rank-bar span{display:block;height:100%;background:linear-gradient(90deg,#1e7ef2,#52aaf8)}
.lebd-exam-activity .ead-bottom-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:12px}
.lebd-exam-activity .ead-bottom-stat{padding:15px;min-height:220px}
.lebd-exam-activity .ead-bottom-stat h3{font-size:12px;font-weight:800;margin:0 0 11px;color:#29527f}
.lebd-exam-activity .ead-bottom-stat strong{font-size:27px;display:block;color:#164a87}
.lebd-exam-activity .ead-bottom-stat p{font-size:11px;color:#7389a8;margin:8px 0}
.lebd-exam-activity .ead-week-dots{display:flex;gap:5px;align-items:flex-end;margin-top:16px;justify-content:space-between}
.lebd-exam-activity .ead-week-dots div{text-align:center;flex:1;font-size:9px;color:#7185a0}
.lebd-exam-activity .ead-week-dots i{display:block;margin:0 auto 6px;border-radius:50%;width:12px;height:12px;background:rgba(0,169,119,var(--op,.15))}
.lebd-exam-activity .ead-empty{color:#8496af;font-size:12px;line-height:1.7;padding:13px}
.lebd-exam-activity .ead-fineprint{color:#7e90ab;font-size:10px;padding:10px 12px;line-height:1.6}
@media(max-width:1400px){.lebd-exam-activity .ead-stats{grid-template-columns:repeat(3,1fr)}.lebd-exam-activity .ead-top-grid{grid-template-columns:1.2fr 1.35fr}.lebd-exam-activity .ead-top-grid>.ead-card:last-child{grid-column:1/-1}.lebd-exam-activity .ead-live-list{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.lebd-exam-activity .ead-foot-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:950px){.lebd-exam-activity .ead-stats{grid-template-columns:repeat(2,1fr)}.lebd-exam-activity .ead-top-grid{grid-template-columns:1fr}.lebd-exam-activity .ead-top-grid>.ead-card:last-child{grid-column:auto}.lebd-exam-activity .ead-bottom-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.lebd-exam-activity{padding:10px;margin:-10px -10px -18px}.lebd-exam-activity .ead-title h1{font-size:19px}.lebd-exam-activity .ead-head-controls{width:100%}.lebd-exam-activity .ead-input{flex:1;min-width:120px}.lebd-exam-activity .ead-stats{gap:8px}.lebd-exam-activity .ead-stat{min-height:87px;padding:9px;gap:8px}.lebd-exam-activity .ead-stat-icon{width:35px;height:35px;flex-basis:35px;font-size:19px}.lebd-exam-activity .ead-stat strong{font-size:19px}.lebd-exam-activity .ead-inner-grid,.lebd-exam-activity .ead-foot-grid,.lebd-exam-activity .ead-bottom-grid{grid-template-columns:1fr}.lebd-exam-activity .ead-top-grid>.ead-card:last-child{grid-column:auto}.lebd-exam-activity .ead-live-list{display:block}}

/* Same-sized cards in every dashboard row. Lists stay compact; See More uses a dialog. */
.lebd-exam-activity .ead-stats > .ead-stat{height:104px;min-height:104px}
.lebd-exam-activity .ead-top-grid > .ead-card{height:475px;min-height:0;display:flex;flex-direction:column}
.lebd-exam-activity .ead-foot-grid > .ead-card{height:310px;min-height:0;display:flex;flex-direction:column}
.lebd-exam-activity .ead-bottom-grid > .ead-card{height:220px;min-height:0}
.lebd-exam-activity .ead-section-head{flex-shrink:0;min-height:51px}
.lebd-exam-activity .ead-top-grid .ead-tablewrap,.lebd-exam-activity .ead-foot-grid .ead-tablewrap{flex:1 1 auto;min-height:0;overflow:auto}
.lebd-exam-activity .ead-live-list,.lebd-exam-activity .ead-recents,.lebd-exam-activity .ead-batch-rank{min-height:0;flex:1 1 auto;overflow-y:auto}
.lebd-exam-activity .ead-inner-grid{flex:1;min-height:0;grid-template-rows:repeat(2,minmax(0,1fr))}
.lebd-exam-activity .ead-mini-card{min-height:0;overflow:hidden}
.lebd-exam-activity .ead-mini-card svg.ead-chart{max-height:125px}
.lebd-exam-activity .ead-mini-card .ead-bars{height:120px}
.lebd-exam-activity .ead-mini-card .ead-donut{width:110px;height:110px}
.lebd-exam-activity .ead-mini-card .ead-donut-inside{width:76px;height:76px}
.lebd-exam-activity .ead-quick-grid{align-content:start}
.lebd-exam-activity .ead-clamp-two{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.lebd-exam-activity .ead-extra-item,.lebd-exam-activity .ead-filter-hidden{display:none!important}
.lebd-exam-activity .ead-more-row{flex-shrink:0;border-top:1px solid #edf2f9;padding:7px 13px;text-align:right;background:#fbfdff}
.lebd-exam-activity .ead-more-btn{background:#eaf2ff;border:1px solid #d0e1fa;border-radius:8px;color:#1466ce;font-size:11px;font-weight:800;padding:7px 12px;cursor:pointer}
.lebd-exam-activity .ead-more-btn:hover{background:#dceaff}
.lebd-exam-activity .ead-more-btn:focus-visible,.lebd-exam-activity .ead-modal-close:focus-visible{outline:2px solid #1379ff;outline-offset:2px}
.lebd-exam-activity .ead-foot-grid .ead-rank-title span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lebd-exam-activity .ead-top-grid .ead-fineprint{flex-shrink:0;max-height:70px;overflow:hidden}
/* Larger view of the complete list, without changing the size of any main card. */
.lebd-exam-activity .ead-modal-backdrop{position:fixed;inset:0;background:rgba(12,27,49,.62);z-index:99999;padding:18px;display:flex;align-items:center;justify-content:center}
.lebd-exam-activity .ead-modal-backdrop[hidden]{display:none!important}
.lebd-exam-activity .ead-modal-panel{background:#fff;border-radius:14px;box-shadow:0 20px 90px rgba(0,0,0,.23);width:min(920px,100%);max-height:88vh;display:flex;flex-direction:column;overflow:hidden}
.lebd-exam-activity .ead-modal-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:15px 18px;border-bottom:1px solid #e2ebf7;flex-shrink:0}
.lebd-exam-activity .ead-modal-head h2{margin:0;font-size:17px;font-weight:800}
.lebd-exam-activity .ead-modal-close{border:0;background:#edf4ff;color:#173e74;font-size:24px;line-height:1;width:34px;height:34px;border-radius:9px;cursor:pointer}
.lebd-exam-activity .ead-modal-body{overflow-y:auto;overflow-x:auto;min-height:0;padding:12px}
.lebd-exam-activity .ead-expanded tr.ead-extra-item{display:table-row!important}
.lebd-exam-activity .ead-expanded li.ead-extra-item{display:flex!important}
.lebd-exam-activity .ead-expanded div.ead-extra-item{display:block!important}
.lebd-exam-activity .ead-expanded .ead-tablewrap{max-height:none!important;overflow-x:auto}
.lebd-exam-activity .ead-expanded .ead-recents,.lebd-exam-activity .ead-expanded .ead-batch-rank,.lebd-exam-activity .ead-expanded .ead-live-list{overflow:visible}
.lebd-exam-activity .ead-expanded .ead-filter-hidden{display:none!important}
body.ead-modal-open{overflow:hidden}
@media(max-width:1400px){.lebd-exam-activity .ead-top-grid > .ead-card{height:450px}.lebd-exam-activity .ead-foot-grid > .ead-card{height:330px}}
@media(max-width:950px){.lebd-exam-activity .ead-top-grid > .ead-card{height:400px}.lebd-exam-activity .ead-top-grid > .ead-card:nth-child(2){height:520px}.lebd-exam-activity .ead-foot-grid > .ead-card{height:300px}}
@media(max-width:640px){.lebd-exam-activity .ead-stats > .ead-stat{height:91px;min-height:91px}.lebd-exam-activity .ead-top-grid > .ead-card{height:370px}.lebd-exam-activity .ead-top-grid > .ead-card:nth-child(2){height:660px}.lebd-exam-activity .ead-foot-grid > .ead-card{height:310px}.lebd-exam-activity .ead-bottom-grid > .ead-card{height:210px}.lebd-exam-activity .ead-mini-card .ead-bars{height:110px}.lebd-exam-activity .ead-modal-backdrop{padding:8px}.lebd-exam-activity .ead-modal-panel{max-height:92vh}}
</style>
<section class="content lebd-exam-activity" id="lebd-exam-activity">
    <div class="ead-head">
        <div class="ead-title"><h1>📊 Exam Activity Dashboard</h1><p>Live Education BD · বাস্তব Routine, Question Paper ও Exam Attempt থেকে প্রস্তুত</p></div>
        <div class="ead-head-controls">
            <input type="search" class="ead-input" id="ead-search" aria-label="Search today's exams" placeholder="আজকের ব্যাচ বা এক্সাম খুঁজুন...">
            <a class="ead-button" href="{{ route('question.create') }}">⊕ প্রশ্ন Create করুন</a>
            <span class="ead-button secondary">📅 {{ $now->format('d M Y') }}</span>
        </div>
    </div>
    <div class="ead-stats">
        @foreach([
            ['📅','আজকের এক্সাম',$stats['today'],'প্রকাশিত রুটিন অনুযায়ী'],
            ['◉','লাইভ এক্সাম',$stats['live'],'রুটিনের সময় অনুযায়ী'],
            ['♙','আজ এক্সাম দিয়েছে',$stats['students_today'],'Unique Student'],
            ['✓','আজ সম্পন্ন',$stats['completed_today'],'Submitted Attempts'],
            ['▤','প্রস্তুতি প্রয়োজন',$stats['needs_questions'],'প্রশ্ন/সময়/পেপার যাচাই'],
            ['▥','সাপ্তাহিক অংশগ্রহণ',$stats['weekly_participation'],'ব্যাচভিত্তিক যোগফল'],
        ] as $s)
        <div class="ead-card ead-stat"><div class="ead-stat-icon">{{ $s[0] }}</div><div><small>{{ $s[1] }}</small><strong>{{ number_format($s[2]) }}</strong><em>{{ $s[3] }}</em></div></div>
        @endforeach
    </div>
    <div class="ead-top-grid">
        <div class="ead-card">
            <div class="ead-section-head"><h2>▦ আজকের ব্যাচ এক্সাম তালিকা</h2><a href="{{ route('routine.index') }}">সব রুটিন →</a></div>
            <div class="ead-tablewrap ead-more-source" id="ead-source-today"><table class="ead-table" id="ead-today-table"><thead><tr><th>#</th><th>Batch</th><th>Exam</th><th>সময়</th><th>Status</th><th>Action</th></tr></thead><tbody>
            @forelse($todayExams as $i => $item)
                <tr data-search="{{ strtolower($item['batch'].' '.$item['paper_name']) }}" class="{{ $i >= 5 ? 'ead-extra-item' : '' }}"><td>{{ $i + 1 }}</td><td class="ead-strong">{{ $item['batch'] }}</td><td>{{ $item['paper_name'] }}<br><small style="color:#90a1b7">{{ $item['questions'] }} প্রশ্ন</small></td><td>{{ $item['start'] }}<br><small style="color:#92a2b9">{{ $item['end'] }}</small></td><td><span class="ead-pill {{ $item['status'] === 'Live' ? 'live' : ($item['status'] === 'Completed' ? 'completed' : ($item['status'] === 'Upcoming' ? 'upcoming' : 'alert')) }}">{{ $item['status'] }}</span></td><td>@if($item['paper_id'])<a class="ead-mini-link" href="{{ route('paper.edit', $item['paper_id']) }}">Manage</a>@else<a class="ead-mini-link" href="{{ route('paper.create') }}">Create</a>@endif</td></tr>
            @empty<tr><td colspan="6" class="ead-empty">আজকের প্রকাশিত Routine-এ কোনো Exam পাওয়া যায়নি।</td></tr>@endforelse
            </tbody></table></div>
            @if(count($todayExams) > 5)
                <div class="ead-more-row"><button type="button" class="ead-more-btn" data-ead-expand="today" data-ead-title="আজকের ব্যাচ এক্সাম তালিকা">See More ({{ count($todayExams) - 5 }}) <span aria-hidden="true">→</span></button></div>
            @endif
            <div class="ead-fineprint">সময়সীমা Routine End থেকে নেওয়া হয়; পুরোনো স্কিমায় End না থাকলে Paper Close ব্যবহার করা হয়েছে। End জানা না থাকলে Live দাবি করা হয় না।</div>
        </div>
        <div class="ead-card">
            <div class="ead-section-head"><h2>▥ অ্যানালিটিক্স ও পরিসংখ্যান</h2><span style="color:#7790af;font-size:11px">DB Live Report</span></div>
            <div class="ead-inner-grid">
                <div class="ead-mini-card"><h3>ব্যাচভিত্তিক অংশগ্রহণ (৭ দিন)</h3>
                    <div class="ead-bars">@forelse(array_slice($batchRows,0,5) as $r)<div class="ead-bar-wrap"><span class="ead-bar-value">{{ $r['attempted'] }}</span><div class="ead-bar" style="--h:{{ round($r['attempted']*100/$scoreMax) }}%"></div><span class="ead-bar-label" title="{{ $r['name'] }}">{{ $r['name'] }}</span></div>@empty<span class="ead-empty">কোনো ডাটা নেই</span>@endforelse</div>
                </div>
                <div class="ead-mini-card"><h3>আজকের এক্সাম অংশগ্রহণ (প্রতি ২ ঘণ্টা)</h3>
                    <svg class="ead-chart" viewBox="0 0 570 195" role="img" aria-label="Today's started attempts line chart">
                        <g stroke="#e6edf7" stroke-width="1">@foreach([32,68,104,140,176] as $y)<line x1="25" y1="{{ $y }}" x2="545" y2="{{ $y }}"/>@endforeach</g>
                        <polyline points="{{ $hourPoints }}" stroke="#1677eb" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                        @php $maxHour=max(1,...array_column($hourChart,'value')); @endphp
                        @foreach($hourChart as $i=>$p)<circle cx="{{ round(25+$i*520/max(1,count($hourChart)-1),1) }}" cy="{{ round(176-$p['value']*145/$maxHour,1) }}" r="4.5" fill="#1677eb"/><title>{{ $p['label'] }}: {{ $p['value'] }} attempts</title>@endforeach
                    </svg>
                    <div class="ead-labels">@foreach($hourChart as $p)<span>{{ $p['label'] }}</span>@endforeach</div>
                </div>
                <div class="ead-mini-card"><h3>আজকের Routine Status</h3>
                    <div class="ead-donut-layout"><div class="ead-donut" style="--live:{{ $livePercent }}%;--complete:{{ $livePercent+$completedPercent }}%"><div class="ead-donut-inside"><b>{{ $totalStatuses }}</b>মোট Exam</div></div><div class="ead-legend"><div><i class="ead-dot" style="background:#09b981"></i>Live: <b>{{ $statusSplit['live'] }}</b></div><div><i class="ead-dot" style="background:#1676ec"></i>Completed Window: <b>{{ $statusSplit['completed'] }}</b></div><div><i class="ead-dot" style="background:#ffa42a"></i>Upcoming/Other: <b>{{ $statusSplit['upcoming']+$statusSplit['other'] }}</b></div></div></div>
                </div>
                <div class="ead-mini-card"><h3>প্রকাশিত ফলাফল: Pass / Fail (৭ দিন)</h3>
                    <div class="ead-pass-grid"><div class="ead-metric-box"><b style="color:#03a978">{{ $passFail['pass'] }}</b><small>Passed</small></div><div class="ead-metric-box"><b style="color:#e94e66">{{ $passFail['fail'] }}</b><small>Failed</small></div></div>
                    <div class="ead-progress"><span style="width:{{ $passPct }}%"></span></div><p style="font-size:10px;color:#8095ad;margin:9px 0 0">Pass Rate: {{ $passTotal ? number_format($passPct,1).'%' : 'No published result' }} · ৭৫% বা Paper Pass Mark</p>
                </div>
            </div>
        </div>
        <div class="ead-card">
            <div class="ead-section-head"><h2>◉ Live Exam Monitor</h2><a href="{{ route('exam.live') }}">সব লাইভ →</a></div>
            <div class="ead-live-list ead-more-source" id="ead-source-live">
                @forelse($liveMonitor as $i => $a)
                    <div class="ead-live-item {{ $i >= 3 ? 'ead-extra-item' : '' }}"><b>{{ $a['batch'] }}</b><small>{{ $a['exam'] }} · {{ $a['student'] }}</small><span class="ead-remaining">⏱ বাকি {{ $a['remaining'] }}</span></div>
                @empty
                    <div class="ead-empty">এই মুহূর্তে মেয়াদ-বাকি কোনো Live Attempt নেই।</div>
                @endforelse
            </div>
            @if(count($liveMonitor) > 3)
                <div class="ead-more-row"><button type="button" class="ead-more-btn" data-ead-expand="live" data-ead-title="Live Exam Monitor">See More ({{ count($liveMonitor) - 3 }}) <span aria-hidden="true">→</span></button></div>
            @endif
            <div class="ead-fineprint">মোট সময়-বাকি Live Attempt: <b>{{ $liveTotal }}</b>। এগুলো Browser Online Status নয়; সত্যিকারের Online Presence-এর জন্য Student Activity Heartbeat প্রয়োজন।</div>
        </div>
    </div>
    <div class="ead-foot-grid">
        <div class="ead-card"><div class="ead-section-head"><h2>▦ সকল ব্যাচের এক্সাম সারাংশ</h2><a href="{{ route('batch.index') }}">সব ব্যাচ →</a></div>
            <div class="ead-tablewrap ead-more-source" id="ead-source-batches"><table class="ead-table"><thead><tr><th>Batch</th><th>Enrolled</th><th>Attempted (7d)</th><th>Live Attempts</th><th>Participation</th></tr></thead><tbody>
                @forelse($batchRows as $i => $r)<tr class="{{ $i >= 5 ? 'ead-extra-item' : '' }}"><td class="ead-strong"><span class="ead-clamp-two" title="{{ $r['name'] }}">{{ $r['name'] }}</span></td><td>{{ $r['total'] }}</td><td>{{ $r['attempted'] }}</td><td>{{ $r['active'] }}</td><td>{{ $r['rate'] === null ? '—' : $r['rate'].'%' }}</td></tr>@empty<tr><td colspan="5">No batch attempts yet</td></tr>@endforelse
            </tbody></table></div>
            @if(count($batchRows) > 5)
                <div class="ead-more-row"><button type="button" class="ead-more-btn" data-ead-expand="batches" data-ead-title="সকল ব্যাচের এক্সাম সারাংশ">See More ({{ count($batchRows) - 5 }}) <span aria-hidden="true">→</span></button></div>
            @endif
        </div>
        <div class="ead-card"><div class="ead-section-head"><h2>▣ সাম্প্রতিক Exam Activity</h2><a href="{{ route('exam.live') }}">View All →</a></div>
            <ul class="ead-recents ead-more-source" id="ead-source-recent">@forelse($recent as $i => $a)<li class="{{ $i >= 4 ? 'ead-extra-item' : '' }}"><span class="ead-action-icon">{{ $a->status==='Completed' ? '✓' : '▶' }}</span><span class="ead-entry"><b>{{ $a->student_name }}</b> · {{ $a->paper_name }} ({{ $a->status }})<small>{{ $a->end_at ?: $a->start_at }}</small></span></li>@empty<li class="ead-empty">কোনো Exam Activity পাওয়া যায়নি।</li>@endforelse</ul>
            @if($recent->count() > 4)
                <div class="ead-more-row"><button type="button" class="ead-more-btn" data-ead-expand="recent" data-ead-title="সাম্প্রতিক Exam Activity">See More ({{ $recent->count() - 4 }}) <span aria-hidden="true">→</span></button></div>
            @endif
        </div>
        <div class="ead-card"><div class="ead-section-head"><h2>▣ Quick Actions</h2></div>
            <div class="ead-quick-grid"><a href="{{ route('question.create') }}">⊕ প্রশ্ন Create</a><a href="{{ route('routine.index') }}">▦ Routine দেখুন</a><a href="{{ route('paper.create') }}">▤ Paper Create</a><a href="{{ route('paper.index') }}">▣ Paper List</a><a href="{{ route('exam.live') }}">◉ Live Exam</a><a href="{{ route('batch.index') }}">♙ Batch List</a></div>
        </div>
        <div class="ead-card"><div class="ead-section-head"><h2>🏆 Top Participating Batches</h2></div><div class="ead-batch-rank ead-more-source" id="ead-source-ranking">
            @forelse($batchRows as $i=>$r)<div class="ead-rank-item {{ $i >= 4 ? 'ead-extra-item' : '' }}"><div class="ead-rank-title"><span>{{ $i+1 }}. {{ $r['name'] }}</span><strong>{{ $r['attempted'] }} students</strong></div><div class="ead-rank-bar"><span style="width:{{ $r['rate']===null?0:min(100,$r['rate']) }}%"></span></div></div>@empty<div class="ead-empty">৭ দিনে কোনো অংশগ্রহণ পাওয়া যায়নি।</div>@endforelse
        </div>
            @if(count($batchRows) > 4)
                <div class="ead-more-row"><button type="button" class="ead-more-btn" data-ead-expand="ranking" data-ead-title="Top Participating Batches">See More ({{ count($batchRows) - 4 }}) <span aria-hidden="true">→</span></button></div>
            @endif
        </div>
    </div> {{-- End ead-foot-grid: keeps the 4 lower cards full width --}}
    <div class="ead-bottom-grid">
        <div class="ead-card ead-bottom-stat"><h3>▥ উপস্থিতির ধারা (গত ৭ দিন)</h3><div class="ead-week-dots">@php $dMax=max(1,...array_column($dailyTrend,'value')); @endphp @foreach($dailyTrend as $d)<div><i style="--op:{{ max(.13,$d['value']/$dMax) }}"></i>{{ $d['label'] }}<br>{{ $d['value'] }}</div>@endforeach</div><p>প্রতিদিন শুরু করা Exam Attempt</p></div>
        <div class="ead-card ead-bottom-stat"><h3>▧ Question Bank প্রস্তুতি</h3><strong>{{ number_format($questionBank['total']) }}</strong><p>মোট প্রশ্ন · {{ number_format($questionBank['assigned']) }} টি কোনো Paper-এ যুক্ত · {{ number_format($questionBank['unassigned']) }} টি এখনো যুক্ত নয়</p><div class="ead-progress"><span style="width:{{ $questionBank['total'] ? round($questionBank['assigned']*100/$questionBank['total']) : 0 }}%"></span></div></div>
        <div class="ead-card ead-bottom-stat"><h3>🏆 Result Publishing Status</h3><strong>{{ $publishing['published'] }} / {{ $publishing['papers'] }}</strong><p>প্রকাশিত Result Settings / Published Papers (সব Paper মিলিয়ে)</p><div class="ead-progress"><span style="width:{{ $publishing['papers'] ? min(100,round($publishing['published']*100/$publishing['papers'])) : 0 }}%"></span></div></div>
        <div class="ead-card ead-bottom-stat"><h3>▤ গত ৭ দিনের Exam Trend</h3><svg class="ead-chart" viewBox="0 0 570 195" role="img" aria-label="Last seven days attempts"><g stroke="#e7edf8">@foreach([32,68,104,140,176] as $y)<line x1="25" x2="545" y1="{{ $y }}" y2="{{ $y }}"/>@endforeach</g><polyline points="{{ $dayPoints }}" fill="none" stroke="#7059e8" stroke-width="4" stroke-linejoin="round" stroke-linecap="round"/></svg><div class="ead-labels">@foreach($dailyTrend as $d)<span>{{ $d['label'] }}</span>@endforeach</div></div>
    </div>
    <!-- One reusable See More dialog. Contents are copied from the current DB-rendered lists. -->
    <div class="ead-modal-backdrop" id="ead-modal" role="presentation" hidden>
        <div class="ead-modal-panel" role="dialog" aria-modal="true" aria-labelledby="ead-modal-title" tabindex="-1">
            <div class="ead-modal-head"><h2 id="ead-modal-title">Details</h2><button type="button" class="ead-modal-close" id="ead-modal-close" aria-label="Close details">&times;</button></div>
            <div class="ead-modal-body" id="ead-modal-body"></div>
        </div>
    </div>
    <p class="ead-fineprint">Last refreshed: {{ $now->format('d M Y h:i:s A') }} Asia/Dhaka · Data from Routine, Papers, Paper Questions, Exams, Students and Batch membership. Refresh page for latest statistics. This Dashboard makes no claim that unverified Live Attempts correspond to online browsers.</p>
</section>
<script>
(function(){
    var root=document.getElementById('lebd-exam-activity');
    if(!root)return;
    var search=document.getElementById('ead-search');
    var table=document.getElementById('ead-today-table');
    var overlay=document.getElementById('ead-modal');
    var modalPanel=overlay && overlay.querySelector('.ead-modal-panel');
    var modalBody=document.getElementById('ead-modal-body');
    var modalTitle=document.getElementById('ead-modal-title');
    var close=document.getElementById('ead-modal-close');
    var previousFocus=null;

    function filterToday(){
        if(!table || !search)return;
        var term=search.value.trim().toLocaleLowerCase(), matches=0;
        Array.prototype.forEach.call(table.querySelectorAll('tbody tr[data-search]'),function(row){
            var show=row.getAttribute('data-search').indexOf(term)!==-1;
            row.classList.toggle('ead-filter-hidden',!show);
            if(show){row.classList.toggle('ead-extra-item',++matches>5);}
        });
        var btn=root.querySelector('[data-ead-expand="today"]');
        if(btn){
            var extra=Math.max(0,matches-5);
            btn.hidden=extra===0;
            btn.innerHTML='See More ('+extra+') <span aria-hidden="true">→</span>';
        }
    }
    if(search)search.addEventListener('input',filterToday);

    root.addEventListener('click',function(event){
        var btn=event.target.closest('[data-ead-expand]');
        if(!btn || !overlay || !modalBody)return;
        var source=document.getElementById('ead-source-'+btn.getAttribute('data-ead-expand'));
        if(!source)return;
        previousFocus=btn;
        modalTitle.textContent=btn.getAttribute('data-ead-title') || 'Details';
        modalBody.innerHTML='';
        var clone=source.cloneNode(true);
        clone.removeAttribute('id');
        clone.querySelectorAll('[id]').forEach(function(node){node.removeAttribute('id')});
        clone.classList.add('ead-expanded');
        if(btn.getAttribute('data-ead-expand')==='today'){
            clone.querySelectorAll('tr.ead-filter-hidden').forEach(function(row){row.remove()});
        }
        modalBody.appendChild(clone);
        overlay.hidden=false;
        document.body.classList.add('ead-modal-open');
        modalPanel.focus();
    });
    function closeModal(){
        overlay.hidden=true;
        document.body.classList.remove('ead-modal-open');
        modalBody.innerHTML='';
        if(previousFocus)previousFocus.focus();
    }
    if(close)close.addEventListener('click',closeModal);
    if(overlay)overlay.addEventListener('click',function(e){if(e.target===overlay)closeModal()});
    document.addEventListener('keydown',function(e){
        if(!overlay || overlay.hidden)return;
        if(e.key==='Escape'){e.preventDefault();closeModal();}
        if(e.key==='Tab'){
            var tabbable=Array.prototype.slice.call(modalPanel.querySelectorAll('a[href],button:not([disabled]),input:not([disabled])'));
            if(!tabbable.length){e.preventDefault();modalPanel.focus();return;}
            var first=tabbable[0],last=tabbable[tabbable.length-1];
            if(e.shiftKey && document.activeElement===first){e.preventDefault();last.focus()}
            else if(!e.shiftKey && document.activeElement===last){e.preventDefault();first.focus()}
        }
    });
    // Do not refresh while a user is reading a modal or using Search.
    window.setInterval(function(){
        if(!document.hidden && (overlay && overlay.hidden) && (!search || !search.value.trim()) && document.activeElement!==search){
            window.location.reload();
        }
    },60000);
})();
</script>
@endsection
