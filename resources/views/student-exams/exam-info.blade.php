@php
/*
|--------------------------------------------------------------------------
| LEBD - STUDENT EXAM INFORMATION
|--------------------------------------------------------------------------
| Separate Blade for Exam Details
| Does not modify Upcoming Exam or Routine
|--------------------------------------------------------------------------
*/

$paper = $paper ?? null;

$examName = $paper?->name ?? 'Exam Information';

$duration = $paper?->time
    ? (int) $paper->time . ' মিনিট'
    : 'নির্ধারিত হয়নি';

/*
|--------------------------------------------------------------------------
| EXAM DATE AND TIME
|--------------------------------------------------------------------------
*/

$examStart = $examStartAt
    ?? $paper?->open
    ?? null;

$examEnd = $examEndAt
    ?? $paper?->close
    ?? null;

$startText = $examStart
    ? \Carbon\Carbon::parse(
        $examStart,
        'Asia/Dhaka'
    )->format('d M Y, h:i A')
    : 'নির্ধারিত হয়নি';

$endText = $examEnd
    ? \Carbon\Carbon::parse(
        $examEnd,
        'Asia/Dhaka'
    )->format('d M Y, h:i A')
    : 'নির্ধারিত হয়নি';

/*
|--------------------------------------------------------------------------
| TOTAL QUESTIONS
|--------------------------------------------------------------------------
| Temporary fallback from existing Paper relation.
| Later StudentExamPaperInfo Service will supply this.
|--------------------------------------------------------------------------
*/

$totalQuestions = $totalQuestions ?? null;

if (
    $totalQuestions === null &&
    $paper &&
    method_exists($paper, 'questions')
) {
    $totalQuestions = $paper->questions()->count();
}

/*
|--------------------------------------------------------------------------
| SUBJECT AND DEPARTMENT BREAKDOWN
|--------------------------------------------------------------------------
| Expected:
| [
|   ['name' => 'বাংলা', 'count' => 20],
|   ['name' => 'Department 2', 'count' => 30]
| ]
|--------------------------------------------------------------------------
*/

$questionBreakdown = collect(
    $questionBreakdown ?? []
);

/*
|--------------------------------------------------------------------------
| QUESTION PAPER INSTRUCTIONS
|--------------------------------------------------------------------------
*/

$rawInstructions = $instructions
    ?? $paper?->details
    ?? '';

$instructionText = preg_replace(
    '/<br\s*\/?>|<\/p>|<\/li>|<\/div>/i',
    "\n",
    (string) $rawInstructions
);

$instructionText = trim(
    html_entity_decode(
        strip_tags($instructionText),
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    )
);

/*
|--------------------------------------------------------------------------
| EXAM STATUS AND PERMISSIONS
|--------------------------------------------------------------------------
| Controller will provide actual access permission.
|--------------------------------------------------------------------------
*/

$canStartExam = (bool) ($canStartExam ?? false);

$examStatus = $examStatus
    ?? ($canStartExam ? 'Live Now' : 'Upcoming');

$takeExamUrl = $takeExamUrl
    ?? ($paper
        ? route(
            'students.check',
            ['paper' => $paper->id]
        )
        : null);

$canViewResult = (bool) ($canViewResult ?? false);

$resultUrl = $resultUrl ?? null;

$backUrl = $backUrl
    ?? url()->previous();

/*
|--------------------------------------------------------------------------
| MARKING
|--------------------------------------------------------------------------
*/

$positiveMark = $paper?->mark;
$negativeMark = $paper?->minus;

@endphp

@extends('student')

@section('title', 'Exam Information')

@section('content')

<style>

/* =========================================
   PAGE
========================================= */

.lebd-info-page {
    background: #edf2f9;
    min-height: 80vh;
    padding: 22px 12px 45px;
    color: #102344;
    font-family: Arial, sans-serif;
}

.lebd-info-container {
    width: 100%;
    max-width: 650px;
    margin: 0 auto;
}

/* =========================================
   BACK AND TITLE
========================================= */

