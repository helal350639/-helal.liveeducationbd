@php

    use \App\Http\Controllers\SourceCtrl;
    use Carbon\Carbon;

    $source = new SourceCtrl;

    $user = Auth::guard('student')->user();

    $student = null;

    if($user){
        $student = \App\Models\Student::find($user->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Batch
    |--------------------------------------------------------------------------
    */

    $value = $batch;


    /*
    |--------------------------------------------------------------------------
    | PRICE CALCULATION
    |--------------------------------------------------------------------------
    */

    $price = 0;
    $discount = 0;
    $reference_discount = 0;
    $selling_price = 0;


    if($value->discount && $value->offer_end_at && $value->offer_end_at > now()){

        $discount = $value->discount;

    }


    if(
        $value->reference_discount &&
        Session::get('batch-referrer') &&
        Session::get('batch-referrer')['batch'] == $batch->id
    ){

        $reference_discount = $value->reference_discount;

        $discount = $discount + $value->reference_discount;

    }


    $price = ($value->price ?? 0) + ($value->fee ?? 0);

    $selling_price = $price - $discount;


    /*
    |--------------------------------------------------------------------------
    | REGISTRATION STATUS
    |--------------------------------------------------------------------------
    */

    $registrationActive = false;

    if($value->reg_end_at){

        $registrationActive =
            Carbon::now()->lte(Carbon::parse($value->reg_end_at));

    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT PURCHASE STATUS
    |--------------------------------------------------------------------------
    */

    $alreadyPurchased = false;

    if($user){

        $alreadyPurchased =
            $value->students()->where('id', $user->id)->exists();

    }


    /*
    |--------------------------------------------------------------------------
    | SYLLABUS
    |--------------------------------------------------------------------------
    */

    $course_syllabuses = collect();

    if($value->syllabus){

        $course_syllabuses =
            $value->syllabuses()
                ->whereNull('department_id')
                ->where('course_id', optional($value->course)->id)
                ->get();

    }

@endphp


@extends('student')


@section('stylesheets')
<link rel="stylesheet" href="{{ asset('web/css/lebd-course-purchase-3d-v54.css') }}?v=54">
<link rel="stylesheet" href="{{ asset('web/css/lebd-course-routine-v55.css') }}?v=55">
<link rel="stylesheet" href="{{ asset('web/css/lebd-description-v49.css') }}">

<meta name="description" content="{{ $batch->subtitle }}">
<meta name="keywords" content="{{ $batch->name }}">
<meta name="robots" content="index, follow, nocache">

<meta name="googlebot"
      content="index, follow, max-video-preview:-1, max-image-preview:large, max-snippet:-1">

<meta name="category" content="education">

<meta property="fb:app_id" content="1360109555168118">

<meta property="og:type" content="website">

<meta property="og:url"
      content="{{ route('home.course.show', $batch->id) }}">

<meta property="og:title"
      content="{{ $batch->name }}">

<meta property="og:description"
      content="{{ $batch->subtitle }}">


@if($batch->video)

<meta property="og:image"
      content="https://img.youtube.com/vi/{{ $batch->video }}/maxresdefault.jpg">

<meta property="og:video"
      content="https://www.youtube.com/embed/{{ $batch->video }}">

<meta property="og:video:secure_url"
      content="https://www.youtube.com/embed/{{ $batch->video }}">

<meta property="og:video:type"
      content="text/html">

<meta property="og:video:width" content="1280">
<meta property="og:video:height" content="720">

@else

<meta property="og:image"
      content="{{ route('homepage') . $batch->banner }}">

<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

@endif


<meta name="twitter:card"
      content="summary_large_image">

<meta name="twitter:title"
      content="{{ $batch->name }}">

<meta name="twitter:description"
      content="{{ $batch->subtitle }}">

<meta name="twitter:image"
      content="{{ route('homepage') . $batch->banner }}">


<title>{{ $batch->name }}</title>


@endsection



@section('content')






<!-- =========================================================
     MAIN PAGE
========================================================= -->

<section class="batch-detail-page">

<div class="batch-detail-container">


    <!-- =====================================================
         COURSE TITLE
    ====================================================== -->

    <div class="batch-course-title">

        <h1>
            {{ optional($value->course)->name ?? 'কোর্স' }}
        </h1>

        <p>
            {{ $value->subtitle }}
        </p>

    </div>



    <!-- =====================================================
         MAIN GRID
    ====================================================== -->

    <div class="batch-main-grid">


        <!-- =================================================
             LEFT
        ================================================== -->

        <div class="batch-left-area">


            <!-- =============================================
                 BATCH HERO
            ============================================== -->

            <div class="batch-hero">

                <h2>
                    {{ $value->name }}
                </h2>


                @if($value->reg_end_at && $registrationActive)

                    <div class="batch-hero-countdown">

                        রেজিস্ট্রেশনের মেয়াদ আছে:

                        <span id="batchTimer">
                            গণনা হচ্ছে...
                        </span>

                    </div>

                @elseif($value->reg_end_at)

                    <div class="batch-hero-countdown">

                        রেজিস্ট্রেশনের মেয়াদ শেষ

                    </div>

                @endif


                @if($value->short)

                    <div class="batch-hero-description">

                        {!! $value->short !!}

                    </div>

                @endif

            </div>



            <!-- =============================================
                 TABS
            ============================================== -->

            <div class="batch-detail-tabs"
                 id="batchDetailTabs">


                <a href="javascript:void(0)"
                   class="batch-detail-tab active"
                   data-target="overview">

                    <i class="fa-solid fa-file-lines"></i>

                    Overview

                </a>


                <a href="javascript:void(0)"
                   class="batch-detail-tab"
                   data-target="description">

                    <i class="fa-solid fa-file-pen"></i>

                    Description

                </a>


                <a href="javascript:void(0)"
                   class="batch-detail-tab"
                   data-target="routine">

                    <i class="fa-solid fa-calendar-days"></i>

                    Routine

                </a>


                <a href="javascript:void(0)"
                   class="batch-detail-tab"
                   data-target="sample-pdf">

                    <i class="fa-solid fa-file-pdf"></i>

                    Sample PDF

                </a>


                <a href="javascript:void(0)"
                   class="batch-detail-tab"
                   data-target="teacher">

                    <i class="fa-solid fa-users"></i>

                    Teacher

                </a>


            </div>



            <!-- LEBD-3: Admin-managed Bangla content groups; keep existing tab IDs. -->
            <div id="overview" class="batch-content-section active">
                @if($value->short)
                <div class="batch-info-card lebd-content-group">
                    <div class="lebd-group-head"><span class="lebd-group-number">০১</span><i class="fa fa-book lebd-v48-headicon"></i><h4>কোর্স পরিচিতি</h4></div>
                    @if($value->short)<div class="lebd-group-copy">{!! $value->short !!}</div>@endif
                </div>
                @endif

                @if($value->what_is || $value->learn)
                <div class="batch-info-card lebd-content-group">
                    <div class="lebd-group-head"><span class="lebd-group-number">০২</span><i class="fa fa-bullseye lebd-v48-headicon"></i><h4>শেখার বিষয় ও সুবিধা</h4></div>
                    @if($value->what_is)
                    <h5 class="lebd-subheading">এই কোর্সে যা থাকছে</h5>
                    <div class="lebd-group-copy">{!! $value->what_is !!}</div>
                    @endif
                    @if($value->learn)
                    <h5 class="lebd-subheading">কোর্সটি করে যা শিখবেন</h5>
                    <div class="lebd-group-copy">{!! $value->learn !!}</div>
                    @endif
                </div>
                @endif

                @if($value->start_at || $value->end_at || $value->reg_end_at || ($value->offer_end_at && $value->offer_end_at > now()))
                <div class="batch-info-card lebd-content-group">
                    <div class="lebd-group-head"><span class="lebd-group-number">০৩</span><i class="fa fa-calendar lebd-v48-headicon"></i><h4>কোর্সের মেয়াদ ও নিবন্ধন</h4></div>
                    <div class="table-responsive"><table class="table table-bordered lebd-course-dates">
                        @if($value->start_at && $value->end_at)
                        <tr><td>কোর্সের মেয়াদ</td><th>{{ \Carbon\Carbon::parse($value->start_at)->format('d M Y') }} - {{ \Carbon\Carbon::parse($value->end_at)->format('d M Y') }}</th></tr>
                        @endif
                        @if($value->reg_end_at)<tr><td>রেজিস্ট্রেশনের শেষ সময়</td><th>{{ $source->dtformat($value->reg_end_at) }}</th></tr>@endif
                        @if($value->offer_end_at && $value->offer_end_at > now())<tr><td>অফারের শেষ সময়</td><th>{{ $source->dtformat($value->offer_end_at) }}</th></tr>@endif
                    </table></div>
                    <p class="lebd-routine-note">ক্লাস ও পরীক্ষার প্রকাশিত সময়সূচি দেখতে উপরের <strong>Routine</strong> ট্যাবে যান।</p>
                </div>
                @endif

                @if($value->faq)
                <div class="batch-info-card lebd-content-group">
                    <div class="lebd-group-head"><span class="lebd-group-number">০৪</span><i class="fa fa-question-circle lebd-v48-headicon"></i><h4>সাধারণ প্রশ্ন ও উত্তর</h4></div>
                    <div class="lebd-v48-faq">{!! $value->faq !!}</div>
                </div>
                @endif

                @if($value->refund)
                <div class="batch-info-card lebd-content-group">
                    <div class="lebd-group-head"><span class="lebd-group-number">০৫</span><i class="fa fa-shield lebd-v48-headicon"></i><h4>নীতিমালা ও শর্তাবলি</h4></div>
                    <div class="lebd-group-copy">{!! $value->refund !!}</div>
                </div>
                @endif

                @if(!$value->short && !$value->what_is && !$value->learn && !$value->faq && !$value->refund)
                    <div class="batch-info-card text-center">কোর্সের বিস্তারিত তথ্য শিগগিরই প্রকাশ করা হবে।</div>
                @endif
                <a href="{{ route('students.complain') }}" class="btn btn-info mt-2">আপনার মতামত/অভিযোগ দিন</a>
            </div>

            <div id="description" class="batch-content-section">
                @if($value->details)
                <div class="batch-info-card lebd-content-group">
                    <div class="lebd-group-head"><span class="lebd-group-number">০১</span><h4>কোর্স সম্পর্কে বিস্তারিত</h4></div>
                    <div class="lebd-group-copy">{!! $value->details !!}</div>
                </div>
                @else
                <div class="batch-info-card text-center">এই ব্যাচের জন্য এখনো কোনো বিস্তারিত তথ্য প্রকাশ করা হয়নি।</div>
                @endif
            </div>

            <!-- =================================================
                 ROUTINE
            ================================================== -->

            <div id="routine" class="batch-content-section">
                <div class="batch-info-card lebd-routine-v55">
                    <div class="lebd-r55-header">
                        <div class="lebd-r55-symbol"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></div>
                        <div><h4>এক্সাম / ক্লাস রুটিন</h4><p>আপনার ডিপার্টমেন্ট নির্বাচন করে এই ব্যাচের রুটিন দেখুন</p></div>
                    </div>
                    @if(!empty($routine))
                        @php
                            // Only departments assigned both to this batch and this published routine.
                            $batchRoutineDepartmentIds = $value->departments->pluck('id')->map(fn($v) => (int) $v)->all();
                            $routineDepartmentList = $routine->departments->filter(fn($d) => in_array((int) $d->id, $batchRoutineDepartmentIds, true));
                        @endphp
                        @if($routineDepartmentList->count())
                            <div class="lebd-r55-label"><i class="fa fa-th-large"></i> ডিপার্টমেন্ট নির্বাচন করুন</div>
                            <div class="lebd-r55-departments" id="lebdRoutineDepartments" role="group" aria-label="রুটিনের ডিপার্টমেন্ট">
                                @foreach($routineDepartmentList as $department)
                                    <button type="button" class="lebd-r55-dept-btn" data-department-id="{{ $department->id }}" aria-pressed="false">
                                        <i class="fa fa-graduation-cap" aria-hidden="true"></i> {{ $department->name }}
                                    </button>
                                @endforeach
                            </div>
                            <div class="lebd-r55-actions" id="lebdRoutineActions" hidden>
                                <button type="button" class="lebd-r55-action" onclick="window.print()"><i class="fa fa-print"></i> প্রিন্ট / PDF</button>
                            </div>
                            <div class="lebd-r55-status" id="lebdRoutineStatus" role="status">একটি ডিপার্টমেন্ট বাটনে ক্লিক করে রুটিন দেখুন।</div>
                            <div class="lebd-r55-results" id="lebdRoutineResults" aria-live="polite"></div>
                        @else
                            <div class="lebd-r55-status">এই ব্যাচের জন্য প্রকাশিত রুটিনে কোনো ডিপার্টমেন্ট যুক্ত নেই।</div>
                        @endif
                    @else
                        <div class="lebd-r55-status">এই ব্যাচের জন্য এখনো কোনো রুটিন প্রকাশ করা হয়নি।</div>
                    @endif
                </div>
            </div>

            <!-- =================================================
                 SAMPLE PDF
            ================================================== -->

            <div id="sample-pdf"
                 class="batch-content-section">


                @if(
                    $value->departments->count() ||
                    $course_syllabuses->count()
                )


                    <div class="batch-info-card">

                        <h4>
                            নমুনা PDF
                        </h4>


                        <div class="accordion batch-accordion"
                             id="batchSyllabusAccordion">


                            <!-- COURSE SYLLABUS -->

                            @if($course_syllabuses->count())

                                <div class="accordion-item">

                                    <h2 class="accordion-header">

                                        <button
                                            class="accordion-button"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#batch_syllabus"
                                            aria-expanded="true"
                                        >

                                            ব্যাচ এর সিলেবাস সমূহ

                                        </button>

                                    </h2>


                                    <div
                                        id="batch_syllabus"
                                        class="accordion-collapse collapse show"
                                    >

                                        <div class="table-responsive">

                                            <table class="table">

                                                @foreach(
                                                    $course_syllabuses
                                                    as $syllabus
                                                )

                                                    <tr>

                                                        <th>

                                                            {{ $syllabus->name }}

                                                        </th>

                                                        <td>

                                                            @if(
                                                                $student &&
                                                                $batch->students()
                                                                ->where('id',$student->id)
                                                                ->exists()
                                                            )

                                                                <a
                                                                    class="btn btn-default"
                                                                    href="{{ route('student.syllabus',$syllabus->id) }}"
                                                                >

                                                                    <i class="fa fa-eye"></i>
                                                                    View

                                                                </a>


                                                                <a
                                                                    href="{{ route('students.syllabus.pdf',$syllabus->id) }}"
                                                                    class="btn btn-info"
                                                                >

                                                                    <i class="fa fa-download"></i>

                                                                    ডাউনলোড

                                                                </a>

                                                            @else

                                                                <span class="text-warning">
                                                                    Premium
                                                                </span>

                                                            @endif

                                                        </td>

                                                    </tr>

                                                @endforeach

                                            </table>

                                        </div>

                                    </div>

                                </div>

                            @endif



                            <!-- DEPARTMENT SYLLABUS -->

                            @foreach(
                                $value->departments
                                as $key => $department
                            )

                                <div class="accordion-item">

                                    <h2 class="accordion-header">

                                        <button
                                            class="accordion-button collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#dept{{ $key }}"
                                        >

                                            {{ $department->name }}

                                        </button>

                                    </h2>


                                    <div
                                        id="dept{{ $key }}"
                                        class="accordion-collapse collapse"
                                    >

                                        <div class="card-body">

                                            @php

                                                $syllabuses =
                                                    $department
                                                    ->syllabus()
                                                    ->where(
                                                        'batch_id',
                                                        $value->id
                                                    )
                                                    ->get();

                                            @endphp


                                            @if($syllabuses->count())

                                                <div class="table-responsive">

                                                    <table class="table">

                                                        @foreach(
                                                            $syllabuses
                                                            as $syllabus
                                                        )

                                                            <tr>

                                                                <th>

                                                                    {{ $syllabus->name }}

                                                                </th>


                                                                <td>

                                                                    @if(
                                                                        $student &&
                                                                        $syllabus
                                                                        ->batch
                                                                        ->students()
                                                                        ->where(
                                                                            'student_id',
                                                                            $user->id
                                                                        )
                                                                        ->exists()
                                                                    )

                                                                        <a
                                                                            class="btn btn-default"
                                                                            href="{{ route('student.syllabus',$syllabus->id) }}"
                                                                        >

                                                                            <i class="fa fa-eye"></i>

                                                                            View

                                                                        </a>


                                                                        <a
                                                                            href="{{ route('students.syllabus.pdf',$syllabus->id) }}"
                                                                            class="btn btn-info"
                                                                        >

                                                                            <i class="fa fa-download"></i>

                                                                            ডাউনলোড

                                                                        </a>

                                                                    @else

                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-warning"
                                                                            onclick="alert('This is premium version. Buy the course first.')"
                                                                        >

                                                                            ডাউনলোড Full PDF

                                                                        </button>

                                                                    @endif

                                                                </td>


                                                                <td>

                                                                    @if($syllabus->sample_pdf)

                                                                        <a
                                                                            href="{{ $syllabus->sample_pdf }}"
                                                                            target="_blank"
                                                                            class="btn btn-info"
                                                                        >

                                                                            <i class="fa fa-download"></i>

                                                                            স্যাম্পল PDF

                                                                        </a>

                                                                    @endif

                                                                </td>

                                                            </tr>

                                                        @endforeach

                                                    </table>

                                                </div>

                                            @else

                                                <p>

                                                    এই ডিপার্টমেন্টের জন্যে এখনো কোনো সিলেবাস প্রকাশিত হয়নি।

                                                </p>

                                            @endif

                                        </div>

                                    </div>

                                </div>

                            @endforeach


                        </div>

                    </div>


                @else


                    <div class="batch-info-card text-center">

                        এই ব্যাচের জন্য কোনো Sample PDF নেই।

                    </div>


                @endif


            </div>



            <!-- =================================================
                 TEACHER — Admin-linked instructor cards (v44)
                 Uses existing Batch::teachers belongsToMany relation.
            ================================================== -->
            <div id="teacher" class="batch-content-section">
                <div class="batch-info-card lebd-instructor-section">
                    <div class="lebd-instructor-heading">
                        <span class="lebd-instructor-heading-icon"><i class="fa fa-users" aria-hidden="true"></i></span>
                        <div>
                            <h4>কোর্স ইন্সট্রাক্টর</h4>
                            <p>এই ব্যাচের সঙ্গে যুক্ত শিক্ষকদের পরিচিতি</p>
                        </div>
                        <span class="lebd-instructor-total">{{ $value->teachers->count() }} জন শিক্ষক</span>
                    </div>

                    @forelse($value->teachers as $teacher)
                        <article class="lebd-instructor-card lebd-instructor-card-v46">
                            <div class="lebd-instructor-primary-v46">
                                <div class="lebd-instructor-avatar">
                                    @if($teacher->image)
                                        <img src="{{ $teacher->image }}" alt="{{ $teacher->name }}"
                                             loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                        <span class="lebd-avatar-fallback" style="display:none"><i class="fa fa-user" aria-hidden="true"></i></span>
                                    @else
                                        <span class="lebd-avatar-fallback"><i class="fa fa-user" aria-hidden="true"></i></span>
                                    @endif
                                </div>
                                <div class="lebd-instructor-details">
                                    <h5>{{ $teacher->name }}</h5>
                                    @if($teacher->designation)
                                        <div class="lebd-instructor-designation">{!! $teacher->designation !!}</div>
                                    @endif
                                    <div class="lebd-instructor-note"><i class="fa fa-check-circle" aria-hidden="true"></i> এই কোর্সের শিক্ষক</div>
                                    @if($teacher->bio)
                                        <p class="t45-teacher-bio">{{ $teacher->bio }}</p>
                                    @endif
                                    @if($teacher->qualification || $teacher->institution)
                                        <div class="t45-teacher-meta"><i class="fa fa-graduation-cap"></i> {{ implode(' — ', array_filter([$teacher->qualification, $teacher->institution])) }}</div>
                                    @endif
                                    @if($teacher->specialization)
                                        <div class="t45-teacher-meta"><i class="fa fa-book"></i> {{ $teacher->specialization }}</div>
                                    @endif
                                    @if($teacher->achievements)
                                        <div class="t45-teacher-meta"><i class="fa fa-trophy"></i> {{ $teacher->achievements }}</div>
                                    @endif
                                    @if($teacher->profile_details)
                                        <details class="t45-teacher-more"><summary>বিস্তারিত পরিচিতি দেখুন</summary><p>{{ $teacher->profile_details }}</p></details>
                                    @endif
                                </div>
                            </div>
                            <div class="lebd-instructor-aside-v46">
                                @if($teacher->experience_years !== null)
                                    <div class="lebd-instructor-stat-v46"><i class="fa fa-briefcase" aria-hidden="true"></i><span>{{ $teacher->experience_years }} বছরের অভিজ্ঞতা</span></div>
                                @endif
                                @if($teacher->total_classes !== null)
                                    <div class="lebd-instructor-stat-v46"><i class="fa fa-book" aria-hidden="true"></i><span>{{ number_format($teacher->total_classes) }} ক্লাস নিয়েছেন</span></div>
                                @endif
                                @if($teacher->total_students !== null)
                                    <div class="lebd-instructor-stat-v46"><i class="fa fa-users" aria-hidden="true"></i><span>{{ number_format($teacher->total_students) }} শিক্ষার্থী</span></div>
                                @endif
                                @if($teacher->profile_details)
                                    <div class="lebd-instructor-more-hint-v46"><i class="fa fa-info-circle" aria-hidden="true"></i> পরিচিতি বামে দেখুন</div>
                                @endif
                                @if($teacher->experience_years === null && $teacher->total_classes === null && $teacher->total_students === null)
                                    <div class="lebd-instructor-pending-v46"><i class="fa fa-id-card-o" aria-hidden="true"></i> শিক্ষক পরিচিতি</div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="lebd-instructor-empty">এই ব্যাচের জন্য শিক্ষক তথ্য এখনো যুক্ত করা হয়নি।</div>
                    @endforelse
                </div>
            </div>

        </div>



        <!-- =================================================
             RIGHT PURCHASE AREA
        ================================================== -->

        <div class="batch-right-area">


            <div class="batch-purchase-card lebd-purchase-v54">


                <!-- MEDIA -->

                <div class="batch-purchase-media lebd-purchase-v54-media">
                    <span class="lebd-v54-video-tag"><i class="fa fa-play-circle"></i> কোর্স ভিডিও</span>


                    @if($value->video)

                        <div class="batch-video-wrapper">

                            <iframe
                                src="https://www.youtube.com/embed/{{ $value->video }}?autoplay=1&mute=1"
                                allowfullscreen
                            ></iframe>

                        </div>

                    @else

                        <img
                            src="{{ $value->banner
                                ? $value->banner
                                : '/img/course.png' }}"
                            alt="{{ $value->name }}"
                        >

                    @endif


                </div>



                <!-- BODY -->

                <div class="batch-purchase-body lebd-v54-body">


                    <!-- PRICE -->

                    @if($price > 0)

                        <div class="batch-price lebd-v54-price">


                            @if($discount)

                                <del>
                                    ৳{{ $source->point0($price) }}
                                </del>


                                <strong>
                                    ৳{{ $source->point0($selling_price) }}
                                </strong>


                                <div class="batch-save">

                                    Save ৳{{ $source->point0($discount) }}

                                </div>

                            @else

                                <strong>

                                    ৳{{ $source->point0($price) }}

                                </strong>

                            @endif


                        </div>

                    @endif



                    <!-- STUDENTS -->

                    <table class="batch-student-count lebd-v54-enrolled">

                        <tr>

                            <td>

                                <i class="fa fa-users"></i>

                                এই কোর্সটি করছেন

                            </td>

                            <th>

                                {{
                                    $value->students->count()
                                    +
                                    ($value->extra_student ?? 0)
                                }}

                                জন

                            </th>

                        </tr>

                    </table>



                    <!-- BUY -->

                    @if($alreadyPurchased)


                        <button
                            type="button"
                            class="batch-buy-button batch-purchased"
                            disabled
                        >

                            আপনি কোর্সটি কিনেছেন

                        </button>


                    @elseif(
                        $value->reg_end_at &&
                        !$registrationActive
                    )


                        <div class="batch-registration-ended">

                            রেজিস্ট্রেশনের মেয়াদ শেষ

                        </div>


                    @else


                        @if(
                            $value->offer_end_at &&
                            $value->offer_end_at > now()
                        )

                            <p class="lebd-v54-offer">

                                ডিস্কাউন্ট অফারের মেয়াদ

                                {{ $source->dformat($value->offer_end_at) }}

                            </p>

                        @endif


                        <!--
                        IMPORTANT:
                        Existing checkout route preserved.
                        -->

                        <a
                            href="{{ route('students.course.checkout',$batch->id) }}"
                            class="batch-buy-button"
                        >

                            <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                            কোর্সটি কিনুন
                            <i class="fa fa-arrow-right" aria-hidden="true"></i>

                        </a>


                    @endif


                </div>


            </div>


        </div>


    </div>

</div>

</section>



<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<style>

/* =========================================================
   RESET / MAIN
========================================================= */

.batch-detail-page{

    width:100%;

    max-width:1200px;

    margin:0 auto;

    padding:25px 15px 60px;

}

.batch-detail-container{

    width:100%;

    max-width:1150px;

    margin:0 auto;

}


/* =========================================================
   COURSE TITLE
========================================================= */

.batch-course-title{

    text-align:center;

    margin-bottom:20px;

}

.batch-course-title h1{

    margin:0;

    font-size:34px;

    font-weight:700;

    color:#092b68;

}

.batch-course-title p{

    margin:7px 0 0;

    color:#666;

    font-size:16px;

}


/* =========================================================
   MAIN GRID
========================================================= */

.batch-main-grid{

    display:grid;

    grid-template-columns:minmax(0, 1fr) 340px;

    gap:22px;

    align-items:start;

}


/* =========================================================
   LEFT AREA
========================================================= */

.batch-left-area{

    min-width:0;

}


/* =========================================================
   BATCH HERO
========================================================= */

.batch-hero{

    width:100%;

    min-height:200px;

    padding:20px;

    border-radius:12px;

    background:
        linear-gradient(
            15deg,
            #01beed,
            #00060a
        );

    color:#fff;

    box-shadow:
        0 8px 25px rgba(0,0,0,.12);

}


.batch-hero h2{

    margin:0;

    font-size:22px;

    font-weight:700;

}


.batch-hero-countdown{

    text-align:center;

    margin-top:8px;

    color:#ff1d1d;

    font-size:14px;

    font-weight:600;

}


.batch-hero-description{

    margin-top:20px;

    min-height:100px;

    line-height:1.7;

}


/* =========================================================
   PURCHASE CARD
========================================================= */

.batch-purchase-card{

    background:#fff;

    border:1px solid #dce8f5;

    border-radius:15px;

    overflow:hidden;

    box-shadow:
        0 8px 25px rgba(0,0,0,.10);

}


.batch-purchase-media{

    width:100%;

    background:#eef5fc;

    overflow:hidden;

}


.batch-purchase-media img{

    width:100%;

    display:block;

    height:200px;

    object-fit:cover;

}


.batch-video-wrapper{

    position:relative;

    width:100%;

    padding-bottom:56.25%;

    height:0;

    overflow:hidden;

}


.batch-video-wrapper iframe{

    position:absolute;

    top:0;
    left:0;

    width:100%;
    height:100%;

    border:0;

}


.batch-purchase-body{

    padding:18px;

}


.batch-price{

    margin-bottom:12px;

    font-size:20px;

}


.batch-price del{

    color:#777;

    margin-right:7px;

}


.batch-price strong{

    color:#0875df;

}


.batch-save{

    display:inline-block;

    margin-top:5px;

    padding:5px 9px;

    background:#ffc107;

    color:#222;

    border-radius:6px;

    font-size:12px;

}


.batch-student-count{

    width:100%;

    border-collapse:collapse;

    margin:15px 0;

}


.batch-student-count td,
.batch-student-count th{

    border:1px solid #ddd;

    padding:9px;

    font-size:13px;

}


.batch-student-count td{

    color:#1cab55;

}


.batch-student-count th{

    text-align:right;

    color:#1cab55;

}


/* =========================================================
   BUY BUTTON
========================================================= */

.batch-buy-button{

    display:block;

    width:100%;

    border:0;

    padding:13px 15px;

    border-radius:9px;

    background:#198754;

    color:#fff !important;

    text-align:center;

    text-decoration:none !important;

    font-size:17px;

    font-weight:700;

    cursor:pointer;

    transition:.25s ease;

}


.batch-buy-button:hover{

    background:#146c43;

    transform:translateY(-1px);

}


.batch-purchased{

    background:#198754;

    cursor:not-allowed;

}


.batch-registration-ended{

    color:#dc3545;

    text-align:center;

    font-weight:600;

    padding:10px;

}


/* =========================================================
   TAB AREA
========================================================= */

.batch-detail-tabs{

    width:100%;

    margin:25px auto;

    padding:7px;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:8px;

    background:#f7fbff;

    border:1px solid #dce8f5;

    border-radius:15px;

    box-shadow:
        0 4px 15px rgba(0,80,160,.07);

    overflow-x:auto;

    scrollbar-width:none;

}


.batch-detail-tabs::-webkit-scrollbar{

    display:none;

}


.batch-detail-tab{

    flex:1 1 0;

    min-width:115px;

    min-height:43px;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:7px;

    padding:9px 13px;

    background:#fff;

    color:#123d79 !important;

    border:1px solid #d5e2f0;

    border-radius:9px;

    text-decoration:none !important;

    font-size:13px;

    font-weight:600;

    white-space:nowrap;

    cursor:pointer;

    transition:.25s ease;

}


.batch-detail-tab:hover{

    background:#eaf4ff;

    border-color:#0875df;

    color:#0869dc !important;

}


.batch-detail-tab.active{

    background:
        linear-gradient(
            135deg,
            #0875df,
            #1269c9
        );

    color:#fff !important;

    border-color:#0875df;

    box-shadow:
        0 5px 13px rgba(8,117,223,.22);

}


/* =========================================================
   CONTENT SECTIONS
========================================================= */

.batch-content-section{

    display:none;

    width:100%;

    margin-top:20px;

    animation:batchFade .25s ease;

}


.batch-content-section.active{

    display:block;

}


@keyframes batchFade{

    from{

        opacity:0;

        transform:translateY(8px);

    }

    to{

        opacity:1;

        transform:translateY(0);

    }

}


/* =========================================================
   CONTENT CARD
========================================================= */

.batch-info-card{

    background:#fff;

    border:1px solid #dce8f5;

    border-radius:15px;

    padding:20px;

    margin-bottom:20px;

    box-shadow:
        0 5px 18px rgba(0,0,0,.06);

}


.batch-info-card h3,
.batch-info-card h4{

    color:#092b68;

    margin-top:0;

}


/* =========================================================
   SYLLABUS
========================================================= */

.batch-accordion{

    width:100%;

}


.batch-accordion .accordion-item{

    margin-bottom:10px;

    border:1px solid #dce8f5;

    border-radius:10px;

    overflow:hidden;

}


.batch-accordion .accordion-button{

    font-weight:600;

    color:#123d79;

}


.batch-accordion table{

    width:100%;

}


/* =========================================================
   TEACHER
========================================================= */

.batch-teacher{

    display:flex;

    align-items:center;

    gap:15px;

    padding:12px;

    border-bottom:1px solid #eee;

}


.batch-teacher:last-child{

    border-bottom:0;

}


.batch-teacher img{

    width:70px;

    height:70px;

    object-fit:cover;

    border-radius:50%;

}


/* =========================================================
   ROUTINE TABLE
========================================================= */

.batch-routine-wrapper{

    width:100%;

    overflow-x:auto;

}


#batch-routine-table{

    width:100%;

    min-width:700px;

    border-collapse:collapse;

}


