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

@extends('student')
@section('title', 'Result')
@section('content')
<style>
  .checkbox{padding-left: 25px}
</style>

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          <!-- Content Header (Page header) -->
          <div class="title-area text-center">
            <h1> Results </h1>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 no-padding">
            <div class="all-courses-area">
              <div class="row g-2 g-md-3 g-lg-4">
    
                @if(count($batches))
                @foreach($batches as $value)
                <div class="col-6 col-lg-3 mt-4">
                  <div class="exam-package-area">
                    <div class="package-exam-image">
                      <a href="#">
                      <img src="{{ $value->banner? $value->banner : '/img/course.png'}}" alt="" loading="lazy" />
                      </a>
                    </div>
                    <div class="package-exam-content mx-2 mx-lg-3">
                      <div class="package-exam-title pt-3">
                        <h2><a href="#">{{substr($value->name, 0, 20)}}</a></h2>
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