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



/* LEBD-05 Exam Activity navigation: scoped; existing overview styling retained. */

.lebd-exam-activity [hidden]{display:none!important}

.lebd-exam-activity .ead-nav{display:flex;flex-wrap:wrap;gap:9px;padding:12px;background:#fff;border:1px solid var(--ead-line);border-radius:13px;margin:0 0 14px}

.lebd-exam-activity .ead-nav-button{font:700 12px/1.5 Arial,'Noto Sans Bengali',sans-serif;color:#205183;background:#f5f9ff;border:1px solid #cfe0f7;border-radius:9px;padding:11px 15px;min-height:42px;cursor:pointer;white-space:normal;text-align:center;transition:background .15s}

.lebd-exam-activity .ead-nav-button:hover{background:#eaf3ff}

.lebd-exam-activity .ead-nav-button[aria-selected="true"]{background:#0b61c8;color:#fff;border-color:#0b61c8;box-shadow:0 3px 10px #0b61c828}

.lebd-exam-activity .ead-nav-button:focus-visible{outline:3px solid #76a8fb;outline-offset:2px}

.lebd-exam-activity .ead-tab-panel{min-width:0}

.lebd-exam-activity .ead-tab-header{margin-bottom:15px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:8px}

.lebd-exam-activity .ead-tab-header h2{font-size:20px;color:#12335e;font-weight:800;margin:0 0 5px}

.lebd-exam-activity .ead-tab-header p{font-size:12px;color:#6f83a3;margin:0}

.lebd-exam-activity .ead-design-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}

.lebd-exam-activity .ead-design-card{padding:16px;min-height:112px}

.lebd-exam-activity .ead-design-card h3{font-size:13px;color:#21436d;font-weight:800;margin:0 0 8px}

.lebd-exam-activity .ead-design-card p{font-size:12px;color:#7185a2;line-height:1.65;margin:0}

.lebd-exam-activity .ead-design-wide{grid-column:1/-1;min-height:195px}

.lebd-exam-activity .ead-design-empty{border:1px dashed #bfd3ec;border-radius:10px;padding:24px 15px;background:#f8fbff;text-align:center;color:#627e9f;font-size:13px}

.lebd-exam-activity .ead-table-placeholder{width:100%;border-collapse:collapse;margin-top:12px}

.lebd-exam-activity .ead-table-placeholder th{font-size:11px;text-align:left;color:#5f7896;background:#f2f7ff;padding:12px;border-bottom:1px solid #dfebf9}

.lebd-exam-activity .ead-today-layout{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:12px}

.lebd-exam-activity .ead-today-tile{display:flex;gap:12px;align-items:center;padding:16px}

.lebd-exam-activity .ead-today-symbol{background:#e7f1ff;color:#2168bc;border-radius:12px;width:44px;height:44px;display:grid;place-items:center;flex-shrink:0;font-size:20px}

.lebd-exam-activity .ead-today-tile small{display:block;color:#6c83a0;font-weight:700;font-size:12px}

.lebd-exam-activity .ead-today-tile b{display:block;font-size:12px;color:#294d7a;margin-top:6px}

.lebd-exam-activity .ead-search-overview[hidden]{display:none!important}

@media(max-width:900px){.lebd-exam-activity .ead-design-grid,.lebd-exam-activity .ead-today-layout{grid-template-columns:repeat(2,minmax(0,1fr))}}

@media(max-width:640px){.lebd-exam-activity .ead-nav{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-exam-activity .ead-nav-button{padding:9px 7px}.lebd-exam-activity .ead-design-grid,.lebd-exam-activity .ead-today-layout{grid-template-columns:1fr}}




.lebd-exam-activity .ead-q-metrics{font-size:11px;margin:7px 0;color:#3b5e83;line-height:1.7}
.lebd-exam-activity .ead-q-ready{display:inline-block;background:#e4f8e9;color:#08763d;border-radius:7px;padding:4px 8px;font-size:11px;font-weight:700}
.lebd-exam-activity .ead-q-alert{display:inline-block;background:#fff2dd;color:#9c5a04;border:1px solid #f1d29b;border-radius:7px;padding:5px 8px;font-size:11px;margin-top:4px;font-weight:700}
.lebd-exam-activity .ead-q-actions{display:flex;flex-direction:row;flex-wrap:nowrap;align-items:center;justify-content:flex-end;gap:7px;white-space:nowrap}
.lebd-exam-activity .ead-q-actions form{margin:0}
.lebd-exam-activity .ead-q-btn{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;white-space:nowrap;line-height:1.25;background:#0d72e8;color:#fff!important;border:0;border-radius:7px;padding:9px 10px;font-size:11px;font-weight:700;cursor:pointer;text-decoration:none}
.lebd-exam-activity .ead-q-btn.view{background:#078dbc}
.lebd-exam-activity .ead-q-btn.create{background:#0aa56d}
.lebd-exam-activity .ead-q-btn.pdf{background:#8056ad}
.lebd-exam-activity .ead-q-btn.danger{background:#d9534f}
.lebd-exam-activity .ead-q-muted{font-size:10px;color:#8d99aa}
.lebd-exam-activity .ead-auto-exam-table th:last-child,.lebd-exam-activity .ead-auto-exam-table td:last-child{min-width:450px;text-align:right;white-space:nowrap}

.lebd-exam-activity .ead-question-box{border:1px solid #dce6f4;background:#f9fbff;border-radius:8px;padding:9px 10px;margin-top:7px;max-width:440px}
.lebd-exam-activity .ead-question-counts{display:flex;flex-wrap:wrap;gap:6px 12px;font-size:11px;color:#315278;line-height:1.6;margin-bottom:6px}
.lebd-exam-activity .ead-question-counts strong{color:#143b70}
.lebd-exam-activity .ead-q-btn.setting{background:#5269a8}
.lebd-exam-activity .ead-auto-exam-table{min-width:1250px!important}
.lebd-exam-activity .ead-auto-exam-table th:last-child,.lebd-exam-activity .ead-auto-exam-table td:last-child{text-align:right;width:470px;min-width:470px}
.lebd-exam-activity .ead-auto-exam-table .ead-q-actions{justify-content:flex-end;gap:5px}
.lebd-exam-activity .ead-auto-exam-table .ead-q-btn{font-size:10px;padding:8px 9px}
/* LEBD05_VISIBLE_EXAM_FILTER_V2 */
.lebd-exam-activity .ead-filter-v2 {display:flex;align-items:center;flex-wrap:wrap;gap:10px 16px;padding:12px 14px;margin-bottom:14px;background:#fff;border:1px solid #dce7f8;border-radius:12px}
.lebd-exam-activity .ead-filter-v2-presets,.lebd-exam-activity .ead-filter-v2-custom{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin:0}
.lebd-exam-activity .ead-filter-v2-btn {background:#f1f6fe;border:1px solid #c9daf2;color:#174d8e;padding:8px 13px;border-radius:8px;white-space:nowrap;cursor:pointer;font-size:12px;font-weight:700}
.lebd-exam-activity .ead-filter-v2-btn.selected{background:#0966cf;color:white;border-color:#0966cf}
.lebd-exam-activity .ead-filter-v2-custom[hidden]{display:none!important}
.lebd-exam-activity .ead-filter-v2-custom label{display:flex;gap:5px;align-items:center;color:#456084;font-size:12px}
.lebd-exam-activity .ead-filter-v2-custom input{border:1px solid #c9daf2;border-radius:6px;padding:7px;color:#1d3756;max-width:155px}
.lebd-exam-activity .ead-filter-v2-span{color:#607d9c;font-size:11px}
.lebd-exam-activity .ead-filter-v2-error{color:#ab281d;background:#fff0f0;padding:8px 12px;border-radius:8px}
</style>

<section class="content lebd-exam-activity" id="lebd-exam-activity">

    <div class="ead-head">

        <div class="ead-title"><h1>📊 Exam Activity Dashboard</h1><p>Live Education BD · বাস্তব Routine, Question Paper ও Exam Attempt থেকে প্রস্তুত</p></div>

        <div class="ead-head-controls">

            <input type="search" class="ead-input ead-search-overview" id="ead-search" hidden aria-label="Search today's exams" placeholder="আজকের ব্যাচ বা এক্সাম খুঁজুন...">

            <a class="ead-button" href="{{ route('question.create') }}">⊕ প্রশ্ন Create করুন</a>

            <span class="ead-button secondary">📅 {{ $now->format('d M Y') }}</span>

        </div>

    </div>

    {{-- Design-only navigation. No additional controller queries or routes. --}}

    <nav class="ead-nav" role="tablist" aria-label="Exam Activity Dashboard sections">

        <button class="ead-nav-button" type="button" role="tab" id="ead-tab-today" data-ead-tab="today" aria-controls="ead-panel-today" aria-selected="true">আজকের এক্সাম</button>

        <button class="ead-nav-button" type="button" role="tab" id="ead-tab-overview" data-ead-tab="overview" aria-controls="ead-panel-overview" aria-selected="false" tabindex="-1">Overview</button>

        <button class="ead-nav-button" type="button" role="tab" id="ead-tab-students-today" data-ead-tab="students-today" aria-controls="ead-panel-students-today" aria-selected="false" tabindex="-1">আজ এক্সাম দিয়েছে</button>

        <button class="ead-nav-button" type="button" role="tab" id="ead-tab-completed-today" data-ead-tab="completed-today" aria-controls="ead-panel-completed-today" aria-selected="false" tabindex="-1">আজ সম্পন্ন</button>

        <button class="ead-nav-button" type="button" role="tab" id="ead-tab-needs-questions" data-ead-tab="needs-questions" aria-controls="ead-panel-needs-questions" aria-selected="false" tabindex="-1">প্রশ্ন করা বাকি</button>

        <button class="ead-nav-button" type="button" role="tab" id="ead-tab-batch-exams" data-ead-tab="batch-exams" aria-controls="ead-panel-batch-exams" aria-selected="false" tabindex="-1">ব্যাচের সকল এক্সাম</button>

        <button class="ead-nav-button" type="button" role="tab" id="ead-tab-running-batches" data-ead-tab="running-batches" aria-controls="ead-panel-running-batches" aria-selected="false" tabindex="-1">চলমান সকল ব্যাচের তালিকা</button>

    </nav>

    @php
    // LEBD05_VISIBLE_EXAM_FILTER_V2: only Today's Exam panel; status remains current-time based.
    $eadRange = (string) request()->query('exam_range', 'today');
    if (!in_array($eadRange, ['today', '7', '30', 'custom'], true)) $eadRange = 'today';
    $eadNow = now('Asia/Dhaka');
    $eadStart = $eadNow->toDateString();
    $eadEnd = $eadStart;
    $eadFilterError = null;
    if ($eadRange === '7') $eadEnd = $eadNow->copy()->addDays(6)->toDateString();
    elseif ($eadRange === '30') $eadEnd = $eadNow->copy()->addDays(29)->toDateString();
    elseif ($eadRange === 'custom') {
        $eadFrom = (string) request()->query('exam_from', '');
        $eadTo = (string) request()->query('exam_to', '');
        $eadValid = static function (string $value): bool {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) return false;
            try { return \Carbon\Carbon::createFromFormat('!Y-m-d', $value, 'Asia/Dhaka')->format('Y-m-d') === $value; }
            catch (\Throwable $e) { return false; }
        };
        if (!$eadValid($eadFrom) || !$eadValid($eadTo) || $eadFrom > $eadTo ||
            \Carbon\Carbon::parse($eadFrom ?: $eadStart, 'Asia/Dhaka')->diffInDays(\Carbon\Carbon::parse($eadTo ?: $eadEnd, 'Asia/Dhaka')) > 365) {
            $eadFilterError = 'সঠিক তারিখ দিন (সর্বোচ্চ ৩৬৬ দিন)।';
            $eadRange = 'today';
        } else { $eadStart = $eadFrom; $eadEnd = $eadTo; }
    }
    $lebdTodayAuto = \App\Services\ExamActivity\TodayExamService::get(null, $eadStart, $eadEnd);
@endphp

    <div class="ead-tab-panel" id="ead-panel-today" role="tabpanel" aria-labelledby="ead-tab-today">
        <div class="ead-tab-header">
            <div><h2>আজকের এক্সাম</h2><p>Today's Exam — Our Batch এবং Exam Section (Asia/Dhaka)</p></div>
            <span class="ead-pill">Published Routine · Live Database</span>
        </div>
                {{-- LEBD05_VISIBLE_EXAM_FILTER_V2: visible filter, no new controller/routes --}}
        <div class="ead-filter-v2" aria-label="Exam start date filter">
            <form method="GET" action="{{ url()->current() }}" class="ead-filter-v2-presets">
                <button type="submit" name="exam_range" value="today" class="ead-filter-v2-btn {{ $eadRange === 'today' ? 'selected' : '' }}">Today</button>
                <button type="submit" name="exam_range" value="7" class="ead-filter-v2-btn {{ $eadRange === '7' ? 'selected' : '' }}">7 Days</button>
                <button type="submit" name="exam_range" value="30" class="ead-filter-v2-btn {{ $eadRange === '30' ? 'selected' : '' }}">30 Days</button>
                <button type="button" class="ead-filter-v2-btn {{ $eadRange === 'custom' ? 'selected' : '' }}" onclick="var f=document.getElementById('ead-filter-v2-custom');f.hidden=!f.hidden;if(!f.hidden)document.getElementById('ead-filter-v2-from').focus();">Custom Date</button>
            </form>
            <form method="GET" action="{{ url()->current() }}" id="ead-filter-v2-custom" class="ead-filter-v2-custom" {{ $eadRange === 'custom' || $eadFilterError ? '' : 'hidden' }}>
                <input type="hidden" name="exam_range" value="custom">
                <label>From <input id="ead-filter-v2-from" name="exam_from" type="date" value="{{ request()->query('exam_from', $eadStart) }}" required></label>
                <label>To <input name="exam_to" type="date" value="{{ request()->query('exam_to', $eadEnd) }}" required></label>
                <button type="submit" class="ead-filter-v2-btn selected">Apply</button>
            </form>
            <span class="ead-filter-v2-span">{{ $eadStart }} — {{ $eadEnd }} · Asia/Dhaka</span>
        </div>
        @if($eadFilterError)<p class="ead-filter-v2-error" role="alert">{{ $eadFilterError }}</p>@endif
        <div class="ead-today-layout">
            <div class="ead-card ead-today-tile"><span class="ead-today-symbol">▦</span><div><small>আজকের মোট এক্সাম</small><b>{{ number_format($lebdTodayAuto['total']) }}</b></div></div>
            <div class="ead-card ead-today-tile"><span class="ead-today-symbol">◉</span><div><small>এখন লাইভ এক্সাম</small><b>{{ number_format($lebdTodayAuto['live']) }}</b></div></div>
            <div class="ead-card ead-today-tile"><span class="ead-today-symbol">✓</span><div><small>আজ শেষ হওয়া Exam Window</small><b>{{ number_format($lebdTodayAuto['completed']) }}</b></div></div>
        </div>
        @foreach(['batch' => 'Our Batch Exams', 'exam_section' => 'Exam Section Exams'] as $examSource => $sourceLabel)
            <div class="ead-card ead-design-card" style="margin-bottom:12px">
                <h3>▦ {{ $sourceLabel }} <span class="ead-pill">{{ count($lebdTodayAuto[$examSource]) }}</span></h3>
                <div class="ead-tablewrap">
                    <table class="ead-table ead-auto-exam-table" style="min-width:1060px">
                        <thead><tr><th>SL</th><th>{{ $examSource === 'batch' ? 'Batch' : 'Category / Routine' }}</th><th>Exam</th><th>Start / End Time</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse($lebdTodayAuto[$examSource] as $index => $examItem)
                                @php
                                    // Read-only exam-specific question status from the existing saved tables.
                                    // The TodayExamService remains unchanged; no generation/reset here.
                                    $examItem = array_merge($examItem, \App\Services\ExamActivity\ExamQuestionStatus::forRow($examSource, $examItem));
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><b>{{ $examItem['group'] }}</b><br><small>{{ $examItem['routine'] }}</small></td>
                                    <td>
                                        <b>{{ $examItem['exam'] }}</b>
                                        <div class="ead-question-box">
                                            <div class="ead-question-counts">
                                                <span>Added Questions: <strong>{{ $examItem['added'] === null ? 'যাচাই প্রয়োজন' : number_format($examItem['added']) }}</strong></span>
                                                <span>Question Setting Limit: <strong>{{ $examItem['limit'] === null ? 'নির্ধারিত নয়' : number_format($examItem['limit']) }}</strong></span>
                                                <span>Remaining: <strong>{{ $examItem['remaining'] === null ? '—' : number_format($examItem['remaining']) }}</strong></span>
                                            </div>
                                            @if($examItem['ready'])
                                                <div class="ead-q-ready">✓ Ready Questions ({{ $examItem['added'] }} / {{ $examItem['limit'] }})</div>
                                            @elseif($examItem['limit'] === null || $examItem['added'] === null)
                                                <div class="ead-q-alert" role="status">⚠ {{ $examItem['count_note'] ?: 'Question Setting / Question Mapping যাচাই প্রয়োজন' }}</div>
                                            @else
                                                <div class="ead-q-alert" role="status">⚠ প্রশ্ন কম — আরও {{ $examItem['remaining'] }}টি প্রয়োজন</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $examItem['start'] }}<br><small>{{ $examItem['end'] }}</small></td>
                                    <td><span class="ead-pill {{ $examItem['status'] === 'Live' ? 'live' : ($examItem['status'] === 'Completed' ? 'completed' : ($examItem['status'] === 'Upcoming' ? 'upcoming' : 'alert')) }}">{{ $examItem['status'] }}</span></td>
                                    <td>
                                        {{-- Existing Auto Question GET pages and existing scoped RESET route. No custom reset handler. --}}
                                        @php
                                            $existingAutoQuestionUrl = null;
                                            $directCreateUrl = null;
                                            $directViewUrl = null;
                                            $directReset = null;
                                            $directSettingsUrl = $examItem['setting_url'] ?? null;
                                            $originalParams = [];
                                            $routineId = (int) ($examItem['routine_id'] ?? 0);
                                            $examName = (string) ($examItem['exam'] ?? '');
                                            $examItemId = null;
                                            if ($examSource === 'exam_section') {
                                                $originalRoutine = \Illuminate\Support\Facades\DB::table('lebd_exam_section_routines')
                                                    ->where('id', $routineId)->first(['category_id', 'subcategory_id']);
                                                if ($originalRoutine) {
                                                    $originalParams = ['category' => (int) $originalRoutine->category_id];
                                                    if ($originalRoutine->subcategory_id !== null) {
                                                        $originalParams['subcategory_id'] = (int) $originalRoutine->subcategory_id;
                                                    }
                                                    if (\Illuminate\Support\Facades\Route::has('admin.exam-section.auto-question.exams')) {
                                                        $existingAutoQuestionUrl = route('admin.exam-section.auto-question.exams', array_merge($originalParams, ['search' => $examName]));
                                                    }
                                                    $examItemId = \Illuminate\Support\Facades\DB::table('lebd_exam_section_routine_items')
                                                        ->where('routine_id', $routineId)->where('exam_name', $examName)
                                                        ->where('type', 'exam')->value('id');
                                                }
                                            } elseif ($examSource === 'batch') {
                                                $examItemId = \Illuminate\Support\Facades\DB::table('routine_derived_exam_items')
                                                    ->where('routine_id', $routineId)->where('exam_name', $examName)->value('id');
                                                $originalRoutine = \Illuminate\Support\Facades\DB::table('routines')
                                                    ->where('id', $routineId)->first(['course_id', 'batch_id']);
                                                if ($originalRoutine && \Illuminate\Support\Facades\Route::has('admin.routine-auto-question.batch')) {
                                                    $originalParams = ['courseId' => (int) $originalRoutine->course_id,
                                                        'batchId' => (int) $originalRoutine->batch_id];
                                                    $existingAutoQuestionUrl = route('admin.routine-auto-question.batch', $originalParams);
                                                } elseif (\Illuminate\Support\Facades\Route::has('admin.routine-auto-question.index')) {
                                                    $existingAutoQuestionUrl = route('admin.routine-auto-question.index');
                                                }
                                            }

                                            // Reuse the real Auto Question screens. Route patterns may only carry
                                            // category+routine, with the selected Exam passed as a query parameter.
                                            // No new CREATE/VIEW endpoint is registered here.
                                            $resolveExistingScreen = static function (string $purpose) use ($examSource, $routineId, $examName, $examItemId, $originalParams): ?string {
                                                $candidates = [];
                                                foreach (\Illuminate\Support\Facades\Route::getRoutes() as $candidate) {
                                                    if (!in_array('GET', $candidate->methods(), true)) continue;
                                                    $uri = strtolower(trim($candidate->uri(), '/'));
                                                    $name = strtolower((string) $candidate->getName());
                                                    $path = $name.' '.$uri;
                                                    if ($examSource === 'exam_section') {
                                                        if (!str_contains($path, 'exam-section') || !str_contains($path, 'auto-question')) continue;
                                                    } else {
                                                        if (!str_contains($path, 'routine-auto-question')) continue;
                                                    }
                                                    if (preg_match('/reset|delete|remove|store|save|update|paper-settings/', $path)) continue;
                                                    if ($purpose === 'create' && !preg_match('/create.question|create-question|auto.add|auto-add|generate/', $path)) continue;
                                                    if ($purpose === 'view' && !preg_match('/question.details|question-details|full.paper|full-paper|paper.view|paper-view|preview/', $path)) continue;
                                                    $params = [];
                                                    $ok = true;
                                                    $selected = false;
                                                    $optional = [];
                                                    foreach ($candidate->parameterNames() as $param) {
                                                        $p = strtolower(str_replace(['-','_'], '', $param));
                                                        if (in_array($p, ['category','categoryid'],true) && isset($originalParams['category'])) $params[$param] = $originalParams['category'];
                                                        elseif (in_array($p, ['subcategory','subcategoryid'],true) && isset($originalParams['subcategory_id'])) $params[$param] = $originalParams['subcategory_id'];
                                                        elseif (in_array($p, ['course','courseid'],true) && isset($originalParams['courseId'])) $params[$param] = $originalParams['courseId'];
                                                        elseif (in_array($p, ['batch','batchid'],true) && isset($originalParams['batchId'])) $params[$param] = $originalParams['batchId'];
                                                        elseif (in_array($p, ['routine','routineid'],true) && $routineId > 0) { $params[$param] = $routineId; $selected = true; }
                                                        elseif (in_array($p, ['examname','examno','examlabel','name','label'],true)) { $params[$param] = $examName; $selected = true; }
                                                        elseif (in_array($p, ['item','itemid','routineitem','routineitemid','examitem','examitemid'],true) && $examItemId) { $params[$param] = (int)$examItemId; $selected = true; }
                                                        elseif (in_array($p, ['exam','examid'],true)) {
                                                            if ($examSource === 'exam_section') { $params[$param] = $examName; $selected = true; }
                                                            elseif ($examItemId) { $params[$param] = (int)$examItemId; $selected = true; }
                                                            else { $ok = false; }
                                                        } elseif (in_array($p, ['id'],true) && $examItemId) { $params[$param] = (int)$examItemId; $selected = true; }
                                                        else $ok = false;
                                                        if (!$ok) break;
                                                    }
                                                    if (!$ok || !$selected || !$candidate->getName()) continue;
                                                    try {
                                                        // Existing category/routine pages often pick the exam from ?exam_name.
                                                        // Passing both names is harmless to endpoints that use just one.
                                                        if ($examSource === 'exam_section') {
                                                            $params['exam_name'] = $examName;
                                                            $params['exam'] = $examName;
                                                        }
                                                        $link = route($candidate->getName(), $params);
                                                        $score = (str_contains($uri, 'create-question') || str_contains($uri, 'question-details')) ? 100 : 0;
                                                        $score += $examSource === 'exam_section' && str_contains($uri, 'routine/') ? 20 : 0;
                                                        $score += count($candidate->parameterNames());
                                                        $candidates[] = ['score'=>$score, 'url'=>$link];
                                                    } catch (\Throwable $e) { continue; }
                                                }
                                                usort($candidates, static fn($a,$b) => $b['score'] <=> $a['score']);
                                                return $candidates[0]['url'] ?? null;
                                            };
                                            $directCreateUrl = $resolveExistingScreen('create');
                                            $directViewUrl = $resolveExistingScreen('view');

                                            // Find an already registered Auto Question RESET endpoint.
                                            // Only a target-scoped endpoint is accepted (routine + exam);
                                            // this view never registers an endpoint or deletes questions.
                                            if ($routineId > 0 && $examName !== '') {
                                                foreach (\Illuminate\Support\Facades\Route::getRoutes() as $resetCandidate) {
                                                    $methods = $resetCandidate->methods();
                                                    $httpMethod = in_array('POST', $methods, true) ? 'POST'
                                                        : (in_array('DELETE', $methods, true) ? 'DELETE' : null);
                                                    if ($httpMethod === null) continue;
                                                    $resetUri = strtolower(trim($resetCandidate->uri(), '/'));
                                                    $resetName = strtolower((string) $resetCandidate->getName());
                                                    $resetPath = $resetUri . ' ' . $resetName;
                                                    if (!preg_match('/reset/', $resetPath) || !preg_match('/question/', $resetPath)) continue;
                                                    if ($examSource === 'exam_section') {
                                                        if (!str_contains($resetPath, 'exam-section')) continue;
                                                    } else {
                                                        if (!str_contains($resetPath, 'routine-auto-question')) continue;
                                                    }
                                                    if (!preg_match('/auto.question|auto-question/', $resetPath)) continue;

                                                    $parameters = [];
                                                    $hasRoutine = false;
                                                    $hasExam = false;
                                                    $valid = true;
                                                    foreach ($resetCandidate->parameterNames() as $parameter) {
                                                        $normalized = strtolower(str_replace(['-', '_'], '', $parameter));
                                                        if (in_array($normalized, ['category','categoryid'], true) && isset($originalParams['category'])) {
                                                            $parameters[$parameter] = $originalParams['category'];
                                                        } elseif (in_array($normalized, ['subcategory','subcategoryid'], true) && isset($originalParams['subcategory_id'])) {
                                                            $parameters[$parameter] = $originalParams['subcategory_id'];
                                                        } elseif (in_array($normalized, ['course','courseid'], true) && isset($originalParams['courseId'])) {
                                                            $parameters[$parameter] = $originalParams['courseId'];
                                                        } elseif (in_array($normalized, ['batch','batchid'], true) && isset($originalParams['batchId'])) {
                                                            $parameters[$parameter] = $originalParams['batchId'];
                                                        } elseif (in_array($normalized, ['routine','routineid'], true)) {
                                                            $parameters[$parameter] = $routineId;
                                                            $hasRoutine = true;
                                                        } elseif (in_array($normalized, ['examname','examno','examlabel','name','label'], true)) {
                                                            $parameters[$parameter] = $examName;
                                                            $hasExam = true;
                                                        } elseif (in_array($normalized, ['item','itemid','routineitem','routineitemid','examitem','examitemid'], true) && $examItemId) {
                                                            $parameters[$parameter] = (int) $examItemId;
                                                            $hasExam = true;
                                                        } elseif (in_array($normalized, ['exam','examid'], true)) {
                                                            $parameters[$parameter] = $examSource === 'exam_section' ? $examName : (int) $examItemId;
                                                            $hasExam = true;
                                                        } elseif ($normalized === 'id' && $examItemId) {
                                                            $parameters[$parameter] = (int) $examItemId;
                                                            $hasExam = true;
                                                        } else {
                                                            $valid = false;
                                                            break;
                                                        }
                                                    }
                                                    // Some existing routes accept exam_name + routine_id as form fields.
                                                    // The path must identify routine/exam OR the route's action must
                                                    // be explicitly an auto-question reset with these form fields.
                                                    if (!$valid || !$resetCandidate->getName()) continue;
                                                    $actionName = strtolower($resetCandidate->getActionName());
                                                    $explicitAutoReset = (bool) preg_match('/auto.*question.*reset|reset.*auto.*question/', $actionName);
                                                    if (!$hasExam && !$explicitAutoReset) continue;
                                                    if (!$hasRoutine && !$explicitAutoReset) continue;
                                                    try {
                                                        $directReset = [
                                                            'url' => route($resetCandidate->getName(), $parameters),
                                                            'method' => $httpMethod,
                                                        ];
                                                        break;
                                                    } catch (\Throwable $exception) {
                                                        continue;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <div class="ead-q-actions">
                                            @if($directSettingsUrl)
                                                <a class="ead-q-btn setting" href="{{ \Illuminate\Support\Facades\Route::has('admin.exam-activity.open-existing') ? route('admin.exam-activity.open-existing', ['target' => $directSettingsUrl]) : $directSettingsUrl }}" title="বিদ্যমান Question Paper Settings পেজ">⚙ Question Setting</a>
                                            @endif
                                            @if($directViewUrl)
                                                <a class="ead-q-btn view" href="{{ $directViewUrl }}">▤ View</a>
                                            @else
                                                <span class="ead-q-btn view" style="opacity:.55;cursor:not-allowed" title="এই Exam-এর direct View GET route পাওয়া যায়নি">▤ View</span>
                                            @endif
                                            {{-- Study PDF: all available MCQs in this exam's saved routine topics and ranges, not generated paper questions. --}}
                                            @if($examSource === 'exam_section' && (int)$routineId > 0 && (int)$examItemId > 0)
                                                <a class="ead-q-btn pdf" target="_blank" rel="noopener" href="{{ url('/admin/exam-activity/study-pdf/exam-section/'.(int)$routineId.'/'.(int)$examItemId) }}" title="All available Routine Topic Questions for Student Study">▣ Study PDF</a>
                                            @else
                                                <span class="ead-q-btn pdf" style="opacity:.55;cursor:not-allowed" title="Batch Study PDF requires a separately verified batch topic mapping">▣ Study PDF</span>
                                            @endif
                                            @if($directCreateUrl)
                                                <a class="ead-q-btn create" href="{{ \Illuminate\Support\Facades\Route::has('admin.exam-activity.open-existing') ? route('admin.exam-activity.open-existing', ['target' => $directCreateUrl]) : $directCreateUrl }}">+ Create Question</a>
                                            @else
                                                <span class="ead-q-btn create" style="opacity:.55;cursor:not-allowed" title="এই Exam-এর direct Create GET route পাওয়া যায়নি">+ Create Question</span>
                                            @endif
                                            @if($directReset)
                                                <form action="{{ $directReset['url'] }}" method="POST" class="ead-direct-reset-form" style="margin:0;display:inline-flex">
                                                    @csrf
                                                    @if($directReset['method'] === 'DELETE') @method('DELETE') @endif
                                                    <input type="hidden" name="routine_id" value="{{ $routineId }}">
                                                    <input type="hidden" name="exam_name" value="{{ $examName }}">
                                                    <input type="hidden" name="exam" value="{{ $examName }}">
                                                    @if($examItemId)<input type="hidden" name="item_id" value="{{ $examItemId }}">@endif
                                                    @if(isset($originalParams['category']))<input type="hidden" name="category_id" value="{{ $originalParams['category'] }}">@endif
                                                    @if(isset($originalParams['subcategory_id']))<input type="hidden" name="subcategory_id" value="{{ $originalParams['subcategory_id'] }}">@endif
                                                    <button type="submit" class="ead-q-btn danger" style="border:0;cursor:pointer">↺ Reset Questions</button>
                                                </form>
                                            @else
                                                <span class="ead-q-btn danger" style="opacity:.55;cursor:not-allowed" title="Existing scoped Reset POST route not confirmed; no destructive fallback">↺ Reset Questions</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="ead-empty">আজকের Published Routine-এ এই Section-এর কোনো Exam পাওয়া যায়নি।</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
        <p class="ead-fineprint">শুধু আজ শুরু হওয়া Published Routine-এর Exam দেখানো হয়েছে। Completed মানে Exam Window শেষ, Student Submit নয়। End Time না থাকলে Live অনুমান করা হয় না।</p>
    </div>

    <div class="ead-tab-panel" id="ead-panel-overview" role="tabpanel" aria-labelledby="ead-tab-overview" hidden>

        {{-- The entire existing live dashboard remains here, unchanged. --}}

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

    </div> {{-- End existing Overview dashboard --}}

    <!-- One reusable See More dialog. Contents are copied from the current DB-rendered lists. -->

    <div class="ead-modal-backdrop" id="ead-modal" role="presentation" hidden>

        <div class="ead-modal-panel" role="dialog" aria-modal="true" aria-labelledby="ead-modal-title" tabindex="-1">

            <div class="ead-modal-head"><h2 id="ead-modal-title">Details</h2><button type="button" class="ead-modal-close" id="ead-modal-close" aria-label="Close details">&times;</button></div>

            <div class="ead-modal-body" id="ead-modal-body"></div>

        </div>

    </div>

    <div class="ead-tab-panel" id="ead-panel-students-today" role="tabpanel" aria-labelledby="ead-tab-students-today" hidden>

        <div class="ead-tab-header"><div><h2>আজ এক্সাম দিয়েছে</h2><p>আজ পরীক্ষা দেওয়া Student-দের তালিকা</p></div><span class="ead-pill">ডিজাইন প্রস্তুত · ডাটা পরে</span></div>

        <div class="ead-design-grid">

            <div class="ead-card ead-design-card"><h3>U</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>n</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>i</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>q</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>u</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>e</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3> </h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>S</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>t</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>u</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>d</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>e</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>n</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>t</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>s</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div>

            <div class="ead-card ead-design-card ead-design-wide"><h3>আজ এক্সাম দিয়েছে — বিস্তারিত তালিকা</h3>

                <div class="ead-tablewrap"><table class="ead-table-placeholder"><thead><tr><th>S</th><th>t</th><th>u</th><th>d</th><th>e</th><th>n</th><th>t</th><th> </th><th>D</th><th>e</th><th>t</th><th>a</th><th>i</th><th>l</th><th>s</th></tr></thead></table></div>

                <div class="ead-design-empty">এই Section-এর Interface প্রস্তুত। পরবর্তী ধাপে বাস্তব Exam Data, Filter ও Action সংযুক্ত করা হবে।</div>

            </div>

        </div>

    </div>

    <div class="ead-tab-panel" id="ead-panel-completed-today" role="tabpanel" aria-labelledby="ead-tab-completed-today" hidden>

        <div class="ead-tab-header"><div><h2>আজ সম্পন্ন</h2><p>আজ সম্পন্ন হওয়া Exam Attempt-গুলোর তথ্য</p></div><span class="ead-pill">ডিজাইন প্রস্তুত · ডাটা পরে</span></div>

        <div class="ead-design-grid">

            <div class="ead-card ead-design-card"><h3>C</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>o</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>m</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>p</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>l</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>e</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>t</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>e</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>d</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3> </h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>E</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>x</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>a</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>m</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>s</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div>

            <div class="ead-card ead-design-card ead-design-wide"><h3>আজ সম্পন্ন — বিস্তারিত তালিকা</h3>

                <div class="ead-tablewrap"><table class="ead-table-placeholder"><thead><tr><th>S</th><th>u</th><th>b</th><th>m</th><th>i</th><th>t</th><th>t</th><th>e</th><th>d</th><th> </th><th>A</th><th>t</th><th>t</th><th>e</th><th>m</th><th>p</th><th>t</th><th>s</th></tr></thead></table></div>

                <div class="ead-design-empty">এই Section-এর Interface প্রস্তুত। পরবর্তী ধাপে বাস্তব Exam Data, Filter ও Action সংযুক্ত করা হবে।</div>

            </div>

        </div>

    </div>

    <div class="ead-tab-panel" id="ead-panel-needs-questions" role="tabpanel" aria-labelledby="ead-tab-needs-questions" hidden>

        <div class="ead-tab-header"><div><h2>প্রশ্ন করা বাকি</h2><p>প্রশ্ন প্রস্তুতি ও Paper Setup-এর জন্য পৃথক তালিকা</p></div><span class="ead-pill">ডিজাইন প্রস্তুত · ডাটা পরে</span></div>

        <div class="ead-design-grid">

            <div class="ead-card ead-design-card"><h3>P</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>e</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>n</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>d</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>i</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>n</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>g</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3> </h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>Q</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>u</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>e</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>s</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>t</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>i</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>o</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>n</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3> </h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>P</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>a</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>p</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>e</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>r</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>s</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div>

            <div class="ead-card ead-design-card ead-design-wide"><h3>প্রশ্ন করা বাকি — বিস্তারিত তালিকা</h3>

                <div class="ead-tablewrap"><table class="ead-table-placeholder"><thead><tr><th>Q</th><th>u</th><th>e</th><th>s</th><th>t</th><th>i</th><th>o</th><th>n</th><th>s</th><th> </th><th>R</th><th>e</th><th>m</th><th>a</th><th>i</th><th>n</th><th>i</th><th>n</th><th>g</th></tr></thead></table></div>

                <div class="ead-design-empty">এই Section-এর Interface প্রস্তুত। পরবর্তী ধাপে বাস্তব Exam Data, Filter ও Action সংযুক্ত করা হবে।</div>

            </div>

        </div>

    </div>

    <div class="ead-tab-panel" id="ead-panel-batch-exams" role="tabpanel" aria-labelledby="ead-tab-batch-exams" hidden>

        <div class="ead-tab-header"><div><h2>ব্যাচের সকল এক্সাম</h2><p>Batch অনুযায়ী সব Exam-এর তথ্য ও Action</p></div><span class="ead-pill">ডিজাইন প্রস্তুত · ডাটা পরে</span></div>

        <div class="ead-design-grid">

            <div class="ead-card ead-design-card"><h3>T</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>o</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>t</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>a</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>l</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3> </h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>B</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>a</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>t</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>c</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>h</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>e</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>s</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div>

            <div class="ead-card ead-design-card ead-design-wide"><h3>ব্যাচের সকল এক্সাম — বিস্তারিত তালিকা</h3>

                <div class="ead-tablewrap"><table class="ead-table-placeholder"><thead><tr><th>T</th><th>o</th><th>t</th><th>a</th><th>l</th><th> </th><th>E</th><th>x</th><th>a</th><th>m</th><th>s</th></tr></thead></table></div>

                <div class="ead-design-empty">এই Section-এর Interface প্রস্তুত। পরবর্তী ধাপে বাস্তব Exam Data, Filter ও Action সংযুক্ত করা হবে।</div>

            </div>

        </div>

    </div>

    <div class="ead-tab-panel" id="ead-panel-running-batches" role="tabpanel" aria-labelledby="ead-tab-running-batches" hidden>

        <div class="ead-tab-header"><div><h2>চলমান সকল ব্যাচের তালিকা</h2><p>চলমান Batch পর্যবেক্ষণের জন্য আলাদা তালিকা</p></div><span class="ead-pill">ডিজাইন প্রস্তুত · ডাটা পরে</span></div>

        <div class="ead-design-grid">

            <div class="ead-card ead-design-card"><h3>R</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>u</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>n</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>n</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>i</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>n</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>g</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3> </h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>B</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>a</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>t</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>c</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>h</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>e</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div><div class="ead-card ead-design-card"><h3>s</h3><p>ডাটা সংযোগ পরবর্তী ধাপে</p></div>

            <div class="ead-card ead-design-card ead-design-wide"><h3>চলমান সকল ব্যাচের তালিকা — বিস্তারিত তালিকা</h3>

                <div class="ead-tablewrap"><table class="ead-table-placeholder"><thead><tr><th>B</th><th>a</th><th>t</th><th>c</th><th>h</th><th> </th><th>E</th><th>x</th><th>a</th><th>m</th><th>s</th></tr></thead></table></div>

                <div class="ead-design-empty">এই Section-এর Interface প্রস্তুত। পরবর্তী ধাপে বাস্তব Exam Data, Filter ও Action সংযুক্ত করা হবে।</div>

            </div>

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



    // Tab switcher: default Today's Exam; Overview retains all original dashboard widgets.

    var tabs=Array.prototype.slice.call(root.querySelectorAll('[data-ead-tab]'));

    function openDashboardTab(key, focusButton){

        tabs.forEach(function(tab){

            var selected=tab.getAttribute('data-ead-tab')===key;

            tab.setAttribute('aria-selected',selected?'true':'false');

            tab.tabIndex=selected?0:-1;

            var panel=document.getElementById('ead-panel-'+tab.getAttribute('data-ead-tab'));

            if(panel)panel.hidden=!selected;

            if(selected&&focusButton)tab.focus();

        });

        if(search)search.hidden=key!=='overview';

    }

    root.addEventListener('click',function(e){

        var tab=e.target.closest('[data-ead-tab]');

        if(tab&&root.contains(tab))openDashboardTab(tab.getAttribute('data-ead-tab'),false);

    });

    root.querySelector('.ead-nav').addEventListener('keydown',function(e){

        if(!['ArrowRight','ArrowLeft','Home','End'].includes(e.key))return;

        var idx=tabs.indexOf(document.activeElement);

        if(idx<0)return;

        e.preventDefault();

        var next=e.key==='Home'?0:e.key==='End'?tabs.length-1:(idx+(e.key==='ArrowRight'?1:-1)+tabs.length)%tabs.length;

        openDashboardTab(tabs[next].getAttribute('data-ead-tab'),true);

    });

    openDashboardTab('today',false);





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

        if(!document.hidden && root.querySelector('[data-ead-tab="overview"]').getAttribute('aria-selected')==='true' && (overlay && overlay.hidden) && (!search || !search.value.trim()) && document.activeElement!==search){

            window.location.reload();

        }

    },60000);

})();

</script>


{{-- Keep the admin on this Dashboard after the existing Auto Question RESET action.
     The endpoint and mutation remain fully owned by the working Auto Question module. --}}
<script>
(function () {
    document.addEventListener('submit', async function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.classList.contains('ead-direct-reset-form')) return;
        event.preventDefault();
        if (!window.confirm('এই Exam-এর সংরক্ষিত Question Links Reset করবেন?')) return;
        const button = form.querySelector('button[type="submit"]');
        if (button && button.disabled) return;
        const originalText = button ? button.textContent : '';
        if (button) { button.disabled = true; button.textContent = 'Resetting...'; }
        try {
            const target = new URL(form.action, window.location.href);
            if (target.origin !== window.location.origin) throw new Error('Cross-origin reset is not allowed.');
            const response = await fetch(target.href, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                redirect: 'follow'
            });
            if (!response.ok) throw new Error('Reset request failed (HTTP ' + response.status + ').');
            const endUrl = new URL(response.url, window.location.href);
            if (endUrl.pathname.includes('/login')) throw new Error('Admin session expired; please sign in.');
            // The server may redirect to the existing Exam List after POST, but fetch
            // follows the redirect internally: the browser stays on Exam Activity.
            window.location.reload();
        } catch (error) {
            window.alert('Reset সম্পন্ন হয়েছে নিশ্চিত করা যায়নি। ' + error.message);
            if (button) { button.disabled = false; button.textContent = originalText; }
        }
    }, true);
})();
</script>

<!-- LEBD05_SAVE_RETURN_CLIENT_V4: origin tracking for existing Create/Setting pages -->
<script>
(function () {
  try {
    sessionStorage.removeItem('lebd05_save_return_v4');
    document.addEventListener('click', function (ev) {
      var a = ev.target.closest('a.ead-q-btn.create, a.ead-q-btn.setting');
      if (!a || !a.href || a.getAttribute('href') === '#') return;
      sessionStorage.setItem('lebd05_save_return_v4', JSON.stringify({at: Date.now()}));
    }, true);
  } catch (e) {}
})();
</script>

@endsection


<script>
// LEBD05 PDF: existing read-only question paper -> browser print dialog / Save as PDF.
// Does not create or overwrite question papers or touch saved MCQs.
document.addEventListener('click', function (event) {
    var btn = event.target.closest('button.ead-paper-print');
    if (!btn) return;
    var url = btn.getAttribute('data-paper-url');
    if (!url) return;
    var paperWindow = window.open(url, '_blank');
    if (!paperWindow) {
        alert('Please allow pop-ups to print the PDF.');
        return;
    }
    paperWindow.addEventListener('load', function () {
        paperWindow.focus();
        paperWindow.print();
    }, {once:true});
});
</script>
