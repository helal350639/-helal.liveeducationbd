<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#edf1f7">

    <title>Exam Routine | {{ $examTitle ?? 'Live Education BD' }}</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --bg: #edf1f7;
            --card: #ffffff;
            --text: #172033;
            --muted: #718097;
            --line: #e4eaf3;
            --blue: #0875e1;
            --soft: #e9f3ff;
            --shadow: 0 5px 17px rgba(30, 51, 90, .045);
        }

        :root[data-theme="dark"] {
            --bg: #101827;
            --card: #1b273a;
            --text: #f0f4fb;
            --muted: #adbbd0;
            --line: #35445b;
            --blue: #85bbff;
            --soft: #263e5c;
            --shadow: 0 5px 17px rgba(0, 0, 0, .13);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, 'Noto Sans Bengali', sans-serif;
        }

        button, a {
            -webkit-tap-highlight-color: transparent;
        }

        button {
            font: inherit;
        }

        .routine-page {
            max-width: 600px;
            min-height: 100vh;
            margin: 0 auto;
        }

        .header {
            min-height: 64px;
            padding: 10px 14px;
            background: var(--card);
            border-bottom: 1px solid var(--line);
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .header-title {
            flex: 1;
            text-align: center;
            color: var(--blue);
            font-size: 16px;
            line-height: 1.3;
            font-weight: 800;
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            border: 0;
            border-radius: 11px;
            background: transparent;
            color: var(--text);
            cursor: pointer;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            font-size: 17px;
        }

        .icon-btn:hover,
        .icon-btn:focus-visible {
            background: var(--soft);
            outline: none;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 1px;
        }

        main {
            padding: 20px 14px 30px;
        }

        .page-title {
            margin: 3px 0 5px;
            text-align: center;
            font-size: 23px;
            line-height: 1.4;
            font-weight: 800;
        }

        .batch-title {
            margin: 0;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .demo-notice {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin: 19px 0 17px;
            padding: 13px 14px;
            border-radius: 13px;
            color: #6c4a00;
            background: #fff0c4;
            line-height: 1.65;
            font-size: 12px;
        }

        .demo-notice i {
            margin-top: 3px;
        }

        .toolbar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 0 0 20px;
        }

        .tool-btn {
            min-height: 44px;
            display: flex;
            gap: 9px;
            justify-content: center;
            align-items: center;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: var(--card);
            color: var(--text);
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
        }

        .tool-btn.primary {
            background: var(--soft);
            border-color: #bcd9fa;
            color: var(--blue);
        }

        .section-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 8px 1px 12px;
        }

        .section-row h2 {
            font-size: 17px;
            margin: 0;
        }

        .section-count {
            font-size: 12px;
            color: var(--muted);
        }

        .routine-card {
            padding: 17px;
            margin-bottom: 14px;
            background: var(--card);
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
            border-radius: 17px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .exam-num {
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .exam-name {
            font-size: 17px;
            line-height: 1.5;
            margin: 5px 0 0;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .status {
            white-space: nowrap;
            border-radius: 999px;
            padding: 7px 11px;
            font-size: 11px;
            font-weight: 800;
        }

        .status.upcoming {
            color: #065dbe;
            background: #e2efff;
        }

        .status.completed {
            color: #15744f;
            background: #e0f6eb;
        }

        .status.running {
            color: #905e00;
            background: #fff1c6;
        }

        .meta {
            margin: 16px 0 15px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 9px;
        }

        .meta-item {
            background: var(--bg);
            border-radius: 10px;
            padding: 10px 11px;
            min-width: 0;
        }

        .meta-item small {
            color: var(--muted);
            font-size: 10px;
            display: block;
            margin-bottom: 5px;
        }

        .meta-item strong {
            font-size: 12px;
            line-height: 1.5;
            overflow-wrap: anywhere;
            display: block;
        }

        .syllabus-title {
            display: flex;
            gap: 7px;
            align-items: center;
            font-size: 13px;
            font-weight: 800;
            margin: 0 0 9px;
        }

        .syllabus-title i {
            color: var(--blue);
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 12px;
            line-height: 1.55;
        }

        th, td {
            padding: 10px 11px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        th {
            background: var(--soft);
            color: var(--text);
            font-weight: 800;
        }

        th:first-child,
        td:first-child {
            width: 33%;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .more {
            border-top: 1px dashed var(--line);
            margin-top: 14px;
            padding-top: 12px;
        }

        .more summary {
            list-style: none;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: var(--blue);
            font-weight: 800;
        }

        .more summary::-webkit-details-marker {
            display: none;
        }

        .more summary i {
            transition: transform .2s;
        }

        .more[open] summary i {
            transform: rotate(180deg);
        }

        .more-body {
            margin: 12px 0 0;
            color: var(--muted);
            line-height: 1.65;
            font-size: 12px;
        }

        .bottom-links {
            display: grid;
            gap: 10px;
            margin-top: 23px;
        }

        .bottom-link {
            display: flex;
            min-height: 53px;
            justify-content: center;
            align-items: center;
            text-align: center;
            gap: 9px;
            padding: 12px;
            border-radius: 13px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.5;
        }

        .bottom-link.blue {
            background: var(--soft);
            color: var(--blue);
            border: 1px solid #c4dcfc;
        }

        .bottom-link.white {
            background: var(--card);
            color: var(--text);
            border: 1px solid var(--line);
        }

        .toast {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            max-width: 88vw;
            background: #17304e;
            color: white;
            padding: 12px 16px;
            border-radius: 11px;
            text-align: center;
            font-size: 12px;
            z-index: 100;
        }

        .toast.show {
            display: block;
        }

        @media (max-width: 380px) {
            .header {
                padding: 8px 9px;
                gap: 3px;
            }

            .header-title {
                font-size: 13px;
            }

            .icon-btn {
                width: 32px;
                height: 32px;
                flex-basis: 32px;
                font-size: 15px;
            }

            .routine-card {
                padding: 13px 11px;
            }

            .page-title {
                font-size: 21px;
            }

            .exam-name {
                font-size: 15px;
            }

            .meta-item {
                padding: 9px 8px;
            }

            th, td {
                padding: 9px 8px;
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 12mm;
            }

            :root,
            :root[data-theme="dark"] {
                --bg: #fff;
                --card: #fff;
                --text: #111;
                --muted: #555;
                --line: #ddd;
                --blue: #175cb0;
                --soft: #f1f5fb;
                --shadow: none;
            }

            body {
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .routine-page {
                max-width: 100%;
            }

            .header {
                justify-content: center;
                border-bottom: 1px solid #ddd;
                padding: 0 0 10px;
            }

            .header-title {
                font-size: 18px;
            }

            .header .icon-btn,
            .actions,
            .toolbar,
            .demo-notice,
            .bottom-links,
            .toast {
                display: none !important;
            }

            main {
                padding: 14px 0 0;
            }

            .routine-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .more summary {
                display: none;
            }

            .more {
                border-top: 1px solid #ddd;
            }

            .more-body {
                color: #333;
            }
        }
    </style>
</head>

<body>
@php
    /*
    |--------------------------------------------------------------------------
    | Routine Design Preview
    |--------------------------------------------------------------------------
    | These are sample exams only.
    | Database integration will be added later.
    */

    $demoExams = [
        [
            'number' => '01',
            'name' => 'বাংলা ভাষা ও সাহিত্য',
            'status' => 'upcoming',
            'status_text' => 'Upcoming',
            'date' => '15 Oct 2026',
            'time' => '10:00 AM - 11:00 AM',
            'subjects' => [
                [
                    'name' => 'বাংলা ব্যাকরণ',
                    'syllabus' => 'সমাস, সন্ধি, কারক ও বিভক্তি'
                ],
                [
                    'name' => 'বাংলা সাহিত্য',
                    'syllabus' => 'প্রাচীন ও আধুনিক বাংলা সাহিত্য'
                ],
            ],
            'details' => 'MCQ পরীক্ষার ডেমো বিবরণ। পরীক্ষার সময় ও নিয়ম পরে বিদ্যমান রুটিন থেকে আসবে।'
        ],
        [
            'number' => '02',
            'name' => 'English Grammar & Vocabulary',
            'status' => 'upcoming',
            'status_text' => 'Upcoming',
            'date' => '18 Oct 2026',
            'time' => '10:00 AM - 11:00 AM',
            'subjects' => [
                [
                    'name' => 'Grammar',
                    'syllabus' => 'Tense, Voice, Narration, Article'
                ],
                [
                    'name' => 'Vocabulary',
                    'syllabus' => 'Synonym, Antonym, Idioms'
                ],
            ],
            'details' => 'This is a design-only sample. Official exam information will be connected later.'
        ],
        [
            'number' => '03',
            'name' => 'সাধারণ জ্ঞান ও গণিত',
            'status' => 'completed',
            'status_text' => 'Completed',
            'date' => '05 Oct 2026',
            'time' => '09:00 AM - 10:00 AM',
            'subjects' => [
                [
                    'name' => 'বাংলাদেশ বিষয়াবলি',
                    'syllabus' => 'ইতিহাস, সংবিধান ও মুক্তিযুদ্ধ'
                ],
                [
                    'name' => 'গণিত',
                    'syllabus' => 'শতকরা, অনুপাত, লাভ-ক্ষতি'
                ],
            ],
            'details' => 'এই পরীক্ষার অবস্থা ডেমো হিসেবে দেখানো হয়েছে; এটি কোনো বাস্তব ফলাফল নয়।'
        ],
    ];
@endphp

<div class="routine-page">

    <header class="header">
        <a href="{{ url('/') }}"
           class="icon-btn"
           id="backButton"
           aria-label="Go back"
           title="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <div class="header-title">Live Education BD</div>

        <div class="actions">
            <button class="icon-btn"
                    id="shareButton"
                    type="button"
                    title="Share routine">
                <i class="fa-solid fa-share-nodes"></i>
            </button>

            <button class="icon-btn"
                    id="printButton"
                    type="button"
                    title="Print routine">
                <i class="fa-solid fa-print"></i>
            </button>

            <button class="icon-btn"
                    id="themeButton"
                    type="button"
                    title="Dark mode">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>
        </div>
    </header>

    <main>
        <h1 class="page-title">Exam Routine</h1>

        <p class="batch-title">
            {{ $examTitle ?? 'Batch / Department Name' }}
        </p>

        <div class="demo-notice">
            <i class="fa-solid fa-circle-info"></i>
            <span>
                <strong>Design Preview:</strong>
                নিচের Exam No, Date, Time, Status ও Syllabus নমুনা তথ্য।
                এগুলো এখনো Routine Database-এর সঙ্গে সংযুক্ত নয়।
            </span>
        </div>

        <div class="toolbar">
            <button class="tool-btn primary"
                    id="shareMain"
                    type="button">
                <i class="fa-solid fa-share-nodes"></i>
                Share Routine
            </button>

            <button class="tool-btn"
                    id="printMain"
                    type="button">
                <i class="fa-solid fa-print"></i>
                Print / Save PDF
            </button>
        </div>

        <div class="section-row">
            <h2>Exam Schedule</h2>
            <span class="section-count">
                {{ count($demoExams) }} Exams · Demo
            </span>
        </div>

        @foreach($demoExams as $exam)
            <article class="routine-card">

                <div class="card-head">
                    <div>
                        <div class="exam-num">
                            EXAM NO: {{ $exam['number'] }}
                        </div>

                        <h3 class="exam-name">
                            {{ $exam['name'] }}
                        </h3>
                    </div>

                    <span class="status {{ $exam['status'] }}">
                        {{ $exam['status_text'] }}
                    </span>
                </div>

                <div class="meta">
                    <div class="meta-item">
                        <small>
                            <i class="fa-regular fa-calendar"></i>
                            Exam Date
                        </small>
                        <strong>{{ $exam['date'] }}</strong>
                    </div>

                    <div class="meta-item">
                        <small>
                            <i class="fa-regular fa-clock"></i>
                            Exam Time
                        </small>
                        <strong>{{ $exam['time'] }}</strong>
                    </div>
                </div>

                <div class="syllabus-title">
                    <i class="fa-solid fa-book-open"></i>
                    Subject &amp; Syllabus
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Subject</th>
                                <th scope="col">Syllabus / Topic</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($exam['subjects'] as $subject)
                                <tr>
                                    <td>{{ $subject['name'] }}</td>
                                    <td>{{ $subject['syllabus'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <details class="more">
                    <summary>
                        <span>
                            <i class="fa-solid fa-circle-info"></i>
                            See More / Instructions
                        </span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </summary>

                    <p class="more-body">
                        {{ $exam['details'] }}
                    </p>
                </details>
            </article>
        @endforeach

        <div class="bottom-links">
            <a class="bottom-link blue"
               href="{{ route('homepage.running.batches') }}">
                <i class="fa-solid fa-layer-group"></i>
                আমাদের চলমান কোর্স ও ব্যাচগুলো দেখুন
                <i class="fa-solid fa-arrow-right"></i>
            </a>

            <a class="bottom-link white"
               href="{{ route('homepage.courses') }}">
                <i class="fa-solid fa-graduation-cap"></i>
                চলমান সকল কোর্স দেখুন
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </main>
</div>

<div id="toast" class="toast" role="status" aria-live="polite"></div>

<script>
(function () {
    const themeBtn = document.getElementById('themeButton');
    const themeIcon = document.getElementById('themeIcon');
    const toast = document.getElementById('toast');
    let toastTimer;

    function notify(message) {
        toast.textContent = message;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(
            () => toast.classList.remove('show'),
            3000
        );
    }

    function setTheme(theme) {
        document.documentElement.dataset.theme = theme;
        themeIcon.className = theme === 'dark'
            ? 'fa-solid fa-sun'
            : 'fa-solid fa-moon';
    }

    try {
        setTheme(
            localStorage.getItem('lebdExamTheme') === 'dark'
                ? 'dark'
                : 'light'
        );
    } catch (_) {
        setTheme('light');
    }

    themeBtn.addEventListener('click', function () {
        const next =
            document.documentElement.dataset.theme === 'dark'
                ? 'light'
                : 'dark';

        setTheme(next);

        try {
            localStorage.setItem('lebdExamTheme', next);
        } catch (_) {}
    });

    document.getElementById('backButton')
        .addEventListener('click', function (e) {
            try {
                if (
                    document.referrer &&
                    new URL(document.referrer).origin === location.origin
                ) {
                    e.preventDefault();
                    history.back();
                }
            } catch (_) {}
        });

    async function copyUrl() {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(
                    window.location.href
                );
            } else {
                const t = document.createElement('textarea');

                t.value = window.location.href;
                t.style.position = 'fixed';
                t.style.opacity = '0';

                document.body.appendChild(t);
                t.select();

                const ok = document.execCommand('copy');
                t.remove();

                if (!ok) {
                    throw new Error('Unable to copy');
                }
            }

            notify('Routine Link কপি হয়েছে');
        } catch (_) {
            notify('লিংক কপি করা যায়নি। Address Bar থেকে কপি করুন।');
        }
    }

    async function shareRoutine() {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: document.title,
                    url: window.location.href
                });

                return;
            } catch (e) {
                if (e.name === 'AbortError') return;
            }
        }

        await copyUrl();
    }

    document.getElementById('shareButton')
        .addEventListener('click', shareRoutine);

    document.getElementById('shareMain')
        .addEventListener('click', shareRoutine);

    function printRoutine() {
        window.print();
    }

    document.getElementById('printButton')
        .addEventListener('click', printRoutine);

    document.getElementById('printMain')
        .addEventListener('click', printRoutine);

    let openedByPrint = [];

    window.addEventListener('beforeprint', function () {
        openedByPrint = [
            ...document.querySelectorAll('details.more:not([open])')
        ];

        openedByPrint.forEach(item => item.open = true);
    });

    window.addEventListener('afterprint', function () {
        openedByPrint.forEach(item => item.open = false);
        openedByPrint = [];
    });
})();
</script>

</body>
</html>