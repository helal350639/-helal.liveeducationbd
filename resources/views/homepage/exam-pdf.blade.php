@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$user = Auth::guard('student')->user();
$student = [];
if($user)
{
  $student = \App\Models\Student::find($user->id);
}

$now = now();
@endphp

@extends('homepage')
@section('title', 'এক্সম PDF')
@section('content')
<link rel="stylesheet" href="{{'/web/css/item-list.css'}}" />
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
#pdfLoading{
    position: fixed; top:0; left:0; bottom:0; width: 100%; height: 100%; background: rgba(0,0,0,0.1);
    text-align: center;
    padding-top: 20%
   }
</style>

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          <!-- Content Header (Page header) -->
          <div class="title-area text-center">
            <h1> {{$department->name}} </h1>
            <h5> {{$batch_name}} </h5>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 no-padding">
            <div class="all-courses-area">
              <div class="row justify-content-center g-3">

                @if($syllabuses->count())

                  @foreach($syllabuses as $key => $syllabus)

                    <div class="col-lg-8 col-md-10 col-sm-12">
                      <div class="border accordion-body-content d-flex justify-content-between align-items-center flex-wrap p-3 py-2">
                        <div class="text d-flex align-items-center list-content">
                          <div class="course_exam-icon">
                            <div class="circular-progress" title="Provided Answer" data-inner-circle-color="#def3ff" data-percentage="6" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)" style="background: #fff; font-size:25px; border:1px solid #ddd; color:#1CAB55">
                              @if($syllabus->open <= $now && $syllabus->close >= $now)
                              <i class="fa fa-file-pdf"></i>
                              @else
                              <i class="fa fa-file-pdf" style="color:red"></i>
                              @endif
                            </div>
                          </div>
                          <p class="exam-title ps-1" style="width: 100%">
                            <span>{{ $syllabus->name }}</span>
                            <span style="float:right;">{{ $source->dformat($syllabus->open) }} </span>
                            <br>
                            {{-- <span style="font-size: 12px; font-weight: 700; color: #1CAB55;"> (প্রশ্ন -{{ $syllabus->questions_count }}টি) </span> --}}
                          </p>
                        </div>
                        <div class="v-button d-flex flex-wrap gap-2">
                          @if($syllabus->status == 'Scheduled')
                          প্রকাশিত হবেঃ {{$source->dtformat($syllabus->datetime)}}
                          @else
                          <a href="{{route('student.syllabus', $syllabus->id)}}" class="btn btn-sm btn-info text-white"> পড়ুন </a>
                          <a href="{{route('students.syllabus.pdf', $syllabus->id)}}" class="btn btn-sm btn-warning"> ডাউনলোড </a>
                          {{-- <a class="btn btn-sm btn-warning"  data-url="{{route('students.syllabus.pdf', $syllabus->id)}}" onclick="pdfGenerate(this)"> ডাউনলোড </a> --}}
                          @endif
                        </div>
                      </div>
                    </div>

                  @endforeach
                
                @else

                <div class="col-12 text-center">
                  <h3>No Exam PDF Available</h3>
                </div>

                @endif

              </div>
            </div>
          </div>
        </div> <!-- /.row -->

        @include('partials.back-button')

      </div> <!-- /.container -->
    </section> <!-- /.our_courses -->

  
  <div id="pdfLoading" style="display:none;">
    <img src="/img/loading.gif" alt="">
  </div>

    <script>
    
    function pdfGenerate(e)
      {
        let preloader = document.getElementById('pdfLoading');
        preloader.style.display = 'block';
        
        let url = e.dataset.url;
    
        fetch(url, {
            method: "GET",
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
    
          preloader.style.display = 'none';
    
            if (data.status === 'success')
            {
                // Redirect to PDF file
                window.location.href = data.url;
            } else {
                alert('Failed to generate PDF');
            }
        })
        .catch(error => {
            document.getElementById('pdfLoading').style.display = 'none';
            alert('Something went wrong');
        });
      }
    </script>
@endsection