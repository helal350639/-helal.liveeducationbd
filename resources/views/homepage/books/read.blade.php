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
.modal{
  margin-top: 5%;
  z-index: 99999;
}
.show{
  padding: 15px;
}
</style>
<div class="preloader">

</div>
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
          <span style="float: left; color:#a00"> প্রশ্ন নং- {{$key + $questions->firstItem()}} : &nbsp; </span>{!!$value->title!!}
        </label>
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
          <button class="btn btn-default" onclick="showHideAnswer(this)">
            Show Answer & Explanation: <i class="fa fa-chevron-down"></i>
          </button>
          <button class="btn btn-warning" onclick="reactShow(this)" data-id="{{$value->id}}">
            <i class="fa fa-smile"></i>
            Reaction
          </button>
          <button class="btn btn-info" onclick="commentShow(this)" data-id="{{$value->id}}">
            <i class="fa fa-comment-o"></i>
            Comments
          </button>
          <button class="btn btn-primary" onclick="rateShow(this)" data-id="{{$value->id}}">
            <i class="fa fa-chart-line"></i>
            Rating
          </button>
          <button class="btn btn-success" onclick="readingShow(this)" data-id="{{$value->id}}">
            <i class="fa fa-eye"></i>
            Reading
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

{{-- <div class="filter">
  <button type="button">Filter</button>
</div> --}}

<!-- Modal React -->
<div class="modal fade" id="reaction">
  <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{route('question.react.store')}}" method="POST" id="reactForm">
          <input type="hidden" name="question_id">
          <div class="modal-body">
              <label class="btn btn-default">
                <input type="radio" name="reaction" value="Good"> Good
              </label>
              <label class="btn btn-default">
                <input type="radio" name="reaction" value="Very Good"> Very Good
              </label>
              <label class="btn btn-default">
                <input type="radio" name="reaction" value="Moderate"> Moderate
              </label>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success" data-bs-dismiss="modal">Done</button>
          </div>
        </form>
      </div>
  </div>
</div>

<!-- Modal React -->
<div class="modal fade" id="comment">
  <div class="modal-dialog">
    <form action="{{route('question.comment.store')}}" id="commentForm" method="POST">
      <input type="hidden" name="question_id">
      <div class="modal-content">
          <div class="modal-body">
              <textarea class="form-control" name="comment" rows="5" placeholder="Type your comment about this question..." required></textarea>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success" data-dismiss="modal">Done</button>
          </div>
          <div class="model-footer" id="comment_show">
          </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Rating -->
<div class="modal fade" id="rate">
  <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{route('question.rate.store')}}" method="POST" id="rateForm">
          <input type="hidden" name="question_id">
          <div class="modal-body">
              <label class="btn btn-default">
                <input type="radio" name="rating" value="Very Important"> Very Important
              </label>
              <label class="btn btn-default">
                <input type="radio" name="rating" value="Medium Important"> Medium Important
              </label>
              <label class="btn btn-default">
                <input type="radio" name="rating" value="Low Important"> Low Important
              </label>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success" data-bs-dismiss="modal">Done</button>
          </div>
        </form>
      </div>
  </div>
</div>

<!-- Modal Reading -->
<div class="modal fade" id="reading">
  <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{route('question.reading.store')}}" method="POST" id="readingForm">
          <input type="hidden" name="question_id">
          <div class="modal-body">
              <label class="btn btn-default">
                <input type="radio" name="reading" value="Done"> Done
              </label>
              <label class="btn btn-default">
                <input type="radio" name="reading" value="Again Reading"> Again Reading
              </label>
              <label class="btn btn-default">
                <input type="radio" name="reading" value="So Hard"> So Hard
              </label>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success" data-bs-dismiss="modal">Done</button>
          </div>
        </form>
      </div>
  </div>
</div>

<script>
  function showHideAnswer(e)
  {
    let item = e.parentNode.nextElementSibling;
    item.classList.toggle('hide');
  }
</script>
@endsection

