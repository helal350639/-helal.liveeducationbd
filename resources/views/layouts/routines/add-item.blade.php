@extends('dashboard')

@section('title', 'Add New Routine')

@section('stylesheets')

<link href="/assets/summernote/summernote.min.css" rel="stylesheet">

<style>

.text-right {
    text-align: right;
}

.exam-select-box {
    position: relative;
}

.exam-select-box select {
    width: 100%;
}

</style>

@endsection


@section('content')

<section class="content-header">

    <h1>Add a Routine Item</h1>

    <ol class="breadcrumb">

        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>
                Routines
            </a>
        </li>

        <li class="active">
            Add Routine
        </li>

    </ol>

</section>


<section class="content">

<div class="row">

<div class="col-md-12">

<div class="box box-primary">


{{-- =====================================================
     HEADER
===================================================== --}}

<div class="box-header with-border">

    <h3
        style="color:#800"
        class="box-title">

        {{ $routine->name }}

    </h3>


    <div class="toolbar-icon pull-right text-right">

        <a
            href="{{ route('routine.index') }}"
            title="View"
            class="label label-success">

            <i class="fa fa-list"></i>

        </a>

    </div>

</div>


{{-- =====================================================
     FORM
===================================================== --}}

<form
    action="{{ route('routine.add-item.store') }}"
    method="POST">

    @csrf

    <input
        type="hidden"
        name="routine_id"
        value="{{ $routine->id }}">


    <div class="box-body">

        <div class="row">


            {{-- =================================================
                 LEFT SIDE
            ================================================= --}}

            <div class="col-md-6">


                {{-- =================================================
     EXAM NAME DROPDOWN
================================================= --}}

<div class="form-group row">

    <label
        class="col-md-4 text-right"
        for="paper_name">

        Exam No

    </label>

    <div class="col-md-8">

        <select
            name="paper_name"
            id="paper_name"
            class="form-control"
            required>

            <option value="">
                Select Exam
            </option>

            @if(isset($exams) && $exams->isNotEmpty())

                @foreach($exams as $exam)

                    <option value="{{ $exam->name }}">
                        {{ $exam->name }}
                    </option>

                @endforeach

            @else

                <option value="" disabled>
                    No Exam Name Added
                </option>

            @endif

        </select>

    </div>

</div>


                {{-- =================================================
                     SUBJECTS
                ================================================= --}}

                @foreach($subjects as $subject)

                    <div class="form-group row">

                        <label
                            class="col-md-4 text-right">

                            {{ $subject->name }}

                        </label>


                        <div class="col-md-8">

                            <input
                                type="hidden"
                                name="subject_id[]"
                                value="{{ $subject->id }}"
                                required>


                            <textarea
                                class="form-control editor"
                                name="subject_item[]"
                                rows="5"></textarea>

                        </div>

                    </div>

                @endforeach


            </div>


            {{-- =================================================
                 RIGHT SIDE
            ================================================= --}}

            <div class="col-md-6">


                {{-- =================================================
                     DEPARTMENTS
                ================================================= --}}

                @foreach($departments as $department)

                    <div class="form-group row">

                        <label
                            class="col-md-4 text-right">

                            {{ $department->name }}

                        </label>


                        <div class="col-md-8">

                            <input
                                type="hidden"
                                name="department_id[]"
                                value="{{ $department->id }}"
                                required>


                            <textarea
                                class="form-control editor"
                                name="department_item[]"
                                rows="5"></textarea>

                        </div>

                    </div>

                @endforeach


                {{-- =================================================
                     DATE & TIME
                ================================================= --}}

                <div class="form-group row">

    <label
        class="col-md-4 text-right"
        for="exam_at">

        Date & Time

    </label>


    <div class="col-md-8">

        <input
            type="datetime-local"
            class="form-control"
            name="exam_at"
            id="exam_at"
            required>

    </div>

</div>


<!-- Exam End Date & Time -->

<div class="form-group row">

    <label
        class="col-md-4 text-right"
        for="exam_end_at">

        Exam End Date & Time

    </label>


    <div class="col-md-8">

        <input
            type="datetime-local"
            class="form-control"
            name="exam_end_at"
            id="exam_end_at">

    </div>

</div>

    {{-- =====================================================
         FOOTER
    ===================================================== --}}

    <div class="box-footer">

        <button
            type="submit"
            class="btn btn-primary pull-right">

            <i class="fa fa-plus"></i>
            Add

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

$(document).ready(function() {


    /* =====================================================
       SUMMERNOTE
    ===================================================== */

    $('.editor').summernote({

        toolbar: [
            [
                'view',
                [
                    'fullscreen',
                    'codeview'
                ]
            ]
        ],

        height: 100

    });


    /* =====================================================
       SELECT2
    ===================================================== */

    if ($.fn.select2) {

        $('.select2').select2();

    }

});


/* =====================================================
   COURSE → BATCH
===================================================== */

function getBatches(e)
{

    let ids = Array.from(
        e.selectedOptions
    ).map(
        ({value}) => value
    );


    $.ajax({

        type: 'GET',

        url: '/get_batches/' + ids,

        success: function(data)
        {

            var obj = JSON.parse(
                JSON.stringify(data)
            );


            var options =
                '<option value="">Select One</option>';


            $.each(
                obj.data,
                function(key, val)
                {

                    options +=
                        '<option value="' +
                        val.id +
                        '">' +
                        val.name +
                        '</option>';

                }
            );


            $('#batch_id').html(options);

        },


        error: function(data)
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

    let ids = Array.from(
        e.selectedOptions
    ).map(
        ({value}) => value
    );


    $.ajax({

        type: 'GET',

        url: '/get_departments_by_batch/' + ids,

        success: function(data)
        {

            var obj = JSON.parse(
                JSON.stringify(data)
            );


            var options =
                '<option value="">Select One</option>';


            $.each(
                obj.data,
                function(key, val)
                {

                    options +=
                        '<option value="' +
                        val.id +
                        '">' +
                        val.name +
                        '</option>';

                }
            );


            $('#department_id').html(options);

        },


        error: function(data)
        {

            console.log(data);

        }

    });

}

</script>

@endsection