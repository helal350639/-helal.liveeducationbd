@extends('dashboard')

@section('title', 'Show Routine Item')

@section('stylesheets')

<link href="/assets/summernote/summernote.min.css" rel="stylesheet">

<style>

/* =====================================================
   GENERAL
===================================================== */

.text-right {
    text-align: right;
}


/* =====================================================
   TABLE WRAPPER
===================================================== */

.routine-table-wrapper {
    width: 100%;
    overflow-x: auto;
}


/* =====================================================
   ROUTINE TABLE
===================================================== */

.routine-table {
    width: 100%;
    border-collapse: collapse;
}


/* =====================================================
   TABLE HEADER
===================================================== */

.routine-table thead th {
    text-align: center;
    vertical-align: middle !important;

    padding: 8px 6px !important;

    white-space: nowrap;

    background: #f5f5f5;
}


/* =====================================================
   TABLE BODY ROW
===================================================== */

.routine-table tbody tr {
    height: 65px;
}


/* =====================================================
   ALL TABLE CELLS
===================================================== */

.routine-table tbody td {
    padding: 7px 6px !important;

    vertical-align: middle !important;

    height: 65px;
}


/* =====================================================
   ID
===================================================== */

.routine-table .id-column {
    width: 50px;

    text-align: center;

    white-space: nowrap;
}


/* =====================================================
   EXAM
===================================================== */

.routine-table .exam-column {
    width: 130px;

    white-space: nowrap;
}


/* =====================================================
   SUBJECT / DEPARTMENT
===================================================== */

.routine-table .data-column {
    min-width: 110px;

    max-width: 180px;
}


/* =====================================================
   CONTENT AREA

   Extra content will be hidden visually,
   but database data remains unchanged.
===================================================== */

.routine-content {
    max-height: 50px;

    overflow: hidden;

    line-height: 1.45;

    word-break: break-word;

    overflow-wrap: anywhere;
}


/* =====================================================
   IMAGES INSIDE CONTENT
===================================================== */

.routine-content img {
    max-width: 100%;
    height: auto;
}


/* =====================================================
   DATE & TIME
===================================================== */

.routine-table .date-column {
    width: 175px;

    min-width: 175px;

    text-align: center;

    white-space: nowrap;
}


.exam-date-cell {
    cursor: pointer;

    vertical-align: middle !important;
}


.exam-date-cell:hover {
    background: #f5f9ff;
}


.exam-date-display {
    display: block;

    overflow: hidden;

    line-height: 1.4;
}


.exam-date-text {
    white-space: nowrap;
}


/* =====================================================
   DATE EDIT BOX
===================================================== */

.exam-edit-box {
    display: none;

    width: 100%;
}


.exam-edit-box input {
    width: 100%;

    margin-bottom: 5px;
}


/* =====================================================
   DATE EDIT BUTTONS
===================================================== */

.exam-edit-buttons {
    display: flex;

    justify-content: center;

    gap: 4px;
}


.exam-edit-buttons button {
    border: none;

    padding: 4px 8px;

    border-radius: 3px;

    cursor: pointer;
}


.exam-save {
    background: #00a65a;

    color: #fff;
}


.exam-cancel {
    background: #dd4b39;

    color: #fff;
}


/* =====================================================
   LOADING
===================================================== */

.exam-loading {
    display: none;

    font-size: 11px;

    color: #777;

    margin-top: 3px;
}


/* =====================================================
   ACTION COLUMN
===================================================== */

.routine-table .action-column {
    width: 80px;

    min-width: 80px;

    text-align: center !important;

    vertical-align: middle !important;

    white-space: nowrap;
}


/* =====================================================
   ACTION BUTTON
===================================================== */

.routine-table .action-column .btn {
    margin: 1px;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 768px) {

    .routine-table {
        min-width: 1100px;
    }

}

</style>

@endsection


@section('content')

<section class="content-header">

    <h1>Show a Routine Item</h1>

    <ol class="breadcrumb">

        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>
                Routines
            </a>
        </li>

        <li class="active">
            Show Routine
        </li>

    </ol>

