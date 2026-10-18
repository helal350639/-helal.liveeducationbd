@php
    $filtered = $filtered ?? false;
    $batches = $batches ?? collect();
    $results = $results ?? collect();
@endphp

@extends('student')

@section('title', 'Results')

@section('content')

<style>
    .lebd-results {
        padding: 22px 0 35px;
    }

    .lebd-results-title {
        text-align: center;
        font-size: 25px;
        font-weight: 800;
        color: #15243d;
        margin: 0 0 8px;
    }

    .lebd-results-subtitle {
        text-align: center;
        font-size: 13px;
        color: #718096;
        line-height: 1.7;
        margin-bottom: 24px;
    }

    .lebd-results-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .lebd-result-card {
        background: #ffffff;
        border: 1px solid #e3eaf3;
        border-radius: 15px;
        padding: 17px;
        box-shadow: 0 5px 20px rgba(20, 40, 75, .04);
        min-width: 0;
    }

    .lebd-result-icon {
        width: 43px;
        height: 43px;
        background: #e7f1ff;
        color: #0875e1;
        display: grid;
        place-items: center;
        border-radius: 12px;
        font-size: 19px;
        margin-bottom: 12px;
    }

    .lebd-result-name {
        font-size: 15px;
        font-weight: 800;
        line-height: 1.55;
        color: #172033;
        margin: 0 0 10px;
        overflow-wrap: anywhere;
    }

    .lebd-result-meta {
        font-size: 12px;
        color: #68788e;
        line-height: 1.8;
    }

    .lebd-result-link {
        display: block;
        margin-top: 14px;
        padding: 11px;
        border-radius: 10px;
        background: #e7f1ff;
        color: #0875e1;
        font-size: 12px;
        font-weight: 800;
        text-align: center;
        text-decoration: none;
    }

    .lebd-result-link:hover {
        background: #d6e9ff;
        text-decoration: none;
    }

    .lebd-empty {
        background: #ffffff;
        border: 1px solid #e3eaf3;
        border-radius: 15px;
        padding: 38px 18px;
        text-align: center;
        color: #718096;
        font-size: 14px;
        line-height: 1.8;
    }

    .lebd-empty i {
        display: block;
        font-size: 32px;
        color: #8aafe5;
        margin-bottom: 13px;
    }

    .lebd-back {
        display: inline-block;
        margin-top: 24px;
        background: #e7f1ff;
        color: #0875e1;
        padding: 11px 24px;
        border-radius: 10px;
        border: 0;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .lebd-old-batch-image {
        width: 100%;
        height: 145px;
        object-fit: cover;
        border-radius: 11px;
        background: #edf1f7;
    }

    @media (max-width: 480px) {
        .lebd-results-grid {
            grid-template-columns: 1fr;
            gap: 11px;
        }

        .lebd-result-card {
            padding: 14px;
        }

        .lebd-results-title {
            font-size: 21px;
        }
    }
</style>

<section class="lebd-results">

    <div class="container">

        <h1 class="lebd-results-title">
            Results
        </h1>

        @if($filtered)

            <!-- Selected Exam Context -->
            <div class="lebd-results-subtitle">

                @if(!empty($course))
                    {{ $course->name }}
                @endif

                @if(!empty($batch))
                    &bull; {{ $batch->name }}
                @endif

                @if(!empty($department))
                    &bull; {{ $department->name }}
                @endif

            </div>

            <!-- Only Selected Scope Results -->
            @if($results->isNotEmpty())

                <div class="lebd-results-grid">

                    @foreach($results as $exam)

                        <article class="lebd-result-card">

                            <div class="lebd-result-icon">
                                <i class="fa fa-bar-chart"></i>
                            </div>

                            <h2 class="lebd-result-name">
                                {{ $exam->paper->name ?? 'Exam Result' }}
                            </h2>

                            <div class="lebd-result-meta">

                                <div>
                                    <strong>Exam ID:</strong>
                                    {{ $exam->id }}
                                </div>

                                <div>
                                    <strong>Obtained Marks:</strong>
                                    {{ $exam->mark ?? '—' }}
                                </div>

                                <div>
                                    <strong>Status:</strong>
                                    Completed
                                </div>

                            </div>

                            <a
                                class="lebd-result-link"
                                href="{{ route('students.result', [
                                    'id' => $exam->paper_id
                                ]) }}"
                            >
                                <i class="fa fa-eye"></i>
                                View Result
                            </a>

                        </article>

                    @endforeach

                </div>

            @else

                <div class="lebd-empty">
                    <i class="fa fa-file-text-o"></i>

                    এই Course / Batch / Department-এর
                    আপনার কোনো সম্পন্ন পরীক্ষার ফলাফল পাওয়া যায়নি।
                </div>

            @endif

        @else

            <!-- Original Results Batch Listing -->
            <div class="lebd-results-subtitle">
                All Batch Results
            </div>

            @if($batches->count())

                <div class="row">

                    @foreach($batches as $value)

                        <div class="col-6 col-lg-3 mt-4">

                            <div class="exam-package-area">

                                <div class="package-exam-image">

                                    <a href="#">
                                        <img
                                            src="{{ $value->banner ? $value->banner : '/img/course.png' }}"
                                            alt="{{ $value->name }}"
                                            class="lebd-old-batch-image"
                                            loading="lazy"
                                        >
                                    </a>

                                </div>

                                <div class="package-exam-content mx-2 mx-lg-3">

                                    <div class="package-exam-title pt-3">

                                        <h2>
                                            <a href="#">
                                                {{ $value->name }}
                                            </a>
                                        </h2>

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="lebd-empty">
                    No Course Available
                </div>

            @endif

        @endif

        <!-- Back Button -->
        <div class="text-center">

            <button
                type="button"
                class="lebd-back"
                onclick="window.history.back();"
            >
                <i class="fa fa-arrow-left"></i>
                Back
            </button>

        </div>

    </div>

</section>

@endsection