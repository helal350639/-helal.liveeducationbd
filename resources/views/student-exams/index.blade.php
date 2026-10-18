@php
/*
|--------------------------------------------------------------------------
| LIVE EDUCATION BD
| COMMON STUDENT EXAM PAGE
|--------------------------------------------------------------------------
| Course / Batch / Department Based Exam Section
| Routine + Question Paper + Topic + Result Links
|--------------------------------------------------------------------------
*/

$currentCourse = $course ?? null;
$currentBatch = $batch ?? null;
$currentDepartment = $department ?? null;

$pageTitle = $examTitle ?? 'Exam Section';

/*
|--------------------------------------------------------------------------
| REAL UPCOMING EXAM DATA
|--------------------------------------------------------------------------
*/

$currentUpcoming = $upcomingExam ?? null;

$isDemo = app()->environment('local')
    && request()->boolean('exam_preview');

/*
|--------------------------------------------------------------------------
| LOAD DATA WHEN CONTROLLER HAS NOT PASSED IT
|--------------------------------------------------------------------------
*/

if (
    !$isDemo &&
    $currentUpcoming === null &&
    ($currentCourse || $currentBatch)
) {

    $selectedCourseId = $currentCourse?->id
        ?? $currentBatch?->course_id;

    if (
        $selectedCourseId &&
        class_exists(\App\Services\StudentUpcomingExam::class)
    ) {

        $currentUpcoming = \App\Services\StudentUpcomingExam::get(
            (int) $selectedCourseId,
            $currentBatch ? (int) $currentBatch->id : null,
            $currentDepartment ? (int) $currentDepartment->id : null
        );
    }
}

/*
|--------------------------------------------------------------------------
| LOCAL DESIGN PREVIEW
|--------------------------------------------------------------------------
*/

if ($isDemo) {

    $currentUpcoming = [
        'exam_no' => 'Exam-01',
        'exam_at' => '2026-10-16 15:06:00',

        'topic_rows' => [
            [
                'type' => 'Department',
                'name' => 'Department 2',
                'details' => 'Department Topic'
            ],
            [
                'type' => 'Subject',
                'name' => 'বাংলা',
                'details' => 'সমাস, সন্ধি, কারক ও বিভক্তি'
            ],
            [
                'type' => 'Subject',
                'name' => 'English',
                'details' => 'Grammar & Vocabulary'
            ],
            [
                'type' => 'Subject',
                'name' => 'বাংলাদেশ',
                'details' => 'ইতিহাস ও মুক্তিযুদ্ধ'
            ]
        ],

        'is_live' => false,
        'take_url' => null,
        'refresh_at' => null
    ];
}

/*
|--------------------------------------------------------------------------
| EXAM DETAILS
|--------------------------------------------------------------------------
*/

$hasUpcoming = !empty($currentUpcoming);

$upcomingDate = $hasUpcoming
    ? data_get(
        $currentUpcoming,
        'exam_at',
        data_get($currentUpcoming, 'date')
    )
    : null;

$examDate = $upcomingDate
    ? \Carbon\Carbon::parse(
        $upcomingDate,
        'Asia/Dhaka'
    )->format('d M Y')
    : '—';

$examTime = $upcomingDate
    ? \Carbon\Carbon::parse(
        $upcomingDate,
        'Asia/Dhaka'
    )->format('h:i A')
    : '—';

$examDay = $upcomingDate
    ? \Carbon\Carbon::parse(
        $upcomingDate,
        'Asia/Dhaka'
    )->format('l')
    : '';

$examNo = $hasUpcoming
    ? data_get($currentUpcoming, 'exam_no', '—')
    : '—';

$examTopic = $hasUpcoming
    ? data_get(
        $currentUpcoming,
        'topic',
        'রুটিনে পরীক্ষার Topic দেওয়া হয়নি।'
    )
    : 'পরবর্তী পরীক্ষার রুটিন এখনো সংযুক্ত হয়নি।';

/*
|--------------------------------------------------------------------------
| DEPARTMENT + SUBJECT TOPIC ROWS
|--------------------------------------------------------------------------
*/

$topicRows = data_get(
    $currentUpcoming,
    'topic_rows',
    []
);

if ($topicRows instanceof \Illuminate\Support\Collection) {
    $topicRows = $topicRows->all();
}

$topicRows = is_array($topicRows)
    ? array_values($topicRows)
    : [];

/*
|--------------------------------------------------------------------------
| SUPPORT OLDER SERVICE DATA
|--------------------------------------------------------------------------
| If the Service only provides a combined Topic string,
| show it as a table until topic_rows is available.
|--------------------------------------------------------------------------
*/

