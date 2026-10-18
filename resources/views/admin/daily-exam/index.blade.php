@extends('dashboard')

@section('title', 'Daily Exams')

@section('content')


<section class="content-header">

    <h1>
        Daily Exams
        <small>Manage Daily Exam</small>
    </h1>

</section>



<section class="content">


<div class="box box-primary">


    <div class="box-header with-border">


        <h3 class="box-title">
            List of Daily Exams
        </h3>


        <a href="{{ route('admin.daily-exam.create') }}" 
           class="btn btn-success pull-right">

            <i class="fa fa-plus"></i>
            Add Daily Exam

        </a>


    </div>




    <div class="box-body">


        <table class="table table-bordered table-striped">


            <thead>

                <tr>

                    <th>Id</th>
                    <th>Exam Title</th>
                    <th>Date</th>
                    <th>Total Question</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th width="150">Action</th>

                </tr>

            </thead>



            <tbody>


            @foreach($dailyExams as $exam)


                <tr>


                    <td>
                        {{ $exam->id }}
                    </td>


                    <td>
                        {{ $exam->exam_title }}
                    </td>


                    <td>
                        {{ $exam->created_at->format('d-m-Y') }}
                    </td>


                    <td>
                        {{ $exam->question_limit }}
                    </td>


                    <td>
                        {{ $exam->duration ?? 0 }} Minutes
                    </td>


                    <td>

                        <span class="label label-success">
                            Active
                        </span>

                    </td>


                    <td>


                        <!-- View -->

                        <a href="{{ route('admin.daily-exam.show',$exam->id) }}"
                           class="btn btn-info btn-sm"
                           title="View">

                            <i class="fa fa-file-text"></i>

                        </a>



                        <!-- Edit (Later) -->

                        <a href="{{ route('admin.daily-exam.edit',$exam->id) }}"
class="btn btn-warning btn-sm"
title="Edit">

<i class="fa fa-edit"></i>


                        <!-- Delete (Later) -->

                        <a href="#"
                           class="btn btn-danger btn-sm"
                           title="Delete">

                            <i class="fa fa-trash"></i>

                        </a>


                    </td>


                </tr>


            @endforeach



            </tbody>


        </table>


    </div>


</div>


</section>


@endsection