#batch-routine-table th,
#batch-routine-table td{

    border:1px solid #ddd;

    padding:8px;

    text-align:center;

    font-size:13px;

}


/* =========================================================
   DESKTOP
========================================================= */

@media(min-width:1001px){

      .batch-detail-tabs{

        width:100%;

        margin:16px 0;

        padding:4px;

        gap:3px;

        display:flex;

        flex-wrap:nowrap;

        overflow:hidden;

        justify-content:space-between;

        box-sizing:border-box;

    }


    .batch-detail-tab{

        flex:1 1 20%;

        min-width:0;

        width:20%;

        max-width:20%;

        min-height:38px;

        height:38px;

        padding:5px 2px;

        margin:0;

        gap:2px;

        border-radius:7px;

        font-size:9px;

        line-height:1;

        white-space:nowrap;

        overflow:hidden;

        text-overflow:ellipsis;

        box-sizing:border-box;

    }


    .batch-detail-tab i{

        font-size:9px;

        flex-shrink:0;

    }

}


/* =========================================================
   TABLET
========================================================= */

@media(max-width:1000px){

    .batch-main-grid{

        grid-template-columns:1fr;

    }

    .batch-purchase-card{

        max-width:500px;

        margin:0 auto;

    }

}


/* =========================================================
   MOBILE FINAL FIX
   HEADER → PURCHASE CARD → TABS
   DESKTOP UNCHANGED
========================================================= */

