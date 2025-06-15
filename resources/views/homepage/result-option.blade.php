@php
$user = Auth::guard('student')->user();
@endphp

@extends('homepage')
@section('title', 'কোর্স সমূহ')
@section('content')

  <style>
    .checkbox{padding-left: 25px}
    .panel ::-webkit-scrollbar{width: 5px;}
    ::-webkit-scrollbar-thumb{background-color: #ddd}
    .course-image{width:100%}
  </style>

    <!-- Main content -->
    <section class="our-service p-5">
      <div class="container">
        <div class="row">
          
          <div class="title-area text-center">
            <h1> Results </h1>
          </div>
          <div class="home-services-area">
            <div class="row g-2 g-md-3 g-lg-4" style="--bs-gutter-x: 0.3rem !important;">
              <div class="col-md-8 offset-md-2">
                <div class="row">
                  <div class="col-md-6 col-12 mb-1 dropdown bcs-jobs" id="home-services-area-one">
                    <a href="{{route('category.batches', 'Premium Results')}}">
                      <div class="my-home-service premium-course-box">
                        <div class="my-home-service-icon">
                          <img src="/web/img/premium.png" alt="" loading="lazy">
                        </div>
                        <div class="my-home-service-content">
                          <h3>Premium Batches</h3>
                        </div>
                      </div>
                    </a>
                  </div>
                  
                  <div class="col-md-6 col-12 mb-1 dropdown bcs-jobs" id="home-services-area-one">
                    <a href="{{route('category.batches', 'Free Results')}}">
                      <div class="my-home-service premium-course-box">
                        <div class="my-home-service-icon">
                          <img src="/web/img/free.jpg" alt="" loading="lazy">
                        </div>
                        <div class="my-home-service-content">
                          <h3>Free Batches</h3>
                        </div>
                      </div>
                    </a>
                  </div>
                </div>
              </div>
              
              

            </div>
          </div>
        </div> <!-- /.row -->
        
        @include('partials.back-button')

      </div>
    </section> <!-- /.content -->

<script></script>
@endsection