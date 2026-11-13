@php
    $pageName = $sub?->name ?? $cat->name;
    $now = now('Asia/Dhaka');
    $examRows = collect($exams)->map(function ($e) use ($now) {
        $start = $e->exam_start_at ? \Carbon\Carbon::parse($e->exam_start_at, 'Asia/Dhaka') : null;
        $end = $e->exam_end_at ? \Carbon\Carbon::parse($e->exam_end_at, 'Asia/Dhaka') : null;
        $live = $start && $end && $now->gte($start) && $now->lt($end);
        $ended = $end && $now->gte($end);
        return ['exam'=>$e, 'start'=>$start, 'end'=>$end, 'live'=>(bool)$live, 'ended'=>(bool)$ended];
    });
    $liveRows = $examRows->filter(fn($r)=>$r['live']);
    $nextRow = $examRows->filter(fn($r)=> !$r['ended'] && !$r['live'] && $r['start'])->sortBy(fn($r)=>$r['start']->timestamp)->first();
    $featured = $liveRows->first() ?? $nextRow;
    $finished = $examRows->filter(fn($r)=>$r['ended'])->count();
    $total = $examRows->count();
    $progress = $total ? round($finished * 100 / $total) : 0;
    // Display-only flags; server-side authorization remains in StudentRoutineExamController.
    $routineIds = collect($exams)->pluck('routine_id')->unique()->values()->all();
    $routineLastEnds = $routineIds ? \Illuminate\Support\Facades\DB::table('lebd_exam_section_routine_items')
        ->whereIn('routine_id', $routineIds)
        ->select('routine_id', \Illuminate\Support\Facades\DB::raw('MAX(exam_end_at) AS last_end'))
        ->groupBy('routine_id')->pluck('last_end','routine_id') : collect();
    $currentStudent = \Illuminate\Support\Facades\Auth::guard('student')->user();
    $attemptStatuses = $currentStudent ? \Illuminate\Support\Facades\DB::table('lebd_exam_section_student_attempts')
        ->where('student_id', $currentStudent->id)
        ->whereIn('routine_item_id',collect($exams)->pluck('id')->all())
        ->pluck('status', 'routine_item_id') : collect();
    // Respect the same student_view_result setting used by the existing result endpoint.
    // Settings may differ by category/subcategory; this view already scopes both.
    // Admin ExamSectionController saves Shared Paper Settings by exact scope_key.
    $paperScopeKey = $sub ? 'sub:' . $sub->id : 'cat:' . $cat->id;
    $paperSettingsRow = \Illuminate\Support\Facades\DB::table('lebd_exam_section_paper_settings')
        ->where('scope_key', $paperScopeKey)->first();
    $paperSettings = $paperSettingsRow ? (json_decode($paperSettingsRow->settings_json, true) ?: []) : [];
    $canViewPaperResult = in_array(strtolower((string)($paperSettings['student_view_result'] ?? 'no')), ['yes','1','true'], true);
    // Display only. exam_minutes is the authoritative Admin field (no hardcoded 40 text).
    $lebd05RunningMinutes = max(1, min(1440, (int)($paperSettings['exam_minutes'] ?? $paperSettings['running_minutes'] ?? $paperSettings['exam_running_time'] ?? 40)));
    // Display-only: show the actual saved routine name for the featured exam.
    $lebd05FeaturedRoutineName = null;
    if ($featured && !empty($featured['exam']->routine_id)) {
        $lebd05FeaturedRoutineName = \Illuminate\Support\Facades\DB::table('lebd_exam_section_routines')
            ->where('id', $featured['exam']->routine_id)
            ->where('category_id', $cat->id)
            ->where('subcategory_id', $sub?->id)
            ->value('name');
    }
    $back = $sub ? route('student.exam-section.browse-category', ['category'=>$cat->id]) : route('student.exam-section.categories');
@endphp
@extends('student')
@section('title', '{{ $pageName }} — Exam Section')
@section('stylesheets')
<style>

