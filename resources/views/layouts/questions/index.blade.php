@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Questions')

@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection

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
    <div class="box box-body">
      <form action="{{route('question.index')}}" method="GET">
        @csrf
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <select name="subject_id" id="" class="form-control">
                <option value="">Select One</option>
                @foreach($subjects as $subject)
                <option value="{{$subject->id}}">{{$subject->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <input type="text" name="title" class="form-control" placeholder="Question Title">
            </div>
          </div>
          <div class="col-md-4">
            <div class="input-group-btn">
              <button type="submit" class="btn btn-info">
                <i class="fa fa-search"></i> Search
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Question</h3>
              <div class="box-tools">
                <a href="{{route('question.create')}}" class="btn btn-info">
                  <i class="fa fa-plus"></i> Add Question
                </a>
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
                  <td ondblclick="QTitleEdit(this)" data-id="{{$mcq->id}}">{{ $mcq->title }}</td>
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
@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
  <script>
    function QTitleEdit(e)
    {
      let form = '<div class="input-group">'+
          '<textarea class="form-control editor">'+e.innerHTML+'</textarea>'+
          '<span class="input-group-addon" onclick="saveTitle(this)">'+
            '<i class="fa fa-check text-success"></i>'+
          '</span>'+
        '</div>';

      e.innerHTML = form;

      $('.editor').summernote({
            height: 50,
            toolbar: [
              ['view', ['codeview']]
            ]
        });
    }

    function saveTitle(e)
    {
      let parent = e.parentNode.parentNode;
      let id = parent.dataset.id;
      let title = parent.firstElementChild.firstElementChild.value;
      $.ajax({
        type: 'GET',
        url: '{{route("question.title.save")}}',
        data: {id:id, title: title},
        success: function(data){
          parent.innerHTML = title
        },
        error:function(data){
          console.error(data);
        }
      });
    }

    //this script for text editor
    $(document).ready(function() {
      $('.editor').summernote({
        height: 150
      });
    });

    // $(function () {
    //   $('#example1').DataTable()
    //   $('#example2').DataTable({
    //     'paging'      : true,
    //     'lengthChange': false,
    //     'searching'   : false,
    //     'ordering'    : true,
    //     'info'        : true,
    //     'autoWidth'   : false
    //   })
    // })
  </script>
@endsection