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
   EXISTING LOGIC — DO NOT CHANGE
========================================================= */

$price = 0;
$discount = 0;
$reference_discount = 0;
$selling_price = 0;


/* Course offer discount */

if(
    $value->discount &&
    $value->offer_end_at &&
    $value->offer_end_at > now()
)
{
    $discount = $value->discount;
}


/* Reference discount */

if(
    $value->reference_discount &&
    Session::get('batch-referrer') &&
    Session::get('batch-referrer')['batch'] == $batch->id
)
{
    $reference_discount = $value->reference_discount;

    $discount = $discount + $value->reference_discount;
}


/* Total original price */

$price = $value->price + $value->fee;


/* Final payable price */

$selling_price = $price - $discount;


/* Course price only */

$course_price = $price - $value->fee;

@endphp


@extends('student')

@section('title', 'Course Checkout')


@section('content')


<style>

/* =========================================================
   PAYMENT / CHECKOUT PAGE
========================================================= */

.checkout-page{

    width:100%;

    max-width:1150px;

    margin:0 auto;

    padding:30px 20px 45px;
}


/* =========================================================
   PAGE TITLE
========================================================= */

.checkout-title{

    text-align:center;

    margin-bottom:25px;
}

.checkout-title h1{

    margin:0;

    font-size:32px;

    font-weight:700;

    color:#092b68;
}

.checkout-title p{

    margin:7px 0 0;

    color:#667085;

    font-size:15px;
}


/* =========================================================
   MAIN CHECKOUT CARD
========================================================= */

.checkout-card{

    background:#fff;

    border:1px solid #dce8f5;

    border-radius:18px;

    overflow:hidden;

    box-shadow:0 8px 30px rgba(0,70,140,.10);
}


/* =========================================================
   COURSE MEDIA
========================================================= */

.checkout-media{

    width:100%;

    background:#eef5fc;

    text-align:center;

    overflow:hidden;
}

.checkout-media img{

    width:100%;

    max-height:360px;

    object-fit:cover;

    display:block;
}


.checkout-video{

    position:relative;

    width:100%;

    padding-bottom:56.25%;

    overflow:hidden;
}

.checkout-video iframe{

    position:absolute;

    top:0;

    left:0;

    width:100%;

    height:100%;

    border:0;
}


/* =========================================================
   COURSE HEADER
========================================================= */

.checkout-course-header{

    padding:22px 25px 10px;

    text-align:center;
}

.checkout-course-header h2{

    margin:0;

    font-size:26px;

    line-height:1.4;

    color:#092b68;

    font-weight:700;
}

.checkout-course-subtitle{

    margin:8px 0 0;

    color:#667085;

    font-size:14px;

    line-height:1.6;
}


/* =========================================================
   OFFER DEADLINE
========================================================= */

.checkout-offer{

    margin:15px 25px 0;

    padding:12px 15px;

    background:#fff7ed;

    border:1px solid #fed7aa;

    border-radius:10px;

    text-align:center;

    color:#c2410c;

    font-size:14px;
}

.checkout-offer strong{

    color:#b91c1c;
}


/* =========================================================
   COUNTDOWN
========================================================= */

.checkout-countdown{

    margin:10px 25px 0;

    padding:10px;

    background:#fff1f2;

    border:1px solid #fecdd3;

    border-radius:10px;

    text-align:center;

    color:#be123c;

    font-size:14px;

    font-weight:600;
}


/* =========================================================
   CHECKOUT BODY
========================================================= */

.checkout-body{

    padding:20px 25px 10px;
}


/* =========================================================
   DEPARTMENT
========================================================= */

.checkout-field{

    margin-bottom:20px;
}

.checkout-field label{

    display:block;

    margin-bottom:7px;

    font-size:15px;

    font-weight:600;

    color:#123d79;
}

.checkout-field select{

    width:100%;

    min-height:45px;

    padding:8px 12px;

    border:1px solid #cbd8e6;

    border-radius:9px;

    background:#fff;

    color:#333;

    font-size:14px;

    outline:none;
}

.checkout-field select:focus{

    border-color:#0875df;

    box-shadow:0 0 0 3px rgba(8,117,223,.10);
}


/* =========================================================
   PRICE SUMMARY
========================================================= */