@section('scripts')
<script>
  function ajaxShow(url)
  {
    // get comments
    return $.ajax({
      type: 'GET',
      url: url,
    });
  }

  function ajaxStore(url, formdata)
  {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    return $.ajax({
      type: 'POST',
      url: url,
      data: formdata,
      success: function(data){
        // console.log(data);
      },
      contentType:false,
      processData:false,
      error: function(data){
        console.error(data);
      }
    });
  }

  // react show
  async function reactShow(e)
  {
    const reactForm = document.getElementById('reactForm');
    reactForm.elements.question_id.value = e.dataset.id;

    let url = '{{route("question.react.show", "")}}/'+e.dataset.id;
    let data = await ajaxShow(url);

    reactForm.elements.reaction.forEach((r) => {
      if(data.react != null && r.value == data.react.reaction)
      {
        r.checked = true;
      }
      else
      {
        r.checked = false;
      }
    });
    
    $('#reaction').modal('show');
  }

  // react store
  const reactForm = document.getElementById('reactForm');
  reactForm.addEventListener('submit', async function (e)
  {
    e.preventDefault();

    if(!reactForm.checkValidity())
    {
      reactForm.reportValidity();
      return;
    }

    let formdata = new FormData(reactForm);

    let url = '{{route("question.react.store")}}';
    let data = await ajaxStore(url, formdata);
    //hide the react form
    $('#react').modal('hide');
  });

  async function commentShow(e)
  {
    const commentFrom = document.getElementById('commentForm');
    const comment_show = document.getElementById('comment_show');
    //assign id
    commentForm.elements.question_id.value = e.dataset.id;
    let url = '{{route("question.comment.show", "")}}/'+e.dataset.id;

    let data = await ajaxShow(url);
    let showlist = '';
    data.comment.forEach((c) => {
      showlist += '<p><i class="fa fa-comment-o"></i> '+c.comment+'</p>';
    });
    comment_show.innerHTML = '<div class="show">'+showlist+'</div>';
    //show modal
    $('#comment').modal('show');    
  }

  const commentForm = document.getElementById('commentForm');
  commentForm.addEventListener('submit', async function (e)
  {
    e.preventDefault();

    if(!commentForm.checkValidity())
    {
      commentForm.reportValidity();
      return;
    }

    let formdata = new FormData(commentForm);

    let url = '{{route("question.comment.store")}}';
    let data = await ajaxStore(url, formdata);
    //hide the comment form
    $('#comment').modal('hide');
  });

  // react show
  async function rateShow(e)
  {
    const rateForm = document.getElementById('rateForm');
    rateForm.elements.question_id.value = e.dataset.id;

    let url = '{{route("question.rate.show", "")}}/'+e.dataset.id;
    let data = await ajaxShow(url);

    rateForm.elements.rating.forEach((r) => {
      if(data.rate != null && r.value == data.rate.rating)
      {
        r.checked = true;
      }
      else
      {
        r.checked = false;
      }
    });
    
    $('#rate').modal('show');
  }

  const rateForm = document.getElementById('rateForm');
  rateForm.addEventListener('submit', async function (e)
  {
    e.preventDefault();

    if(!rateForm.checkValidity())
    {
      rateForm.reportValidity();
      return;
    }

    let formdata = new FormData(rateForm);

    let url = '{{route("question.rate.store")}}';
    let data = await ajaxStore(url, formdata);
    //hide the rate form
    $('#rate').modal('hide');
  });

  // react show
  async function readingShow(e)
  {
    const readingForm = document.getElementById('readingForm');
    readingForm.elements.question_id.value = e.dataset.id;

    let url = '{{route("question.reading.show", "")}}/'+e.dataset.id;
    let data = await ajaxShow(url);

    readingForm.elements.reading.forEach((r) => {
      if(data.reading != null && r.value == data.reading.reading)
      {
        r.checked = true;
      }
      else
      {
        r.checked = false;
      }
    });
    
    $('#reading').modal('show');
  }

  const readingForm = document.getElementById('readingForm');
  readingForm.addEventListener('submit', async function (e)
  {
    e.preventDefault();

    if(!readingForm.checkValidity())
    {
      readingForm.reportValidity();
      return;
    }

    let formdata = new FormData(readingForm);

    let url = '{{route("question.reading.store")}}';
    let data = await ajaxStore(url, formdata);
    //hide the reading form
    $('#reading').modal('hide');
  });
</script>
@endsection