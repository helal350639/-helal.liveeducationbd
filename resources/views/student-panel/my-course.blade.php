@php
$student = [];
$departments_ids = [];
$user = Auth::guard('student')->user();
if($user)
{
  $student = \App\Models\Student::find($user->id);
  $departments_ids = $student->departments->pluck('id')->toArray();
}
// dd($student->departments->pluck('id')->toArray());
@endphp

@extends('student')
@section('title', 'আমার কোর্স সমূহ')
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
            <h1> আমার কোর্স সমূহ </h1>
          </div>
        </div>
      @if(count($courses))
      
      <div class="all-courses-area">
        <div class="row g-2 g-md-3 g-lg-4">

          @foreach($courses as $value)
          
          <div class="col-6 col-lg-3 mt-4">
            <div class="exam-package-area">
              <div class="package-exam-image">
                <a href="{{route('home.course.show', $value->id)}}">
                <img src="{{ $value->banner? $value->banner : '/img/course.png'}}" alt="" loading="lazy" />
                </a>
              </div>
              <div class="package-exam-content mx-2 mx-lg-3">
                <div class="package-exam-title pt-3">
                  <h2><a href="{{route('home.course.show', $value->id)}}">{{substr($value->name, 0, 20)}}</a></h2>
                </div>
                <div class="row button-and-price pb-3 pb-lg-4">
                  <div class="col">
                    <div class="package-exam-rating">
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="far fa-star"></i>
                    </div>
                  </div>
                  <div class="col text-end">
                    <div class="package-exam-button">
                      <div class="package-exam-details">
                        <a href="{{route('home.course.show', $value->id)}}">View Details </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          
          @endforeach
        </div>
      </div>
      @else
      <div class="panel panel-default">
        <div class="panel-body">
          আপনি এখনো কোন কোর্স ক্রয় করেননি। কোর্স ক্রয় করার জন্যে <a href="{{route('home.course')}}">চলমান কোর্স সমূহ</a> পেজে যান।
        </div>
      </div>
      @endif
      
    </div>
    </section> <!-- /.content -->
@endsection