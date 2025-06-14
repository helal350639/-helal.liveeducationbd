@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$value = $routine;

// $subjects = $subject_columns;
// // dd($subjects);

function checkSubjectIn($id, $subject_columns)
{
  if(in_array($id, $subject_columns))
  {
    return 'selected';
  }

}
@endphp

@extends('dashboard')
@section('title', 'Edit Routine')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
<section class="content-header">
  <h1>Edit Routine</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Routinees</a></li>
    <li class="active">Edit Routine</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-12"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Routine Details</h3>
            <div class="text-right toolbar-icon pull-right" style="display: inline">
                <a href="{{route('routine.create')}}" title="Add" class="label label-info"><i class="fa fa-plus"></i></a>
                <a href="{{route('routine.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>
            </div>
        </div>
        <form action="{{route('routine.update', $value)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="box-body">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="">Course</label>
                        <select name="course_id" id="course_id" class="form-control select2" onchange="getBatches(this)">
                            <option value="">Select One</option>
                            @foreach($courses as $val)
                            <option value="{{$val->id}}" {{$value->course_id == $val->id? 'selected': ''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="">Batch</label>
                        <select name="batch_id" id="batch_id" class="form-control select2" onchange="getDepartments(this)">
                            <option value="">Select One</option>
                            @foreach($batches as $val)
                            <option value="{{$val->id}}" {{$value->batch_id == $val->id? 'selected': ''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Routine Name</label>
                        <input type="text" class="form-control" name="name" id="name" required value="{{$value->name}}">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="header">Header</label>
                        <textarea class="form-control editor" name="header" id="header" rows="5">{{$value->header}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label for="subject">Subject</label>
                    <select class="form-control select2" multiple name="subjects[]" id="subjects" required>
                      @foreach($subjects as $val)
                      <option value="{{$val->id}}" {{checkSubjectIn($val->id, [])}}>{{$val->name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label for="department">Department</label>
                    <select class="form-control select2" multiple name="departments[]" id="departments" required>
                      @foreach($departments as $val)
                      <option value="{{$val->id}}">{{$val->name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="pdf">Full PDF</label>
                        <input type="file" name="pdf" class="form-control">
                    </div>
                    @if($value->pdf)
                    <a target="_blank" href="{{$value->pdf}}"><i class="fa fa-file"></i> Full PDF</a>
                    @endif
                </div>
                <div class="col-md-4 no-padding">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status" id="status" onchange="Status(this)">
                                <option value="">Select One</option>
                                <option value="Unpublished" {{$value->status == 'Unpublished'? 'selected':''}}>Unpublished</option>
                                <option value="Published" {{$value->status == 'Published'? 'selected':''}}>Published</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 no-padding hide">
                        
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
</section> <!-- /.content -->
@endsection

@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
<script type="text/javascript">

    // on change course get batches
    function getBatches(e)
    {
        function check(id)
        {
            let deptId = '{{$value->batch_id}}';
            if(deptId == id)
            {
                return 'selected';
            }
            return '';
        }

        let ids = Array.from(e.selectedOptions).map(({value}) => value);
    
        $.ajax({
            type: 'GET', //THIS NEEDS TO BE GET
            url: '/get_batches/' + ids,
            success: function (data) {
    
                var obj = JSON.parse(JSON.stringify(data));
                var options = '<option value="">Select One</option>';
    
                $.each(obj['data'], function (key, val) {
                    options += '<option value="'+val.id+'" '+check(val.id)+'>'+val.name+'</option>';
                });
    
                if(options != ""){
                    $("#batch_id").html(options)
                }else{
                    $("#batch_id").html('')
                    $("#department_id").html('')
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
        function check(id)
        {
            let deptId = '{{$value->department_id}}';
            if(deptId == id)
            {
                return 'selected';
            }
            return '';
        }

        let ids = Array.from(e.selectedOptions).map(({value}) => value);
    
        $.ajax({
            type: 'GET', //THIS NEEDS TO BE GET
            url: '/get_departments_by_batch/' + ids,
            success: function (data) {
    
                var obj = JSON.parse(JSON.stringify(data));
                var options = '<option value="">Select One</option>';
    
                $.each(obj['data'], function (key, val) {
                    options += '<option value="'+val.id+'" '+check(val.id)+'>'+val.name+'</option>';
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
            height: 150
        });
    });

    //select 2
    $(function(){$('.select2').select2();});
</script>
@endsection