</section>


{{-- =====================================================
     MAIN CONTENT
===================================================== --}}

<section class="content">

<div class="row">

<div class="col-md-12">

<div class="box box-info">


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

        {{-- ADD --}}

        <a
            href="{{ route('routine.add-item', $routine->id) }}"
            title="Add"
            class="label label-info">

            <i class="fa fa-plus"></i>

        </a>


        {{-- LIST --}}

        <a
            href="{{ route('routine.index') }}"
            title="View"
            class="label label-success">

            <i class="fa fa-list"></i>

        </a>

    </div>

</div>


{{-- =====================================================
     TABLE
===================================================== --}}

<div class="routine-table-wrapper">

<table class="table table-bordered table-hover routine-table">

<thead>

<tr>

    {{-- ID --}}

    <th class="id-column">
        ID
    </th>


    {{-- EXAM --}}

    <th class="exam-column">
        Exam Name/No
    </th>


    {{-- SUBJECTS --}}

    @foreach($subjects as $subject)

        <th class="data-column">

            {{ $subject->name }}

        </th>

    @endforeach


    {{-- DEPARTMENTS --}}

    @foreach($departments as $department)

        <th class="data-column">

            {{ $department->name }}

        </th>

    @endforeach


    {{-- DATE --}}

    <th class="date-column">
        Date & Time
    </th>
    {{-- End DATE --}}

    <th class="date-column">

    Exam End

</th>

    {{-- ACTION --}}

    <th class="action-column">
        Action
    </th>

</tr>

</thead>


<tbody>


@foreach($routine_rows as $item)

<tr>


    {{-- =================================================
         ID
    ================================================= --}}

    <td class="id-column">

        {{ $item->id }}

    </td>


    {{-- =================================================
         EXAM NAME
    ================================================= --}}

    <td class="exam-column">

        {{ $item->paper_name }}

    </td>


    {{-- =================================================
         SUBJECTS
    ================================================= --}}

    @foreach($item->subjects as $subject)

        <td class="data-column">

            <div
                class="routine-content"
                title="{{ strip_tags($subject->chapter) }}"
            >

                {!! $subject->chapter !!}

            </div>

        </td>

    @endforeach


    {{-- =================================================
         DEPARTMENTS
    ================================================= --}}

    @foreach($item->departments as $department)

        <td class="data-column">

            <div
                class="routine-content"
                title="{{ strip_tags($department->details) }}"
            >

                {!! $department->details !!}

            </div>

        </td>

    @endforeach


    {{-- =================================================
         DATE & TIME
    ================================================= --}}

<td class="exam-date-cell date-column"
    data-id="{{ $item->id }}">

    <div class="exam-date-display">

        <span class="exam-date-text">
            {{ $item->exam_at }}
        </span>

        <br>

        <small style="color:#999;">
            <i class="fa fa-pencil"></i>
            Click to edit
        </small>

    </div>


    <div class="exam-edit-box">

        <input type="datetime-local"
               class="form-control exam-at-input"
               value="{{ $item->exam_at ? \Carbon\Carbon::parse($item->exam_at)->format('Y-m-d\TH:i') : '' }}">


        <div class="exam-edit-buttons">

            <button type="button"
                    class="exam-save btn btn-success btn-xs">
                <i class="fa fa-check"></i>
            </button>


            <button type="button"
                    class="exam-cancel btn btn-danger btn-xs">
                <i class="fa fa-times"></i>
            </button>

        </div>


        <div class="exam-loading">
            Updating...
        </div>

    </div>

</td>



{{-- Exam End Date & Time --}}

<td 
    class="exam-end-cell date-column"
    data-id="{{ $item->id }}"
    data-original="{{ $item->exam_end_at }}"
>


<div class="exam-date-display">

    <span class="exam-date-text">
        {{ $item->exam_end_at }}
    </span>


    <br>


    <small style="color:#999;">
        <i class="fa fa-pencil"></i>
        Click to edit
    </small>


