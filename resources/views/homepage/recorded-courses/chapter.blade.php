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
            <h1> Chapters </h1>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 no-padding">
            <div class="all-courses-area">
              <div class="row justify-content-center g-3">

                @foreach($chapters as $value)

                  <div class="col-lg-8 col-md-10 col-sm-12">
                    <div class="border accordion-body-content d-flex justify-content-between align-items-center flex-wrap p-3 py-2">
                      <div class="text d-flex align-items-center list-content">
                        <div class="course_exam-icon">
                          <div class="circular-progress" title="" data-inner-circle-color="#def3ff" data-percentage="6" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)" style="background: #fff; font-size:22px; border:1px solid #ddd; color:#1CAB55">
                            <i class="fa fa-file-text"></i>
                          </div>
                        </div>
                        <p class="exam-title ps-1" style="width: 100%">
                          <span>{{$value->name}}</span>
                          <br>
                          <span style="font-size: 12px; font-weight: 700; color: green;"> Videos: {{$value->questions()->where('type', 'Video')->count()}}</span>
                        </p>
                      </div>
                      <div class="v-button d-flex flex-wrap gap-2">
                        @if($value->questions()->where('type', 'Video')->count())
                        <a href="{{route('category.recorded-video', ['Chapter', $value->id])}}" class="btn btn-sm btn-info text-white"> Videos </a>
                        @endif
                      </div>
                    </div>
                  </div>

                @endforeach
              </div>
            </div>
          </div>
        </div> <!-- /.row -->
        
        @include('partials.back-button')

      </div> <!-- /.container -->
    </section> <!-- /.our_courses -->
@endsection