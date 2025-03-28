@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Exams')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Exams</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">All Exams</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Exams</h3>
          <div class="box-tools">
            <a href="{{route('paper.index')}}" title="View" class="btn btn-success"><i class="fa fa-list"></i></a>
            <a href="{{route('paper.view', $paper->id)}}" title="View" class="btn btn-primary"><i class="fa fa-th"></i></a>
            <a class="btn btn-info" onclick="printDiv()">
              <i class="fa fa-print"></i> Print
            </a>
            {{-- <a href="{{route('paper.result.csv', $paper->id)}}" class="btn btn-success" title="File CSV"><i class="fa fa-download"></i> Export CSV</a> --}}
          </div>
        </div>
        <!-- /.box-header -->
      </div><!--/.box -->
      <div class="box box-body table-responsive no-padding">
        <table id="print" class="table table-bordered table-hover">
          <tr>
            <th colspan="12">
              @if($paper->banner)
              <img src="{{$paper->banner}}" alt="" style="width:100%" />
              @endif
              {!! $paper->header !!}
            </th>
          </tr>
          <tr>
            <th colspan="4">Exam No. {{$paper->name}}</th>
            <th colspan="4" style="text-align: right">Exam Date: {{$source->dformat($exams[0]->created_at)}}</th>
          </tr>
          <tr>
            <th>SL No.</th>
            <th>Student Name</th>
            <th>Registration ID</th>
            <th>Department</th>
            <th>Start & End Time</th>
            <th>Status</th>
            <th style="text-align: right">Correct</th>
            <th style="text-align: right">Wrong</th>
            <th style="text-align: right">Blank</th>
            <th style="text-align: right">Total Mark</th>
            <th>Actions</th>
          </tr>
          @foreach($exams as $key => $value)
          @if($value->student)
          <tr style="color:{{in_array($value->student->id, $students)?'':'red'}}">
            <td>{{$key+1}}</td>
            <td>{{$value->student->name}}</td>
            <td>{{str_pad($value->student->id, 6, '0', STR_PAD_LEFT)}}</td>
            <td>{{$value->paper->department ? $value->paper->department->name : ''}}</td>
            <td>{{$source->tformat($value->start_at).' - '.$source->tformat($value->end_at)}}</td>
            <td>
              @if($value->status == 'Live')
              <label class="label label-info">{{$value->status}}</label>
              @elseif($value->status == 'Completed')
              <label class="label label-success"> {{$value->status}}</label>
              @endif
            </td>
            <td style="text-align: right">{{$value->correct}}</td>
            <td style="text-align: right">{{$value->wrong}}</td>
            <td style="text-align: right">{{$value->no_answer}}</td>
            <td style="text-align: right">{{$value->mark}}</td>
            <td>

              @if(Auth::user()->authorizeRoles(['SuperAdmin']))
              <form style="display: inline" action="{{route('exam.destroy', $value->id)}}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this one? If you delete this exam, the candidate will lose his/her result that\'s will not get back later.')" title="Delete This!"><i class="fa fa-trash"></i></button>
              </form>
              @endif

              @if($value->mark == 0)
              <a href="{{route('paper.recheck.result', $value->id)}}" title="View" class="btn btn-info btn-sm" title="Re-Check Result" onclick="return confirm('Are you sure, you want to update the result?')"><i class="fa fa-check"></i></a>
              @endif
            </td>
          </tr>
          @endif
          @endforeach
        </table>
      </div> <!-- /.box-body -->
    </div> <!-- /.box -->
  </div>
  </section>
    <!-- /.content -->
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