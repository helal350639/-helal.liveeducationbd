@php
$user = Auth::guard('student')->user();
@endphp

@extends('student')
@section('title', 'কোর্স সমূহ')
@section('content')

<style>
  .checkbox{padding-left: 25px}
  .panel ::-webkit-scrollbar{width: 5px;}
  ::-webkit-scrollbar-thumb{background-color: #ddd}
  .course-image{width:100%}
</style>

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          
          <div class="title-area text-center">
            <h1>All Semisters </h1>
            <small>Polytechnic Study</small>
          </div>
          <div class="all-courses-area">
            <div class="row g-2 g-md-3 g-lg-4">
  
              @if(count($semesters))
              @foreach($semesters as $value)
              <div class="col-6 col-lg-3 mt-4">
                <div class="exam-package-area">
                  <div class="package-exam-image p-3">
                    <a href="{{route('category.departments', ['Polytechnic', $value->id])}}">
                    <img src="{{ $value->banner? $value->banner : '/web/img/semester.png'}}" alt="" loading="lazy" />
                    </a>
                  </div>
                  <div class="package-exam-content mx-2 mx-lg-3">
                    <div class="package-exam-title pt-3">
                      <h2 class="text-center"><a href="{{route('category.departments', ['Polytechnic', $value->id])}}">{{substr($value->name, 0, 20)}}</a></h2>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
              @else
              <div class="panel panel-default">
                <div class="panel-body">
                  No Course Available
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