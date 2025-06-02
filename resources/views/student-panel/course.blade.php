@php
$user = Auth::guard('student')->user();
@endphp
@extends('student')
@section('title', 'কোর্স সমূহ')
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
            <h1>চলমান কোর্স সমূহ </h1>
          </div>
          <div class="all-courses-area">
            <div class="row g-2 g-md-3 g-lg-4">
  
              @if(count($courses))
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
                        <div class="package-exam-price">
                          <div class="package-exam-total-price text-muted">
                            <s class="text-muted">৳ 320</s>
                          </div>
                          <div class="package-exam-discount-price">৳ 200</div>
                        </div>
                      </div>
                      <div class="col text-end">
                        <div class="package-exam-button">
                          <div class="package-exam-details">
                            <a href="{{route('home.course.show', $value->id)}}">View Details </a>
                          </div>
                          <a href="{{route('home.course.show', $value->id)}}">
                            
                            @if(!empty($user) && $value->students()->where('id', $user->id)->first())
                            <button class="btn btn-default pull-right" disabled>Applied</button>
                            @else
                            <div class="custom-btn btn-12">
                              <span>ক্লিক করুন!</span><span>কোর্সটি কিনুন</span>
                            </div>
                            @endif
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
      </div>
    </section> <!-- /.content -->

<script></script>
@endsection