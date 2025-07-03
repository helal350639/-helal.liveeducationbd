@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;

function batch($ids)
{
  $batches = \App\Models\Batch::whereIn('id', json_decode($ids))->pluck('name')->toArray();
  // dd($ids);
  return implode(' | ', $batches);
}

function student($ids)
{
  $students = \App\Models\Student::whereIn('id', json_decode($ids))->pluck('name')->toArray();
  // dd($ids);
  return implode(' | ', $students);
}

@endphp

@extends('dashboard')
@section('title', 'View SMS')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>SMS</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">SMS</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of SMS</h3>
              <div class="box-tools">
                <a href="{{route('sms.create')}}" class="btn btn-sm btn-info"><i class="fa fa-send"></i> Type SMS</a>
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
                  <th>Title</th>
                  <th>Details</th>
                  <th>Students</th>
                  <th>Batches</th>
                  <th>Created At</th>
                  <th>Status</th>
                  <th width="130">Action</th>
                </tr>
                @foreach($smses as $value)
                <tr>
                  <td>{{$value->id}}</td>
                  <td>{{$value->name}}</td>
                  <td>{{$value->details}}</td>
                  <td>{{student($value->student_id)}}</td>
                  <td>{{batch($value->batch_id)}}</td>
                  <td>{{$source->dtformat($value->created_at)}}</td>
                  <td>
                    @if($value->status == 'Sent')
                    <span class="label label-success">{{$value->status}}</span>
                    @elseif($value->status == 'Scheduled')
                    <span class="label label-info">{{$value->status}}</span>
                    <br>
                    <small>{{$source->dtformat($value->datetime)}}</small>
                    @else
                    <span class="label label-warning">{{$value->status}}</span>
                    @endif
                  </td>
                  <td>
                    {{-- <a href="{{route('sms.show', $value->id)}}" class="label label-info" title="Details"><i class="fa fa-file-text"></i></a> --}}
                    <a href="{{route('sms.edit', $value->id)}}" class="btn btn-warning btn-sm" title="Edit"><i class="fa fa-edit"></i></a>
                    <form style="display: inline" action="{{route('sms.destroy', $value->id)}}" method="POST">
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
                {{$smses->links()}}
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