.checkout-summary{

    border:1px solid #dce8f5;

    border-radius:14px;

    overflow:hidden;

    margin-top:15px;

    background:#fbfdff;
}

.checkout-summary-title{

    padding:13px 16px;

    background:#f1f7ff;

    color:#123d79;

    font-size:16px;

    font-weight:700;
}


.checkout-summary table{

    width:100%;

    border-collapse:collapse;

    margin:0;
}

.checkout-summary td,
.checkout-summary th{

    padding:12px 15px;

    border-bottom:1px solid #e7eef6;

    font-size:14px;
}

.checkout-summary td{

    color:#475467;
}

.checkout-summary th{

    text-align:right;

    color:#123d79;
}

.checkout-summary tr:last-child td,
.checkout-summary tr:last-child th{

    border-bottom:0;
}


/* TOTAL */

.checkout-total td,
.checkout-total th{

    font-size:18px !important;

    font-weight:700 !important;

    color:#092b68 !important;

    background:#eef7ff;
}


/* DISCOUNT */

.checkout-discount th{

    color:#dc2626 !important;
}


/* REFERENCE DISCOUNT */

.checkout-reference th{

    color:#16a34a !important;
}


/* =========================================================
   TERMS
========================================================= */

.checkout-terms{

    margin-top:20px;

    padding:13px 15px;

    background:#f8fafc;

    border:1px solid #e2e8f0;

    border-radius:10px;

    font-size:13px;

    line-height:1.7;

    color:#475467;
}

.checkout-terms label{

    cursor:pointer;
}

.checkout-terms input{

    width:16px;

    height:16px;

    vertical-align:middle;

    margin-right:5px;
}

.checkout-terms a{

    color:#0875df;

    font-weight:600;

    text-decoration:none;
}


/* =========================================================
   PAYMENT BUTTON
========================================================= */

.checkout-payment-button{

    width:100%;

    min-height:52px;

    margin-top:18px;

    padding:12px 20px;

    border:0;

    border-radius:11px;

    background:linear-gradient(
        135deg,
        #0875df,
        #1269c9
    );

    color:#fff;

    font-size:18px;

    font-weight:700;

    cursor:pointer;

    transition:.25s ease;

    box-shadow:0 7px 18px rgba(8,117,223,.22);
}

.checkout-payment-button:hover{

    background:linear-gradient(
        135deg,
        #0666c5,
        #0d5eb5
    );

    transform:translateY(-1px);
}

.checkout-payment-button i{

    margin-left:7px;
}


/* =========================================================
   ALREADY PURCHASED
========================================================= */

.checkout-purchased{

    padding:20px;

    text-align:center;

    background:#effdf5;

    border:1px solid #bbf7d0;

    border-radius:12px;

    color:#166534;
}

.checkout-purchased h4{

    margin:0 0 12px;

    font-size:18px;
}

.checkout-my-course{

    display:inline-block;

    padding:10px 18px;

    background:#16a34a;

    color:#fff !important;

    text-decoration:none !important;

    border-radius:8px;

    font-weight:600;
}


/* =========================================================
   FREE COURSE BUTTON
========================================================= */

.checkout-confirm-button{

    width:100%;

    min-height:52px;

    border:0;

    border-radius:11px;

    background:#16a34a;

    color:#fff;

    font-size:18px;

    font-weight:700;

    cursor:pointer;
}


/* =========================================================
   IMPORTANT NOTE
========================================================= */

.checkout-note{

    margin:22px 0 0;

    padding:14px 16px;

    background:#fffbea;

    border:1px solid #fde68a;

    border-radius:10px;

    color:#713f12;

    font-size:13px;

    line-height:1.7;
}

.checkout-note-title{

    display:block;

    margin-bottom:5px;

    font-weight:700;

    color:#92400e;
}


/* =========================================================
   FOOTER AREA OF CHECKOUT CARD
========================================================= */

