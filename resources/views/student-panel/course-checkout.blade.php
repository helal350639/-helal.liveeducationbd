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
   EXISTING PAYMENT LOGIC PRESERVED
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
   CHECKOUT PAGE
========================================================= */

.checkout-page{
    width:100%;
    max-width:1050px;
    margin:0 auto;
    padding:25px 15px 40px;
}


/* =========================================================
   TITLE
========================================================= */

.checkout-title{
    text-align:center;
    margin-bottom:22px;
}

.checkout-title h1{
    margin:0;
    font-size:30px;
    font-weight:700;
    color:#092b68;
}

.checkout-title p{
    margin:6px 0 0;
    color:#667085;
    font-size:14px;
}


/* =========================================================
   MAIN CARD
========================================================= */

.checkout-card{
    background:#fff;
    border:1px solid #dce8f5;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 7px 25px rgba(0,70,140,.09);
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
    padding:18px 22px 8px;
    text-align:center;
}

.checkout-course-header h2{
    margin:0;
    font-size:24px;
    line-height:1.4;
    color:#092b68;
    font-weight:700;
}

.checkout-course-subtitle{
    margin:6px 0 0;
    color:#667085;
    font-size:14px;
    line-height:1.6;
}


/* =========================================================
   OFFER
========================================================= */

.checkout-offer{
    margin:12px 22px 0;
    padding:10px 13px;
    background:#fff7ed;
    border:1px solid #fed7aa;
    border-radius:9px;
    text-align:center;
    color:#c2410c;
    font-size:13px;
}

.checkout-offer strong{
    color:#b91c1c;
}


/* =========================================================
   COUNTDOWN
========================================================= */

.checkout-countdown{
    margin:9px 22px 0;
    padding:9px;
    background:#fff1f2;
    border:1px solid #fecdd3;
    border-radius:9px;
    text-align:center;
    color:#be123c;
    font-size:13px;
    font-weight:600;
}


/* =========================================================
   BODY
========================================================= */

.checkout-body{
    padding:18px 22px 10px;
}


/* =========================================================
   DEPARTMENT
========================================================= */

.checkout-field{
    margin-bottom:17px;
}

.checkout-field label{
    display:block;
    margin-bottom:6px;
    font-size:14px;
    font-weight:600;
    color:#123d79;
}

.checkout-field select{
    width:100%;
    min-height:43px;
    padding:8px 11px;
    border:1px solid #cbd8e6;
    border-radius:8px;
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
    border-radius:12px;
    overflow:hidden;
    margin-top:12px;
    background:#fff;
}

.checkout-summary-title{
    padding:11px 14px;
    background:#f1f7ff;
    color:#123d79;
    font-size:15px;
    font-weight:700;
}

.checkout-summary table{
    width:100%;
    border-collapse:collapse;
    margin:0;
}

