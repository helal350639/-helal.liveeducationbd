@extends('dashboard')

@section('title','Auto Add Questions')


@section('content')

<section class="content-header">

    <h1>
        <i class="fa fa-magic"></i>
        Auto Add Questions
    </h1>

</section>


<section class="content">


<div class="row">
<div class="col-md-12">


<div class="box box-primary">


<div class="box-header with-border">

<h3 class="box-title">
Question Paper Information
</h3>


<div class="pull-right">

<button class="btn btn-success btn-sm">
<i class="fa fa-book"></i>
Add Syllabus
</button>


<button class="btn btn-info btn-sm">
<i class="fa fa-database"></i>
Add Database
</button>

</div>


</div>



<div class="box-body">


<div class="row">


<div class="box-body">

    <div class="row">

        <div class="col-md-2">
            <b>Question Paper No:</b>
            <br>
            {{ $paper->name }}
        </div>


        <div class="col-md-2">
            <b>Course:</b>
            <br>
            {{ $paper->course ? $paper->course->name : '' }}
        </div>


        <div class="col-md-2">
            <b>Batch:</b>
            <br>
            {{ $paper->batch ? $paper->batch->name : '' }}
        </div>


        <div class="col-md-3">
            <b>Department:</b>
            <br>

            @if($paper->departments->count())

                @foreach($paper->departments as $department)

                    <span class="label label-info"
                          style="margin-right:5px; display:inline-block;">
                        {{ $department->name }}
                    </span>

                @endforeach

            @elseif($paper->department)

                <span class="label label-info">
                    {{ $paper->department->name }}
                </span>

            @endif

        </div>


        <div class="col-md-1">
            <b>Max:</b>
            <br>

            <span class="label label-primary">
                {{ $paper->max }}
            </span>

        </div>


        <div class="col-md-2">
            <b>Current:</b>
            <br>

            <span class="label label-success">
                {{ $paper->questions->count() }}
            </span>

        </div>

    </div>

</div>

</div>


</div>

</div>


<!-- Non Question Selection Table -->

<div class="box box-primary">

<div class="box-header with-border">

<h3 class="box-title">
<i class="fa fa-list"></i>
Non Department
</h3>

</div>


<div class="box-body">


<table class="table table-bordered table-hover">

<thead>

<tr>

<th width="12%">Course</th>

<th width="12%">Department</th>

<th width="12%">Subject</th>

<th width="12%">Chapter</th>

<th width="8%">Available</th>

<th width="8%">Add</th>

<th width="8%">Difficult</th>

<th width="8%">Medium</th>

<th width="8%">Easy</th>

<th width="5%">Action</th>

</tr>

</thead>


<tbody>


<tr>


<td>

<select class="form-control">

<option>
Select Course
</option>

</select>

</td>



<td>

<select class="form-control">

<option>
Select Department
</option>

</select>

</td>



<td>

<select class="form-control">

<option>
Select Subject
</option>

</select>

</td>



<td>

<select class="form-control">

<option>
Select Chapter
</option>

</select>

</td>



<td class="text-center">

<span class="label label-info">
0
</span>

</td>



<td>

<input 
type="number"
class="form-control"
value="0"
style="width:70px">

</td>



<td>

<input 
type="number"
class="form-control"
value="0"
style="width:70px">

</td>



<td>

<input 
type="number"
class="form-control"
value="0"
style="width:70px">

</td>



<td>

<input 
type="number"
class="form-control"
value="0"
style="width:70px">

</td>


<td>

<button type="button"
class="btn btn-success btn-sm"
onclick="copyQuestionRow(this)">

<i class="fa fa-copy"></i>

</button>

</td>


</tr>

</tbody>


</table>


</div>

</div>


<!-- DEPARTMENT QUESTIONS -->

