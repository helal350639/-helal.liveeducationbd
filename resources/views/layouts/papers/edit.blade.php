@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$value = $paper;

// $deptId = [];

// if($paper->department)
// {
//     array_push($deptIds, $val->id);
// }
@endphp

@extends('dashboard')
@section('title', 'Edit Question Paper')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
<section class="content-header">
  <h1>Edit Question Paper</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Question Papers</a></li>
    <li class="active">Edit Question Paper</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-12"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Question Paper Details</h3>
            <div class="text-right toolbar-icon pull-right" style="display: inline">
                <a href="{{route('paper.create')}}" title="Add" class="label label-info"><i class="fa fa-plus"></i></a>
                <a href="{{route('paper.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>
                <a href="{{route('paper.show', $paper->id)}}" class="label label-primary" title="Show"><i class="fa fa-file-text"></i></a>
            </div>
        </div>
        <form action="{{route('paper.update', $value)}}" method="POST" enctype="multipart/form-data">
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
                <div class="col-md-3">
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
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="header">Header</label>
                        <textarea class="form-control editor" name="header" id="header" rows="5" required>{{$value->header}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control" name="details" id="details" rows="4">{{$value->details}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="banner">Banner</label>
                        <input type="file" class="form-control" name="banner" id="banner" onchange="showImg(this)" />
                    </div>
                    <p style="color:red;padding:5px 0">Image size: 8:1 / 800px X 100px</p>
                    <img src="{{$value->banner}}" alt="" style="max-width:600px"><hr>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Question Paper No.</label>
                        <input type="text" class="form-control" name="name" id="name" required value="{{$value->name}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max">Questions Limit</label>
                        <input type="number" class="form-control" name="max" id="max" value="{{$value->max}}" >
                    </div>
                </div>
                <div class="col-md-6 no-padding">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status" id="status" onchange="Status(this)">
                                <option value="">Select One</option>
                                <option value="Unpublished" {{$value->status == 'Unpublished'? 'selected':''}}>Unpublished</option>
                                <option value="Published" {{$value->status == 'Published'? 'selected':''}}>Published</option>
                                <option value="Scheduled" {{$value->status == 'Scheduled'? 'selected':''}}>Scheduled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 no-padding">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="open">Exam Start Time</label>
                                <input type="datetime-local" class="form-control" name="open" id="open" value="{{$value->open}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="close">Exam Finished Time</label>
                                <input type="datetime-local" class="form-control" name="close" id="close" value="{{$value->close}}" required>
                            </div>
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
                        <label for="time">Exam Running Time (in minute)</label>
                        <input type="number" class="form-control" name="time" id="time" required value="{{$value->time}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="mark">Mark (For Correct Answer)</label>
                        <input type="number" class="form-control" name="mark" id="mark" required value="{{$value->mark}}" step="0.01">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="minus">Mark (Negative for wrong Answer)</label>
                        <input type="number" class="form-control" name="minus" id="minus" value="{{$value->minus}}" step="0.01">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exam_limit">How many times can a student take the exam?</label>
                        <select class="form-control" name="exam_limit" id="exam_limit" >
                            <option value="">Select One</option>
                            <option value="1" {{$value->exam_limit == '1'? 'selected': ''}}>One Time</option>
                            <option value="2" {{$value->exam_limit == '2'? 'selected': ''}}>Two Time</option>
                            <option value="3" {{$value->exam_limit == '3'? 'selected': ''}}>Three Time</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="random">Show Random Questions?</label>
                        <select class="form-control" name="random" id="random" >
                            <option value="">Select One</option>
                            <option value="Yes" {{$value->random == 'Yes'? 'selected': ''}}>Yes</option>
                            <option value="No" {{$value->random == 'No'? 'selected': ''}}>No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="display">Display Question?</label>
                        <select class="form-control" name="display" id="display" >
                            <option value="">Select One</option>
                            <option value="All" {{$value->display == 'All'? 'selected': ''}}>All in One Paper</option>
                            <option value="One" {{$value->display == 'One'? 'selected': ''}}>One by One</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="permit">Exam Permission For Candidates?</label>
                        <select class="form-control" name="permit" id="permit" >
                            <option value="">Select One</option>
                            <option value="Batch" {{$value->permit == 'Batch'? 'selected': ''}}>Batch</option>
                            <option value="Department" {{$value->permit == 'Department'? 'selected': ''}}>Department</option>
                            <option value="Every One" {{$value->permit == 'Every One'? 'selected': ''}}>Every One</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="paid">Paid exam?</label>
                        <select class="form-control" name="paid" id="paid" required>
                            <option value="">Select One</option>
                            <option value="Yes" {{$value->paid == 'Yes'? 'selected': ''}}>Yes</option>
                            <option value="No" {{$value->paid == 'No'? 'selected': ''}}>No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Send Result to the Email?</label>
                        <select class="form-control" name="email" id="email" >
                            <option value="">Select One</option>
                            <option value="Yes" {{$value->email == 'Yes'? 'selected': ''}}>Yes</option>
                            <option value="No" {{$value->email == 'No'? 'selected': ''}}>No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="result_view">Student Can View Result After Exam?</label>
                        <select class="form-control" name="result_view" id="result_view" onchange="resultView(this)" required>
                            <option value="">Select One</option>
                            <option value="Yes" {{$value->result_view == 'Yes'? 'selected': ''}}>Yes</option>
                            <option value="No" {{$value->result_view == 'No'? 'selected': ''}}>No</option>
                        </select>
                    </div>
                    <div class="col-md-12 no-padding" id="result_message" style="display: none">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="candidate">Max Candidates</label>
                        <input type="number" class="form-control" name="candidate" id="candidate" value="{{$value->candidate}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="message">Message After Exam</label>
                        <textarea class="form-control" name="message" id="message" rows="3">{{$value->message}}</textarea>
                    </div>
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
        let deptId = '{{$paper->batch_id}}';
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
        let deptId = '{{$paper->department_id}}';
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

// on upload show image
    function showImg(e)
    {
        if(e.parentNode.firstElementChild.tagName == 'IMG')
        {
            e.parentNode.firstElementChild.src = window.URL.createObjectURL(e.files[0]);
        }
        else
        {
            let img = document.createElement('img');
            img.src = window.URL.createObjectURL(e.files[0]);
            img.setAttribute('style', 'width:100%;max-width:400px; margin-top:15px');
            img.alt = '';
            e.parentNode.appendChild(img);
        }
        
    }

    //result view add setting
    function resultView(e)
    {
        let result_message = document.getElementById('result_message');
        console.log(e.options[e.selectedIndex].value == 'No');
        if(e.options[e.selectedIndex].value == 'No')
        {
            result_message.innerHTML = ''+
            '<div class="form-group">'+
                '<label for="result_message">Result Publish Date & Time</label>'+
                '<input type="datetime-local" class="form-control" name="result_at" id="result_at" required onchange="setTime(this)" value="{{$value->result_at}}">'+
            '</div>';
            // '<div class="form-group">'+
            //     '<label for="result_message">Write short message instead of result publish</label>'+
            //     '<textarea type="text" class="form-control" name="result_message" id="result_message" required rows="3">{{$value->result_message}}</textarea>'+
            // '</div>';
            result_message.style.display = 'block';
        }
        else
        {
            result_message.innerHTML = '';
            result_message.style.display = 'none';
        }
    }
    resultView(document.getElementById('result_view'));
    // set result publish time to message
    // function setTime(e)
    // {
    //     // console.log(e.value);
    //     // let message_field = document.getElementById('result_message');
    //     document.getElementById('result_message').value = 'ফলাফল প্রকাশিত হবে ';
    // }
    // function Status(e){
    //     let timer = e.parentNode.parentNode.nextElementSibling;
    //     if(e.options[e.selectedIndex].value == 'Scheduled')
    //     {
    //         timer.classList.remove('hide');
    //         timer.innerHTML = '<div class="col-md-6">'+
    //                         '<div class="form-group">'+
    //                             '<label for="open">Publish Time</label>'+
    //                             '<input type="datetime-local" class="form-control" name="open" id="open" value="{{$value->open}}">'+
    //                         '</div>'+
    //                     '</div>'+
    //                     '<div class="col-md-6">'+
    //                         '<div class="form-group">'+
    //                             '<label for="close">Close Time</label>'+
    //                             '<input type="datetime-local" class="form-control" name="close" id="close" value="{{$value->close}}">'+
    //                         '</div>'+
    //                     '</div>';
    //     }
    //     else
    //     {
    //         timer.innerHTML = '';
    //         timer.classList.add('hide');
    //     }
    // }

    // onload call to the status change
    // Status(document.getElementById('status'));

    //this script for text editor
    $(document).ready(function() {
        $('.editor').summernote({
            height: 150
        });
    });

    // getsubcats(elm);
</script>
@endsection