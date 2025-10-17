@extends('dashboard')
@section('title', 'Show Routine Item')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
<style>
  .text-right{text-align: right}
  .table-responsive{overflow-x: scroll; max-height: 600px}
</style>
@endsection
@section('content')
<section class="content-header">
  <h1>Show a Routine Item</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Routines</a></li>
    <li class="active">Show Routine</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="box box-info">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">{{$routine->name}}</h3>
            <div class="toolbar-icon pull-right text-right">
              
              <a href="{{route('routine.add-item', $routine->id)}}" title="add" class="label label-info"><i class="fa fa-plus"></i></a>
              <a href="{{route('routine.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>
            </div>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered">
            <tr>
              <th>ID</th>
              <th>Exam Name/No</th>
  
              @foreach($subjects as $subject)
              <th>{{$subject->name}}</th>
              @endforeach
  
              @foreach($departments as $department)
              <th>{{$department->name}}</th>
              @endforeach
  
              <th>Date & Time</th>
              <th>Action</th>
            </tr>
  
            @foreach($routine_rows as $key => $item)
            <tr>
              <td>{{$item->id}}</td>
              <td>{{$item->paper_name}}</td>
  
              @foreach($item->subjects as $subject)
              <td>{!!$subject->chapter!!}</td>
              @endforeach
  
              @foreach($item->departments as $department)
              <td>{!!$department->details!!}</td>
              @endforeach
  
              <td>{{$item->exam_at}}</td>
              <td width="70">
                <a class="btn btn-warning btn-xs" href="{{route('routine.item.edit', $item->id)}}">
                  <i class="fa fa-pencil"></i>
                </a>
  
                <a class="btn btn-danger btn-xs" href="{{route('routine.item.delete', $item->id)}}" onclick="return confirm('Are you sure, you want to delete this?')">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
            @endforeach
  
          </table>
        </div>
      </div>
      <div class="clearfix"></div>
    </div>
  </div>
</section> <!-- /.content -->
@endsection
@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
<script>

    // on change course get batches
    function getBatches(e)
    {
        let ids = Array.from(e.selectedOptions).map(({value}) => value);
    
        $.ajax({
            type: 'GET', //THIS NEEDS TO BE GET
            url: '/get_batches/' + ids,
            success: function (data) {
    
                var obj = JSON.parse(JSON.stringify(data));
                var options = '<option value="">Select One</option>';
    
                $.each(obj['data'], function (key, val) {
                    options += '<option value="'+val.id+'">'+val.name+'</option>';
                });
    
                if(options != ""){
                    $("#batch_id").html(options)
                }else{
                    $("#batch_id").html('')
                }
            },
            error: function(data) { 
                    console.log('data error');
            }
        });
    }
    
    // on change batch get departments
    function getDepartments(e)
    {
        let ids = Array.from(e.selectedOptions).map(({value}) => value);
    
        $.ajax({
            type: 'GET', //THIS NEEDS TO BE GET
            url: '/get_departments_by_batch/' + ids,
            success: function (data) {
    
                var obj = JSON.parse(JSON.stringify(data));
                var options = '<option value="">Select One</option>';
    
                $.each(obj['data'], function (key, val) {
                    options += '<option value="'+val.id+'">'+val.name+'</option>';
                });
    
                if(options != ""){
                    $("#department_id").html(options)
                }else{
                    $("#department_id").html('')
                }
            },
            error: function(data) { 
                    console.log('data error');
            }
        });
    }
    
    //this script for text editor
    $(document).ready(function() {
        $('.editor').summernote({
          toolbar: [['view', ['fullscreen', 'codeview']]],
            height: 100
        });
    });

    //select 2
    $(function(){$('.select2').select2();});
</script>
@endsection