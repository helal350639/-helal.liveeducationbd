@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$correct = '';
$format = ['a)', 'b)', 'c)', 'd)', 'e)'];

$xqs = [];
if(!is_null(Session::get('_paper')))
{
  $paper = Session::get('_paper');
  $xqs = $paper->questions()->pluck('id')->toArray();
}
$book = $subject;
@endphp
@extends('student')
@section('title', 'Read Book')
@section('content')
<style>
.panel .panel-heading{background-color:#fff; text-align: justify}
.panel-body{padding-top:5px}
.panel-body label{white-space: normal;}
.tools{text-align: right}
.check{float: left; height: 20px; width:20px; margin-right: 10px !important; }
.correct{font-weight: bold; font-size:16px; color:green}

.hide{display: none}
body{background:#eee}
ul li{list-style: none}
.front_cover, .back_cover{
  width: 100%
}
.index-page{
  width: 100%;
  max-width: 1240px;
  max-height: 1754px;
  background:#fff;
  margin: 15px 0;
  text-align: center;
  padding: 35px 15px;
}
.index-list{
  text-align: left;
  margin-top: 20px;
  margin-left: 10%;
}
.index-list li{
  margin: 25px 0;
  list-style: square;
}
.index-footer{
  /* position: absolute; */
  margin-bottom:10px;
  text-align: center
}
</style>
<div class="col-md-8 offset-md-2" style="margin: 50px auto; ">
<div class="box-header">

  <img class="front_cover" src="{{$subject->front_cover}}" alt="">
  <div class="index-page">
    <h1>{{$book->name}}</h1>
    <h3>Index</h3>
    <ul class="index-list">
      @foreach($chapters as $chapter)
      <li>{{$chapter->name}}</li>
      @endforeach
    </ul>
    <div class="index-footer">
      Publisher: <b>Live Education BD</b>
    </div>
  </div>
  
  <div class="pagination-sm no-margin pull-right">
    {!! $questions->links() !!}
  </div>
  <hr>
  
  <h5 class="box-title">
    Total Questions ({{$questions->total()}})
  </h5>
</div><!-- /.box-header -->
<div class="box-body">
  @foreach($questions as $key => $value)
  <div class="col-md-12" style="background:#fff;">
    <div class="panel panel-default" style="padding:15px; margin:10px">
      <div class="panel-heading">
        <label style="display: block; font-size:16px">
          <input type="checkbox" class="check" value="{{$value->id}}" onclick="count(this)" {{in_array($value->id, $xqs) ? 'checked':''}}>
          <span style="float: left; color:#a00"> প্রশ্ন নং- {{$key + $questions->firstItem()}} : &nbsp; </span>{!!$value->title!!}</label>
      </div>
      <div class="panel-body">
        @foreach($value->getlabels as $val)
        <label class="label label-default">{{$val->label}}</label>
        @endforeach

        @if($value->type == 'MCQ')
        <ul class="list" style="padding-left:10px; padding-top:15px">
          @foreach($value->mcqitems as $k => $val)
            @if($k <= 4)
              @php
              if($val->correct_answer)
              {
                $correct = $format[$k].' '.$val->item;
              }
              @endphp
              <li>
                <span class="{{$val->correct_answer ? 'correct' : ''}}">{{$format[$k]}} {!!$val->item!!}</span>
              </li>
            @endif
          @endforeach
        </ul>
        <div class="col-md-12 no-padding">
          <button class="btn btn-success" onclick="showHideAnswer(this)">
            Show Answer & Explanation: <i class="fa fa-chevron-down"></i>
          </button>
          <button class="btn btn-info">
            <i class="fa fa-comment-o"></i>
            Comments
          </button>
        </div>
        <div class="col-md-12 no-padding hide">
          <hr>
          <p class="text-success"><b>Answer:</b> {!! $correct !!}</p>
          <p><b>Explanation:</b>{!!$value->explanation!!}</p>
        </div>
        @php
        $correct = '';
        @endphp

        @elseif($value->type == 'Written')
        <p style="padding-top:15px;"><b>Answer:</b> {!! $value->answer !!}</p>
        <p>
          @foreach($value->answerfiles as $val)
          @if(substr($val->file, -3) == 'pdf')
          <a target="_blank" href="{{$val->file}}"><i class="fa fa-file"></i></a>
          @else
          <a target="_blank" href="{{$val->file}}"><img src="{{$val->file}}" alt="" width="100"></a>
            &nbsp; 
          @endif
          @endforeach
        </p>

        @elseif($value->type == 'Video')
        <p>{!! $value->video !!}</p>
        <div class="col-md-12 no-padding">
          <button class="btn btn-info" onclick="showHideAnswer(this)">Show Explanation: <i class="fa fa-chevron-down"></i></button>
        </div>
        <div class="col-md-12 no-padding hide">
          <hr>
          <p><b>Explanation:</b>{!!$value->explanation!!}</p>
        </div>
        
        @endif
        <hr>
      </div>
    </div>
  </div>
  @endforeach
</div><!-- /.box-body -->
<div class="box-footer clearfix">
  <div class="pagination-sm no-margin pull-right">
    {!! $questions->links() !!}
  </div>


  <img class="back_cover" src="{{$subject->back_cover}}" alt="">
</div><!-- /.box-footer -->
</div>
<script>
  function showHideAnswer(e)
  {
    let item = e.parentNode.nextElementSibling;
    item.classList.toggle('hide');
  }
</script>
@endsection

