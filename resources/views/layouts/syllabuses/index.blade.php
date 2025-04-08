@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Syllabus')
@section('content')
<style>
  .tools{text-align: right;}
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Syllabus</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Syllabus</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Syllabus</h3>
              <div class="box-tools">
                <a href="{{route('syllabus.create')}}" class="btn btn-sm btn-info">
                  <i class="fa fa-plus"></i> Add
                </a>
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
              <table id="example1" class="table table-bordered table-hover">
                <tr>
                  <th>Id</th>
                  <th>Name</th>
                  <th>Questions</th>
                  <th>Department Name</th>
                  <th>Batch Name</th>
                  <th>Course Name</th>
                  <th>Status</th>
                  <th width="130">Action</th>
                </tr>
              @foreach($syllabus as $key => $value)
                <tr>
                  <td>{{$key+1}}</td>
                  <td>{{$value->name}}</td>
                  <td>{{$value->questions->count()}}</td>
                  <td>{{$value->department ? $value->department->name : ''}}</td>
                  <td>{{$value->batch ? $value->batch->name : ''}}</td>
                  <td>{{$value->course ? $value->course->name : ''}}</td>
                  <td>
                    <label class="text-{{$value->status == 'Published'? 'success':'warning'}}">{{$value->status}}</label>
                  </td>
                  <td>
                    <a href="{{route('syllabus.add.question', ['syllabus', $value->id])}}" title="Add Questions" class="btn btn-info btn-sm"><i class="fa fa-plus"></i></a>
                    <a class="btn btn-primary btn-sm" href="{{route('syllabus.view', $value->id)}}"><i class="fa fa-file"></i></a>
                    <a class="btn btn-warning btn-sm" href="{{route('syllabus.edit', $value->id)}}"><i class="fa fa-gear"></i></a>
                  </td>
                </tr>
              @endforeach
              </table>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
              <div class="pagination-sm no-margin pull-right">
                {{-- {{$syllabus->links()}} --}}
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