@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Questions')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Questions</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Questions</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Question</h3>
              <div class="box-tools">
                <a href="{{route('question.create')}}" class="btn btn-info">
                  <i class="fa fa-plus"></i> Add Question
                </a>
                <form class="form-inline" style="display: inline;" action="{{route('question.search')}}" method="post">
                  @csrf
                <div class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">
                  <input type="text" name="search" class="form-control pull-right" placeholder="Search" required>

                  <div class="input-group-btn">
                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                  </div>
                </form>
                </div>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="example1" class="table table-bordered table-hover">
                <tr>
                  <th>Id</th>
                  <th>Title</th>
                  <th>Type</th>
                  <th>Chapter</th>
                  <th>Subject</th>
                  {{-- <th>Semester</th>
                  <th>Department</th>
                  <th>Course</th> --}}
                  <th>Status</th>
                  <th width="130">Action</th>
                </tr>
                @foreach($mcqs as $mcq)
                <tr>
                  <td>{{$mcq->id}}</td>
                  <td>{{ $mcq->title }}</td>
                  <td>{{ $mcq->type }}</td>
                  <td>
                    @foreach($mcq->chapters as $val)
                    <label class="label label-primary"> {{$val->name}}</label>
                    @endforeach
                  </td>
                  <td>
                    @foreach($mcq->subjects as $val)
                    <label class="label label-primary"> {{$val->name}}</label>
                    @endforeach
                  </td>
                  {{-- <td>
                    @foreach($mcq->semesters as $val)
                    <label class="label label-primary"> {{$val->name}}</label>
                    @endforeach
                  </td>
                  <td>
                    @foreach($mcq->departments as $val)
                    <label class="label label-primary"> {{$val->name}}</label>
                    @endforeach
                  </td>
                  <td>
                    @foreach($mcq->courses as $val)
                    <label class="label label-primary"> {{$val->name}}</label>
                    @endforeach
                  </td> --}}
                  <td>
                    @if($mcq->status == 'Active')
                    <span class="label label-success">Active</span>
                    @elseif($mcq->status == 'Inactive')
                    <span class="label label-warning">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{route('question.show', $mcq->id)}}" class="btn btn-info" title="Details"><i class="fa fa-file-text"></i></a>
                    <a href="{{route('question.edit', $mcq->id)}}" class="btn btn-warning btn-sm" title="Edit this MCQ"><i class="fa fa-edit"></i></a>
                    <form style="display: inline" action="{{route('question.destroy', $mcq->id)}}" method="POST">
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
                {{$mcqs->links()}}
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