<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#edf1f7">

    <title>{{ $examTitle ?? 'Exam Section' }} | Live Education BD</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* =====================================
           LEBD COMMON EXAM PAGE - FULL DESIGN
        ===================================== */

        :root {
            --bg: #edf1f7;
            --surface: #ffffff;
            --text: #172033;
            --muted: #6c788c;
            --primary: #0875e1;
            --primary-soft: #e7f1ff;
            --border: #e4eaf3;
            --notice: #ffe38a;
            --shadow: 0 5px 20px rgba(25, 43, 77, .045);
        }

        :root[data-theme="dark"] {
            --bg: #101827;
            --surface: #1b273a;
            --text: #f0f4fb;
            --muted: #abb7ca;
            --primary: #74b5ff;
            --primary-soft: #253f61;
            --border: #324258;
            --notice: #68571d;
            --shadow: 0 5px 20px rgba(0, 0, 0, .12);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, 'Noto Sans Bengali', sans-serif;
        }

        button,
        a {
            -webkit-tap-highlight-color: transparent;
        }

        button {
            font: inherit;
        }

        .exam-page {
            max-width: 600px;
            min-height: 100vh;
            margin: 0 auto;
        }

        /* =====================================
           HEADER
        ===================================== */

        .exam-header {
            min-height: 64px;
            background: var(--surface);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border);
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 11px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            background: transparent;
            color: var(--text);
            text-decoration: none;
            cursor: pointer;
            font-size: 17px;
            flex-shrink: 0;
        }

        .icon-btn:hover,
        .icon-btn:focus-visible {
            background: var(--primary-soft);
            outline: none;
        }

        .exam-logo {
            flex: 1;
            text-align: center;
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.25;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1px;
        }

        .exam-content {
            padding: 18px 14px 30px;
        }

        .exam-title {
            font-size: 22px;
            line-height: 1.45;
            font-weight: 800;
            text-align: center;
            margin: 12px 0 9px;
            overflow-wrap: anywhere;
        }

        .title-info {
            font-size: 16px;
            color: var(--primary);
            margin-left: 5px;
        }

        .exam-context {
            color: var(--muted);
            text-align: center;
            font-size: 12px;
            margin-bottom: 22px;
            line-height: 1.6;
        }

        .exam-context span + span::before {
            content: ' • ';
        }

        /* =====================================
           YELLOW LIVE EXAM NOTICE
        ===================================== */

        .notice-box {
            background: var(--notice);
            color: #18253b;
            border-radius: 15px;
            padding: 14px 13px;
            margin-bottom: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 11px;
            font-size: 12px;
            line-height: 1.65;
        }

        :root[data-theme="dark"] .notice-box {
            color: #fff4cd;
        }

        .notice-icon {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: rgba(255,255,255,.45);
            color: #15243d;
            font-size: 17px;
        }

        /* =====================================
           COMPACT UPCOMING EXAM CARD
        ===================================== */

        .upcoming-card,
        .progress-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 17px;
            box-shadow: var(--shadow);
        }

        .upcoming-card {
            padding: 15px;
            margin-bottom: 20px;
        }

        .upcoming-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 13px;
        }

        .upcoming-head-left {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .upcoming-head-icon {
            width: 39px;
            height: 39px;
            flex-shrink: 0;
            border-radius: 11px;
            background: #e2f1ff;
            color: #0875e1;
            display: grid;
            place-items: center;
            font-size: 19px;
        }

        .upcoming-head h2 {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.3;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 7px 9px;
            border-radius: 30px;
            background: #e4f0ff;
            color: #076ad7;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .status-chip.upcoming {
            background: #ffecab;
            color: #865e00;
        }

        .status-chip.live {
            background: #ddf9e8;
            color: #086c42;
        }

        .demo-tag {
            padding: 5px 8px;
            border-radius: 20px;
            background: #fff1c7;
            color: #8d6300;
            font-size: 10px;
            font-weight: 800;
        }

        /* =====================================
           EXAM DATE | DURATION | TOTAL MARKS
           ALL THREE IN ONE ROW
        ===================================== */

        .exam-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            padding: 8px 5px;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 11px;
        }

        .exam-stat {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
            padding: 5px 7px;
        }

        .exam-stat + .exam-stat {
            border-left: 1px solid var(--border);
        }

        .stat-icon {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            border-radius: 9px;
            font-size: 14px;
        }

        .stat-icon.green {
            background: #dcfaeb;
            color: #05965c;
        }

        .stat-icon.orange {
            background: #fff0e5;
            color: #f0751b;
        }

        .stat-icon.purple {
            background: #f0e6ff;
            color: #8654ea;
        }

        .stat-info {
            min-width: 0;
        }

        .stat-label {
            display: block;
            color: var(--muted);
            font-size: 10px;
            margin-bottom: 4px;
        }

        .stat-value {
            display: block;
            color: var(--text);
            font-size: 11px;
            font-weight: 800;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .stat-day {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 9px;
        }

        /* =====================================
           EXAM NUMBER + TOPIC
        ===================================== */

        .exam-topic-panel {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px;
            border-radius: 12px;
            background: var(--primary-soft);
            margin-bottom: 11px;
        }

        .exam-no-box {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 105px;
            min-width: 0;
            border-right: 1px solid #aacbec;
            padding-right: 9px;
        }

        .exam-no-icon {
            width: 31px;
            height: 31px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            background: #d1e9ff;
            border-radius: 9px;
            color: #0875e1;
            font-size: 15px;
        }

        .exam-no-label {
            display: block;
            font-size: 10px;
            color: var(--muted);
            margin-bottom: 3px;
        }

        .exam-no-value {
            display: block;
            font-size: 17px;
            font-weight: 800;
            color: var(--text);
        }

        .topic-area {
            flex: 1;
            min-width: 0;
        }

        .topic-badge {
            display: inline-block;
            background: #1683f2;
            color: #ffffff;
            border-radius: 7px;
            padding: 4px 10px;
            font-weight: 800;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .topic-text {
            margin: 0;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.6;
            color: var(--text);
            overflow-wrap: anywhere;
        }

        /* =====================================
           THREE ACTION BUTTONS
        ===================================== */

        .exam-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .exam-action {
            min-height: 42px;
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 5px;
            border: 1px solid transparent;
            border-radius: 11px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.35;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            font-family: inherit;
        }

        .exam-action i {
            font-size: 13px;
            flex-shrink: 0;
        }

        .exam-action.more {
            background: #eaf5ff;
            color: #0869d2;
            border-color: #cee6ff;
        }

        .exam-action.take {
            background: #fff2df;
            color: #a9560a;
            border-color: #ffdfb4;
        }

        .exam-action.prepare {
            background: #e5faed;
            color: #087747;
            border-color: #c3efd3;
        }

        .exam-action:hover:not(:disabled) {
            filter: brightness(.97);
        }

        .exam-action:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        /* =====================================
           EXAM MENU
        ===================================== */

        .menu-caption {
            margin: 2px 1px 12px;
            font-size: 15px;
            font-weight: 800;
        }

        .exam-menu {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 11px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            min-width: 0;
            min-height: 76px;
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 12px 11px;
            box-shadow: var(--shadow);
            font-size: 12.5px;
            font-weight: 700;
            line-height: 1.45;
            text-align: left;
            text-decoration: none;
        }

        a.menu-item:hover,
        a.menu-item:focus-visible {
            border-color: var(--primary);
            background: var(--primary-soft);
        }

        button.menu-item {
            cursor: default;
        }

        .menu-icon {
            display: inline-grid;
            place-items: center;
            flex-shrink: 0;
            width: 39px;
            height: 39px;
            border-radius: 12px;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 18px;
        }

        .menu-label {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .menu-item.is-pending {
            opacity: .8;
        }

        .menu-item.is-pending .menu-label small {
            display: block;
            font-size: 10px;
            font-weight: 400;
            color: var(--muted);
            margin-top: 2px;
        }

        /* =====================================
           MY BATCH PROGRESS
        ===================================== */

        .progress-card {
            margin-top: 22px;
            padding: 19px;
        }

        .progress-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .progress-head h2 {
            margin: 0;
            font-size: 17px;
        }

        .progress-head i {
            font-size: 19px;
            color: var(--primary);
        }

        .progress-content {
            margin-top: 21px;
            display: flex;
            align-items: center;
            gap: 17px;
        }

        .progress-ring {
            width: 128px;
            height: 128px;
            padding: 12px;
            flex: 0 0 128px;
            border-radius: 50%;
            background: conic-gradient(
                var(--primary) 0% var(--progress),
                var(--border) var(--progress) 100%
            );
        }

        .progress-inner {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background: var(--surface);
        }

        .progress-inner strong {
            color: var(--primary);
            font-size: 26px;
        }

        .progress-inner small {
            color: var(--muted);
            font-size: 10px;
        }

        .progress-stats {
            flex: 1;
            min-width: 0;
        }

        .progress-stats div {
            padding: 9px 0;
            display: flex;
            justify-content: space-between;
            gap: 8px;
            border-bottom: 1px solid var(--border);
            font-size: 12px;
        }

        .progress-stats div:last-child {
            border-bottom: 0;
        }

        .progress-stats strong {
            color: var(--primary);
        }

        .progress-note {
            margin: 16px 0 0;
            text-align: center;
            font-size: 11px;
            line-height: 1.6;
            color: var(--muted);
        }

        /* =====================================
           BOTTOM COURSE LINKS
        ===================================== */

        .bottom-links {
            display: grid;
            gap: 10px;
            margin-top: 22px;
        }

        .bottom-link {
            min-height: 55px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            border-radius: 14px;
            padding: 13px 12px;
            font-size: 13px;
            line-height: 1.5;
            text-align: center;
            font-weight: 800;
            text-decoration: none;
        }

        .bottom-link.primary {
            background: var(--primary-soft);
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .bottom-link.secondary {
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .bottom-link:hover {
            filter: brightness(.97);
        }

        /* =====================================
           TOAST
        ===================================== */

        .toast {
            position: fixed;
            bottom: 22px;
            left: 50%;
            transform: translateX(-50%);
            background: #15293e;
            color: white;
            font-size: 13px;
            padding: 12px 18px;
            border-radius: 12px;
            display: none;
            z-index: 10;
            max-width: 90vw;
            text-align: center;
        }

        .toast.show {
            display: block;
        }

        /* =====================================
           MOBILE RESPONSIVE
        ===================================== */

        @media (max-width: 480px) {

            .upcoming-card {
                padding: 12px;
            }

            .upcoming-head h2 {
                font-size: 16px;
            }

            .status-chip {
                font-size: 9px;
                padding: 6px 7px;
            }

            .exam-stats {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                padding: 7px 3px;
            }

            .exam-stat {
                gap: 5px;
                padding: 4px;
            }

            .stat-icon {
                width: 26px;
                height: 26px;
                font-size: 12px;
            }

            .stat-label {
                font-size: 9px;
            }

            .stat-value {
                font-size: 10px;
            }

            .exam-no-box {
                flex-basis: 88px;
                gap: 6px;
                padding-right: 6px;
            }

            .exam-no-icon {
                width: 27px;
                height: 27px;
                font-size: 12px;
            }

            .exam-topic-panel {
                gap: 8px;
                padding: 10px;
            }

            .exam-actions {
                gap: 6px;
            }

            .exam-action {
                font-size: 10px;
                gap: 5px;
                min-height: 40px;
            }
        }

        @media (max-width: 380px) {

            .exam-header {
                padding: 9px;
                gap: 3px;
            }

            .icon-btn {
                width: 32px;
                height: 32px;
                font-size: 15px;
            }

            .exam-logo {
                font-size: 13px;
            }

            .exam-title {
                font-size: 20px;
            }

            .exam-menu {
                gap: 9px;
            }

            .menu-item {
                padding: 10px 8px;
                gap: 7px;
                font-size: 11px;
            }

            .menu-icon {
                width: 32px;
                height: 32px;
                font-size: 16px;
            }

            .progress-card {
                padding: 15px 13px;
            }

            .progress-ring {
                width: 108px;
                height: 108px;
                flex-basis: 108px;
                padding: 10px;
            }

            .progress-content {
                gap: 11px;
            }

            .progress-stats div {
                font-size: 11px;
            }

            .stat-icon {
                width: 23px;
                height: 23px;
                font-size: 10px;
            }

            .stat-value {
                font-size: 9.5px;
            }

            .exam-no-box {
                flex-basis: 75px;
                gap: 4px;
            }

            .exam-no-icon {
                width: 23px;
                height: 23px;
                font-size: 11px;
            }

            .exam-action i {
                font-size: 11px;
            }
        }

        /* ======================================
   UPCOMING EXAM SYLLABUS TABLE
====================================== */

.upcoming-syllabus {
    margin-top: 10px;
    border: 1px solid #dbe7f5;
    border-radius: 11px;
    overflow: hidden;
    background: #ffffff;
}

.upcoming-syllabus-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    font-size: 11px;
}

.upcoming-syllabus-table th {
    background: #e7f1ff;
    color: #142d4d;
    text-align: left;
    padding: 9px 10px;
    font-weight: 800;
}

.upcoming-syllabus-table th:first-child,
.upcoming-syllabus-table td:first-child {
    width: 37%;
}

.upcoming-syllabus-table td {
    padding: 9px 10px;
    border-top: 1px solid #e5edf7;
    color: #23344d;
    vertical-align: top;
    line-height: 1.5;
}

.upcoming-syllabus-table tr[hidden] {
    display: none !important;
}

.syllabus-line {
    display: block;
    overflow-wrap: anywhere;
}

/* Compact one-line preview for 3 rows */
.upcoming-syllabus-table.has-extra:not(.is-expanded)
.syllabus-line {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.syllabus-type {
    display: block;
    color: #1683f2;
    font-size: 9px;
    margin-top: 2px;
}

/* 2 buttons when See More is unnecessary */
.exam-actions.two-actions {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

@media (max-width: 480px) {
    .upcoming-syllabus-table {
        font-size: 10px;
    }

    .upcoming-syllabus-table th,
    .upcoming-syllabus-table td {
        padding: 8px 7px;
    }
}

    </style>
</head>

<body>

@php
    /*
    |--------------------------------------------------------------------------
    | COURSE / BATCH / DEPARTMENT
    |--------------------------------------------------------------------------
    */

    $currentCourse = $course ?? null;
    $currentBatch = $batch ?? null;
    $currentDepartment = $department ?? null;

    /*
    |--------------------------------------------------------------------------
    | REAL EXAM DATA
    |--------------------------------------------------------------------------
    */

    $currentUpcoming = $upcomingExam ?? null;
    $currentLive = $liveExam ?? null;

    /*
    |--------------------------------------------------------------------------
    | LOCAL DEMO PREVIEW
    |--------------------------------------------------------------------------
    | Add ?exam_preview=1 to the existing Exam Page URL.
    | Works only when APP_ENV=local.
    */

    $isDemo = app()->environment('local')
        && request()->boolean('exam_preview');

    if ($isDemo) {
        $currentUpcoming = [
            'exam_no' => '01',
            'exam_at' => '2026-10-16',
            'duration' => '1 hr 30 mins',
            'mark' => 200,
            'topic' => 'রুটিনের Topic থাকবে এখানে'
        ];
    }

    $hasUpcoming = !empty($currentUpcoming);

    $upcomingDate = $hasUpcoming
        ? data_get(
            $currentUpcoming,
            'exam_at',
            data_get($currentUpcoming, 'date')
        )
        : null;

    $examDate = $upcomingDate
        ? \Carbon\Carbon::parse($upcomingDate)->format('d M Y')
        : '—';

    $examDay = $upcomingDate
        ? \Carbon\Carbon::parse($upcomingDate)->format('l')
        : '';

    $examNo = $hasUpcoming
        ? data_get($currentUpcoming, 'exam_no', '—')
        : '—';

    $examDuration = $hasUpcoming
        ? data_get($currentUpcoming, 'duration', 'Not set')
        : 'Not set';

    $examMarks = $hasUpcoming
        ? data_get(
            $currentUpcoming,
            'mark',
            data_get($currentUpcoming, 'marks', '—')
        )
        : '—';

    $examTopic = $hasUpcoming
        ? data_get(
            $currentUpcoming,
            'topic',
            data_get(
                $currentUpcoming,
                'name',
                data_get(
                    $currentUpcoming,
                    'title',
                    'Topic not available'
                )
            )
        )
        : 'রুটিন সংযুক্ত হলে পরীক্ষার Topic এখানে দেখানো হবে।';

    /*
|--------------------------------------------------------------------------
| UPCOMING EXAM TOPIC ROWS
|--------------------------------------------------------------------------
*/

$topicRows = data_get(
    $currentUpcoming,
    'topic_rows',
    []
);

$topicRows = is_array($topicRows)
    ? $topicRows
    : [];

$hasExtraTopics = count($topicRows) > 3;


/*
|--------------------------------------------------------------------------
| LIVE EXAM BUTTON
|--------------------------------------------------------------------------
*/

$examIsLive = !$isDemo
    && $hasUpcoming
    && (bool) data_get(
        $currentUpcoming,
        'is_live',
        false
    );

$takeExamUrl = $examIsLive
    ? data_get($currentUpcoming, 'take_url')
    : null;


/*
|--------------------------------------------------------------------------
| AUTOMATIC REFRESH TIME
|--------------------------------------------------------------------------
*/

$examRefreshAt = $isDemo
    ? null
    : data_get(
        $currentUpcoming,
        'refresh_at'
    );

    /*
    |--------------------------------------------------------------------------
    | BATCH PROGRESS
    |--------------------------------------------------------------------------
    */

    $progressData = $progress ?? [];

    $totalExams = max(
        0,
        (int) ($progressData['total_exams'] ?? 0)
    );

    $completedExams = min(
        $totalExams,
        max(
            0,
            (int) ($progressData['completed_exams'] ?? 0)
        )
    );

    $remainingExams = $totalExams - $completedExams;

    $progressPercent = $totalExams > 0
        ? round(($completedExams / $totalExams) * 100)
        : 0;

    /*
|--------------------------------------------------------------------------
| ROUTINE URL
|--------------------------------------------------------------------------
*/

$routineUrl = route('students.exam.routine', [
    'title' => $examTitle ?? 'Exam Routine'
], false);


/*
|--------------------------------------------------------------------------
| RESULT URL - SELECTED COURSE / BATCH / DEPARTMENT
|--------------------------------------------------------------------------
*/

// Get selected Course ID
$selectedCourseId = $currentCourse?->id
    ?? $currentBatch?->course_id
    ?? null;

// Default: No result link
$resultUrl = null;

// Generate filtered result link only when Course exists
if ($selectedCourseId) {

    $resultUrl = route('students.batch-result', array_filter([
        'course_id' => $selectedCourseId,
        'batch_id' => $currentBatch?->id,
        'department_id' => $currentDepartment?->id,
    ], static fn($value) => $value !== null), false);

}


/*
|--------------------------------------------------------------------------
| EXAM MENU
|--------------------------------------------------------------------------
*/

$menus = [
    [
        'label' => 'Routine',
        'icon' => 'fa-calendar-days',
        'url' => $routineUrl
    ],
    [
        'label' => 'Result',
        'icon' => 'fa-square-poll-vertical',
        'url' => $resultUrl
    ],
    [
        'label' => 'All Exam Paper',
        'icon' => 'fa-file-lines',
        'url' => null
    ],
    [
        'label' => 'Missing Exam List',
        'icon' => 'fa-calendar-xmark',
        'url' => null
    ],
    [
        'label' => 'Exam PDF',
        'icon' => 'fa-file-pdf',
        'url' => null
    ],
    [
        'label' => 'Merit List',
        'icon' => 'fa-medal',
        'url' => null
    ],
    [
        'label' => 'Wrong & Unanswered',
        'icon' => 'fa-circle-xmark',
        'url' => null
    ],
    [
        'label' => 'Complain',
        'icon' => 'fa-comment-dots',
        'url' => route('students.complain')
    ],
];

@endphp

<div class="exam-page">

    <!-- =====================================
         HEADER
    ===================================== -->

    <header class="exam-header">

        <a
            href="{{ route('students.dashboard') }}"
            class="icon-btn"
            id="backButton"
            aria-label="Back"
            title="Back"
        >
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <div class="exam-logo">
            Live Education BD
        </div>

        <div class="header-actions">

            <button
                type="button"
                class="icon-btn"
                id="sharePage"
                title="Share this exam page"
                aria-label="Share this exam page"
            >
                <i class="fa-solid fa-share-nodes"></i>
            </button>

            <button
                type="button"
                class="icon-btn"
                id="themeToggle"
                title="Toggle dark mode"
                aria-label="Toggle dark mode"
            >
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>

            <span
                class="icon-btn"
                aria-label="Notifications"
                title="Notifications"
            >
                <i class="fa-regular fa-bell"></i>
            </span>

        </div>
    </header>

    <main class="exam-content">

        <!-- =====================================
             EXAM TITLE
        ===================================== -->

        <h1 class="exam-title">
            {{ $examTitle ?? 'Exam Section' }}

            <i
                class="fa-solid fa-circle-info title-info"
                title="Exam section"
            ></i>
        </h1>

        <!-- Selected Course / Batch / Department -->
        <div class="exam-context">

            @if($currentCourse)
                <span>{{ $currentCourse->name }}</span>
            @endif

            @if($currentBatch)
                <span>{{ $currentBatch->name }}</span>
            @endif

            @if($currentDepartment)
                <span>{{ $currentDepartment->name }}</span>
            @endif

        </div>

        <!-- =====================================
             YELLOW NOTICE
        ===================================== -->

        <div class="notice-box">

            <span class="notice-icon">
                <i class="fa-solid fa-circle-info"></i>
            </span>

            <span>
                @if($currentLive)
                    বর্তমানে লাইভ পরীক্ষা উপলব্ধ।
                    বিস্তারিত পরীক্ষার তালিকা থেকে দেখুন।
                @else
                    বর্তমানে লাইভ পরীক্ষার তথ্য দেখানোর
                    ব্যবস্থা সংযুক্ত হয়নি।
                @endif
            </span>

        </div>

        <!-- =====================================
             COMPACT UPCOMING EXAM CARD
        ===================================== -->

        <section
            class="upcoming-card"
            aria-labelledby="upcomingHeading"
        >

            <!-- Heading + Status -->
            <div class="upcoming-head">

                <div class="upcoming-head-left">

                    <span class="upcoming-head-icon">
                        <i class="fa-regular fa-file-lines"></i>
                    </span>

                    <h2 id="upcomingHeading">
                        Upcoming Exam
                    </h2>

                </div>

                @if($isDemo)

                    <span class="demo-tag">
                        DEMO PREVIEW
                    </span>

                @elseif($currentLive)

                    <span class="status-chip live">
                        <i class="fa-solid fa-circle-play"></i>
                        Live Available
                    </span>

                @elseif($hasUpcoming && $upcomingDate)

                    <span class="status-chip upcoming">
                        <i class="fa-regular fa-clock"></i>
                        Upcoming
                    </span>

                @else

                    <span class="status-chip">
                        <i class="fa-regular fa-calendar"></i>
                        Schedule Pending
                    </span>

                @endif

            </div>

            <!-- =================================
                 DATE | DURATION | MARKS
            ================================= -->

            <div class="exam-stats">

                <!-- Exam Date -->
                <div class="exam-stat">

                    <span class="stat-icon green">
                        <i class="fa-regular fa-calendar-days"></i>
                    </span>

                    <div class="stat-info">

                        <span class="stat-label">
                            Exam Date
                        </span>

                        <strong class="stat-value">
                            {{ $examDate }}
                        </strong>

                        @if($examDay)
                            <span class="stat-day">
                                {{ $examDay }}
                            </span>
                        @endif

                    </div>

                </div>

                <!-- Duration -->
                <div class="exam-stat">

                    <span class="stat-icon orange">
                        <i class="fa-regular fa-clock"></i>
                    </span>

                    <div class="stat-info">

                        <span class="stat-label">
                            Duration
                        </span>

                        <strong class="stat-value">
                            {{ $examDuration }}
                        </strong>

                    </div>

                </div>

                <!-- Total Marks -->
                <div class="exam-stat">

                    <span class="stat-icon purple">
                        <i class="fa-regular fa-file-lines"></i>
                    </span>

                    <div class="stat-info">

                        <span class="stat-label">
                            Total Marks
                        </span>

                        <strong class="stat-value">
                            {{ $examMarks }}
                        </strong>

                    </div>

                </div>

            </div>

            <!-- =================================
                 EXAM NO + TOPIC
            ================================= -->

            <div class="exam-topic-panel">

                <div class="exam-no-box">

                    <span class="exam-no-icon">
                        <i class="fa-solid fa-hashtag"></i>
                    </span>

                    <div>

                        <span class="exam-no-label">
                            Exam No
                        </span>

                        <strong class="exam-no-value">
                            {{ $examNo }}
                        </strong>

                    </div>

                </div>

                <div class="topic-area">
                    <span class="topic-badge">
                     Subject &amp; Syllabus
                    </span>
                    </div>

            </div>

            <!-- =========================================
     UPCOMING EXAM SUBJECT & SYLLABUS
========================================= -->

<div
    id="upcomingSyllabus"
    class="upcoming-syllabus"
>
    <table
        class="upcoming-syllabus-table {{ $hasExtraTopics ? 'has-extra' : '' }}"
        id="upcomingSyllabusTable"
    >
        <thead>
            <tr>
                <th>Subject / Department</th>
                <th>Syllabus / Topic</th>
            </tr>
        </thead>

        <tbody>
            @if(count($topicRows) > 0)

                @foreach($topicRows as $index => $row)

                    <tr
                        class="{{ $index >= 3 ? 'syllabus-extra' : '' }}"
                        @if($index >= 3) hidden @endif
                    >
                        <td>
                            <span class="syllabus-line">
                                {{ $row['name'] }}
                            </span>

                            @if($row['type'] === 'Department')
                                <small class="syllabus-type">
                                    Department
                                </small>
                            @endif
                        </td>

                        <td>
                            <span class="syllabus-line">
                                {{ $row['details'] }}
                            </span>
                        </td>
                    </tr>

                @endforeach

            @else

                <tr>
                    <td colspan="2">
                        {{ $examTopic }}
                    </td>
                </tr>

            @endif
        </tbody>
    </table>
</div>

            <!-- =================================
                 ACTION BUTTONS
            ================================= -->

            <div class="exam-actions {{ $hasExtraTopics ? '' : 'two-actions' }}">

                <!-- See More: FULL ROUTINE PAGE -->
                <!-- See More: Only Upcoming Exam Topic -->
@if($hasExtraTopics)

    <button
        type="button"
        id="upcomingSeeMore"
        class="exam-action more"
        aria-expanded="false"
        aria-controls="upcomingSyllabus"
        onclick="toggleUpcomingTopics(this)"
    >
        <i class="fa-solid fa-book-open"></i>

        <span class="see-more-label">
            See More
        </span>

        <i class="fa-solid fa-chevron-down"></i>
    </button>

@endif

                <!-- Take Exam -->
                @if($examIsLive && $takeExamUrl)

                    <a
                        href="{{ $takeExamUrl }}"
                        class="exam-action take"
                    >
                        <i class="fa-solid fa-file-pen"></i>
                        পরীক্ষা দিন
                    </a>

                @else

                    <button
                        type="button"
                        class="exam-action take"
                        disabled
                        title="পরীক্ষা চালু হলে এখানে প্রবেশ করা যাবে"
                    >
                        <i class="fa-solid fa-file-pen"></i>
                        পরীক্ষা দিন
                    </button>

                @endif

                <!-- Preparation -->
                <a
                    href="{{ route('homepage.courses') }}"
                    class="exam-action prepare"
                >
                    <i class="fa-solid fa-circle-play"></i>
                    প্রস্তুতি নিন
                    <i class="fa-solid fa-chevron-right"></i>
                </a>

            </div>

        </section>

        <!-- =====================================
             EXAM MENU (ALL 8 ITEMS)
        ===================================== -->

        <h2 class="menu-caption">
            Exam Menu
        </h2>

        <nav
            class="exam-menu"
            aria-label="Exam menu"
        >

            @foreach($menus as $menu)

                @if($menu['url'])

                    <a
                        class="menu-item"
                        href="{{ $menu['url'] }}"
                    >

                        <span class="menu-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>

                        <span class="menu-label">
                            {{ $menu['label'] }}
                        </span>

                    </a>

                @else

                    <button
                        type="button"
                        class="menu-item is-pending"
                        disabled
                        title="এই ফিচারের সংযোগ পরবর্তী ধাপে করা হবে"
                    >

                        <span class="menu-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>

                        <span class="menu-label">
                            {{ $menu['label'] }}

                            <small>
                                Coming soon
                            </small>
                        </span>

                    </button>

                @endif

            @endforeach

        </nav>

        <!-- =====================================
             MY BATCH PROGRESS
        ===================================== -->

        <section
            class="progress-card"
            aria-labelledby="progressHeading"
        >

            <div class="progress-head">

                <h2 id="progressHeading">
                    My Batch Progress
                </h2>

                <i
                    class="fa-solid fa-chart-pie"
                    aria-hidden="true"
                ></i>

            </div>

            <div class="progress-content">

                <!-- Progress Ring -->
                <div
                    class="progress-ring"
                    style="--progress: {{ $progressPercent }}%;"
                    role="img"
                    aria-label="{{ $progressPercent }} percent completed"
                >

                    <div class="progress-inner">

                        <strong>
                            {{ $progressPercent }}%
                        </strong>

                        <small>
                            Completed
                        </small>

                    </div>

                </div>

                <!-- Progress Numbers -->
                <div class="progress-stats">

                    <div>
                        <span>Total Exams</span>
                        <strong>{{ $totalExams }}</strong>
                    </div>

                    <div>
                        <span>Completed</span>
                        <strong>{{ $completedExams }}</strong>
                    </div>

                    <div>
                        <span>Remaining</span>
                        <strong>{{ $remainingExams }}</strong>
                    </div>

                </div>

            </div>

            @if($totalExams === 0)

                <p class="progress-note">
                    নির্বাচিত ব্যাচের পরীক্ষার অগ্রগতির তথ্য
                    এখনো সংযুক্ত হয়নি।
                </p>

            @endif

        </section>

        <!-- =====================================
             BOTTOM COURSE LINKS
        ===================================== -->

        <div class="bottom-links">

            <a
                class="bottom-link primary"
                href="{{ route('homepage.running.batches') }}"
            >
                <i class="fa-solid fa-layer-group"></i>
                আমাদের চলমান কোর্স ও ব্যাচগুলো দেখুন
                <i class="fa-solid fa-arrow-right"></i>
            </a>

            <a
                class="bottom-link secondary"
                href="{{ route('homepage.courses') }}"
            >
                <i class="fa-solid fa-graduation-cap"></i>
                চলমান সকল কোর্স দেখুন
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

    </main>

</div>

<!-- Toast -->
<div
    class="toast"
    id="toast"
    role="status"
    aria-live="polite"
></div>

<script>
(function () {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const themeButton = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const shareButton = document.getElementById('sharePage');
    const backButton = document.getElementById('backButton');
    const toast = document.getElementById('toast');

    let toastTimeout;

    /*
    |--------------------------------------------------------------------------
    | TOAST MESSAGE
    |--------------------------------------------------------------------------
    */

    function showToast(message) {
        if (!toast) return;

        toast.textContent = message;
        toast.classList.add('show');

        clearTimeout(toastTimeout);

        toastTimeout = setTimeout(function () {
            toast.classList.remove('show');
        }, 3500);
    }

    /*
    |--------------------------------------------------------------------------
    | DARK MODE
    |--------------------------------------------------------------------------
    */

    function applyTheme(theme) {
        document.documentElement.dataset.theme = theme;

        if (themeIcon) {
            themeIcon.className = theme === 'dark'
                ? 'fa-solid fa-sun'
                : 'fa-solid fa-moon';
        }
    }

    try {
        const savedTheme = localStorage.getItem('lebdExamTheme');

        applyTheme(
            savedTheme === 'dark' ? 'dark' : 'light'
        );
    } catch (err) {
        applyTheme('light');
    }

    if (themeButton) {
        themeButton.addEventListener('click', function () {
            const newTheme =
                document.documentElement.dataset.theme === 'dark'
                    ? 'light'
                    : 'dark';

            applyTheme(newTheme);

            try {
                localStorage.setItem('lebdExamTheme', newTheme);
            } catch (err) {
                // Ignore storage errors.
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | BACK BUTTON
    |--------------------------------------------------------------------------
    */

    if (backButton) {
        backButton.addEventListener('click', function (event) {
            try {
                if (
                    document.referrer &&
                    new URL(document.referrer).origin === location.origin
                ) {
                    event.preventDefault();
                    history.back();
                }
            } catch (err) {
                // Default link will open student dashboard.
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | COPY PAGE LINK
    |--------------------------------------------------------------------------
    */

    async function copyLink() {
        try {
            if (
                navigator.clipboard &&
                window.isSecureContext
            ) {
                await navigator.clipboard.writeText(
                    window.location.href
                );
            } else {
                const field = document.createElement('textarea');

                field.value = window.location.href;
                field.style.position = 'fixed';
                field.style.opacity = '0';

                document.body.appendChild(field);
                field.select();

                const success = document.execCommand('copy');

                field.remove();

                if (!success) {
                    throw new Error('Copy failed');
                }
            }

            showToast('পেজের লিংক কপি হয়েছে');

        } catch (err) {
            showToast(
                'লিংক কপি করা যায়নি। Address Bar থেকে কপি করুন।'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHARE BUTTON
    |--------------------------------------------------------------------------
    */

    if (shareButton) {
        shareButton.addEventListener('click', async function () {

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: document.title,
                        url: window.location.href
                    });

                    return;
                } catch (err) {
                    if (err.name === 'AbortError') {
                        return;
                    }
                }
            }

            await copyLink();
        });
    }

})();
</script>

</body>
</html>