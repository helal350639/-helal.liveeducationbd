@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$user = Auth::guard('student')->user();
$student = [];
if($user)
{
  $student = \App\Models\Student::find($user->id);
}
@endphp

@extends('homepage')
@section('title', 'নিয়োগ বিজ্ঞপ্তি')
@section('content')
<link rel="stylesheet" href="{{'/web/css/item-list.css'}}" />

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          <!-- Content Header (Page header) -->
          <div class="title-area text-center">
            <h1> নিয়োগ বিজ্ঞপ্তি </h1>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 no-padding">
            <div class="all-courses-area">
              <div class="row justify-content-center g-3">

                @foreach($circulars as $value)

                  <div class="col-lg-8 col-md-10 col-sm-12">
                    <div class="border accordion-body-content d-flex justify-content-between align-items-center flex-wrap p-3 py-2">
                      <div class="text d-flex align-items-center list-content">
                        <div class="course_exam-icon">
                          <div class="circular-progress" title="Provided Answer" data-inner-circle-color="#def3ff" data-percentage="6" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)" style="background: #fff; font-size:22px; border:1px solid #ddd; color:#1CAB55">
                            <i class="fa fa-info-circle"></i>
                          </div>
                        </div>
                        <p class="exam-title ps-1" style="width: 100%">
                          <span>{{$value->title}}</span>
                          <span style="float:right;">{{$source->dformat($value->published_at)}} </span>
                          <br>
                          <span style="font-size: 12px; font-weight: 700; color: red;"> Deadline: {{$source->dformat($value->end_at)}} </span>
                        </p>
                      </div>
                      <div class="v-button d-flex flex-wrap gap-2">
                        <a href="{{route('home.circular.show', $value->id)}}" class="btn btn-sm btn-info text-white"> বিস্তারিত </a>
                      </div>
                    </div>
                  </div>

                @endforeach
              </div>
            </div>
          </div>
        </div> <!-- /.row -->

        <div class="row p-5">
          <div class="container text-center">
            <p><button class="btn btn-warning btn-outline" onclick="window.history.back();"><i class="fa fa-long-arrow-left"></i> Back</button></p>
          </div>
        </div>

      </div> <!-- /.container -->
    </section> <!-- /.our_courses -->
@endsection