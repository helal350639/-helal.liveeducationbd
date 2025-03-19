@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'Solution')
@section('content')
<style>
  .mcqitems{list-style: none; padding-left: 10px}
  .mcqitems li{padding: 10px}
  .banner{margin-top:15px}
  .banner img{width:100%}
  .questions{margin: auto;}
  
  /* @media screen and (min-width:480px) {
    .tools{max-width:400px; max-height: 600px; overflow:auto;}
    .sticky{position: fixed;top:10px; z-index: 999999;}
  }
  @media screen and (max-width:481px){
    .tools{width:100%}
    .sticky{position: relative;}
  } */
</style>
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Solution</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Solution</a></li>
        <li class="active">Details</li>
      </ol>
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-12"><!-- general form elements -->
        <div class="box box-primary sticky">
          <div class="box-header with-border">
            <h4 class="box-title" style="display: inline">Solution (<b>{{count($paper->questions)}}</b>)</h4>
            <div class="text-right toolbar-icon pull-right" style="display: inline">
              {{-- <a href="{{route('paper.add.question', $paper->id)}}" title="Add Questions" class="label label-info"><i class="fa fa-plus"></i> Add Questions</a> --}}
              {{-- <a href="{{route('paper.create')}}" title="Add" class="label label-primary"><i class="fa fa-pencil"></i> Create</a> --}}
              {{-- <a href="{{route('paper.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a> --}}
              <a href="{{route('paper.view', $paper->id)}}" class="label label-primary" title="Details"><i class="fa fa-file-text"></i></a>
              {{-- <a href="#" class="label label-info" title="Print" onclick="printDiv()"><i class="fa fa-print"></i></a> --}}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
      <div class="box" id="print">
        @if($paper->banner)
        <div class="banner">
          <img src="{{$paper->banner}}" alt="" style="width:100%">
        </div>
        @endif
        <div class="col-md-12">
          <div class="header" style="text-align:center">{!! $paper->header !!} </div>
          <p style="text-align: center">
            {{$paper->name}}</p>
          <h3 style="text-align: center;border-bottom:2px solid #ddd; padding-bottom:15px">
            Solution Paper
          </h3>
            <div class="questions" id="questions-area" style=" width:750px; columns:360px 2; column-gap:30px; column-rule: 1px solid #888; font-size:12px">
              {{-- questions will load here by javascript --}}
            </div>
          </div>
          <div class="clearfix"></div>
        </div><!--/.col -->

        <div class="box col-md-12">
          <h4 class="box-title" style="display: inline">Total Questions: (<b>{{count($paper->questions)}}</b>)</h4>
          <div class="text-right toolbar-icon pull-right" style="display: inline"><a href="#" class="label label-info" title="Print" onclick="printDiv()">Print <i class="fa fa-print"></i></a>
            </div>
            <div class="clearfix"></div>

        </div>

        
      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
   
@endsection
@section('scripts')
<script>
  //load all questions
  let questions_area = document.getElementById('questions-area');
  function loadQuestions()
  {
    let paper_id = '{{$paper->id}}';

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

    // let formData = new FormData();
    // formData.append('paper_id', '{{$paper->id}}');

    $.ajax({
      url: '{{route("paper.solution.ajax", "")}}/'+paper_id,
      type: 'GET',
      // data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success: function(data){
        // console.log(data.questions.data.length);
        if(data.success == true && data.questions.data.length > 0){
          // call to the data writer on the html
          writeQuestion(data);
        }
      },
      error: function(data){
        console.log(data);
      },
    });
  }

  //execute questions
  loadQuestions();

// onscroll load questions according to the pagination
  var page = 2;

  $(document).ready(function()
  {
    // see if we're at the bottom of the page to potentially load more content
    $(window).on('scroll', scrollProducts);

    function scrollProducts()
    {
      // let last_page = 3;
      let paper_id = '{{$paper->id}}';
      var end = $("#footer").offset().top;
      var viewEnd = $(window).scrollTop() + $(window).height();
      var distance = end - viewEnd;

      // when we're almost at the bottom
      if (distance < 900){
        // unbind to prevent excessive firing
        $(window).off('scroll', scrollProducts);
        // console.log('we reached the bottom');

        $.ajax({
          type: 'GET',
          url: '{{route("paper.solution.ajax", "")}}/'+paper_id+'?page=' + page,
          success: function(data) {
            // console.log("success!");
            // $('#container').append(data).fadeIn();
            // rebind after successful update
            if(data.success == true && data.questions.data.length > 0)
            {
              // console.log(data.qeustions.last_page);
              // last_page = data.questions.last_page;
              // call to the data writer on the html
              writeQuestion(data);
            }

            $(window).on('scroll', scrollProducts);
            page++;
          }
        });
      }
      // console.log(page);
    }
  });
  // on scroll load questions end
  
  let index = 1;
  let total_question = 0;
  // data writer on the question area
  function writeQuestion(data)
  {
    data.questions.data.forEach((e, n) => 
    {
      let mcqs = '';
      let correct_ans = '';
      
      e.mcqitems.forEach((i, n) => {
        mcqs += '<div style="padding:10px;">'+
            '<span>'+data.format[n]+ '</span> '+i.item+
          '</div>';

          if(i.correct_answer){
            correct_ans = data.format[n]+ ' '+i.item;
          }
          
      });

      let question = document.createElement('div');
      question.setAttribute('class', 'question');
      question.innerHTML = '<div class="panel-heading question-title-main">'+
        '<div style="display:inline; font-weight:bold;float:left; padding-right:10px">প্রশ্ন '+Number(index)+'.</div>'+
        '<div style="display: inline; text-align:justify">'+e.title+'</div>'+
      '</div>'+
      '<div class="mcqitems" style="list-style: none; width:360px; column-width:140px" id="'+e.id+'">'+mcqs+'<div class="clearfix"></div></div>'+

      '<div style="color:green; clear:top; padding:10px 0;padding-left:20px">সঠিক উত্তরঃ <b>'+correct_ans+'</b></div>';

      questions_area.append(question);
      index++;
    });
  }

  // print this solution area
  function printDiv()
  {
    // document.getElementById('heading').style.display = 'block';
    var divToPrint = document.getElementById('print');
    var htmlToPrint = '' +
        '<style type="text/css">' +
        '.heading{display:block}'+
        '.pageheader{font-size:15px}'+
        'table { border-collapse:collapse; font-size:15px;width:100%}' +
        '.table tr th, .table tr td { padding: 10px; border:1px solid #ddd; text-align:left}' +
        'table tr{background: #ddd}'+
        '.receipt{display:none}'+
        '</style>';
    htmlToPrint += divToPrint.outerHTML;
    newWin = window.open(htmlToPrint);
    newWin.document.write(htmlToPrint);
    newWin.print();
    newWin.close();
    // document.getElementById('heading').style.display = 'none';
  }
</script>
@endsection