.checkout-footer{

    padding:0 25px 25px;
}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:600px){

    .checkout-page{

        padding:12px 8px 25px;

        margin:0 auto;
    }


    .checkout-title{

        margin-bottom:14px;
    }


    .checkout-title h1{

        font-size:23px;
    }


    .checkout-title p{

        font-size:12px;
    }


    .checkout-card{

        border-radius:13px;
    }


    .checkout-media img{

        max-height:220px;
    }


    .checkout-course-header{

        padding:14px 12px 7px;
    }


    .checkout-course-header h2{

        font-size:19px;
    }


    .checkout-course-subtitle{

        font-size:12px;

        margin-top:5px;
    }


    .checkout-offer{

        margin:10px 12px 0;

        padding:9px 10px;

        font-size:11px;
    }


    .checkout-countdown{

        margin:7px 12px 0;

        padding:8px;

        font-size:11px;
    }


    .checkout-body{

        padding:12px;
    }


    .checkout-field{

        margin-bottom:13px;
    }


    .checkout-field label{

        font-size:13px;
    }


    .checkout-field select{

        min-height:42px;

        font-size:13px;
    }


    .checkout-summary-title{

        padding:10px 12px;

        font-size:14px;
    }


    .checkout-summary td,
    .checkout-summary th{

        padding:9px 10px;

        font-size:12px;
    }


    .checkout-total td,
    .checkout-total th{

        font-size:15px !important;
    }


    .checkout-terms{

        padding:10px;

        font-size:11px;

        line-height:1.7;
    }


    .checkout-payment-button{

        min-height:46px;

        padding:10px;

        font-size:15px;
    }


    .checkout-confirm-button{

        min-height:46px;

        font-size:15px;
    }


    .checkout-note{

        margin-top:15px;

        padding:10px;

        font-size:11px;
    }


    .checkout-footer{

        padding:0 12px 15px;
    }

}

/* =========================================================
   EXTRA FEE NOTE
========================================================= */

.extra-fee-note{

    display:inline-block;

    margin-left:8px;

    padding:3px 8px;

    background:#eef6ff;

    border:1px solid #cfe2f5;

    border-radius:5px;

    color:#1269c9;

    font-size:11px;

    font-weight:500;

    vertical-align:middle;

    white-space:nowrap;
}


/* MOBILE */

@media(max-width:600px){

    .extra-fee-note{

        margin-left:5px;

        padding:2px 5px;

        font-size:9px;

        max-width:120px;

        overflow:hidden;

        text-overflow:ellipsis;

        vertical-align:middle;
    }

}

</style>


<!-- =========================================================
     CHECKOUT PAGE
========================================================= -->