</div>



<div class="exam-end-edit-box" style="display:none;">

    <input 
        type="datetime-local"
        class="form-control exam-end-input"
        value="{{ $item->exam_end_at ? \Carbon\Carbon::parse($item->exam_end_at)->format('Y-m-d\TH:i') : '' }}">


    <br>


    <button class="btn btn-success btn-xs exam-end-save">
        <i class="fa fa-check"></i>
    </button>


    <button class="btn btn-danger btn-xs exam-end-cancel">
        <i class="fa fa-times"></i>
    </button>


</div>


</td>



{{-- ACTION --}}
<td class="action-column">

    <a href="{{ route('routine.item.edit',$item->id) }}"
       class="btn btn-warning btn-xs">

        <i class="fa fa-pencil"></i>

    </a>


    <a href="{{ route('routine.item.delete',$item->id) }}"
       class="btn btn-danger btn-xs"
       onclick="return confirm('Are you sure?')">

        <i class="fa fa-trash"></i>

    </a>

</td>

@endforeach


</tbody>

</table>

</div>


</div>

</div>

</div>

</section>

@endsection



@section('scripts')

<script src="/assets/summernote/summernote.min.js"></script>


<script>

$(document).ready(function(){


    /* =====================================================
       OPEN DATE EDIT
    ===================================================== */

    $(document).on(
        'click',
        '.exam-date-cell',
        function(e)
        {

            if(
                $(e.target).closest(
                    '.exam-edit-box'
                ).length
            ){

                return;

            }


            let cell = $(this);


            /*
            |--------------------------------------------------------------------------
            | Close other edit boxes
            |--------------------------------------------------------------------------
            */

            $('.exam-date-cell')
                .not(cell)
                .each(function(){

                    $(this)
                        .find('.exam-date-display')
                        .show();

                    $(this)
                        .find('.exam-edit-box')
                        .hide();

                });


            /*
            |--------------------------------------------------------------------------
            | Open current
            |--------------------------------------------------------------------------
            */

            cell
                .find('.exam-date-display')
                .hide();


            cell
                .find('.exam-edit-box')
                .show();


            cell
                .find('.exam-at-input')
                .focus();

        }
    );



    /* =====================================================
       CANCEL DATE EDIT
    ===================================================== */

    $(document).on(
        'click',
        '.exam-cancel',
        function(e)
        {

            e.stopPropagation();


            let cell = $(this)
                .closest('.exam-date-cell');


            cell
                .find('.exam-edit-box')
                .hide();


            cell
                .find('.exam-date-display')
                .show();

        }
    );



    /* =====================================================
       SAVE DATE & TIME
    ===================================================== */

    $(document).on(
        'click',
        '.exam-save',
        function(e)
        {

            e.stopPropagation();


            let button = $(this);

            let cell = button.closest(
                '.exam-date-cell'
            );


            let id = cell.data('id');


            let input = cell.find(
                '.exam-at-input'
            );


            let newDate = input.val();


            if(!newDate){

                alert(
                    'Please select Date & Time.'
                );

                return;

            }


            button.prop(
                'disabled',
                true
            );


            cell.find(
                '.exam-loading'
            ).show();


            $.ajax({

                url:
                    "{{ url('/routine/item/update-exam-at') }}/"
                    + id,

                type: 'POST',

                data: {

                    _token:
                        "{{ csrf_token() }}",

                    exam_at:
                        newDate

                },


                success: function(response)
                {

                    if(response.success){

                        let date =
                            new Date(
                                response.exam_at
                            );


                        let formatted =
                            formatDateTime(date);


                        cell.find(
                            '.exam-date-text'
                        ).text(
                            formatted
                        );


                        cell.find(
                            '.exam-edit-box'
                        ).hide();


                        cell.find(
                            '.exam-date-display'
                        ).show();


                        showRoutineMessage(
                            response.message,
                            'success'
                        );

                    }
                    else{

                        alert(
                            response.message ||
                            'Update failed.'
                        );

                    }

                },


                error: function(xhr)
                {

                    let message =
                        'Date & Time update failed.';


                    if(
                        xhr.responseJSON &&
                        xhr.responseJSON.message
                    ){

                        message =
                            xhr.responseJSON.message;

                    }


                    alert(message);

                },


                complete: function()
                {

                    button.prop(
                        'disabled',
                        false
                    );


                    cell.find(
                        '.exam-loading'
                    ).hide();

                }

            });

        }
    );



    /* =====================================================
       FORMAT DATE
    ===================================================== */

    function formatDateTime(date)
    {

        if(
            isNaN(date.getTime())
        ){

            return '';

        }


        let year =
            date.getFullYear();


        let month =
            String(
                date.getMonth() + 1
            ).padStart(2,'0');


        let day =
            String(
                date.getDate()
            ).padStart(2,'0');


        let hours =
            String(
                date.getHours()
            ).padStart(2,'0');


        let minutes =
            String(
                date.getMinutes()
            ).padStart(2,'0');


        let seconds =
            String(
                date.getSeconds()
            ).padStart(2,'0');


        return (
            year +
            '-' +
            month +
            '-' +
            day +
            ' ' +
            hours +
            ':' +
            minutes +
            ':' +
            seconds
        );

    }

       

    /* =====================================================
       SUCCESS MESSAGE
    ===================================================== */

    function showRoutineMessage(
        message,
        type
    )
    {

        let alertClass =
            type === 'success'
                ? 'alert-success'
                : 'alert-danger';


        $('#routineUpdateMessage').remove();


        let messageBox = `

            <div
                class="alert ${alertClass}"
                id="routineUpdateMessage"
                style="
                    position:fixed;
                    top:70px;
                    right:20px;
                    z-index:9999;
                    min-width:280px;
                "
            >

                <button
                    type="button"
                    class="close"
                    onclick="
                        $('#routineUpdateMessage').remove();
                    "
                >

                    &times;

                </button>

                ${message}

            </div>

        `;


        $('body').append(
            messageBox
        );


        setTimeout(
            function(){

                $('#routineUpdateMessage')
                    .fadeOut(
                        300,
                        function(){

                            $(this).remove();

                        }
                    );

            },
            2500
        );

    }

});


