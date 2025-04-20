@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'Question Paper')
@section('content')
<style>
  .mcqitems{list-style: none; padding-left: 10px}
  .mcqitems li{padding: 10px}
  .banner{margin-top:15px}
  .banner img{width:100%}
</style>
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Question Paper</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Question Paper</a></li>
        <li class="active">Details</li>
      </ol>
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-12"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title" style="display: inline">Question Paper (<b>{{count($paper->questions)}}</b>)</h4>
            <div class="text-right toolbar-icon pull-right" style="display: inline">
              <a href="{{route('paper.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>
              <a href="{{route('paper.show', $paper->id)}}" class="label label-primary" title="Details"><i class="fa fa-file-text"></i></a>
              <a href="{{route('paper.edit', $paper->id)}}" class="label label-warning" title="Edit"><i class="fa fa-gear"></i></a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
      <div class="box">
        <div class="col-md-12">
          <div class="banner"><img src="{{$paper->banner}}" alt=""></div>
          <div class="header" style="text-align:center">{!! $paper->header !!} </div>
            <div class="col-md-12">
              @foreach($paper->questions as $key => $value)
              <div class="panel">
                <div style="display: inline; font-weight:bold;float:left; padding-right:10px">প্রশ্ন {{$key+1}}.</div>
                <div style="display: inline">{!! $value->title !!}
                  <span style="float:right">
                    <button class="btn btn-danger btn-xs" onclick="removeQuestion(this)" id="{{$value->id}}">
                      <i class="fa fa-times"></i>
                    </button>
                  </span>
                </div>
                <ul class="mcqitems">
                  @foreach($value->mcqitems as $k => $val)
                  <li style="{{$val->correct_answer ? 'color:green; font-weight:bolder' : ''}}">
                    {{-- <span>
                      <input type="radio" name="correct{{$value->id}}" />
                    </span> --}}
                    <span> {{$source->mcqlist()[$paper->format][$k]}} </span>{{$val->item}}
                  </li>
                  @endforeach
                </ul>
              </div>
              @endforeach
            </div>
          </div>
          <div class="clearfix"></div>
        </div><!--/.col -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
   
@endsection
@section('scripts')
<script>

  function copyToClipBoard(str){
    const element = document.createElement('textarea');
    element.value = str;
    document.body.appendChild(element);
    element.select();
    document.execCommand('copy');
    document.body.removeChild(element);
  }

  function handleClick(){
    let text = document.querySelector('#text');
    copyToClipBoard(text.getAttribute('href'));
  }

  // remove/detuch the question from question paper
  function removeQuestion(e)
  {
    if(confirm('Are you sure, you want to remove this question from this paper?')){

      $.ajax({
        url: '/paper/detach/{{$paper->id}}/'+e.id,
        type: 'GET',
        success:function (data){
          // console.log(data);
          if(data.success == true){
            e.parentNode.parentNode.parentNode.remove();
          }
        },
        error: function (data){
          console.log(data);
        }
      });
    }
  }
</script>
@endsection
