@php
use \App\Http\Controllers\SourceCtrl;
$source = new SourceCtrl;
$correct = '';
$format = ['a)', 'b)', 'c)', 'd)', 'e)'];
$xqs = [];
if (!is_null(Session::get('_paper'))) {
    $paper = Session::get('_paper');
    $xqs = $paper->questions()->pluck('id')->toArray();
}
@endphp
<style>
.panel .panel-heading{background-color:#fff; text-align:justify}
.panel-body{padding-top:5px}
.panel-body label{white-space:normal}
.tools{text-align:right}
.check{float:left;height:20px;width:20px;margin-right:10px!important}
.correct{font-weight:bold;font-size:16px;color:green}
/* Keep each MCQ marker and equation in the same left-aligned row. */
.lebd-mcq-options{list-style:none!important;margin:14px 0 18px!important;padding:0!important}
.lebd-mcq-options>li{display:flex!important;align-items:flex-start;justify-content:flex-start;gap:9px;clear:both;width:100%;padding:7px 0!important;min-height:28px}
.lebd-mcq-marker{flex:0 0 28px;width:28px;line-height:1.7;white-space:nowrap}
.lebd-mcq-content{flex:1 1 auto;min-width:0;text-align:left!important;line-height:1.7;overflow-wrap:anywhere;font-size:16px}
.lebd-mcq-content p,.lebd-mcq-content div{margin-top:0;margin-bottom:0;text-align:left!important}
.lebd-mcq-content mjx-container[display="true"],
.lebd-mcq-content .MathJax_Display{display:inline-block!important;margin:0!important;text-align:left!important;vertical-align:middle!important;max-width:100%}
.lebd-mcq-content mjx-container{margin:0!important;max-width:100%}
</style>
<div class="box-header">
  <div class="pagination-sm no-margin pull-right">{!! $questions->links() !!}</div>
  <h3 class="box-title">Questions ({{$questions->total()}})
    <br><label for="checkAll" style="margin-top:12px;margin-left:15px;font-weight:normal;font-size:14px;cursor:pointer;">
      <input type="checkbox" onclick="lebdSelectVisibleQuestions(this)" style="width:18px;height:18px;vertical-align:middle;margin-right:5px;" id="checkAll" /> Select All (this page)
    </label>
  </h3>
</div>
<div class="box-body lebd-math-question-list">
@foreach($questions as $key => $value)
  <div class="col-md-12"><div class="panel panel-default">
    <div class="panel-heading">
      <label style="display:block;font-size:16px">
        <input type="checkbox" class="check" value="{{$value->id}}" onclick="lebdHandleRowSelect(this)" {{in_array($value->id, $xqs) ? 'checked':''}}>
        <span style="float:left;color:#a00">প্রশ্ন নং- {{$key + $questions->firstItem()}} : &nbsp; </span>{!!$value->title!!}
      </label>
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
        <ul class="list lebd-mcq-options">
          @foreach($value->mcqitems as $k => $val)
            @if($k <= 4)
              @php
              if($val->correct_answer) { $correct = $format[$k].' '.$val->item; }
              @endphp
              @php
                // Normalize math presentation only (do not update stored MCQ content).
                // Both inline and display LaTeX use the same inline display-style fraction size.
                $lebdOptionHtml = (string) $val->item;
                $lebdDisplay = static function ($match) {
                    $tex = trim($match[1]);
                    foreach (['\\displaystyle', '\\textstyle'] as $style) {
                        if (str_starts_with($tex, $style)) { $tex = trim(substr($tex, strlen($style))); break; }
                    }
                    return '\\(\\displaystyle ' . $tex . '\\)';
                };
                foreach (['\\[', '\\('] as $opening) {
                    $closing = $opening === '\\[' ? '\\]' : '\\)';
                    $regex = '~' . preg_quote($opening, '~') . '(.*?)' . preg_quote($closing, '~') . '~s';
                    $lebdOptionHtml = preg_replace_callback($regex, $lebdDisplay, $lebdOptionHtml);
                }
                $lebdOptionHtml = preg_replace_callback('/\$\$(.*?)\$\$/s', $lebdDisplay, $lebdOptionHtml);
              @endphp
              <li>
                <span class="lebd-mcq-marker {{$val->correct_answer ? 'correct' : ''}}">{{$format[$k]}}</span>
                <div class="lebd-mcq-content {{$val->correct_answer ? 'correct' : ''}}">{!! $lebdOptionHtml !!}</div>
              </li>
            @endif
          @endforeach
        </ul>
        <div class="col-md-12 no-padding"><button class="btn btn-info" onclick="showHideAnswer(this)">Show Answer & Explanation: <i class="fa fa-chevron-down"></i></button></div>
        <div class="col-md-12 no-padding hide"><hr><p class="text-success"><b>Answer:</b> {!! $correct !!}</p><p><b>Explanation:</b>{!!$value->explanation!!}</p></div>
        @php $correct = ''; @endphp
      @elseif($value->type == 'Written')
        <p style="padding-top:15px"><b>Answer:</b> {!! $value->answer !!}</p>
        <p>
          @foreach($value->answerfiles as $val)
            @if(substr($val->file, -3) == 'pdf')
              <a target="_blank" href="{{$val->file}}"><i class="fa fa-file"></i></a>
            @else
              <a target="_blank" href="{{$val->file}}"><img src="{{$val->file}}" alt="" width="100"></a>&nbsp;
            @endif
          @endforeach
        </p>
      @elseif($value->type == 'Video')
        @if($value->url_type == 'Youtube')
          <div class="iframe-container"><iframe src="https://www.youtube.com/embed/tgbNymZ7vqY"></iframe></div>
        @elseif($value->url_type == 'Google Drive')
          <div class="iframe-container"><iframe src="https://drive.google.com/file/d/{{$value->video}}/preview" allow="autoplay" allowfullscreen></iframe></div>
        @endif
        <div class="col-md-12 no-padding"><button class="btn btn-info" onclick="showHideAnswer(this)">Show Explanation: <i class="fa fa-chevron-down"></i></button></div>
        <div class="col-md-12 no-padding hide"><hr><p><b>Explanation:</b>{!!$value->explanation!!}</p></div>
      @endif
    </div>
  </div></div>
@endforeach
</div>
<div class="box-footer clearfix"><div class="pagination-sm no-margin pull-right">{!! $questions->links() !!}</div></div>