@media (max-width:600px){

    /* =====================================================
       1. HEADER — ALWAYS AT TOP
    ===================================================== */

    #Header{
        position:fixed !important;
        top:0 !important;
        left:0 !important;
        right:0 !important;

        width:100% !important;

        z-index:99999 !important;

        background:#ffffff !important;

        display:block !important;
    }


    /*
    Header-এর নিচে content শুরু হবে
    */

    body{
        padding-top:125px !important;
    }


    /* Header-এর extra <br><br> এর প্রভাব কমানো */
    #Header br{
        display:none !important;
    }



    /* =====================================================
       2. MAIN BATCH PAGE
    ===================================================== */

    .batch-detail-page{

        width:100% !important;

        max-width:100% !important;

        margin:0 !important;

        padding:8px !important;

        box-sizing:border-box !important;
    }


    .batch-detail-container{

        width:100% !important;

        max-width:100% !important;

        margin:0 !important;

        padding:0 !important;
    }



    /* =====================================================
       3. MAIN GRID
    ===================================================== */

    .batch-main-grid{

        display:flex !important;

        flex-direction:column !important;

        width:100% !important;

        gap:10px !important;

        margin:0 !important;

        padding:0 !important;
    }



    /* =====================================================
       4. COURSE TITLE HIDE
    ===================================================== */

    .batch-course-title{

        display:none !important;
    }



    /* =====================================================
       5. LEFT AREA — KEEP IT
       কারণ এর ভিতরেই TAB BUTTON আছে
    ===================================================== */

    .batch-left-area{

        display:flex !important;

        flex-direction:column !important;

        width:100% !important;

        min-width:0 !important;

        order:2 !important;

        margin:0 !important;

        padding:0 !important;
    }



    /* =====================================================
       6. BATCH HERO HIDE
       Batch 3 / বড় description mobile-এ লাগবে না
    ===================================================== */

    .batch-hero{

        display:none !important;
    }



    /* =====================================================
       7. PURCHASE CARD FIRST
    ===================================================== */

    .batch-right-area{

        display:block !important;

        width:100% !important;

        max-width:100% !important;

        order:1 !important;

        margin:0 !important;

        padding:0 !important;
    }


    .batch-purchase-card{

        display:block !important;

        width:100% !important;

        max-width:100% !important;

        margin:0 auto !important;

        border-radius:10px !important;

        overflow:hidden !important;

        box-sizing:border-box !important;

        background:#ffffff !important;

        box-shadow:
            0 4px 15px rgba(0,0,0,.10) !important;
    }



    /* =====================================================
       8. PURCHASE IMAGE
    ===================================================== */

    .batch-purchase-media{

        width:100% !important;

        height:auto !important;

        min-height:0 !important;

        overflow:hidden !important;
    }


    .batch-purchase-media img{

        display:block !important;

        width:100% !important;

        height:auto !important;

        min-height:125px !important;

        max-height:220px !important;

        object-fit:cover !important;
    }



    /* =====================================================
       9. VIDEO
    ===================================================== */

    .batch-video-wrapper{

        width:100% !important;

        height:0 !important;

        padding-bottom:56.25% !important;

        position:relative !important;
    }


    .batch-video-wrapper iframe{

        position:absolute !important;

        top:0 !important;

        left:0 !important;

        width:100% !important;

        height:100% !important;
    }



    /* =====================================================
       10. PURCHASE BODY
    ===================================================== */

    .batch-purchase-body{

        padding:10px 12px 12px !important;
    }



    /* PRICE */

    .batch-price{

        margin:0 0 7px !important;

        font-size:16px !important;

        line-height:1.3 !important;
    }


    .batch-price del{

        font-size:13px !important;

        margin-right:4px !important;
    }


    .batch-price strong{

        font-size:17px !important;
    }


    .batch-save{

        font-size:9px !important;

        padding:4px 6px !important;

        margin-left:3px !important;
    }



    /* =====================================================
       11. STUDENT COUNT
    ===================================================== */

    .batch-student-count{

        width:100% !important;

        margin:8px 0 !important;

        border-collapse:collapse !important;
    }


    .batch-student-count td,
    .batch-student-count th{

        padding:7px 5px !important;

        font-size:10px !important;
    }



    /* =====================================================
       12. REGISTRATION MESSAGE
    ===================================================== */

    .batch-registration-ended{

        padding:7px 3px !important;

        font-size:10px !important;

        text-align:center !important;
    }



    /* =====================================================
       13. BUY BUTTON
    ===================================================== */

    .batch-buy-button{

        display:block !important;

        width:100% !important;

        min-height:36px !important;

        padding:8px 10px !important;

        margin:0 !important;

        border-radius:7px !important;

        font-size:15px !important;

        line-height:1.2 !important;

        text-align:center !important;

        box-sizing:border-box !important;
    }



    /* =====================================================
       14. TABS — SHOW
    ===================================================== */

    .batch-detail-tabs{

        display:flex !important;

        width:100% !important;

        margin:10px 0 0 !important;

        padding:4px !important;

        gap:3px !important;

        justify-content:space-between !important;

        align-items:center !important;

        overflow:hidden !important;

        box-sizing:border-box !important;

        order:1 !important;
    }



    /* =====================================================
       15. ALL 5 BUTTONS VISIBLE
    ===================================================== */

    .batch-detail-tab{

        display:flex !important;

        flex:1 1 20% !important;

        width:20% !important;

        min-width:0 !important;

        max-width:20% !important;

        height:36px !important;

        min-height:36px !important;

        padding:5px 2px !important;

        margin:0 !important;

        gap:2px !important;

        border-radius:7px !important;

        font-size:8px !important;

        line-height:1 !important;

        white-space:nowrap !important;

        overflow:hidden !important;

        text-overflow:ellipsis !important;

        box-sizing:border-box !important;
    }


    .batch-detail-tab i{

        display:inline-block !important;

        font-size:8px !important;

        flex-shrink:0 !important;
    }



    /* =====================================================
       16. CONTENT HIDE INITIALLY
       Button থাকবে, কিন্তু নিচের content থাকবে না
    ===================================================== */

    .batch-content-section{

        display:none !important;

        width:100% !important;

        margin-top:10px !important;
    }


    /*
    JavaScript active করলে selected section দেখাবে
    */

    .batch-content-section.active{

        display:block !important;
    }



    /* =====================================================
       17. VERY SMALL PHONE
    ===================================================== */

    @media(max-width:380px){

        .batch-detail-tab{

            height:34px !important;

            min-height:34px !important;

            padding:4px 1px !important;

            font-size:7px !important;
        }


        .batch-detail-tab i{

            font-size:7px !important;
        }


        .batch-purchase-body{

            padding:9px !important;
        }


        .batch-buy-button{

            font-size:14px !important;

            padding:8px !important;
        }

    }

}