<section class="checkout-page">


    <!-- PAGE TITLE -->

    <div class="checkout-title">

        <h1>
            Course Checkout
        </h1>

        <p>
            আপনার কোর্সের তথ্য যাচাই করে পেমেন্ট সম্পন্ন করুন
        </p>

    </div>


    <!-- MAIN CARD -->

    <div class="checkout-card">


        <!-- =================================================
             COURSE MEDIA
        ================================================== -->

        @if($value->video)

            <div class="checkout-media checkout-video">

                <iframe
                    src="https://www.youtube.com/embed/{{$value->video}}?autoplay=1&mute=1"
                    allowfullscreen>
                </iframe>

            </div>

        @elseif($value->banner)

            <div class="checkout-media">

                <img
                    src="{{ $value->banner ? $value->banner : '/img/course.jpg' }}"
                    alt="{{ $value->name }}"
                >

            </div>

        @endif


        <!-- =================================================
             COURSE NAME
        ================================================== -->

        <div class="checkout-course-header">

            <h2>
                {{ $value->name }}
            </h2>

            @if($value->subtitle)

                <p class="checkout-course-subtitle">
                    {{ $value->subtitle }}
                </p>

            @endif

        </div>


        <!-- =================================================
             OFFER DEADLINE
        ================================================== -->

        @if($value->offer_end_at)

            <div class="checkout-offer">

                ডিসকাউন্ট অফারের মেয়াদ:

                <strong>
                    {{ $source->dtformat($value->offer_end_at) }}
                </strong>

            </div>

        @endif


        <!-- =================================================
             REGISTRATION COUNTDOWN
        ================================================== -->

        @if($value->reg_end_at)

            <div class="checkout-countdown">

                রেজিস্ট্রেশনের সময় বাকি:

                <span id="timer">
                    হিসাব করা হচ্ছে...
                </span>

            </div>

        @endif


        <!-- =================================================
             CHECKOUT BODY
        ================================================== -->

        <div class="checkout-body">


            <!-- =================================================
                 FORM
                 EXISTING PAYMENT FLOW — UNCHANGED
            ================================================== -->

            <form
                action="{{ route('students.course.apply') }}"
                method="post"
                id="courseCheckoutForm"
            >

                @csrf


                <!-- =================================================
                     EXISTING HIDDEN DATA
                ================================================== -->

                <input
                    type="hidden"
                    name="student_id"
                    value="{{ $user ? $user->id : null }}"
                >

                <input
                    type="hidden"
                    name="batch_id"
                    value="{{ $value->id }}"
                >

                <input
                    type="hidden"
                    name="name"
                    value="{{ $student ? $student->name : '' }}"
                >

                <input
                    type="hidden"
                    name="email"
                    value="{{ $student ? $student->email : '' }}"
                >

                <input
                    type="hidden"
                    name="phone"
                    value="{{ $student ? $student->contact : '' }}"
                >

                <input
                    type="hidden"
                    name="address1"
                    value=""
                >

                <input
                    type="hidden"
                    name="total"
                    value="{{ $selling_price }}"
                >


                <!-- =================================================
                     DEPARTMENT
                ================================================== -->

                @if(
                    !empty($user->id) &&
                    !$value->students()->where('id', $user->id)->first()
                )

                    @if($departments->count())

                        <div class="checkout-field">

                            <label for="department_id">

                                ডিপার্টমেন্ট নির্বাচন করুন

                            </label>

                            <select
                                id="department_id"
                                name="department_id"
                                required
                            >

                                <option value="">
                                    Select One
                                </option>

                                @foreach($departments as $val)

                                    <option value="{{ $val->id }}">
                                        {{ $val->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                    @else

                        <div class="checkout-field">

                            <label>
                                ডিপার্টমেন্ট
                            </label>

                            <div>
                                No Departments for The course
                            </div>

                        </div>

                    @endif

                @endif


                <!-- =================================================
                     PRICE SUMMARY
                ================================================== -->

                @if(
                    !empty($user->id) &&
                    !$value->students()->where('id', $user->id)->first()
                )

                    <div class="checkout-summary">


                        <div class="checkout-summary-title">

                            পেমেন্টের বিস্তারিত

                        </div>


                        <table>

                            <!-- COURSE PRICE -->

                            <tr>

                                <td>
                                    কোর্সের মূল্য
                                </td>

                                <th>
                                    ৳ {{ $source->point0($course_price) }}
                                </th>

                            </tr>


                            <!-- OTHER FEE -->

                            <tr>

                                <td>
                                    অন্যান্য ফী (+)
                                </td>

                                <th style="text-align:right;">

    <span>
        ৳ {{ $source->point0($value->fee) }}
    </span>

    @if($value->fee > 0 && $value->extra_fee_note)

        <span
            class="extra-fee-note"
            title="{{ $value->extra_fee_note }}"
        >
            {{ $value->extra_fee_note }}
        </span>

    @endif

</th>

                            </tr>


                            <!-- DISCOUNT -->

                            @if($discount)

                                <tr class="checkout-discount">

                                    <td>
                                        ডিসকাউন্ট (-)
                                    </td>

                                    <th>
                                        - ৳ {{ $source->point0($discount - $reference_discount) }}
                                    </th>

                                </tr>

                            @endif


                            <!-- REFERENCE DISCOUNT -->

                            @if($reference_discount)

                                <tr class="checkout-reference">

                                    <td>
                                        রিফারেন্স ডিসকাউন্ট (-)
                                    </td>

                                    <th>
                                        - ৳ {{ $source->point0($reference_discount) }}
                                    </th>

                                </tr>

                            @endif


                            <!-- TOTAL -->

                            <tr class="checkout-total">

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
                     TERMS & CONDITIONS
                ================================================== -->

                @if(
                    !empty($user->id) &&
                    !$value->students()->where('id', $user->id)->first()
                )

                    <div class="checkout-terms">

                        <label>

                            <input
                                type="checkbox"
                                required
                                id="agree"
                            >

                            I agree to the

                            <a
                                href="{{ route('home.page', 'terms-condition') }}"
                                target="_blank"
                            >
                                Terms & Condition
                            </a>,

                            <a
                                href="{{ route('home.page', 'privacy-policy') }}"
                                target="_blank"
                            >
                                Privacy Policy
                            </a>

                            and

                            <a
                                href="{{ route('home.page', 'return-policy') }}"
                                target="_blank"
                            >
                                Fund Return Policy
                            </a>.

                        </label>

                    </div>

                @endif


                <!-- =================================================
                     PAYMENT / CONFIRM BUTTON
                ================================================== -->

                @if(
                    !empty($user->id) &&
                    $value->students()->where('id', $user->id)->first()
                )

                    <div class="checkout-purchased">

                        <h4>
                            আপনি কোর্সটি কিনেছেন
                        </h4>

                        <a
                            class="checkout-my-course"
                            href="{{ route('students.my-course') }}"
                        >
                            আমার কোর্স এ ফিরে যান
                        </a>

                    </div>

                @else


                    @if($value->price < 1)

                        <button
                            type="submit"
                            class="checkout-confirm-button"
                            onclick="return confirm('Double check you provided information.')"
                        >

                            কনফার্ম করুন

                        </button>

                    @else

                        <button
                            type="submit"
                            class="checkout-payment-button"
                            onclick="return confirm('Double check you provided information.')"
                        >

                            <i class="fa-solid fa-lock"></i>

                            পেমেন্ট এর জন্য এগিয়ে যান

                            <i class="fa-solid fa-arrow-right"></i>

                        </button>

                    @endif

                @endif


            </form>

        </div>


        <!-- =================================================
             NOTE
        ================================================== -->

        <div class="checkout-footer">

            <div class="checkout-note">

                <span class="checkout-note-title">

                    ⚠️ দ্রষ্টব্য

                </span>

                পেমেন্ট সম্পন্ন করার পর Payment Gateway থেকে
                সরাসরি বের হয়ে যাবেন না। Payment সম্পন্ন হওয়ার
                পর কয়েক সেকেন্ড অপেক্ষা করুন, যাতে আপনার
                পেমেন্টের তথ্য সঠিকভাবে আমাদের সিস্টেমে
                আপডেট হতে পারে।

            </div>

        </div>


    </div>

</section>


@endsection


@section('scripts')

<script>

/* =========================================================
   REGISTRATION COUNTDOWN
========================================================= */

@if($value->reg_end_at)

(function(){

    const timerElement = document.getElementById('timer');

    if(!timerElement){
        return;
    }


    const countDownDate =
        new Date(
            "{{ $source->dtcformat($value->reg_end_at) }}"
        ).getTime();


    function updateCountdown(){

        const now = new Date().getTime();

        const distance = countDownDate - now;


        if(distance <= 0){

            timerElement.innerHTML =
                '<span style="color:#d00">সময় শেষ</span>';

            return;
        }


        const days =
            Math.floor(
                distance /
                (1000 * 60 * 60 * 24)
            );


        const hours =
            Math.floor(
                (distance %
                (1000 * 60 * 60 * 24)) /
                (1000 * 60 * 60)
            );


        const minutes =
            Math.floor(
                (distance %
                (1000 * 60 * 60)) /
                (1000 * 60)
            );


        const seconds =
            Math.floor(
                (distance %
                (1000 * 60)) /
                1000
            );


        let output = '';


        if(days > 0){

            output += days + ' দিন ';

        }


        output += hours + ' ঘণ্টা ';

        output += minutes + ' মিনিট ';

        output += seconds + ' সেকেন্ড';


        timerElement.innerHTML = output;


        if(distance < 60000){

            timerElement.style.color = '#d00';

        }

    }


    updateCountdown();

    setInterval(updateCountdown,1000);

})();

@endif


/* =========================================================
   PAYMENT FORM
   Existing form submission remains untouched
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function(){

        const form =
            document.getElementById(
                'courseCheckoutForm'
            );


        if(!form){
            return;
        }


        form.addEventListener(
            'submit',
            function(){

                const button =
                    form.querySelector(
                        '.checkout-payment-button, .checkout-confirm-button'
                    );


                if(button){
                    button.disabled = true;
                    button.style.opacity = '0.75';
                    button.style.cursor = 'wait';

                }

            }
        );

    }
);

</script>

@endsection