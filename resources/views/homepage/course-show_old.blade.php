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
   MOBILE
========================================================= */

@media(max-width:600px){

    .batch-detail-page{

        padding:15px 8px 40px;

    }


    .batch-course-title h1{

        font-size:24px;

    }


    .batch-course-title p{

        font-size:14px;

    }


    .batch-hero{

        min-height:180px;

        padding:15px;

    }


    .batch-hero h2{

        font-size:19px;

    }


    .batch-hero-description{

        min-height:80px;

        font-size:13px;

    }


    .batch-detail-tabs{

        margin:16px 0;

        padding:5px;

        gap:5px;

        justify-content:flex-start;

    }


    .batch-detail-tab{

        flex:0 0 auto;

        min-width:auto;

        min-height:36px;

        padding:7px 9px;

        font-size:10px;

        gap:4px;

    }


    .batch-detail-tab i{

        font-size:10px;

    }


    .batch-info-card{

        padding:12px;

        border-radius:11px;

    }


    .batch-purchase-card{

        border-radius:12px;

    }


    .batch-purchase-media img{

        height:180px;

    }


    .batch-teacher{

        align-items:flex-start;

    }

}


/* =========================================================
   VERY SMALL MOBILE
========================================================= */

@media(max-width:380px){

    .batch-detail-tab{

        padding:6px 7px;

        font-size:9px;

    }

}


/* =========================================================
   PRINT
========================================================= */

@media print{

    .batch-detail-tabs,
    .batch-purchase-card{

        display:none !important;

    }

    .batch-content-section{

        display:block !important;

    }

}

