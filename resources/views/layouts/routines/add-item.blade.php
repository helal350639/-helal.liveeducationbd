@extends('dashboard')
@section('title', 'Add New Routine')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
<section class="content-header">
  <h1>Add a Routine Item</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Routines</a></li>
    <li class="active">Add Routine</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-12"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Routine Details</h3>
        </div>
        <form action="{{route('routine.add-item.store')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="routine_id" value="{{$routine->id}}">
            <div class="box-body">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="name">Exam Name/No</label>
                        <select class="form-control select2" name="paper_id" id="paper_id" required>
                            <option value="">Select One</option>
                            @foreach($papers as $val)
                            <option value="{{$val->id}}">{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="">Subject</label>
                        <select name="subject_id" id="subject_id" class="form-control select2" required>
                            <option value="">Select One</option>
                            @foreach($subjects as $val)
                            <option value="{{$val->id}}">{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="">Department</label>
                        <select name="department_id" id="department_id" class="form-control select2">
                            <option value="">Select One</option>
                            @foreach($departments as $val)
                            <option value="{{$val->id}}">{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" class="form-control" name="date" id="date" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="chapter">Chapter Details</label>
                        <textarea class="form-control editor" name="chapter" id="chapter" rows="5"></textarea>
                    </div>
                </div>
            </div> <!-- /.box body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save</button>
            </div>
        </form>
      </div> <!-- /.box -->
    </div> <!--/.col (left) -->
  </div> <!-- /.row -->
  <div class="row">
    <div class="col-md-12">
      <div class="box box-info">
        <table class="table">
          <tr>
            <th>ID</th>
            <th>Exam Name/No</th>
            <th>Subject</th>
            <th>Chapter</th>
            <th>Department</th>
            <th>Date</th>
          </tr>

          @foreach($items as $item)
          <tr>
            <td>{{$item->id}}</td>
            <td>{{$item->paper_id}}</td>
            <td>{{$item->subject_id}}</td>
            <td>{!!$item->chapter!!}</td>
            <td>{{$item->department_id}}</td>
            <td>{{$item->date}}</td>
          </tr>
          @endforeach

        </table>
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
            height: 100
        });
    });

    //select 2
    $(function(){$('.select2').select2();});
</script>
@endsection