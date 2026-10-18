@php
    use Carbon\Carbon;

    $student = Auth::guard('student')->user();
    $currentExam = $exam ?? null;
    $examId = $currentExam ? $currentExam->id : null;
    $totalQuestions = $paper->questions()->count();
    $examDuration = max(1, (int) $paper->time);

    // Individual exam duration is measured from the original DB start time.
    // A refresh must not reset the clock.
    $startedAt = ($currentExam && $currentExam->start_at)
        ? Carbon::parse($currentExam->start_at, 'Asia/Dhaka')
        : now('Asia/Dhaka');
    $endsAt = $startedAt->copy()->addMinutes($examDuration);
    $oneQuestionAtATime = $paper->display === 'One';
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/img/favicon.png?v=1.1.0">
    <title>{{ $paper->name }} - Live Education BD</title>
    <link rel="stylesheet" href="{{ asset('web/assets/vendors/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('web/assets/vendors/slick/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('web/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('web/css/style.css?v=1.3.3') }}">
    <link rel="stylesheet" href="{{ asset('web/assets/css/style2b24.css?v=3.8.25') }}">
    <style>
        :root { --exam-blue:#1768d7; --exam-ink:#172b4d; --exam-muted:#66758c; --exam-border:#e1e8f1; --exam-bg:#f5f8fc; --exam-topbar-height:100px; }
        * { box-sizing:border-box; }
        body { background:var(--exam-bg); color:var(--exam-ink); margin:0; padding-bottom:110px; font-family:Arial,"Noto Sans Bengali",sans-serif; }
        .exam-wrap { max-width:900px; margin:0 auto; padding:0 14px 40px; }

        /* Fixed exam summary: total, answered, remaining and countdown */
        .exam-topbar { position:fixed; top:0; left:0; right:0; width:100%; z-index:800; background:rgba(255,255,255,.98); box-shadow:0 3px 18px rgba(22,48,86,.09); border-bottom:1px solid var(--exam-border); }
        .exam-topbar-spacer { height:var(--exam-topbar-height); }
        .exam-stats { max-width:900px; margin:auto; padding:10px 14px 8px; display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:9px; }
        .exam-stat { min-width:0; padding:9px 6px; background:#f6f9ff; border:1px solid #e6eef9; border-radius:12px; text-align:center; }
        .exam-stat-label { display:block; color:var(--exam-muted); font-size:12px; margin-bottom:3px; font-weight:600; }
        .exam-stat-value { display:block; font-size:22px; line-height:1.25; font-weight:800; font-variant-numeric:tabular-nums; }
        .exam-stat.total .exam-stat-value { color:#184e9c; }
        .exam-stat.answered .exam-stat-value { color:#147d58; }
        .exam-stat.remaining .exam-stat-value { color:#c06d12; }
        .exam-stat.clock { background:#fff; border-color:#cad9ed; }
        .exam-stat.clock .exam-stat-value { color:var(--exam-blue); letter-spacing:1px; white-space:nowrap; font-size:21px; }
        .exam-stat.clock.is-low .exam-stat-value { color:#d12a35; }
        .exam-progress { height:4px; background:#e8eef7; overflow:hidden; }
        .exam-progress-fill { width:0%; height:100%; background:linear-gradient(90deg,#1768d7,#12a77a); transition:width .2s ease; }

        .exam-heading { margin:19px 0 19px; padding:19px; text-align:center; border:1px solid var(--exam-border); border-radius:15px; background:#fff; }
        .exam-heading .exam-paper-name { font-size:18px; font-weight:800; margin:7px 0 0; }
        .exam-heading .exam-banner { width:100%; max-height:160px; object-fit:contain; margin-bottom:12px; }
        .exam-heading img { max-width:100%; height:auto; }
        .exam-heading p { margin:4px 0; }

        /* Every question is its own self-contained card */
        .exam-question-card { background:#fff; border:1px solid var(--exam-border); border-radius:16px; padding:18px; margin:0 0 16px; box-shadow:0 4px 16px rgba(34,69,106,.045); }
        .exam-question-card.is-answered { border-color:#b5dbc9; }
        .exam-question-card.is-hidden { display:none; }
        .exam-question-card { scroll-margin-top:calc(var(--exam-topbar-height) + 12px); }
        .exam-question-title { display:flex; align-items:flex-start; gap:11px; font-size:17px; line-height:1.8; font-weight:600; margin:0 0 14px; overflow-wrap:anywhere; }
        .exam-question-number { flex:none; background:#eaf2ff; color:#175fbd; font-size:13px; padding:3px 10px; border-radius:9px; font-weight:800; line-height:1.9; }
        .exam-question-title-text { min-width:0; width:100%; }
        .exam-question-title-text p { margin:0 0 6px; }
        .exam-question-title-text img, .exam-option-text img { max-width:100%; height:auto; }
        .exam-question-title-text table, .exam-option-text table { display:block; max-width:100%; overflow-x:auto; }
        .exam-options { list-style:none; padding:0; margin:0; display:grid; grid-template-columns:1fr; gap:9px; }
        .exam-options li { margin:0; padding:0; max-width:none; }
        .exam-option { display:flex; align-items:flex-start; gap:11px; width:100%; border:1px solid #dfe5ec; border-radius:12px; background:#fff; padding:12px 14px; cursor:pointer; margin:0; font-size:15px; line-height:1.55; font-weight:400; transition:background .15s,border-color .15s; }
        .exam-option:hover { background:#f7faff; border-color:#9ec5f6; }
        .exam-option.is-selected { background:#eaf4ff; border-color:#438fea; }
        .exam-option input[type=radio] { flex:none; width:18px; height:18px; margin:3px 0 0; accent-color:var(--exam-blue); cursor:pointer; }
        .exam-option-label { flex:none; color:#53647b; font-weight:600; }
        .exam-option-text { flex:1; min-width:0; overflow-wrap:anywhere; }
        .exam-question-actions { display:flex; justify-content:space-between; align-items:center; margin-top:12px; gap:10px; }
        .exam-clear-answer { border:0; padding:5px 4px; color:#61758f; background:transparent; font-size:13px; cursor:pointer; }
        .exam-clear-answer:hover { color:#b62637; text-decoration:underline; }
        .exam-next-btn { background:#1768d7; color:#fff; border:0; border-radius:9px; padding:9px 17px; cursor:pointer; font-weight:700; }
        .exam-next-btn:hover { background:#1159b9; }
        .exam-finished-card { background:#fff; border:1px solid var(--exam-border); padding:24px; border-radius:15px; text-align:center; font-weight:600; }

        /* Loading and retry feedback for paginated question requests */
        .question-loading { display:none; justify-content:center; align-items:center; gap:11px; padding:24px 16px; border:1px dashed #d7e2f1; border-radius:14px; background:#fff; text-align:center; color:#335c90; font-weight:700; margin:12px 0 15px; }
        .question-loading.is-visible { display:flex; }
        .exam-spinner { display:inline-block; flex:none; width:22px; height:22px; border:3px solid #c8dcfb; border-top-color:#1768d7; border-radius:50%; animation:exam-spin .75s linear infinite; }
        @keyframes exam-spin { to { transform:rotate(360deg); } }
        .question-error { display:none; padding:18px; text-align:center; background:#fff7f7; border:1px solid #f2cbcb; border-radius:12px; margin:10px 0; }
        .question-error.is-visible { display:block; }
        .question-error button { border:0; color:#fff; background:#1768d7; border-radius:9px; padding:8px 16px; margin-top:10px; }
        #question-sentinel { height:1px; }

        /* Submit action stays at the bottom of the viewport */
        .exam-submit-bar { position:fixed; bottom:0; left:0; right:0; z-index:850; background:#fff; border-top:1px solid #e0e8f3; box-shadow:0 -5px 25px rgba(24,53,84,.10); padding:12px 16px calc(12px + env(safe-area-inset-bottom, 0px)); }
        .exam-submit-inner { max-width:900px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:14px; }
        .exam-submit-summary { font-size:14px; color:#5b6b81; }
        .exam-submit-summary strong { color:#1c314f; }
        #submit-btn { min-width:174px; background:#1768d7; color:#fff; border:0; padding:12px 20px; border-radius:12px; font-size:16px; font-weight:800; cursor:pointer; }
        #submit-btn:hover:not(:disabled) { background:#1159b9; }
        #submit-btn:disabled { opacity:.5; cursor:not-allowed; }

        /* Submission confirmation dialog */
        .exam-confirm-backdrop { display:none; position:fixed; inset:0; z-index:2500; background:rgba(12,24,43,.57); align-items:center; justify-content:center; padding:18px; }
        .exam-confirm-backdrop.is-visible { display:flex; }
        .exam-confirm-box { background:#fff; width:100%; max-width:455px; border-radius:19px; padding:25px 23px 21px; box-shadow:0 24px 90px rgba(8,29,51,.25); text-align:center; }
        .exam-confirm-icon { width:50px; height:50px; line-height:50px; border-radius:50%; background:#eaf2ff; color:#1768d7; font-size:26px; font-weight:800; margin:0 auto 12px; }
        .exam-confirm-box h2 { margin:0 0 12px; font-size:21px; font-weight:800; }
        .exam-confirm-box p { color:#53647b; font-size:15px; line-height:1.8; margin-bottom:14px; }
        .exam-confirm-counts { display:grid; grid-template-columns:1fr 1fr; gap:9px; margin:12px 0 18px; }
        .exam-confirm-count { background:#f5f8fc; border-radius:11px; padding:11px 5px; }
        .exam-confirm-count b { display:block; font-size:25px; }
        .exam-confirm-count span { display:block; color:#5b6b81; font-size:13px; }
        .exam-confirm-buttons { display:flex; gap:10px; }
        .exam-confirm-buttons button { flex:1; border:0; border-radius:11px; padding:12px 9px; font-weight:800; cursor:pointer; }
        .exam-confirm-cancel { background:#ecf1f8; color:#293d59; }
        .exam-confirm-yes { background:#1768d7; color:#fff; }

        /* Existing result processing overlay */
        .exam-submitting-overlay { display:none; position:fixed; inset:0; z-index:3000; background:rgba(13,25,46,.81); align-items:center; justify-content:center; padding:18px; text-align:center; }
        .exam-submitting-overlay.is-visible { display:flex; }
        .exam-submitting-content { color:#fff; max-width:370px; }
        .exam-submitting-content .exam-spinner { width:40px; height:40px; border-width:4px; margin-bottom:20px; }
        .exam-submitting-content p { color:#fff; line-height:1.8; font-size:17px; }
        .exam-submit-retry { display:none; background:#fff; color:#154c99; border:0; padding:10px 18px; border-radius:10px; font-weight:800; margin-top:14px; }

        @media (max-width:600px) {
            body { padding-bottom:108px; }
            .exam-stats { gap:5px; padding:8px 7px; }
            .exam-stat { padding:8px 2px; border-radius:9px; }
            .exam-stat-label { font-size:11px; }
            .exam-stat-value, .exam-stat.clock .exam-stat-value { font-size:17px; }
            .exam-wrap { padding:0 10px 30px; }
            .exam-heading { padding:15px 11px; margin:14px 0; }
            .exam-question-card { padding:14px 12px; margin-bottom:12px; border-radius:13px; }
            .exam-question-title { gap:7px; font-size:15px; }
            .exam-question-number { font-size:12px; padding:3px 7px; }
            .exam-option { padding:11px 10px; font-size:14px; gap:8px; }
            .exam-submit-summary { font-size:12px; }
            #submit-btn { min-width:128px; padding:12px; font-size:14px; }
        }
    </style>
    <script src="/ckeditor/ckeditor.js"></script>
    <script src="https://www.wiris.net/demo/plugins/app/WIRISplugins.js?viewer=image"></script>
</head>
<body>
@if($examId)
    <div class="exam-topbar" aria-label="পরীক্ষার অগ্রগতি">
        <div class="exam-stats">
            <div class="exam-stat total">
                <span class="exam-stat-label">মোট প্রশ্ন</span>
                <strong class="exam-stat-value" id="total-count">{{ $totalQuestions }}</strong>
            </div>
            <div class="exam-stat answered">
                <span class="exam-stat-label">উত্তর দিয়েছেন</span>
                <strong class="exam-stat-value" id="answered-count">0</strong>
            </div>
            <div class="exam-stat remaining">
                <span class="exam-stat-label">বাকি প্রশ্ন</span>
                <strong class="exam-stat-value" id="remaining-count">{{ $totalQuestions }}</strong>
            </div>
            <div class="exam-stat clock" id="clock-box">
                <span class="exam-stat-label">সময় বাকি</span>
                <strong class="exam-stat-value" id="timer">--:--</strong>
            </div>
        </div>
        <div class="exam-progress" aria-hidden="true"><div id="exam-progress-fill" class="exam-progress-fill"></div></div>
    </div>
    <div class="exam-topbar-spacer" aria-hidden="true"></div>

    <main class="exam-wrap">
        <div id="questions-area" aria-label="পরীক্ষার প্রশ্নসমূহ"></div>
        <div id="finished-one-question" class="exam-finished-card" style="display:none">
            সব প্রশ্ন দেখা হয়েছে। নিচের "পরীক্ষা জমা দিন" বাটন ব্যবহার করুন।
        </div>
        <div id="question-loading" class="question-loading is-visible" role="status" aria-live="polite">
            <span class="exam-spinner" aria-hidden="true"></span>
            <span>প্রশ্ন লোড হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন...</span>
        </div>
        <div id="question-error" class="question-error" role="alert">
            প্রশ্ন লোড করা যাচ্ছে না। আপনার ইন্টারনেট সংযোগ পরীক্ষা করুন।<br>
            <button type="button" id="retry-questions">আবার চেষ্টা করুন</button>
        </div>
        <div id="question-sentinel" aria-hidden="true"></div>
    </main>

    <div class="exam-submit-bar">
        <div class="exam-submit-inner">
            <div class="exam-submit-summary">
                উত্তর দিয়েছেন <strong id="footer-answered">0</strong>টি<br>
                বাকি <strong id="footer-remaining">{{ $totalQuestions }}</strong>টি
            </div>
            <button type="button" id="submit-btn" disabled>পরীক্ষা জমা দিন</button>
        </div>
    </div>

    <div class="exam-confirm-backdrop" id="submit-confirm" role="dialog" aria-modal="true" aria-labelledby="submit-confirm-title" aria-hidden="true">
        <div class="exam-confirm-box">
            <div class="exam-confirm-icon">?</div>
            <h2 id="submit-confirm-title">পরীক্ষা জমা দেবেন?</h2>
            <p>আপনি <strong id="confirm-answered-text">0</strong>টি প্রশ্নের উত্তর দিয়েছেন এবং <strong id="confirm-remaining-text">0</strong>টি প্রশ্ন বাকি আছে। আপনি কি পরীক্ষাটি জমা দিতে চান?</p>
            <div class="exam-confirm-counts">
                <div class="exam-confirm-count"><b id="confirm-answered" style="color:#147d58">0</b><span>উত্তর দিয়েছেন</span></div>
                <div class="exam-confirm-count"><b id="confirm-remaining" style="color:#c06d12">0</b><span>বাকি আছে</span></div>
            </div>
            <div class="exam-confirm-buttons">
                <button type="button" class="exam-confirm-cancel" id="cancel-submit">ফিরে যান</button>
                <button type="button" class="exam-confirm-yes" id="confirm-submit">হ্যাঁ, জমা দিন</button>
            </div>
        </div>
    </div>

    <div id="loading" class="exam-submitting-overlay" role="status" aria-live="assertive">
        <div class="exam-submitting-content">
            <span class="exam-spinner" aria-hidden="true"></span>
            <p id="submit-loading-message">আপনার পরীক্ষা জমা দেওয়া হচ্ছে এবং রেজাল্ট প্রস্তুত করা হচ্ছে। অনুগ্রহ করে অপেক্ষা করুন...</p>
            <button type="button" class="exam-submit-retry" id="retry-submit">আবার জমা দিন</button>
        </div>
    </div>
@else
    <main class="exam-wrap">
        <div class="exam-heading">পরীক্ষায় প্রবেশ করা যাচ্ছে না। <a href="{{ route('students.dashboard') }}">ড্যাশবোর্ডে ফিরুন</a></div>
    </main>
@endif

<script src="{{ asset('web/assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('web/assets/js/bootstrap.bundle.min.js') }}" defer></script>
<script src="{{ asset('web/assets/vendors/slick/slick.min.js') }}" defer></script>
<script src="{{ asset('web/assets/js/main.js') }}" defer></script>
@if($examId)
<script>
(function () {
    'use strict';

    const paperId = @json((int) $paper->id);
    const examId = @json((int) $examId);
    const totalQuestions = @json((int) $totalQuestions);
    const oneAtATime = @json($oneQuestionAtATime);
    const examStartTimestamp = @json($startedAt->timestamp);
    const examEndISO = @json($endsAt->toIso8601String());
    const serverTimeISO = @json(now('Asia/Dhaka')->toIso8601String());
    const questionsURL = @json(route('students.exam.questions', ['paper_id' => $paper->id]));
    const submitURL = @json(route('student.exam.add'));
    const resultURL = @json(route('students.result', [$paper->id, 'after']));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Match the question area's top spacing to the fixed summary's real height.
    const examTopbar = document.querySelector('.exam-topbar');
    const syncTopbarHeight = () => {
        if (examTopbar) {
            document.documentElement.style.setProperty(
                '--exam-topbar-height',
                Math.ceil(examTopbar.getBoundingClientRect().height) + 'px'
            );
        }
    };
    syncTopbarHeight();
    if (examTopbar && 'ResizeObserver' in window) {
        new ResizeObserver(syncTopbarHeight).observe(examTopbar);
    }
    window.addEventListener('resize', syncTopbarHeight, { passive: true });

    const questionArea = document.getElementById('questions-area');
    const questionLoading = document.getElementById('question-loading');
    const questionError = document.getElementById('question-error');
    const submitButton = document.getElementById('submit-btn');
    const confirmation = document.getElementById('submit-confirm');
    const submittingOverlay = document.getElementById('loading');
    const sentinel = document.getElementById('question-sentinel');

    let page = 1;
    let renderedQuestionCount = 0;
    let fetchingQuestions = false;
    let allQuestionsLoaded = false;
    let submitStarted = false;
    let submitted = false;
    let questionObserver = null;
    let lastFormData = null;
    let retryTimer = null;
    let timerInterval = null;

    function getCounts() {
        const answered = new Set(
            Array.from(questionArea.querySelectorAll('input[type="radio"]:checked'))
                .map(input => input.name)
        ).size;
        return {
            answered,
            remaining: Math.max(0, totalQuestions - answered)
        };
    }

    function updateCounts() {
        const counts = getCounts();
        document.getElementById('answered-count').textContent = counts.answered;
        document.getElementById('remaining-count').textContent = counts.remaining;
        document.getElementById('footer-answered').textContent = counts.answered;
        document.getElementById('footer-remaining').textContent = counts.remaining;
        document.getElementById('exam-progress-fill').style.width =
            (totalQuestions > 0 ? Math.min(100, counts.answered * 100 / totalQuestions) : 0) + '%';

        questionArea.querySelectorAll('.exam-question-card').forEach(card => {
            const checked = card.querySelector('input[type="radio"]:checked');
            card.classList.toggle('is-answered', !!checked);
            card.querySelectorAll('.exam-option').forEach(label => {
                label.classList.toggle('is-selected', !!label.querySelector('input:checked'));
            });
        });
        return counts;
    }

    function formatOptionLabel(index, format) {
        if (format && format[index] !== undefined) return format[index];
        return '(' + (index + 1) + ')';
    }

    function writeQuestion(data) {
        const format = data.format || [];
        data.questions.data.forEach(question => {
            renderedQuestionCount++;
            const card = document.createElement('article');
            card.className = 'exam-question-card' + (oneAtATime && renderedQuestionCount > 1 ? ' is-hidden' : '');
            card.dataset.questionId = String(question.id);

            const title = document.createElement('div');
            title.className = 'exam-question-title';
            const number = document.createElement('span');
            number.className = 'exam-question-number';
            number.textContent = 'প্রশ্ন ' + renderedQuestionCount;
            const titleText = document.createElement('div');
            titleText.className = 'exam-question-title-text';
            // The existing question editor stores formatted HTML / math content.
            titleText.innerHTML = question.title || '';
            title.append(number, titleText);
            card.appendChild(title);

            const options = document.createElement('ul');
            options.className = 'exam-options';
            options.dataset.questionId = String(question.id);
            (question.mcqitems || []).forEach((option, index) => {
                const li = document.createElement('li');
                const label = document.createElement('label');
                label.className = 'exam-option';
                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = String(question.id);
                radio.value = String(option.id);
                radio.setAttribute('aria-label', 'উত্তর ' + (index + 1));
                const letter = document.createElement('span');
                letter.className = 'exam-option-label';
                letter.textContent = formatOptionLabel(index, format);
                const text = document.createElement('span');
                text.className = 'exam-option-text';
                // Keep editor HTML and formulas rendered as in the original exam page.
                text.innerHTML = option.item || '';
                label.append(radio, letter, text);
                li.appendChild(label);
                options.appendChild(li);
            });
            card.appendChild(options);

            const actions = document.createElement('div');
            actions.className = 'exam-question-actions';
            const clear = document.createElement('button');
            clear.className = 'exam-clear-answer';
            clear.type = 'button';
            clear.textContent = 'উত্তর মুছুন';
            clear.addEventListener('click', () => {
                card.querySelectorAll('input[type="radio"]').forEach(input => { input.checked = false; });
                updateCounts();
            });
            actions.appendChild(clear);
            if (oneAtATime) {
                const next = document.createElement('button');
                next.className = 'exam-next-btn';
                next.type = 'button';
                next.textContent = 'পরের প্রশ্ন →';
                next.addEventListener('click', () => goToNextQuestion(card));
                actions.appendChild(next);
            }
            card.appendChild(actions);
            questionArea.appendChild(card);
        });

        if (typeof com !== 'undefined' && com.wiris && com.wiris.js &&
            com.wiris.js.JsPluginViewer && com.wiris.js.JsPluginViewer.parseElement) {
            com.wiris.js.JsPluginViewer.parseElement(questionArea);
        }
    }

    function loadQuestions() {
        if (fetchingQuestions || allQuestionsLoaded || submitStarted) return Promise.resolve(false);
        fetchingQuestions = true;
        questionLoading.classList.add('is-visible');
        questionError.classList.remove('is-visible');

        return new Promise(resolve => {
            $.ajax({
                url: questionsURL,
                method: 'GET',
                dataType: 'json',
                cache: false,
                data: { page: page }
            }).done(data => {
                if (!data || data.success !== true || !data.questions || !Array.isArray(data.questions.data)) {
                    questionError.classList.add('is-visible');
                    resolve(false);
                    return;
                }

                const response = data.questions;
                writeQuestion(data);
                page++;
                const lastPage = Number(response.last_page || 1);
                allQuestionsLoaded = (Number(response.current_page || page - 1) >= lastPage);
                if (allQuestionsLoaded && questionObserver) questionObserver.disconnect();
                if (renderedQuestionCount > 0) submitButton.disabled = false;
                if (renderedQuestionCount === 0 && allQuestionsLoaded) {
                    questionError.textContent = 'এই পরীক্ষার জন্য কোনো প্রশ্ন পাওয়া যায়নি।';
                    questionError.classList.add('is-visible');
                }
                updateCounts();
                resolve(true);
            }).fail(() => {
                questionError.classList.add('is-visible');
                resolve(false);
            }).always(() => {
                fetchingQuestions = false;
                questionLoading.classList.remove('is-visible');
            });
        });
    }

    async function goToNextQuestion(currentCard) {
        if (submitStarted) return;
        let nextCard = currentCard.nextElementSibling;
        if (!nextCard && !allQuestionsLoaded) {
            const ok = await loadQuestions();
            if (!ok) return;
            nextCard = currentCard.nextElementSibling;
        }
        currentCard.classList.add('is-hidden');
        if (nextCard) {
            nextCard.classList.remove('is-hidden');
            nextCard.scrollIntoView({ block:'start', behavior:'smooth' });
        } else {
            document.getElementById('finished-one-question').style.display = 'block';
            document.getElementById('finished-one-question').scrollIntoView({ block:'center', behavior:'smooth' });
        }
    }

    questionArea.addEventListener('change', event => {
        if (event.target.matches('input[type="radio"]')) updateCounts();
    });
    document.getElementById('retry-questions').addEventListener('click', loadQuestions);
    submitButton.addEventListener('click', () => {
        if (submitStarted || renderedQuestionCount === 0) return;
        const counts = updateCounts();
        document.getElementById('confirm-answered').textContent = counts.answered;
        document.getElementById('confirm-remaining').textContent = counts.remaining;
        document.getElementById('confirm-answered-text').textContent = counts.answered;
        document.getElementById('confirm-remaining-text').textContent = counts.remaining;
        confirmation.classList.add('is-visible');
        confirmation.setAttribute('aria-hidden','false');
        document.getElementById('cancel-submit').focus();
    });

    function closeConfirm() {
        confirmation.classList.remove('is-visible');
        confirmation.setAttribute('aria-hidden','true');
    }
    document.getElementById('cancel-submit').addEventListener('click', closeConfirm);
    document.getElementById('confirm-submit').addEventListener('click', () => submitExam());
    confirmation.addEventListener('click', event => {
        if (event.target === confirmation) closeConfirm();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !submitStarted) closeConfirm();
    });

    function makeFormData() {
        const selected = Array.from(questionArea.querySelectorAll('input[type="radio"]:checked'));
        const questionIds = selected.map(input => Number(input.name));
        const mcqIds = selected.map(input => Number(input.value));
        const formData = new FormData();
        formData.append('paper_id', paperId);
        formData.append('exam_id', examId);
        formData.append('question_id', questionIds.join(','));
        formData.append('mcq_id', mcqIds.join(','));
        formData.append('start_at', examStartTimestamp);
        return formData;
    }

    function submitExam() {
        if (submitStarted) return;
        submitStarted = true;
        closeConfirm();
        submitButton.disabled = true;
        if (timerInterval) clearInterval(timerInterval);
        submittingOverlay.classList.add('is-visible');
        lastFormData = makeFormData();
        pushToDatabase(lastFormData);
    }

    function pushToDatabase(formData) {
        document.getElementById('retry-submit').style.display = 'none';
        $.ajax({
            url: submitURL,
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json'
        }).done(data => {
            if (data && data.success === true) {
                submitted = true;
                window.location.href = resultURL;
                return;
            }
            showSubmitRetry();
        }).fail(() => showSubmitRetry());
    }

    function showSubmitRetry() {
        if (submitted) return;
        document.getElementById('submit-loading-message').textContent =
            'পরীক্ষা জমা দেওয়া যায়নি। ইন্টারনেট সংযোগ পরীক্ষা করে আবার চেষ্টা করুন।';
        document.getElementById('retry-submit').style.display = 'inline-block';
    }
    document.getElementById('retry-submit').addEventListener('click', () => {
        if (!submitted && lastFormData) {
            document.getElementById('submit-loading-message').textContent =
                'আপনার পরীক্ষা জমা দেওয়া হচ্ছে। অনুগ্রহ করে অপেক্ষা করুন...';
            pushToDatabase(lastFormData);
        }
    });

    // Use an absolute, timezone-qualified deadline so refresh never grants extra time.
    const examEndsAtMs = Date.parse(examEndISO);
    // Use the server clock as a reference; a student's device time may differ.
    const clockOffsetMs = Date.parse(serverTimeISO) - Date.now();
    function tickTimer() {
        if (submitStarted) return;
        const msLeft = Math.max(0, examEndsAtMs - (Date.now() + clockOffsetMs));
        const hours = Math.floor(msLeft / 3600000);
        const minutes = Math.floor(msLeft % 3600000 / 60000);
        const seconds = Math.floor(msLeft % 60000 / 1000);
        document.getElementById('timer').textContent =
            (hours > 0 ? hours + ':' + String(minutes).padStart(2,'0') : String(minutes).padStart(2,'0')) +
            ':' + String(seconds).padStart(2,'0');
        document.getElementById('clock-box').classList.toggle('is-low', msLeft <= 60000);
        if (msLeft <= 0) {
            document.getElementById('timer').textContent = 'সময় শেষ';
            submitExam(); // Automatic submission bypasses confirmation at the deadline.
        }
    }

    // Load the first page immediately; fetch subsequent pages as the user scrolls.
    if (!oneAtATime && 'IntersectionObserver' in window) {
        questionObserver = new IntersectionObserver(entries => {
            if (entries.some(entry => entry.isIntersecting)) loadQuestions();
        }, { rootMargin:'350px 0px' });
        questionObserver.observe(sentinel);
    } else if (!oneAtATime) {
        window.addEventListener('scroll', () => {
            if (sentinel.getBoundingClientRect().top < window.innerHeight + 350) loadQuestions();
        }, { passive:true });
    }
    loadQuestions();
    tickTimer();
    timerInterval = setInterval(tickTimer, 1000);

    // Keep the existing warning when leaving an unfinished exam.
    window.addEventListener('beforeunload', event => {
        if (!submitted && !submitStarted) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
})();
</script>
@endif
</body>
</html>