*{box-sizing:border-box}.lebd-exam-scope{background:#edf2fa;color:#17253b;font-family:system-ui,-apple-system,'Segoe UI','Noto Sans Bengali',sans-serif}.lebd-exam-scope a{text-decoration:none;color:inherit}.lebd-exam-scope button{font:inherit} .se26{max-width:1020px;margin:auto;padding:0 15px 50px}.se26-header{background:#fff;border-bottom:1px solid #e2e8f1}.se26-head-inner{max-width:1020px;margin:auto;padding:13px 17px;display:flex;justify-content:space-between;align-items:center;gap:10px}.se26-brand{color:#1870c6;font-weight:800;font-size:19px}.se26-back{font-size:13px;background:#edf3fc;color:#1457a4;padding:9px 12px;border-radius:9px}.se26-title{text-align:center;margin:34px 0 25px}.se26-title h1{font-size:clamp(23px,4vw,32px);margin:0 0 7px;font-weight:850}.se26-title p{color:#6c7b8f;margin:0;font-size:14px}.se26-notice{padding:17px 20px;border-radius:17px;background:#ffe391;color:#5d4810;font-size:16px;text-align:center;margin-bottom:20px;font-weight:700}.se26-notice.live{background:#d5f4e5;color:#07563a}.se26-panel{background:#fff;border:1px solid #e3eaf3;border-radius:22px;padding:24px;margin-bottom:25px;box-shadow:0 8px 26px #2039500b}.se26-feature-head{display:flex;justify-content:space-between;gap:10px;align-items:center}.se26-feature-head h2{font-size:22px;margin:0}.se26-icon{display:inline-flex;align-items:center;justify-content:center;background:#e6f3ff;color:#0b6fcc;border-radius:13px;width:60px;height:60px;font-size:27px}.se26-state{border-radius:20px;background:#eaf1fa;color:#4176a8;padding:7px 12px;font-size:12px;font-weight:750}.se26-state.live{background:#d9fae7;color:#08754a}.se26-feature-name{font-weight:800;font-size:19px;margin:15px 0 7px}.se26-muted{color:#77859a;line-height:1.8}.se26-feature-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:22px}.se26-btn{display:flex;align-items:center;justify-content:center;gap:8px;border-radius:13px;padding:14px 11px;border:1px solid #cde9db;background:#e4f9ec;color:#087e52;font-weight:800;font-size:14px;min-height:50px}.se26-btn.muted{background:#fff6e7;border-color:#fae8c7;color:#a98050}.se26-btn.disabled{cursor:not-allowed;opacity:.65}.se26-heading{font-size:24px;margin:29px 0 18px}.se26-menu{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.se26-menu-item{background:#fff;border-radius:20px;padding:17px 14px;display:flex;gap:12px;align-items:center;min-height:101px;font-weight:800;box-shadow:0 6px 21px #24416a08}.se26-menu-item span:first-child{display:flex;align-items:center;justify-content:center;background:#eaf3ff;color:#0f76c9;width:48px;height:48px;flex:none;border-radius:13px;font-size:22px}.se26-menu-item small{font-weight:500;color:#94a0ac;display:block;margin-top:3px}.se26-progress{margin-top:27px}.se26-progress-grid{display:grid;grid-template-columns:165px 1fr;gap:22px;align-items:center}.se26-ring{width:160px;height:160px;border-radius:100%;display:grid;place-items:center;background:conic-gradient(#1174d1 {{ $progress }}%,#e8eff8 0)}.se26-ring-inner{width:126px;height:126px;background:#fff;border-radius:100%;display:flex;flex-direction:column;align-items:center;justify-content:center}.se26-ring-inner strong{font-size:32px;color:#147bd7}.se26-progress-row{display:flex;justify-content:space-between;gap:10px;padding:12px 0;border-bottom:1px solid #dfe6f0;font-size:16px}.se26-progress-row b{color:#1678d1}.se26-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.se26-exam{background:#fff;border:1px solid #dbe5f2;border-radius:15px;padding:15px;position:relative;box-shadow:0 4px 15px #213b5408}.se26-exam h3{font-size:16px;margin:0 0 8px}.se26-exam p{color:#586b83;font-size:12px;margin:4px 0}.se26-exam-footer{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:12px;flex-wrap:wrap}.se26-dot{display:inline-block;width:9px;height:9px;border-radius:50%;background:#ff2861;box-shadow:0 0 0 4px #ff286120;animation:se26blink 1.15s infinite;margin-left:6px}.se26-more{color:#0a70c3;font-weight:750;font-size:12px}.se26-empty{padding:17px;color:#72829a;background:#fff;border-radius:12px}@keyframes se26blink{50%{opacity:.2;box-shadow:0 0 0 7px #ff28610d}}@media(max-width:560px){.se26{padding:0 13px 35px}.se26-panel{padding:17px;border-radius:19px}.se26-feature-head h2{font-size:19px}.se26-menu{gap:11px}.se26-menu-item{padding:12px;min-height:95px;font-size:13px;gap:8px}.se26-menu-item span:first-child{width:42px;height:42px;font-size:19px}.se26-progress-grid{grid-template-columns:105px 1fr;gap:15px}.se26-ring{height:105px;width:105px}.se26-ring-inner{width:80px;height:80px}.se26-ring-inner strong{font-size:23px}.se26-progress-row{font-size:13px;padding:8px 0}.se26-list{grid-template-columns:1fr}}@media(prefers-reduced-motion:reduce){.se26-dot{animation:none}}


/* LEBD05: status + package CTA share the same content-width row. */
.lebd-exam-scope .se26-status-package-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px;align-items:stretch;margin:0 0 20px}
.lebd-exam-scope .se26-status-package-row .se26-notice{margin:0;display:flex;align-items:center;justify-content:center;min-width:0;padding:14px 15px;border-radius:14px;font-size:15px;line-height:1.5;text-align:center}
.lebd-exam-scope .se26-package-slot{display:flex;align-items:center;justify-content:center;min-width:0;padding:7px 12px;border:1px solid #fbd4bf;background:#fff4ed;border-radius:14px}
.lebd-exam-scope a.se26-package-link{display:inline-flex;align-items:center;justify-content:center;gap:8px;max-width:100%;min-height:39px;padding:9px 20px;border-radius:999px;background:linear-gradient(115deg,#ff7a13,#ff4239);color:#fff!important;font-size:13px;font-weight:800;line-height:1.3;text-decoration:none;box-shadow:0 5px 13px rgba(235,81,31,.20);white-space:nowrap}
.lebd-exam-scope a.se26-package-link:hover,.lebd-exam-scope a.se26-package-link:focus{box-shadow:0 6px 17px rgba(235,81,31,.28);filter:brightness(.98)}
@media(max-width:600px){.lebd-exam-scope .se26-status-package-row{grid-template-columns:minmax(0,1.35fr) minmax(0,1fr);gap:6px}.lebd-exam-scope .se26-status-package-row .se26-notice{padding:8px 5px;font-size:11px;line-height:1.3;border-radius:10px}.lebd-exam-scope .se26-package-slot{padding:4px;border-radius:10px}.lebd-exam-scope a.se26-package-link{width:100%;min-height:34px;padding:6px 4px;gap:3px;font-size:11px;white-space:nowrap}}
/* LEBD05 scoped UI refinement: larger back control and single exam action. */
.lebd-exam-scope a.se26-back{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:12px 20px;font-size:15px;font-weight:750;border:1px solid #d7e5f9;border-radius:10px;line-height:1.3}
.lebd-exam-scope .se26-feature-actions{grid-template-columns:minmax(0,1fr)}
@media(max-width:600px){.lebd-exam-scope a.se26-back{min-height:44px;padding:11px 17px;font-size:14px}}

/* LEBD-05: dedicated, clearly visible exam-page return control on mobile and desktop. */
.lebd-exam-scope .se26 .lebd05-exam-return{display:flex!important;align-items:center!important;justify-content:flex-start!important;width:100%!important;margin:16px 0 8px!important;position:relative!important;z-index:1!important}
.lebd-exam-scope .se26 .lebd05-exam-return-link{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:8px!important;min-height:48px!important;min-width:145px!important;padding:11px 20px!important;border:1px solid #b5cff9!important;border-radius:12px!important;background:#e8f1ff!important;color:#0b4fa8!important;font-size:16px!important;font-weight:800!important;line-height:1.4!important;text-decoration:none!important;visibility:visible!important;opacity:1!important;box-shadow:0 3px 10px rgba(19,86,170,.10)!important}
.lebd-exam-scope .se26 .lebd05-exam-return-link:hover,.lebd-exam-scope .se26 .lebd05-exam-return-link:focus-visible{background:#d9eaff!important;outline:2px solid #3b82f6;outline-offset:2px}
@media(max-width:600px){.lebd-exam-scope .se26 .lebd05-exam-return{margin:13px 0 12px!important}.lebd-exam-scope .se26 .lebd05-exam-return-link{min-height:48px!important;min-width:145px!important;padding:11px 17px!important;font-size:15px!important}}

/* LEBD05 exam browse only: hide the dark global layout footer strip on this page. */
body:has(.lebd-exam-scope) > footer,
body:has(.lebd-exam-scope) .main-footer,
body:has(.lebd-exam-scope) .footer-area,
body:has(.lebd-exam-scope) .footer-section,
body:has(.lebd-exam-scope) #footer { display:none !important; }
/* The All Exam Paper menu tile reveals its own exam list; no list at page bottom. */
.se26-paper-details{min-width:0;align-self:start}
.se26-paper-details > summary{list-style:none;cursor:pointer}
.se26-paper-details > summary::-webkit-details-marker{display:none}
.se26-paper-details[open] > summary{border-radius:20px 20px 12px 12px}
.se26-paper-details > summary:focus-visible{outline:3px solid #1378d4;outline-offset:2px}
.se26-paper-details .se26-paper-dropdown{margin-top:9px;border-radius:14px;background:#fff;padding:12px;border:1px solid #dbe5f2;box-shadow:0 5px 18px #213b5410}
.se26-paper-details .se26-paper-dropdown h3{font-size:15px;margin:0 0 10px;color:#183b60}
.se26-paper-details .se26-list{grid-template-columns:1fr}
.se26-paper-details .se26-exam{padding:11px}
.se26-paper-details .se26-exam h3{font-size:14px}
@media(max-width:560px){.se26-paper-details .se26-paper-dropdown{padding:8px}.se26-paper-details .se26-exam{padding:9px}}

/* LEBD05 standalone All Exam Paper view. No shared global CSS altered. */
.lebd-exam-scope .lebd-paper-page{max-width:1120px;margin:0 auto;padding:18px 16px 55px}
.lebd-exam-scope .lebd-paper-nav{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
.lebd-exam-scope .lebd-paper-back{display:inline-flex;align-items:center;min-height:46px;padding:10px 18px;border-radius:13px;background:#fff;color:#126bd0;font-weight:800;box-shadow:0 3px 12px #17345612}
.lebd-exam-scope .lebd-paper-title{text-align:center;margin:14px 0 24px}
.lebd-exam-scope .lebd-paper-title h1{font-size:clamp(25px,4vw,37px);margin:0 0 6px;color:#12325c;font-weight:850}
.lebd-exam-scope .lebd-paper-title p{margin:0;color:#6383ac;font-size:14px}
.lebd-exam-scope .lebd-paper-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
.lebd-exam-scope .lebd-paper-stat{border:1px solid #d8e5f5;border-radius:16px;padding:17px;display:flex;align-items:center;gap:12px;background:#fff;min-width:0}
.lebd-exam-scope .lebd-paper-stat:nth-child(2){background:#f0fbf6}.lebd-exam-scope .lebd-paper-stat:nth-child(3){background:#fff9ed}.lebd-exam-scope .lebd-paper-stat:nth-child(4){background:#f1f6ff}
.lebd-exam-scope .lebd-paper-stat-icon{flex:none;font-size:23px;border-radius:12px;background:#e8f3ff;padding:10px}
.lebd-exam-scope .lebd-paper-stat small{display:block;color:#506582;font-size:12px}.lebd-exam-scope .lebd-paper-stat strong{font-size:24px;color:#15345a}
.lebd-exam-scope .lebd-paper-card{background:white;border:1px solid #cfe4f1;border-left:5px solid #08a8ba;border-radius:17px;padding:18px;margin:12px 0;box-shadow:0 4px 16px #1d47700a}
.lebd-exam-scope .lebd-paper-card:nth-child(3n+2){border-left-color:#ffb92d}.lebd-exam-scope .lebd-paper-card:nth-child(3n){border-left-color:#10b981}
.lebd-exam-scope .lebd-paper-main{display:grid;grid-template-columns:65px minmax(105px,1.25fr) minmax(130px,1.4fr) minmax(130px,1.4fr) minmax(115px,.9fr) minmax(140px,1fr);gap:12px;align-items:center}
.lebd-exam-scope .lebd-paper-number{border-radius:12px;background:#0fa5b4;color:white;font-size:25px;font-weight:850;display:grid;place-items:center;min-height:62px}
.lebd-exam-scope .lebd-paper-name{font-weight:800;color:#112b4d}.lebd-exam-scope .lebd-paper-name small{display:block;font-size:11px;font-weight:650;color:#1675ce;margin-bottom:5px}
.lebd-exam-scope .lebd-paper-time small{display:block;color:#72849a;font-size:12px}.lebd-exam-scope .lebd-paper-time strong{font-size:13px;line-height:1.5}
.lebd-exam-scope .lebd-paper-status{display:inline-block;padding:6px 10px;border-radius:9px;font-weight:800;font-size:12px;background:#e9f0f9;color:#55738e}.lebd-exam-scope .lebd-paper-status.live{background:#d9f8e7;color:#067847}.lebd-exam-scope .lebd-paper-status.ended{background:#e9eef4;color:#56667b}
.lebd-exam-scope .lebd-paper-action{display:flex;align-items:center;justify-content:center;min-height:43px;border-radius:10px;background:#1372e9;color:#fff!important;font-size:13px;font-weight:800;padding:9px;text-align:center}
.lebd-exam-scope .lebd-paper-action.off{background:#f0f4fa;color:#64758b!important}
.lebd-exam-scope .lebd-paper-more{margin-top:13px;padding-top:10px;border-top:1px dashed #d3e3ee;font-size:12px;color:#437aaf}
@media(max-width:850px){.lebd-exam-scope .lebd-paper-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-exam-scope .lebd-paper-main{grid-template-columns:54px 1fr 1fr;gap:10px}.lebd-exam-scope .lebd-paper-status-box,.lebd-exam-scope .lebd-paper-action-box{grid-column:auto}.lebd-exam-scope .lebd-paper-number{min-height:54px}}
@media(max-width:520px){.lebd-exam-scope .lebd-paper-page{padding:12px 10px 35px}.lebd-exam-scope .lebd-paper-stat{padding:12px 9px;gap:7px}.lebd-exam-scope .lebd-paper-stat-icon{font-size:18px;padding:8px}.lebd-exam-scope .lebd-paper-stat strong{font-size:21px}.lebd-exam-scope .lebd-paper-card{padding:12px}.lebd-exam-scope .lebd-paper-main{grid-template-columns:48px minmax(0,1fr) minmax(0,1fr)}.lebd-exam-scope .lebd-paper-name{grid-column:2/4}.lebd-exam-scope .lebd-paper-time{grid-column:span 1}.lebd-exam-scope .lebd-paper-status-box{grid-column:1/3}.lebd-exam-scope .lebd-paper-action-box{grid-column:3/4}.lebd-exam-scope .lebd-paper-title h1{font-size:26px}}

/* LEBD05: Mobile-only All Exam Paper layout: date/time on right, like reference #1.
   No Blade condition, routes, attempt, or deadline logic changes. */
@media (max-width:520px){
  .lebd-exam-scope .lebd-paper-card{padding:13px 12px;border-radius:13px;margin:10px 0}
  .lebd-exam-scope .lebd-paper-main{
    display:grid;
    grid-template-columns:50px minmax(0,1fr) minmax(0,1fr);
    column-gap:8px;
    row-gap:8px;
    align-items:start;
  }
  .lebd-exam-scope .lebd-paper-number{grid-column:1;grid-row:1;min-height:46px;font-size:20px;border-radius:9px}
  .lebd-exam-scope .lebd-paper-name{grid-column:2;grid-row:1;font-size:13px;min-width:0;align-self:center;overflow-wrap:anywhere}
  .lebd-exam-scope .lebd-paper-name small{margin-bottom:3px;font-size:10px}
  .lebd-exam-scope .lebd-paper-time{min-width:0;align-self:start}
  .lebd-exam-scope .lebd-paper-time:nth-child(3){grid-column:3;grid-row:1}
  .lebd-exam-scope .lebd-paper-time:nth-child(4){grid-column:3;grid-row:2}
  .lebd-exam-scope .lebd-paper-time small{display:block;font-size:10px;line-height:1.3;margin-bottom:3px}
  .lebd-exam-scope .lebd-paper-time strong{display:block;font-size:11px;line-height:1.45;overflow-wrap:break-word}
  .lebd-exam-scope .lebd-paper-status-box{grid-column:1 / 3;grid-row:2;min-width:0;align-self:center}
  .lebd-exam-scope .lebd-paper-action-box{grid-column:3;grid-row:3;min-width:0;align-self:stretch}
  .lebd-exam-scope .lebd-paper-action{min-height:39px;font-size:11px;padding:7px 6px;line-height:1.35}
  .lebd-exam-scope .lebd-paper-more{margin-top:11px;padding-top:9px;font-size:11px}
}
/* LEBD-05: consistent reference-1 colors throughout All Exam Paper, desktop + mobile. */
.lebd-exam-scope .lebd-paper-page{background:transparent;color:#17253b}
.lebd-exam-scope .lebd-paper-card{background:#fff;border-top-color:#cfe4f1;border-right-color:#cfe4f1;border-bottom-color:#cfe4f1}
.lebd-exam-scope .lebd-paper-number{background:#0fa5b4;color:#fff}
.lebd-exam-scope .lebd-paper-status.live{background:#d9f8e7;color:#067847}
.lebd-exam-scope .lebd-paper-status.ended{background:#daf7e7;color:#087d4c}
.lebd-exam-scope .lebd-paper-action:not(.off){background:#1372e9;color:#fff}
.lebd-exam-scope .lebd-paper-action.off{background:#f0f4fa;color:#64758b}
.lebd-exam-scope .lebd-paper-action-box .lebd-paper-action{white-space:normal;overflow-wrap:anywhere}
/* LEBD05: Keep the blue result CTA visible after an exam ends; connection pending. */
.lebd-exam-scope .lebd-paper-result-preview{background:#1372e9!important;color:#fff!important;opacity:1!important;cursor:default}

/* LEBD-05 screenshot-20 fidelity: applies ONLY to All Exam Paper page. */
.lebd-exam-scope .lebd-paper-page{max-width:1400px}
.lebd-exam-scope .lebd-paper-stats{gap:12px;margin-bottom:14px}
.lebd-exam-scope .lebd-paper-stat{min-height:82px;padding:14px 16px;border-radius:15px;gap:13px}
.lebd-exam-scope .lebd-paper-stat-icon{display:grid;place-items:center;width:51px;height:51px;padding:0;border-radius:14px;color:#076fe3;font-size:24px;font-weight:850;background:#e7f2ff}
.lebd-exam-scope .lebd-paper-stat:nth-child(2) .lebd-paper-stat-icon{background:#d8f8e7;color:#008e4c}
.lebd-exam-scope .lebd-paper-stat:nth-child(3) .lebd-paper-stat-icon{background:#ffefc9;color:#e99800}
.lebd-exam-scope .lebd-paper-stat:nth-child(4) .lebd-paper-stat-icon{background:#e2edff;color:#096ce0}
.lebd-exam-scope .lebd-paper-stat small{font-size:13px;color:#123257;font-weight:700}
.lebd-exam-scope .lebd-paper-stat strong{font-size:24px;line-height:1.15}
.lebd-exam-scope .lebd-paper-card{border-left-width:4px;padding:14px 17px 12px;margin:11px 0;border-radius:15px;background:#f8fdff;box-shadow:none}
.lebd-exam-scope .lebd-paper-card:nth-of-type(3n+2){background:#fffcf7;border-color:#ffdda1;border-left-color:#ffa61e}
.lebd-exam-scope .lebd-paper-card:nth-of-type(3n){background:#f5fff9;border-color:#b6efd2;border-left-color:#0bb981}
.lebd-exam-scope .lebd-paper-main{grid-template-columns:50px minmax(110px,1.2fr) minmax(140px,1fr) minmax(155px,1fr) minmax(120px,.95fr) minmax(180px,1fr);column-gap:12px;row-gap:9px;align-items:center}
.lebd-exam-scope .lebd-paper-number{width:50px;min-height:57px;font-size:24px;border-radius:11px;background:#0ea5b6}
.lebd-exam-scope .lebd-paper-card:nth-of-type(3n+2) .lebd-paper-number{background:#ffa61b}
.lebd-exam-scope .lebd-paper-card:nth-of-type(3n) .lebd-paper-number{background:#08ac78}
.lebd-exam-scope .lebd-paper-name{font-size:16px;color:#071e40}
.lebd-exam-scope .lebd-paper-name small{display:table;background:#e8f1ff;color:#0972e4;padding:4px 9px;border-radius:7px;font-size:11px;font-weight:800;margin-bottom:3px}
.lebd-exam-scope .lebd-paper-time{display:flex;align-items:center;gap:11px;min-width:0}
.lebd-exam-scope .lebd-paper-time-icon{flex:none;display:grid;place-items:center;width:35px;height:35px;border-radius:50%;background:#e9f3ff;color:#0878fa;font-size:17px}
.lebd-exam-scope .lebd-paper-time-icon.clock{background:#f0e8ff;color:#7939ed}
.lebd-exam-scope .lebd-paper-time small{font-size:12px;color:#607a9b;line-height:1.3}
.lebd-exam-scope .lebd-paper-time strong{font-size:12px;color:#071d39;line-height:1.55}
.lebd-exam-scope .lebd-paper-status{border-radius:9px;padding:7px 11px;font-size:12px;font-weight:850;background:#e9f0fa;color:#527195}
.lebd-exam-scope .lebd-paper-status.ended{background:#ddf8e9;color:#008c52}
.lebd-exam-scope .lebd-paper-status.live{background:#ddf8e9;color:#008c52}
.lebd-exam-scope .lebd-paper-status-caption{display:block;font-size:11px;color:#6280a3;margin-top:4px;line-height:1.4}
.lebd-exam-scope .lebd-paper-action{background:#0c70eb!important;min-height:44px;border-radius:10px;font-size:13px}
.lebd-exam-scope .lebd-paper-action.off{background:#e6eaf0!important;color:#8090a6!important}
.lebd-exam-scope .lebd-paper-more{border-top:1px dashed #cbdff5;margin-top:13px;padding-top:10px;color:#0073ef;font-weight:800;font-size:12px}
.lebd-exam-scope .lebd-paper-more summary{list-style:none;cursor:pointer;display:inline-block}
.lebd-exam-scope .lebd-paper-more summary::-webkit-details-marker{display:none}
.lebd-exam-scope .lebd-paper-more .lebd-paper-detail{margin-top:8px;color:#536b87;font-weight:500}
@media(max-width:900px){.lebd-exam-scope .lebd-paper-main{grid-template-columns:50px minmax(0,1fr) minmax(0,1fr);gap:9px}.lebd-exam-scope .lebd-paper-name{grid-column:2;grid-row:1}.lebd-exam-scope .lebd-paper-time.date{grid-column:3;grid-row:1}.lebd-exam-scope .lebd-paper-time.time{grid-column:3;grid-row:2}.lebd-exam-scope .lebd-paper-status-box{grid-column:1/3;grid-row:2}.lebd-exam-scope .lebd-paper-action-box{grid-column:3;grid-row:3}}
@media(max-width:520px){.lebd-exam-scope .lebd-paper-page{padding:12px 10px 35px}.lebd-exam-scope .lebd-paper-stats{gap:7px}.lebd-exam-scope .lebd-paper-stat{padding:10px 8px;min-height:68px;gap:6px}.lebd-exam-scope .lebd-paper-stat-icon{width:33px;height:33px;font-size:17px;border-radius:9px}.lebd-exam-scope .lebd-paper-stat small{font-size:10px}.lebd-exam-scope .lebd-paper-stat strong{font-size:20px}.lebd-exam-scope .lebd-paper-card{padding:10px 9px}.lebd-exam-scope .lebd-paper-main{grid-template-columns:45px minmax(0,1fr) minmax(0,1fr);gap:7px}.lebd-exam-scope .lebd-paper-number{width:45px;min-height:45px;font-size:19px}.lebd-exam-scope .lebd-paper-name{font-size:13px}.lebd-exam-scope .lebd-paper-name small{font-size:9px;padding:3px 5px}.lebd-exam-scope .lebd-paper-time{gap:4px;align-items:flex-start}.lebd-exam-scope .lebd-paper-time-icon{height:22px;width:22px;font-size:12px}.lebd-exam-scope .lebd-paper-time small{font-size:9px}.lebd-exam-scope .lebd-paper-time strong{font-size:10px}.lebd-exam-scope .lebd-paper-status{font-size:10px;padding:5px 6px}.lebd-exam-scope .lebd-paper-status-caption{font-size:9px}.lebd-exam-scope .lebd-paper-action{font-size:10px;min-height:39px;padding:6px 4px}.lebd-exam-scope .lebd-paper-more{font-size:10px}}

/* LEBD-05: Routine-only page, matches supplied compact reference without changing other pages. */
.lebd-exam-scope .lebd-routine-page{min-height:100vh;background:#edf2fa;color:#102d52;font-family:system-ui,'Noto Sans Bengali',sans-serif}
.lebd-exam-scope .lebd-routine-toolbar{height:48px;background:#fff;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:0 max(16px,calc((100vw - 840px)/2));border-bottom:1px solid #dae5f2}
.lebd-exam-scope .lebd-routine-toolbar strong{font-size:14px;color:#0b70d6}.lebd-exam-scope .lebd-routine-toolbar-tools{display:flex;gap:12px;align-items:center}.lebd-exam-scope .lebd-routine-toolbar-tools button{background:transparent;border:0;color:#133659;cursor:pointer;font-weight:800}.lebd-exam-scope .lebd-routine-arrow{font-size:23px;font-weight:700}
.lebd-exam-scope .lebd-routine-body{max-width:840px;margin:auto;padding:18px 16px 55px}.lebd-exam-scope .lebd-routine-heading{text-align:center}.lebd-exam-scope .lebd-routine-heading h1{font-size:27px;margin:0;color:#0e274b;font-weight:850}.lebd-exam-scope .lebd-routine-heading p{font-size:12px;margin:4px 0 14px;color:#7184a2}
.lebd-exam-scope .lebd-routine-commands{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin:0 0 18px}.lebd-exam-scope .lebd-routine-commands button{min-height:40px;border-radius:10px;cursor:pointer;font-size:13px;font-weight:750}.lebd-exam-scope .lebd-routine-share{border:1px solid #a9cfff;background:#e6f3ff;color:#096bd6}.lebd-exam-scope .lebd-routine-print{border:1px solid #dae2ee;background:#fff;color:#173456}
.lebd-exam-scope .lebd-routine-section-title{display:flex;justify-content:space-between;align-items:center;margin:8px 0}.lebd-exam-scope .lebd-routine-section-title h2{font-size:17px;margin:0}.lebd-exam-scope .lebd-routine-section-title small{color:#6f86aa}
.lebd-exam-scope .lebd-routine-card{background:#fff;padding:16px;margin:12px 0;border-radius:17px;border:1px solid #e1e8f1;box-shadow:0 4px 12px #1539590b}.lebd-exam-scope .lebd-routine-card-head{display:flex;justify-content:space-between;align-items:center}.lebd-exam-scope .lebd-routine-card-head small{display:block;color:#647eaa;font-size:10px;font-weight:800}.lebd-exam-scope .lebd-routine-card-head h3{margin:2px 0 12px;font-size:19px}.lebd-exam-scope .lebd-routine-badge{background:#e6f0fb;color:#2763a1;padding:6px 12px;border-radius:25px;font-size:11px;font-weight:800}.lebd-exam-scope .lebd-routine-badge.is-live{background:#daf8e6;color:#047648}
.lebd-exam-scope .lebd-routine-dates{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}.lebd-exam-scope .lebd-routine-dates>div{background:#eef3fa;border-radius:10px;padding:12px;min-width:0}.lebd-exam-scope .lebd-routine-dates small{display:block;color:#7188a4;font-size:11px}.lebd-exam-scope .lebd-routine-dates b{display:block;font-size:12px;overflow-wrap:anywhere}
.lebd-exam-scope .lebd-routine-syllabus-title{font-size:13px;font-weight:850;margin:10px 0;color:#0b4c99}.lebd-exam-scope .lebd-routine-table-wrap{overflow-x:auto}.lebd-exam-scope .lebd-routine-table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid #d5e3f4;border-radius:10px;overflow:hidden;background:#fff;font-size:12px}.lebd-exam-scope .lebd-routine-table th{background:#e8f2ff;text-align:left;font-weight:850}.lebd-exam-scope .lebd-routine-table th,.lebd-exam-scope .lebd-routine-table td{padding:10px 9px;border-bottom:1px solid #e3eaf3;vertical-align:top;overflow-wrap:anywhere}.lebd-exam-scope .lebd-routine-table tr:last-child td{border-bottom:0}.lebd-exam-scope .lebd-routine-table th:first-child,.lebd-exam-scope .lebd-routine-table td:first-child{width:30%}
.lebd-exam-scope .lebd-routine-instructions{margin-top:13px;border-top:1px dashed #d4e1f0;padding-top:11px}.lebd-exam-scope .lebd-routine-instructions summary{cursor:pointer;color:#0475e8;font-size:12px;font-weight:800;list-style:none;display:flex;justify-content:space-between}.lebd-exam-scope .lebd-routine-instructions summary::-webkit-details-marker{display:none}.lebd-exam-scope .lebd-routine-instructions>div{padding-top:12px;font-size:12px;color:#556985}.lebd-exam-scope .lebd-routine-bottom{text-align:center;margin-top:20px}.lebd-exam-scope .lebd-routine-bottom a{display:inline-block;background:#fff;color:#096bd6;padding:12px 18px;border-radius:10px;font-weight:800}
@media(max-width:560px){.lebd-exam-scope .lebd-routine-body{padding:15px 11px 36px}.lebd-exam-scope .lebd-routine-card{padding:12px;border-radius:15px}.lebd-exam-scope .lebd-routine-heading h1{font-size:22px}.lebd-exam-scope .lebd-routine-dates{gap:7px}.lebd-exam-scope .lebd-routine-dates>div{padding:9px 7px}.lebd-exam-scope .lebd-routine-dates b{font-size:11px}.lebd-exam-scope .lebd-routine-table{font-size:10px}.lebd-exam-scope .lebd-routine-table th,.lebd-exam-scope .lebd-routine-table td{padding:8px 6px}}
@media print{.lebd-exam-scope .lebd-routine-toolbar,.lebd-exam-scope .lebd-routine-commands,.lebd-exam-scope .lebd-routine-bottom{display:none!important}.lebd-exam-scope .lebd-routine-page{background:#fff}.lebd-exam-scope .lebd-routine-card{break-inside:avoid;box-shadow:none}}
/* LEBD05 print-only exam/subject/chapter schedule. Screen appearance is unchanged. */
.lebd-exam-scope .lebd05-print-schedule{display:none}
@media print{
 @page{size:A4 landscape;margin:11mm}
 html,body{background:white!important}
 body:has(#lebd05-routine-content) header:not(.lebd05-print-header),
 body:has(#lebd05-routine-content) footer,
 body:has(#lebd05-routine-content) nav,
 body:has(#lebd05-routine-content) .chat-widget,
 body:has(#lebd05-routine-content) .floating-chat,
 body:has(#lebd05-routine-content) .fixed-bottom{display:none!important}
 .lebd-exam-scope{background:#fff!important;color:#000!important}
 .lebd-exam-scope .lebd-routine-page{min-height:0!important;background:#fff!important}
 .lebd-exam-scope .lebd-routine-toolbar,.lebd-exam-scope .lebd-routine-body{display:none!important}
 .lebd-exam-scope .lebd05-print-schedule{display:block!important;position:relative;width:100%;max-width:none!important;padding:0!important;margin:0!important;color:#000;background:#fff;font:10pt/1.35 'Noto Sans Bengali',sans-serif}
 .lebd-exam-scope .lebd05-print-schedule h1{text-align:center;font-size:17pt;margin:0 0 5mm;font-weight:800}
 .lebd-exam-scope .lebd05-print-schedule p{text-align:center;margin:0 0 5mm;font-weight:600;font-size:11pt}
 .lebd-exam-scope .lebd05-print-info{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:2mm 5mm;margin:0 0 5mm;padding:3mm 4mm;border:1px solid #333;font-size:9pt;line-height:1.5;overflow-wrap:anywhere}
 .lebd-exam-scope .lebd05-print-info strong{font-weight:800}
 .lebd-exam-scope .lebd05-print-table{width:100%!important;border-collapse:collapse!important;table-layout:fixed;font-size:9pt}
 .lebd-exam-scope .lebd05-print-table th,.lebd-exam-scope .lebd05-print-table td{border:1px solid #333!important;padding:7px 8px!important;vertical-align:top!important;overflow-wrap:anywhere}
 .lebd-exam-scope .lebd05-print-table thead th{background:#eaf2fb!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;font-weight:800;text-align:left}
 .lebd-exam-scope .lebd05-print-table tbody tr{break-inside:avoid;page-break-inside:avoid}
 .lebd-exam-scope .lebd05-print-table .lebd05-topic-line + .lebd05-topic-line{border-top:1px dotted #aaa;padding-top:3px;margin-top:3px}
 .lebd-exam-scope .lebd05-print-table td:nth-child(1),.lebd-exam-scope .lebd05-print-table td:nth-child(4){font-weight:700}
}


/* LEBD05: app-style countdown ONLY; all surrounding exam-card styles preserved. */
.lebd-exam-scope .lebd05-next-exam-countdown{margin:11px 0 4px;padding:12px 11px 10px;background:#e6f1ff;border:0;border-radius:15px;color:#0c397c;font-family:inherit;min-width:0}
.lebd-exam-scope .lebd05-countdown-caption{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:12px;font-size:14px;font-weight:800;line-height:1.4;text-align:center;color:#0c2861}
.lebd-exam-scope .lebd05-countdown-caption::before,.lebd-exam-scope .lebd05-countdown-caption::after{content:"";height:1px;flex:1;background:#b2d1fa;min-width:8px}
.lebd-exam-scope .lebd05-countdown-caption svg{width:19px;height:19px;flex:none;stroke:#0965d9}
.lebd-exam-scope .lebd05-countdown-units{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:5px;min-width:0}
.lebd-exam-scope .lebd05-countdown-unit{min-width:0;text-align:center;border-radius:11px;overflow:hidden;background:#bfddff}
.lebd-exam-scope .lebd05-countdown-value{display:block;background:#156fe5;color:#fff;font-size:clamp(18px,5vw,29px);font-weight:850;line-height:1.15;letter-spacing:0;font-variant-numeric:tabular-nums;padding:8px 1px 7px;font-family:system-ui,-apple-system,'Segoe UI',sans-serif}
.lebd-exam-scope .lebd05-countdown-label{display:block;color:#152b4b;font-size:clamp(10px,2.8vw,13px);font-weight:700;padding:3px 0 5px;line-height:1.25;font-family:inherit}
.lebd-exam-scope .lebd05-countdown-ended{display:none;text-align:center;font-size:13px;font-weight:800;padding:9px 4px}
.lebd-exam-scope .lebd05-next-exam-countdown.is-finished .lebd05-countdown-units{display:none}
.lebd-exam-scope .lebd05-next-exam-countdown.is-finished .lebd05-countdown-ended{display:block}
@media(max-width:360px){.lebd-exam-scope .lebd05-next-exam-countdown{padding:9px 7px}.lebd-exam-scope .lebd05-countdown-units{gap:4px}.lebd-exam-scope .lebd05-countdown-value{font-size:19px;padding:8px 1px}.lebd-exam-scope .lebd05-countdown-label{font-size:10px}}

/* LEBD-05: upcoming feature-card header & CTA visual only; leave timer and all other pages untouched. */
.lebd-exam-scope .lebd05-upcoming-only .se26-feature-head{gap:7px;min-width:0}
.lebd-exam-scope .lebd05-upcoming-only .se26-feature-head > div:first-child{gap:7px!important;min-width:0;flex:1 1 auto}
.lebd-exam-scope .lebd05-upcoming-only .se26-icon{width:34px;height:34px;min-width:34px;border-radius:9px;font-size:17px;flex:0 0 34px}
.lebd-exam-scope .lebd05-upcoming-only .se26-feature-head h2{font-size:clamp(13px,3.6vw,19px);white-space:nowrap;min-width:0;line-height:1.3}
.lebd-exam-scope .lebd05-upcoming-only .se26-feature-head .se26-state{flex:0 0 auto;white-space:nowrap;color:#c62828;background:#fff1f1;border:1px solid #ffcaca;font-weight:850;padding:7px 11px}
.lebd-exam-scope .lebd05-upcoming-only .se26-feature-actions .se26-btn.muted.disabled{opacity:1;background:#fff0e9;border:1px solid #ffb58f;color:#c84e1a;font-size:15px;font-weight:850;box-shadow:0 3px 10px rgba(226,100,47,.11);min-height:47px;cursor:not-allowed}
@media(max-width:360px){.lebd-exam-scope .lebd05-upcoming-only .se26-feature-head h2{font-size:12px}.lebd-exam-scope .lebd05-upcoming-only .se26-icon{width:30px;height:30px;min-width:30px;font-size:15px}.lebd-exam-scope .lebd05-upcoming-only .se26-feature-head .se26-state{font-size:11px;padding:6px 8px}}

/* LEBD05: action-button-only update. Existing card/countdown CSS unchanged. */
.lebd-exam-scope .lebd05-exam-action-zone{position:relative}
.lebd-exam-scope .lebd05-locked-action{display:flex;align-items:center;justify-content:center;width:100%;min-height:48px;background:#fff0e9;border:1px solid #ffb58f;border-radius:13px}
.lebd-exam-scope .lebd05-locked-action[hidden]{display:none!important}
.lebd-exam-scope .lebd05-lock-round{width:34px;height:34px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#fff;color:#c84e1a;border:1px solid #ffd2bb}
.lebd-exam-scope .lebd05-lock-round svg{width:19px;height:19px}
.lebd-exam-scope a.lebd05-live-join{width:100%;display:flex;align-items:center;justify-content:center;gap:10px;min-height:50px;border:0;border-radius:13px;background:linear-gradient(105deg,#008d74 0%,#09b780 55%,#49e77a 100%);color:#fff!important;font-size:16px;font-weight:850;box-shadow:0 6px 16px rgba(4,171,115,.17);text-decoration:none}
.lebd-exam-scope a.lebd05-live-join[hidden]{display:none!important}
.lebd-exam-scope .lebd05-play-round{width:27px;height:27px;display:inline-flex;align-items:center;justify-content:center;background:#fff;border-radius:50%;font-size:13px;color:#009d6d;padding-left:2px}
/* LEBD05: only the countdown caption: no clock icon, compact app-style two-line mobile guidance. */
.lebd-exam-scope .lebd05-countdown-caption svg{display:none!important}
.lebd-exam-scope .lebd05-countdown-caption{gap:7px;margin-bottom:9px;font-size:12px;line-height:1.35}
.lebd-exam-scope .lebd05-countdown-phase{display:block;max-width:290px;margin:auto;text-wrap:balance}
@media(max-width:600px){.lebd-exam-scope .lebd05-countdown-caption{font-size:11.5px;line-height:1.3;min-height:30px}.lebd-exam-scope .lebd05-countdown-phase{max-width:220px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}}

/* LEBD-05: only live badge readability + featured exam category/routine caption. */
.lebd-exam-scope .se26-state.live{font-size:14px;font-weight:850;padding:8px 14px;line-height:1.2;white-space:nowrap}
.lebd-exam-scope .lebd05-feature-routine-path{font-size:12px;font-weight:600;color:#627a9b;line-height:1.4;margin:0 0 7px;overflow-wrap:anywhere}
@media(max-width:600px){.lebd-exam-scope .se26-state.live{font-size:12px;padding:7px 12px}.lebd-exam-scope .lebd05-feature-routine-path{font-size:11px}}

/* LEBD05: exam number and category > routine on one line; scoped to featured card. */
.lebd-exam-scope .lebd05-feature-inline-row{display:flex;align-items:baseline;gap:7px;min-width:0;max-width:100%;margin:12px 0 8px;white-space:nowrap;overflow:hidden}
.lebd-exam-scope .lebd05-feature-inline-row .se26-feature-name{flex:0 0 auto;margin:0;font-size:19px;font-weight:800;line-height:1.3}
.lebd-exam-scope .lebd05-feature-inline-row .lebd05-feature-routine-path{display:block;flex:1 1 auto;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin:0;font-size:11px;line-height:1.5}
@media(max-width:600px){.lebd-exam-scope .lebd05-feature-inline-row{gap:6px}.lebd-exam-scope .lebd05-feature-inline-row .se26-feature-name{font-size:16px}.lebd-exam-scope .lebd05-feature-inline-row .lebd05-feature-routine-path{font-size:9px}}

/* LEBD-05 featured exam: read-only Routine Subject & Syllabus; no other card styling changes. */
.lebd-exam-scope .lebd05-feature-topic-box{margin:10px 0 13px;border:1px solid #d1e4fc;border-radius:10px;padding:9px;background:#f8fbff;min-width:0}
.lebd-exam-scope .lebd05-feature-topic-title{font-size:12px;font-weight:800;color:#1266c4;margin:0 0 7px}
.lebd-exam-scope .lebd05-feature-topic-table-wrap{overflow-x:auto;border:1px solid #d1e4fc;border-radius:8px}
.lebd-exam-scope .lebd05-feature-topic-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:11px;line-height:1.5}
.lebd-exam-scope .lebd05-feature-topic-table th{background:#e7f2ff;color:#12375e;text-align:left;padding:7px 8px;font-weight:800}
.lebd-exam-scope .lebd05-feature-topic-table td{background:white;padding:7px 8px;border-top:1px solid #dce9f8;overflow-wrap:anywhere;color:#243e5d;vertical-align:top}
.lebd-exam-scope .lebd05-feature-topic-table th:first-child,.lebd-exam-scope .lebd05-feature-topic-table td:first-child{width:38%;border-right:1px solid #dce9f8}
@media(max-width:600px){.lebd-exam-scope .lebd05-feature-topic-box{padding:7px;margin:9px 0 11px}.lebd-exam-scope .lebd05-feature-topic-table{font-size:10px}.lebd-exam-scope .lebd05-feature-topic-table th,.lebd-exam-scope .lebd05-feature-topic-table td{padding:6px 5px}}
</style>
@endsection
@section('content')
<div class="lebd-exam-scope">
@if(request()->query('routine') === '1')
@php
    // Read-only lookups. Do not alter exam/routine/attempt data.
    $routineItemIds = $examRows->pluck('exam.id')->all();
    $routineSavedTopics = ($routineItemIds && \Illuminate\Support\Facades\Schema::hasTable('lebd_exam_section_routine_item_topics'))
        ? \Illuminate\Support\Facades\DB::table('lebd_exam_section_routine_item_topics')->whereIn('item_id',$routineItemIds)->orderBy('id')->get()->groupBy('item_id')
        : collect();
    $routineDeptIds=[]; $routineSubIds=[]; $routineChapIds=[];
    foreach ($routineSavedTopics as $topicList) foreach ($topicList as $topic) {
        if (preg_match('/^(department|subject|chapter):(\d+)$/', (string)($topic->topic_key ?? ''), $parts)) {
            if($parts[1]==='department') $routineDeptIds[]=(int)$parts[2];
            if($parts[1]==='subject') $routineSubIds[]=(int)$parts[2];
            if($parts[1]==='chapter') $routineChapIds[]=(int)$parts[2];
        }
    }
    $routineChapterMap=($routineChapIds && \Illuminate\Support\Facades\Schema::hasTable('lebd_exam_routine_chapters')) ? \Illuminate\Support\Facades\DB::table('lebd_exam_routine_chapters')->whereIn('id',array_unique($routineChapIds))->get()->keyBy('id') : collect();
    foreach($routineChapterMap as $chapter) $routineSubIds[]=(int)$chapter->subject_id;
    $routineSubjectMap=($routineSubIds && \Illuminate\Support\Facades\Schema::hasTable('lebd_exam_routine_subjects')) ? \Illuminate\Support\Facades\DB::table('lebd_exam_routine_subjects')->whereIn('id',array_unique($routineSubIds))->get()->keyBy('id') : collect();
    foreach($routineSubjectMap as $subject) $routineDeptIds[]=(int)$subject->department_id;
    $routineDeptMap=($routineDeptIds && \Illuminate\Support\Facades\Schema::hasTable('lebd_exam_routine_departments')) ? \Illuminate\Support\Facades\DB::table('lebd_exam_routine_departments')->whereIn('id',array_unique($routineDeptIds))->get()->keyBy('id') : collect();
@endphp
<div class="lebd-routine-page" id="lebd05-routine-content">
  <div class="lebd-routine-toolbar">
    <a class="lebd-routine-arrow" href="{{ route('student.exam-section.browse-exams',['category'=>$cat->id,'subcategory_id'=>$sub?->id]) }}" aria-label="Exam Dashboard-এ ফিরে যান">←</a>
    <strong>Live Education BD</strong><div class="lebd-routine-toolbar-tools"><button type="button" onclick="window.print()" aria-label="Print">▣</button><span aria-hidden="true">◐</span></div>
  </div>
  <div class="lebd-routine-body">
    <header class="lebd-routine-heading"><h1>Exam Routine</h1><p>{{ $pageName }}</p></header>
    <div class="lebd-routine-commands">
      <button type="button" class="lebd-routine-share" onclick="if(navigator.share){navigator.share({title:document.title,url:location.href}).catch(function(){});}else if(navigator.clipboard){navigator.clipboard.writeText(location.href).then(function(){alert('Routine link copied');}).catch(function(){});}">♧ Share Routine</button>
      <button type="button" class="lebd-routine-print" onclick="window.print()">▣ Print / Save PDF</button>
    </div>
    <div class="lebd-routine-section-title"><h2>Exam Schedule</h2><small>{{ $total }} Exams</small></div>
    @forelse($examRows as $entry)
      @php
        $routineExam=$entry['exam'];
        $savedForItem=$routineSavedTopics->get($routineExam->id, collect());
      @endphp
      <article class="lebd-routine-card">
        <div class="lebd-routine-card-head"><div><small>EXAM NO: {{ $routineExam->exam_name }}</small><h3>{{ $routineExam->exam_name }}</h3></div><span class="lebd-routine-badge {{ $entry['live']?'is-live':'' }}">{{ $entry['ended']?'Completed':($entry['live']?'Live':'Upcoming') }}</span></div>
        <div class="lebd-routine-dates"><div><small>▦ Exam Date</small><b>{{ $entry['start']?->format('d M Y') ?? 'Not scheduled' }}</b></div><div><small>◷ Exam Time</small><b>{{ $entry['start']?->format('h:i A') ?? '—' }} - {{ $entry['end']?->format('d M Y, h:i A') ?? '—' }}</b></div></div>
        <div class="lebd-routine-syllabus-title">▣ Subject &amp; Syllabus</div>
        <div class="lebd-routine-table-wrap"><table class="lebd-routine-table"><thead><tr><th>Subject</th><th>Syllabus / Topic</th></tr></thead><tbody>
        @forelse($savedForItem as $topic)
          @php
            $key=(string)($topic->topic_key ?? ''); $dept=null;$subject=null;$chapter=null;
            if (preg_match('/^(department|subject|chapter):(\d+)$/',$key,$parts)) {
                $id=(int)$parts[2];
                if($parts[1]==='department') $dept=$routineDeptMap->get($id);
                if($parts[1]==='subject') {$subject=$routineSubjectMap->get($id);$dept=$subject?$routineDeptMap->get($subject->department_id):null;}
                if($parts[1]==='chapter') {$chapter=$routineChapterMap->get($id);$subject=$chapter?$routineSubjectMap->get($chapter->subject_id):null;$dept=$subject?$routineDeptMap->get($subject->department_id):null;}
            }
            $path=implode(' > ',array_filter([$dept->name??null,$subject->name??null,$chapter->name??null]));
          @endphp
          <tr><td>{{ $subject->name ?? ($dept->name ?? 'Subject') }}</td><td>{{ $path ?: '—' }} @if(isset($topic->question_start) && $topic->question_start !== null) (Questions: {{ $topic->question_start }}@if(isset($topic->question_end) && $topic->question_end !== null)-{{ $topic->question_end }}@endif) @endif</td></tr>
        @empty <tr><td colspan="2">এই পরীক্ষার Subject &amp; Syllabus এখনো যুক্ত করা হয়নি।</td></tr>
        @endforelse
        </tbody></table></div>
        <details class="lebd-routine-instructions"><summary>ⓘ See More / Instructions <span aria-hidden="true">⌄</span></summary><div>{!! nl2br(e((string)($routineExam->notes ?? 'প্রকাশিত Routine অনুযায়ী পরীক্ষায় অংশ নিন।'))) !!}</div></details>
      </article>
    @empty <div class="se26-empty">এই বিভাগে কোনো Routine Exam পাওয়া যায়নি।</div>
    @endforelse
    <div class="lebd-routine-bottom"><a href="{{ route('student.exam-section.browse-exams',['category'=>$cat->id,'subcategory_id'=>$sub?->id]) }}">← Exam Dashboard</a></div>
  </div>
  {{-- Read-only print heading metadata. No changes to the screen routine cards. --}}
  @php
    $printRoutineMeta = $routineIds ? \Illuminate\Support\Facades\DB::table('lebd_exam_section_routines')
        ->whereIn('id', $routineIds)->get(['id','name','batch_id','course_id']) : collect();
    $printBatchIds = $printRoutineMeta->pluck('batch_id')->filter()->unique()->all();
    $printCourseIds = $printRoutineMeta->pluck('course_id')->filter()->unique()->all();
    $printBatchMap = ($printBatchIds && \Illuminate\Support\Facades\Schema::hasTable('batches'))
        ? \Illuminate\Support\Facades\DB::table('batches')->whereIn('id',$printBatchIds)->pluck('name','id') : collect();
    $printCourseMap = ($printCourseIds && \Illuminate\Support\Facades\Schema::hasTable('courses'))
        ? \Illuminate\Support\Facades\DB::table('courses')->whereIn('id',$printCourseIds)->pluck('name','id') : collect();
    $printRoutineNames = $printRoutineMeta->pluck('name')->filter()->unique()->values();
    $printBatchNames = $printRoutineMeta->pluck('batch_id')->filter()->map(fn($id)=>$printBatchMap->get($id))->filter()->unique()->values();
    $printCourseNames = $printRoutineMeta->pluck('course_id')->filter()->map(fn($id)=>$printCourseMap->get($id))->filter()->unique()->values();
    $printDeptNames = $routineDeptMap->pluck('name')->filter()->unique()->values();
  @endphp
  {{-- Print/PDF only: screen cards above are untouched; every saved topic is represented below. --}}
  <section class="lebd05-print-schedule" aria-label="Printable Routine Schedule">
    <header class="lebd05-print-header">
      <h1>Live Education BD — Exam Routine</h1>
      <p>{{ $cat->name }} @if($sub) — {{ $sub->name }} @endif</p>
      <div class="lebd05-print-info">
        <div><strong>Routine:</strong> {{ $printRoutineNames->isNotEmpty() ? $printRoutineNames->implode(', ') : '—' }}</div>
        @if($printBatchNames->isNotEmpty())<div><strong>Batch:</strong> {{ $printBatchNames->implode(', ') }}</div>@endif
        @if($printCourseNames->isNotEmpty())<div><strong>Course:</strong> {{ $printCourseNames->implode(', ') }}</div>@endif
        @if($printDeptNames->isNotEmpty())<div><strong>Department:</strong> {{ $printDeptNames->implode(', ') }}</div>@endif
        <div><strong>Total Exams:</strong> {{ $total }}</div>
      </div>
    </header>
    <table class="lebd05-print-table">
      <colgroup><col style="width:13%"><col style="width:24%"><col style="width:24%"><col style="width:24%"><col style="width:15%"></colgroup>
      <thead><tr><th>Exam No</th><th>Subject</th><th>Chapter / Syllabus</th><th>Exam Date &amp; Time</th><th>Status</th></tr></thead>
      <tbody>
      @forelse($examRows as $entry)
        @php
          $printExam=$entry['exam'];
          $printTopics=$routineSavedTopics->get($printExam->id, collect());
        @endphp
        <tr>
          <td>{{ $printExam->exam_name }}</td>
          <td>
            @forelse($printTopics as $topic)
              @php
                $parts=[]; $printSubject=null; $printDept=null; $printChapter=null;
                if(preg_match('/^(department|subject|chapter):(\d+)$/',(string)($topic->topic_key??''),$parts)){
                  $topicId=(int)$parts[2];
                  if($parts[1]==='department') $printDept=$routineDeptMap->get($topicId);
                  if($parts[1]==='subject'){$printSubject=$routineSubjectMap->get($topicId);$printDept=$printSubject?$routineDeptMap->get($printSubject->department_id):null;}
                  if($parts[1]==='chapter'){$printChapter=$routineChapterMap->get($topicId);$printSubject=$printChapter?$routineSubjectMap->get($printChapter->subject_id):null;$printDept=$printSubject?$routineDeptMap->get($printSubject->department_id):null;}
                }
              @endphp
              <div class="lebd05-topic-line">{{ $printSubject->name ?? ($printDept->name ?? '—') }}</div>
            @empty
              —
            @endforelse
          </td>
          <td>
            @forelse($printTopics as $topic)
              @php
                $parts=[]; $printSubject=null; $printDept=null; $printChapter=null;
                if(preg_match('/^(department|subject|chapter):(\d+)$/',(string)($topic->topic_key??''),$parts)){
                  $topicId=(int)$parts[2];
                  if($parts[1]==='department') $printDept=$routineDeptMap->get($topicId);
                  if($parts[1]==='subject'){$printSubject=$routineSubjectMap->get($topicId);$printDept=$printSubject?$routineDeptMap->get($printSubject->department_id):null;}
                  if($parts[1]==='chapter'){$printChapter=$routineChapterMap->get($topicId);$printSubject=$printChapter?$routineSubjectMap->get($printChapter->subject_id):null;$printDept=$printSubject?$routineDeptMap->get($printSubject->department_id):null;}
                }
                $printPath=implode(' > ',array_filter([$printDept->name??null,$printSubject->name??null,$printChapter->name??null]));
              @endphp
              <div class="lebd05-topic-line">{{ $printPath ?: '—' }}</div>
            @empty
              —
            @endforelse
          </td>
          <td>{{ $entry['start']?->format('d M Y, h:i A') ?? 'Not scheduled' }}<div>End: {{ $entry['end']?->format('d M Y, h:i A') ?? '—' }}</div></td>
          <td>{{ $entry['ended']?'Completed':($entry['live']?'Live':'Upcoming') }}</td>
        </tr>
      @empty
        <tr><td colspan="5">No routine exams available.</td></tr>
      @endforelse
      </tbody>
    </table>
  </section>
</div>
@elseif(request()->query('missing') === '1')
{{-- LEBD05: student-specific missing exams; only original end time and absence of an attempt. --}}
@auth('student')
@php
    $lebd05MissingRows = $examRows->filter(function ($entry) use ($attemptStatuses) {
        return $entry['end'] !== null
            && $entry['ended']
            && !$attemptStatuses->has($entry['exam']->id);
    })->values();
@endphp
<div class="lebd-paper-page" id="lebd05-missing-exams">
  <div class="lebd-paper-nav"><a class="lebd-paper-back" href="{{ route('student.exam-section.browse-exams', ['category'=>$cat->id, 'subcategory_id'=>$sub?->id]) }}">← Exam Dashboard</a><small>{{ $lebd05MissingRows->count() }} Missing Exams</small></div>
  <div class="lebd-paper-title"><h1>▧ Missing Exam List</h1><p>{{ $cat->name }} @if($sub) — {{ $sub->name }} @endif</p></div>
  <div class="se26-panel" style="padding:14px 17px;margin-bottom:17px;background:#fff8e9;color:#76510f;border-color:#f6dc9f;font-size:13px;line-height:1.7">
    এই Category / Sub-category-এর যে পরীক্ষাগুলোর নির্ধারিত End Time পার হয়েছে এবং আপনি একবারও পরীক্ষা শুরু করেননি, শুধু সেগুলো এখানে দেখাবে।
  </div>
  @forelse($lebd05MissingRows as $entry)
  @php $missingItem = $entry['exam']; @endphp
  <article class="lebd-paper-card">
    <div class="lebd-paper-main">
      <div class="lebd-paper-number">{{ str_pad((string)$loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
      <div class="lebd-paper-name"><small>Missed Exam</small>{{ $missingItem->exam_name }}</div>
      <div class="lebd-paper-time date"><span class="lebd-paper-time-icon" aria-hidden="true">▦</span><div><small>শুরুর তারিখ</small><strong>{{ $entry['start']?->format('d M Y') ?? '—' }}</strong></div></div>
      <div class="lebd-paper-time time"><span class="lebd-paper-time-icon clock" aria-hidden="true">◷</span><div><small>পরীক্ষার সময়</small><strong>{{ $entry['start']?->format('h:i A') ?? '—' }} — {{ $entry['end']->format('d M Y, h:i A') }}</strong></div></div>
      <div class="lebd-paper-status-box"><span class="lebd-paper-status" style="background:#fff0ec;color:#bd4937">✕ Not Participated</span></div>
      <div class="lebd-paper-action-box"><span class="lebd-paper-action off">পরীক্ষার সময় শেষ</span></div>
    </div>
    <details class="lebd-paper-more"><summary>▹ See More</summary><div class="lebd-paper-detail">Routine: {{ $missingItem->routine_name ?? '—' }} @if(!empty($missingItem->notes)) — {{ $missingItem->notes }} @endif</div></details>
  </article>
  @empty
    <div class="se26-empty" style="text-align:center;padding:30px">এই Category / Sub-category-তে আপনার কোনো Missing Exam নেই।</div>
  @endforelse
</div>
@else
<div class="lebd-paper-page" style="text-align:center"><div class="se26-panel"><h2>Missing Exam List</h2><p>নিজের Missing Exam দেখতে Student Account-এ লগইন করুন।</p><a class="lebd-paper-back" href="{{ url('/student/login') }}">Student Login →</a></div></div>
@endauth
@elseif(in_array(request()->query('paper'), ['1', 'result'], true))
@php $lebd05ResultPage = request()->query('paper') === 'result'; $lebd05SubmittedTotal = $attemptStatuses->filter(fn($status) => $status === 'submitted')->count(); @endphp
<div class="lebd-paper-page">
  <div class="lebd-paper-nav"><a class="lebd-paper-back" href="{{ route('student.exam-section.browse-exams',['category'=>$cat->id,'subcategory_id'=>$sub?->id]) }}">← Exam Dashboard</a><small>{{ $total }} Exams</small></div>
  <div class="lebd-paper-title"><h1>{{ $lebd05ResultPage ? '▣ Exam Result' : '▤ All Exam Paper' }}</h1><p>{{ $cat->name }} @if($sub) — {{ $sub->name }} @endif</p></div>
  <div class="lebd-paper-stats">
    <div class="lebd-paper-stat"><span class="lebd-paper-stat-icon">▤</span><div><small>মোট পরীক্ষা</small><strong>{{ $total }}</strong></div></div>
    <div class="lebd-paper-stat"><span class="lebd-paper-stat-icon">✓</span><div><small>{{ $lebd05ResultPage ? 'পরীক্ষা দিয়েছেন' : 'সময়সীমা শেষ' }}</small><strong>{{ $lebd05ResultPage ? $lebd05SubmittedTotal : $finished }}</strong></div></div>
    <div class="lebd-paper-stat"><span class="lebd-paper-stat-icon">◷</span><div><small>লাইভ পরীক্ষা</small><strong>{{ $liveRows->count() }}</strong></div></div>
    <div class="lebd-paper-stat"><span class="lebd-paper-stat-icon">▦</span><div><small>আসন্ন পরীক্ষা</small><strong>{{ $examRows->filter(fn($r) => !$r['ended'] && !$r['live'])->count() }}</strong></div></div>
  </div>
  <section aria-label="{{ $lebd05ResultPage ? 'Exam Results' : 'All Exam Papers' }}">
  @forelse($examRows as $entry)
    @php
      $paperItem=$entry['exam'];
      $lastEndValue=$routineLastEnds->get($paperItem->routine_id);
      $lastEnd=$lastEndValue ? \Carbon\Carbon::parse($lastEndValue,'Asia/Dhaka') : null;
      $lateOpen=$entry['end'] && $lastEnd && $now->gte($entry['end']->copy()->addHours(24)) && $now->lt($lastEnd);
      $attemptStatus=$attemptStatuses->get($paperItem->id);
      $alreadySubmitted=$attemptStatus==='submitted';
      // Use the same schedule flags as the four summary counters above.
      // A future exam must never be labelled Completed simply because an attempt exists.
      $isFuture = !$entry['start'] || $now->lt($entry['start']);
      $isLiveOriginal = !$isFuture && $entry['live'];
      $isEndedOriginal = !$isFuture && $entry['ended'];
      $isLateEligible = $isEndedOriginal && $lateOpen && !$attemptStatus;
      $mayStart = $currentStudent && !$isFuture && !$alreadySubmitted && !$attemptStatus && ($isLiveOriginal || $isLateEligible);
      $mayViewResult = $alreadySubmitted && ($canViewPaperResult || $isEndedOriginal);
      // Result page: preserve the same cards/colors, show attempt-based result status.
      if ($lebd05ResultPage) {
          if ($alreadySubmitted) {
              $paperStatusLabel = '✓ Completed';
              $paperStatusClass = 'live';
              $paperActionLabel = 'ফলাফল দেখুন';
          } elseif ($isFuture) {
              $paperStatusLabel = '◷ Pending';
              $paperStatusClass = '';
              $paperActionLabel = 'Pending';
          } elseif ($isLiveOriginal) {
              $paperStatusLabel = '● Live Exam';
              $paperStatusClass = 'live';
              $paperActionLabel = 'পরীক্ষা চলছে';
          } else {
              $paperStatusLabel = '✕ Not Taken';
              $paperStatusClass = 'ended';
              $paperActionLabel = 'আপনি পরীক্ষায় অংশ নেননি';
          }
      } else if ($isFuture) {
          $paperStatusLabel = '◷ Pending';
          $paperStatusClass = '';
          $paperActionLabel = 'Pending';
      } elseif ($alreadySubmitted) {
          $paperStatusLabel = '✓ Completed';
          $paperStatusClass = 'ended';
          $paperActionLabel = '✓ Completed';
      } elseif ($isLiveOriginal) {
          $paperStatusLabel = '● Live Exam';
          $paperStatusClass = 'live';
          $paperActionLabel = 'পরীক্ষা দিন';
      } elseif ($isLateEligible) {
          $paperStatusLabel = '● পরীক্ষা দিন';
          $paperStatusClass = 'live';
          $paperActionLabel = 'পরীক্ষা দিন';
      } elseif ($isEndedOriginal) {
          $paperStatusLabel = '✓ Completed';
          $paperStatusClass = 'ended';
          $paperActionLabel = 'Completed';
      } else {
          $paperStatusLabel = '◷ Pending';
          $paperStatusClass = '';
          $paperActionLabel = 'Pending';
      }
    @endphp
    <article class="lebd-paper-card"><div class="lebd-paper-main">
      <div class="lebd-paper-number">{{ str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT) }}</div>
      <div class="lebd-paper-name"><small>Main Exam</small>{{ $paperItem->exam_name }}</div>
      <div class="lebd-paper-time date"><span class="lebd-paper-time-icon" aria-hidden="true">▦</span><div><small>তারিখ</small><strong>{{ $entry['start']?->format('d M Y') ?? 'Not scheduled' }}</strong></div></div>
      <div class="lebd-paper-time time"><span class="lebd-paper-time-icon clock" aria-hidden="true">◷</span><div><small>সময়</small><strong>{{ $entry['start']?->format('h:i A') ?? '—' }} — {{ $entry['end']?->format('d M, h:i A') ?? '—' }}</strong></div></div>
      {{-- Schedule-first labels: future exams are always Pending, regardless of attempts. --}}
      @if($lebd05ResultPage)
      {{-- Result page intentionally hides schedule badges (Live / Completed / Pending). --}}
      <div class="lebd-paper-status-box"><span class="lebd-paper-status-caption">@if($alreadySubmitted)আপনি পরীক্ষায় অংশ নিয়েছেন@endif</span></div>
      @else
      <div class="lebd-paper-status-box"><span class="lebd-paper-status {{ $paperStatusClass }}">{{ $paperStatusLabel }}</span>@if($alreadySubmitted)<span class="lebd-paper-status-caption">আপনি পরীক্ষায় অংশ নিয়েছেন</span>@elseif($isEndedOriginal)<span class="lebd-paper-status-caption">পরীক্ষার নির্ধারিত সময়সীমা শেষ</span>@endif</div>
      @endif
      <div class="lebd-paper-action-box">
        @if($lebd05ResultPage)
          @if($mayViewResult)
            <a class="lebd-paper-action" href="{{ route('student.exam-section.take.result', ['item' => $paperItem->id]) }}">▣ ফলাফল দেখুন</a>
          @elseif($isEndedOriginal && $alreadySubmitted)
            {{-- Requested visual-only result button; access remains controlled by admin settings. --}}
            <span class="lebd-paper-action lebd-paper-result-preview" role="text" title="ফলাফল প্রকাশ হলে সংযোগ কার্যকর হবে">▣ ফলাফল দেখুন</span>
          @elseif($isEndedOriginal)
            <span class="lebd-paper-action off">এই এক্সামে আপনি অংশগ্রহণ করেন নাই</span>
          @else
            <span class="lebd-paper-action off">ফলাফল পরীক্ষার সময় শেষ হলে দেখাবে</span>
          @endif
        @elseif($mayViewResult)
          <a class="lebd-paper-action" href="{{ route('student.exam-section.take.result', ['item' => $paperItem->id]) }}">▣ ফলাফল দেখুন</a>
        @elseif($mayStart)
          <a class="lebd-paper-action" href="{{ route('student.exam-section.take', ['item' => $paperItem->id]) }}">▣ পরীক্ষা দিন</a>
        @elseif($isEndedOriginal)
          {{-- Visual-only result action for ended exams; do not create an invalid result URL. --}}
          <span class="lebd-paper-action lebd-paper-result-preview" role="text" title="ফলাফল সংযোগ পরবর্তীতে যুক্ত হবে">▣ ফলাফল দেখুন</span>
        @else
          <span class="lebd-paper-action off">{{ $paperActionLabel === 'পরীক্ষা দিন' ? ($attemptStatus ? 'আগের প্রচেষ্টা রয়েছে' : 'লগইন প্রয়োজন') : $paperActionLabel }}</span>
        @endif
      </div>
    </div>
    <details class="lebd-paper-more"><summary>▹ Subject &amp; Syllabus / See More</summary><div class="lebd-paper-detail">{{ $pageName }} — {{ $paperItem->exam_name }}</div></details>
    </article>
  @empty
    <div class="se26-empty">এই বিভাগে এখনো কোনো Exam যুক্ত হয়নি।</div>
  @endforelse
  </section>
  <div style="text-align:center;margin:23px 0"><a class="lebd-paper-back" href="{{ route('student.exam-section.browse-exams',['category'=>$cat->id,'subcategory_id'=>$sub?->id]) }}">← ফিরে যান</a></div>
</div>
@else
<main class="se26">
<div class="lebd05-exam-return"><a href="{{ $back }}" class="lebd05-exam-return-link"><span aria-hidden="true">←</span> ফিরে যান</a></div><div class="se26-title"><h1>{{ $pageName }} <span style="color:#1678ce">ℹ</span></h1><p>পরীক্ষা, রুটিন ও অগ্রগতির বিস্তারিত</p></div>
<div class="se26-status-package-row">
  <div class="se26-notice {{ $liveRows->isNotEmpty() ? 'live' : '' }}" role="status">
    @if($liveRows->isNotEmpty())● এখন {{ $liveRows->count() }}টি লাইভ পরীক্ষা চলছে
    @else ⓘ আজকে এই বিভাগে কোনো লাইভ এক্সাম নেই @endif
  </div>
  <div class="se26-package-slot">
    <a href="{{ route('student.exam-section.packages') }}" class="lebd215-package-button se26-package-link"><span aria-hidden="true">▣</span><span>প্যাকেজ কিনুন</span></a>
  </div>
</div>
<section class="se26-panel {{ $featured && !$featured['live'] ? 'lebd05-upcoming-only' : '' }}"><div class="se26-feature-head"><div style="display:flex;align-items:center;gap:13px"><span class="se26-icon">▤</span><h2>{{ $featured && $featured['live'] ? 'Live Exam' : 'Upcoming Exam' }}</h2></div><span class="se26-state {{ $featured && $featured['live'] ? 'live' : '' }}">{{ $featured && $featured['live'] ? '● Live' : 'Schedule' }}</span></div>
@if($featured)<div class="lebd05-feature-inline-row"><span class="se26-feature-name">{{ preg_replace('/^Exam[-\s]*/i', 'Exam No-', (string) $featured['exam']->exam_name) }}</span>
@if($lebd05FeaturedRoutineName)
<span class="lebd05-feature-routine-path">{{ $cat->name }} @if($sub) &gt; {{ $sub->name }} @endif &gt; {{ $lebd05FeaturedRoutineName }}</span>
@endif</div>
@php
    // Same read-only Routine item topics and hierarchy used in the Routine screen above.
    $lebd05FeaturedTopics = \Illuminate\Support\Facades\Schema::hasTable('lebd_exam_section_routine_item_topics')
        ? \Illuminate\Support\Facades\DB::table('lebd_exam_section_routine_item_topics')
            ->where('item_id', (int)$featured['exam']->id)->orderBy('id')->get()
        : collect();
    $lebd05TopicDepts = []; $lebd05TopicSubjects = []; $lebd05TopicChapters = [];
    foreach ($lebd05FeaturedTopics as $t) {
        if (preg_match('/^(department|subject|chapter):(\d+)$/', (string)($t->topic_key ?? ''), $m)) {
            if ($m[1] === 'department') $lebd05TopicDepts[]=(int)$m[2];
            if ($m[1] === 'subject') $lebd05TopicSubjects[]=(int)$m[2];
            if ($m[1] === 'chapter') $lebd05TopicChapters[]=(int)$m[2];
        }
    }
    $lebd05ChapterLookup = $lebd05TopicChapters ? \Illuminate\Support\Facades\DB::table('lebd_exam_routine_chapters')->whereIn('id',array_unique($lebd05TopicChapters))->get()->keyBy('id') : collect();
    foreach ($lebd05ChapterLookup as $c) $lebd05TopicSubjects[]=(int)$c->subject_id;
    $lebd05SubjectLookup = $lebd05TopicSubjects ? \Illuminate\Support\Facades\DB::table('lebd_exam_routine_subjects')->whereIn('id',array_unique($lebd05TopicSubjects))->get()->keyBy('id') : collect();
    foreach ($lebd05SubjectLookup as $sb) $lebd05TopicDepts[]=(int)$sb->department_id;
    $lebd05DepartmentLookup = $lebd05TopicDepts ? \Illuminate\Support\Facades\DB::table('lebd_exam_routine_departments')->whereIn('id',array_unique($lebd05TopicDepts))->get()->keyBy('id') : collect();
@endphp
<div class="lebd05-feature-topic-box">
  <div class="lebd05-feature-topic-title">▣ <span data-topic-title-upcoming="Exam Topic" data-topic-title-live="Today Exam Topic">{{ $featured['live'] ? 'Today Exam Topic' : 'Exam Topic' }}</span></div>
  <div class="lebd05-feature-topic-table-wrap"><table class="lebd05-feature-topic-table"><tbody>
  @forelse($lebd05FeaturedTopics as $topic)
    @php
      $dept=null; $subject=null; $chapter=null;
      if (preg_match('/^(department|subject|chapter):(\d+)$/', (string)($topic->topic_key ?? ''), $parts)) {
          $id=(int)$parts[2];
          if ($parts[1]==='department') $dept=$lebd05DepartmentLookup->get($id);
          if ($parts[1]==='subject') { $subject=$lebd05SubjectLookup->get($id); $dept=$subject?$lebd05DepartmentLookup->get($subject->department_id):null; }
          if ($parts[1]==='chapter') { $chapter=$lebd05ChapterLookup->get($id); $subject=$chapter?$lebd05SubjectLookup->get($chapter->subject_id):null; $dept=$subject?$lebd05DepartmentLookup->get($subject->department_id):null; }
      }
      $topicPath=implode(' > ',array_filter([$dept->name??null,$subject->name??null,$chapter->name??null]));
    @endphp
    <tr><td>{{ $subject->name ?? ($dept->name ?? 'Subject') }}</td><td>{{ $topicPath ?: '—' }}</td></tr>
  @empty
    <tr><td colspan="2">এই পরীক্ষার Subject &amp; Syllabus এখনো যুক্ত করা হয়নি।</td></tr>
  @endforelse
  </tbody></table></div>
</div>
<p class="se26-muted lebd05-feature-start-time" @if($featured['live']) style="display:none" @endif>শুরু: {{ $featured['start']?->format('d M Y, h:i A') ?? 'Not scheduled' }}</p>
@if($featured['start'] && $featured['end'])
<div class="lebd05-next-exam-countdown" data-exam-start-ms="{{ $featured['start']->timestamp * 1000 }}" data-exam-end-ms="{{ $featured['end']->timestamp * 1000 }}" role="timer" aria-label="পরীক্ষা শুরু বা শেষ হতে বাকি সময়">
  <div class="lebd05-countdown-caption"><span class="lebd05-countdown-phase" data-upcoming-text="শুরু হতে বাকি" data-live-text="এখন পরীক্ষা শুরু করলে সর্বোচ্চ {{ $lebd05RunningMinutes }} মিনিট সময় পাবেন">{{ $featured['live'] ? 'এখন পরীক্ষা শুরু করলে সর্বোচ্চ '.$lebd05RunningMinutes.' মিনিট সময় পাবেন' : 'শুরু হতে বাকি' }}</span></div>
  <div class="lebd05-countdown-units" aria-hidden="true">
    <div class="lebd05-countdown-unit"><strong class="lebd05-countdown-value" data-unit="days">--</strong><span class="lebd05-countdown-label">দিন</span></div>
    <div class="lebd05-countdown-unit"><strong class="lebd05-countdown-value" data-unit="hours">--</strong><span class="lebd05-countdown-label">ঘণ্টা</span></div>
    <div class="lebd05-countdown-unit"><strong class="lebd05-countdown-value" data-unit="minutes">--</strong><span class="lebd05-countdown-label">মিনিট</span></div>
    <div class="lebd05-countdown-unit"><strong class="lebd05-countdown-value" data-unit="seconds">--</strong><span class="lebd05-countdown-label">সেকেন্ড</span></div>
  </div>
  <div class="lebd05-countdown-ended" aria-live="polite">পরীক্ষার সময় শেষ হয়েছে</div>
</div>
@endif
@else<p class="se26-muted">এই Category / Sub-category-এর জন্য এখন কোনো চলমান বা পরবর্তী পরীক্ষা পাওয়া যায়নি। Routine প্রকাশ হলে এখানে দেখাবে।</p>@endif
<div class="se26-feature-actions lebd05-exam-action-zone">
@if($featured && $featured['live'])
  <a class="se26-btn lebd05-live-join lebd05-live-existing-link" data-end-ms="{{ $featured['end']->timestamp * 1000 }}" href="{{ route('student.exam-section.take',['item'=>$featured['exam']->id]) }}"><span aria-hidden="true" class="lebd05-play-round">▶</span><span>পরীক্ষা দিন</span></a>
@elseif($featured && $featured['start'] && $featured['end'])
  <span class="lebd05-locked-action" role="status" aria-label="পরীক্ষা শুরু হয়নি — লক করা আছে"><span class="lebd05-lock-round"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span></span>
  <a class="se26-btn lebd05-live-join lebd05-live-start-link" href="{{ route('student.exam-section.take',['item'=>$featured['exam']->id]) }}" data-start-ms="{{ $featured['start']->timestamp * 1000 }}" data-end-ms="{{ $featured['end']->timestamp * 1000 }}" hidden><span aria-hidden="true" class="lebd05-play-round">▶</span><span>পরীক্ষা দিন</span></a>
@else
  <span class="lebd05-locked-action" role="status" aria-label="পরীক্ষা এখনো নির্ধারিত হয়নি"><span class="lebd05-lock-round"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span></span>
@endif
</div></section>
<h2 class="se26-heading">Exam Menu</h2><section class="se26-menu" aria-label="Exam Menu">
@php $menu = [['Routine','▦',null],['Result','▣',null],['All Exam Paper','▤','#exam-list'],['Missing Exam List','▧',null],['Exam PDF','▤',null],['Merit List','✪',null],['Wrong & Unanswered','⊗',null],['Complain','☏',null]]; @endphp
@foreach($menu as [$name,$symbol,$link])
@if($name === 'Routine')
<a class="se26-menu-item" href="{{ route('student.exam-section.browse-exams', ['category'=>$cat->id, 'subcategory_id'=>$sub?->id, 'routine'=>1]) }}"><span>{{ $symbol }}</span><span>{{ $name }}<small>পরীক্ষার সময়সূচি ও সিলেবাস দেখুন</small></span></a>
@elseif($name === 'Result')
<a class="se26-menu-item" href="{{ route('student.exam-section.browse-exams', ['category'=>$cat->id, 'subcategory_id'=>$sub?->id, 'paper'=>'result']) }}"><span>{{ $symbol }}</span><span>{{ $name }}<small>সকল পরীক্ষার ফলাফল দেখুন</small></span></a>
@elseif($name === 'All Exam Paper')
<a class="se26-menu-item" href="{{ route('student.exam-section.browse-exams', ['category'=>$cat->id, 'subcategory_id'=>$sub?->id, 'paper'=>1]) }}"><span>{{ $symbol }}</span><span>{{ $name }}<small>সকল পরীক্ষার তালিকা দেখুন ({{ $total }})</small></span></a>
@elseif($name === 'Missing Exam List')
<a class="se26-menu-item" href="{{ route('student.exam-section.browse-exams', ['category'=>$cat->id, 'subcategory_id'=>$sub?->id, 'missing'=>1]) }}"><span>{{ $symbol }}</span><span>{{ $name }}<small>সময়সীমা শেষ, অংশ নেননি এমন পরীক্ষার তালিকা</small></span></a>
@elseif($name === 'Exam PDF')
<a class="se26-menu-item" href="{{ route('student.exam-section.exam-pdf', ['category'=>$cat->id, 'subcategory_id'=>$sub?->id]) }}"><span>{{ $symbol }}</span><span>{{ $name }}<small>Routine অনুযায়ী প্রকাশিত পরীক্ষার PDF দেখুন</small></span></a>
@elseif($link)<a class="se26-menu-item" href="{{ $link }}"><span>{{ $symbol }}</span><span>{{ $name }}</span></a>
@else<div class="se26-menu-item"><span>{{ $symbol }}</span><span>{{ $name }}<small>Coming soon</small></span></div>@endif
@endforeach
</section>
<section class="se26-panel se26-progress"><h2 style="margin:0 0 21px;font-size:23px">My Exam Progress <span style="float:right;color:#1977cd">◔</span></h2><div class="se26-progress-grid"><div class="se26-ring"><div class="se26-ring-inner"><strong>{{ $progress }}%</strong><small style="color:#8895a7">Completed</small></div></div><div><div class="se26-progress-row"><span>Total Exams</span><b>{{ $total }}</b></div><div class="se26-progress-row"><span>Completed</span><b>{{ $finished }}</b></div><div class="se26-progress-row"><span>Remaining</span><b>{{ max(0,$total-$finished) }}</b></div></div></div><p class="se26-muted" style="margin-bottom:0;font-size:12px">এই হিসাবটি Routine Schedule অনুযায়ী শেষ হওয়া Exam-এর, শিক্ষার্থীর ব্যক্তিগত জমা দেওয়া পরীক্ষার সংখ্যা নয়।</p></section>

</main>
@endif
</div>

{{-- LEBD05: display-only client countdown. Exam admission remains server-controlled. --}}
<script>
(function () {
  function updateCountdown() {
    document.querySelectorAll('.lebd05-next-exam-countdown').forEach(function (box) {
      var start = Number(box.getAttribute('data-exam-start-ms'));
      var end = Number(box.getAttribute('data-exam-end-ms'));
      if (!start || !end) return;
      var now = Date.now();
      var isLive = now >= start && now < end;
      // Show the start label only before the exam begins; hide it as soon as it goes live.
      var startLabel = box.closest('.se26-panel')?.querySelector('.lebd05-feature-start-time');
      if (startLabel) startLabel.style.display = now >= start ? 'none' : '';
      var target = now < start ? start : end;
      var left = Math.max(0, Math.ceil((target - now) / 1000));
      box.classList.toggle('is-finished', now >= end);
      var phase = box.querySelector('.lebd05-countdown-phase');
      var topicTitle = document.querySelector('[data-topic-title-upcoming][data-topic-title-live]');
      if (topicTitle) topicTitle.textContent = isLive ? topicTitle.getAttribute('data-topic-title-live') : topicTitle.getAttribute('data-topic-title-upcoming');
      if (phase) phase.textContent = isLive ? phase.getAttribute('data-live-text') : (now >= end ? 'পরীক্ষার সময় শেষ' : phase.getAttribute('data-upcoming-text'));
      box.setAttribute('aria-label', isLive ? 'পরীক্ষা শেষ হতে বাকি সময়' : 'পরীক্ষা শুরু হতে বাকি সময়');
      var values = {days: Math.floor(left / 86400), hours: Math.floor((left % 86400) / 3600), minutes: Math.floor((left % 3600) / 60), seconds: left % 60};
      Object.keys(values).forEach(function (unit) {
        var el = box.querySelector('[data-unit="' + unit + '"]');
        if (el) el.textContent = String(values[unit]).padStart(2, '0');
      });
    });
    document.querySelectorAll('.lebd05-live-existing-link').forEach(function (link) {
      var end = Number(link.getAttribute('data-end-ms'));
      if (end && Date.now() >= end) link.hidden = true;
    });
    document.querySelectorAll('.lebd05-live-start-link').forEach(function (link) {
       var start = Number(link.getAttribute('data-start-ms'));
       var end = Number(link.getAttribute('data-end-ms'));
       var isLive = start > 0 && end > start && Date.now() >= start && Date.now() < end;
       link.hidden = !isLive;
       var lock = link.parentElement.querySelector('.lebd05-locked-action');
       if (lock) lock.hidden = isLive;
    });
  }
  updateCountdown();
  setInterval(updateCountdown, 1000);
})();
</script>
@endsection
