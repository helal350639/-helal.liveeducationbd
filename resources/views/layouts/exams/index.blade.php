@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Exam')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Exam</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Exam</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Exam</h3>
          <div class="box-tools">
              {{-- <a href="{{route('exam.create')}}" class="btn btn-sm btn-info">
                <i class="fa fa-plus"></i> Add
              </a> --}}
              {{-- <div class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">
                <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">

                <div class="input-group-btn">
                  <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                </div>
              </div> --}}
            </div>
          </div>
          <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="print" class="table table-bordered table-hover">
                <tr>
                  <th>SL No.</th>
                  <th>Student Name</th>
                  <th>Registration ID</th>
                  <th>Department</th>
                  <th>Start & End Time</th>
                  <th>Status</th>
                  {{-- <th style="text-align: right">Correct</th>
                  <th style="text-align: right">Wrong</th>
                  <th style="text-align: right">Blank</th> --}}
                  <th style="text-align: right">Total Mark</th>
                  <th>Actions</th>
                </tr>
                @foreach($exams as $key => $value)
                <tr>
                  <td>{{$key+1}}</td>
                  <td>{{$value->student->name}}</td>
                  <td>{{str_pad($value->student->id, 6, '0', STR_PAD_LEFT)}}</td>
                  <td>{{!empty($value->paper->department) ?$value->paper->department->name : ''}}</td>
                  <td>{{$source->tformat($value->start_at).' - '.$source->tformat($value->end_at)}}</td>
                  <td>
                    @if($value->status == 'Live')
                    <label class="label label-info">{{$value->status}}</label>
                    @elseif($value->status == 'Completed')
                    <label class="label label-success"> {{$value->status}}</label>
                    @endif
                  </td>
                  {{-- <td style="text-align: right">{{$value->correct}}</td>
                  <td style="text-align: right">{{$value->wrong}}</td>
                  <td style="text-align: right">{{$value->no_answer}}</td> --}}
                  <td style="text-align: right">{{$value->mark}}</td>
                  <td>
                    <a class="btn btn-info btn-sm" href="{{route('exam.paper', $value->id)}}"><i class="fa fa-th"></i></a>
                    @if(Auth::user()->authorizeRoles(['SuperAdmin']))
                    <form style="display: inline" action="{{route('exam.destroy', $value->id)}}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this one? If you delete this exam, the candidate will lose his/her result that\'s will not get back later.')" title="Delete This!"><i class="fa fa-trash"></i></button>
                    </form>
                    @endif
                    @if($value->mark == 0)
                    {{-- <a class="btn btn-success btn-sm" href="{{route('exam.paper', $value->id)}}" title="Re-Check Result"><i class="fa fa-check"></i></a> --}}
                    @endif
                  </td>
                </tr>
                @endforeach
              </table>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
              <div class="pagination-sm no-margin pull-right">
                {{$exams->links()}}
              </div>
            </div>
          </div>
          <!-- /.box -->
        </div>
      </div>
    </section>
    <!-- /.content -->
    @endsection
{{-- @section('scripts')
  <script>
    $(function () {
      $('#example1').DataTable()
      $('#example2').DataTable({
        'paging'      : true,
        'lengthChange': false,
        'searching'   : false,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : false
      })
    })
  </script>
@endsection --}}