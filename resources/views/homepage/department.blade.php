@php
$user = Auth::guard('student')->user();
@endphp

@extends('batch')
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
            <h1> {{$batch->name}} </h1>
            <small> এক্সাম ডিপার্টমেন্ট সমূহ </small>
          </div>
          <div class="home-services-area">
            <div class="row g-2 g-md-3 g-lg-4" style="--bs-gutter-x: 0.3rem !important;">
  
              @if($departments->count())
                @foreach($departments as $value)
                <div class="col-md-3 col-6 mb-1 dropdown bcs-jobs" id="home-services-area-one">
                  <a href="{{route('category.paper', [$type, $batch->id, $value->id])}}">
                    <div class="my-home-service premium-course-box">
                      <div class="my-home-service-icon">
                        <img src="/web/img/department.png" alt="" loading="lazy">
                      </div>
                      <div class="my-home-service-content">
                        <h3>{{$value->name}}</h3>
                      </div>
                    </div>
                  </a>
                </div>
                @endforeach
              @else
              <div class="card card-default">
                <div class="card-body text-center">
                  No Batch Available Departments
                </div>
              </div>
              @endif
            </div>
          </div>
        </div> <!-- /.row -->
        
        @include('partials.back-button')

      </div>
    </section> <!-- /.content -->

<script></script>
@endsection