@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$value = $routine;

function checkIn($id, $columns)
{
    if(in_array($id, $columns))
    {
        return 'selected';
    }

    return '';
}
@endphp

@extends('dashboard')

@section('title', 'Edit Routine')

@section('stylesheets')

<link href="/assets/summernote/summernote.min.css" rel="stylesheet">

<style>

.exam-name-box{
    border:1px solid #ddd;
    border-radius:4px;
    padding:15px;
    background:#fafafa;
}

.exam-name-item{
    display:inline-flex;
    align-items:center;
    background:#3c8dbc;
    color:#fff;
    padding:6px 10px;
    margin:3px;
    border-radius:3px;
}

.exam-name-item .remove-exam{
    margin-left:8px;
    cursor:pointer;
    color:#fff;
}

.exam-add-row{
    margin-top:10px;
}

</style>

@endsection


@section('content')

<section class="content-header">

    <h1>Edit Routine</h1>

    <ol class="breadcrumb">

        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>
                Routines
            </a>
        </li>

        <li class="active">
            Edit Routine
        </li>

    </ol>

</section>


<section class="content">

<div class="row">

<div class="col-md-12">

<div class="box box-primary">


<div class="box-header with-border">

    <h3 style="color:#800" class="box-title">
        Routine Details
    </h3>

    <div class="text-right toolbar-icon pull-right">

        <a
            href="{{route('routine.create')}}"
            title="Add"
            class="label label-info">

            <i class="fa fa-plus"></i>

        </a>

        <a
            href="{{route('routine.index')}}"
            title="View"
            class="label label-success">

            <i class="fa fa-list"></i>

        </a>

    </div>

</div>


<form
    action="{{route('routine.update', $value)}}"
    method="POST"
    enctype="multipart/form-data">

@csrf

@method('PUT')


<div class="box-body">


{{-- COURSE --}}

<div class="col-md-3">

<div class="form-group">

<label>Course</label>

<select
    name="course_id"
    id="course_id"
    class="form-control select2"
    onchange="getBatches(this)">

<option value="">
    Select One
</option>

@foreach($courses as $val)

<option
    value="{{$val->id}}"
    {{$value->course_id == $val->id ? 'selected' : ''}}>

    {{$val->name}}

</option>

@endforeach

</select>

</div>

</div>



{{-- BATCH --}}

<div class="col-md-3">

<div class="form-group">

<label>Batch</label>

<select
    name="batch_id"
    id="batch_id"
    class="form-control select2"
    onchange="getDepartments(this)">

<option value="">
    Select One
</option>

@foreach($batches as $val)

<option
    value="{{$val->id}}"
    {{$value->batch_id == $val->id ? 'selected' : ''}}>

    {{$val->name}}

</option>

@endforeach

</select>

</div>

</div>



{{-- ROUTINE NAME --}}

<div class="col-md-6">

<div class="form-group">

<label for="name">
    Routine Name
</label>

<input
    type="text"
    class="form-control"
    name="name"
    id="name"
    required
    value="{{$value->name}}">

</div>

</div>



{{-- HEADER --}}

<div class="col-md-12">

<div class="form-group">

<label for="header">
    Header
</label>

<textarea
    class="form-control editor"
    name="header"
    id="header"
    rows="5">{{$value->header}}</textarea>

</div>

</div>



{{-- SUBJECT --}}

<div class="col-md-12">

<div class="form-group">

<label for="subject">
    Subject
</label>

<select
    class="form-control select2"
    multiple
    name="subjects[]"
    id="subjects"
    required>

@foreach($subjects as $val)

<option
    value="{{$val->id}}"
    {{checkIn($val->id, $subject_columns)}}>

    {{$val->name}}

</option>

@endforeach

</select>

</div>

</div>



{{-- DEPARTMENT --}}

<div class="col-md-12">

<div class="form-group">

<label for="department">
    Department
</label>

<select
    class="form-control select2"
    multiple
    name="departments[]"
    id="departments"
    required>

@foreach($departments as $val)

<option
    value="{{$val->id}}"
    {{checkIn($val->id, $department_columns)}}>

    {{$val->name}}

</option>

@endforeach

</select>

</div>

</div>



{{-- ================================================= --}}
{{-- EXAM NAME SECTION --}}
{{-- ================================================= --}}

<div class="col-md-12">

<div class="form-group">

<label>
    Exam Name
</label>


<div class="exam-name-box">

    <div id="examNameList">

        @if(isset($value->exams))

            @foreach($value->exams as $exam)

                <span
                    class="exam-name-item"
                    data-id="{{$exam->id}}">

                    {{$exam->name}}

                    <span
                        class="remove-exam"
                        data-id="{{$exam->id}}">

                        <i class="fa fa-times"></i>

                    </span>

                    <input
                        type="hidden"
                        name="exam_names[]"
                        value="{{$exam->name}}">

                </span>

            @endforeach

        @endif

    </div>


    <div class="exam-add-row">

        <div class="input-group">

            <input
                type="text"
                id="newExamName"
                class="form-control"
                placeholder="Exam Name লিখুন">

            <span class="input-group-btn">

                <button
                    type="button"
                    class="btn btn-success"
                    id="addExamBtn">

                    <i class="fa fa-plus"></i>
                    Add Exam Name

                </button>

            </span>

        </div>

    </div>

