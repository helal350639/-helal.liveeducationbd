@extends('homepage')

@section('title','Course Details')


@section('content')


<section class="py-5">


<div class="container">


<div class="course-header-box">

    <a href="{{ url()->previous() }}" class="back-btn">
        ← Back
    </a>


    <div class="course-title-box">

        <span class="course-icon">
            🎓
        </span>

        <h2>
            {{ $course->name }}
        </h2>

        <p>
            এই কোর্সের অধীনে যে সকল ব্যাচ চলমান আছে
        </p>

    </div>

</div>


<div class="course-list">


@foreach($course->batches as $batch)


<div class="course-card">


    <!-- Banner -->

    <div class="course-banner">


        @if($batch->banner)

            <img src="{{ asset($batch->banner) }}" 
                 alt="{{ $batch->name }}">


        @else

            <div class="no-banner">

                Course Image

            </div>


        @endif


    </div>




    <!-- Body -->

    <div class="course-body">


        <h5>

            {{ $batch->name }}

        </h5>



        <!-- Price -->

       <div class="course-price">

    @if($batch->price)

    <span class="old-price">
        ৳ {{ number_format($batch->price,2) }}
    </span>


    @php
        $finalPrice = $batch->price - $batch->discount;
    @endphp


    <span class="new-price">
        ৳ {{ number_format($finalPrice,2) }}
    </span>


    @if($batch->discount)

    <span class="save">
        Save ৳{{ number_format($batch->discount,2) }}
    </span>

    @endif


    @endif


</div>



        <!-- Button -->


        <a href="{{ route('home.course.show',$batch->id) }}"
           class="course-buy-btn">

            কোর্সটি কিনুন

        </a>



    </div>


</div>


@endforeach


</div>


</div>


</section>


@endsection