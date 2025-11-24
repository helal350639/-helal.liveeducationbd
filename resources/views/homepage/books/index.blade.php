@php
$user = Auth::guard('student')->user();
@endphp
@extends('student')
@section('title', 'বই সমূহ')
@section('content')
{{-- <script src='https://www.google.com/recaptcha/api.js' async defer></script> --}}
<style>
  .checkbox{padding-left: 25px}
  .panel ::-webkit-scrollbar{width: 5px;}
  ::-webkit-scrollbar-thumb{background-color: #ddd}
  .course-image{width:100%}
</style>

    <!-- Content Header (Page header) -->

    <!-- Main content -->
    <section class="our_courses" style="background: #eef">
      <div class="container">
        <div class="row">
          
          <div class="title-area text-center">
            <h1>প্রিমিয়াম বই সমূহ </h1>
          </div>
          <div class="all-courses-area">
            <div class="row g-2 g-md-3 g-lg-4">
  
              @if(count($books))
              @foreach($books as $value)
              <div class="col-6 col-lg-3 mt-4">
                <div class="exam-package-area">
                  <div class="package-exam-image">
                    <a href="{{route('home.book.read', $value->id)}}">
                    <img src="{{ $value->banner? $value->banner : '/img/book-cover.png'}}" alt="" loading="lazy" />
                    </a>
                  </div>
                  <div class="package-exam-content mx-2 mx-lg-3">
                    <div class="package-exam-title pt-3">
                      <h2>
                        <a href="{{route('home.book.read', $value->id)}}">{{substr($value->name, 0, 20)}}</a>
                      </h2>
                    </div>
                    <div class="row button-and-price pb-3 pb-lg-4">
                      {{-- <div class="col">
                        <div class="package-exam-price">
                          <div class="package-exam-total-price text-muted">
                            <s class="text-muted">৳ {{number_format($value->price)}}</s>
                          </div>
                          <div class="package-exam-discount-price">৳ {{number_format($value->net_price)}}</div>
                        </div>
                      </div> --}}
                      <div class="col text-end">
                        <div class="package-exam-button">
                          {{-- <div class="package-exam-details">
                            <a href="{{route('home.book.read', $value->id)}}">View Details </a>
                          </div> --}}
                          <a href="{{route('home.book.read', $value->id)}}">
                            <div class="custom-btn btn-12">
                              <span>ক্লিক করুন!</span><span>পড়ুন</span>
                            </div>
                          </a>
                        </div>
                      </div>
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

        @include('partials.back-button')

      </div>
    </section> <!-- /.content -->

<script></script>
@endsection