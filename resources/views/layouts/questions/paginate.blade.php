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
@endphp
<style>
.panel .panel-heading{background-color:#fff; text-align: justify}
.panel-body{padding-top:5px}
.panel-body label{white-space: normal;}
.tools{text-align: right}
.check{float: left; height: 20px; width:20px; margin-right: 10px !important; }
.correct{font-weight: bold; font-size:16px; color:green}
</style>
<div class="box-header">
  <div class="pagination-sm no-margin pull-right">
    {!! $questions->links() !!}
  </div>
  
  <h3 class="box-title">
    Questions ({{$questions->total()}})

    @if(!is_null(Session::get('_paper')))
      <br>
      <label for="checkAll" style="margin-top: 15px; margin-left:15px; font-weight:normal">
        <input type="checkbox" onclick="checkAll(this)" style="width:18px; height:18px" id="checkAll" /> Check All
      </label>
    @endif
  </h3>
</div><!-- /.box-header -->
<div class="box-body">
  @foreach($questions as $key => $value)
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <label style="display: block; font-size:16px">
          <input type="checkbox" class="check" value="{{$value->id}}" onclick="count(this)" {{in_array($value->id, $xqs) ? 'checked':''}}>
          <span style="float: left; color:#a00"> প্রশ্ন নং- {{$key + $questions->firstItem()}} : &nbsp; </span>{!!$value->title!!}</label>
      </div>
      <div class="panel-body">
        <div class="tools">
          <a href="{{route('question.show', $value->id)}}" class="btn btn-info btn-xs"><i class="fa fa-file"></i></a>
          <a href="{{route('question.edit', $value->id)}}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
          <button class="btn btn-danger btn-xs" onclick="del(this)" value="{{$value->id}}"><i class="fa fa-trash"></i></button>
        </div>
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
          <button class="btn btn-info" onclick="showHideAnswer(this)">Show Answer & Explanation: <i class="fa fa-chevron-down"></i></button>
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
      </div>
    </div>
  </div>
  @endforeach
</div><!-- /.box-body -->
<div class="box-footer clearfix">
  <div class="pagination-sm no-margin pull-right">
    {!! $questions->links() !!}
  </div>
</div><!-- /.box-footer -->