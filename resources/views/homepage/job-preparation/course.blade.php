@php
    $user = Auth::guard('student')->user();
@endphp

@extends('student')

@section('title', 'চলমান কোর্স সমূহ')

@section('content')

<style>

/* =========================================================
   JOB PREPARATION COURSES PAGE
========================================================= */

.job-course-page {
    background: linear-gradient(135deg, #f4f9ff 0%, #ffffff 55%, #eef7ff 100%);
    padding: 35px 0 60px;
    min-height: 70vh;
}

/* Main Container */

.job-course-wrapper {
    max-width: 1250px;
    margin: 0 auto;
    padding: 0 20px;
}


/* =========================================================
   PAGE TITLE
========================================================= */

.job-course-heading {
    text-align: center;
    margin-bottom: 35px;
}

.job-course-heading h1 {
    display: inline-block;

    margin: 0;
    padding: 12px 45px;

    font-size: 34px;
    font-weight: 800;

    color: #ffffff;

    background: linear-gradient(
        135deg,
        #075bd8,
        #167eea
    );

    border-radius: 50px;

    box-shadow:
        0 8px 20px rgba(0, 91, 216, .25);

    position: relative;
}

.job-course-heading h1::before,
.job-course-heading h1::after {
    content: "";
    position: absolute;

    top: 50%;

    width: 65px;
    height: 3px;

    background: #167eea;
}

.job-course-heading h1::before {
    right: 100%;
    margin-right: 18px;
}

.job-course-heading h1::after {
    left: 100%;
    margin-left: 18px;
}

.job-course-heading p {
    margin-top: 14px;
    margin-bottom: 0;

    color: #52657c;

    font-size: 18px;
    font-weight: 500;
}


/* =========================================================
   COURSE GRID
========================================================= */

.job-course-grid {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 25px;
}


/* =========================================================
   COURSE CARD
========================================================= */

.job-course-card {

    background: #ffffff;

    border: 1px solid #dce9f8;

    border-radius: 20px;

    overflow: hidden;

    box-shadow:
        0 8px 25px rgba(23, 74, 130, .10);

    transition:
        transform .25s ease,
        box-shadow .25s ease;

    position: relative;
}

.job-course-card:hover {

    transform: translateY(-7px);

    box-shadow:
        0 16px 35px rgba(23, 74, 130, .18);
}


/* =========================================================
   COURSE IMAGE
========================================================= */

.job-course-image {

    width: 100%;
    height: 190px;

    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            #eaf4ff,
            #f7fbff
        );

    position: relative;
}

.job-course-image img {

    width: 100%;
    height: 100%;

    object-fit: cover;

    display: block;

    transition: transform .35s ease;
}

.job-course-card:hover .job-course-image img {

    transform: scale(1.05);
}


/* Image overlay */

.job-course-image::after {

    content: "";

    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    height: 55px;

    background:
        linear-gradient(
            transparent,
            rgba(0, 40, 90, .25)
        );
}


/* =========================================================
   CARD CONTENT
========================================================= */

.job-course-content {

    padding: 18px 18px 20px;

    text-align: center;
}

.job-course-content h2 {

    margin: 0 0 16px;

    color: #092d68;

    font-size: 21px;

    font-weight: 700;

    line-height: 1.45;

    min-height: 60px;
}


/* =========================================================
   DETAILS BUTTON
========================================================= */

.job-course-button {

    display: block;

    width: 100%;

    padding: 11px 15px;

    background:
        linear-gradient(
            135deg,
            #0876e8,
            #0054c7
        );

    color: #ffffff !important;

    text-decoration: none !important;

    border-radius: 12px;

    font-size: 17px;

    font-weight: 700;

    box-shadow:
        0 5px 12px rgba(0, 92, 210, .20);

    transition:
        background .2s ease,
        transform .2s ease;
}

.job-course-button:hover {

    background:
        linear-gradient(
            135deg,
            #0054c7,
            #003f9c
        );

    color: #ffffff !important;

    transform: translateY(-2px);
}


/* =========================================================
   EMPTY COURSE
========================================================= */

.job-course-empty {

    grid-column: 1 / -1;

    text-align: center;

    padding: 60px 20px;

    background: #ffffff;

    border-radius: 20px;

    border: 1px solid #e0eaf5;

    color: #60738b;

    font-size: 20px;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 992px) {

    .job-course-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 20px;
    }

    .job-course-heading h1 {

        font-size: 29px;

        padding: 11px 35px;
    }

    .job-course-image {

        height: 175px;
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 576px) {

    .job-course-page {

        padding: 20px 0 35px;
    }

    .job-course-wrapper {

        padding: 0 10px;
    }

    .job-course-heading {

        margin-bottom: 22px;
    }

    .job-course-heading h1 {

        font-size: 20px;

        padding: 9px 22px;

        border-radius: 30px;
    }

    .job-course-heading h1::before,
    .job-course-heading h1::after {

        display: none;
    }

    .job-course-heading p {

        font-size: 13px;

        margin-top: 8px;
    }

    .job-course-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 10px;
    }

    .job-course-card {

        border-radius: 12px;
    }

    .job-course-image {

        height: 105px;
    }

    .job-course-content {

        padding: 9px 8px 10px;
    }

    .job-course-content h2 {

        font-size: 13px;

        line-height: 1.35;

        min-height: 36px;

        margin-bottom: 8px;
    }

    .job-course-button {

        padding: 7px 5px;

        font-size: 11px;

        border-radius: 7px;
    }

}


/* =========================================================
   VERY SMALL MOBILE
========================================================= */

@media (max-width: 380px) {

    .job-course-grid {

        gap: 8px;
    }

    .job-course-image {

        height: 90px;
    }

    .job-course-content h2 {

        font-size: 12px;

        min-height: 34px;
    }

    .job-course-button {

        font-size: 10px;

        padding: 6px 3px;
    }

}

</style>


<!-- =========================================================
     MAIN COURSE PAGE
========================================================= -->

<section class="job-course-page">

    <div class="job-course-wrapper">


        <!-- PAGE TITLE -->

        <div class="job-course-heading">

            <h1>
                🎓 চলমান কোর্স সমূহ
            </h1>

            <p>
                আমাদের বর্তমানে পরিচালিত সকল কোর্স
            </p>

        </div>


        <!-- =================================================
             COURSE GRID
        ================================================== -->

        <div class="job-course-grid">


            @forelse($courses as $value)


                <!-- COURSE CARD -->

                <div class="job-course-card">


                    <!-- COURSE IMAGE -->

                    <div class="job-course-image">

                        <a href="{{ route('homepage.batches', $value->id) }}">

                            <img
                                src="{{ $value->banner ? $value->banner : '/web/img/no-course.png' }}"
                                alt="{{ $value->name }}"
                                loading="lazy"
                            >

                        </a>

                    </div>


                    <!-- COURSE CONTENT -->

                    <div class="job-course-content">


                        <h2>

                            {{ $value->name }}

                        </h2>


                        <!-- DETAILS -->

                        <a
                            href="{{ route('homepage.batches', $value->id) }}"
                            class="job-course-button"
                        >

                            বিস্তারিত দেখুন →

                        </a>


                    </div>

                </div>


            @empty


                <!-- NO COURSE -->

                <div class="job-course-empty">

                    <div style="font-size:45px; margin-bottom:10px;">
                        📚
                    </div>

                    বর্তমানে কোনো চলমান কোর্স পাওয়া যায়নি।

                </div>


            @endforelse


        </div>


        <!-- BACK BUTTON -->

        <div style="margin-top:30px;">

            @include('partials.back-button')

        </div>


    </div>

</section>

@endsection