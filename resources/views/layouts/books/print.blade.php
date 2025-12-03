@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$subject = '';
@endphp

@extends('dashboard')
@section('title', 'Syllabus')
@section('content')
  <style type="text/css">
    .mcqitems{list-style: none; padding-left: 10px}
    .mcqitems li{padding: 10px}
    .banner{margin-top:15px}
    .banner img{width:100%}
    .questions{margin: auto}
  </style>


  
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1 style="text-align: center; font-family: Tahoma">Syllabus</h1>
      <a href="{{route('syllabus.view', $syllabus->id)}}" title="Add" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Back</a>
      <a href="#" title="Add" class="btn btn-info pull-right" onclick="printDiv()"><i class="fa fa-print"></i> Print</a>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box" id="print" style="overflow: hidden">
            {{-- <img src="/img/logo.png" alt="" style="position: abso/lute; top:20%; left:20%;"> --}}
            <div class="col-md-12">
              <div class="banner">
                <img src="{{$syllabus->banner}}" alt="">
              </div>
              <div class="header" style="text-align:center">{!! $syllabus->header !!} </div>
              <p style="text-align: center">Course Name: <b>{{$syllabus->course?$syllabus->course->name:''}}</b></p>
              <p style="text-align: center">Batch: <b>{{$syllabus->batch?$syllabus->batch->name:''}}</b></p>
              <hr>
              <div class="questions" style="width:750px; columns:300px 2; column-gap:30px; column-rule: 1px solid #888; font-size:12px">
                  @foreach($syllabus->questions as $key => $value)

                  {{-- @if($subject == '' && $value->subjects->first() && $subject != $value->subjects->first()->name)
                  <h4 style="text-align: center">Subject: {{$value->subjects->first()?$value->subjects->first()->name:''}}</h4>
                  @endif --}}

                  <div class="question">
                    <p style="position: absolute; margin-top:150px; margin-left: 120px; transform:rotate(-30deg); opacity: 0.2 ; font-size:25px">LiveEducationBD.com</p>
                    
                    <div style="display: inline; font-weight:bold; float:left; padding-right:10px;">প্রশ্ন {{$key+1}}.
                    </div>
                    <div style="display: inline; text-align:justify;">{!! $value->title !!}</div>

                      @foreach($value->getlabels as $val)
                      <label style="border:1px solid #aaa; padding:0 5px; color:brown">{{$val->label}}</label>
                      @endforeach
                      @php
                      $correct_ans = '';
                      @endphp
                      <div class="mcqitems" style="width:360px; column-width:140px">
                        @foreach($value->mcqitems as $k => $val)
                        @php
                        if($val->correct_answer)
                        {
                          $correct_ans = $source->mcqlist()[$syllabus->format][$k].' '.$val->item;
                        }
                        @endphp
                        <div style="padding:10px;">
                          <span> {{$source->mcqlist()[$syllabus->format][$k]}} </span>{{$val->item}}
                        </div>
                        @endforeach
                      </div>
                      <div style="color:green; clear:top; padding:10px 0; padding-left:20px;">সঠিক উত্তরঃ <b>{{$correct_ans}}</b></div>
                    </div>

                    {{-- @php
                    $subject = $value->subjects->first()?$value->subjects->first()->name:'';
                    @endphp --}}
                  @endforeach
              </div>
            </div>
            <div class="clearfix"></div>
          </div><!--/.box -->
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