if (
    !$topicRows &&
    $hasUpcoming &&
    is_string($examTopic) &&
    trim($examTopic) !== ''
) {

    $topicParts = array_values(
        array_filter(
            array_map(
                'trim',
                explode(' | ', $examTopic)
            )
        )
    );

    foreach ($topicParts as $index => $part) {

        $separator = strpos($part, ':');

        if ($separator !== false) {

            $partName = trim(
                substr($part, 0, $separator)
            );

            $partDetails = trim(
                substr($part, $separator + 1)
            );

        } else {

            $partName = $index === 0
                ? ($currentDepartment?->name ?? 'Department / Subject')
                : 'Subject';

            $partDetails = $part;
        }

        $topicRows[] = [
            'type' => $index === 0 && $currentDepartment
                ? 'Department'
                : 'Subject',

            'name' => $partName ?: 'Subject',
            'details' => $partDetails ?: '—'
        ];
    }
}

/*
|--------------------------------------------------------------------------
| SEE MORE / SEE LESS
|--------------------------------------------------------------------------
*/

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
| AUTOMATIC EXAM CARD REFRESH
|--------------------------------------------------------------------------
*/

$examRefreshAt = $isDemo
    ? null
    : data_get(
        $currentUpcoming,
        'refresh_at'
    );

$serverTimeMs = \Carbon\Carbon::now(
    'Asia/Dhaka'
)->getTimestamp() * 1000;

/*
|--------------------------------------------------------------------------
| BATCH PROGRESS
|--------------------------------------------------------------------------
*/

$progressData = $progress ?? [];

$totalExams = max(
    0,
    (int) data_get(
        $progressData,
        'total_exams',
        0
    )
);

$completedExams = min(
    $totalExams,
    max(
        0,
        (int) data_get(
            $progressData,
            'completed_exams',
            0
        )
    )
);

$remainingExams = $totalExams - $completedExams;

$progressPercent = $totalExams > 0
    ? (int) round(
        ($completedExams / $totalExams) * 100
    )
    : 0;

/*
|--------------------------------------------------------------------------
| ROUTINE URL
|--------------------------------------------------------------------------
*/

$routineUrl = route(
    'students.exam.routine',
    [
        'title' => $pageTitle
    ],
    false
);

/*
|--------------------------------------------------------------------------
| RESULT URL
|--------------------------------------------------------------------------
*/

$selectedCourseId = $currentCourse?->id
    ?? $currentBatch?->course_id
    ?? null;

$resultUrl = null;

if ($selectedCourseId) {

    $resultUrl = route(
        'students.batch-result',

        array_filter([
            'course_id' => $selectedCourseId,
            'batch_id' => $currentBatch?->id,
            'department_id' => $currentDepartment?->id
        ], static fn ($value) => $value !== null),

        false
    );
}

