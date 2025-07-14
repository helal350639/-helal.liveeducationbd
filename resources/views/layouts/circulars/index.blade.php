@php
use \App\Http\Controllers\CircularCtrl;
$circular = New CircularCtrl;

use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Circular')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Circular</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Circular</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Circular</h3>
              <div class="box-tools">
                <a href="{{route('circular.create')}}" class="btn btn-sm btn-info">
                  <i class="fa fa-plus"></i> Add
                </a>
                <form action="{{route('circular.index')}}" method="GET" class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">
                  <input type="text" name="search" class="form-control pull-right" placeholder="Name" value="{{request('search')}}">

                  <div class="input-group-btn">
                    <button type="submit" class="btn btn-info"><i class="fa fa-search"></i></button>
                  </div>
                </form>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              
              <table id="example1" class="table table-bordered table-hover">
                <tr>
                  <th>Id</th>
                  <th>Title</th>
                  <th>Publish Date</th>
                  <th>Apply Start</th>
                  <th>Deadline</th>
                  <th>Institute</th>
                  <th>Position</th>
                  <th>Fee</th>
                  <th>Status</th>
                  <th width="160">Action</th>
                </tr>
                @foreach($circulars as $value)
                <tr>
                  <td>{{$value->id}}</td>
                  <td>{{$value->title}}</td>
                  <td>{{$source->dformat($value->published_at)}}</td>
                  <td>{{$source->dformat($value->start_at)}}</td>
                  <td>{{$source->dformat($value->end_at)}}</td>
                  <td>{{$value->admit_to}}</td>
                  <td>{{$value->position}}</td>
                  <td>{{$value->fee}}</td>
                  <td>
                    @if($value->status == 'Published')
                    <span class="label label-success">{{$value->status}}</span>
                    @else
                    <span class="label label-warning">{{$value->status}}</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{route('circular.show', $value->id)}}" class="btn btn-info btn-sm" title="Details"><i class="fa fa-eye"></i></a>
                    <a href="{{route('circular.edit', $value->id)}}" class="btn btn-warning btn-sm" title="Edit this one"><i class="fa fa-edit"></i></a>
                    <form style="display: inline" action="{{route('circular.destroy', $value->id)}}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this one?')"><i class="fa fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </table>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
              <div class="pagination-sm no-margin pull-right">
                {{$circulars->links()}}
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