/* =========================================================
   FINAL MOBILE LAYOUT FIX
   PURCHASE → TABS → CONTENT
   DESKTOP UNCHANGED
========================================================= */

@media (max-width:600px){

    /* =========================================
       MAIN LEFT AREA
    ========================================= */

    .batch-left-area{
        display:flex !important;
        flex-direction:column !important;

        width:100% !important;
        min-width:0 !important;

        margin:0 !important;
        padding:0 !important;

        order:2 !important;
    }


    /* =========================================
       HERO HIDDEN ON MOBILE
    ========================================= */

    .batch-hero{
        display:none !important;
    }


    /* =========================================
       TABS
       ALWAYS BEFORE CONTENT
    ========================================= */

    .batch-detail-tabs{
        display:flex !important;

        width:100% !important;

        order:1 !important;

        margin:10px 0 10px 0 !important;

        padding:4px !important;

        gap:3px !important;

        align-items:center !important;
        justify-content:space-between !important;

        background:#f7fbff !important;

        border:1px solid #dce8f5 !important;

        border-radius:10px !important;

        box-sizing:border-box !important;

        overflow:hidden !important;

        position:relative !important;

        z-index:10 !important;
    }


    /* =========================================
       ALL FIVE BUTTONS
    ========================================= */

    .batch-detail-tab{
        display:flex !important;

        flex:1 1 20% !important;

        width:20% !important;

        min-width:0 !important;
        max-width:20% !important;

        height:36px !important;
        min-height:36px !important;

        margin:0 !important;

        padding:5px 2px !important;

        align-items:center !important;
        justify-content:center !important;

        gap:2px !important;

        border-radius:7px !important;

        font-size:8px !important;

        line-height:1 !important;

        white-space:nowrap !important;

        overflow:hidden !important;

        text-overflow:ellipsis !important;

        box-sizing:border-box !important;
    }


    .batch-detail-tab i{
        display:inline-block !important;

        font-size:8px !important;

        flex-shrink:0 !important;
    }


    /* =========================================
       CONTENT
       MUST COME AFTER TABS
    ========================================= */

    .batch-content-section{
        display:none !important;

        width:100% !important;

        order:2 !important;

        margin:10px 0 20px 0 !important;

        box-sizing:border-box !important;
    }


    /* Overview / clicked section */

    .batch-content-section.active{
        display:block !important;
    }


    /* =========================================
       CONTENT CARD
    ========================================= */

    .batch-info-card{
        width:100% !important;

        box-sizing:border-box !important;

        margin:0 0 15px 0 !important;

        padding:15px !important;

        border-radius:12px !important;

        overflow:hidden !important;
    }


    /* =========================================
       ROUTINE
    ========================================= */

    .batch-routine-wrapper{
        width:100% !important;

        overflow-x:auto !important;
    }


    #batch-routine-table{
        min-width:700px !important;
    }


    /* =========================================
       VERY SMALL MOBILE
    ========================================= */

    @media(max-width:380px){

        .batch-detail-tabs{
            gap:2px !important;

            padding:3px !important;
        }

        .batch-detail-tab{
            height:34px !important;
            min-height:34px !important;

            padding:4px 1px !important;

            font-size:7px !important;
        }

        .batch-detail-tab i{
            font-size:7px !important;
        }

    }

}



