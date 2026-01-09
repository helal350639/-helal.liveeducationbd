@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'Book')
@section('content')
<style>
  .mcqitems{list-style: none; padding-left: 10px}
  .mcqitems li{padding: 10px}
  .banner{margin-top:15px}
  .banner img{width:100%}
</style>
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Book</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Book</a></li>
        <li class="active">Details</li>
      </ol>
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-12"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title" style="display: inline">Book (<b>{{count($book->questions)}}</b>)</h4>
            <div class="text-right toolbar-icon pull-right" style="display: inline">
              <a href="{{route('book.add.question', ['book', $book->id])}}" title="Add Questions" class="label label-info"><i class="fa fa-plus"></i> Add Questions</a>
              <a href="{{route('book.create')}}" title="Add" class="label label-primary"><i class="fa fa-pencil"></i> Create</a>
              {{-- <a href="{{route('book.solution', $book->id)}}" title="Solution" class="label label-info"><i class="fa fa-file-o"> Solution</i></a> --}}
              <a href="{{route('book.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>
              <a href="{{route('book.edit', $book->id)}}" class="label label-warning" title="Edit"><i class="fa fa-gear"></i></a>
              <a href="#" class="label label-info" title="Print" onclick="printDiv()"><i class="fa fa-print"></i></a>
              {{-- <a href="{{route('book.pdf', $book->id)}}" title="PDF Download" class="label label-info"><i class="fa fa-file"> Print PDF</i></a> --}}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
      <div class="box" id="print">
        <div class="col-md-12">
          <div class="banner">
            <img src="{{$book->banner}}" alt="">
          </div>
          <div class="header" style="text-align:center">{!! $book->header !!} </div>
          <p style="text-align: center">Course Name: <b>{{$book->course?$book->course->name:''}}</b></p>
          <hr>
            <div class="row">
              @foreach($questions as $key => $value)
              <div class="panel col-md-6">
                <div style="display: inline; font-weight:bold;float:left; padding-right:10px; text-align:justify">প্রশ্ন {{$key + $questions->firstItem()}}.</div>
                <div style="display: inline">{!! $value->title !!}</div>
                @foreach($value->getlabels as $val)
                <label style="border:1px solid #aaa; padding:0 5px; color:brown">{{$val->label}}</label>
                @endforeach

                @php
                $correct_ans = '';
                @endphp
                <ul class="mcqitems" style="list-style: none">
                  @foreach($value->mcqitems as $k => $val)
                  @php
                  if($val->correct_answer)
                  {
                    $correct_ans = $source->mcqlist()[$book->format][$k].' '.$val->item;
                  }
                  @endphp
                  <li style="width:50%; float:left">
                    <span> {{$source->mcqlist()[$book->format][$k]}} </span>{{$val->item}}
                  </li>
                  @endforeach
                  <div class="clearfix"></div>
                </ul>
                <div style="color:green; clear:top; padding:10px 0;">সঠিক উত্তরঃ <b>{{$correct_ans}}</b></div>
              </div>
              <div class="{{$key % 2 ? 'clearfix':''}}"></div>
              @endforeach
            </div>
          </div>
          <div class="clearfix"></div>
          <div class="box-footer clearfix">
            <div class="pagination-sm no-margin pull-right">
              {{$questions->links()}}
            </div>
          </div>
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