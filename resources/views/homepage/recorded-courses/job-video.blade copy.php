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
@section('title', 'Videos')
@section('content')
<link rel="stylesheet" href="{{'/web/css/item-list.css'}}" />

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          <!-- Content Header (Page header) -->
          <div class="title-area text-center">
            <h1> Videos </h1>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 no-padding">
            <div class="all-courses-area">
              <div class="row justify-content-center g-3">
                <div class="col-md-12">
                  <h3>Subject List</h3>
                </div>

                @foreach($subjects as $value)

                  <div class="col-lg-8 col-md-10 col-sm-12">
                    <div class="border accordion-body-content d-flex justify-content-between align-items-center flex-wrap p-3 py-2">
                      <div class="text d-flex align-items-center list-content">
                        <div class="course_exam-icon">
                          <div class="circular-progress" title="Provided Answer" data-inner-circle-color="#def3ff" data-percentage="6" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)" style="background: #fff; font-size:22px; border:1px solid #ddd; color:#1CAB55">
                            <i class="fa fa-book-open"></i>
                          </div>
                        </div>
                        <p class="exam-title ps-1" style="width: 100%">
                          <span>{{$value->name}}</span>
                          <br>
                          <span style="font-size: 12px; font-weight: 700; color: green;"> Chapters: {{count($value->chapters)}} Videos: {{count($value->videos?$value->videos:[])}}</span>
                        </p>
                      </div>
                      <div class="v-button d-flex flex-wrap gap-2">
                        <a href="{{route('category.recorded-video', ['Subject', $value->id])}}" class="btn btn-sm btn-info text-white"> Videos </a>
                      </div>
                    </div>
                  </div>

                @endforeach
              </div>
              <div class="row justify-content-center g-3">
                <div class="col-md-12">
                <br>
                <br>
                  <h3>Polytechnic Video List</h3>
                </div>

                @foreach($semesters as $value)

                  <div class="col-lg-8 col-md-10 col-sm-12">
                    <div class="border accordion-body-content d-flex justify-content-between align-items-center flex-wrap p-3 py-2">
                      <div class="text d-flex align-items-center list-content">
                        <div class="course_exam-icon">
                          <div class="circular-progress" title="Provided Answer" data-inner-circle-color="#def3ff" data-percentage="6" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)" style="background: #fff; font-size:22px; border:1px solid #ddd; color:#1CAB55">
                            <i class="fa fa-graduation-cap"></i>
                          </div>
                        </div>
                        <p class="exam-title ps-1" style="width: 100%">
                          <span>{{$value->name}}</span>
                          <br>
                          <span style="font-size: 12px; font-weight: 700; color: green;"> Subjects: {{count($value->subjects)}} Videos: {{count($value->videos?$value->videos:[])}}</span>
                        </p>
                      </div>
                      <div class="v-button d-flex flex-wrap gap-2">
                        <a href="{{route('category.recorded-video', ['Semester', $value->id])}}" class="btn btn-sm btn-info text-white"> Videos </a>
                      </div>
                    </div>
                  </div>

                @endforeach
              </div>
              <div class="row justify-content-center g-3">
                <div class="col-md-12">
                <br>
                <br>
                  <h3>Department List</h3>
                </div>

                @foreach($departments as $value)

                  <div class="col-lg-8 col-md-10 col-sm-12">
                    <div class="border accordion-body-content d-flex justify-content-between align-items-center flex-wrap p-3 py-2">
                      <div class="text d-flex align-items-center list-content">
                        <div class="course_exam-icon">
                          <div class="circular-progress" title="Provided Answer" data-inner-circle-color="#def3ff" data-percentage="6" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)" style="background: #fff; font-size:22px; border:1px solid #ddd; color:#1CAB55">
                            <i class="fa fa-cubes"></i>
                          </div>
                        </div>
                        <p class="exam-title ps-1" style="width: 100%">
                          <span>{{$value->name}}</span>
                          <br>
                          <span style="font-size: 12px; font-weight: 700; color: green;"> Subjects: {{count($value->subjects)}} Videos: {{count($value->videos?$value->videos:[])}}</span>
                        </p>
                      </div>
                      <div class="v-button d-flex flex-wrap gap-2">
                        <a href="{{route('category.recorded-video', ['Department', $value->id])}}" class="btn btn-sm btn-info text-white"> Videos </a>
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