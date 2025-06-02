@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Complain')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Complains</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Complain</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Complain</h3>
              <div class="box-tools">
                {{-- <a href="{{route('complain.create')}}" class="btn btn-sm btn-info">
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
              <table id="example1" class="table table-bordered table-hover">
                <tr>
                  <th>Id</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Mobile</th>
                  <th>Department</th>
                  <th>Status</th>
                  <th width="130">Action</th>
                </tr>
                @foreach($complains as $val)
                <tr>
                  <td>{{$val->id}}</td>
                  <td>{{$val->name}}</td>
                  <td>{{$val->email}}</td>
                  <td>{{$val->contact}}</td>
                  <td>{{$val->department}}</td>
                  <td>
                    @if($val->status == 'Replied')
                    <span class="label label-success">{{$val->status}}</span>
                    @elseif($val->status == 'New')
                    <span class="label label-warning">{{$val->status}}</span>
                    @elseif($val->status == 'Read')
                    <span class="label label-primary">{{$val->status}}</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{route('complain.show', $val->id)}}" class="btn btn-info btn-sm" title="Details"><i class="fa fa-file-text"></i></a>
                    <a href="{{route('complain.edit', $val->id)}}" class="btn btn-warning btn-sm" title="Edit this"><i class="fa fa-edit"></i></a>
                    <form style="display: inline" action="{{route('complain.destroy', $val->id)}}" method="POST">
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
                {{$complains->links()}}
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