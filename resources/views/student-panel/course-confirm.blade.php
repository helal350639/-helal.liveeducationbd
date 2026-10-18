@php
use \App\Http\Controllers\SourceCtrl;

$source = new SourceCtrl;

$user = Auth::guard('student')->user();

$student = [];

if($user)
{
    $student = \App\Models\Student::find($user->id);
}

$value = $batch;


/* =========================================================
   PRICE CALCULATION
========================================================= */

$price = 0;
$discount = 0;
$reference_discount = 0;
$selling_price = 0;

if(
    $value->discount &&
    $value->offer_end_at &&
    $value->offer_end_at > now()
) {
    $discount = $value->discount;
}

if(
    $value->reference_discount &&
    Session::get('batch-referrer') &&
    Session::get('batch-referrer')['batch'] == $batch->id
) {
    $reference_discount = $value->reference_discount;

    $discount = $discount + $value->reference_discount;
}

$price = $value->price + $value->fee;

$selling_price = $price - $discount;

$course_price = $price - $value->fee;

@endphp


@extends('student')

@section('title', 'Course Confirm')


@section('content')


<style>

/* =========================================================
   COURSE CONFIRM PAGE
========================================================= */

.course-confirm-page{
    width:100%;
    min-height:100vh;

    padding:25px 12px 45px;

    box-sizing:border-box;

    background:
        linear-gradient(
            180deg,
            #f4f9ff 0%,
            #ffffff 45%,
            #f7fbff 100%
        );
}


/* =========================================================
   WRAPPER
========================================================= */

.course-confirm-wrapper{
    width:100%;
    max-width:760px;

    margin:0 auto;
}


/* =========================================================
   PAGE TITLE
========================================================= */

.course-confirm-title{
    text-align:center;
    margin-bottom:18px;
}

.course-confirm-title h1{
    margin:0;

    color:#092b68;

    font-size:32px;
    font-weight:800;

    line-height:1.25;
}

.course-confirm-title p{
    margin:6px 0 0;

    color:#64748b;

    font-size:14px;
}


/* =========================================================
   MAIN CARD
========================================================= */

.course-confirm-card{
    background:#fff;

    border:1px solid #dce8f5;

    border-radius:18px;

    padding:18px;

    box-shadow:
        0 12px 35px rgba(8,70,130,.12);

    box-sizing:border-box;
}


/* =========================================================
   TOP COURSE SECTION
========================================================= */

.course-confirm-top{
    display:flex;

    gap:18px;

    align-items:stretch;

    margin-bottom:18px;
}


/* =========================================================
   COURSE IMAGE
========================================================= */

.course-confirm-image{
    width:50%;

    min-width:0;

    border-radius:12px;

    overflow:hidden;

    background:#edf5fc;
}

.course-confirm-image img{
    width:100%;
    height:100%;

    min-height:210px;

    display:block;

    object-fit:cover;
}


/* =========================================================
   COURSE INFORMATION
========================================================= */

.course-confirm-info{
    width:50%;

    min-width:0;

    display:flex;

    flex-direction:column;

    justify-content:center;
}

.course-confirm-info h2{
    margin:0 0 6px;

    color:#092b68;

    font-size:28px;

    font-weight:800;

    line-height:1.25;
}

.course-confirm-subtitle{
    margin:0 0 14px;

    color:#64748b;

    font-size:14px;

    line-height:1.5;
}


/* =========================================================
   PRICE
========================================================= */

.course-confirm-price{
    display:flex;

    align-items:center;

    flex-wrap:wrap;

    gap:8px;

    margin-bottom:12px;
}

.course-confirm-old-price{
    color:#475467;

    font-size:17px;

    text-decoration:line-through;
}

.course-confirm-discount{
    background:#fff0b8;

    color:#8a5300;

    padding:5px 8px;

    border-radius:7px;

    font-size:12px;

    font-weight:700;
}

.course-confirm-final-price{
    color:#0875df;

    font-size:30px;

    font-weight:800;
}


/* =========================================================
   OFFER DEADLINE
========================================================= */

.course-confirm-offer{
    display:flex;

    align-items:center;

    gap:9px;

    padding:10px 11px;

    background:#fff0f3;

    border:1px solid #ffd1da;

    border-radius:9px;

    color:#e11d48;

    font-size:11px;

    line-height:1.5;
}

