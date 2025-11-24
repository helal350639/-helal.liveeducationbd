@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$user = Auth::guard('student')->user();
$value = $paper;
$student = [];
if($user)
{
  $student = \App\Models\Student::find($user->id);
}
@endphp

@extends('student')
@section('title', 'পরীক্ষা সমূহ')
@section('content')
<style>
  .checkbox{padding-left: 25px}
</style>

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          <div class="content-header title-area text-center">
            <h1> পরীক্ষা </h1>
          </div>
        </div>
        <div class="col-md-6 offset-md-3">
          @if( $paper->permit == 'Batch' && $student->batches->find($paper->batch_id) || $paper->permit == 'Department' && $student->departments->find($paper->department_id) || $paper->permit == 'Group' && $student->groups->find($paper->group_id) || $paper->permit == 'Every One')
          
            @if($paper->close && $paper->close < date('Y-m-d H:i:s'))
            <div class="card card-warning">
              <div class="card-heading p-3">
                <b>Exam No. {{$value->name}}</b>
              </div>
              <div class="card-body">
                <p class="text-danger">পরীক্ষার সময় শেষ...</p>
              </div>
              <div class="card-footer">
                {{-- <a class="btn btn-danger pull-right" href="{{route('students.exam')}}"><i class="fa fa-arrow-left"></i> এক্সাম পেজ এ ফিরে যান</a> --}}
                <div class="clearfix"></div>
              </div>
            </div>

            @elseif($value->status == 'Scheduled')
            <div class="card card-warning">
              <div class="card-heading p-3">
                <b>Exam No. {{$value->name}}</b>
              </div>
              <div class="card-body">
                <p> পরীক্ষা সময় <b>{{ $source->dtformat($value->open)}}</b></p>
                <b><span id="timer" style="font-size:20px; font-wieght:bold"></span></b> সময় পরে পরীক্ষা শুরু হবে
            </div>
              <div class="card-footer">
                {{-- <a class="btn btn-info pull-right" href="{{route('students.exam', $value->id)}}">Back</a> --}}
                <div class="clearfix"></div>
              </div>
            </div>

            @elseif($value->status == 'Published')
            <div class="card card-warning">
              <div class="card-heading p-3">
                <b>Exam No. {{$value->name}}</b>
              </div>
              <div class="card-body">
                <p>{!! $value->details !!}</p>
              </div>
              <div class="card-footer">
                <a class="btn btn-info pull-right" href="{{route('students.exam.show', $value->id)}}">শুরু করুন</a>
                <div class="clearfix"></div>
              </div>
            </div>
            @endif
          @else
            <div class="card card-danger">
              <div class="card-heading p-3">
                <p>This exam is not match with you!</p>
                {{-- <a href="{{route('homepage')}}">Back</a> --}}
              </div>
            </div>
          @endif
        </div>
      </div>
        
      @include('partials.back-button')

    </section> <!-- /.content -->
    <div class="preloader"></div>
    {{-- <div id="timer"></div> --}}

<script>
  let timer = document.getElementById('timer');
  // console.log(timer == null);
  if(timer != null)
  {
    // Set the date we're counting down to
    // var countDownDate = new Date("Oct 27, 2024 12:47:25").getTime();
    var countDownDate = new Date("{{$source->dtcformat($value->open)}}").getTime();
    // var countDownDate = new Date().getTime();
    
    // Update the count down every 1 second
    var x = setInterval(function() {
    
      // Get today's date and time
      var now = new Date().getTime();
      // var now = new Date("{{$source->dtcformat($paper->close)}}").getTime();
        
      // Find the distance between now and the count down date
      var distance = countDownDate - now;
        
      // Time calculations for days, hours, minutes and seconds
      var days = Math.floor(distance / (1000 * 60 * 60 * 24));
      var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
      // Output the result in an element with id="demo"
      let d = h = m = s = '';
      if(days != 0)
      {
        d = days+"Days ";
      }
      // document.getElementById("timer").innerHTML = hours + "h "
      // + minutes + "m " + seconds + "s ";
      document.getElementById("timer").innerHTML = d + " " + hours + " : "
      + minutes + " : " + seconds + " ";

      // console.log(distance);
        
      // If the count down is over, write some text 
      if (distance < 60000 && distance > 59000) {
        // alert('10 Seconds Left!');
        document.getElementById('timer').style.color='red';

      }
      if (distance < 0)
      {      
        // submit the form automatically
        // submitExam();

        clearInterval(x);
        document.getElementById("timer").innerHTML = '<span style="color:#d00">অনুগ্রহ পূর্বক অপেক্ষা করুন...</span>';
        updatePaper();
      }
    }, 1000);
  }

  // update paper by ajax
  function updatePaper()
  {
    $.ajax({
      type : 'GET',
      url : '{{route("paper.update.ajax", $value->id)}}',
      success : function(data){
        // console.log(data);
        if(data.success == true)
        {
          window.location.href = '{{route("students.instruction", $value->id)}}';
        }
      },
      error : function(data){
        console.log(data);
      }
    });
  }
</script>
@endsection