@extends('student')
@section('title', 'Course-Checkout')
@section('content')

<section class="out_courses p-5">
  <div class="container">
    <div class="row">
    	<div class="" style="background:#fff; text-align:center;margin-top:5%">
        <div class="">
          <div style="text-align:center;max-width:350px; margin:0 auto">
            <img src="/web/img/404-error.avif" alt="" class="img-responsive">
          </div>
          <br>
          <h1> Your requested page cannot be found!</h1>
        	<h3 class="btn btn-default" onclick="window.history.back();">Back to Previous Page</h3>
            {{-- <h2>{{ $exception->getMessage() }}</h2> --}}
        </div>
       </div>
    </div>
  </div>
</section>

@endsection