</style>



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



            <!-- =================================================
                 OVERVIEW
                 Initially OPEN
            ================================================== -->

            <div id="overview"
                 class="batch-content-section active">


                <!-- WHAT IS -->

                @if($value->what_is)

                    <div class="batch-info-card">

                        <h4>
                            <u>এই কোর্সে যা থাকছেঃ</u>
                        </h4>

                        {!! $value->what_is !!}

                    </div>

                @endif



                <!-- COURSE DURATION -->

                <div class="batch-info-card">

                    <h4>
                        কোর্সটির মেয়াদ ও অন্যান্য
                    </h4>


                    <div class="table-responsive">

                        <table class="table table-bordered">


                            @if($value->start_at && $value->end_at)

                                <tr>

                                    <td>
                                        কোর্সটি চলবে
                                    </td>

                                    <th>

                                        {{ \Carbon\Carbon::parse($value->start_at)->format('d M Y') }}

                                        -

                                        {{ \Carbon\Carbon::parse($value->end_at)->format('d M Y') }}

                                    </th>

                                </tr>

                            @endif


                            @if($value->reg_end_at)

                                <tr>

                                    <td>
                                        রেজিস্ট্রেশনের মেয়াদ
                                    </td>

                                    <th>

                                        {{ $source->dtformat($value->reg_end_at) }}

                                    </th>

                                </tr>

                            @endif


                            @if($value->offer_end_at && $value->offer_end_at > now())

                                <tr>

                                    <td>
                                        বর্তমান অফারের মেয়াদ
                                    </td>

                                    <th>

                                        {{ $source->dtformat($value->offer_end_at) }}

                                    </th>

                                </tr>

                            @endif


                        </table>

                    </div>

                </div>



                <!-- LEARN -->

                @if($value->learn)

                    <div class="batch-info-card">

                        <h4>
                            কোর্সটি করে যা শিখবেন
                        </h4>

                        {!! $value->learn !!}

                    </div>

                @endif



                <!-- DETAILS -->

                @if($value->details)

                    <div class="batch-info-card">

                        <h4>
                            কোর্স সম্পর্কে বিস্তারিত
                        </h4>

                        {!! $value->details !!}

                    </div>

                @endif



                <!-- FAQ -->

                @if($value->faq)

                    <div class="batch-info-card">

                        <h4>
                            সাধারণ জিজ্ঞাসা সমূহ
                        </h4>

                        {!! $value->faq !!}

                    </div>

                @endif



                <!-- REFUND -->

                @if($value->refund)

                    <div class="batch-info-card">

                        <h4>
                            রিফান্ড পলিসি
                        </h4>

                        {!! $value->refund !!}

                    </div>

                @endif



                <!-- COMPLAINT -->

                <a href="{{ route('students.complain') }}"
                   class="btn btn-info mt-2">

                    আপনার মতামত/অভিযোগ দিন

                </a>


            </div>



            <!-- =================================================
                 DESCRIPTION
            ================================================== -->

            <div id="description"
                 class="batch-content-section">


                @if($value->details)

                    <div class="batch-info-card">

                        <h4>
                            কোর্স সম্পর্কে বিস্তারিত
                        </h4>

                        {!! $value->details !!}

                    </div>

                @endif


                @if($value->learn)

                    <div class="batch-info-card">

                        <h4>
                            কোর্সটি করে যা শিখবেন
                        </h4>

                        {!! $value->learn !!}

                    </div>

                @endif


                @if($value->faq)

                    <div class="batch-info-card">

                        <h4>
                            সাধারণ জিজ্ঞাসা সমূহ
                        </h4>

                        {!! $value->faq !!}

                    </div>

                @endif


                @if($value->refund)

                    <div class="batch-info-card">

                        <h4>
                            রিফান্ড পলিসি
                        </h4>

                        {!! $value->refund !!}

                    </div>

                @endif


                @if(
                    !$value->details &&
                    !$value->learn &&
                    !$value->faq &&
                    !$value->refund
                )

                    <div class="batch-info-card text-center">

                        এই ব্যাচের জন্য এখনো কোনো বিস্তারিত তথ্য প্রকাশ করা হয়নি।

                    </div>

                @endif


            </div>



            <!-- =================================================
                 ROUTINE
            ================================================== -->

            <div id="routine"
                 class="batch-content-section">


                @if(!empty($routine))

                    <div class="batch-info-card">

                        <h4>
                            এক্সাম / ক্লাস রুটিন
                        </h4>


                        @if(!empty($departments))

                            <div class="form-group mb-3">

                                <label>
                                    Department
                                </label>

                                <select
                                    class="form-control"
                                    id="batch_department_id"
                                    onchange="getBatchRoutine(this)"
                                >

                                    <option value="">
                                        Select Department
                                    </option>


                                    @foreach($departments as $department)

                                        <option value="{{ $department->id }}">

                                            {{ $department->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                        @endif


                        <div class="batch-routine-wrapper">

                            <table
                                class="table table-bordered"
                                id="batch-routine-table">

                                <tr>

                                    <td class="text-center">

                                        Department নির্বাচন করুন

                                    </td>

                                </tr>

                            </table>

                        </div>

                    </div>


                @else

                    <div class="batch-info-card text-center">

                        <h4>
                            Routine
                        </h4>

                        <p>
                            এই ব্যাচের জন্য এখনো কোনো Routine প্রকাশ করা হয়নি।
                        </p>

                    </div>

                @endif


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
                 TEACHER
            ================================================== -->

            <div id="teacher"
                 class="batch-content-section">


                @if($value->teachers->count())

                    <div class="batch-info-card">

                        <h4>
                            কোর্স ইন্সট্রাক্টর
                        </h4>


                        @foreach($value->teachers as $teacher)

                            <div class="batch-teacher">


                                <img
                                    src="{{ $teacher->image
                                        ? $teacher->image
                                        : '/img/teacher.png' }}"
                                    alt="{{ $teacher->name }}"
                                >


                                <div>

                                    <strong>

                                        {{ $teacher->name }}

                                    </strong>

                                    <div>

                                        {!! $teacher->designation !!}

                                    </div>

                                </div>


                            </div>

                        @endforeach


                    </div>

                @else

                    <div class="batch-info-card text-center">

                        এই ব্যাচের জন্য কোনো Teacher তথ্য পাওয়া যায়নি।

                    </div>

                @endif


            </div>


        </div>



        <!-- =================================================
             RIGHT PURCHASE AREA
        ================================================== -->

        <div class="batch-right-area">


            <div class="batch-purchase-card">


                <!-- MEDIA -->

                <div class="batch-purchase-media">


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

                <div class="batch-purchase-body">


                    <!-- PRICE -->

                    @if($price > 0)

                        <div class="batch-price">


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

                    <table class="batch-student-count">

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

                            <p
                                style="
                                color:red;
                                font-size:12px;
                                text-align:center;
                                "
                            >

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

                            কোর্সটি কিনুন

                            <i class="fa fa-arrow-right"></i>

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

