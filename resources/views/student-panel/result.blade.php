@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$user = Auth::guard('student')->user();
$percentage = $score = 0;
$exam_index = $exams->count();

function position($user_mark, $total_marks)
{
  $user_mark = number_format($user_mark);
  
  if($user_mark > 0)
  {
  $arr = range(1, $total_marks);
  rsort($arr);
  $rank = array_search($user_mark, $arr)+1;
  return $rank;
  }
  return 0;
}
@endphp

@extends('student')
@section('title', 'Course')
@section('content')
{{-- <script src='https://www.google.com/recaptcha/api.js' async defer></script> --}}
<style>
  .checkbox{padding-left: 25px}
  .panel ::-webkit-scrollbar{width: 5px;}
  ::-webkit-scrollbar-thumb{background-color: #ddd}
  .mcqitems{list-style: none; padding-left: 10px}
  .mcqitems li{padding:5px; margin: 10px; max-width: 300px;}
  .mcqitems li label{ display: bl/ock;border-radius: 15px;border:1px solid #ddd; padding: 5px 15px; color: #444; cursor: pointer; font-weight: normal; min-width: 300px;}
  .mcqitems li input[type="radio"]{width: 20px;height: 20px; margin-right: 5px; padding-top:5px}
  .mcqitems li span{vertical-align: top}
  .banner{margin-top:15px}
  .banner img{width:100%}
  .timer{font-weight: bold; font-size: 20px; text-align: center; color:#666; border:2px solid; border-radius:10px}
  .time span{ border:2px solid #444; color:#ddd}
  .sticky{position: fixed; top:50px; left: 0; right: 0; z-index: 999999;}
  #fixed{text-align: center}
  .selected{background:lightblue;}
  .result table th{text-align: right;}
  .panel{background-color: #eee}
  .items{background: #fff; text-align: center; color:#000; margin:5px; padding:10px}
</style>

    <!-- Content Header (Page header) -->
    <section class="content-header"></section>

    <!-- Main content -->
    <section class="content no-padding" id="content">
      @if(isset($result['result']))
      <div class="row">
        @foreach($exams as $key => $value)
        @php
        $user_mark = number_format($value->mark);
        $marks = $paper->questions->count() * $paper->mark;
        @endphp
        <div class="col-md-6 col-md-offset-3 result no-padding" id="result">
          <div class="panel">
            <div style="text-align: center">
              <img src="/img/logo.png" alt="" style="max-width:100px">
            </div>
            @if($paper->banner)
            <img src="{{$paper->banner}}" alt="" style="width:100%">
            @endif
            {!! $paper->header !!}
            <p style="text-align: center">
              <b><span style="font-size:18px"> <i class="fa fa-user"></i> {{$user->name}}</span></b>
              <br>
              {{$paper->name}} - {{$value->paper->course?$value->paper->course->name:''}} - {{$value->paper->department?$value->paper->department->name:''}}
              <br>
              {{$source->dformat($value->start_at)}}
            </p>
            <div class="col-xs-6 no-padding">
              <div class="items">
                মোট পরীক্ষার্থীর সংখ্যা<br>
                <i class="fa fa-users"></i> {{$candidates}}
              </div>
            </div>
            <div class="col-xs-6 no-padding">
              <div class="items">
                উত্তীর্ণ পরীক্ষার্থীর সংখ্যা<br>
                <i class="fa fa-check-circle" style="color:green"></i> {{$passed}}
              </div>
            </div>
            <div class="col-xs-6 no-padding">
              <div class="items">
                আপনার মার্কস<br>
                <i class="fa fa-"></i> {{$value->correct - ($value->wrong * $paper->minus)}}
              </div>
            </div>
            <div class="col-xs-6 no-padding">
              <div class="items">
                কর্তনকৃত মার্কস<br>
                <i class="fa fa-close" style="color:brown"></i> {{$value->wrong * $paper->minus}}
              </div>
            </div>
            <div class="col-xs-6 no-padding">
              <div class="items">
                আপনার ফলাফল<br>
                @if($percentage > 80)
                <label class="label label-success">Extra Ordinary</label>
                @elseif($percentage > 60)
                <label class="label label-info">Very Good</label>
                @elseif($percentage > 40)
                <label class="label label-warning">Good</label>
                @elseif($percentage < 40)
                <label class="label label-danger">Learner</label>
                @endif
              </div>
            </div>
            <div class="col-xs-6 no-padding">
              <div class="items">
                আপনার অবস্থান<br>
                {{position($user_mark, $marks)}}
              </div>
            </div>
            <div class="panel-body">
              <table class="table table-bordered">
                <tr>
                  <th colspan="2" style="text-align: center;font-size:18px">Result Summary</th>
                </tr>
                <tr>
                  <td>Exam No.</td>
                  <th>{{$exam_index }}</th>
                </tr>
                <tr>
                  <td>Start Time</td>
                  <th>{{$source->dtformat($value->start_at)}}</th>
                </tr>
                <tr>
                  <td>End Time</td>
                  <th>{{$source->dtformat($value->end_at)}}</th>
                </tr>
                <tr>
                  <td>Answered</td>
                  <th id="answer">{{$value->answer}}</th>
                </tr>
                <tr>
                  <td>Correct</td>
                  <th id="correct">{{$value->correct}}</th>
                </tr>
                <tr>
                  <td>Wrong</td>
                  <th id="wrong">{{$value->wrong}}</th>
                </tr>
                <tr>
                  <td>No Answered</td>
                  <th id="no_answer">{{$value->no_answer}}</th>
                </tr>
                <tr>
                  <td>Mark for per right answer</td>
                  <th>{{$value->paper->mark}}</th>
                </tr>
                <tr>
                  <td>Negative Mark for per wrong answer</td>
                  <th>{{$value->paper->minus}}</th>
                </tr>
              </table>
            </div>
            <div class="panel-footer">
              <p style="text-align: center">
                <a href="{{route('students.exam')}}" class="btn btn-warning pull-left"><i class="fa fa-arrow-left"></i> Back</a>
                {{-- <a href="{{route('students.solution', $value->id)}}" class="btn btn-info pull-right"><i class="fa fa-file-o"></i> Your Exam Paper</a> --}}
              </p>
              <div class="clearfix"></div>
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
          @php
          $exam_index --;
          @endphp
        @endforeach
      </div>
      @elseif(isset($result['message']))
      <div class="col-md-6 col-md-offset-3 panel panel-default">
        <div class="panel-body" style="text-align: center">
          <p>{{$paper->result_message}}</p>
          <hr>
          <p>
            <a href="{{route('students.exam')}}" class="btn btn-warning"> <i class="fa fa-arrow-left"></i> Back</a>
            <a href="{{route('students.complain')}}" class="btn btn-info">আপনার মতামত/অভিযোগ দিন</a>
          </p>
        </div>
      </div>
      @else
      <div class="col-md-6 col-md-offset-3 panel panel-default">
        <div class="panel-body" style="text-align: center">
          <p>{{$paper->message}}</p>
          <hr>
          <p><a href="/">Back</a></p>
        </div>
      </div>
      @endif
    </section> <!-- /.content -->
@endsection

@section('scripts')
<script>
  //
</script>
@endsection