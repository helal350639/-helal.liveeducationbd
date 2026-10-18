@extends('dashboard')
@section('title', 'Recent Activity Dashboard')
@section('content')
@php
    $maxCourse = max(1, (int) ($topCourses->max('total') ?? 0));
    $maxClick = max(1, (int) ($topClicks->max('total') ?? 0));
    $maxPopular = max(1, (int) ($popularPages->max('total') ?? 0));
    $maxFunnel = max(1, (int) max(array_filter(array_column($funnel, 'count'), 'is_numeric') ?: [0]));
    $typeColors = ['#1979f6', '#0dbb80', '#f59a1a', '#9656ec', '#fa5a75', '#00b3ce'];
    $colorStops = [];
    $sumTypes = max(1, (int) $activityTypes->sum('total'));
    $position = 0;
    foreach ($activityTypes as $key => $typeRow) {
        $next = min(100, $position + 100 * (int) $typeRow->total / $sumTypes);
        $colorStops[] = $typeColors[$key % count($typeColors)] . ' ' . round($position, 2) . '% ' . round($next, 2) . '%';
        $position = $next;
    }
    $donut = $activityTypes->count() ? 'conic-gradient(' . implode(', ', $colorStops) . ')' : '#e7eef9';
    $maxTrend = max(1, max(array_column($trend, 'visitors') ?: [0]));
    $trendCount = max(1, count($trend) - 1);
    $points = [];
    foreach ($trend as $index => $item) {
        $points[] = round(30 + $index * (510 / $trendCount), 1) . ',' . round(173 - ((int) $item['visitors'] / $maxTrend) * 140, 1);
    }
    $pointString = implode(' ', $points);
    $areaPoints = '30,180 ' . $pointString . ' 540,180';
    $avgMinutes = intdiv($stats['avg_seconds'], 60);
    $avgRemaining = $stats['avg_seconds'] % 60;

    // The Student Growth chart is based on registered students, not tracked page events.
    $growthPeak = max(1, max(array_column($studentGrowth, 'total') ?: [0]));
    $growthAxisMax = max(4, (int) ceil($growthPeak / 4) * 4);
    $growthSpan = max(1, count($studentGrowth) - 1);
    $growthPoints = [];
    foreach ($studentGrowth as $idx => $growthRow) {
        $gx = 54 + ($idx * 492 / $growthSpan);
        $gy = 212 - ((int) $growthRow['total'] / $growthAxisMax) * 172;
        $growthPoints[] = round($gx, 1) . ',' . round($gy, 1);
    }
    $growthPolyline = implode(' ', $growthPoints);
    $growthArea = '54,212 ' . $growthPolyline . ' 546,212';
    $growthLabelStep = max(1, (int) ceil($growthSpan / 5));

    $enrollmentColors = ['#167bf4', '#8ac323', '#ffa51d', '#8655eb', '#f44970', '#06b5c7'];
    $enrollStops = [];
    $enrollPosition = 0;
    foreach ($enrollmentBreakdown as $idx => $part) {
        $percentage = $enrollmentTotal > 0 ? 100 * (int) $part['count'] / $enrollmentTotal : 0;
        $stop = min(100, $enrollPosition + $percentage);
        $enrollStops[] = $enrollmentColors[$idx % count($enrollmentColors)] . ' ' . round($enrollPosition, 2) . '% ' . round($stop, 2) . '%';
        $enrollPosition = $stop;
    }
    $enrollmentDonut = $enrollmentTotal > 0
        ? 'conic-gradient(' . implode(', ', $enrollStops) . ')'
        : '#eaf0f8';
