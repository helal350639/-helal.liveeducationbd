@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Chapter')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Chapters</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Chapter</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Chapter</h3>
              <div class="box-tools">
                <a href="{{route('chapter.create')}}" class="btn btn-sm btn-info">
                  <i class="fa fa-plus"></i> Add
                </a>
                <form action="{{route('chapter.index')}}" method="GET" class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">
                  <input type="text" name="search" class="form-control pull-right" placeholder="Chapter Name" value="{{request('search')}}">

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
                  <th>Chapter Name</th>
                  <th>Subject Name</th>
                  {{-- <th>Semester Name</th>
                  <th>Department Name</th>
                  <th>Course Name</th> --}}
                  <th>Details</th>
                  <th>Status</th>
                  <th width="130">Action</th>
                </tr>
                @foreach($chapters as $val)
                <tr>
                  <td>{{$val->id}}</td>
                  <td>{{$val->name}}</td>
                  <td>
                    @foreach($val->subjects as $value)
                    <label class="label label-primary">{{$value->name}}</label>
                    @endforeach
                  </td>
                  {{-- <td>
                    @foreach($val->semesters as $value)
                    <label class="label label-primary">{{$value->name}}</label>
                    @endforeach
                  </td>
                  <td>
                    @foreach($val->departments as $value)
                    <label class="label label-primary">{{$value->name}}</label>
                    @endforeach
                  </td>
                  <td>
                    @foreach($val->courses as $value)
                    <label class="label label-primary">{{$value->name}}</label>
                    @endforeach
                  </td> --}}
                  <td>{{$val->details}}</td>
                  <td>
                    @if($val->status == 'Active')
                    <span class="label label-success">Active</span>
                    @elseif($val->status == 'Inactive')
                    <span class="label label-warning">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{route('chapter.show', $val->id)}}" class="btn btn-info btn-sm" title="Details"><i class="fa fa-file-text"></i></a>
                    <a href="{{route('chapter.edit', $val->id)}}" class="btn btn-warning btn-sm" title="Edit this"><i class="fa fa-edit"></i></a>
                    <form style="display: inline" action="{{route('chapter.destroy', $val->id)}}" method="POST">
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
                {{$chapters->links()}}
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