<div class="box box-primary">

    <div class="box-header with-border">

        <h3 class="box-title">
            <i class="fa fa-list"></i>
            Department
        </h3>

    </div>

    <div class="box-body">

        <table class="table table-bordered table-hover">

            <thead>
                <tr>
                    <th class="text-center">Course</th>
                    <th class="text-center">Department</th>
                    <th class="text-center">Subject</th>
                    <th class="text-center">Chapter</th>
                    <th class="text-center">Available</th>
                    <th class="text-center">Select Q. Range</th>
                    <th class="text-center">Total Questions</th>
                    <th class="text-center">Difficult</th>
                    <th class="text-center">Medium</th>
                    <th class="text-center">Easy</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

            <tbody id="departmentQuestionRows">

                @foreach($paper->departments as $department)

<tr class="department-question-row">

    <!-- COURSE -->
    <td>
        <select
            class="form-control course-select"
            name="department_questions[{{ $department->id }}][course_id]">

            <option value="">Select Course</option>

            @foreach($courses as $course)

                <option value="{{ $course->id }}"
                    {{ $paper->course_id == $course->id ? 'selected' : '' }}>

                    {{ $course->name }}

                </option>

            @endforeach

        </select>
    </td>


    <!-- DEPARTMENT -->
    <td>
        <select
            class="form-control department-select"
            name="department_questions[{{ $department->id }}][department_id]">

            <option value="">
                Select Department
            </option>

        </select>
</td>


                
                   <!-- SUBJECT -->
<td>
    <select
        class="form-control subject-select"
        name="department_questions[{{ $department->id }}][subject_id]">

        <option value="">
            Select Subject
        </option>

    </select>
</td>



                    <!-- CHAPTER -->
<td>
    <select
        class="form-control chapter-select"
        name="department_questions[{{ $department->id }}][chapter_id]">

        <option value="">
            Select Chapter
        </option>

    </select>
</td>


<!-- AVAILABLE -->
<td class="text-center">

    <span
    class="label label-info available-question-count"
    style="font-size: 16px; padding: 6px 10px; display: inline-block;">
    0
</span>

</td>




<!-- QUESTION SELECT -->
<td class="text-center">

    <div style="min-width: 120px;">

        <div
            style="
                display: flex;
                gap: 4px;
                justify-content: center;
            ">

            <!-- FROM -->
<input
    <input
                type="number"
                class="form-control question-start"
                name="department_questions[{{ $department->id }}][question_start]"
                min="1"
                placeholder="Start"
                style="
                    width: 65px;
                    height: 30px;
                    padding: 3px 5px;
                    text-align: center;
                    font-size: 13px;
                ">


            <!-- LAST -->
            <input
                type="number"
                class="form-control question-last"
                name="department_questions[{{ $department->id }}][question_last]"
                min="1"
                placeholder="Last"
                style="
                    width: 65px;
                    height: 30px;
                    padding: 3px 5px;
                    text-align: center;
                    font-size: 13px;
                ">

        </div>

    </div>

</td>

                    <!-- Total Add -->
                    <td>
                        <input
                            type="number"
                            min="0"
                            value="0"
                            class="form-control difficult-question"
                            name="department_questions[{{ $department->id }}][difficult]"
                            style="width:70px">
                    </td>



                <!-- TOTAL QUESTIONS -->
<td class="text-center">

    <input
        type="number"
        class="form-control total-question-input"
        name="department_questions[{{ $department->id }}][total_questions]"
        value=""
        min="1"
        placeholder="Total"
        style="
            width: 75px;
            height: 30px;
            margin: auto;
            padding: 3px 5px;
            text-align: center;
            font-size: 13px;
        ">

