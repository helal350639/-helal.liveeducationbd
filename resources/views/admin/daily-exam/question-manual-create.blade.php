@extends('dashboard')

@section('title','Add Manual Question')


@section('content')


<section class="content-header">

<h1>
<i class="fa fa-book"></i> Add Manual Question
</h1>

</section>




<section class="content">


<div class="box box-primary">


<div class="box-header with-border">


<h3 class="box-title">
Question Create
</h3>



<a href="{{ route('admin.daily-exam.show',$dailyExam->id) }}"
class="btn btn-default pull-right">

<i class="fa fa-arrow-left"></i>

Back To Exam

</a>


</div>





<div class="box-body">



<form method="POST"
action="{{ route('admin.daily-exam.question.store') }}">

@csrf



<input type="hidden"
name="daily_exam_id"
value="{{ $dailyExam->id }}">







<!-- Question Settings -->


<div class="box box-info">


<div class="box-header">

<h4>
<i class="fa fa-cog"></i>
Question Settings
</h4>

</div>




<div class="box-body">



<div class="row">


<div class="col-md-4">


<label>
Select Question Paper
</label>



<select name="paper_id"
class="form-control"
required>


<option value="">
Select Paper
</option>



@foreach($papers as $paper)


<option value="{{ $paper->id }}">

{{ $paper->paper_name }}

</option>


@endforeach



</select>


</div>





<div class="col-md-3">


<label>
Question Type
</label>


<select name="question_type"
class="form-control">


<option value="MCQ">
MCQ
</option>


</select>


</div>





<div class="col-md-3">


<label>
Difficulty Level
</label>


<select name="difficulty"
class="form-control">


<option value="Easy">
Easy
</option>


<option value="Medium" selected>
Medium
</option>


<option value="Hard">
Hard
</option>


</select>


</div>


</div>






<br>



<div class="row">


<div class="col-md-4">


<label>
Question Order
</label>


<input type="number"
name="question_order"
class="form-control"
placeholder="1">


</div>


</div>



</div>


</div>









<!-- Question -->


<div class="box box-success">


<div class="box-header">

<h4>

<i class="fa fa-pencil"></i>

Question Title

</h4>

</div>




<div class="box-body">


<label>
Question
</label>



<textarea name="question"
class="form-control"
rows="5"
placeholder="Enter question here"
required></textarea>



</div>


</div>









<!-- MCQ OPTIONS -->


<div class="box box-warning">


<div class="box-header with-border">


<h4 class="box-title">

<i class="fa fa-list"></i>

MCQ Options

</h4>


</div>




<div class="box-body">



<div class="form-group">

<div class="input-group input-group-lg">


<span class="input-group-addon"
style="background:#00a65a;color:white;font-weight:bold;">
A
</span>


<input type="text"
name="option_a"
class="form-control"
placeholder="Enter option A"
required>


</div>

</div>





<div class="form-group">

<div class="input-group input-group-lg">


<span class="input-group-addon"
style="background:#0073b7;color:white;font-weight:bold;">
B
</span>


<input type="text"
name="option_b"
class="form-control"
placeholder="Enter option B"
required>


</div>

</div>






<div class="form-group">

<div class="input-group input-group-lg">


<span class="input-group-addon"
style="background:#f39c12;color:white;font-weight:bold;">
C
</span>


<input type="text"
name="option_c"
class="form-control"
placeholder="Enter option C"
required>


</div>

</div>






<div class="form-group">

<div class="input-group input-group-lg">


<span class="input-group-addon"
style="background:#dd4b39;color:white;font-weight:bold;">
D
</span>


<input type="text"
name="option_d"
class="form-control"
placeholder="Enter option D"
required>


</div>

</div>



</div>


</div>









<!-- Correct Answer -->


<div class="box box-danger">


<div class="box-header">

<h4>

<i class="fa fa-check"></i>

Correct Answer

</h4>

</div>



<div class="box-body">


<select name="correct_answer"
class="form-control"
required>


<option value="">
Select Correct Answer
</option>


<option value="A">
Option A
</option>


<option value="B">
Option B
</option>


<option value="C">
Option C
</option>


<option value="D">
Option D
</option>


</select>


</div>


</div>









<!-- Question Mark -->


<div class="box box-default">


<div class="box-header">

<h4>

<i class="fa fa-star"></i>

Question Mark

</h4>

</div>



<div class="box-body">


<input type="number"
name="mark"
class="form-control"
value="1"
min="1"
required>


</div>


</div>









<button type="submit"
class="btn btn-success btn-lg">


<i class="fa fa-save"></i>

Save Question


</button>





<a href="{{ route('admin.daily-exam.show',$dailyExam->id) }}"
class="btn btn-danger btn-lg">


Cancel


</a>




</form>


</div>


</div>



</section>


@endsection