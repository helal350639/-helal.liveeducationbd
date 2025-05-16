@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Orders')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Orders</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Orders</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Orders</h3>
              <div class="box-tools">
                {{-- <a href="{{route('order.create')}}" class="btn btn-sm btn-info"><i class="fa fa-plus"></i> Add</a> --}}
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
                  <th>Batch</th>
                  <th>Department</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Order At</th>
                  <th>Status</th>
                  <th>Connected?</th>
                  <th width="100">Action</th>
                </tr>
                @foreach($orders as $value)
                <tr>
                  <td>{{$value->id}}</td>
                  <td>{{$value->student_name}}</td>
                  <td>{{$value->batch_name}}</td>
                  <td>
                    {{$value->department_name}}
                    @if(!DB::table('department_student')->where('department_id', $value->department_id)->where('student_id', $value->student_id)->first())
                    <p style="color:red">Missing</p>
                    @endif
                  </td>
                  <td>{{$value->email}}</td>
                  <td>{{$value->phone}}</td>
                  <td>{{$source->dtformat($value->created_at)}}</td>
                  <td>
                    @if($value->status == 'Complete')
                    <span class="label label-success">{{$value->status}}</span>
                    @elseif($value->status == 'Pending')
                    <span class="label label-warning">{{$value->status}}</span>
                    @elseif($value->status == 'Processing')
                    <span class="label label-primary">{{$value->status}}</span>
                    @elseif($value->status == 'Failed')
                    <span class="label label-default">{{$value->status}}</span>
                    @else
                    <span class="label label-danger">{{$value->status}}</span>
                    @endif
                  </td>
                  <td>
                    @if($value->batch_has_student)
                    <i class="text-green fa fa-check"></i>
                    @else
                      <i class="text-red fa fa-times"></i>

                      @if($value->status == 'Processing' || $value->status == 'Success')
                      <a href="{{route('student.addto.batch', $value->id)}}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> > <i class="fa fa-file-text"></i> </a>
                      @endif

                    @endif
                  </td>
                  <td>
                    {{-- <a href="{{route('group.show',$value->id)}}" class="label label-info" title="Details"><i class="fa fa-file-text"></i></a> --}}
                    {{-- <a href="{{route('students.add', [$value->id, 'Group'])}}" class="btn btn-primary btn-sm" title="Add Students"><i class="fa fa-user-plus"></i></a> --}}
                    {{-- <a href="{{route('group.edit', $value->id)}}" class="btn btn-warning btn-sm" title="Edit"><i class="fa fa-edit"></i></a> --}}
                    <form style="display: inline" action="{{route('order.destroy', $value->id)}}" method="POST">
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
                {{$orders->links()}}
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