</td>




                    <!-- DIFFICULT -->
                    <td>
                        <input
                            type="number"
                            min="0"
                            value="0"
                            class="form-control difficult-question"
                            name="department_questions[{{ $department->id }}][difficult]"
                            style="width:70px">
                    </td>


                    <!-- MEDIUM -->
                    <td>
                        <input
                            type="number"
                            min="0"
                            value="0"
                            class="form-control medium-question"
                            name="department_questions[{{ $department->id }}][medium]"
                            style="width:70px">
                    </td>


                    <!-- EASY -->
                    <td>
                        <input
                            type="number"
                            min="0"
                            value="0"
                            class="form-control easy-question"
                            name="department_questions[{{ $department->id }}][easy]"
                            style="width:70px">
                    </td>


                    <!-- ACTION -->
                    <td class="text-center">

                    <button
                    type="button"
                        class="btn btn-success btn-sm"
                            onclick="copyQuestionRow(this)"
                            title="Copy Row">

                        <i class="fa fa-copy"></i>

                        </button>

                        </td>
                </tr>

                @endforeach

            </tbody>

        </table>
  
    

        <!-- DONE BUTTON -->
        <div class="text-right" style="margin-top: 15px;">

            <button
                type="button"
                id="doneDepartmentQuestions"
                class="btn btn-success">

                <i class="fa fa-check"></i>
                DONE

            </button>

        </div>

    </div>

</div>



<!-- SUMMARY -->
<div class="box box-primary">
<div class="box-header">

<h3>
Selected Question Summary
</h3>

</div>


<div class="box-body">


<div class="row">


<div class="col-md-3">

<div class="alert alert-info">

Total Selected Question

<h3>
0
</h3>

</div>

</div>



<div class="col-md-3">

<div class="alert alert-success">

Available Question

<h3>
0
</h3>

</div>

</div>



<div class="col-md-2">

<div class="alert alert-danger">

Difficult

<h3>
0
</h3>

</div>

</div>



<div class="col-md-2">

<div class="alert alert-warning">

Medium

<h3>
0
</h3>

</div>

</div>



<div class="col-md-2">

<div class="alert alert-success">

Easy

<h3>
0
</h3>

</div>

</div>


</div>


</div>


</div>



<div class="text-right">


<button class="btn btn-default">
Cancel
</button>


<button class="btn btn-primary">
<i class="fa fa-magic"></i>
Auto Add to Question Paper
</button>


</div>



</div>
</div>


</section>


@endsection




@section('scripts')

<script src="/assets/summernote/summernote.min.js"></script>

<script type="text/javascript">