/*
|--------------------------------------------------------------------------
| EXAM MENU - EXISTING 8 OPTIONS
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
    ]

];
@endphp

<!DOCTYPE html>
<html lang="bn">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="theme-color"
        content="#edf1f7"
    >

    <title>
        {{ $pageTitle }} | Live Education BD
    </title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        /* =====================================
           THEME
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
            --shadow: 0 5px 20px rgba(25,43,77,.045);
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
            --shadow: 0 5px 20px rgba(0,0,0,.12);
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

        button {
            font: inherit;
        }

        a,
        button {
            -webkit-tap-highlight-color: transparent;
        }

        button:focus-visible,
        a:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        /* =====================================
           MAIN PAGE
        ===================================== */

        .exam-page {
            width: 100%;
            max-width: 600px;
            min-height: 100vh;
            margin: 0 auto;
        }

        .exam-header {
            min-height: 62px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .exam-logo {
            flex: 1;
            text-align: center;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.25;
            color: var(--primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 35px;
            height: 35px;
            border: 0;
            border-radius: 10px;
            color: var(--text);
            background: transparent;
            text-decoration: none;
            cursor: pointer;
            font-size: 16px;
        }

        .icon-btn:hover {
            background: var(--primary-soft);
        }

        .exam-content {
            padding: 17px 14px 30px;
        }

        .exam-title {
            margin: 12px 0 8px;
            font-size: 22px;
            font-weight: 800;
            line-height: 1.4;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .title-info {
            color: var(--primary);
            font-size: 15px;
            margin-left: 4px;
        }

        .exam-context {
            color: var(--muted);
            text-align: center;
            font-size: 11px;
            line-height: 1.65;
            margin-bottom: 20px;
        }

        .exam-context span + span::before {
            content: ' · ';
        }

        /* =====================================
           YELLOW NOTICE
        ===================================== */

        .notice-box {
            background: var(--notice);
            color: #18253b;
            border-radius: 15px;
            padding: 13px 12px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 12px;
            line-height: 1.65;
        }

        :root[data-theme="dark"] .notice-box {
            color: #fff4cd;
        }

        .notice-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 31px;
            height: 31px;
            border-radius: 50%;
            background: rgba(255,255,255,.47);
            color: #15243d;
            font-size: 16px;
        }

        /* =====================================
           UPCOMING EXAM CARD
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
            margin-bottom: 12px;
        }

        .upcoming-head-left {
            display: flex;
            align-items: center;
            min-width: 0;
            gap: 9px;
        }

        .upcoming-head-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            border-radius: 11px;
            background: #e2f1ff;
            color: #0875e1;
            font-size: 18px;
        }

        .upcoming-head h2 {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.3;
        }

        .status-chip,
        .demo-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            border-radius: 30px;
            padding: 7px 9px;
            background: #e4f0ff;
            color: #076ad7;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .status-chip.upcoming,
        .demo-tag {
            color: #865e00;
            background: #ffecab;
        }

        .status-chip.live {
            color: #086c42;
            background: #ddf9e8;
        }

        /* =====================================
           EXAM NUMBER + DATE TIME
        ===================================== */

        .exam-number-date-bar {
            display: grid;
            grid-template-columns:
                minmax(0, 1fr)
                minmax(0, 1.35fr);
            align-items: center;
            gap: 10px;
            padding: 11px;
            margin-bottom: 10px;
            background: var(--primary-soft);
            border-radius: 13px;
        }

        .exam-number-side,
        .exam-datetime-side {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .exam-number-side {
            border-right: 1px solid #aacbec;
            padding-right: 8px;
        }

        .exam-number-icon,
        .exam-datetime-icon {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex-shrink: 0;
            background: #d1e9ff;
            color: #0875e1;
            border-radius: 10px;
            font-size: 15px;
        }

        .exam-number-copy,
        .exam-datetime-copy {
            min-width: 0;
        }

        .exam-field-label {
            display: block;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.4;
            margin-bottom: 3px;
        }

        .exam-number-text,
        .exam-datetime-text {
            display: block;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.35;
            color: var(--text);
            overflow-wrap: anywhere;
        }

        .exam-clock-text {
            display: block;
            margin-top: 3px;
            color: var(--primary);
            font-size: 11px;
            font-weight: 700;
        }

        .exam-day-text {
            color: var(--muted);
            font-weight: 400;
        }

        .pending-note {
            color: var(--muted);
            line-height: 1.7;
            padding: 11px 2px;
            font-size: 12px;
        }

        /* =====================================
           DEPARTMENT / SUBJECT TOPIC TABLE
        ===================================== */

        .upcoming-syllabus {
            border: 1px solid var(--border);
            border-radius: 11px;
            overflow: hidden;
            background: var(--surface);
            margin-bottom: 11px;
        }

        .upcoming-syllabus-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 11px;
        }

        .upcoming-syllabus-table th {
            color: #142d4d;
            background: #e7f1ff;
            text-align: left;
            padding: 9px 10px;
            font-weight: 800;
        }

        .upcoming-syllabus-table th:first-child,
        .upcoming-syllabus-table td:first-child {
            width: 37%;
        }

        .upcoming-syllabus-table td {
            color: var(--text);
            background: var(--surface);
            border-top: 1px solid var(--border);
            padding: 9px 10px;
            vertical-align: top;
            line-height: 1.55;
        }

        .upcoming-syllabus-table tr[hidden] {
            display: none !important;
        }

        .syllabus-line {
            display: block;
            overflow-wrap: anywhere;
        }

        .upcoming-syllabus-table.has-extra:not(.is-expanded)
        .syllabus-line {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .syllabus-type {
            display: block;
            margin-top: 2px;
            color: var(--primary);
            font-size: 9px;
        }

        /* =====================================
           ACTION BUTTONS
        ===================================== */

        .exam-actions {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .exam-actions.two-actions {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
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
            color: inherit;
        }

        .exam-action i {
            font-size: 12px;
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
            font-size: 15px;
            font-weight: 800;
            margin: 3px 1px 12px;
        }

        .exam-menu {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 11px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            min-height: 76px;
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 12px 11px;
            background: var(--surface);
            color: var(--text);
            text-decoration: none;
            text-align: left;
            box-shadow: var(--shadow);
            font-size: 12.5px;
            font-weight: 700;
            line-height: 1.45;
        }

        a.menu-item:hover {
            border-color: var(--primary);
            background: var(--primary-soft);
        }

        button.menu-item {
            width: 100%;
            cursor: default;
        }

        .menu-icon {
            display: grid;
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

        .menu-item.is-pending small {
            display: block;
            color: var(--muted);
            margin-top: 2px;
            font-size: 10px;
            font-weight: 400;
        }

        /* =====================================
           BATCH PROGRESS
        ===================================== */

        .progress-card {
            margin-top: 22px;
            padding: 19px;
        }

        .progress-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .progress-head h2 {
            margin: 0;
            font-size: 17px;
        }

        .progress-head i {
            color: var(--primary);
            font-size: 19px;
        }

        .progress-content {
            display: flex;
            align-items: center;
            gap: 17px;
            margin-top: 20px;
        }

        .progress-ring {
            width: 128px;
            height: 128px;
            flex: 0 0 128px;
            padding: 12px;
            border-radius: 50%;
            background: conic-gradient(
                var(--primary) 0% var(--progress),
                var(--border) var(--progress) 100%
            );
        }

        .progress-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
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
            display: flex;
            justify-content: space-between;
            gap: 8px;
            border-bottom: 1px solid var(--border);
            padding: 9px 0;
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
            color: var(--muted);
            text-align: center;
            font-size: 11px;
            line-height: 1.6;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 55px;
            padding: 13px 12px;
            border-radius: 14px;
            text-align: center;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.5;
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

        /* =====================================
           TOAST MESSAGE
        ===================================== */

        .toast {
            position: fixed;
            z-index: 100;
            bottom: 22px;
            left: 50%;
            transform: translateX(-50%);
            max-width: 90vw;
            display: none;
            padding: 12px 18px;
            border-radius: 12px;
            background: #15293e;
            color: #ffffff;
            text-align: center;
            font-size: 13px;
        }

        .toast.show {
            display: block;
        }

        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 480px) {

            .exam-content {
                padding: 15px 12px 26px;
            }

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

            .exam-number-date-bar {
                grid-template-columns:
                    minmax(0, 1fr)
                    minmax(0, 1.3fr);
                gap: 7px;
                padding: 9px;
            }

            .exam-number-side,
            .exam-datetime-side {
                gap: 6px;
            }

            .exam-number-icon,
            .exam-datetime-icon {
                width: 28px;
                height: 28px;
                font-size: 13px;
            }

            .exam-number-text {
                font-size: 12px;
            }

            .exam-datetime-text {
                font-size: 11px;
            }

            .exam-clock-text {
                font-size: 10px;
            }

            .upcoming-syllabus-table {
                font-size: 10px;
            }

            .upcoming-syllabus-table th,
            .upcoming-syllabus-table td {
                padding: 8px 7px;
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
        }

        /* =========================================
   COMPACT EXAM COUNTDOWN
========================================= */

.exam-countdown-inline {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 6px;
    width: 100%;
    min-width: 0;
    white-space: nowrap;
    font-size: 11px;
    line-height: 1.4;
    color: #0869d9;
}

.exam-countdown-inline > i {
    font-size: 12px;
    flex-shrink: 0;
    color: #0875e1;
}

.countdown-label {
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}

.countdown-values {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}

.count-item {
    display: inline-flex;
    align-items: baseline;
    gap: 1px;
    font-size: 10px;
    color: #475569;
}

.count-item strong {
    font-size: 12px;
    font-weight: 800;
    color: #0b59b0;
}

/* MOBILE SCREEN */

@media (max-width: 480px) {

    .exam-countdown-inline {
        gap: 3px;
        font-size: 9px;
    }

    .exam-countdown-inline > i {
        font-size: 10px;
    }

    .countdown-label {
        font-size: 9px;
    }

    .countdown-values {
        gap: 3px;
    }

    .count-item {
        font-size: 9px;
    }

    .count-item strong {
        font-size: 10px;
    }
}

/* EXTRA SMALL MOBILE */

@media (max-width: 360px) {

    .countdown-label {
        display: none;
    }

    .countdown-values {
        gap: 2px;
    }
}

/* UPCOMING ACTION BUTTONS */

.exam-actions.two-actions {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

/* LIVE: ONLY ONE BUTTON */

.exam-actions.only-live {
    grid-template-columns: minmax(0, 1fr) !important;
}

/* UPCOMING TAKE EXAM CLICKABLE */

#takeExamButton {
    cursor: pointer;
    pointer-events: auto;
    opacity: 1;
}

/* LIVE TAKE EXAM - GREEN BLINK */

.exam-action.live-exam-blink {
    width: 100%;
    min-height: 48px;
    background: #08a65a;
    color: #ffffff;
    border: 1px solid #078648;
    font-size: 15px;
    font-weight: 800;
    animation: liveExamBlink 1.5s ease-in-out infinite;
}

@keyframes liveExamBlink {
    0%, 100% {
        background: #08a65a;
        box-shadow: 0 0 0 0 rgba(8, 166, 90, .3);
    }

    50% {
        background: #078648;
        box-shadow: 0 0 0 7px rgba(8, 166, 90, 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .exam-action.live-exam-blink {
        animation: none;
    }
}
    </style>

</head>

<body>

<div class="exam-page">

    <!-- =====================================
         PAGE HEADER
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
                aria-label="Share this exam page"
                title="Share"
            >
                <i class="fa-solid fa-share-nodes"></i>
            </button>

            <button
                type="button"
                class="icon-btn"
                id="themeToggle"
                aria-label="Toggle dark mode"
                title="Dark mode"
            >
                <i
                    class="fa-solid fa-moon"
                    id="themeIcon"
                ></i>
            </button>

            <span
                class="icon-btn"
                title="Notifications"
                aria-label="Notifications"
            >
                <i class="fa-regular fa-bell"></i>
            </span>

        </div>

    </header>

    <!-- =====================================
         MAIN CONTENT
    ===================================== -->

    <main class="exam-content">

        <!-- EXAM TITLE -->

        <h1 class="exam-title">

            {{ $pageTitle }}

            <i
                class="fa-solid fa-circle-info title-info"
                title="Exam section"
                aria-hidden="true"
            ></i>

        </h1>

        <!-- COURSE / BATCH / DEPARTMENT -->

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
             LIVE EXAM NOTICE
        ===================================== -->

        <div class="notice-box">

            <span class="notice-icon">
                <i class="fa-solid fa-circle-info"></i>
            </span>

            <span>

                @if($examIsLive)

                    বর্তমানে লাইভ পরীক্ষা চলছে।
                    নিচের <strong>পরীক্ষা দিন</strong>
                    বাটন থেকে প্রবেশ করুন।

                @else

                    আজকে কোন লাইভ এক্সাম নেই।

                @endif

            </span>

        </div>

        <!-- =====================================
             UPCOMING EXAM CARD
        ===================================== -->

        <section
            class="upcoming-card"
            aria-labelledby="upcomingHeading"
        >

            <!-- HEADER -->

            <div class="upcoming-head">

                <div class="upcoming-head-left">

                    <span class="upcoming-head-icon">
                        <i class="fa-regular fa-file-lines"></i>
                    </span>

                        @if($examIsLive)
                         Now Exam Live
                        @else
                         Upcoming Exam
                        @endif
                        </h2>

                </div>

                <!-- EXAM STATUS -->

                @if($isDemo)

                    <span class="demo-tag">
                        DEMO PREVIEW
                    </span>

                @elseif($examIsLive)

                    <span class="status-chip live">
                        <i class="fa-solid fa-circle-play"></i>
                        Live Now
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

            @if($hasUpcoming)

                <!-- =====================================
                     EXAM NO + DATE & TIME
                ===================================== -->

                <div class="exam-number-date-bar">

                    <!-- LEFT: EXAM NO -->

                    <div class="exam-number-side">

                        <span class="exam-number-icon">
                            <i class="fa-solid fa-hashtag"></i>
                        </span>

                        <div class="exam-number-copy">

                            <span class="exam-field-label">
                                Exam No
                            </span>

                            <strong class="exam-number-text">
                                {{ $examNo }}
                            </strong>

                        </div>

                    </div>

                    <!-- RIGHT: EXAM DATE & TIME -->

                    <div class="exam-datetime-side">

                        <span class="exam-datetime-icon">
                            <i class="fa-regular fa-calendar-days"></i>
                        </span>

                        <div class="exam-datetime-copy">

                            <span class="exam-field-label">
                               Exam Start Date &amp; Time
                            </span>

                            <strong class="exam-datetime-text">
                                {{ $examDate }}
                            </strong>

                            <!-- EXAM START TIME & DAY -->

<span class="exam-clock-text">

    {{ $examTime }}

    @if($examDay)
        <span class="exam-day-text">
            · {{ $examDay }}
        </span>
    @endif

</span>

<!-- =====================================
     LIVE EXAM COUNTDOWN
===================================== -->

@if($hasUpcoming && !$examIsLive && $upcomingDate)

    <div id="examCountdown" class="exam-countdown-inline">

        <i class="fa-regular fa-clock"></i>

        <span class="countdown-label">
            Exam Starts In
        </span>

        <span class="countdown-values">
            <span class="count-item">
                <strong id="cdDays">00</strong>d
            </span>

            <span class="count-item">
                <strong id="cdHours">00</strong>h
            </span>

            <span class="count-item">
                <strong id="cdMinutes">00</strong>m
            </span>

            <span class="count-item">
                <strong id="cdSeconds">00</strong>s
            </span>
        </span>

    </div>

@endif

                            </span>

                        </div>

                    </div>

                </div>

                <!-- =====================================
                     DEPARTMENT & SUBJECT TOPIC TABLE
                ===================================== -->

                <div
                    class="upcoming-syllabus"
                    id="upcomingSyllabus"
                >

                    <table
                        id="upcomingSyllabusTable"
                        class="upcoming-syllabus-table {{ $hasExtraTopics ? 'has-extra' : '' }}"
                    >

                        <thead>

                            <tr>
                                <th>Department / Subject</th>
                                <th>Topic</th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse($topicRows as $index => $row)

                                <tr
                                    class="{{ $index >= 3 ? 'syllabus-extra' : '' }}"
                                    @if($index >= 3) hidden @endif
                                >

                                    <td>

                                        <span class="syllabus-line">
                                            {{ data_get($row, 'name', '—') }}
                                        </span>

                                        @if(
                                            data_get($row, 'type') === 'Department'
                                        )

                                            <small class="syllabus-type">
                                                Department
                                            </small>

                                        @endif

                                    </td>

                                    <td>

                                        <span class="syllabus-line">
                                            {{ data_get($row, 'details', '—') }}
                                        </span>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="2">
                                        {{ $examTopic }}
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            @else

                <p class="pending-note">

                    এই Course / Batch / Department-এর জন্য
                    এখন কোনো চলমান বা পরবর্তী পরীক্ষা পাওয়া যায়নি।
                    রুটিন প্রকাশিত হলে তথ্য এখানে দেখা যাবে।

                </p>

            @endif

           <!-- =====================================
     EXAM ACTION BUTTONS
===================================== -->

<div class="exam-actions {{ $examIsLive ? 'only-live' : ($hasExtraTopics ? '' : 'two-actions') }}">

    @if($examIsLive)

        <!-- =====================================
             LIVE EXAM - ONLY TAKE EXAM
        ===================================== -->

        @if($takeExamUrl)

            <a
                href="{{ $takeExamUrl }}"
                class="exam-action take live-exam-blink"
            >
                <i class="fa-solid fa-circle-play"></i>

                <span>পরীক্ষা দিন</span>

                <i class="fa-solid fa-arrow-right"></i>
            </a>

        @else

            <!-- Live but exam entry not available -->

            <button
                type="button"
                id="takeExamButton"
                data-mode="unavailable"
                class="exam-action take"
            >
                <i class="fa-solid fa-lock"></i>

                <span>পরীক্ষা দিন</span>
            </button>

        @endif

    @else

        <!-- =====================================
             UPCOMING EXAM - SEE MORE
        ===================================== -->

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

        <!-- =====================================
             UPCOMING EXAM - TAKE EXAM NOTICE
        ===================================== -->

        @if($hasUpcoming)

            <button
                type="button"
                id="takeExamButton"
                data-mode="upcoming"
                class="exam-action take"
            >
                <i class="fa-solid fa-file-pen"></i>

                <span>পরীক্ষা দিন</span>

                <i class="fa-solid fa-chevron-right"></i>
            </button>

        @else

            <button
                type="button"
                class="exam-action take"
                disabled
            >
                <i class="fa-solid fa-file-pen"></i>
                পরীক্ষা দিন
            </button>

        @endif

        <!-- =====================================
             UPCOMING EXAM - PREPARATION
        ===================================== -->

        <a
            href="{{ route('homepage.courses') }}"
            class="exam-action prepare"
        >
            <i class="fa-solid fa-circle-play"></i>

            <span>প্রস্তুতি নিন</span>

            <i class="fa-solid fa-chevron-right"></i>
        </a>

    @endif

</div>

        </section>

        <!-- =====================================
             EXAM MENU
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

                <!-- PROGRESS RING -->

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

                <!-- PROGRESS NUMBERS -->

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

<!-- TOAST -->

<div
    class="toast"
    id="toast"
    role="status"
    aria-live="polite"
></div>

<!-- =====================================
     JAVASCRIPT
===================================== -->

<script>

/*
|--------------------------------------------------------------------------
| SEE MORE / SEE LESS
|--------------------------------------------------------------------------
*/

function toggleUpcomingTopics(button) {

    const table = document.getElementById(
        'upcomingSyllabusTable'
    );

    if (!table) {
        return;
    }

    const expand =
        button.getAttribute('aria-expanded') !== 'true';

    const rows = table.querySelectorAll(
        '.syllabus-extra'
    );

    rows.forEach(function (row) {
        row.hidden = !expand;
    });

    table.classList.toggle(
        'is-expanded',
        expand
    );

    button.setAttribute(
        'aria-expanded',
        String(expand)
    );

    const label = button.querySelector(
        '.see-more-label'
    );

    if (label) {
        label.textContent = expand
            ? 'See Less'
            : 'See More';
    }

    const arrow = button.querySelector(
        '.fa-chevron-down, .fa-chevron-up'
    );

    if (arrow) {

        arrow.className = expand
            ? 'fa-solid fa-chevron-up'
            : 'fa-solid fa-chevron-down';

    }
}

/*
|--------------------------------------------------------------------------
| PAGE EVENTS
|--------------------------------------------------------------------------
*/

(function () {

    'use strict';

    const themeButton =
        document.getElementById('themeToggle');

    const themeIcon =
        document.getElementById('themeIcon');

    const shareButton =
        document.getElementById('sharePage');

    const backButton =
        document.getElementById('backButton');

    const toast =
        document.getElementById('toast');

    let toastTimeout;

    /*
    |--------------------------------------------------------------------------
    | TOAST
    |--------------------------------------------------------------------------
    */

    function showToast(message) {

        if (!toast) {
            return;
        }

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

        const savedTheme = localStorage.getItem(
            'lebdExamTheme'
        );

        applyTheme(
            savedTheme === 'dark' ? 'dark' : 'light'
        );

    } catch (error) {

        applyTheme('light');

    }

    if (themeButton) {

        themeButton.addEventListener(
            'click',
            function () {

                const mode =
                    document.documentElement.dataset.theme === 'dark'
                        ? 'light'
                        : 'dark';

                applyTheme(mode);

                try {

                    localStorage.setItem(
                        'lebdExamTheme',
                        mode
                    );

                } catch (error) {
                    // Ignore local storage errors.
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BACK BUTTON
    |--------------------------------------------------------------------------
    */

    if (backButton) {

        backButton.addEventListener(
            'click',
            function (event) {

                try {

                    if (
                        document.referrer &&
                        new URL(document.referrer).origin === location.origin
                    ) {

                        event.preventDefault();

                        history.back();
                    }

                } catch (error) {
                    // Dashboard link remains the fallback.
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | COPY LINK
    |--------------------------------------------------------------------------
    */

    async function copyLink() {

        try {

            if (
                navigator.clipboard &&
                window.isSecureContext
            ) {

                await navigator.clipboard.writeText(
                    location.href
                );

            } else {

                const field = document.createElement(
                    'textarea'
                );

                field.value = location.href;

                field.style.position = 'fixed';

                field.style.opacity = '0';

                document.body.appendChild(field);

                field.select();

                const copied = document.execCommand(
                    'copy'
                );

                field.remove();

                if (!copied) {
                    throw new Error('Copy failed');
                }
            }

            showToast('পেজের লিংক কপি হয়েছে');

        } catch (error) {

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

        shareButton.addEventListener(
            'click',
            async function () {

                if (navigator.share) {

                    try {

                        await navigator.share({
                            title: document.title,
                            url: location.href
                        });

                        return;

                    } catch (error) {

                        if (error.name === 'AbortError') {
                            return;
                        }
                    }
                }

                await copyLink();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO REFRESH ON EXAM START / END
    |--------------------------------------------------------------------------
    */

    const refreshAt = @json($examRefreshAt);

    const serverTimeAtLoad = @json($serverTimeMs);

    if (refreshAt) {

        const targetMs = Date.parse(refreshAt);

        if (Number.isFinite(targetMs)) {

            const initialTick = performance.now();

            let refreshed = false;

            function updateExamCard() {

                if (refreshed) {
                    return;
                }

                const serverNow =
                    serverTimeAtLoad +
                    (performance.now() - initialTick);

                if (serverNow >= targetMs) {

                    refreshed = true;

                    location.reload();
                }
            }

            updateExamCard();

            setInterval(
                updateExamCard,
                1000
            );

            document.addEventListener(
                'visibilitychange',
                function () {

                    if (!document.hidden) {
                        updateExamCard();
                    }
                }
            );
        }
    }

})();

</script>
<script>
(function () {
    'use strict';

    const box = document.getElementById('examCountdown');

    if (!box) return;

    // Exam start date with Bangladesh timezone.
    const examStart = @json(
        $upcomingDate
            ? \Carbon\Carbon::parse(
                $upcomingDate,
                'Asia/Dhaka'
              )->toIso8601String()
            : null
    );

    // Server time when page was generated.
    const serverTime = @json($serverTimeMs);

    const target = Date.parse(examStart);
    const startTick = performance.now();

    if (!Number.isFinite(target)) return;

    let wasFuture = false;
    let reloading = false;

    function updateCountdown() {

        const now = serverTime +
            (performance.now() - startTick);

        const remaining = target - now;

        if (remaining <= 0) {

            box.hidden = true;

            // Refresh only when countdown actually
            // crosses the exam start time.
            if (wasFuture && !reloading) {
                reloading = true;
                window.location.reload();
            }

            return;
        }

        wasFuture = true;

        const totalSeconds = Math.floor(
            remaining / 1000
        );

        const days = Math.floor(
            totalSeconds / 86400
        );

        const hours = Math.floor(
            (totalSeconds % 86400) / 3600
        );

        const minutes = Math.floor(
            (totalSeconds % 3600) / 60
        );

        const seconds = totalSeconds % 60;

        function setValue(id, value) {
            const el = document.getElementById(id);

            if (el) {
                el.textContent = String(value)
                    .padStart(2, '0');
            }
        }

        setValue('cdDays', days);
        setValue('cdHours', hours);
        setValue('cdMinutes', minutes);
        setValue('cdSeconds', seconds);
    }

    updateCountdown();

    setInterval(updateCountdown, 1000);

    document.addEventListener(
        'visibilitychange',
        function () {
            if (!document.hidden) {
                updateCountdown();
            }
        }
    );

})();
</script>
<script>
(function () {
    'use strict';

    const button = document.getElementById('takeExamButton');

    if (!button) return;

    const mode = button.dataset.mode;

    const availableAt = @json(
        data_get($currentUpcoming, 'available_at')
        ?: (
            $upcomingDate
                ? \Carbon\Carbon::parse(
                    $upcomingDate,
                    'Asia/Dhaka'
                )->toIso8601String()
                : null
        )
    );

    const target = Date.parse(availableAt);

    const serverTime = @json($serverTimeMs);

    const startTick = performance.now();

    button.addEventListener('click', function () {

        // Exam card is live but paper access is unavailable
        if (mode === 'unavailable') {

            alert(
                'পরীক্ষাটি লাইভ রয়েছে, কিন্তু Question Paper-এর ' +
                'প্রবেশের অনুমতি অথবা সময়সীমা অনুযায়ী ' +
                'এখন পরীক্ষা দেওয়া যাচ্ছে না।'
            );

            return;
        }

        if (!Number.isFinite(target)) {

            alert('পরীক্ষার শুরুর সময় পাওয়া যায়নি।');

            return;
        }

        const now = serverTime +
            (performance.now() - startTick);

        const remaining = Math.max(
            0,
            Math.ceil((target - now) / 1000)
        );

        // Check updated exam status with Laravel
        if (remaining <= 0) {

            window.location.reload();

            return;
        }

        // Open existing Countdown Notice
        if (
            typeof window.openUpcomingExamNotice === 'function' &&
            document.getElementById('examStartNotice')
        ) {

            window.openUpcomingExamNotice();

            return;
        }

        // Fallback countdown notice
        const days = Math.floor(remaining / 86400);

        const hours = Math.floor(
            (remaining % 86400) / 3600
        );

        const minutes = Math.floor(
            (remaining % 3600) / 60
        );

        const seconds = remaining % 60;

        alert(
            'পরীক্ষা এখনো শুরু হয়নি।\n\n' +
            'পরীক্ষা শুরু হতে বাকি:\n\n' +
            days + ' দিন ' +
            hours + ' ঘণ্টা ' +
            minutes + ' মিনিট ' +
            seconds + ' সেকেন্ড'
        );
    });

})();
</script>

</body>
</html>