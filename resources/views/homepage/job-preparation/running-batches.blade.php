@extends('homepage')

@section('title', 'চলমান সকল ব্যাচ')

@section('content')

<style>

/* =========================================================
   ALL RUNNING BATCHES PAGE
========================================================= */

.running-all-page{
    width:100%;
    max-width:1200px;
    margin:25px auto;
    padding:0 18px 40px;
}

/* ================= HEADER ================= */

.running-all-header{
    background:linear-gradient(135deg,#0869dc,#1269c9);
    border-radius:20px;
    padding:25px 20px;
    text-align:center;
    color:#fff;
    margin-bottom:28px;
    box-shadow:0 8px 20px rgba(0,80,180,.18);
}

.running-all-header h1{
    margin:0;
    font-size:32px;
    font-weight:700;
}

.running-all-header p{
    margin:7px 0 0;
    font-size:17px;
}

/* ================= GRID ================= */

.running-all-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:25px;
}

/* ================= CARD ================= */

.running-all-card{
    background:#fff;
    border:1px solid #dbe7f5;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 7px 20px rgba(0,0,0,.09);
    transition:.25s ease;
}

.running-all-card:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 28px rgba(0,0,0,.13);
}

/* ================= IMAGE ================= */

.running-all-image{
    width:100%;
    height:198px;
    background:#eef5fc;
    overflow:hidden;
}

.running-all-image img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

/* ================= CONTENT ================= */

.running-all-content{
    padding:17px;
}

.running-all-content h2{
    margin:0 0 15px;
    color:#092b68;
    font-size:23px;
    line-height:1.35;
    font-weight:700;
}

/* ================= COURSE NAME ================= */

.running-course-name{
    font-size:14px;
    color:#555;
    margin-bottom:13px;
}

.running-course-name strong{
    color:#0869dc;
}

/* ================= DATE ================= */

.running-all-date{
    display:flex;
    gap:10px;
    margin-bottom:16px;
}

.running-date-box{
    flex:1;
    background:#f3f8ff;
    border-radius:10px;
    padding:8px 5px;
    text-align:center;
}

.running-date-box small{
    display:block;
    color:#666;
    font-size:12px;
    margin-bottom:3px;
}

.running-date-box strong{
    color:#123d79;
    font-size:13px;
}

/* ================= BUTTON ================= */

.running-all-button{
    display:block;
    width:100%;
    padding:11px 10px;
    border-radius:9px;
    background:#0875df;
    color:#fff !important;
    text-align:center;
    text-decoration:none !important;
    font-size:17px;
    font-weight:600;
    transition:.2s ease;
}

.running-all-button:hover{
    background:#055db5;
}

/* ================= NO BATCH ================= */

.no-running-all{
    background:#f5f9ff;
    border:1px solid #dce8f7;
    border-radius:18px;
    padding:45px 20px;
    text-align:center;
    color:#555;
    font-size:19px;
}

/* =========================================================
   TABLET
========================================================= */

@media(max-width:900px){

    .running-all-grid{
        grid-template-columns:repeat(2,1fr);
        gap:18px;
    }

    .running-all-content h2{
        font-size:20px;
    }

}

/* =========================================================
   MOBILE
========================================================= */

@media(max-width:600px){

    .running-all-page{
        margin:15px auto;
        padding:0 10px 30px;
    }

    .running-all-header{
        padding:18px 12px;
        border-radius:15px;
        margin-bottom:18px;
    }

    .running-all-header h1{
        font-size:23px;
    }

    .running-all-header p{
        font-size:14px;
    }

    .running-all-grid{
        grid-template-columns:1fr;
        gap:15px;
    }

    .running-all-card{
        border-radius:15px;
    }

    .running-all-image{
        height:170px;
    }

    .running-all-content{
        padding:12px;
    }

    .running-all-content h2{
        font-size:18px;
        margin-bottom:10px;
    }

    .running-course-name{
        font-size:13px;
    }

    .running-date-box{
        padding:7px 4px;
    }

    .running-date-box small{
        font-size:11px;
    }

    .running-date-box strong{
        font-size:12px;
    }

    .running-all-button{
        font-size:15px;
        padding:10px;
    }
}

</style>


<!-- =========================================================
     ALL RUNNING BATCHES
========================================================= -->

<section class="running-all-page">


    <!-- ================= HEADER ================= -->

    <div class="running-all-header">

        <h1>
            🎓 চলমান সকল ব্যাচ
        </h1>

        <p>
            বর্তমানে চলমান সকল ব্যাচ এখানে দেখানো হচ্ছে
        </p>

    </div>


    <!-- ================= BATCH LIST ================= -->

    @if($batches->count())

        <div class="running-all-grid">

            @foreach($batches as $batch)

                <div class="running-all-card">


                    <!-- IMAGE -->

                    <div class="running-all-image">

                        <img
                            src="{{ $batch->banner ? asset($batch->banner) : asset('/img/course.png') }}"
                            alt="{{ $batch->name }}"
                            loading="lazy"
                        >

                    </div>


                    <!-- CONTENT -->

                    <div class="running-all-content">


                        <!-- BATCH NAME -->

                        <h2>
                            {{ $batch->name }}
                        </h2>


                        <!-- COURSE NAME -->

                        <div class="running-course-name">

                            কোর্স:

                            <strong>
                                {{ $batch->course->name ?? 'N/A' }}
                            </strong>

                        </div>


                        <!-- DATES -->

                        <div class="running-all-date">


                            <div class="running-date-box">

                                <small>
                                    শুরু
                                </small>

                                <strong>

                                    @if($batch->start_at)

                                        {{ \Carbon\Carbon::parse($batch->start_at)->format('d M Y') }}

                                    @else

                                        -

                                    @endif

                                </strong>

                            </div>


                            <div class="running-date-box">

                                <small>
                                    শেষ
                                </small>

                                <strong>

                                    @if($batch->end_at)

                                        {{ \Carbon\Carbon::parse($batch->end_at)->format('d M Y') }}

                                    @else

                                        -

                                    @endif

                                </strong>

                            </div>


                        </div>


                        <!-- BUTTON -->

                        @php

                            $today = \Carbon\Carbon::today();

                            $registrationEnded =
                                $batch->reg_end_at &&
                                $today->gt(
                                    \Carbon\Carbon::parse($batch->reg_end_at)
                                );

                        @endphp


                        @if($registrationEnded)

                            <a
                                href="javascript:void(0)"
                                class="running-all-button"
                                style="background:#dc3545;"
                            >
                                রেজিষ্ট্রেশন মেয়াদ শেষ
                            </a>

                        @else

                            <a
                                href="{{ route('home.course.show', $batch->id) }}"
                                class="running-all-button"
                            >
                                বিস্তারিত দেখুন
                                →
                            </a>

                        @endif


                    </div>

                </div>

            @endforeach

        </div>


    @else


        <!-- NO BATCH -->

        <div class="no-running-all">

            বর্তমানে কোনো চলমান ব্যাচ পাওয়া যায়নি।

        </div>


    @endif


</section>

@endsection