.lebd-info-back {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none;
    color: #086ad8;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 16px;
}

.lebd-info-title {
    font-size: 25px;
    font-weight: 800;
    color: #102344;
    margin: 0 0 7px;
}

.lebd-info-subtitle {
    color: #71809a;
    font-size: 12px;
    margin-bottom: 20px;
}

/* =========================================
   EXAM MAIN CARD
========================================= */

.lebd-info-card {
    background: #fff;
    border: 1px solid #e4ebf5;
    border-radius: 19px;
    padding: 21px;
    box-shadow: 0 8px 30px rgba(17, 40, 85, .055);
}

.lebd-info-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}

.lebd-info-exam {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.lebd-info-exam-icon {
    flex: 0 0 49px;
    height: 49px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e6f2ff;
    border-radius: 14px;
    color: #0878ed;
    font-size: 22px;
}

.lebd-info-exam-name {
    font-size: 21px;
    font-weight: 800;
    color: #102344;
    overflow-wrap: anywhere;
}

.lebd-info-status {
    flex-shrink: 0;
    border-radius: 25px;
    padding: 8px 12px;
    background: #fff0ba;
    color: #805a05;
    font-size: 11px;
    font-weight: 800;
}

.lebd-info-status.live {
    background: #dcf9e8;
    color: #087849;
}

.lebd-info-status.completed {
    background: #e9edf4;
    color: #64748b;
}

/* =========================================
   EXAM DETAIL ROWS
========================================= */

.lebd-info-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.lebd-info-detail {
    min-width: 0;
    background: #f2f7ff;
    border: 1px solid #e3ecfa;
    border-radius: 12px;
    padding: 13px;
}

.lebd-info-detail-label {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #647795;
    font-size: 11px;
    margin-bottom: 7px;
}

.lebd-info-detail-label i {
    color: #0878ed;
}

.lebd-info-detail-value {
    color: #11264b;
    font-weight: 800;
    font-size: 13px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

/* =========================================
   QUESTION COUNT
========================================= */

.lebd-info-question-box {
    margin-top: 14px;
    background: #edf7ff;
    border: 1px solid #cfe5ff;
    border-radius: 12px;
    padding: 13px;
}

.lebd-info-section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 800;
    margin: 0 0 12px;
    color: #102344;
}

.lebd-info-section-title i {
    color: #0878ed;
}

.lebd-info-count-line {
    display: flex;
    align-items: center;
    gap: 12px;
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 4px;
    scrollbar-width: thin;
}

.lebd-info-count-total {
    font-size: 13px;
    font-weight: 800;
    color: #086ad8;
    flex-shrink: 0;
}

.lebd-info-count-part {
    border-left: 1px solid #c4d8f1;
    padding-left: 12px;
    color: #334b68;
    font-size: 12px;
    flex-shrink: 0;
}

.lebd-info-count-part strong {
    color: #102344;
}

/* =========================================
   INSTRUCTIONS
========================================= */

.lebd-info-instructions {
    margin-top: 17px;
    background: #fff;
    border: 1px solid #e2eaf5;
    border-radius: 13px;
    padding: 17px;
}

.lebd-info-instruction-text {
    color: #43546d;
    font-size: 13px;
    line-height: 1.9;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.lebd-info-no-data {
    color: #8490a3;
    font-size: 12px;
}

/* =========================================
   MARKING
========================================= */

.lebd-info-marking {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 14px;
}

.lebd-info-mark {
    background: #f2f7ff;
    padding: 9px 12px;
    border-radius: 9px;
    color: #52657f;
    font-size: 11px;
}

.lebd-info-mark strong {
    color: #102344;
}

/* =========================================
   BUTTONS
========================================= */

.lebd-info-actions {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 10px;
    margin-top: 19px;
}

.lebd-info-btn {
    min-height: 47px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    border-radius: 12px;
    border: 1px solid transparent;
    font-size: 14px;
    font-weight: 800;
    text-decoration: none;
    text-align: center;
    padding: 12px;
    cursor: pointer;
}

.lebd-info-btn.start {
    background: #0875e9;
    color: #fff;
}

.lebd-info-btn.result {
    background: #edf5ff;
    color: #086ad8;
    border-color: #cbdfff;
}

.lebd-info-btn:disabled {
    opacity: .55;
    cursor: not-allowed;
}

.lebd-info-btn.start:hover {
    color: #fff;
    background: #0865cc;
    text-decoration: none;
}

.lebd-info-btn.result:hover {
    color: #0757b3;
    text-decoration: none;
}

/* =========================================
   NOTICE
========================================= */

.lebd-info-notice {
    margin-top: 16px;
    padding: 14px;
    background: #fff8e9;
    border: 1px solid #ffe3a3;
    border-radius: 12px;
    color: #86600d;
    font-size: 12px;
    line-height: 1.8;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.lebd-info-notice i {
    font-size: 18px;
    margin-top: 2px;
}

/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 480px) {

    .lebd-info-page {
        padding: 15px 10px 30px;
    }

    .lebd-info-card {
        padding: 14px;
    }

    .lebd-info-title {
        font-size: 22px;
    }

    .lebd-info-exam-name {
        font-size: 17px;
    }

    .lebd-info-exam-icon {
        flex-basis: 42px;
        height: 42px;
    }

    .lebd-info-detail {
        padding: 10px;
    }

    .lebd-info-detail-value {
        font-size: 11px;
    }

    .lebd-info-actions {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .lebd-info-btn {
        font-size: 12px;
        padding: 10px 6px;
    }
}

/* Center the Take Exam button */

.lebd-info-actions {
    display: flex !important;
    justify-content: center !important;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.lebd-info-actions .lebd-info-btn {
    width: 190px;
    max-width: 100%;
    flex: 0 1 190px;
}

</style>

<div class="lebd-info-page">

    <div class="lebd-info-container">

        <!-- BACK -->

        <a
            href="{{ $backUrl }}"
            class="lebd-info-back"
        >
            <i class="fa-solid fa-arrow-left"></i>
            ফিরে যান
        </a>

        <!-- PAGE TITLE -->

        <h1 class="lebd-info-title">
            পরীক্ষা সমূহ
        </h1>

        <p class="lebd-info-subtitle">
            পরীক্ষার বিস্তারিত তথ্য ও নির্দেশনা
        </p>

        <!-- MAIN CARD -->

        <div class="lebd-info-card">

            <!-- EXAM HEADER -->

            <div class="lebd-info-head">

                <div class="lebd-info-exam">

                    <div class="lebd-info-exam-icon">
                        <i class="fa-regular fa-file-lines"></i>
                    </div>

                    <div class="lebd-info-exam-name">
                        {{ $examName }}
                    </div>

                </div>

                @php
                    $statusClass = match($examStatus) {
                        'Live Now' => 'live',
                        'Completed' => 'completed',
                        default => ''
                    };
                @endphp

                <span class="lebd-info-status {{ $statusClass }}">

                    @if($examStatus === 'Live Now')
                        <i class="fa-solid fa-circle"></i>
                    @else
                        <i class="fa-regular fa-clock"></i>
                    @endif

                    {{ $examStatus }}

                </span>

            </div>

            <!-- EXAM DETAILS -->

            <div class="lebd-info-detail-grid">

                <div class="lebd-info-detail">

                    <div class="lebd-info-detail-label">
                        <i class="fa-solid fa-hashtag"></i>
                        পরীক্ষা নং
                    </div>

                    <div class="lebd-info-detail-value">
                        {{ $examName }}
                    </div>

                </div>

                <div class="lebd-info-detail">

                    <div class="lebd-info-detail-label">
                        <i class="fa-regular fa-hourglass-half"></i>
                        এক্সাম লিংকে প্রবেশের পরে সময় পাবেন
                    </div>

                    <div class="lebd-info-detail-value">
                        {{ $duration }}
                    </div>

                </div>

                <div class="lebd-info-detail">

                    <div class="lebd-info-detail-label">
                        <i class="fa-regular fa-calendar-days"></i>
                        পরীক্ষা শুরু হয়েছে
                    </div>

                    <div class="lebd-info-detail-value">
                        {{ $startText }}
                    </div>

                </div>

                <div class="lebd-info-detail">

                    <div class="lebd-info-detail-label">
                        <i class="fa-regular fa-clock"></i>
                        এক্সাম দিতে পারবেন
                    </div>

                    <div class="lebd-info-detail-value">
                        {{ $endText }}
                    </div>

                </div>

            </div>

            <!-- =====================================
                 TOTAL QUESTIONS - SINGLE LINE
            ===================================== -->

            <div class="lebd-info-question-box">

                <h3 class="lebd-info-section-title">
                    <i class="fa-solid fa-list-check"></i>
                    প্রশ্নপত্রের প্রশ্নসংখ্যা
                </h3>

                <div class="lebd-info-count-line">

                    <span class="lebd-info-count-total">
                        মোট প্রশ্ন:
                        {{ $totalQuestions ?? '—' }}
                    </span>

                    @foreach($questionBreakdown as $item)

                        <span class="lebd-info-count-part">

                            {{ data_get($item, 'name', 'বিষয়') }}:

                            <strong>
                                {{ data_get($item, 'count', 0) }}
                            </strong>

                        </span>

                    @endforeach

                </div>

                @if($questionBreakdown->isEmpty())

                    <p class="lebd-info-no-data" style="margin:9px 0 0;">
                        বিষয়ভিত্তিক প্রশ্নের হিসাব যুক্ত হলে এখানে দেখাবে।
                    </p>

                @endif

            </div>

            <!-- =====================================
                 MARKING INFORMATION
            ===================================== -->

            <div class="lebd-info-marking">

                @if($positiveMark !== null)

                    <span class="lebd-info-mark">
                        সঠিক উত্তরে:
                        <strong>
                            +{{ $positiveMark }}
                        </strong>
                    </span>

                @endif

                @if($negativeMark !== null)

                    <span class="lebd-info-mark">
                        ভুল উত্তরে কর্তন:
                        <strong>
                            {{ $negativeMark }}
                        </strong>
                    </span>

                @endif

            </div>

            <!-- =====================================
                 QUESTION PAPER INSTRUCTIONS
            ===================================== -->

            <div class="lebd-info-instructions">

                <h3 class="lebd-info-section-title">
                    <i class="fa-solid fa-circle-info"></i>
                    নির্দেশনা
                </h3>

                @if(
                    $instructionText !== '' &&
                    strtoupper($instructionText) !== 'N/A'
                )

                    <div class="lebd-info-instruction-text">
                        {{ $instructionText }}
                    </div>

                @else

                    <div class="lebd-info-no-data">
                        প্রশ্ন সম্পর্কে বিস্তারিত তথ্য দেখা হলে। নিচের শুরু করুন বাটুনে ক্লিক করে এক্সাম শুরু করুন।
                    </div>

                @endif

            </div>

            <!-- =====================================
                 EXAM ACTIONS
            ===================================== -->

            <div class="lebd-info-actions">

                @if($canStartExam && $takeExamUrl)

                    <a
                        href="{{ $takeExamUrl }}"
                        class="lebd-info-btn start"
                    >
                        <i class="fa-solid fa-pen-to-square"></i>
                        পরীক্ষা দিন
                    </a>

                @else

                    <button
                        type="button"
                        class="lebd-info-btn start"
                        disabled
                    >
                        <i class="fa-solid fa-lock"></i>
                        Start Exam
                    </button>

                @endif

                

            </div>

        </div>

        <!-- BOTTOM NOTICE -->

        <div class="lebd-info-notice">

            <i class="fa-solid fa-circle-info"></i>

            <span>
                পরীক্ষার সময় ও প্রবেশের অনুমতি অনুযায়ী
                পরীক্ষা দিন বাটন সক্রিয় হবে।
                ফলাফল প্রকাশের অনুমতি পাওয়া গেলে
                ফলাফল দেখা যাবে।
            </span>

        </div>

    </div>

</div>

@endsection