/* LEBD-3 course content groups */
.lebd-content-group{padding:20px 22px; border-color:#d9e7f5;}
.lebd-group-head{display:flex;align-items:center;gap:11px;margin-bottom:15px;padding-bottom:13px;border-bottom:1px solid #e9eff8;}
.lebd-group-head h4{margin:0!important;font-size:20px;font-weight:700;color:#0a376e;}
.lebd-group-number{display:inline-flex;min-width:37px;height:37px;padding:0 7px;align-items:center;justify-content:center;border-radius:10px;background:#eaf4ff;color:#0b68ce;font-size:15px;font-weight:800;}
.lebd-subheading{font-size:16px;font-weight:700;color:#134b80;margin:14px 0 8px;}
.lebd-group-copy{font-size:15px;line-height:1.85;color:#26384b;overflow-wrap:anywhere;}
.lebd-group-copy p{margin-bottom:10px;}
.lebd-group-copy ul,.lebd-group-copy ol{padding-left:24px;margin:8px 0 14px;}
.lebd-group-copy li{margin-bottom:6px;}
.lebd-group-copy img{max-width:100%;height:auto;}
.lebd-course-dates{margin:0;}
.lebd-course-dates td{width:42%;font-weight:500;}
.lebd-course-dates th{font-weight:700;}
.lebd-routine-note{color:#64748b;font-size:13px;margin:11px 0 0;}
@media(max-width:600px){.lebd-content-group{padding:15px!important}.lebd-group-head h4{font-size:17px}.lebd-group-copy{font-size:14px}.lebd-course-dates td{width:43%;}}


.t45-teacher-bio{margin:9px 0;color:#55677e;line-height:1.7;white-space:pre-line}.t45-teacher-meta{font-size:12px;color:#416181;margin-top:7px;white-space:pre-line}.t45-teacher-stats{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}.t45-teacher-stats span{padding:5px 9px;background:#eaf4ff;border-radius:16px;color:#1c65a7;font-weight:600;font-size:11px}.t45-teacher-more{margin-top:12px;color:#315985;font-size:13px}.t45-teacher-more summary{cursor:pointer;font-weight:700}.t45-teacher-more p{margin-top:7px;white-space:pre-line}
/* LEBD-3 v44: Instructor cards only; other tab/checkout/routine styles untouched. */
#teacher .lebd-instructor-section{padding:22px 24px}
#teacher .lebd-instructor-heading{display:flex;align-items:center;gap:13px;padding-bottom:18px;margin-bottom:15px;border-bottom:1px solid #e3ebf5}
#teacher .lebd-instructor-heading-icon{width:44px;height:44px;display:flex;align-items:center;justify-content:center;flex:0 0 44px;background:#e7f1ff;border-radius:12px;color:#0875df;font-size:20px}
#teacher .lebd-instructor-heading h4{font-size:23px;line-height:1.35;margin:0 0 4px;color:#092b68;font-weight:700}
#teacher .lebd-instructor-heading p{font-size:13px;color:#66788c;margin:0}
#teacher .lebd-instructor-total{margin-left:auto;background:#eff6ff;color:#1154a5;border:1px solid #d3e5ff;border-radius:20px;padding:6px 10px;font-size:12px;white-space:nowrap;font-weight:700}
#teacher .lebd-instructor-card{display:flex;align-items:center;gap:20px;padding:18px;margin:12px 0;background:#fff;border:1px solid #dbe7f4;border-radius:14px;box-shadow:0 3px 14px rgba(17,57,100,.045);transition:border-color .2s,transform .2s,box-shadow .2s}
#teacher .lebd-instructor-card:hover{border-color:#94beec;box-shadow:0 8px 22px rgba(15,71,129,.09);transform:translateY(-2px)}
#teacher .lebd-instructor-avatar{width:104px;height:104px;flex:0 0 104px;overflow:hidden;border-radius:50%;background:#e5f0ff;border:4px solid #edf5ff;display:flex;align-items:center;justify-content:center}
#teacher .lebd-instructor-avatar img{width:100%;height:100%;display:block;object-fit:cover}
#teacher .lebd-avatar-fallback{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#6b97c8;font-size:42px}
#teacher .lebd-instructor-details{flex:1;min-width:0}
#teacher .lebd-instructor-details h5{font-size:21px;color:#0b2d65;font-weight:700;margin:0 0 8px;line-height:1.4;overflow-wrap:anywhere}
#teacher .lebd-instructor-designation{color:#1470d5;font-weight:600;line-height:1.6;font-size:15px;overflow-wrap:anywhere}
#teacher .lebd-instructor-designation p{margin:0 0 5px}
#teacher .lebd-instructor-note{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;background:#ecfdf3;color:#198754;border-radius:20px;margin-top:11px;font-size:12px;font-weight:600}
#teacher .lebd-instructor-empty{padding:25px;text-align:center;border-radius:10px;background:#f5f9fe;color:#52647a}
@media(max-width:600px){#teacher .lebd-instructor-section{padding:15px!important}#teacher .lebd-instructor-heading{flex-wrap:wrap}#teacher .lebd-instructor-heading h4{font-size:19px}#teacher .lebd-instructor-total{margin-left:0}#teacher .lebd-instructor-card{gap:12px;padding:12px}#teacher .lebd-instructor-avatar{height:72px;width:72px;flex-basis:72px;border-width:2px}#teacher .lebd-avatar-fallback{font-size:28px}#teacher .lebd-instructor-details h5{font-size:16px}#teacher .lebd-instructor-designation{font-size:13px}#teacher .lebd-instructor-note{font-size:11px}}


/* LEBD-3 v46 — true two-column instructor card on desktop, no effect outside teacher tab */
#teacher .lebd-instructor-card-v46{display:grid!important;grid-template-columns:minmax(0,1fr) minmax(125px,29%);gap:12px;align-items:stretch;padding:15px!important;}
#teacher .lebd-instructor-primary-v46{display:flex;align-items:flex-start;gap:13px;min-width:0;}
#teacher .lebd-instructor-primary-v46 .lebd-instructor-avatar{width:82px;height:82px;flex:0 0 82px;}
#teacher .lebd-instructor-primary-v46 .lebd-instructor-details{min-width:0;flex:1;}
#teacher .lebd-instructor-primary-v46 .lebd-instructor-details h5{font-size:18px;margin:0 0 4px;}
#teacher .lebd-instructor-primary-v46 .lebd-instructor-designation{font-size:13px;}
#teacher .lebd-instructor-primary-v46 .t45-teacher-bio{font-size:12px;margin:8px 0;}
#teacher .lebd-instructor-aside-v46{border-left:1px solid #e2ebf6;padding:5px 0 4px 12px;display:flex;flex-direction:column;justify-content:center;gap:11px;min-width:0;}
#teacher .lebd-instructor-stat-v46{display:flex;align-items:flex-start;gap:7px;line-height:1.35;color:#3b5372;font-size:12px;font-weight:600;overflow-wrap:anywhere;}
#teacher .lebd-instructor-stat-v46 i{color:#1266c8;font-size:13px;min-width:15px;margin-top:1px;}
#teacher .lebd-instructor-pending-v46{font-size:12px;color:#7386a0;line-height:1.5;}
#teacher .lebd-instructor-pending-v46 i{color:#1266c8;}
#teacher .lebd-instructor-more-hint-v46{font-size:10px;color:#7590ac;}
@media(max-width:600px){
  #teacher .lebd-instructor-card-v46{grid-template-columns:1fr!important;gap:12px;}
  #teacher .lebd-instructor-primary-v46 .lebd-instructor-avatar{width:68px;height:68px;flex:0 0 68px;}
  #teacher .lebd-instructor-aside-v46{border-left:0;border-top:1px solid #e2ebf6;padding:10px 0 0;flex-direction:row;flex-wrap:wrap;justify-content:flex-start;gap:9px 13px;}
  #teacher .lebd-instructor-stat-v46{font-size:11px;}
}

</style>



<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>


<script>

/* =========================================================
   BATCH TABS
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function(){

        const tabs =
            document.querySelectorAll(
                '.batch-detail-tab'
            );

        const sections =
            document.querySelectorAll(
                '.batch-content-section'
            );


        tabs.forEach(function(tab){

            tab.addEventListener(
                'click',
                function(e){

                    e.preventDefault();


                    const target =
                        this.getAttribute(
                            'data-target'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Remove active from tabs
                    |--------------------------------------------------------------------------
                    */

                    tabs.forEach(
                        function(item){

                            item.classList.remove(
                                'active'
                            );

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Hide all sections
                    |--------------------------------------------------------------------------
                    */

                    sections.forEach(
                        function(section){

                            section.classList.remove(
                                'active'
                            );

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Active clicked tab
                    |--------------------------------------------------------------------------
                    */

                    this.classList.add(
                        'active'
                    );


                    // Each tab shows its own content only; Overview is the default.
                    const selected = document.getElementById(target);
                    if (selected) selected.classList.add('active');

                    /*
                    |--------------------------------------------------------------------------
                    | Smooth scroll
                    |--------------------------------------------------------------------------
                    */

                    if(window.innerWidth <= 600){

                        setTimeout(
                            function(){

                                document
                                    .getElementById(target)
                                    ?.scrollIntoView({
                                        behavior:'smooth',
                                        block:'start'
                                    });

                            },
                            100
                        );

                    }

                }
            );

        }

    );

});



/* =========================================================
   REGISTRATION COUNTDOWN
========================================================= */

(function(){


    const timer =
        document.getElementById(
            'batchTimer'
        );


    @if($value->reg_end_at)

        const targetDate =
            new Date(
                "{{ \Carbon\Carbon::parse($value->reg_end_at)->format('Y-m-d H:i:s') }}"
                .replace(' ','T')
            ).getTime();


        if(timer && targetDate){

            const countdown =
                setInterval(
                    function(){

                        const now =
                            new Date().getTime();


                        const distance =
                            targetDate - now;


                        if(distance <= 0){

                            clearInterval(
                                countdown
                            );


                            timer.innerHTML =
                                'সময় শেষ';

                            timer.style.color =
                                '#ff0000';

                            return;

                        }


                        const days =
                            Math.floor(
                                distance /
                                (
                                    1000 *
                                    60 *
                                    60 *
                                    24
                                )
                            );


                        const hours =
                            Math.floor(
                                (
                                    distance %
                                    (
                                        1000 *
                                        60 *
                                        60 *
                                        24
                                    )
                                )
                                /
                                (
                                    1000 *
                                    60 *
                                    60
                                )
                            );


                        const minutes =
                            Math.floor(
                                (
                                    distance %
                                    (
                                        1000 *
                                        60 *
                                        60
                                    )
                                )
                                /
                                (
                                    1000 *
                                    60
                                )
                            );


                        const seconds =
                            Math.floor(
                                (
                                    distance %
                                    (
                                        1000 *
                                        60
                                    )
                                )
                                /
                                1000
                            );


                        timer.innerHTML =
                            days +
                            ' দিন ' +

                            hours +
                            ' ঘণ্টা ' +

                            minutes +
                            ' মিনিট ' +

                            seconds +
                            ' সেকেন্ড';

                    },
                    1000
                );

        }

    @endif

})();



/* =========================================================
   PDF DOWNLOAD
========================================================= */

function downloadPDF(){

    const element =
        document.getElementById(
            'batch-routine-table'
        );


    if(!element){

        alert(
            'Routine পাওয়া যায়নি।'
        );

        return;

    }


    html2pdf()
        .set({

            margin:10,

            filename:
                'batch-routine.pdf',

            image:{
                type:'jpeg',
                quality:.98
            },

            html2canvas:{
                scale:2
            },

            jsPDF:{
                unit:'mm',
                format:'a4',
                orientation:'portrait'
            }

        })

        .from(element)

        .save();

}



/* =========================================================
   LEBD V55 — Department specific published routine
========================================================= */
@if(!empty($routine))
(function(){
    const group = document.getElementById('lebdRoutineDepartments');
    const result = document.getElementById('lebdRoutineResults');
    const status = document.getElementById('lebdRoutineStatus');
    const actions = document.getElementById('lebdRoutineActions');
    if (!group || !result || !status) return;

    // Use the existing named route; avoid appending a second routine ID.
    const endpointTemplate = @json(route('home.routine.filter', ['id' => $routine->id, 'department' => '__DEPT_ID__']));
    let requestVersion = 0;
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[character]));
    const present = (value) => value !== null && value !== undefined && value !== '';

    // Routine topics are saved by the Admin rich-text editor as HTML.
    // Decode entities, remove markup, then escape output to keep it safe.
    function routinePlainText(value){
        let text = String(value ?? '');
        for (let n = 0; n < 3; n++) {
            const box = document.createElement('textarea');
            box.innerHTML = text;
            if (box.value === text) break;
            text = box.value;
        }
        text = text
            .replace(/<\s*br\s*\/?\s*>/gi, ' ')
            .replace(/<\/\s*(p|div|li|h[1-6])\s*>/gi, ' ')
            .replace(/<[^>]+>/g, ' ')
            .replace(/\u00a0/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        return text;
    }

    function renderRow(row, index){
        const subjects = Array.isArray(row.subjects) ? row.subjects : [];
        const depts = Array.isArray(row.departments) ? row.departments : [];
        const cells = [];
        subjects.forEach(item => {
            const name = item.subject?.name || item.name || 'বিষয়';
            const syllabus = routinePlainText(item.chapter || item.details || item.syllabus || '');
            cells.push('<tr><td>'+escapeHtml(name)+'</td><td>'+escapeHtml(syllabus)+'</td></tr>');
        });
        depts.forEach(item => {
            if (present(item.details)) cells.push('<tr><td>ডিপার্টমেন্ট</td><td>'+escapeHtml(routinePlainText(item.details))+'</td></tr>');
        });
        const start = row.exam_at ? dateTime(row.exam_at) : 'প্রকাশিত হয়নি';
        const end = row.exam_end_at ? dateTime(row.exam_end_at) : '';
        const examName = routinePlainText(row.paper_name || '');
        // Hide generic names such as Exam-01 when EXAM NO: 01 is already shown.
        const showName = examName && !/^exam[\s\-_]*0*\d+$/i.test(examName);
        return '<article class="lebd-r55-exam">'+
            '<div class="lebd-r55-exam-top"><span>EXAM NO: '+String(index+1).padStart(2,'0')+'</span><span class="lebd-r55-chip">'+(row.exam_at?dateCheck(row.exam_at):'Scheduled')+'</span></div>'+
            (showName ? '<h5>'+escapeHtml(examName)+'</h5>' : '')+
            '<div class="lebd-r55-datetime"><div><small>পরীক্ষার তারিখ ও সময়</small><strong>'+escapeHtml(start)+'</strong></div>'+ (end?'<div><small>শেষ সময়</small><strong>'+escapeHtml(end)+'</strong></div>':'')+'</div>'+
            (cells.length ? '<div class="lebd-r55-table-wrap"><table><thead><tr><th>বিষয়</th><th>সিলেবাস / টপিক</th></tr></thead><tbody>'+cells.join('')+'</tbody></table></div>' : '<p class="lebd-r55-empty">সিলেবাসের তথ্য এখনো যোগ করা হয়নি।</p>')+
            '</article>';
    }
    async function selectDepartment(button){
        const id = button.dataset.departmentId;
        const requestId = ++requestVersion;
        group.querySelectorAll('button').forEach(el => {el.classList.toggle('active', el===button); el.setAttribute('aria-pressed', el===button?'true':'false');});
        result.innerHTML=''; status.hidden=false; status.textContent='রুটিন লোড হচ্ছে...'; actions.hidden=true;
        try {
            const response = await fetch(endpointTemplate.replace('__DEPT_ID__', encodeURIComponent(id)), {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'});
            if(!response.ok) throw new Error('HTTP '+response.status);
            const data=await response.json();
            if(requestId!==requestVersion)return;
            const rows=Array.isArray(data.routine_rows)?data.routine_rows:[];
            if(!rows.length){status.textContent='এই ডিপার্টমেন্টের জন্য এখনো কোনো পরীক্ষার রুটিন পাওয়া যায়নি।';return;}
            status.hidden=true; result.innerHTML=rows.map(renderRow).join(''); actions.hidden=false;
        } catch(error){ if(requestId===requestVersion)status.textContent='রুটিন লোড করা যায়নি। পেজ Refresh করে আবার চেষ্টা করুন।'; }
    }
    group.addEventListener('click', event=>{const button=event.target.closest('button[data-department-id]');if(button)selectDepartment(button);});
})();
@endif

/* =========================================================
   DATE TIME
========================================================= */

function dateTime(date){

    if(!date){

        return '';

    }


    const d =
        new Date(
            String(date).replace(' ','T')
        );


    if(isNaN(d.getTime())){

        return date;

    }


    const day =
        String(
            d.getDate()
        ).padStart(2,'0');


    const month =
        d.toLocaleString(
            'en-US',
            {
                month:'short'
            }
        );


    const year =
        d.getFullYear();


    let hours =
        d.getHours();


    const minutes =
        String(
            d.getMinutes()
        ).padStart(2,'0');


    const ampm =
        hours >= 12
        ? 'PM'
        : 'AM';


    hours =
        hours % 12;


    hours =
        hours
        ? hours
        : 12;


    return (
        day +
        ' ' +
        month +
        ' ' +
        year +
        ', ' +
        hours +
        ':' +
        minutes +
        ' ' +
        ampm
    );

}



/* =========================================================
   ROUTINE STATUS
========================================================= */

function dateCheck(date){

    if(!date){

        return '';

    }


    const now =
        new Date();


    const target =
        new Date(
            String(date).replace(' ','T')
        );


    const today =
        now.toISOString()
            .split('T')[0];


    const targetDate =
        target.toISOString()
            .split('T')[0];


    if(today > targetDate){

        return `
            <span class="text-success">
                Completed
            </span>
        `;

    }


    if(today === targetDate){

        return `
            <span class="text-info">
                Next
            </span>
        `;

    }


    return `
        <span class="text-warning">
            Pending
        </span>
    `;

}


</script>

<style>

/* LEBD v48: strictly scoped presentation; no checkout/routine changes */
#overview .lebd-content-group{border:1px solid #d7e6fb;border-radius:16px;background:#fff;box-shadow:0 7px 22px rgba(12,60,131,.07);padding:20px 22px;margin-bottom:16px;color:#17365b}
#overview .lebd-group-head{display:flex;align-items:center;gap:11px;padding-bottom:12px;margin-bottom:14px;border-bottom:1px solid #e4edfa}
#overview .lebd-group-head h4{font-size:21px;line-height:1.45;color:#083979;font-weight:700;margin:0}
#overview .lebd-group-number{flex:0 0 38px;width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,#53b8ff,#075bd5);color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;box-shadow:0 4px 10px rgba(10,95,205,.15)}
#overview .lebd-v48-headicon{font-size:23px;color:#1373e7;min-width:25px;text-align:center}
#overview .lebd-group-copy{font-size:15px;line-height:1.95;color:#243c5c;overflow-wrap:anywhere}
#overview .lebd-group-copy p{margin:0 0 11px}
#overview .lebd-group-copy ul,#overview .lebd-group-copy ol{padding-left:24px}
#overview .lebd-subheading{font-weight:700;color:#087a58;font-size:17px;padding:10px 13px;border-radius:9px;background:#e9f8f2;margin:15px 0 12px}
#overview .lebd-course-dates{border-collapse:separate;border-spacing:0;width:100%;overflow:hidden;border-radius:9px}
#overview .lebd-course-dates td{font-weight:500;background:#f4f9ff;width:47%}
#overview .lebd-course-dates td,#overview .lebd-course-dates th{font-size:14px;padding:12px;line-height:1.5;border-color:#e0e9f3}
#overview .lebd-v48-faq{overflow-x:auto}
#overview .lebd-v48-faq table{width:100%;border-collapse:collapse;margin:0;border:1px solid #d4e4f6;border-radius:9px;overflow:hidden}
#overview .lebd-v48-faq th,#overview .lebd-v48-faq td{padding:13px 14px;text-align:left;border:1px solid #dce7f4;vertical-align:top;font-size:14px;line-height:1.75}
#overview .lebd-v48-faq th{background:#edf6ff;color:#06428d;font-weight:700}
#overview .lebd-v48-faq td:first-child{font-weight:650;width:42%;color:#0a438c}
#overview .lebd-v48-faq tr:nth-child(even) td{background:#f8fbff}
#overview .lebd-v48-faq p{margin:0 0 8px;line-height:1.85}
#overview .lebd-v48-faq td p:last-child{margin-bottom:0}
@media(max-width:600px){#overview .lebd-content-group{padding:14px!important}#overview .lebd-group-head h4{font-size:18px}#overview .lebd-v48-faq th,#overview .lebd-v48-faq td{font-size:12px;padding:9px 8px}#overview .lebd-group-copy{font-size:14px}}
</style>
@endsection
