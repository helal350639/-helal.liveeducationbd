@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'Add New Question Paper')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
<section class="content-header">
  <h1>Add a Question Paper</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Question Papers</a></li>
    <li class="active">Add Question Paper</li>
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
                <a href="{{route('paper.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>
              </div>
        </div>
        <form action="{{route('paper.store')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="box-body">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="">Course</label>
                        <select name="course_id" id="course_id" class="form-control select2" onchange="getBatches(this)">
                            <option value="">Select One</option>
                            @foreach($courses as $val)
                            <option value="{{$val->id}}">{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="">Batch</label>
                        <select name="batch_id" id="batch_id" class="form-control select2" onchange="getDepartments(this); getRoutineExams(this)">
                        <option value="">Select One</option>
                        </select>
                    
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="">Department</label>
                        <select name="department_id" id="department_id" class="form-control select2">
                            <option value="">Select One</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="header">Header</label>
                        <textarea class="form-control editor" name="header" id="header" rows="5" required></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control" name="details" id="details" rows="4"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="banner">Banner</label>
                        <input type="file" class="form-control" name="banner" id="banner" onchange="showImg(this)" />
                    </div>
                    <p style="color:red;padding:5px 0">Image size: 8:1 / 800px X 100px</p>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Question Paper No.</label>
                        <select
    class="form-control select2"
    name="name"
    id="name"
    required
    disabled
>
    <option value="">Select Course & Batch First</option>
</select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max">Questions Limit</label>
                        <input type="number" class="form-control" name="max" id="max" >
                    </div>
                </div>
                <div class="col-md-6 no-padding">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status" id="status" onchange="Status(this)">
                                <option value="">Select One</option>
                                <option value="Unpublished">Unpublished</option>
                                <option value="Published">Published</option>
                                <option value="Scheduled">Scheduled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 no-padding hide">
                        
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="format">MCQ List Format</label>
                        <select class="form-control" name="format" id="format" >
                            <option value="">Select One</option>
                            @foreach($source->mcqlist() as $key => $val)
                            <option value="{{$key}}">{{implode(', ', $val)}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- <div class="col-md-6">
                    <div class="form-group">
                        <label for="time">Time (in minute)</label>
                        <input type="number" class="form-control" name="time" id="time" required >
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="mark">Mark (For Correct Answer)</label>
                        <input type="number" class="form-control" name="mark" id="mark" required step="0.01">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="minus">Mark (Negative for wrong Answer)</label>
                        <input type="number" class="form-control" name="minus" id="minus" step="0.01">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="result_view">Student Can View Result After Exam?</label>
                        <select class="form-control" name="result_view" id="result_view" >
                            <option value="">Select One</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exam_limit">How many times can a student take the exam?</label>
                        <select class="form-control" name="exam_limit" id="exam_limit" >
                            <option value="">Select One</option>
                            <option value="1">One Time</option>
                            <option value="2">Two Time</option>
                            <option value="3">Three Time</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="random">Show Random Questions?</label>
                        <select class="form-control" name="random" id="random" >
                            <option value="">Select One</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="display">Display Question?</label>
                        <select class="form-control" name="display" id="display" >
                            <option value="">Select One</option>
                            <option value="All">All in One Paper</option>
                            <option value="One">One by One</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="permit">Who can exam?</label>
                        <select class="form-control" name="permit" id="permit" >
                            <option value="">Select One</option>
                            <option value="No">Every One</option>
                            <option value="Yes">Authenticated</option>
                        </select>
                    </div>
                </div> --}}
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


// ============================================================
// ON CHANGE COURSE → GET BATCHES
// ============================================================

function getBatches(e)
{
    let ids = Array.from(
        e.selectedOptions
    ).map(({value}) => value);


    $.ajax({

        type: 'GET',

        url: '/get_batches/' + ids,

        success: function (data) {

            var obj =
                JSON.parse(
                    JSON.stringify(data)
                );


            var options =
                '<option value="">Select One</option>';


            $.each(
                obj['data'],
                function (key, val)
                {

                    options +=
                        '<option value="' +
                        val.id +
                        '">' +
                        val.name +
                        '</option>';

                }
            );


            if(options != "")
            {

                $("#batch_id").html(
                    options
                );

            }
            else
            {

                $("#batch_id").html('');

            }

        },


        error: function(data)
        {

            console.log('data error');

        }

    });
}



// ============================================================
// GET ROUTINE EXAM NO. BY BATCH
// ============================================================

