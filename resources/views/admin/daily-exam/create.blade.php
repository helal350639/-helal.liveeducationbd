@extends('dashboard')

@section('title', 'Add Daily Exam')

@section('content')


<section class="content-header">

<h1>Daily Exam Details</h1>

<ol class="breadcrumb">
<li>
<a href="#">
<i class="fa fa-dashboard"></i>
Dashboard
</a>
</li>

<li class="active">
Add Daily Exam
</li>

</ol>

</section>



<section class="content">


<div class="row">

<div class="col-xs-12">


<div class="box">


<div class="box-header">

<h3 class="box-title">
Daily Exam Details
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


<form method="POST" action="{{ route('admin.daily-exam.store') }}">

@csrf



<div class="row">


<div class="col-md-4">

<div class="form-group">

<label>Course</label>

<select name="course_id" class="form-control">

<option value="">Select One</option>
<option value="1">Test Course</option>

</select>

</div>

</div>



<div class="col-md-4">

<div class="form-group">

<label>Batch</label>

<select name="department_id" class="form-control">

<option value="">Select One</option>
<option value="1">Test Department</option>

</select>

</div>

</div>



<div class="col-md-4">

<div class="form-group">

<label>Department</label>

<select name="department_id" class="form-control">

<option value="">Select One</option>
<option value="1">Test Department</option>

</select>

</div>

</div>


</div>




<div class="form-group">

<label>Header</label>

<textarea name="header" class="form-control" rows="4"></textarea>

</div>



<div class="form-group">

<label>Details</label>

<textarea name="details" class="form-control" rows="4"></textarea>

</div>



<div class="form-group">

<label>Banner</label>

<input type="file" name="banner" class="form-control">

</div>




<div class="row">


<div class="col-md-6">

<div class="form-group">

<label>Exam Title</label>

<input type="text"
name="exam_title"
class="form-control"
placeholder="Daily Exam Title">

</div>

</div>



<div class="col-md-6">

<div class="form-group">

<label>Questions Limit</label>

<input type="number"
name="question_limit"
class="form-control"
placeholder="Total Question">

</div>

</div>


</div>



<hr>



<div class="row">


<div class="col-md-6">


<div class="form-group">

<label>Exam Start Time</label>

<input type="datetime-local"
name="start_time"
class="form-control">

</div>



<div class="form-group">

<label>Exam Finished Time</label>

<input type="datetime-local"
name="finish_time"
class="form-control">

</div>



<div class="form-group">

<label>Mark (Correct Answer)</label>

<input type="number"
name="correct_mark"
class="form-control">

</div>



<div class="form-group">

<label>Attempt</label>

<select name="attempt" class="form-control">

<option>Select One</option>
<option value="one">One Time</option>
<option value="multiple">Multiple Time</option>

</select>

</div>



<div class="form-group">

<label>Display Question?</label>

<select name="display_question" class="form-control">

<option>Select One</option>
<option value="yes">Yes</option>
<option value="no">No</option>

</select>

</div>


</div>





<div class="col-md-6">


<div class="form-group">

<label>Exam Running Time (Minute)</label>

<input type="number"
name="duration"
class="form-control">

</div>




<div class="form-group">

<label>Negative Mark</label>

<input type="number"
name="negative_mark"
class="form-control">

</div>




<div class="form-group">

<label>Random Question?</label>

<select name="random_question" class="form-control">

<option>Select One</option>
<option value="yes">Yes</option>
<option value="no">No</option>

</select>

</div>




<div class="form-group">

<label>Exam Permission</label>

<select name="permission" class="form-control">

<option>Select One</option>
<option value="all">All</option>
<option value="selected">Selected</option>

</select>

</div>




<div class="form-group">

<label>Paid Exam?</label>

<select name="paid_exam" class="form-control">

<option>Select One</option>
<option value="yes">Yes</option>
<option value="no">No</option>

</select>

</div>




<div class="form-group">

<label>Max Candidates</label>

<input type="number"
name="max_candidate"
class="form-control">

</div>



</div>


</div>



<br>



<button type="submit" class="btn btn-success">

<i class="fa fa-save"></i>

Save

</button>



<button type="reset" class="btn btn-warning">

Reset

</button>



</form>



</div>


</div>


</div>


</div>


</section>


@endsection