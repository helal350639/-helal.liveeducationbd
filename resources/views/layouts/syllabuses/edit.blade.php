@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$value = $syllabus;
@endphp

@extends('dashboard')
@section('title', 'Edit Syllabus')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
<section class="content-header">
  <h1>Edit Syllabus</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Syllabuses</a></li>
    <li class="active">Edit Syllabus</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-12"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Syllabus Details</h3>
            <div class="text-right toolbar-icon pull-right" style="display: inline">
                <a href="{{route('syllabus.create')}}" title="Add" class="label label-info"><i class="fa fa-plus"></i></a>
                <a href="{{route('syllabus.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>
                <a href="{{route('syllabus.view', $syllabus->id)}}" class="label label-primary" title="view"><i class="fa fa-file-text"></i></a>
                <form style="display: inline" action="{{route('syllabus.destroy', $syllabus->id)}}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this one?')"><i class="fa fa-trash"></i></button>
                </form>
            </div>
        </div>
        <form action="{{route('syllabus.update', $value)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="box-body">
                <div class="col-md-4">
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
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Department</label>
                        <select name="department_id" id="department_id" class="form-control select2">
                            <option value="">Select One</option>
                            @foreach($departments as $val)
                            <option value="{{$val->id}}" {{$value->department_id == $val->id? 'selected': ''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name">Syllabus Name</label>
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
                        <label for="details">Details</label>
                        <textarea class="form-control ckeditor" name="details" id="details" rows="4">{{$value->details}}</textarea>
                    </div>
                </div>
                <div class="col-md-6 no-padding">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status" id="status" onchange="Status(this)" required>
                                <option value="">Select One</option>
                                <option value="Unpublished" {{$value->status == 'Unpublished'? 'selected':''}}>Unpublished</option>
                                <option value="Published" {{$value->status == 'Published'? 'selected':''}}>Published</option>
                                <option value="Scheduled" {{$value->status == 'Scheduled'? 'selected':''}}>Scheduled</option>
                            </select>
                        </div>
                    </div>
                    {{-- <div class="col-md-12 no-padding hide">
                        
                    </div> --}}
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="">Date & Time</label>
                        <input type="datetime-local" class="form-control" name="datetime" id="datetime" value="{{$value->datetime}}">
                      </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="format">MCQ List Format</label>
                        <select class="form-control" name="format" id="format" >
                            <option value="">Select One</option>
                            @foreach($source->mcqlist() as $key => $val)
                            <option value="{{$key}}" {{$value->format == $key? 'selected': ''}}>{{implode(', ', $val)}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sample_pdf">Sample PDF</label>
                        <input type="file" name="sample_pdf" class="form-control">
                    </div>
                    @if($value->sample_pdf)
                    <a target="_blank" href="{{$value->sample_pdf}}"><i class="fa fa-file"></i> Sample PDF</a>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="pdf">Full PDF</label>
                        <input type="file" name="pdf" class="form-control">
                    </div>
                    @if($value->pdf)
                    <a target="_blank" href="{{$value->pdf}}"><i class="fa fa-file"></i> Full PDF</a>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="routine">Routine</label>
                        <input type="file" name="routine" class="form-control">
                    </div>
                    @if($value->routine)
                    <a target="_blank" href="{{$value->routine}}"><i class="fa fa-file"></i> Routine</a>
                    @endif
                </div>
            </div> <!-- /.box body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right" onclick="formCheck(this)"><i class="fa fa-save"></i> Save</button>
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

//result view add setting
function resultView(e)
{
    // console.log(e.options[e.selectedIndex].value == 'No');
    if(e.options[e.selectedIndex].value == 'No')
    {
        let msg = document.createElement('div');
        msg.classList.add('col-md-6');
        msg.innerHTML = '<div class="form-group">'+
                                '<label for="message">Message After Exam</label>'+
                                '<input type="text" class="form-control" name="message" id="message" value="">'+
                            '</div>';

        e.parentNode.parentNode.nextElementSibling = msg;
    }
}

function Status(e)
{
  const datetime = document.getElementById('datetime');
  if(e.options[e.selectedIndex].value == 'Scheduled')
  {
    datetime.setAttribute('required', 'required');
  }
  else
  {
    datetime.removeAttribute('required');
  }
      // let timer = e.parentNode.parentNode.nextElementSibling;
      // if(e.options[e.selectedIndex].value == 'Scheduled')
      // {
      //     timer.classList.remove('hide');
      //     timer.innerHTML = '<div class="col-md-6">'+
      //                     '<div class="form-group">'+
      //                         '<label for="open">Publish Time</label>'+
      //                         '<input type="datetime-local" class="form-control" name="open" id="open" value="{{$value->open}}">'+
      //                     '</div>'+
      //                 '</div>'+
      //                 '<div class="col-md-6">'+
      //                     '<div class="form-group">'+
      //                         '<label for="close">Close Time</label>'+
      //                         '<input type="datetime-local" class="form-control" name="close" id="close" value="{{$value->close}}">'+
      //                     '</div>'+
      //                 '</div>';
      // }
      // else
      // {
      //     timer.innerHTML = '';
      //     timer.classList.add('hide');
      // }
    }

    // onload call to the status change
    Status(document.getElementById('status'));

    function getsubcats(elm){

        var catid = elm.options[elm.options.selectedIndex].value;

        $.ajax({
            type: 'GET', //THIS NEEDS TO BE GET
            url: '/get_sub_cats/'+catid,
            success: function (data) {

                var obj = JSON.parse(JSON.stringify(data));
                var sub_cat_html = "";

                $.each(obj['subcats'], function (key, val) {
                   sub_cat_html += "<option value="+val.id+">"+val.name+"</option>";
                });

                if(sub_cat_html != ""){
                    $("#sub_cat").html('<option value="">Select SubCategory</option>'+sub_cat_html)
                }else{
                    $("#sub_cat").html('<option value="">No SubCategory</option>')
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

    // getsubcats(elm);
</script>
@endsection