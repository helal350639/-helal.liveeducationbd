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


/* Step 3P integrated table viewport */
.content-wrapper .content, .content-wrapper .content .col-md-12 { min-width: 0; }
.routine-table-wrapper.lebd-sticky-routine { display:block; }

/* Scroll within the routine table, leaving the page navigation available. */
.routine-table-wrapper.lebd-sticky-routine {
    position: relative;
    width: 100%;
    height: calc(100vh - 205px);
    max-height: calc(100vh - 205px);
    min-height: 250px;
    overflow: auto !important;
    overscroll-behavior: contain;
    isolation: isolate;
    border: 1px solid #d9e1e8;
}
.routine-table-wrapper.lebd-sticky-routine .routine-table {
    width: max-content !important;
    min-width: 100% !important;
    table-layout: auto;
    margin-bottom: 0;
    border-collapse: separate !important;
    border-spacing: 0;
}
.routine-table-wrapper.lebd-sticky-routine .routine-table th,
.routine-table-wrapper.lebd-sticky-routine .routine-table td {
    box-sizing: border-box;
    background: #fff;
}
.routine-table-wrapper.lebd-sticky-routine .routine-table thead th {
    position: sticky !important;
    top: 0;
    z-index: 11;
    background: #f1f5f9 !important;
    box-shadow: 0 1px #cbd5e1;
}
/* Two frozen columns on the left. */
.routine-table-wrapper.lebd-sticky-routine .routine-table .id-column {
    width: 56px !important;
    min-width: 56px !important;
    max-width: 56px !important;
    position: sticky !important;
    left: 0;
    z-index: 7;
    background: #fff !important;
}
.routine-table-wrapper.lebd-sticky-routine .routine-table .exam-column {
    width: 145px !important;
    min-width: 145px !important;
    max-width: 145px !important;
    position: sticky !important;
    left: 56px;
    z-index: 7;
    background: #fff !important;
    box-shadow: 2px 0 3px rgba(0,0,0,.08);
}
/* Frozen Action column on the right. */
.routine-table-wrapper.lebd-sticky-routine .routine-table .action-column {
    width: 88px !important;
    min-width: 88px !important;
    max-width: 88px !important;
    position: sticky !important;
    right: 0;
    z-index: 7;
    background: #fff !important;
    box-shadow: -2px 0 3px rgba(0,0,0,.08);
}
.routine-table-wrapper.lebd-sticky-routine .routine-table thead .id-column,
.routine-table-wrapper.lebd-sticky-routine .routine-table thead .exam-column,
.routine-table-wrapper.lebd-sticky-routine .routine-table thead .action-column {
    z-index: 15;
    background: #e7eef7 !important;
}
/* Show full content in every cell rather than hiding long syllabus/links. */
.routine-table-wrapper.lebd-sticky-routine .routine-content {
    max-height: none !important;
    overflow: visible !important;
    overflow-wrap: anywhere;
    white-space: normal;
}
.routine-table-wrapper.lebd-sticky-routine .routine-table tbody tr,
.routine-table-wrapper.lebd-sticky-routine .routine-table tbody td {
    height: auto !important;
}
.routine-table-wrapper.lebd-sticky-routine .routine-table .data-column {
    min-width: 155px !important;
    max-width: 280px !important;
}
.routine-table-wrapper.lebd-sticky-routine .routine-table .date-column {
    min-width: 190px !important;
}
/* Step 3S: fit the ordinary table viewport to the actual visible page. */
.routine-table-wrapper.lebd-sticky-routine:not(.lebd-fullscreen):not(:fullscreen) {
    height: var(--lebd-available-table-height, calc(100dvh - 185px)) !important;
    max-height: none !important;
    min-height: 220px !important;
    overflow: auto !important;
    scrollbar-gutter: stable;
}

