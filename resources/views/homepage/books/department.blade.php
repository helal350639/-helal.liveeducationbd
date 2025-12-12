@php
$user = Auth::guard('student')->user();
@endphp

@extends('homepage')
@section('title', 'ডিপার্টমেন্ট সমূহ')
@section('content')

<style>
  .checkbox{padding-left: 25px}
  .panel ::-webkit-scrollbar{width: 5px;}
  ::-webkit-scrollbar-thumb{background-color: #ddd}
  .course-image{width:100%}
  .exam-package-area .package-exam-image img{
    height: 100%;
  }
</style>

    <!-- Main content -->
    <section class="our-service p-5">
      <div class="container">

        <div class="row">
          
          <div class="title-area text-center">
            <h1> {{$category->name}} </h1>
            <small> ডিপার্টমেন্ট সমূহ </small>
          </div>
          <div class="all-courses-area pt-5">
            <div class="row g-2 g-md-3 g-lg-4">
  
              @if(count($departments))
              @foreach($departments as $value)
              <div class="col-6 col-lg-3 mt-4">
                <div class="exam-package-area">
                  <div class="package-exam-image">
                    <a href="{{route('home.books', $value->id)}}">
                    <img src="{{ $value->banner? $value->banner : '/img/book-department.jpg'}}" alt="" loading="lazy" />
                    </a>
                  </div>
                  <div class="package-exam-content mx-2 mx-lg-3">
                    <div class="package-exam-title pt-3">
                      <h2>
                        <a href="{{route('home.books', $value->id)}}">{{$value->name}}</a>
                      </h2>
                    </div>
                    <div class="row button-and-price pb-3 pb-lg-4">
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
              @else
              <div class="card card-default">
                <div class="card-body text-center">
                  No Departments Available 
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>
        
        @include('partials.back-button')

      </div>
    </section> <!-- /.content -->

<script></script>
@endsection