@endphp
<style>
/* All styles scoped to this page: leave the existing AdminLTE and sidebar untouched. */
.lebd-recent{--blue:#1477f3;--navy:#123560;--muted:#7890ae;--border:#e2ebf8;background:#f2f7fe;color:var(--navy);margin:-12px -15px -15px;padding:18px;font-family:Arial,'Noto Sans Bengali',sans-serif;min-height:100vh}
.lebd-recent *{box-sizing:border-box}
.lebd-recent .rad-page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}
.lebd-recent h1{font-size:24px;font-weight:850;margin:0;color:#11345d}
.lebd-recent .rad-sub{font-size:12px;color:var(--muted);line-height:1.5;margin:4px 0 0}
.lebd-recent .rad-date{padding:9px 14px;border:1px solid var(--border);background:#fff;border-radius:10px;color:#5678a6;font-size:12px;font-weight:700}
.lebd-recent .rad-filters{display:grid;grid-template-columns:1.05fr 1fr 1fr 1fr .9fr 1fr auto;gap:9px;align-items:end;background:#fff;border:1px solid var(--border);border-radius:13px;padding:12px;margin-bottom:13px}
.lebd-recent .rad-filter{min-width:0}
.lebd-recent .rad-filter label{display:block;font-size:10px;color:#5e779b;margin-bottom:4px;font-weight:750}
.lebd-recent .rad-filter select,.lebd-recent .rad-filter input{height:37px;min-width:0;width:100%;padding:7px 8px;border:1px solid #dce7f7;border-radius:8px;background:#fbfdff;color:#315481;font-size:11px;outline:none}
.lebd-recent .rad-button{background:var(--blue);border:0;border-radius:8px;padding:10px 14px;color:#fff!important;font-size:12px;font-weight:750;text-decoration:none!important;white-space:nowrap;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:7px}
.lebd-recent .rad-button:hover{background:#0860cf}
.lebd-recent .rad-button.subtle{background:#eaf3ff;color:#1766bc!important}
.lebd-recent .rad-stats{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px;margin-bottom:14px}
.lebd-recent .rad-stat{background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:0 4px 16px rgba(20,70,125,.03);padding:13px;display:flex;gap:10px;align-items:center;min-width:0;min-height:99px}
.lebd-recent .rad-stat .icon-box{width:42px;height:42px;flex:none;display:grid;place-items:center;border-radius:10px;font-size:19px;color:#fff;background:#0bbb77}
.lebd-recent .rad-stat:nth-child(2) .icon-box{background:#187bf5}.lebd-recent .rad-stat:nth-child(3) .icon-box{background:#ffa018}.lebd-recent .rad-stat:nth-child(4) .icon-box{background:#824ce4}.lebd-recent .rad-stat:nth-child(5) .icon-box{background:#f14a56}.lebd-recent .rad-stat:nth-child(6) .icon-box{background:#05b3cd}
.lebd-recent .rad-stat small{font-size:10px;font-weight:700;color:#627e9e;display:block;line-height:1.5}
.lebd-recent .rad-stat strong{font-size:21px;font-weight:900;display:block;margin:5px 0;color:#112e58;line-height:1;white-space:nowrap}
.lebd-recent .rad-stat em{font-size:10px;color:#7890ae;font-style:normal;display:block}
.lebd-recent .rad-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px;margin-bottom:12px;align-items:stretch}
.lebd-recent .rad-card{background:#fff;border:1px solid var(--border);border-radius:12px;min-width:0;overflow:hidden;display:flex;flex-direction:column;height:285px;box-shadow:0 4px 13px rgba(20,60,110,.032)}
.lebd-recent .rad-card-head{display:flex;justify-content:space-between;align-items:center;gap:7px;padding:12px;border-bottom:1px solid #edf2fa;flex:none}
.lebd-recent .rad-card-head h2{font-size:12px;font-weight:800;margin:0;color:#173d68;line-height:1.45}
.lebd-recent .rad-more{background:none;border:0;color:#1479ed;font-size:11px;font-weight:800;cursor:pointer;white-space:nowrap;padding:3px}
.lebd-recent .rad-card-body{padding:10px 12px;min-height:0;overflow:hidden;flex:1}
.lebd-recent .rad-card-note{font-size:10px;color:#8197b4;padding:6px 12px 9px;margin-top:auto;border-top:1px solid #f0f4fa;flex:none}
.lebd-recent .rad-list{list-style:none;padding:0;margin:0}
.lebd-recent .rad-list li{display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #edf2f9;font-size:11px;line-height:1.2;min-width:0}
.lebd-recent .rad-list li:last-child{border-bottom:0}
.lebd-recent .rad-list .rad-name{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#315378}
.lebd-recent .rad-list b{color:#15385e;font-size:11px;flex:none}
.lebd-recent .rad-line-track{flex:0 0 34%;height:7px;background:#edf2f9;border-radius:10px;overflow:hidden}
.lebd-recent .rad-line-track>span{height:100%;display:block;background:linear-gradient(90deg,#2d8cf3,#65bbf2);border-radius:10px}
.lebd-recent .rad-list li:nth-child(2) .rad-line-track>span{background:#18bd90}.lebd-recent .rad-list li:nth-child(3) .rad-line-track>span{background:#f8a127}.lebd-recent .rad-list li:nth-child(4) .rad-line-track>span{background:#9864ec}.lebd-recent .rad-list li:nth-child(5) .rad-line-track>span{background:#f46d8e}
.lebd-recent .rad-icon{width:25px;height:25px;flex:none;border-radius:8px;display:grid;place-items:center;background:#e9f2ff;color:#1170ea;font-size:12px}
.lebd-recent .rad-donut-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);align-items:center;gap:9px;height:100%}
.lebd-recent .rad-donut{width:min(144px,100%);aspect-ratio:1;border-radius:50%;display:grid;place-items:center;margin:auto;background:var(--gradient)}
.lebd-recent .rad-hole{width:65%;height:65%;background:#fff;border-radius:50%;display:grid;place-items:center;align-content:center;text-align:center;font-size:10px;color:#7b8ba4}
.lebd-recent .rad-hole strong{font-size:19px;color:#163965}
.lebd-recent .rad-legend{font-size:10px;color:#647b98;line-height:1.9;overflow:hidden}
.lebd-recent .rad-legend>div{display:flex;gap:5px;align-items:center;white-space:nowrap;overflow:hidden}
.lebd-recent .rad-legend .swatch{width:8px;height:8px;flex:none;border-radius:50%;background:var(--c)}
.lebd-recent .rad-legend b{margin-left:auto;color:#173a66;padding-left:5px}
.lebd-recent svg.rad-svg{width:100%;max-height:201px;display:block;overflow:visible}
.lebd-recent .rad-chart-labels{display:flex;justify-content:space-between;color:#738cab;font-size:9px;margin-top:4px}
.lebd-recent .rad-event{display:flex;gap:7px;border-bottom:1px solid #eef3fa;padding:8px 0;align-items:flex-start}
.lebd-recent .rad-event:last-child{border:0}
.lebd-recent .rad-event .rad-event-text{min-width:0;flex:1;font-size:10px;line-height:1.5;color:#5b7293;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lebd-recent .rad-event .rad-event-text b{font-size:11px;color:#214a7f}
.lebd-recent .rad-event time{font-size:9px;color:#95a6bc;flex:none}
.lebd-recent .rad-rank{flex:none;width:22px;height:22px;border-radius:50%;display:grid;place-items:center;background:#f6a11d;color:#fff;font-size:10px;font-weight:800}
.lebd-recent .rad-rank.r2{background:#00b6c9}.lebd-recent .rad-rank.r3{background:#557af4}.lebd-recent .rad-rank.r4{background:#fa596a}.lebd-recent .rad-rank.r5{background:#7dbb21}
.lebd-recent .rad-funnel{height:100%;display:flex;flex-direction:column;justify-content:space-around}
.lebd-recent .rad-funnel-row{display:flex;align-items:center;gap:8px;font-size:11px;color:#376287}
.lebd-recent .rad-funnel-symbol{flex:none;border-radius:50%;width:26px;height:26px;background:#e8f7f1;display:grid;place-items:center;color:#0aaa72}
.lebd-recent .rad-funnel-row strong{display:block;font-size:12px;color:#163b64}
.lebd-recent .rad-funnel-label{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lebd-recent .rad-funnel-bar{width:62px;height:7px;background:#eff4fb;border-radius:10px;overflow:hidden;flex:none}
.lebd-recent .rad-funnel-bar>span{display:block;height:100%;background:#1688f9;border-radius:10px}
.lebd-recent .rad-bottom{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:12px}
.lebd-recent .rad-bottom-head{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;padding:13px 15px;border-bottom:1px solid #e5eef9}
.lebd-recent .rad-bottom-head h2{margin:0;font-size:14px;font-weight:800}
.lebd-recent .rad-tablewrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}
.lebd-recent table{border-collapse:collapse;width:100%;font-size:11px;min-width:790px}
.lebd-recent th{color:#4e6c94;background:#f0f6fd;text-align:left;white-space:nowrap}
.lebd-recent td,.lebd-recent th{padding:10px 9px;border-bottom:1px solid #eaf0f7}
.lebd-recent tr:last-child td{border-bottom:0}
.lebd-recent td a{font-weight:750;color:#0d74ef}
.lebd-recent .rad-pill{font-size:10px;font-weight:750;color:#087e57;background:#e5f9ef;border-radius:9px;padding:5px 8px;white-space:nowrap}
.lebd-recent .rad-pill.old{background:#eef2f8;color:#67809e}
.lebd-recent .rad-pager{display:flex;gap:12px;justify-content:space-between;align-items:center;padding:12px 15px;border-top:1px solid #edf2fa;font-size:11px;color:#6f89ad}
.lebd-recent .rad-pager nav{display:flex;gap:7px}
.lebd-recent .rad-pager a{border:1px solid #cfe0f6;padding:7px 10px;border-radius:7px;color:#1372e2;text-decoration:none}
.lebd-recent .rad-muted{color:#8398b2;font-size:11px;line-height:1.6}
.lebd-recent .rad-hidden{display:none!important}
/* Details modal, no fake content; all displayed rows come from DB. */
.lebd-recent .rad-modal{position:fixed;inset:0;z-index:100000;background:rgba(10,28,57,.58);padding:25px;display:none;align-items:center;justify-content:center}
.lebd-recent .rad-modal.is-open{display:flex}
.lebd-recent .rad-dialog{width:min(680px,100%);max-height:88vh;background:#fff;border-radius:15px;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 15px 70px rgba(0,0,0,.2)}
.lebd-recent .rad-dialog-head{display:flex;justify-content:space-between;padding:16px;border-bottom:1px solid #e1ebf7;gap:10px}
.lebd-recent .rad-dialog h3{font-size:16px;margin:0;font-weight:800}
.lebd-recent .rad-close{cursor:pointer;background:#edf3fc;border:0;border-radius:7px;padding:3px 10px;font-size:22px}
.lebd-recent .rad-dialog-body{padding:13px 19px;overflow-y:auto}
.lebd-recent .rad-dialog-body .rad-list li{font-size:13px;padding:10px 0}
.lebd-recent .rad-dialog-body .rad-event-text{white-space:normal!important}
.lebd-recent .rad-dialog-foot{padding:12px 17px;border-top:1px solid #e8f0fa;display:flex;justify-content:flex-end}
@media(max-width:1400px){.lebd-recent .rad-stats{grid-template-columns:repeat(3,minmax(0,1fr))}.lebd-recent .rad-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-recent .rad-filters{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:730px){.lebd-recent{padding:10px;margin:0}.lebd-recent h1{font-size:21px}.lebd-recent .rad-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-recent .rad-grid{grid-template-columns:1fr}.lebd-recent .rad-filters{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-recent .rad-card{height:270px}.lebd-recent .rad-stat{min-height:86px;padding:10px}.lebd-recent .rad-stat .icon-box{height:34px;width:34px}.lebd-recent .rad-stat strong{font-size:18px}}

/* Growth & Enrollment: 2 large equal cards above the activity analysis grid. */
.lebd-recent .rad-growth-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:0 0 14px;align-items:stretch}
.lebd-recent .rad-growth-card{background:#fff;border:1px solid var(--border);border-radius:12px;min-width:0;min-height:326px;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 4px 16px rgba(20,70,125,.04)}
.lebd-recent .rad-growth-header{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:14px 15px;border-bottom:1px solid #edf2fa;flex-wrap:wrap}
.lebd-recent .rad-growth-header h2{font-size:15px;font-weight:800;margin:0;color:#183963}
.lebd-recent .rad-growth-header select{border:1px solid #dce8f6;background:#fff;border-radius:8px;padding:7px 10px;font-size:11px;color:#456586;outline:none}
.lebd-recent .rad-growth-body{flex:1;min-height:0;padding:12px 15px}
.lebd-recent .rad-growth-body svg{width:100%;height:230px;display:block;overflow:visible}
.lebd-recent .rad-growth-gridline{stroke:#e5edf8;stroke-width:1}
.lebd-recent .rad-growth-axis{fill:#7790b1;font-size:11px}
.lebd-recent .rad-growth-foot{color:#8497af;font-size:10px;padding:0 15px 12px}
.lebd-recent .rad-enrollment-flex{display:grid;grid-template-columns:minmax(150px, 42%) minmax(0,1fr);gap:12px;align-items:center;min-height:244px}
.lebd-recent .rad-enrollment-donut{width:min(220px,100%);aspect-ratio:1;border-radius:50%;background:var(--enrollment-gradient);margin:auto;display:grid;place-items:center}
.lebd-recent .rad-enrollment-hole{height:62%;width:62%;border-radius:50%;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:3px;padding:5px}
.lebd-recent .rad-enrollment-hole strong{font-size:24px;color:#183a68;font-weight:900;line-height:1}
.lebd-recent .rad-enrollment-hole span{color:#8294ad;font-size:10px}
.lebd-recent .rad-enrollment-list{list-style:none;margin:0;padding:0;min-width:0}
.lebd-recent .rad-enrollment-list li{display:flex;align-items:center;gap:8px;padding:7px 0;min-width:0;font-size:11px;color:#425e84}
.lebd-recent .rad-enrollment-list .dot{width:9px;height:9px;flex:none;border-radius:50%;background:var(--dot)}
.lebd-recent .rad-enrollment-list .course-name{min-width:0;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lebd-recent .rad-enrollment-list b{flex:none;color:#1b416d;font-size:11px}
.lebd-recent .rad-enrollment-list small{font-size:10px;color:#8aa0b8;min-width:31px;text-align:right}
@media(max-width:1060px){.lebd-recent .rad-growth-grid{grid-template-columns:1fr}}
@media(max-width:450px){.lebd-recent .rad-enrollment-flex{grid-template-columns:1fr;gap:15px}.lebd-recent .rad-growth-card{min-height:300px}.lebd-recent .rad-enrollment-donut{width:min(172px,60vw)}.lebd-recent .rad-growth-body svg{height:200px}}

</style>

<div class="lebd-recent">
  <div class="rad-page-head">
    <div><h1><i class="fa fa-line-chart" style="color:#1477f3"></i> Recent Activity Dashboard</h1><p class="rad-sub">Live Education BD · শিক্ষার্থীদের পেজ ভিজিট, বাটন ক্লিক ও সক্রিয় সময় · শুধুমাত্র Tracking চালুর পরের তথ্য</p></div>
    <div class="rad-date"><i class="fa fa-calendar"></i> {{ $today->format('d M Y') }} · Asia/Dhaka</div>
  </div>
  <form class="rad-filters" method="GET" action="{{ route('admin.recent.activity') }}">
    <div class="rad-filter"><label>তারিখ পরিসর</label><select name="days"><option value="1" @selected($days===1)>Today</option><option value="7" @selected($days===7)>Last 7 Days</option><option value="30" @selected($days===30)>Last 30 Days</option></select></div>
    <div class="rad-filter"><label>Course</label><select name="course"><option value="0">All Courses</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected($courseId==$course->id)>{{ $course->name }}</option>@endforeach</select></div>
    <div class="rad-filter"><label>Batch</label><select name="batch"><option value="0">All Batches</option>@foreach($batches as $batch)<option value="{{ $batch->id }}" @selected($batchId==$batch->id)>{{ $batch->name }}</option>@endforeach</select></div>
    <div class="rad-filter"><label>Department</label><select name="department"><option value="0">All Departments</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected($departmentId==$department->id)>{{ $department->name }}</option>@endforeach</select></div>
    <div class="rad-filter"><label>Device</label><select name="device"><option value="">All Devices</option>@foreach(['Mobile','Desktop','Tablet','Unknown'] as $item)<option value="{{ $item }}" @selected($device===$item)>{{ $item }}</option>@endforeach</select></div>
    <div class="rad-filter"><label>Activity Type</label><select name="type"><option value="">All Activities</option><option value="page_view" @selected($kind==='page_view')>Page Visits</option><option value="click" @selected($kind==='click')>Button Clicks</option></select></div>
    <button class="rad-button" type="submit"><i class="fa fa-filter"></i> Filter</button>
  </form>

  <div class="rad-stats">
    <div class="rad-stat"><span class="icon-box"><i class="fa fa-users"></i></span><div><small>Active Students</small><strong>{{ number_format($stats['students']) }}</strong><em>নির্বাচিত সময়ে ইউনিক</em></div></div>
    <div class="rad-stat"><span class="icon-box"><i class="fa fa-book"></i></span><div><small>Student Activities</small><strong>{{ number_format($stats['events']) }}</strong><em>Page + Click Events</em></div></div>
    <div class="rad-stat"><span class="icon-box"><i class="fa fa-clock-o"></i></span><div><small>Avg Session Time</small><strong>{{ $avgMinutes }}m {{ $avgRemaining }}s</strong><em>আনুমানিক সক্রিয় সময়</em></div></div>
    <div class="rad-stat"><span class="icon-box"><i class="fa fa-eye"></i></span><div><small>Last Seen Students</small><strong>{{ number_format($stats['seen_students']) }}</strong><em>মোট ট্র্যাকড শিক্ষার্থী</em></div></div>
    <div class="rad-stat"><span class="icon-box"><i class="fa fa-bar-chart"></i></span><div><small>Course Visits</small><strong>{{ number_format($stats['course_visits']) }}</strong><em>শনাক্ত করা Course Page</em></div></div>
    <div class="rad-stat"><span class="icon-box"><i class="fa fa-mouse-pointer"></i></span><div><small>Button Clicks</small><strong>{{ number_format($stats['clicks']) }}</strong><em>Button / Link</em></div></div>
  </div>


  {{-- =============================================================
       NEW: STUDENT GROWTH + COURSE-WISE ENROLLMENT
       Real enrollment and registration tables; not sample values.
       Shown near the top; original Activity Cards stay below.
       ============================================================= --}}
  <div class="rad-growth-grid">
    <section class="rad-growth-card" aria-label="Student Growth">
      <div class="rad-growth-header">
        <h2><i class="fa fa-line-chart" style="color:#1979f6"></i> Student Growth</h2>
        <select id="rad-growth-period" aria-label="Student growth period">
          <option value="7" @selected($growthDays===7)>Last 7 Days</option>
          <option value="30" @selected($growthDays===30)>Last 30 Days</option>
          <option value="90" @selected($growthDays===90)>Last 90 Days</option>
        </select>
      </div>
      <div class="rad-growth-body">
        <svg viewBox="0 0 590 253" role="img" aria-label="Cumulative registered students by date">
          <defs>
            <linearGradient id="rad-student-growth-fill" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#b8d8ff" stop-opacity=".72"/>
              <stop offset="100%" stop-color="#e9f4ff" stop-opacity=".30"/>
            </linearGradient>
          </defs>
          @for($tick=0;$tick<=4;$tick++)
            @php $tickY = 212 - $tick * 43; @endphp
            <line x1="54" x2="554" y1="{{ $tickY }}" y2="{{ $tickY }}" class="rad-growth-gridline"/>
            <text class="rad-growth-axis" x="45" y="{{ $tickY + 4 }}" text-anchor="end">{{ number_format((int) round($growthAxisMax * $tick / 4)) }}</text>
          @endfor
          <polygon points="{{ $growthArea }}" fill="url(#rad-student-growth-fill)" />
          <polyline points="{{ $growthPolyline }}" stroke="#1479f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
          @foreach($studentGrowth as $idx=>$row)
            @php
              $gx = 54 + ($idx * 492 / $growthSpan);
              $gy = 212 - ((int) $row['total'] / $growthAxisMax) * 172;
            @endphp
            <circle cx="{{ round($gx,1) }}" cy="{{ round($gy,1) }}" r="{{ $idx===count($studentGrowth)-1 ? 4 : 2.3 }}" fill="#1479f6">
              <title>{{ $row['date'] }}: {{ number_format($row['total']) }} students ({{ $row['registered'] }} new)</title>
            </circle>
            @if($idx%$growthLabelStep===0 || $idx===count($studentGrowth)-1)
              <text class="rad-growth-axis" x="{{ round($gx,1) }}" y="241" text-anchor="middle">{{ $row['label'] }}</text>
            @endif
          @endforeach
        </svg>
      </div>
      <div class="rad-growth-foot">Registered students (cumulative) · Last day: {{ number_format((int) ($studentGrowth[count($studentGrowth)-1]['total'] ?? 0)) }} · Source: students.created_at</div>
    </section>

    <section class="rad-growth-card" aria-label="Course-wise Enrollment">
      <div class="rad-growth-header">
        <h2><i class="fa fa-pie-chart" style="color:#1979f6"></i> Course-wise Enrollment</h2>
        <div style="display:flex;align-items:center;gap:10px">
          <span class="rad-muted">All-time Memberships</span>
          @if($enrollmentAll->count()>5)<button type="button" class="rad-more" data-rad-modal="enrollments">See More →</button>@endif
        </div>
      </div>
      <div class="rad-growth-body rad-enrollment-flex">
        <div class="rad-enrollment-donut" style="--enrollment-gradient:{{ $enrollmentDonut }}">
          <div class="rad-enrollment-hole">
            <strong>{{ number_format($enrollmentTotal) }}</strong>
            <span>Course Enrollments</span>
          </div>
        </div>
        <ul class="rad-enrollment-list">
          @forelse($enrollmentBreakdown as $idx=>$course)
            <li>
              <span class="dot" style="--dot:{{ $enrollmentColors[$idx % count($enrollmentColors)] }}"></span>
              <span class="course-name" title="{{ $course['name'] }}">{{ $course['name'] }}</span>
              <b>{{ number_format($course['count']) }}</b>
              <small>{{ $enrollmentTotal ? round($course['count'] * 100 / $enrollmentTotal) : 0 }}%</small>
            </li>
          @empty
            <li class="rad-muted">এখনো Course Enrollment পাওয়া যায়নি।</li>
          @endforelse
        </ul>
      </div>
      <div class="rad-growth-foot">Each student counted once per course · Direct course and batch memberships · Not page visits</div>
    </section>
  </div>

  <div class="rad-grid">
    <section class="rad-card"><div class="rad-card-head"><h2>সবচেয়ে বেশি ভিজিট করা কোর্স</h2>@if($topCourses->count()>6)<button class="rad-more" data-rad-modal="courses">See More →</button>@endif</div><div class="rad-card-body"><ul class="rad-list">
      @forelse($topCourses->take(6) as $item)<li><span class="rad-name" title="{{ $item->name }}">{{ $item->name }}</span><span class="rad-line-track"><span style="width:{{ round($item->total*100/$maxCourse) }}%"></span></span><b>{{ number_format($item->total) }}</b></li>@empty<li class="rad-muted">কোর্স ভিজিটের তথ্য পাওয়া যায়নি।</li>@endforelse
    </ul></div><div class="rad-card-note">শুধু শনাক্ত করা Course/Batch পেজ ভিজিট</div></section>

    <section class="rad-card"><div class="rad-card-head"><h2>সবচেয়ে ক্লিক হওয়া বাটন / ফিচার</h2>@if($topClicks->count()>6)<button class="rad-more" data-rad-modal="buttons">See More →</button>@endif</div><div class="rad-card-body"><ul class="rad-list">
      @forelse($topClicks->take(6) as $item)<li><span class="rad-icon"><i class="fa fa-mouse-pointer"></i></span><span class="rad-name" title="{{ $item->title }}">{{ $item->title }}</span><b>{{ number_format($item->total) }}</b></li>@empty<li class="rad-muted">কোনো বাটন ক্লিক রেকর্ড হয়নি।</li>@endforelse
    </ul></div><div class="rad-card-note">ক্লিকের সংখ্যা, সফল Action সম্পন্নের সংখ্যা নয়</div></section>

    <section class="rad-card"><div class="rad-card-head"><h2>Activity Type Summary</h2><button class="rad-more" data-rad-modal="types">Details →</button></div><div class="rad-card-body"><div class="rad-donut-grid">
      <div class="rad-donut" style="--gradient:{{ $donut }}"><div class="rad-hole"><strong>{{ number_format($stats['events']) }}</strong><span>Total Events</span></div></div>
      <div class="rad-legend">@foreach($activityTypes as $i=>$item)<div><span class="swatch" style="--c:{{ $typeColors[$i % count($typeColors)] }}"></span><span style="overflow:hidden;text-overflow:ellipsis">{{ $item->title }}</span><b>{{ number_format($item->total) }}</b></div>@endforeach</div>
    </div></div><div class="rad-card-note">Page Type ও Click-এর প্রকৃত অনুপাত</div></section>

    <section class="rad-card"><div class="rad-card-head"><h2>Student Visit Trend</h2><span class="rad-muted">Last {{ $days }} Days</span></div><div class="rad-card-body"><svg class="rad-svg" viewBox="0 0 570 210" role="img" aria-label="Daily unique student visitors line chart">
      @foreach([45,85,125,165] as $y)<line x1="30" y1="{{ $y }}" x2="545" y2="{{ $y }}" stroke="#e4eefb" stroke-width="1" />@endforeach
      <polygon points="{{ $areaPoints }}" fill="#e8f3ff"/><polyline points="{{ $pointString }}" fill="none" stroke="#147bfa" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
      @foreach($trend as $i=>$item)<circle cx="{{ round(30+$i*510/$trendCount,1) }}" cy="{{ round(173-$item['visitors']*140/$maxTrend,1) }}" r="3" fill="#147bfa"><title>{{ $item['date'] }}: {{ $item['visitors'] }} students</title></circle>@endforeach
    </svg><div class="rad-chart-labels"><span>{{ $trend[0]['label'] ?? '' }}</span><span>{{ $trend[count($trend)-1]['label'] ?? '' }}</span></div></div><div class="rad-card-note">প্রতিদিন ইউনিক শিক্ষার্থী · সর্বোচ্চ {{ $maxTrend }}</div></section>
  </div>

  <div class="rad-grid">
    <section class="rad-card"><div class="rad-card-head"><h2>সাম্প্রতিক Student Activity</h2>@if($recent->count()>6)<button class="rad-more" data-rad-modal="recent">See More →</button>@endif</div><div class="rad-card-body">
      @forelse($recent->take(6) as $ev)<div class="rad-event"><span class="rad-icon"><i class="fa fa-{{ $ev->event_type==='click'?'mouse-pointer':'eye' }}"></i></span><div class="rad-event-text"><b>{{ $ev->name }}</b> — {{ $ev->event_type==='click' ? ($ev->target_label ?: 'Button Click') : 'Visited' }} · {{ $ev->path }}</div><time>{{ \Carbon\Carbon::parse($ev->occurred_at)->format('d M H:i') }}</time></div>@empty<p class="rad-muted">Tracking চালুর পরে Activity এখানে আসবে।</p>@endforelse
    </div></section>

    <section class="rad-card"><div class="rad-card-head"><h2>Top Active Students</h2>@if($topStudents->count()>6)<button class="rad-more" data-rad-modal="students">See More →</button>@endif</div><div class="rad-card-body"><ul class="rad-list">
      @forelse($topStudents->take(6) as $i=>$item)<li><span class="rad-rank {{ $i===1?'r2':($i===2?'r3':($i===3?'r4':($i===4?'r5':''))) }}">{{ $i+1 }}</span><span class="rad-name"><a href="{{ route('admin.recent.activity.student',['studentId'=>$item->id]) }}">{{ $item->name }}</a></span><b>{{ $item->total }}</b></li>@empty<li class="rad-muted">এখনো শিক্ষার্থীর Activity নেই।</li>@endforelse
    </ul></div><div class="rad-card-note">সংরক্ষিত Page ও Click Event অনুযায়ী</div></section>

    <section class="rad-card"><div class="rad-card-head"><h2>Popular Actions (Pages)</h2>@if($popularPages->count()>6)<button class="rad-more" data-rad-modal="pages">See More →</button>@endif</div><div class="rad-card-body"><ul class="rad-list">
      @forelse($popularPages->take(6) as $item)<li><span class="rad-icon"><i class="fa fa-file-text-o"></i></span><span class="rad-name" title="{{ $item->path }}">{{ $item->path }}</span><span class="rad-line-track"><span style="width:{{ round($item->total*100/$maxPopular) }}%"></span></span><b>{{ $item->total }}</b></li>@empty<li class="rad-muted">এখনো Page Visit পাওয়া যায়নি।</li>@endforelse
    </ul></div></section>

    <section class="rad-card"><div class="rad-card-head"><h2>Student Journey (Funnel)</h2><button class="rad-more" data-rad-modal="funnel">Details →</button></div><div class="rad-card-body"><div class="rad-funnel">
      @foreach($funnel as $i=>$step)<div class="rad-funnel-row"><div class="rad-funnel-symbol"><i class="fa fa-{{ ['home','book','file-text','play','check'][$i] }}"></i></div><div class="rad-funnel-label">{{ $step['name'] }}<strong>{{ $step['count']===null?'N/A':number_format($step['count']) }}</strong></div><div class="rad-funnel-bar"><span style="width:{{ $step['count']===null?0:round($step['count']*100/$maxFunnel) }}%"></span></div></div>@endforeach
    </div></div><div class="rad-card-note">ধাপগুলো আলাদা ইউনিক Count; একই যাত্রাপথের Cohort নয়</div></section>
  </div>

  <section class="rad-bottom">
    <div class="rad-bottom-head"><div><h2>Student Tracking Log — সকল শিক্ষার্থীর Activity History</h2><p class="rad-sub">সর্বশেষ আসা শিক্ষার্থী আগে · প্রথমে ১০ জন · See More-এ পরের পেজ</p></div><div style="display:flex;gap:8px;flex-wrap:wrap"><a class="rad-button subtle" href="{{ route('admin.recent.activity.export') }}"><i class="fa fa-download"></i> Export Last 30 Days CSV</a></div></div>
    <div class="rad-tablewrap"><table><thead><tr><th>#</th><th>Student</th><th>Student ID</th><th>Activity Count</th><th>Page Views</th><th>Clicks</th><th>Active Time (All)</th><th>Last Seen</th><th>History</th></tr></thead><tbody>
      @forelse($studentRows as $i=>$st)
      <tr><td>{{ $studentRows->firstItem()+$i }}</td><td><a href="{{ route('admin.recent.activity.student',['studentId'=>$st->id]) }}">{{ $st->name }}</a></td><td>{{ $st->reg_id ?: '#'.$st->id }}</td><td>{{ $st->activity_count }}</td><td>{{ $st->pages }}</td><td>{{ $st->clicks }}</td><td>{{ gmdate('H:i:s',(int)$st->active_seconds) }}</td><td>{{ $st->last_seen }}</td><td><a href="{{ route('admin.recent.activity.student',['studentId'=>$st->id]) }}">Details →</a></td></tr>
      @empty<tr><td colspan="9" class="rad-muted" style="text-align:center;padding:23px">Tracking চালু হওয়ার পর শিক্ষার্থীর ভিজিট এখানে দেখা যাবে।</td></tr>@endforelse
    </tbody></table></div>
    <div class="rad-pager"><span>Showing {{ $studentRows->firstItem() ?: 0 }}–{{ $studentRows->lastItem() ?: 0 }} of {{ $studentRows->total() }} tracked students</span><nav>@if($studentRows->previousPageUrl())<a href="{{ $studentRows->previousPageUrl() }}">← Previous</a>@endif @if($studentRows->nextPageUrl())<a href="{{ $studentRows->nextPageUrl() }}">See More →</a>@endif</nav></div>
  </section>
  <p class="rad-muted">তথ্যসূত্র: student_activity_events / sessions / days এবং সংশ্লিষ্ট Course ও Batch। Tracking চালুর আগের তথ্য নেই। Active Time আনুমানিক; এ রিপোর্ট Browser Online Presence-এর নিশ্চিত প্রমাণ নয়।</p>

  {{-- See More: show the full DB-backed top lists without changing the card height. --}}
  <div class="rad-modal" id="rad-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="View more activity"><div class="rad-dialog"><div class="rad-dialog-head"><h3 id="rad-modal-title"></h3><button class="rad-close" type="button" id="rad-close" aria-label="Close">×</button></div><div class="rad-dialog-body" id="rad-modal-body"></div><div class="rad-dialog-foot"><button class="rad-button" type="button" id="rad-close-bottom">Close</button></div></div></div>
  <div id="rad-full-lists" class="rad-hidden">
    <section data-rad-source="enrollments" data-rad-title="All Course Enrollments"><ul class="rad-list">@forelse($enrollmentAll as $item)<li><span class="rad-name">{{ $item->name }}</span><b>{{ number_format($item->enrollment_count) }} students</b></li>@empty<li>এখনো কোনো Course Enrollment নেই।</li>@endforelse</ul><p class="rad-muted">একই Course-এ একই শিক্ষার্থী একবার গণনা করা হয়েছে।</p></section>
    <section data-rad-source="courses" data-rad-title="Most Visited Courses"><ul class="rad-list">@forelse($topCourses as $item)<li><span class="rad-name">{{ $item->name }}</span><b>{{ $item->total }} views</b></li>@empty<li>No course visits yet.</li>@endforelse</ul></section>
    <section data-rad-source="buttons" data-rad-title="Most Clicked Buttons"><ul class="rad-list">@forelse($topClicks as $item)<li><span class="rad-name">{{ $item->title }}</span><b>{{ $item->total }} clicks</b></li>@empty<li>No clicks yet.</li>@endforelse</ul></section>
    <section data-rad-source="types" data-rad-title="Activity Type Summary"><ul class="rad-list">@forelse($activityTypes as $item)<li><span class="rad-name">{{ $item->title }}</span><b>{{ $item->total }}</b></li>@empty<li>No tracked events yet.</li>@endforelse</ul></section>
    <section data-rad-source="recent" data-rad-title="Recent Student Activity">@forelse($recent as $ev)<div class="rad-event"><div class="rad-event-text"><b><a href="{{ route('admin.recent.activity.student',['studentId'=>$ev->student_id]) }}">{{ $ev->name }}</a></b> — {{ $ev->event_type }} · {{ $ev->target_label ?: $ev->path }}<div class="rad-muted">{{ $ev->occurred_at }} · {{ $ev->path }}</div></div></div>@empty<p>No recent student activity.</p>@endforelse</section>
    <section data-rad-source="students" data-rad-title="Top Active Students"><ul class="rad-list">@forelse($topStudents as $i=>$item)<li><span class="rad-rank">{{ $i+1 }}</span><span class="rad-name"><a href="{{ route('admin.recent.activity.student',['studentId'=>$item->id]) }}">{{ $item->name }}</a></span><b>{{ $item->total }} activities</b></li>@empty<li>No tracked students.</li>@endforelse</ul></section>
    <section data-rad-source="pages" data-rad-title="Popular Pages"><ul class="rad-list">@forelse($popularPages as $item)<li><span class="rad-name">{{ $item->path }}</span><b>{{ $item->total }}</b></li>@empty<li>No page views.</li>@endforelse</ul></section>
    <section data-rad-source="funnel" data-rad-title="Student Journey — Distinct Visitors"><ul class="rad-list">@foreach($funnel as $item)<li><span class="rad-name">{{ $item['name'] }}</span><b>{{ $item['count']===null?'N/A':$item['count'] }}</b></li>@endforeach</ul><p class="rad-muted">প্রতিটি ধাপ আলাদা ইউনিক শিক্ষার্থীর সংখ্যা। একে নিশ্চিত Conversion Rate হিসেবে ব্যাখ্যা করবেন না।</p></section>
  </div>
</div>
<script>
(function(){
    const root=document.querySelector('.lebd-recent');
    if(!root)return;
    const growthPeriod=root.querySelector('#rad-growth-period');
    if(growthPeriod)growthPeriod.addEventListener('change',function(){const url=new URL(window.location.href);url.searchParams.set('growth_days',this.value);window.location.assign(url.toString());});
    const modal=root.querySelector('#rad-modal');
    const body=root.querySelector('#rad-modal-body');
    const title=root.querySelector('#rad-modal-title');
    const close=()=>{modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');body.replaceChildren();};
    root.querySelectorAll('[data-rad-modal]').forEach(btn=>btn.addEventListener('click',()=>{
        const id=btn.getAttribute('data-rad-modal');
        const source=root.querySelector('[data-rad-source="'+id+'"]');
        if(!source)return;
        title.textContent=source.getAttribute('data-rad-title') || 'Details';
        body.replaceChildren(...Array.from(source.children).map(node=>node.cloneNode(true)));
        modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');
        root.querySelector('#rad-close').focus();
    }));
    root.querySelector('#rad-close').addEventListener('click',close);
    root.querySelector('#rad-close-bottom').addEventListener('click',close);
    modal.addEventListener('click',ev=>{if(ev.target===modal)close();});
    document.addEventListener('keydown',ev=>{if(ev.key==='Escape'&&modal.classList.contains('is-open'))close();});
})();
</script>
@endsection
