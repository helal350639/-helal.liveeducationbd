@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Filter')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Filter</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Filter</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Filter</h3>
              <div class="box-tools">
                <a href="{{route('filter.create')}}" class="btn btn-sm btn-info">
                  <i class="fa fa-plus"></i> Add
                </a>
                <form action="{{route('filter.index')}}" method="GET" class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">
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
                  <th>Name</th>
                  <th>Course Name</th>
                  <th>Label</th>
                  <th>Details</th>
                  <th>Created At</th>
                  <th>Status</th>
                  <th width="110">Action</th>
                </tr>
                @foreach($filters as $filter)
                <tr>
                  <td>{{$filter->id}}</td>
                  <td>{{$filter->name}}</td>
                  <td>
                    @foreach($filter->courses as $course)
                    <label class="label label-primary">{{$course->name}}</label>
                    @endforeach
                  </td>
                  <td>{{$filter->label}}</td>
                  <td>{{$filter->details}}</td>
                  <td>{{$source->dtformat($filter->created_at)}}</td>
                  <td>
                    @if($filter->status == 'Active')
                    <span class="label label-success">Active</span>
                    @elseif($filter->status == 'Inactive')
                    <span class="label label-warning">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{route('filter.edit', $filter->id)}}" class="btn btn-warning btn-sm" title="Edit this filter"><i class="fa fa-edit"></i></a>
                    <form style="display: inline" action="{{route('filter.destroy', $filter->id)}}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this one?')"><i class="fa fa-trash"></i></button></form>
                  </td>
                </tr>
                @endforeach
              </table>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
              <div class="pagination-sm no-margin pull-right">
                {{$filters->links()}}
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