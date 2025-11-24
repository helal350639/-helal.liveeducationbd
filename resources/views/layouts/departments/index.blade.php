@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Department')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Department</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Department</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Department</h3>
              <div class="box-tools">
                <a href="{{route('department.create')}}" class="btn btn-sm btn-info">
                  <i class="fa fa-plus"></i> Add
                </a>
                <form action="{{route('department.index')}}" method="GET" class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">
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
                  <th>Students</th>
                  <th>Course Name</th>
                  <th>Created At</th>
                  <th>Status</th>
                  <th width="130">Action</th>
                </tr>
                @foreach($departments as $val)
                <tr>
                  <td>{{$val->id}}</td>
                  <td>{{$val->name}}</td>
                  <td>
                    <a href="{{route('student.view', [$val->id, 'department'])}}" class="btn btn-info">{{$val->students()->count()}}</a>
                  </td>
                  <td>
                    @foreach($val->courses as $value)
                    <label class="label label-primary"> {{$value->name}}</label>
                    @endforeach
                  </td>
                  <td>{{$source->dtformat($val->created_at)}}</td>
                  <td>
                    @if($val->status == 'Active')
                    <span class="label label-success">Active</span>
                    @elseif($val->status == 'Deactive')
                    <span class="label label-danger">Deactive</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{route('students.add', [$val->id, 'Department'])}}" class="btn btn-primary btn-sm" title="Add Students"><i class="fa fa-user-plus"></i></a>
                    <a href="{{route('department.edit', $val->id)}}" class="btn btn-warning btn-sm" title="Edit this filter"><i class="fa fa-edit"></i></a>
                    <form style="display: inline" action="{{route('department.destroy', $val->id)}}" method="POST">
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
                {{$departments->links()}}
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