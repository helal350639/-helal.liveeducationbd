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
@section('title', 'এক্সাম রুটিন')
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
</style>

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          <!-- Content Header (Page header) -->
          <div class="title-area text-center">
            <h1> {{$batch->name}} </h1>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 no-padding">
            <div class="all-courses-area">
              <div class="row justify-content-center g-3">

                @if($routine && $routine_rows->count())

                <p>{!! $routine->header !!}</p>

                <table class="table table-bordered">
                  <tr>
                    <th>SL No.</th>
                    <th>Exam Name/No</th>
                
                    @foreach($subjects as $subject)
                    <th>{{$subject->name}}</th>
                    @endforeach
                
                    @foreach($departments as $department)
                    <th>{{$department->name}}</th>
                    @endforeach
                
                    <th>Date & Time</th>
                  </tr>
                
                  @foreach($routine_rows as $key => $item)
                  <tr>
                    <td>{{$key+1}}</td>
                    <td>{{$item->paper_name}}</td>
                
                    @foreach($item->subjects as $subject)
                    <td>{!!$subject->chapter!!}</td>
                    @endforeach
                
                    @foreach($item->departments as $department)
                    <td>{!!$department->details!!}</td>
                    @endforeach
                
                    <td>{{$source->dtformat($item->exam_at)}}</td>
                  </tr>
                  @endforeach
                
                </table>
                
                @elseif($routine->pdf)
                  <div class="card card-default">
                    <object data="{{$routine->pdf}}" type="application/pdf" width="100%" height="600px">
                      <p>Your browser does not support PDFs.
                        <a href="path/to/file.pdf">Download the PDF</a>.
                      </p>
                    </object>
                  </div>
                @else

                <div class="card card-default">
                  <div class="card-body text-center">
                    Routine Not Available
                  </div>
                </div>
                @endif

              </div>
            </div>
          </div>
        </div> <!-- /.row -->

        @include('partials.back-button')

      </div> <!-- /.container -->
    </section> <!-- /.our_courses -->
@endsection