.course-confirm-offer-icon{
    width:25px;
    height:25px;

    flex-shrink:0;

    display:flex;

    align-items:center;
    justify-content:center;

    border:2px solid #e11d48;

    border-radius:50%;

    font-size:12px;
}


/* =========================================================
   DETAILS BOX
========================================================= */

.course-confirm-details{
    border:1px solid #dce8f5;

    border-radius:13px;

    overflow:hidden;

    background:#fff;
}


/* =========================================================
   DETAILS HEADER
========================================================= */

.course-confirm-details-header{
    display:flex;

    align-items:center;

    gap:10px;

    padding:13px 15px;

    background:#eef8ff;

    border-bottom:1px solid #dce8f5;
}

.course-confirm-details-icon{
    width:40px;
    height:40px;

    flex-shrink:0;

    display:flex;

    align-items:center;
    justify-content:center;

    background:#dfefff;

    border-radius:50%;

    color:#0875df;

    font-size:20px;
}

.course-confirm-details-header h3{
    margin:0;

    color:#092b68;

    font-size:19px;

    font-weight:800;
}

.course-confirm-details-header p{
    margin:2px 0 0;

    color:#718096;

    font-size:10px;
}


/* =========================================================
   TABLE
========================================================= */

.course-confirm-table{
    width:100%;

    border-collapse:collapse;

    margin:0;
}

.course-confirm-table tr{
    border-bottom:1px solid #e5edf5;
}

.course-confirm-table tr:last-child{
    border-bottom:0;
}

.course-confirm-table td,
.course-confirm-table th{
    padding:11px 15px;

    font-size:14px;

    line-height:1.4;

    vertical-align:middle;
}

.course-confirm-table td{
    width:58%;

    color:#334155;
}

.course-confirm-table th{
    width:42%;

    color:#123d79;

    text-align:left;

    font-weight:700;
}


/* =========================================================
   PRICE ROWS
========================================================= */

.course-price-row{
    background:#f0fbf5;
}

.course-price-row td,
.course-price-row th{
    color:#234f3b;
}


/* =========================================================
   DISCOUNT
========================================================= */

.course-discount-row{
    background:#fff8f9;
}

.course-discount-row td,
.course-discount-row th{
    color:#e11d48;
}


/* =========================================================
   EXTRA FEE
========================================================= */

.extra-fee-value{
    display:flex;

    align-items:center;

    justify-content:flex-start;

    flex-wrap:wrap;

    gap:7px;
}

.extra-fee-note{
    display:inline-block;

    padding:4px 8px;

    background:#e5f1ff;

    border:1px solid #cfe2f5;

    border-radius:6px;

    color:#0875df;

    font-size:10px;

    font-weight:600;

    line-height:1.3;
}


/* =========================================================
   TOTAL
========================================================= */

.course-confirm-total{
    background:#dff4ff;
}

.course-confirm-total td,
.course-confirm-total th{
    padding-top:13px;
    padding-bottom:13px;

    color:#092b68;

    font-size:20px;

    font-weight:800;
}


/* =========================================================
   CONFIRM BUTTON
========================================================= */

.course-confirm-button{
    width:100%;

    min-height:55px;

    margin-top:18px;

    padding:12px 15px;

    border:0;

    border-radius:11px;

    background:
        linear-gradient(
            135deg,
            #ffc107,
            #ffca28
        );

    color:#171717;

    font-size:18px;

    font-weight:800;

    cursor:pointer;

    box-shadow:
        0 6px 18px rgba(255,193,7,.28);

    transition:.2s ease;
}

.course-confirm-button:hover{
    transform:translateY(-2px);

    box-shadow:
        0 8px 20px rgba(255,193,7,.35);
}

.course-confirm-button:active{
    transform:translateY(0);
}

.course-confirm-button:disabled{
    opacity:.7;

    cursor:wait;

    transform:none;
}


/* =========================================================
   SECURE PAYMENT
========================================================= */

.course-confirm-secure{
    text-align:center;

    margin-top:10px;

    color:#8a99aa;

    font-size:11px;
}


/* =========================================================
   DESKTOP
========================================================= */

