@extends('student')
@section('title', 'Course-Checkout')
@section('content')

<section class="our_courses py-5">
  <div class="container">
    <div class="row">
      <div class="card col-md-10 offset-md-1 py-5 text-center">
        <h3>{{$service->title}}</h3>
        <p>
          {!! $service->details !!}
        </p>
        <hr>
        <a href="#" onclick="window.history.back();"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>
</section>

@endsection