@section('scripts')


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


                    /*
                    |--------------------------------------------------------------------------
                    | OVERVIEW = SHOW EVERYTHING
                    |--------------------------------------------------------------------------
                    */

                    if(target === 'overview'){

                        sections.forEach(
                            function(section){

                                section.classList.add(
                                    'active'
                                );

                            }
                        );

                    }

                    else{

                        const selected =
                            document.getElementById(
                                target
                            );


                        if(selected){

                            selected.classList.add(
                                'active'
                            );

                        }

                    }


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
   ROUTINE AJAX
========================================================= */

@if(!empty($routine))


function getBatchRoutine(element){

    const routineTable =
        document.getElementById(
            'batch-routine-table'
        );


    const departmentId =
        element.value;


    if(!departmentId){

        routineTable.innerHTML = `

            <tr>

                <td class="text-center">

                    Department নির্বাচন করুন

                </td>

            </tr>

        `;

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    if(typeof $ !== 'undefined'){

        $.ajaxSetup({

            headers:{

                'X-CSRF-TOKEN':
                    $('meta[name="csrf-token"]')
                    .attr('content')

            }

        });


        $.ajax({

            type:'GET',

            url:
                '{{ route("home.routine.filter","") }}'
                +
                '/'
                +
                '{{ $routine->id }}'
                +
                '/'
                +
                departmentId,


            success:function(data){

                let obj =
                    typeof data === 'string'
                    ? JSON.parse(data)
                    : data;


                let subjects = '';

                let departments = '';

                let routineRows = '';


                /*
                |--------------------------------------------------------------------------
                | Subjects
                |--------------------------------------------------------------------------
                */

                if(obj.subjects){

                    obj.subjects.forEach(
                        function(subject){

                            subjects +=
                                '<th>'
                                +
                                subject.name
                                +
                                '</th>';

                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Departments
                |--------------------------------------------------------------------------
                */

                if(obj.departments){

                    obj.departments.forEach(
                        function(department){

                            departments +=
                                '<th>'
                                +
                                department.name
                                +
                                '</th>';

                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Rows
                |--------------------------------------------------------------------------
                */

                if(obj.routine_rows){

                    obj.routine_rows.forEach(
                        function(row){

                            let subjectRow = '';

                            let departmentRow = '';


                            if(row.subjects){

                                row.subjects.forEach(
                                    function(subject){

                                        subjectRow +=
                                            '<td>'
                                            +
                                            (
                                                subject.chapter
                                                || ''
                                            )
                                            +
                                            '</td>';

                                    }
                                );

                            }


                            if(row.departments){

                                row.departments.forEach(
                                    function(department){

                                        departmentRow +=
                                            '<td>'
                                            +
                                            (
                                                department.details
                                                || ''
                                            )
                                            +
                                            '</td>';

                                    }
                                );

                            }


                            routineRows += `

                                <tr>

                                    <td>
                                        ${row.paper_name || ''}
                                    </td>

                                    ${subjectRow}

                                    ${departmentRow}

                                    <td>
                                        ${dateTime(row.exam_at)}
                                    </td>

                                    <td>
                                        ${dateCheck(row.exam_at)}
                                    </td>

                                </tr>

                            `;

                        }
                    );

                }


                routineTable.innerHTML = `

                    <thead>

                        <tr>

                            <th>
                                Exam No
                            </th>

                            ${subjects}

                            ${departments}

                            <th>
                                Date & Time
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        ${routineRows}

                    </tbody>

                `;

            },


            error:function(){

                routineTable.innerHTML = `

                    <tr>

                        <td
                            class="text-center text-danger"
                        >

                            Routine load করা যায়নি।

                        </td>

                    </tr>

                `;

            }

        });

    }

}


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


@endsection