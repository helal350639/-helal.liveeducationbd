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

.routine-exam-name-list { min-height: 28px; padding: 5px 2px; }
.routine-exam-name-list .exam-name-item { margin-bottom: 4px; }
</style>

@endsection


@section('content')
@include('layouts.routines.partials.all-pages-navigation')


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
    {{-- Main Exam names --}}
    <label>Main Exam Name</label>
    <div id="examNameList" class="routine-exam-name-list">
        @foreach($value->exams ?? [] as $exam)
            @php $examKind = preg_match('/^(revision|revition|revison|রিভিশন)/iu', trim($exam->name)) ? 'revision' : (preg_match('/^(model|মডেল)/iu', trim($exam->name)) ? 'model' : 'main'); @endphp
            @if($examKind === 'main')
                <span class="exam-name-item" data-id="{{ $exam->id }}">
                    {{ $exam->name }}
                    <span class="remove-exam" title="Remove"><i class="fa fa-times"></i></span>
                    <input type="hidden" name="exam_names[]" value="{{ $exam->name }}">
                </span>
            @endif
        @endforeach
    </div>
    <div class="exam-add-row input-group">
        <input type="text" id="newExamName" class="form-control" placeholder="Exam Name লিখুন">
        <span class="input-group-btn"><button type="button" class="btn btn-success" id="addExamBtn"><i class="fa fa-plus"></i> Add Exam Name</button></span>
    </div>

    {{-- Revision Exam names --}}
    <div class="exam-add-row">
        <label for="newRevisionExamName">Revision Exam Name</label>
        <div id="revisionExamNameList" class="routine-exam-name-list">
            @foreach($value->exams ?? [] as $exam)
                @if(preg_match('/^(revision|revition|revison|রিভিশন)/iu', trim($exam->name)))
                    <span class="exam-name-item" data-id="{{ $exam->id }}">
                        {{ $exam->name }}
                        <span class="remove-exam" title="Remove"><i class="fa fa-times"></i></span>
                        <input type="hidden" name="exam_names[]" value="{{ $exam->name }}">
                    </span>
                @endif
            @endforeach
        </div>
        <div class="input-group">
            <input type="text" id="newRevisionExamName" class="form-control" placeholder="Revision Exam-01">
            <span class="input-group-btn"><button type="button" id="addRevisionExamBtn" class="btn btn-success"><i class="fa fa-plus"></i> Add Revision Exam</button></span>
        </div>
    </div>

    {{-- Model Exam names --}}
    <div class="exam-add-row">
        <label for="newModelExamName">Model Exam Name</label>
        <div id="modelExamNameList" class="routine-exam-name-list">
            @foreach($value->exams ?? [] as $exam)
                @if(preg_match('/^(model|মডেল)/iu', trim($exam->name)))
                    <span class="exam-name-item" data-id="{{ $exam->id }}">
                        {{ $exam->name }}
                        <span class="remove-exam" title="Remove"><i class="fa fa-times"></i></span>
                        <input type="hidden" name="exam_names[]" value="{{ $exam->name }}">
                    </span>
                @endif
            @endforeach
        </div>
        <div class="input-group">
            <input type="text" id="newModelExamName" class="form-control" placeholder="Model Test-01">
            <span class="input-group-btn"><button type="button" id="addModelExamBtn" class="btn btn-success"><i class="fa fa-plus"></i> Add Model Exam</button></span>
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
   SEPARATE MAIN / REVISION / MODEL NAME LISTS
   Existing exam_names[] persistence remains unchanged.
===================================================== */
function addRoutineExamName(inputSelector, listSelector, type) {
    var $input = $(inputSelector);
    var name = String($input.val() || '').trim();
    if (!name) { alert('অনুগ্রহ করে Exam Name লিখুন।'); $input.focus(); return; }
    var isRevision = /^(revision|revition|revison|রিভিশন)/i.test(name);
    var isModel = /^(model|মডেল)/i.test(name);
    if (type === 'revision' && !isRevision) name = 'Revision ' + name;
    if (type === 'model' && !isModel) name = 'Model ' + name;
    if (type === 'main' && (isRevision || isModel)) {
        alert('Revision/Model Exam সংশ্লিষ্ট আলাদা সারি থেকে Add করুন।'); return;
    }
    var exists = false;
    $('input[name="exam_names[]"]').each(function () {
        if (String($(this).val()).trim().toLocaleLowerCase() === name.toLocaleLowerCase()) exists = true;
    });
    if (exists) { alert('এই Exam Name ইতোমধ্যে যোগ করা হয়েছে।'); return; }
    var $item = $('<span/>', {'class':'exam-name-item'});
    $item.append(document.createTextNode(name));
    $item.append($('<span/>', {'class':'remove-exam', 'title':'Remove'}).append($('<i/>', {'class':'fa fa-times'})));
    $item.append($('<input/>', {'type':'hidden', 'name':'exam_names[]'}).val(name));
    $(listSelector).append($item);
    $input.val('').focus();
}
$(document).on('click','#addExamBtn',function () { addRoutineExamName('#newExamName','#examNameList','main'); });
$(document).on('click','#addRevisionExamBtn',function () { addRoutineExamName('#newRevisionExamName','#revisionExamNameList','revision'); });
$(document).on('click','#addModelExamBtn',function () { addRoutineExamName('#newModelExamName','#modelExamNameList','model'); });
$(document).on('keydown','#newExamName, #newRevisionExamName, #newModelExamName',function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        if(this.id === 'newExamName') $('#addExamBtn').trigger('click');
        else if(this.id === 'newRevisionExamName') $('#addRevisionExamBtn').trigger('click');
        else $('#addModelExamBtn').trigger('click');
    }
});
$(document).on('click','.routine-exam-name-list .remove-exam',function () {
    $(this).closest('.exam-name-item').remove();
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