function getRoutineExams(e)
{

    let batch_id = e.value;


    // Disable Exam dropdown while loading

    $('#name').prop(
        'disabled',
        true
    );


    $('#name').html(
        '<option value="">Loading Exams...</option>'
    );


    // If Batch is empty

    if(!batch_id)
    {

        $('#name').html(
            '<option value="">Select Course & Batch First</option>'
        );


        return;

    }



    $.ajax({

        type: 'GET',

        url: '/get-routine-exams/' + batch_id,


        success: function(data)
        {
        console.log('ROUTINE EXAM RESPONSE:', data);
        console.log('BATCH ID:', batch_id);
            var options =
                '<option value="">Select Exam No.</option>';
                

            // Check whether exams exist

            if(
                data.exams &&
                data.exams.length > 0
            )
            {

                $.each(
                    data.exams,
                    function(key, val)
                    {

                        options +=
                            '<option value="' +
                            val.name +
                            '">' +
                            val.name +
                            '</option>';

                    }
                );


                $('#name').html(
                    options
                );


                $('#name').prop(
                    'disabled',
                    false
                );

            }
            else
            {

                $('#name').html(
                    '<option value="">No Exam Found in Routine</option>'
                );


                $('#name').prop(
                    'disabled',
                    true
                );

            }


            // Refresh Select2

            $('#name').trigger(
                'change.select2'
            );

        },


        error: function(xhr)
        {

            console.log(
                xhr.responseText
            );


            $('#name').html(
                '<option value="">Unable to Load Exams</option>'
            );


            $('#name').prop(
                'disabled',
                true
            );


            $('#name').trigger(
                'change.select2'
            );

        }

    });

}



// ============================================================
// ON CHANGE BATCH → GET DEPARTMENTS
// ============================================================

function getDepartments(e)
{

    let ids = Array.from(
        e.selectedOptions
    ).map(({value}) => value);


    $.ajax({

        type: 'GET',

        url: '/get_departments_by_batch/' + ids,


        success: function (data) {

            var obj =
                JSON.parse(
                    JSON.stringify(data)
                );


            var options =
                '<option value="">Select One</option>';


            $.each(
                obj['data'],
                function (key, val)
                {

                    options +=
                        '<option value="' +
                        val.id +
                        '">' +
                        val.name +
                        '</option>';

                }
            );


            if(options != "")
            {

                $("#department_id").html(
                    options
                );

            }
            else
            {

                $("#department_id").html('');

            }

        },


        error: function(data)
        {

            console.log('data error');

        }

    });

}



// ============================================================
// ON UPLOAD → SHOW IMAGE
// ============================================================

function showImg(e)
{

    if(
        e.parentNode
        .firstElementChild
        .tagName == 'IMG'
    )
    {

        e.parentNode
        .firstElementChild
        .src =
            window.URL.createObjectURL(
                e.files[0]
            );

    }
    else
    {

        let img =
            document.createElement(
                'img'
            );


        img.src =
            window.URL.createObjectURL(
                e.files[0]
            );


        img.setAttribute(
            'style',
            'width:100%;max-width:400px; margin-top:15px'
        );


        img.alt = '';


        e.parentNode.appendChild(
            img
        );

    }

}



// ============================================================
// SUB CATEGORY
// ============================================================

function getsubcats(elm)
{

    var catid =
        elm.options[
            elm.options.selectedIndex
        ].value;


    $.ajax({

        type: 'GET',

        url: '/get_sub_cats/' + catid,


        success: function (data) {

            var obj =
                JSON.parse(
                    JSON.stringify(data)
                );


            var sub_cat_html = "";


            $.each(
                obj['subcats'],
                function (key, val)
                {

                    sub_cat_html +=
                        "<option value=" +
                        val.id +
                        ">" +
                        val.name +
                        "</option>";

                }
            );


            if(
                sub_cat_html != ""
            )
            {

                $("#sub_cat").html(
                    '<option value="">Select SubCategory</option>' +
                    sub_cat_html
                );

            }
            else
            {

                $("#sub_cat").html(
                    '<option value="">No SubCategory</option>'
                );

            }

        },


        error: function(data)
        {

            console.log(
                'data error'
            );

        }

    });

}



// ============================================================
// SUMMERNOTE EDITOR
// ============================================================

$(document).ready(function()
{

    $('.editor').summernote({

        height: 150

    });

});



// ============================================================
// FORM REQUIRED CHECK
// ============================================================

function formCheck(e)
{

    if(
        e.form.header.value == ''
    )
    {

        console.log(
            e.form.header
        );


        alert(
            'Form Header is required'
        );

    }

}



// ============================================================
// STATUS
// ============================================================

function Status(e)
{

    let timer =
        e.parentNode
        .parentNode
        .nextElementSibling;


    if(
        e.options[
            e.selectedIndex
        ].value == 'Scheduled'
    )
    {

        timer.classList.remove(
            'hide'
        );


        timer.innerHTML =

            '<div class="col-md-6">' +

                '<div class="form-group">' +

                    '<label for="open">' +
                        'Publish Time' +
                    '</label>' +

                    '<input ' +
                        'type="datetime-local" ' +
                        'class="form-control" ' +
                        'name="open" ' +
                        'id="open">' +

                '</div>' +

            '</div>' +


            '<div class="col-md-6">' +

                '<div class="form-group">' +

                    '<label for="close">' +
                        'Close Time' +
                    '</label>' +

                    '<input ' +
                        'type="datetime-local" ' +
                        'class="form-control" ' +
                        'name="close" ' +
                        'id="close">' +

                '</div>' +

            '</div>';

    }
    else
    {

        timer.innerHTML = '';

        timer.classList.add(
            'hide'
        );

    }

}



// ============================================================
// SELECT2
// ============================================================

$(function()
{

    $('.select2').select2();

});



</script>

@endsection