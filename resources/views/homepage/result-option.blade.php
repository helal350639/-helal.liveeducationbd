@php
use \App\Http\Controllers\SourceCtrl;
$source = new SourceCtrl;

$user = Auth::guard('student')->user();
@endphp

@extends('homepage')
@section('title', 'Result Option')
@section('content')

  <style>
    .checkbox{padding-left: 25px}
    .panel ::-webkit-scrollbar{width: 5px;}
    ::-webkit-scrollbar-thumb{background-color: #ddd}
    .course-image{width:100%}
  </style>

<style>
  /* Custom CSS for mobile devices */
  @media (max-width: 575.98px) {
  
    .video-container h5 {
      font-size: 14px; /* Smaller font size for mobile */
      text-align: center;
    }
  
    .playButton svg {
      width: 30px; /* Smaller play button for mobile */
      height: 30px;
    }
  }
  
  /* Custom CSS for tablets and larger devices */
  @media (min-width: 576px) {
  
    .video-container h5 {
        font-size: 18px;
        text-align: center;
    }
  
    .playButton svg {
        width: 45px; /* Larger play button for tablets and desktops */
        height: 45px;
    }
  }
  
  @media (min-width: 481px) {
    .list-content{
      width: 50%;
    }
  }
  </style>

    <!-- Main content -->
    <section class="our-service p-5">
      <div class="container">
        <div class="row">
          
          <div class="title-area text-center">
            <h1> Results </h1>
          </div>

          @if(isset($papers) && isset($user) && $papers->count())

          <div class="row">
            <div class="col-md-12 no-padding">
              <div class="all-courses-area">
                <h4 class="text-center">Recent Exams Result</h4>
                <div class="row justify-content-center g-3">
  
                    @foreach($papers as $key => $paper)
  
                      <div class="col-lg-8 col-md-10 col-sm-12" title="{{$paper->open}}">
                        <div class="border accordion-body-content d-flex justify-content-between align-items-center flex-wrap p-3 py-2">
                          <div class="text d-flex align-items-center list-content">
                            <div class="course_exam-icon">
                              {{-- <div class="circular-progress" title="Provided Answer" data-inner-circle-color="#def3ff" data-percentage="6" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)" style="background: #fff; font-size:25px; border:1px solid #ddd; color:#1CAB55">
                                @if($paper->open <= date('Y-m-d H:i:s') && $paper->close > date('Y-m-d H:i:s'))
                                <i class="fa fa-unlock"></i>
                                @else
                                <i class="fa fa-lock" style="color:red"></i>
                                @endif
                              </div> --}}
                            </div>
                            <p class="exam-title ps-1" style="width: 100%">
                              <span>{{ $paper->name }}</span>
                              {{-- <span style="float:right;">{{ $source->dformat($paper->result_at) }}</span> --}}
                              <br>
                              <span style="font-size: 12px; font-weight: 700; color: #1CAB55;"> 
                                {{-- (প্রশ্ন -00টি, সময়-00মিনিট) --}}
                               </span>
                            </p>
                          </div>
                          <div class="v-button d-flex flex-wrap gap-2">
                            @if(date('Y-m-d H:i:s', strtotime($paper->open.' +12 hours')) < date('Y-m-d H:i:s'))
                            <a href="{{route('students.solution', $paper->id)}}" class="btn btn-sm btn-success"> সল্যুশন</a>
                            @endif
  
                            @if($student && $paper->exams()->where('student_id', $student->id)->first())
  
                            <a href="{{route('students.exam.paper', $paper->id)}}" class="btn btn-sm btn-primary">এক্সাম পেপার </a>
  
                            <a href="{{route('students.result', $paper->id)}}" class="btn btn-sm btn-warning"> ফলাফল</a>
  
                            @endif
                            
                            {{-- {{route('home.results', $paper->id)}} --}}
                            {{-- <a href="{{route('home.coming-soon')}}" class="btn btn-sm btn-info text-white"> মেধাক্রম </a> --}}
                            @if($paper->close < date('Y-m-d H:i:s'))
                            <p></p>
                            @endif
                          </div>
                        </div>
                      </div>
  
                    @endforeach
  
                </div>
              </div>
            </div>
          </div> <!-- /.row -->
          @endif

          <div class="home-services-area">

            <div class="row g-2 g-md-3 g-lg-4" style="--bs-gutter-x: 0.3rem !important;">
              <div class="col-md-8 offset-md-2">
                
                <div class="row">
                  <div class="col-md-6 col-12 mb-1 dropdown bcs-jobs" id="home-services-area-one">
                    <a href="{{route('category.batches', 'Premium Results')}}">
                      <div class="my-home-service premium-course-box">
                        <div class="my-home-service-icon">
                          <img src="/web/img/premium.png" alt="" loading="lazy">
                        </div>
                        <div class="my-home-service-content">
                          <h3>Premium Batches</h3>
                        </div>
                      </div>
                    </a>
                  </div>
                  
                  <div class="col-md-6 col-12 mb-1 dropdown bcs-jobs" id="home-services-area-one">
                    <a href="{{route('category.batches', 'Free Results')}}">
                      <div class="my-home-service premium-course-box">
                        <div class="my-home-service-icon">
                          <img src="/web/img/free.jpg" alt="" loading="lazy">
                        </div>
                        <div class="my-home-service-content">
                          <h3>Free Batches</h3>
                        </div>
                      </div>
                    </a>
                  </div>
                </div>
              </div>              
              

            </div>
          </div>
        </div> <!-- /.row -->
        
        @include('partials.back-button')

      </div>
    </section> <!-- /.content -->

<script></script>
@endsection