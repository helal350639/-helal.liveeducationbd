@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('student')
@section('title', 'Read Book')
@section('content')
<style>
  .mcqitems{list-style: none; padding-left: 10px; }
  .mcqitems li{padding: 10px;list-style: none; }
  .banner{margin-top:15px}
  .banner img{width:100%}
  .questions{margin: auto; font-size:12px}
  @media screen and (max-width:768px){
    .questions{}
  }
  @media screen and (min-width:769px){
    .questions{width:750px; columns:360px 2; column-gap:30px; column-rule: 1px solid #888;}
    .mcqitems{width:340px; column-width:140px}
  }
</style>

    <!-- Main content -->
  <section class="our_courses">
    <div class="row">
      <div class="title-area text-center">
          <h1>Read Book</h1>
        </div>
      </div>
    <div class="row"> <!-- left column -->
      <div class="col-md-12"> <!-- general form elements -->
        <div class="card">
          <div class="card-header with-border">
            <h4 class="float-start" style="display: inline">Questions (<b>{{$questions->count()}}</b>)</h4>
            <div class="text-right toolbar-icon float-end" style="display: inline">
              <a href="#" class="label label-info" title="Print" onclick="printDiv()"><i class="fa fa-print"></i> Print</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
      <div class="box" id="print">
        @if($subject->banner)
        <div class="banner">
          <img src="{{$subject->banner}}" alt="" style="width:100%">
        </div>
        @endif
        <div class="col-md-12">
          <div class="header" style="text-align:center">{!! $subject->header !!} </div>
          <p style="text-align: center">
            {{$subject->name}}</p>
          <h3 style="text-align: center;border-bottom:2px solid #ddd; padding-bottom:15px">
            {{$subject->name}}
          </h3>
            <div class="questions">
              @foreach($questions as $key => $value)
              @php
              $correct_ans = '';
              @endphp
              
              <div class="question">
                <div style="display: inline; font-weight:bold; float:left; padding-right:10px;">প্রশ্ন {{$key+1}}.</div>
                <div style="display: inline; text-align:justify">{!! $value->title !!}</div>

                <div class="mcqitems">
                  @foreach($value->mcqitems as $k => $val)
                  @php

                  if($val->correct_answer)
                  {
                    // $correct_ans = $source->mcqlist()[$subject->format][$k].' '.$val->item;
                  }
                  @endphp
                  <div style="padding:10px;">
                    {{-- <span> {{$source->mcqlist()[$subject->format][$k]}} </span> --}}
                    {{$val->item}}
                  </div>
                  @endforeach
                  <div class="clearfix"></div>
                </div>
                <div style="color:green; clear:top; padding:10px 0;padding-left:20px">সঠিক উত্তরঃ <b>{{$correct_ans}}</b></div>
                @if($value->explanation)
                <div style="border-bottom:1px solid #ddd; margin-bottom:15px; margin-left:20px"><b style="float:left"> ব্যাখ্যাঃ &nbsp; </b> <p> {!! $value->explanation !!}</p></div>
                @endif
              </div>
              @php
              $correct_ans = '';
              @endphp
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