// Exam End Click Edit

$(document).on(
    'click',
    '.exam-end-cell .exam-date-display',
    function(e){

        e.preventDefault();

        e.stopPropagation();


        let td = $(this).closest('.exam-end-cell');


        $('.exam-end-cell')
            .not(td)
            .find('.exam-end-edit-box')
            .hide();


        td.find('.exam-date-display').hide();


        td.find('.exam-end-edit-box').show();


        td.find('.exam-end-input').focus();


    }
);
// Exam End Cancel

$(document).on(
    'click',
    '.exam-end-cancel',
    function(e){

        e.preventDefault();

        e.stopPropagation();


        let td = $(this).closest('.exam-end-cell');


        td.find('.exam-end-edit-box').hide();


        td.find('.exam-date-display').show();


    }
);

// Exam End Save

$(document).on(
    'click',
    '.exam-end-save',
    function(e){

        e.preventDefault();

        e.stopPropagation();


        let td = $(this).closest('.exam-end-cell');


        let id = td.data('id');


        let value = td.find('.exam-end-input').val();



        $.ajax({

            url: "{{ url('/routine/item/update-exam-end') }}/" + id,

            type: "POST",


            data: {

                _token: "{{ csrf_token() }}",

                exam_end_at: value

            },


            success:function(response){

    if(response.success){


        td.find('.exam-date-text')
          .text(value.replace('T',' '));


        td.find('.exam-end-edit-box')
          .hide();


        td.find('.exam-date-display')
          .show();


        alert('Exam End Updated Successfully');


    }
    else{

        alert(response.message);

    }

},


            error:function(xhr){

    console.log(xhr.responseText);

    alert(xhr.responseText);

}


      });


});

</script>

@endsection