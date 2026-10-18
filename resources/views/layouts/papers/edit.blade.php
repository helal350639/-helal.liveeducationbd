@php
    use App\Http\Controllers\SourceCtrl;

    $source = new SourceCtrl;
    $value = $paper;

    $dateTimeLocal = static function ($date) {
        if (empty($date)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d\TH:i');
        } catch (\Throwable $e) {
            return '';
        }
    };

    $existingResultAtValue = $dateTimeLocal($value->result_at);

    $selectedDepartments = $value->departments
        ->pluck('id')
        ->toArray();

    if (empty($selectedDepartments) && $value->department_id) {
        $selectedDepartments = [$value->department_id];
    }
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
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>
                Question Papers
            </a>
        </li>
        <li class="active">Edit Question Paper</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-primary">

                <div class="box-header with-border">
                    <h3 style="color:#800" class="box-title">
                        Question Paper Details
                    </h3>

                    <div class="text-right toolbar-icon pull-right"
                         style="display:inline">

                        <a href="{{ route('paper.create') }}"
                           title="Add"
                           class="label label-info">
                            <i class="fa fa-plus"></i>
                        </a>

                        <a href="{{ route('paper.index') }}"
                           title="View"
                           class="label label-success">
                            <i class="fa fa-list"></i>
                        </a>

                        <a href="{{ route('paper.show', $paper->id) }}"
                           title="Show"
                           class="label label-primary">
                            <i class="fa fa-file-text"></i>
                        </a>
                    </div>
                </div>

                <form action="{{ route('paper.update', $value) }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    <div class="box-body">

                        <!-- ========================================
                             COURSE
                        ======================================== -->

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="course_id">Course</label>

                                <select name="course_id"
                                        id="course_id"
                                        class="form-control select2"
                                        onchange="getBatches(this)">

                                    <option value="">Select One</option>

                                    @foreach($courses as $val)
                                        <option value="{{ $val->id }}"
                                            {{ $value->course_id == $val->id ? 'selected' : '' }}>
                                            {{ $val->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             BATCH
                        ======================================== -->

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="batch_id">Batch</label>

                                <select name="batch_id"
                                        id="batch_id"
                                        class="form-control select2"
                                        onchange="getDepartments(this); getRoutineExams(this)">

                                    <option value="">Select One</option>

                                    @foreach($batches as $val)
                                        <option value="{{ $val->id }}"
                                            {{ $value->batch_id == $val->id ? 'selected' : '' }}>
                                            {{ $val->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             MULTIPLE DEPARTMENTS
                        ======================================== -->

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="department_id">Department</label>

                                <select name="department_id[]"
                                        id="department_id"
                                        class="form-control select2"
                                        multiple
                                        data-placeholder="Select Department"
                                        style="width:100%">

                                    @foreach($departments as $val)
                                        <option value="{{ $val->id }}"
                                            {{ in_array($val->id, $selectedDepartments) ? 'selected' : '' }}>
                                            {{ $val->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             HEADER
                        ======================================== -->

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="header">Header</label>

                                <textarea class="form-control editor"
                                          name="header"
                                          id="header"
                                          rows="5"
                                          required>{{ $value->header }}</textarea>
                            </div>
                        </div>

                        <!-- ========================================
                             DETAILS
                        ======================================== -->

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="details">Details</label>

                                <textarea class="form-control"
                                          name="details"
                                          id="details"
                                          rows="4">{{ $value->details }}</textarea>
                            </div>
                        </div>

                        <!-- ========================================
                             BANNER
                        ======================================== -->

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="banner">Banner</label>

                                <input type="file"
                                       class="form-control"
                                       name="banner"
                                       id="banner"
                                       onchange="showImg(this)">
                            </div>

                            <p style="color:red;padding:5px 0">
                                Image size: 8:1 / 800px X 100px
                            </p>

                            @if($value->banner)
                                <img src="{{ $value->banner }}"
                                     alt="Current banner"
                                     style="max-width:600px;max-height:200px">
                            @endif

                            <hr>
                        </div>

                        <!-- ========================================
                             QUESTION PAPER NO
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Question Paper No.</label>

                                <select class="form-control select2"
                                        name="name"
                                        id="name"
                                        required
                                        disabled>

                                    <option value="">
                                        Loading Exam No...
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             QUESTIONS LIMIT
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max">Questions Limit</label>

                                <input type="number"
                                       class="form-control"
                                       name="max"
                                       id="max"
                                       value="{{ $value->max }}">
                            </div>
                        </div>

                        <!-- ========================================
                             STATUS
                        ======================================== -->

                        <div class="col-md-6 no-padding">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="status">Status</label>

                                    <select class="form-control"
                                            name="status"
                                            id="status">

                                        <option value="">
                                            Select One
                                        </option>

                                        <option value="Unpublished"
                                            {{ $value->status == 'Unpublished' ? 'selected' : '' }}>
                                            Unpublished
                                        </option>

                                        <option value="Published"
                                            {{ $value->status == 'Published' ? 'selected' : '' }}>
                                            Published
                                        </option>

                                        <option value="Scheduled"
                                            {{ $value->status == 'Scheduled' ? 'selected' : '' }}>
                                            Scheduled
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- ====================================
                                 EXAM START + END TIME
                            ==================================== -->

                            <div class="col-md-12 no-padding">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="open">
                                            Exam Start Time
                                        </label>

                                        <input type="datetime-local"
                                               class="form-control"
                                               name="open"
                                               id="open"
                                               value="{{ $dateTimeLocal($value->open) }}"
                                               required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="close">
                                            Exam Finished Time
                                        </label>

                                        <input type="datetime-local"
                                               class="form-control"
                                               name="close"
                                               id="close"
                                               value="{{ $dateTimeLocal($value->close) }}"
                                               required>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- ========================================
                             MCQ LIST FORMAT
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="format">MCQ List Format</label>

                                <select class="form-control"
                                        name="format"
                                        id="format">

                                    <option value="">Select One</option>

                                    @foreach($source->mcqlist() as $key => $val)
                                        <option value="{{ $key }}"
                                            {{ (string)$value->format === (string)$key ? 'selected' : '' }}>
                                            {{ implode(', ', $val) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             EXAM RUNNING TIME
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="time">
                                    Exam Running Time (in minute)
                                </label>

                                <input type="number"
                                       class="form-control"
                                       name="time"
                                       id="time"
                                       required
                                       value="{{ $value->time }}">
                            </div>
                        </div>

                        <!-- ========================================
                             MARKS
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mark">
                                    Mark (For Correct Answer)
                                </label>

                                <input type="number"
                                       class="form-control"
                                       name="mark"
                                       id="mark"
                                       required
                                       value="{{ $value->mark }}"
                                       step="0.01">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="minus">
                                    Mark (Negative for wrong Answer)
                                </label>

                                <input type="number"
                                       class="form-control"
                                       name="minus"
                                       id="minus"
                                       value="{{ $value->minus }}"
                                       step="0.01">
                            </div>
                        </div>

                        <!-- ========================================
                             EXAM LIMIT
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exam_limit">
                                    How many times can a student take the exam?
                                </label>

                                <select class="form-control"
                                        name="exam_limit"
                                        id="exam_limit">

                                    <option value="">Select One</option>

                                    <option value="1"
                                        {{ $value->exam_limit == '1' ? 'selected' : '' }}>
                                        One Time
                                    </option>

                                    <option value="2"
                                        {{ $value->exam_limit == '2' ? 'selected' : '' }}>
                                        Two Time
                                    </option>

                                    <option value="3"
                                        {{ $value->exam_limit == '3' ? 'selected' : '' }}>
                                        Three Time
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             RANDOM QUESTIONS
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="random">
                                    Show Random Questions?
                                </label>

                                <select class="form-control"
                                        name="random"
                                        id="random">

                                    <option value="">Select One</option>

                                    <option value="Yes"
                                        {{ $value->random == 'Yes' ? 'selected' : '' }}>
                                        Yes
                                    </option>

                                    <option value="No"
                                        {{ $value->random == 'No' ? 'selected' : '' }}>
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             DISPLAY QUESTIONS
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="display">
                                    Display Question?
                                </label>

                                <select class="form-control"
                                        name="display"
                                        id="display">

                                    <option value="">Select One</option>

                                    <option value="All"
                                        {{ $value->display == 'All' ? 'selected' : '' }}>
                                        All in One Paper
                                    </option>

                                    <option value="One"
                                        {{ $value->display == 'One' ? 'selected' : '' }}>
                                        One by One
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             EXAM PERMISSION
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="permit">
                                    Exam Permission For Candidates?
                                </label>

                                <select class="form-control"
                                        name="permit"
                                        id="permit">

                                    <option value="">Select One</option>

                                    <option value="Batch"
                                        {{ $value->permit == 'Batch' ? 'selected' : '' }}>
                                        Batch
                                    </option>

                                    <option value="Department"
                                        {{ $value->permit == 'Department' ? 'selected' : '' }}>
                                        Department
                                    </option>

                                    <option value="Every One"
                                        {{ $value->permit == 'Every One' ? 'selected' : '' }}>
                                        Every One
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             PAID EXAM
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="paid">Paid exam?</label>

                                <select class="form-control"
                                        name="paid"
                                        id="paid"
                                        required>

                                    <option value="">Select One</option>

                                    <option value="Yes"
                                        {{ $value->paid == 'Yes' ? 'selected' : '' }}>
                                        Yes
                                    </option>

                                    <option value="No"
                                        {{ $value->paid == 'No' ? 'selected' : '' }}>
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             EMAIL RESULT
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">
                                    Send Result to the Email?
                                </label>

                                <select class="form-control"
                                        name="email"
                                        id="email">

                                    <option value="">Select One</option>

                                    <option value="Yes"
                                        {{ $value->email == 'Yes' ? 'selected' : '' }}>
                                        Yes
                                    </option>

                                    <option value="No"
                                        {{ $value->email == 'No' ? 'selected' : '' }}>
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- ========================================
                             RESULT VIEW
                        ======================================== -->

                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="result_view">
                                    Student Can View Result After Exam?
                                </label>

                                <select class="form-control"
                                        name="result_view"
                                        id="result_view"
                                        onchange="resultView(this)"
                                        required>

                                    <option value="">Select One</option>

                                    <option value="Yes"
                                        {{ $value->result_view == 'Yes' ? 'selected' : '' }}>
                                        Yes
                                    </option>

                                    <option value="No"
                                        {{ $value->result_view == 'No' ? 'selected' : '' }}>
                                        No
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-12 no-padding"
                                 id="result_message"
                                 style="display:none">
                            </div>

                        </div>

                        <!-- ========================================
                             MAX CANDIDATES
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="candidate">
                                    Max Candidates
                                </label>

                                <input type="number"
                                       class="form-control"
                                       name="candidate"
                                       id="candidate"
                                       value="{{ $value->candidate }}">
                            </div>
                        </div>

                        <!-- ========================================
                             MESSAGE AFTER EXAM
                        ======================================== -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="message">
                                    Message After Exam
                                </label>

                                <textarea class="form-control"
                                          name="message"
                                          id="message"
                                          rows="3">{{ $value->message }}</textarea>
                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <button type="submit"
                                class="btn btn-primary pull-right"
                                onclick="formCheck(this)">
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
'use strict';

/*
|--------------------------------------------------------------------------
| GLOBAL ROUTINE VARIABLES
|--------------------------------------------------------------------------
*/

let routineExamList = [];
let selectedRoutineEndAt = '';

const existingExamName = @json($value->name);
const existingBatchId = @json($value->batch_id);
const existingResultAt = @json($existingResultAtValue);

let initialRoutineLoaded = false;

/*
|--------------------------------------------------------------------------
| DATETIME FORMAT HELPER
|--------------------------------------------------------------------------
| Routine example: 2026-10-17 15:06:00
| HTML field:      2026-10-17T15:06
|--------------------------------------------------------------------------
*/

function routineDateTimeValue(value) {

    if (!value) {
        return '';
    }

    const match = String(value).trim().match(
        /^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2})/
    );

    return match
        ? match[1] + 'T' + match[2]
        : '';
}

/*
|--------------------------------------------------------------------------
| ROUTINE DATE DISPLAY FORMAT
|--------------------------------------------------------------------------
*/

function routineDateText(value) {

    const formatted = routineDateTimeValue(value);

    if (!formatted) {
        return '';
    }

    const parts = formatted.slice(0, 10).split('-');

    return parts[2] + '/' + parts[1] + '/' + parts[0];
}

/*
|--------------------------------------------------------------------------
| COURSE CHANGE -> LOAD BATCHES
|--------------------------------------------------------------------------
*/

function getBatches(e) {

    const courseId = e.value;

    routineExamList = [];
    selectedRoutineEndAt = '';

    $('#batch_id')
        .empty()
        .append(new Option('Loading Batches...', ''))
        .trigger('change.select2');

    $('#department_id')
        .empty()
        .trigger('change.select2');

    $('#name')
        .prop('disabled', true)
        .empty()
        .append(
            new Option('Select Course & Batch First', '')
        )
        .trigger('change.select2');

    if (!courseId) {
        return;
    }

    $.ajax({

        type: 'GET',

        url: '/get_batches/' + encodeURIComponent(courseId),

        success: function (response) {

            if (
                String($('#course_id').val()) !== String(courseId)
            ) {
                return;
            }

            const batches =
                response && Array.isArray(response.data)
                    ? response.data
                    : [];

            const $batch = $('#batch_id');

            $batch.empty().append(
                new Option('Select One', '')
            );

            batches.forEach(function (batch) {

                $batch.append(
                    new Option(batch.name, batch.id)
                );

            });

            $batch.trigger('change.select2');
        },

        error: function (xhr) {

            console.error(
                'Batch loading failed:',
                xhr.responseText
            );

            $('#batch_id')
                .empty()
                .append(
                    new Option('Unable to Load Batches', '')
                )
                .trigger('change.select2');
        }
    });
}

/*
|--------------------------------------------------------------------------
| BATCH CHANGE -> LOAD DEPARTMENTS
|--------------------------------------------------------------------------
*/

function getDepartments(e) {

    const batchId = e.value;

    const $department = $('#department_id');

    $department.empty().trigger('change.select2');

    if (!batchId) {
        return;
    }

    $.ajax({

        type: 'GET',

        url: '/get_departments_by_batch/' +
            encodeURIComponent(batchId),

        success: function (response) {

            if (
                String($('#batch_id').val()) !== String(batchId)
            ) {
                return;
            }

            const departments =
                response && Array.isArray(response.data)
                    ? response.data
                    : [];

            $department.empty();

            departments.forEach(function (department) {

                $department.append(
                    new Option(
                        department.name,
                        department.id
                    )
                );

            });

            $department.trigger('change.select2');
        },

        error: function (xhr) {

            console.error(
                'Department loading failed:',
                xhr.responseText
            );

        }
    });
}

/*
|--------------------------------------------------------------------------
| RESULT PUBLISH FIELD
|--------------------------------------------------------------------------
| Existing rule:
| result_view = No -> Show Result Publish Date & Time
|--------------------------------------------------------------------------
*/

function resultView(e) {

    const $container = $('#result_message');

    if (!e || e.value !== 'No') {

        $container.empty().hide();

        return;
    }

    if (!$('#result_at').length) {

        const $group = $('<div>', {
            class: 'form-group'
        });

        $group.append(
            $('<label>', {
                for: 'result_at',
                text: 'Result Publish Date & Time'
            })
        );

        $group.append(
            $('<input>', {
                type: 'datetime-local',
                class: 'form-control',
                name: 'result_at',
                id: 'result_at',
                required: true
            })
        );

        $container.empty().append($group);
    }

    $('#result_at').val(
        selectedRoutineEndAt || existingResultAt
    );

    $container.show();
}

/*
|--------------------------------------------------------------------------
| APPLY ROUTINE EXAM DATA
|--------------------------------------------------------------------------
| updateText = true:
| Admin manually changed Exam No.
| Update Header + After Exam Message.
|
| updateText = false:
| Initial Edit Page load.
| Keep saved Header + Message unchanged.
|--------------------------------------------------------------------------
*/

function applyRoutineExam(updateText) {

    const examName = $('#name').val();

    const exam = routineExamList.find(function (item) {

        return String(item.name) === String(examName);

    });

    if (!exam) {

        if (updateText) {

            $('#open').val('');
            $('#close').val('');

            selectedRoutineEndAt = '';

            if ($('#result_at').length) {
                $('#result_at').val('');
            }
        }

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | GET ROUTINE START & END TIME
    |--------------------------------------------------------------------------
    */

    const start = routineDateTimeValue(
        exam.exam_at
    );

    const end = routineDateTimeValue(
        exam.exam_end_at
    );

    selectedRoutineEndAt = end;

    /*
    |--------------------------------------------------------------------------
    | AUTO EXAM START TIME
    |--------------------------------------------------------------------------
    */

    if (start) {

        $('#open').val(start);

    }

    /*
    |--------------------------------------------------------------------------
    | AUTO EXAM FINISHED TIME
    |--------------------------------------------------------------------------
    */

    if (end) {

        $('#close').val(end);

    } else if (updateText) {

        $('#close').val('');
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO RESULT PUBLISH DATE & TIME
    |--------------------------------------------------------------------------
    | Result Publish = Routine Exam End
    |--------------------------------------------------------------------------
    */

    if ($('#result_view').val() === 'No') {

        if (!$('#result_at').length) {

            resultView(
                document.getElementById('result_view')
            );
        }

        if (end) {

            $('#result_at').val(end);

        } else if (updateText) {

            $('#result_at').val('');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PRESERVE EXISTING HEADER/MESSAGE ON INITIAL LOAD
    |--------------------------------------------------------------------------
    */

    if (!updateText) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO HEADER
    |--------------------------------------------------------------------------
    */

    const batchName =
        $('#batch_id option:selected').text();

    const examDate = routineDateText(
        exam.exam_at
    );

    const headerText =
        '<div style="text-align:center;">' +
            '<b>Live Education BD</b><br><br>' +
            'Batch Name: ' + batchName + '<br><br>' +
            'Exam No: ' + exam.name + '<br><br>' +
            'Exam Date: ' + examDate +
        '</div>';

    if ($('#header').next('.note-editor').length) {

        $('#header').summernote(
            'code',
            headerText
        );

    } else {

        $('#header').val(headerText);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO AFTER EXAM MESSAGE
    |--------------------------------------------------------------------------
    */

    const index = routineExamList.findIndex(function (item) {

        return String(item.name) === String(examName);

    });

    const nextExam = routineExamList[index + 1];

    const nextExamName = nextExam
        ? nextExam.name
        : '';

    const nextExamDate = nextExam
        ? routineDateText(nextExam.exam_at)
        : '';

    const messageText =
        'আপনার উত্তর পত্রটি সঠিক ভাবে জমা হয়েছে। ধন্যবাদ।\n\n' +
        'পরবর্তী এক্সাম নং - ' + nextExamName +
        '\nতারিখ ' + nextExamDate +
        '\n\nপ্রশ্ন সম্পর্কে কোন অভিযোগ থাকলে অভিযোগ বক্সে লিখে দিন। ' +
        'আমরা সেটির যথাযথ সমাধানের চেষ্টা করবো।';

    $('#message').val(messageText);
}

/*
|--------------------------------------------------------------------------
| EXAM NO CHANGE
|--------------------------------------------------------------------------
*/

$(document).on('change', '#name', function () {

    applyRoutineExam(true);

});

/*
|--------------------------------------------------------------------------
| LOAD EXAM NUMBERS FROM ROUTINE
|--------------------------------------------------------------------------
*/

function getRoutineExams(e) {

    const batchId = e.value;

    routineExamList = [];
    selectedRoutineEndAt = '';

    $('#name')
        .prop('disabled', true)
        .empty()
        .append(
            new Option('Loading Exams...', '')
        )
        .trigger('change.select2');

    if (!batchId) {

        $('#name')
            .empty()
            .append(
                new Option(
                    'Select Course & Batch First',
                    ''
                )
            )
            .trigger('change.select2');

        return;
    }

    $.ajax({

        type: 'GET',

        url: '/get-routine-exams/' +
            encodeURIComponent(batchId),

        success: function (response) {

            if (
                String($('#batch_id').val()) !== String(batchId)
            ) {
                return;
            }

            routineExamList =
                response && Array.isArray(response.exams)
                    ? response.exams
                    : [];

            const $name = $('#name');

            $name.empty();

            if (!routineExamList.length) {

                $name
                    .append(
                        new Option(
                            'No Exam Found in Routine',
                            ''
                        )
                    )
                    .prop('disabled', true)
                    .trigger('change.select2');

                initialRoutineLoaded = true;

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | BUILD EXAM DROPDOWN
            |--------------------------------------------------------------------------
            */

            $name.append(
                new Option('Select Exam No.', '')
            );

            routineExamList.forEach(function (exam) {

                $name.append(
                    new Option(
                        exam.name,
                        exam.name
                    )
                );

            });

            $name.prop('disabled', false);

            /*
            |--------------------------------------------------------------------------
            | RESTORE EXISTING EXAM ON FIRST LOAD
            |--------------------------------------------------------------------------
            */

            const restore =
                !initialRoutineLoaded &&
                String(batchId) === String(existingBatchId) &&
                routineExamList.some(function (exam) {

                    return String(exam.name) ===
                        String(existingExamName);

                });

            initialRoutineLoaded = true;

            if (restore) {

                $name.val(existingExamName);

                $name.trigger('change.select2');

                // Fill times without replacing saved Header/Message.
                applyRoutineExam(false);

            } else {

                $name.val('')
                    .trigger('change.select2');
            }

        },

        error: function (xhr) {

            console.error(
                'Routine exams loading failed:',
                xhr.responseText
            );

            $('#name')
                .empty()
                .append(
                    new Option(
                        'Unable to Load Exams',
                        ''
                    )
                )
                .prop('disabled', true)
                .trigger('change.select2');

        }
    });
}

/*
|--------------------------------------------------------------------------
| IMAGE PREVIEW
|--------------------------------------------------------------------------
*/

function showImg(e) {

    if (!e.files || !e.files[0]) {
        return;
    }

    const fileUrl = URL.createObjectURL(
        e.files[0]
    );

    let $img = $(e).siblings('img').first();

    if (!$img.length) {

        $img = $('<img>', {
            alt: 'Selected banner preview'
        }).css({
            width: '100%',
            maxWidth: '400px',
            marginTop: '15px'
        });

        $(e).parent().append($img);
    }

    $img.attr('src', fileUrl);
}

/*
|--------------------------------------------------------------------------
| SUB CATEGORY - COMPATIBILITY
|--------------------------------------------------------------------------
*/

function getsubcats(elm) {

    const catId = elm.value;

    $.ajax({

        type: 'GET',

        url: '/get_sub_cats/' +
            encodeURIComponent(catId),

        success: function (response) {

            const $subcat = $('#sub_cat');

            $subcat.empty().append(
                new Option('Select SubCategory', '')
            );

            const items =
                response && Array.isArray(response.subcats)
                    ? response.subcats
                    : [];

            items.forEach(function (entry) {

                $subcat.append(
                    new Option(
                        entry.name,
                        entry.id
                    )
                );

            });
        },

        error: function (xhr) {

            console.error(
                'SubCategory error:',
                xhr.responseText
            );
        }
    });
}

/*
|--------------------------------------------------------------------------
| FORM HEADER CHECK
|--------------------------------------------------------------------------
*/

function formCheck(e) {

    if (!e.form.header.value) {

        alert('Form Header is required');

    }
}

/*
|--------------------------------------------------------------------------
| DOCUMENT READY
|--------------------------------------------------------------------------
*/

$(document).ready(function () {

    // Select2
    $('.select2').select2();

    // Summernote
    $('.editor').summernote({
        height: 150
    });

    // Result Publish Field
    resultView(
        document.getElementById('result_view')
    );

    // Load existing Batch's Routine.
    const batch = document.getElementById('batch_id');

    if (batch && batch.value) {

        getRoutineExams(batch);

    }
});

</script>

@endsection