.checkout-summary td,
.checkout-summary th{
    padding:11px 13px;
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


/* =========================================================
   EXTRA FEE NOTE
========================================================= */

.extra-fee-wrap{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:6px;
    flex-wrap:wrap;
}

.extra-fee-note{
    display:inline-block;
    padding:3px 7px;
    background:#eef6ff;
    border:1px solid #cfe2f5;
    border-radius:5px;
    color:#1269c9;
    font-size:10px;
    font-weight:500;
    line-height:1.3;
    max-width:180px;
    white-space:normal;
    text-align:left;
}


/* =========================================================
   DISCOUNT
========================================================= */

.checkout-discount th{
    color:#dc2626 !important;
}

.checkout-reference th{
    color:#16a34a !important;
}


/* =========================================================
   TOTAL
========================================================= */

.checkout-total td,
.checkout-total th{
    font-size:18px !important;
    font-weight:700 !important;
    color:#092b68 !important;
    background:#eef7ff;
}


/* =========================================================
   PAYMENT METHOD
========================================================= */

.payment-method-section{
    margin-top:20px;
    background:#fff;
    border-radius:14px;
    padding:18px;
    box-shadow:0 4px 18px rgba(0,0,0,.07);
}

.payment-method-title{
    margin:0 0 13px;
    font-size:20px;
    font-weight:700;
    color:#172554;
    text-align:center;
}

.payment-method-box{
    width:100%;
    min-height:145px;
    border:3px solid #17177d;
    border-radius:13px;
    background:#f1fbfb;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:15px;
    box-sizing:border-box;
}

.payment-method-content{
    width:100%;
    text-align:center;
}

.payment-radio{
    width:22px;
    height:22px;
    accent-color:#0875df;
    vertical-align:middle;
    margin-right:8px;
}

.payment-method-name{
    display:inline-block;
    font-size:17px;
    font-weight:700;
    color:#172554;
}

.payment-method-items{
    margin-top:12px;
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:8px;
}

.payment-method-item{
    background:#fff;
    border:1px solid #d7e2ea;
    border-radius:5px;
    padding:6px 10px;
    font-size:11px;
    color:#475467;
}


/* =========================================================
   IMPORTANT NOTE
========================================================= */

.checkout-note{
    margin-top:18px;
    padding:16px 18px;
    background:#17116e;
    border-left:7px solid #64b5f6;
    border-radius:12px;
    color:#fff;
    font-size:14px;
    line-height:1.8;
    box-shadow:0 5px 15px rgba(0,0,0,.12);
}

.checkout-note-title{
    display:block;
    margin-bottom:4px;
    font-weight:700;
    font-size:15px;
    color:#fff;
}


/* =========================================================
   TERMS
========================================================= */

.checkout-terms{
    margin-top:18px;
    padding:13px 14px;
    background:#fff;
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
    width:17px;
    height:17px;
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
    margin-top:17px;
    padding:12px 20px;
    border:0;
    border-radius:11px;
    background:#198754;
    color:#fff;
    font-size:18px;
    font-weight:700;
    cursor:pointer;
    transition:.25s ease;
    box-shadow:0 5px 15px rgba(25,135,84,.18);
}

.checkout-payment-button:hover{
    background:#157347;
    transform:translateY(-1px);
}

.checkout-payment-button:disabled{
    opacity:.65;
    cursor:wait;
    transform:none;
}


/* =========================================================
   FREE COURSE BUTTON
========================================================= */

.checkout-confirm-button{
    width:100%;
    min-height:52px;
    margin-top:17px;
    border:0;
    border-radius:11px;
    background:#198754;
    color:#fff;
    font-size:18px;
    font-weight:700;
    cursor:pointer;
}


/* =========================================================
   ALREADY PURCHASED
========================================================= */

.checkout-purchased{
    margin-top:18px;
    padding:20px;
    text-align:center;
    background:#effdf5;
    border:1px solid #bbf7d0;
    border-radius:12px;
    color:#166534;
}

.checkout-purchased h4{
    margin:0 0 12px;
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
   MOBILE
========================================================= */

@media(max-width:600px){

    .checkout-page{
        width:100%;
        padding:10px 7px 25px;
        margin:0;
        box-sizing:border-box;
    }

    .checkout-title{
        margin-bottom:12px;
    }

    .checkout-title h1{
        font-size:22px;
    }

    .checkout-title p{
        font-size:11px;
    }

    .checkout-card{
        width:100%;
        border-radius:10px;
    }

    .checkout-media img{
        max-height:220px;
    }

    .checkout-course-header{
        padding:12px 10px 6px;
    }

    .checkout-course-header h2{
        font-size:18px;
    }

    .checkout-course-subtitle{
        font-size:11px;
    }

    .checkout-offer{
        margin:8px 10px 0;
        padding:8px;
        font-size:10px;
    }

    .checkout-countdown{
        margin:6px 10px 0;
        padding:7px;
        font-size:10px;
    }

    .checkout-body{
        padding:10px;
    }

    .checkout-field{
        margin-bottom:11px;
    }

    .checkout-field label{
        font-size:12px;
    }

    .checkout-field select{
        min-height:40px;
        font-size:12px;
    }

    .checkout-summary{
        margin-top:9px;
    }

    .checkout-summary-title{
        padding:9px 10px;
        font-size:13px;
    }

    .checkout-summary td,
    .checkout-summary th{
        padding:8px 9px;
        font-size:11px;
    }

    .checkout-total td,
    .checkout-total th{
        font-size:14px !important;
    }

    .extra-fee-wrap{
        gap:4px;
    }

    .extra-fee-note{
        max-width:115px;
        font-size:8px;
        padding:2px 4px;
    }

    .payment-method-section{
        margin-top:13px;
        padding:10px;
        border-radius:10px;
    }

    .payment-method-title{
        font-size:16px;
        margin-bottom:9px;
    }

    .payment-method-box{
        min-height:120px;
        padding:9px;
        border-width:2px;
        border-radius:9px;
    }

    .payment-method-name{
        font-size:13px;
    }

    .payment-method-items{
        margin-top:8px;
        gap:4px;
    }

    .payment-method-item{
        padding:4px 6px;
        font-size:8px;
    }

    .checkout-note{
        margin-top:12px;
        padding:10px 11px;
        border-left-width:5px;
        border-radius:8px;
        font-size:10px;
        line-height:1.7;
    }

    .checkout-note-title{
        font-size:11px;
        margin-bottom:2px;
    }

    .checkout-terms{
        margin-top:12px;
        padding:9px;
        font-size:9px;
        line-height:1.7;
    }

    .checkout-terms input{
        width:15px;
        height:15px;
    }

    .checkout-payment-button,
    .checkout-confirm-button{
        min-height:46px;
        margin-top:12px;
        padding:9px;
        font-size:14px;
        border-radius:9px;
    }

    .checkout-purchased{
        margin-top:12px;
        padding:14px;
    }

}


/* =========================================================
   EXTRA SMALL MOBILE
========================================================= */

@media(max-width:380px){

    .checkout-page{
        padding-left:5px;
        padding-right:5px;
    }

    .checkout-summary td,
    .checkout-summary th{
        padding:7px 6px;
        font-size:10px;
    }

    .extra-fee-note{
        max-width:95px;
        font-size:7px;
    }

    .payment-method-box{
        min-height:105px;
    }

    .checkout-note{
        font-size:9px;
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
                    src="https://www.youtube.com/embed/{{ $value->video }}?autoplay=1&mute=1"
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
             CHECKOUT BODY
        ================================================== -->

        <div class="checkout-body">


            <!-- EXISTING PAYMENT FORM -->

            <form
                action="{{ route('students.course.apply') }}"
                method="post"
                id="courseCheckoutForm"
            >

                @csrf


                <!-- HIDDEN DATA -->

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

                                <th>

                                    <div class="extra-fee-wrap">

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
                     PAYMENT METHOD
                ================================================== -->

                @if(
                    !empty($user->id) &&
                    !$value->students()->where('id', $user->id)->first() &&
                    $value->price >= 1
                )

                    <div class="payment-method-section">

                        <h3 class="payment-method-title">
                            আমাদের পেমেন্ট সিস্টেমে অন্তরভুক্ত আছে
                        </h3>

                        <div class="payment-method-box">

                            <div class="payment-method-content">

                                <label>

                                    <input
                                        type="radio"
                                        name="payment_method_display"
                                        value="online"
                                        class="payment-radio"
                                        checked
                                        disabled
                                    >

                                    <span class="payment-method-name">
                                        Online Payment Gateway
                                    </span>

                                </label>


                                <div class="payment-method-items">

                                    <span class="payment-method-item">
                                        Card
                                    </span>

                                    <span class="payment-method-item">
                                        Mobile Banking
                                    </span>

                                    <span class="payment-method-item">
                                        Internet Banking
                                    </span>

                                    <span class="payment-method-item">
                                        Wallet
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                @endif


                <!-- =================================================
                     IMPORTANT NOTE
                ================================================== -->

                @if(
                    !empty($user->id) &&
                    !$value->students()->where('id', $user->id)->first() &&
                    $value->price >= 1
                )

                    <div class="checkout-note">

                        <span class="checkout-note-title">
                            ⚠️ দ্রষ্টব্য
                        </span>

                        পেমেন্ট গেটওয়েতে প্রবেশ করে আপনার পেমেন্ট সম্পন্ন হওয়ার
                        পর অনুগ্রহ করে নিশ্চিতকরণের জন্য ৫-৬ সেকেন্ড অপেক্ষা করুন।
                        নিশ্চিতকরণ না আসা পর্যন্ত পেজটি বন্ধ করবেন না বা ত্যাগ করবেন না।

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
                                Refund Policy
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
                            onclick="return confirm('আপনার তথ্য পুনরায় যাচাই করতে Next পেজ তথ্য দেখুন.')"
                        >
                            কনফার্ম করুন
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>

                    @else

                        <button
                            type="submit"
                            class="checkout-payment-button"
                            onclick="return confirm('আপনার তথ্য পুনরায় যাচাই করতে Next পেজ তথ্য দেখুন.')"
                        >

                            <i class="fa-solid fa-lock"></i>

                            Proceed To Payment

                            <i class="fa-solid fa-arrow-right"></i>

                        </button>

                    @endif

                @endif


            </form>

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

    const timerElement =
        document.getElementById('timer');

    if(!timerElement){
        return;
    }


    const countDownDate =
        new Date(
            "{{ $source->dtcformat($value->reg_end_at) }}"
        ).getTime();


    function updateCountdown(){

        const now =
            new Date().getTime();

        const distance =
            countDownDate - now;


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

            output +=
                days + ' দিন ';

        }


        output +=
            hours + ' ঘণ্টা ';


        output +=
            minutes + ' মিনিট ';


        output +=
            seconds + ' সেকেন্ড';


        timerElement.innerHTML =
            output;


        if(distance < 60000){

            timerElement.style.color =
                '#d00';

        }

    }


    updateCountdown();

    setInterval(
        updateCountdown,
        1000
    );

})();

@endif



/* =========================================================
   PAYMENT FORM
   PREVENT DOUBLE CLICK
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

                    button.style.opacity =
                        '0.70';

                    button.style.cursor =
                        'wait';

                }

            }
        );

    }
);

</script>

@endsection