$(document).ready(function () {


    /*
    |--------------------------------------------------------------------------
    | LOAD DEPARTMENTS BY COURSE
    |--------------------------------------------------------------------------
    */

    function loadDepartments(row, keepSelectedDepartment = false) {

        let courseSelect =
            row.find('.course-select');

        let departmentSelect =
            row.find('.department-select');

        let subjectSelect =
            row.find('.subject-select');

        let chapterSelect =
            row.find('.chapter-select');

        let courseId =
            courseSelect.val();

        let oldDepartment = '';

        if (keepSelectedDepartment) {

            oldDepartment =
                departmentSelect.val();

        }


        /*
        |--------------------------------------------------------------------------
        | Clear Department
        |--------------------------------------------------------------------------
        */

        departmentSelect.empty();

        departmentSelect.append(
            '<option value="">Select Department</option>'
        );


        /*
        |--------------------------------------------------------------------------
        | Clear Subject
        |--------------------------------------------------------------------------
        */

        subjectSelect.empty();

        subjectSelect.append(
            '<option value="">Select Subject</option>'
        );


        /*
        |--------------------------------------------------------------------------
        | Clear Chapter
        |--------------------------------------------------------------------------
        */

        chapterSelect.empty();

        chapterSelect.append(
            '<option value="">Select Chapter</option>'
        );


        /*
        |--------------------------------------------------------------------------
        | No Course
        |--------------------------------------------------------------------------
        */

        if (!courseId) {

            departmentSelect.trigger('change');

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Get Departments
        |--------------------------------------------------------------------------
        */

        $.ajax({

            type: 'GET',

            url:
                '/get_departments_by_course/' +
                courseId,


            success: function (response) {

                if (
                    response.success &&
                    response.data
                ) {

                    $.each(
                        response.data,
                        function (key, department) {

                            let selected = '';

                            if (
                                keepSelectedDepartment &&
                                oldDepartment ==
                                department.id
                            ) {

                                selected =
                                    'selected';

                            }


                            departmentSelect.append(

                                '<option value="' +
                                department.id +
                                '" ' +
                                selected +
                                '>' +
                                department.name +
                                '</option>'

                            );

                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Refresh Department
                |--------------------------------------------------------------------------
                */

                departmentSelect.trigger('change');

            },


            error: function (xhr) {

                console.log(
                    'Department loading error:',
                    xhr.responseText
                );

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD SUBJECTS BY DEPARTMENT
    |--------------------------------------------------------------------------
    */

    function loadSubjects(row) {

        let departmentSelect =
            row.find('.department-select');

        let subjectSelect =
            row.find('.subject-select');

        let chapterSelect =
            row.find('.chapter-select');

        let departmentId =
            departmentSelect.val();


        /*
        |--------------------------------------------------------------------------
        | Clear Subject
        |--------------------------------------------------------------------------
        */

        subjectSelect.empty();

        subjectSelect.append(
            '<option value="">Select Subject</option>'
        );


        /*
        |--------------------------------------------------------------------------
        | Clear Chapter
        |--------------------------------------------------------------------------
        */

        chapterSelect.empty();

        chapterSelect.append(
            '<option value="">Select Chapter</option>'
        );


        /*
        |--------------------------------------------------------------------------
        | No Department
        |--------------------------------------------------------------------------
        */

        if (!departmentId) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Get Subjects
        |--------------------------------------------------------------------------
        */

        $.ajax({

            type: 'GET',

            url:
                '/get_subjects/' +
                departmentId,


            success: function (response) {

                let subjects =
                    response.data || response;


                $.each(
                    subjects,
                    function (key, subject) {

                        subjectSelect.append(

                            '<option value="' +
                            subject.id +
                            '">' +
                            subject.name +
                            '</option>'

                        );

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Refresh Subject
                |--------------------------------------------------------------------------
                */

                subjectSelect.trigger('change');

            },


            error: function (xhr) {

                console.log(
                    'Subject loading error:',
                    xhr.responseText
                );

            }

        });

    }


    /*
|--------------------------------------------------------------------------
| LOAD CHAPTERS BY SUBJECT
|--------------------------------------------------------------------------
*/

function loadChapters(row) {

    let subjectSelect =
        row.find('.subject-select');

    let chapterSelect =
        row.find('.chapter-select');

    let subjectId =
        subjectSelect.val();


    /*
    |--------------------------------------------------------------------------
    | Clear Chapter
    |--------------------------------------------------------------------------
    */

    chapterSelect.empty();

    chapterSelect.append(
        '<option value="">Select Chapter</option>'
    );


    /*
    |--------------------------------------------------------------------------
    | No Subject
    |--------------------------------------------------------------------------
    */

    if (!subjectId) {

        loadAvailableQuestions(row);

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Get Chapters
    |--------------------------------------------------------------------------
    */

    $.ajax({

        type: 'GET',

        url:
            '/get_chapters/' +
            subjectId,


        success: function (response) {

            let chapters =
                response.data || response;


            $.each(
                chapters,
                function (key, chapter) {

                    chapterSelect.append(

                        '<option value="' +
                        chapter.id +
                        '">' +
                        chapter.name +
                        '</option>'

                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Refresh Chapter
            |--------------------------------------------------------------------------
            */

            chapterSelect.trigger('change');

        },


        error: function (xhr) {

            console.log(
                'Chapter loading error:',
                xhr.responseText
            );

        }

    });

}


/*
|--------------------------------------------------------------------------
| LOAD AVAILABLE QUESTION COUNT
|--------------------------------------------------------------------------
*/

function loadAvailableQuestions(row) {

    let courseId =
        row.find('.course-select').val();

    let departmentId =
        row.find('.department-select').val();

    let subjectId =
        row.find('.subject-select').val();

    let chapterId =
        row.find('.chapter-select').val();

    let questionCount =
        row.find('.available-question-count');


    /*
    |--------------------------------------------------------------------------
    | Reset Count
    |--------------------------------------------------------------------------
    */

    questionCount.text('0');


    /*
    |--------------------------------------------------------------------------
    | Course Required
    |--------------------------------------------------------------------------
    */

    if (!courseId) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Get Available Questions
    |--------------------------------------------------------------------------
    */

    $.ajax({

        type: 'GET',

        url:
            '/paper/get-available-questions',

        data: {

    course_id:
        courseId,

    department_id:
        departmentId,

    subject_id:
        subjectId,

    chapter_id:
        chapterId,

    paper_id:
        '{{ $paper->id }}'

},


        success: function (response) {

    /*
    |--------------------------------------------------------------------------
    | Show Available Question Count
    |--------------------------------------------------------------------------
    */

    if (response.success) {

        questionCount.text(
            response.count
        );

    }
    else {

        questionCount.text('0');

    }

},


        error: function (xhr) {

            console.log(
                'Available Question Error:',
                xhr.responseText
            );

            questionCount.text('0');

        }

    });

}


/*
|--------------------------------------------------------------------------
| PAGE LOAD
|--------------------------------------------------------------------------
*/

$('.department-question-row').each(function () {

    let row = $(this);

    loadDepartments(
        row,
        true
    );

});


/*
|--------------------------------------------------------------------------
| COURSE CHANGE
|--------------------------------------------------------------------------
*/

$(document).on(
    'change',
    '.course-select',
    function () {

        let row =
            $(this).closest(
                '.department-question-row'
            );

        loadDepartments(
            row,
            false
        );

        loadAvailableQuestions(row);
    }
);


/*
|--------------------------------------------------------------------------
| DEPARTMENT CHANGE
|--------------------------------------------------------------------------
*/

$(document).on(
    'change',
    '.department-select',
    function () {

        let row =
            $(this).closest(
                '.department-question-row'
            );

        loadSubjects(row);

        /*
        |--------------------------------------------------------------------------
        | Update Available Count
        |--------------------------------------------------------------------------
        */

        loadAvailableQuestions(row);

    }
);


/*
|--------------------------------------------------------------------------
| SUBJECT CHANGE
|--------------------------------------------------------------------------
*/

$(document).on(
    'change',
    '.subject-select',
    function () {

        let row =
            $(this).closest(
                '.department-question-row'
            );

        loadChapters(row);

        /*
        |--------------------------------------------------------------------------
        | Update Available Count
        |--------------------------------------------------------------------------
        */

        loadAvailableQuestions(row);

    }
);


/*
|--------------------------------------------------------------------------
| CHAPTER CHANGE
|--------------------------------------------------------------------------
*/

$(document).on(
    'change',
    '.chapter-select',
    function () {

        let row =
            $(this).closest(
                '.department-question-row'
            );

        /*
        |--------------------------------------------------------------------------
        | Update Available Count
        |--------------------------------------------------------------------------
        */

        loadAvailableQuestions(row);

    }
);


});


/*
|--------------------------------------------------------------------------
| QUESTION RANGE VALIDATION
|--------------------------------------------------------------------------
*/

$(document).on(
    'input',
    '.question-from, .question-last, .total-question-input',
    function () {

        let row = $(this).closest(
            '.department-question-row'
        );

        let available = parseInt(
            row.find('.available-question-count').text()
        ) || 0;

        let from = parseInt(
            row.find('.question-from').val()
        ) || 0;

        let last = parseInt(
            row.find('.question-last').val()
        ) || 0;

        let total = parseInt(
            row.find('.total-question-input').val()
        ) || 0;


        /*
        |--------------------------------------------------------------------------
        | Last Cannot Exceed Available
        |--------------------------------------------------------------------------
        */

        if (last > available && available > 0) {

            row.find('.question-last').val(
                available
            );

        }


        /*
        |--------------------------------------------------------------------------
        | From Cannot Exceed Last
        |--------------------------------------------------------------------------
        */

        if (
            from > 0 &&
            last > 0 &&
            from > last
        ) {

            row.find('.question-from').val(
                last
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Total Cannot Exceed Available
        |--------------------------------------------------------------------------
        */

        if (
            total > available &&
            available > 0
        ) {

            row.find('.total-question-input').val(
                available
            );

        }

    }
);

</script>

@endsection