/* Step 3R: native browser fullscreen, with fixed-position fallback. */
.routine-table-wrapper.lebd-sticky-routine:fullscreen,
.routine-table-wrapper.lebd-sticky-routine:-webkit-full-screen,
.routine-table-wrapper.lebd-sticky-routine.lebd-fullscreen {
    box-sizing: border-box !important;
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: 0 !important;
    inset: 0 !important;
    display: block !important;
    width: 100vw !important;
    min-width: 100vw !important;
    max-width: 100vw !important;
    height: 100vh !important;
    height: 100dvh !important;
    min-height: 100vh !important;
    max-height: 100vh !important;
    max-height: 100dvh !important;
    margin: 0 !important;
    padding: 48px 0 0 !important;
    overflow: auto !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: #fff !important;
    z-index: 2147483000 !important;
    isolation: isolate;
    transform: none !important;
}
.routine-table-wrapper.lebd-sticky-routine:fullscreen .routine-table,
.routine-table-wrapper.lebd-sticky-routine.lebd-fullscreen .routine-table {
    width: max-content !important;
    min-width: 100% !important;
}
.lebd-routine-expand-btn { margin-right: 7px; }
.lebd-routine-fullscreen-toolbar {
    display: none;
    box-sizing: border-box;
    position: sticky;
    top: -48px;
    left: 0;
    width: 100%;
    height: 48px;
    margin-top: -48px;
    padding: 7px 16px;
    background: #174c75;
    color: white;
    z-index: 40;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.routine-table-wrapper.lebd-sticky-routine:fullscreen .lebd-routine-fullscreen-toolbar,
.routine-table-wrapper.lebd-sticky-routine:-webkit-full-screen .lebd-routine-fullscreen-toolbar,
.routine-table-wrapper.lebd-sticky-routine.lebd-fullscreen .lebd-routine-fullscreen-toolbar { display: flex; }
body.lebd-routine-screen-active { overflow: hidden !important; }
/* Sticky table header must remain underneath the fullscreen toolbar. */
.routine-table-wrapper.lebd-sticky-routine:fullscreen .routine-table thead th,
.routine-table-wrapper.lebd-sticky-routine.lebd-fullscreen .routine-table thead th { top: 0 !important; }
@media (max-width: 768px) {
    .routine-table-wrapper.lebd-sticky-routine { max-height: 70vh; }
    .routine-table-wrapper.lebd-sticky-routine .routine-table .exam-column {
        min-width: 118px !important; width: 118px !important; max-width: 118px !important;
    }
    .routine-table-wrapper.lebd-sticky-routine .routine-table .id-column { width: 48px !important; min-width: 48px !important; max-width: 48px !important; }
    .routine-table-wrapper.lebd-sticky-routine .routine-table .exam-column { left: 48px; }
}

</style>

@endsection


@section('content')

{{-- Preserve shared navigation when installed; no dependency when absent. --}}
@includeIf('layouts.routines.partials.all-pages-navigation')

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

<div class="routine-table-wrapper" tabindex="0">

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

    {{-- EXTRA DATES (display only; managed on Add/Edit Item) --}}
    <th class="date-column">Create PDF Date &amp; Time</th>
    <th class="date-column">Question Create Date &amp; Time</th>

    {{-- ACTION --}}

    <th class="action-column">
        Action
    </th>

</tr>

</thead>


<tbody>


@foreach($displayRows as $item)

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
        <strong>{{ $item->paper_name }}</strong>
    </td>

    {{-- Each Subject column shows ONLY its own linked Main Exam numbers for Revision/Model. --}}
    @if($item->row_kind === 'derived')
        @foreach($subjects as $subject)
            @php
                $linkedNumbers = $item->linked_exam_names['subject:'.$subject->id] ?? [];
            @endphp
            <td class="data-column">
                <div class="routine-content" title="{{ implode(', ', $linkedNumbers) }}">
                    @forelse($linkedNumbers as $linkedNumber)
                        <div>{{ $linkedNumber }}</div>
                    @empty
                        <span class="text-muted">—</span>
                    @endforelse
                </div>
            </td>
        @endforeach
    @else
        @foreach($subjects as $subject)
            @php
                $subjectRow = $item->subjects->firstWhere('subject_id', $subject->id);
                $labels = $routineMcqLabels[$item->id]['subject'][$subject->id] ?? [];
            @endphp
            <td class="data-column">
                @if(count($labels))
                    <div class="routine-content" title="{{ implode(' | ', $labels) }}">
                        @foreach($labels as $label)
                            <div>{{ $label }}</div>
                        @endforeach
                    </div>
                @elseif($subjectRow)
                    <div class="routine-content" title="{{ strip_tags($subjectRow->chapter) }}">{!! $subjectRow->chapter !!}</div>
                @endif
            </td>
        @endforeach
    @endif

    {{-- Each Department column also shows ONLY its own linked Main Exam numbers. --}}
    @if($item->row_kind === 'derived')
        @foreach($departments as $department)
            @php
                $linkedNumbers = $item->linked_exam_names['department:'.$department->id] ?? [];
            @endphp
            <td class="data-column">
                <div class="routine-content" title="{{ implode(', ', $linkedNumbers) }}">
                    @forelse($linkedNumbers as $linkedNumber)
                        <div>{{ $linkedNumber }}</div>
                    @empty
                        <span class="text-muted">—</span>
                    @endforelse
                </div>
            </td>
        @endforeach
    @else
        @foreach($departments as $department)
            @php
                $departmentRow = $item->departments->firstWhere('department_id', $department->id);
                $labels = $routineMcqLabels[$item->id]['department'][$department->id] ?? [];
            @endphp
            <td class="data-column">
                @if(count($labels))
                    <div class="routine-content" title="{{ implode(' | ', $labels) }}">
                        @foreach($labels as $label)
                            <div>{{ $label }}</div>
                        @endforeach
                    </div>
                @elseif($departmentRow)
                    <div class="routine-content" title="{{ strip_tags($departmentRow->details) }}">{!! $departmentRow->details !!}</div>
                @endif
            </td>
        @endforeach
    @endif

    {{-- =================================================
         DATE & TIME
    ================================================= --}}

<td class="exam-date-cell date-column"
    data-id="{{ $item->id }}" data-row-kind="{{ $item->row_kind }}">

    <div class="exam-date-display">

        <span class="exam-date-text">
            {{ $item->exam_at }}
        </span>

        <br>

        @if(true)
        <small style="color:#999;"><i class="fa fa-pencil"></i> Click to edit</small>
        @endif

    </div>


    @if(true)
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

    @endif
</td>



{{-- Exam End Date & Time --}}

<td 
    class="exam-end-cell date-column"
    data-id="{{ $item->id }}"
    data-original="{{ $item->exam_end_at }}" data-row-kind="{{ $item->row_kind }}"
>


<div class="exam-date-display">

    <span class="exam-date-text">
        {{ $item->exam_end_at }}
    </span>


    <br>


    @if(true)
<small style="color:#999;">
        <i class="fa fa-pencil"></i>
        Click to edit
    </small>
@endif


</div>



@if(true)
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


@endif
</td>



{{-- CREATE PDF DATE & TIME --}}
<td class="date-column">
    @if($item->create_pdf_at)
        {{ \Carbon\Carbon::parse($item->create_pdf_at)->format('Y-m-d H:i:s') }}
    @else
        <span class="text-muted">—</span>
    @endif
</td>

{{-- QUESTION CREATE DATE & TIME --}}
<td class="date-column">
    @if($item->question_create_at)
        {{ \Carbon\Carbon::parse($item->question_create_at)->format('Y-m-d H:i:s') }}
    @else
        <span class="text-muted">—</span>
    @endif
</td>

{{-- ACTION --}}
<td class="action-column">
@if($item->row_kind === 'derived')
<a href="{{ route('routine.derived.edit', $item->id) }}" class="btn btn-warning btn-xs" title="Edit {{ ucfirst($item->exam_type) }}"><i class="fa fa-pencil"></i></a>
<form method="POST" action="{{ route('routine.derived.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Delete this Revision/Model Exam?');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></button></form>
@else

    <a href="{{ route('routine.item.edit',$item->id) }}"
       class="btn btn-warning btn-xs">

        <i class="fa fa-pencil"></i>

    </a>


    <a href="{{ route('routine.item.delete',$item->id) }}"
       class="btn btn-danger btn-xs"
       onclick="return confirm('Are you sure?')">

        <i class="fa fa-trash"></i>

    </a>

@endif
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

                url: cell.data('row-kind') === 'derived'
                    ? "{{ url('/routine/derived-item') }}/" + id + "/date"
                    : "{{ url('/routine/item/update-exam-at') }}/" + id,

                type: 'POST',

                data: {

                    _token:
                        "{{ csrf_token() }}",

                    exam_at: newDate,
                    field: 'exam_at', value: newDate

                },


                success: function(response)
                {

                    if(response.success){

                        let date =
                            new Date(
                                (response.exam_at || response.value)
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

            url: td.data('row-kind') === 'derived'
                ? "{{ url('/routine/derived-item') }}/" + id + "/date"
                : "{{ url('/routine/item/update-exam-end') }}/" + id,

            type: "POST",


            data: {

                _token: "{{ csrf_token() }}",

                exam_end_at: value,
                field: 'exam_end_at', value: value

            },


            success:function(response){

    if(response.success){


        td.find('.exam-date-text')
          .text(value.replace('T',' '));


        td.find('.exam-end-edit-box')
          .hide();


        td.find('.exam-date-display')
          .show();


        window.location.reload();


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


/* Step 3R: use real browser fullscreen; fall back to a fixed viewport overlay. */
(function () {
    function init() {
        var wrap = document.querySelector('.routine-table-wrapper');
        if (!wrap || wrap.dataset.routineViewport === 'ready') return;
        wrap.dataset.routineViewport = 'ready';
        wrap.classList.add('lebd-sticky-routine');
        var box = wrap.closest('.box');
        var header = box ? box.querySelector('.box-header') : null;
        var home = wrap.parentNode;
        var marker = document.createComment('routine table original position');
        home.insertBefore(marker, wrap);

        var toolbar = document.createElement('div');
        toolbar.className = 'lebd-routine-fullscreen-toolbar';
        var name = document.createElement('strong');
        name.textContent = @json($routine->name) + ' — Routine Items';
        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn btn-danger btn-sm';
        close.textContent = 'Exit Full Screen';
        toolbar.appendChild(name);
        toolbar.appendChild(close);
        wrap.insertBefore(toolbar, wrap.firstChild);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-primary lebd-routine-expand-btn';
        btn.textContent = 'Full Screen';
        btn.setAttribute('aria-pressed', 'false');
        if (header) header.appendChild(btn);
        else home.insertBefore(btn, marker);

        /* Keep the horizontal scrollbar at the bottom of the usable browser area
           when not fullscreen; resize/reposition does not change table content. */
        function fitOrdinaryViewport() {
            if (document.fullscreenElement === wrap || wrap.classList.contains('lebd-fullscreen')) return;
            var rect = wrap.getBoundingClientRect();
            var available = Math.floor(window.innerHeight - rect.top - 12);
            if (available < 220) available = 220;
            wrap.style.setProperty('--lebd-available-table-height', available + 'px');
        }
        window.addEventListener('resize', fitOrdinaryViewport);
        window.addEventListener('scroll', fitOrdinaryViewport, { passive: true });
        fitOrdinaryViewport();
        function active() {
            return document.fullscreenElement === wrap || wrap.classList.contains('lebd-fullscreen');
        }
        function sync() {
            var opened = active();
            btn.textContent = opened ? 'Exit Full Screen' : 'Full Screen';
            btn.setAttribute('aria-pressed', opened ? 'true' : 'false');
            if (!opened) {
                document.body.classList.remove('lebd-routine-screen-active');
                if (marker.parentNode && wrap.parentNode !== marker.parentNode) {
                    marker.parentNode.insertBefore(wrap, marker.nextSibling);
                }
                fitOrdinaryViewport();
            }
        }
        async function enter() {
            if (document.fullscreenEnabled && typeof wrap.requestFullscreen === 'function') {
                try {
                    await wrap.requestFullscreen();
                    wrap.scrollTop = 0;
                    sync();
                    return;
                } catch (e) { /* Use fixed-position fallback. */ }
            }
            document.body.appendChild(wrap);
            wrap.classList.add('lebd-fullscreen');
            document.body.classList.add('lebd-routine-screen-active');
            wrap.scrollTop = 0;
            sync();
        }
        async function exit() {
            if (document.fullscreenElement === wrap) {
                try { await document.exitFullscreen(); } catch (e) { /* Continue restoring. */ }
            }
            wrap.classList.remove('lebd-fullscreen');
            document.body.classList.remove('lebd-routine-screen-active');
            if (marker.parentNode) marker.parentNode.insertBefore(wrap, marker.nextSibling);
            sync();
        }
        btn.addEventListener('click', function () { active() ? exit() : enter(); });
        close.addEventListener('click', exit);
        document.addEventListener('fullscreenchange', sync);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && wrap.classList.contains('lebd-fullscreen')) exit();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
</script>

@endsection