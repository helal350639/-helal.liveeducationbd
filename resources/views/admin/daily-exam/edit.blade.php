@extends('dashboard')

@section('title','Edit Daily Exam')


@section('content')


<section class="content-header">

<h1>
Edit Daily Exam
</h1>

<ol class="breadcrumb">

<li>
<a href="#">
<i class="fa fa-dashboard"></i>
Dashboard
</a>
</li>

<li class="active">
Edit Daily Exam
</li>

</ol>

</section>




<section class="content">


<div class="row">


<div class="col-xs-12">


<div class="box box-primary">


<div class="box-header with-border">


<h3 class="box-title">
Edit Daily Exam Details
</h3>


<div class="box-tools pull-right">

<a href="{{ route('admin.daily-exam.index') }}"
class="btn btn-danger btn-sm">

<i class="fa fa-arrow-left"></i>
Back

</a>

</div>


</div>





<div class="box-body">


<form method="POST"
action="{{ route('admin.daily-exam.update',$dailyExam->id) }}">


@csrf

@method('PUT')



<div class="row">


<div class="col-md-6">


<div class="form-group">

<label>
Exam Title
</label>


<input type="text"
name="exam_title"
class="form-control"
value="{{ $dailyExam->exam_title }}">


</div>


</div>




<div class="col-md-6">


<div class="form-group">

<label>
Question Limit
</label>


<input type="number"
name="question_limit"
class="form-control"
value="{{ $dailyExam->question_limit }}">


</div>


</div>


</div>





<div class="row">


<div class="col-md-6">


<div class="form-group">

<label>
Exam Start Time
</label>


<input type="datetime-local"
name="start_time"
class="form-control"
value="{{ $dailyExam->start_time }}">


</div>


</div>




<div class="col-md-6">


<div class="form-group">

<label>
Exam Finish Time
</label>


<input type="datetime-local"
name="finish_time"
class="form-control"
value="{{ $dailyExam->finish_time }}">


</div>


</div>


</div>






<div class="row">


<div class="col-md-6">


<div class="form-group">

<label>
Correct Mark
</label>


<input type="number"
name="correct_mark"
class="form-control"
value="{{ $dailyExam->correct_mark }}">


</div>


</div>



<div class="col-md-6">


<div class="form-group">

<label>
Duration (Minute)
</label>


<input type="number"
name="duration"
class="form-control"
value="{{ $dailyExam->duration }}">


</div>


</div>


</div>






<div class="row">


<div class="col-md-6">


<div class="form-group">

<label>
Negative Mark
</label>


<input type="number"
name="negative_mark"
class="form-control"
value="{{ $dailyExam->negative_mark }}">


</div>


</div>



<div class="col-md-6">


<div class="form-group">

<label>
Max Candidates
</label>


<input type="number"
name="max_candidate"
class="form-control"
value="{{ $dailyExam->max_candidate }}">


</div>


</div>


</div>






<div class="form-group">

<label>
Header
</label>


<textarea name="header"
class="form-control"
rows="3">{{ $dailyExam->header }}</textarea>


</div>





<div class="form-group">

<label>
Details
</label>


<textarea name="details"
class="form-control"
rows="4">{{ $dailyExam->details }}</textarea>


</div>





<br>


<button type="submit"
class="btn btn-success">

<i class="fa fa-save"></i>

Update

</button>



<a href="{{ route('admin.daily-exam.index') }}"
class="btn btn-warning">

Cancel

</a>




</form>


</div>


</div>


</div>


</div>


</section>



@endsection