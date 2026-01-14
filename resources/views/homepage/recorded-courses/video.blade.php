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
@section('title', 'Recorded Courses')
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
.exam-package-area{cursor: pointer;}

.flex-container {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    /* column-gap: 15px */
  }
  .flex-box{
    display: flex;
    flex-direction: column;
  }
</style>

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          <!-- Content Header (Page header) -->
          <div class="title-area text-center">
            <h1> {{$title}} </h1>
          </div>
        </div>

        <div class="row">
          <div class="all-courses-area">
            <div class="row g-2 g-md-3 g-lg-4 flex-container">
              @if($questions->count())
              @foreach($questions as $value)

              <div class="col-6 col-lg-3 mt-4">
                @if($value->url_type == 'Youtube')
                <div class="exam-package-area" onclick="showVideoModal('https://www.youtube.com/embed/{{$value->video}}?autoplay=1')">
                  <div class="package-exam-image">
                    <img src="https://img.youtube.com/vi/{{$value->video}}/maxresdefault.jpg" alt="" loading="lazy" />
                  </div>
                  <div class="package-exam-content mx-lg-3">
                    <div class="package-exam-title pt-3">
                      {!! $value->title !!}
                    </div>
                  </div>
                </div>
                @elseif($value->url_type == 'Google Drive')
                <div class="exam-package-area" onclick="showVideoModal('https://drive.google.com/file/d/{{$value->video}}/preview')">
                  <div class="package-exam-image">
                    <img src="{{$value->banner? $value->banner: '/web/img/video-icon.png'}}" alt="" loading="lazy" />
                  </div>
                  <div class="package-exam-content mx-lg-3">
                    <div class="package-exam-title pt-3">
                      {!! $value->title !!}
                    </div>
                  </div>
                </div>
                @endif
              </div>
              @endforeach
              @else
              <p class="text-center">Videos not published.</p>
              @endif
            </div>
          </div>
        </div> <!-- /.row -->

        @include('partials.back-button')

      </div> <!-- /.container -->
    </section> <!-- /.our_courses -->
@endsection