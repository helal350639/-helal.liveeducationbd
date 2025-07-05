@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Semester')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Semester</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Semester</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Semester</h3>
              <div class="box-tools">
                <a href="{{route('semester.create')}}" class="btn btn-sm btn-info">
                  <i class="fa fa-plus"></i> Add
                </a>
                <form action="{{route('semester.index')}}" method="GET" class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">
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
                  <th>Department Name</th>
                  <th>Course Name</th>
                  <th>Created At</th>
                  <th>Status</th>
                  <th width="110">Action</th>
                </tr>
                @foreach($semesters as $value)
                <tr>
                  <td>{{$value->id}}</td>
                  <td>{{$value->name}}</td>
                  <td>
                    @foreach($value->departments as $val)
                    <label class="label label-primary">{{$val->name}}</label>
                    @endforeach
                  </td>
                  <td>
                    @foreach($value->courses as $val)
                    <label class="label label-info">{{$val->name}}</label>
                    @endforeach
                  </td>
                  <td>{{$source->dformat($value->created_at)}}</td>
                  <td>
                    @if($value->status == 'Active')
                    <span class="label label-success">Active</span>
                    @elseif($value->status == 'Inactive')
                    <span class="label label-warning">Inactive</span>
                    @endif
                  </td>
                  <td>
                    {{-- <a href="{{route('value.show',$value->id)}}" class="label label-info" title="value Details"><i class="fa fa-file-text"></i></a> --}}
                    <a href="{{route('semester.edit', $value->id)}}" class="btn btn-warning btn-sm" title="Edit this item"><i class="fa fa-edit"></i></a>
                    <form style="display: inline" action="{{route('semester.destroy', $value->id)}}" method="POST">
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
                {{-- {{$filters->links()}} --}}
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