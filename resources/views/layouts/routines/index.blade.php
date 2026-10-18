@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Routine')
@section('content')
<style>
  .tools{text-align: right;}
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Routine</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Routine</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Routine</h3>
              <div class="box-tools">
                <a href="{{route('routine.create')}}" class="btn btn-sm btn-info">
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
              <table class="table">
              <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Department</th>
                <th>Batch</th>
                <th>Course</th>
                <th>PDF</th>
                <th>Status</th>
                <th width="130">Action</th>
              </tr>
              @foreach($routines as $key => $value)
              <tr>
                <td>{{ $value->id }}</td>
                <td>{{ $value->name }}</td>
                <td>{{ $value->department ? $value->department->name:'' }}</td>
                <td>{{ $value->batch ? $value->batch->name : '' }}</td>
                <td>{{ $value->course ? $value->course->name : '' }}</td>
                <td>
                  @if($value->pdf)
                  <a target="_blank" href="{{$value->pdf}}" class="btn btn-sm btn-info"><i class="fa fa-file"></i></a>
                  @endif
                </td>
                <td>
                  @if($value->status == 'Published')
                  <span class="label label-success">{{$value->status}}</span>
                  @else
                  <span class="label label-danger">{{$value->status}}</span>
                  @endif
                </td>


                <td style="white-space: nowrap;">

  {{-- View Routine Items --}}
  <a href="{{route('routine.item.show', $value->id)}}"
   class="btn btn-info btn-sm"
   title="View Routine">
    <i class="fa fa-list"></i>
</a>

  {{-- Copy Routine --}}
  <a href="{{route('routine.copy', $value->id)}}"
     class="btn btn-success btn-sm"
     title="Copy Routine"
     onclick="return confirm('এই Routine-এর একটি সম্পূর্ণ Copy তৈরি করতে চান?')">
    <i class="fa fa-copy"></i>
  </a>

  {{-- Edit Routine --}}
  <a href="{{route('routine.edit', $value->id)}}"
     class="btn btn-warning btn-sm"
     title="Edit Routine">
    <i class="fa fa-edit"></i>
  </a>

  {{-- Delete Routine --}}
  <form style="display: inline"
        action="{{route('routine.destroy', $value->id)}}"
        method="POST">

    @csrf
    @method('DELETE')

    <button type="submit"
            class="btn btn-danger btn-sm"
            title="Delete Routine"
            onclick="return confirm('Are you sure you want to delete this one?')">

      <i class="fa fa-trash"></i>

    </button>

  </form>

</td>


              @endforeach
            </table>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
              <div class="pagination-sm no-margin pull-right">
                {{$routines->links()}}
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