@media(min-width:768px){

    .course-confirm-page{
        padding-top:35px;
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:767px){

    .course-confirm-page{
        padding:10px 6px 25px;

        background:
            linear-gradient(
                180deg,
                #f4f9ff 0%,
                #ffffff 100%
            );
    }

    .course-confirm-wrapper{
        max-width:520px;
    }

    .course-confirm-title{
        margin-bottom:12px;
    }

    .course-confirm-title h1{
        font-size:27px;
    }

    .course-confirm-title p{
        font-size:11px;

        padding:0 12px;
    }

    .course-confirm-card{
        padding:10px;

        border-radius:15px;
    }


    /* TOP */

    .course-confirm-top{
        gap:10px;

        margin-bottom:12px;
    }


    /* IMAGE */

    .course-confirm-image{
        width:50%;

        border-radius:9px;
    }

    .course-confirm-image img{
        min-height:155px;

        height:100%;

        object-fit:cover;
    }


    /* INFO */

    .course-confirm-info{
        width:50%;
    }

    .course-confirm-info h2{
        font-size:21px;

        margin-bottom:4px;
    }

    .course-confirm-subtitle{
        font-size:10px;

        margin-bottom:8px;
    }


    /* PRICE */

    .course-confirm-price{
        gap:5px;

        margin-bottom:8px;
    }

    .course-confirm-old-price{
        font-size:14px;
    }

    .course-confirm-discount{
        padding:4px 6px;

        font-size:9px;
    }

    .course-confirm-final-price{
        font-size:24px;
    }


    /* OFFER */

    .course-confirm-offer{
        padding:7px;

        gap:6px;

        font-size:9px;
    }

    .course-confirm-offer-icon{
        width:21px;
        height:21px;

        font-size:10px;
    }


    /* DETAILS */

    .course-confirm-details{
        border-radius:10px;
    }

    .course-confirm-details-header{
        padding:10px;

        gap:8px;
    }

    .course-confirm-details-icon{
        width:34px;
        height:34px;

        font-size:17px;
    }

    .course-confirm-details-header h3{
        font-size:16px;
    }

    .course-confirm-details-header p{
        font-size:8px;
    }


    /* TABLE */

    .course-confirm-table td,
    .course-confirm-table th{
        padding:8px 10px;

        font-size:11px;
    }

    .course-confirm-table td{
        width:57%;
    }

    .course-confirm-table th{
        width:43%;
    }


    /* EXTRA FEE */

    .extra-fee-value{
        gap:4px;
    }

    .extra-fee-note{
        padding:3px 5px;

        font-size:8px;
    }


    /* TOTAL */

    .course-confirm-total td,
    .course-confirm-total th{
        padding-top:10px;
        padding-bottom:10px;

        font-size:17px;
    }


    /* BUTTON */

    .course-confirm-button{
        min-height:51px;

        margin-top:13px;

        padding:10px;

        font-size:14px;

        border-radius:9px;
    }

    .course-confirm-secure{
        margin-top:8px;

        font-size:9px;
    }

}


/* =========================================================
   VERY SMALL MOBILE
========================================================= */

@media(max-width:380px){

    .course-confirm-page{
        padding-left:4px;
        padding-right:4px;
    }

    .course-confirm-card{
        padding:8px;
    }

    .course-confirm-top{
        gap:7px;
    }

    .course-confirm-info h2{
        font-size:18px;
    }

    .course-confirm-final-price{
        font-size:21px;
    }

    .course-confirm-table td,
    .course-confirm-table th{
        padding:7px 8px;

        font-size:10px;
    }

    .course-confirm-total td,
    .course-confirm-total th{
        font-size:15px;
    }

    .course-confirm-button{
        font-size:13px;

        min-height:48px;
    }

}

</style>


<!-- =========================================================
     COURSE CONFIRM PAGE
========================================================= -->

<section class="course-confirm-page">

    <div class="course-confirm-wrapper">


        <!-- PAGE TITLE -->

        <div class="course-confirm-title">

            <h1>
                Summary
            </h1>

            <p>
                আপনার দেওয়া সকল তথ্য সঠিক থাকলে পেমেন্ট কনফার্ম করুন বাটুনে ক্লিক করুন ।
            </p>

        </div>


        <!-- =================================================
             EXISTING CONFIRM SESSION
        ================================================== -->

        @if(Session::get('_confirm'))


        <!-- =================================================
             EXISTING PAYMENT FORM
             ROUTE UNCHANGED
        ================================================== -->

        <form
            action="{{ route('payment.proceed') }}"
            method="post"
            id="courseConfirmForm"
        >

            @csrf


            <!-- EXISTING HIDDEN VALUES -->

            <input
                type="hidden"
                name="batch_id"
                value="{{ $value->id }}"
            >

            <input
                type="hidden"
                name="name"
                value="{{ $student->name }}"
            >

            <input
                type="hidden"
                name="student_id"
                value="{{ $student->id }}"
            >

            <input
                type="hidden"
                name="department_id"
                value="{{ $department->id }}"
            >

            <input
                type="hidden"
                name="email"
                value="{{ $student->email }}"
            >

            <input
                type="hidden"
                name="phone"
                value="{{ $student->contact }}"
            >

            <input
                type="hidden"
                name="address1"
                value=""
            >

            <input
                type="hidden"
                name="total"
                value="{{ $price }}"
            >


            <!-- =================================================
                 MAIN CARD
            ================================================== -->

            <div class="course-confirm-card">


                <!-- =================================================
                     COURSE TOP
                ================================================== -->

                <div class="course-confirm-top">


                    <!-- COURSE IMAGE -->

                    <div class="course-confirm-image">

                        @if($value->banner)

                            <img
                                src="{{ asset($value->banner) }}"
                                alt="{{ $value->name }}"
                            >

                        @else

                            <img
                                src="{{ asset('/img/course.png') }}"
                                alt="{{ $value->name }}"
                            >

                        @endif

                    </div>


                    <!-- COURSE INFO -->

                    <div class="course-confirm-info">

                        <h2>
                            {{ $value->name }}
                        </h2>


                        @if($value->subtitle)

                            <p class="course-confirm-subtitle">
                                {{ $value->subtitle }}
                            </p>

                        @endif


                        <!-- PRICE -->

                        <div class="course-confirm-price">

                            @if($discount)

                                <span class="course-confirm-old-price">

                                    ৳{{ $source->point0($price) }}

                                </span>


                                <span class="course-confirm-discount">

                                    ৳{{ $source->point0($discount) }} ছাড়

                                </span>

                            @endif


                            <span class="course-confirm-final-price">

                                ৳{{ $source->point0($selling_price) }}

                            </span>

                        </div>


                        <!-- OFFER END -->

                        @if($value->offer_end_at)

                            <div class="course-confirm-offer">

                                <span class="course-confirm-offer-icon">
                                    ⏱
                                </span>

                                <span>

                                    ডিসকাউন্ট অফারের মেয়াদ

                                    <strong>
                                        {{ $source->dtformat($value->offer_end_at) }}
                                    </strong>

                                </span>

                            </div>

                        @endif

                    </div>

                </div>


                <!-- =================================================
                     COURSE DETAILS
                ================================================== -->

                @if(
                    !empty($user->id) &&
                    !$value->students()->where('id', $user->id)->first()
                )

                <div class="course-confirm-details">


                    <!-- DETAILS HEADER -->

                    <div class="course-confirm-details-header">

                        <div class="course-confirm-details-icon">
                            📋
                        </div>

                        <div>

                            <h3>
                                কোর্সের বিস্তারিত তথ্য
                            </h3>

                            <p>
                                নিচের তথ্যগুলো যাচাই করুন
                            </p>

                        </div>

                    </div>


                    <!-- DETAILS TABLE -->

                    <table class="course-confirm-table">


                        <!-- COURSE -->

                        <tr>

                            <td>
                                কোর্স এর নাম
                            </td>

                            <th>
                                {{ $batch->course ? $batch->course->name : '' }}
                            </th>

                        </tr>


                        <!-- BATCH -->

                        <tr>

                            <td>
                                ব্যাচ এর নাম
                            </td>

                            <th>
                                {{ $batch->name }}
                            </th>

                        </tr>


                        <!-- DEPARTMENT -->

                        <tr>

                            <td>
                                আপনার নির্বাচিত ডিপার্টমেন্ট
                            </td>

                            <th>
                                {{ $department->name }}
                            </th>

                        </tr>


                        <!-- COURSE PRICE -->

                        <tr class="course-price-row">

                            <td>
                                কোর্সটির মূল্য
                            </td>

                            <th>
                                ৳ {{ $source->point0($price - $value->fee) }}
                            </th>

                        </tr>


                        <!-- EXTRA FEE -->

                        <tr class="course-price-row">

                            <td>
                                অন্যান্য ফী (+)
                            </td>

                            <th>

                                <div class="extra-fee-value">

                                    <span>
                                        ৳ {{ $source->point0($value->fee) }}
                                    </span>


                                    @if(
                                        $value->fee > 0 &&
                                        $value->extra_fee_note
                                    )

                                        <span
                                            class="extra-fee-note"
                                            title="{{ $value->extra_fee_note }}"
                                        >
                                            {{ $value->extra_fee_note }}
                                        </span>

                                    @endif

                                </div>

                            </th>

                        </tr>


                        <!-- DISCOUNT -->

                        <tr class="course-discount-row">

                            <td>
                                ডিসকাউন্ট (-)
                            </td>

                            <th>
                                ৳ {{ $source->point0($value->discount) }}
                            </th>

                        </tr>


                        <!-- REFERENCE DISCOUNT -->

                        @if($reference_discount)

                            <tr class="course-discount-row">

                                <td>
                                    রিফারেন্স ডিসকাউন্ট (-)
                                </td>

                                <th>
                                    ৳ {{ $source->point0($reference_discount) }}
                                </th>

                            </tr>

                        @endif


                        <!-- TOTAL -->

                        <tr class="course-confirm-total">

                            <td>
                                মোট =
                            </td>

                            <th>
                                ৳ {{ $source->point0($selling_price) }}
                            </th>

                        </tr>


                    </table>

                </div>

                @endif


                <!-- =================================================
                     CONFIRM PAYMENT BUTTON
                ================================================== -->

                <button
                    type="submit"
                    class="course-confirm-button"
                    id="courseConfirmButton"
                >

                    🔒

                    &nbsp;

                    ৳ {{ $source->point0($selling_price) }}

                    পেমেন্ট কনফার্ম করুন

                    &nbsp; →

                </button>


                <!-- SECURE PAYMENT -->

                <div class="course-confirm-secure">

                    🔒 নিরাপদ ও সুরক্ষিত পেমেন্ট

                </div>


            </div>


        </form>

        @endif


    </div>

</section>


@endsection


@section('scripts')

<script>

/* =========================================================
   EXISTING COUNTDOWN
========================================================= */

var countDownDate =
    new Date(
        "{{ $source->dtcformat($value->reg_end_at) }}"
    ).getTime();


var x = setInterval(function()
{

    let timer =
        document.getElementById("timer");


    /*
     * timer element may not exist
     * on the redesigned page.
     */

    if(!timer)
    {
        clearInterval(x);
        return;
    }


    var now =
        new Date().getTime();


    var distance =
        countDownDate - now;


    var days =
        Math.floor(
            distance /
            (1000 * 60 * 60 * 24)
        );


    var hours =
        Math.floor(
            (distance %
            (1000 * 60 * 60 * 24)) /
            (1000 * 60 * 60)
        );


    var minutes =
        Math.floor(
            (distance %
            (1000 * 60 * 60)) /
            (1000 * 60)
        );


    var seconds =
        Math.floor(
            (distance %
            (1000 * 60)) /
            1000
        );


    let d = 0;
    let h = 0;
    let m = 0;
    let s = 0;


    if(days != 0)
    {
        d = days + " ";
    }


    if(hours != 0)
    {
        h = hours + " ";
    }


    if(minutes != 0)
    {
        m = minutes + " ";
    }


    if(seconds != 0)
    {
        s = seconds + " ";
    }


    timer.innerHTML =
        d + "দিন " +
        h + "ঘণ্টা " +
        m + "মিনিট " +
        s + "সেকেন্ড ";


    if(
        distance < 60000 &&
        distance > 59000
    )
    {
        timer.style.color = 'red';
    }


    if(distance < 0)
    {

        clearInterval(x);

        timer.innerHTML =
            '<span style="color:#d00">সময় শেষ</span>';

    }

}, 1000);


/* =========================================================
   PREVENT DOUBLE CLICK
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function()
    {

        const form =
            document.getElementById(
                'courseConfirmForm'
            );


        const button =
            document.getElementById(
                'courseConfirmButton'
            );


        if(!form || !button)
        {
            return;
        }


        form.addEventListener(
            'submit',
            function()
            {

                button.disabled = true;

                button.innerHTML =
                    '⏳ পেমেন্ট প্রসেস হচ্ছে...';


                button.style.opacity =
                    '0.7';


                button.style.cursor =
                    'wait';

            }
        );

    }
);

</script>

@endsection