</div>

</div>

</div>



{{-- FULL PDF --}}

<div class="col-md-8">

<div class="form-group">

<label for="pdf">
    Full PDF
</label>

<input
    type="file"
    name="pdf"
    class="form-control">

</div>


@if($value->pdf)

<a
    target="_blank"
    href="{{$value->pdf}}">

    <i class="fa fa-file"></i>
    Full PDF

</a>

@endif

</div>



{{-- STATUS --}}

<div class="col-md-4 no-padding">

<div class="col-md-12">

<div class="form-group">

<label for="status">
    Status
</label>

<select
    class="form-control"
    name="status"
    id="status">

<option value="">
    Select One
</option>

<option
    value="Unpublished"
    {{$value->status == 'Unpublished' ? 'selected' : ''}}>

    Unpublished

</option>

<option
    value="Published"
    {{$value->status == 'Published' ? 'selected' : ''}}>

    Published

</option>

</select>

</div>

</div>

</div>


</div>



{{-- SAVE --}}

<div class="box-footer">

<button
    type="submit"
    class="btn btn-primary pull-right">

    <i class="fa fa-save"></i>
    Save

</button>

</div>


</form>


</div>

</div>

</div>

</section>

@endsection



@section('scripts')

<script src="/assets/summernote/summernote.min.js"></script>


<script>


/* =====================================================
   COURSE → BATCH
===================================================== */

function getBatches(e)
{

    function check(id)
    {

        let batchId = '{{$value->batch_id}}';

        if(batchId == id)
        {
            return 'selected';
        }

        return '';

    }


    let ids = Array.from(
        e.selectedOptions
    ).map(({value}) => value);


    $.ajax({

        type:'GET',

        url:'/get_batches/' + ids,

        success:function(data)
        {

            var obj =
                JSON.parse(
                    JSON.stringify(data)
                );


            var options =
                '<option value="">Select One</option>';


            $.each(
                obj['data'],
                function(key,val)
                {

                    options +=
                        '<option value="' +
                        val.id +
                        '" ' +
                        check(val.id) +
                        '>' +
                        val.name +
                        '</option>';

                }
            );


            $("#batch_id").html(options);

        },

        error:function(data)
        {

            console.log(data);

        }

    });

}



/* =====================================================
   BATCH → DEPARTMENT
===================================================== */

function getDepartments(e)
{

    function check(id)
    {

        let deptId =
            '{{$value->department_id}}';


        if(deptId == id)
        {
            return 'selected';
        }

        return '';

    }


    let ids =
        Array.from(
            e.selectedOptions
        ).map(({value}) => value);


    $.ajax({

        type:'GET',

        url:'/get_departments_by_batch/' + ids,

        success:function(data)
        {

            var obj =
                JSON.parse(
                    JSON.stringify(data)
                );


            var options =
                '<option value="">Select One</option>';


            $.each(
                obj['data'],
                function(key,val)
                {

                    options +=
                        '<option value="' +
                        val.id +
                        '" ' +
                        check(val.id) +
                        '>' +
                        val.name +
                        '</option>';

                }
            );


            $("#departments").html(options);

        },

        error:function(data)
        {

            console.log(data);

        }

    });

}



/* =====================================================
   ADD EXAM NAME
===================================================== */

$(document).on(
    'click',
    '#addExamBtn',
    function()
{

    let examName =
        $('#newExamName')
        .val()
        .trim();


    if(examName === '')
    {

        alert(
            'অনুগ্রহ করে Exam Name লিখুন।'
        );

        return;

    }


    let exists = false;


    $('input[name="exam_names[]"]')
        .each(function()
        {

            if(
                $(this)
                .val()
                .toLowerCase()
                ===
                examName.toLowerCase()
            )
            {

                exists = true;

            }

        });


    if(exists)
    {

        alert(
            'এই Exam Name ইতোমধ্যে যোগ করা হয়েছে।'
        );

        return;

    }


    let html = `

        <span class="exam-name-item">

            ${examName}

            <span
                class="remove-exam"
                onclick="$(this).parent().remove();">

                <i class="fa fa-times"></i>

            </span>

            <input
                type="hidden"
                name="exam_names[]"
                value="${examName}">

        </span>

    `;


    $('#examNameList')
        .append(html);


    $('#newExamName')
        .val('')
        .focus();

});



/* =====================================================
   ENTER KEY → ADD EXAM
===================================================== */

$(document).on(
    'keypress',
    '#newExamName',
    function(e)
{

    if(e.which === 13)
    {

        e.preventDefault();

        $('#addExamBtn').click();

    }

});



/* =====================================================
   SUMMERNOTE
===================================================== */

$(document).ready(function()
{

    $('.editor').summernote({

        height:150

    });


    $('.select2').select2();

});

</script>

@endsection