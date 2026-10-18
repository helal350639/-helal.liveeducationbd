@extends('dashboard')


@section('title','Daily Exam Details')


@section('content')


<section class="content-header">

<h1>
Daily Exam Details
</h1>

</section>





<section class="content">



<!-- Exam Details -->


<div class="box box-primary">


<div class="box-header with-border">


<h3 class="box-title">

{{ $dailyExam->exam_title }}

</h3>


</div>





<div class="box-body">


<table class="table table-bordered">


<tr>
<th width="30%">Exam Title</th>
<td>{{ $dailyExam->exam_title }}</td>
</tr>


<tr>
<th>Total Question</th>
<td>{{ $dailyExam->question_limit }}</td>
</tr>


<tr>
<th>Duration</th>
<td>{{ $dailyExam->duration }} Minutes</td>
</tr>


<tr>
<th>Correct Mark</th>
<td>{{ $dailyExam->correct_mark }}</td>
</tr>


<tr>
<th>Negative Mark</th>
<td>{{ $dailyExam->negative_mark }}</td>
</tr>


<tr>
<th>Start Time</th>
<td>{{ $dailyExam->start_time }}</td>
</tr>


<tr>
<th>Finish Time</th>
<td>{{ $dailyExam->finish_time }}</td>
</tr>


</table>




<a href="{{ route('admin.daily-exam.paper.create',$dailyExam->id) }}"
class="btn btn-primary">

<i class="fa fa-file"></i>

Add Question Paper

</a>




<a href="{{ route('admin.daily-exam.question.create',$dailyExam->id) }}"
class="btn btn-success">

<i class="fa fa-plus"></i>

Add Question

</a>




<a href="{{ route('admin.daily-exam.index') }}"
class="btn btn-danger">

<i class="fa fa-arrow-left"></i>

Back

</a>


</div>


</div>









<!-- Question Paper List -->


<div class="box box-info">


<div class="box-header with-border">

<h3 class="box-title">

<i class="fa fa-file"></i>

Question Papers

</h3>

</div>




<div class="box-body">



@if(isset($papers) && $papers->count() > 0)



@foreach($papers as $paper)



<div class="alert alert-info">


<i class="fa fa-file-text"></i>

<strong>

{{ $paper->paper_name }}

</strong>



</div>



@endforeach



@else


<div class="alert alert-warning">

No Question Paper Added Yet.

</div>


@endif



</div>


</div>









<!-- Question List -->


<div class="box box-warning">


<div class="box-header with-border">


<h3 class="box-title">

<i class="fa fa-list"></i>

Question List

</h3>


</div>




<div class="box-body">



@if($questions->count() > 0)



@foreach($questions as $question)



<div class="box box-default">



<div class="box-header">


<h4>


<strong>

{{ $loop->iteration }}.

</strong>


{{ $question->question }}



</h4>



<p>


<strong>
Paper:
</strong>


<span class="label label-primary">

{{ $question->paper->paper_name ?? 'No Paper' }}

</span>


</p>


</div>







<div class="box-body">



<div class="row">


<div class="col-md-6">


<p>
<b>A:</b>
{{ $question->option_a }}
</p>


<p>
<b>B:</b>
{{ $question->option_b }}
</p>


</div>





<div class="col-md-6">


<p>
<b>C:</b>
{{ $question->option_c }}
</p>


<p>
<b>D:</b>
{{ $question->option_d }}
</p>


</div>


</div>





<hr>




<p>


<strong>
Correct Answer:
</strong>


<span class="label label-success">

{{ $question->correct_answer }}

</span>




&nbsp;&nbsp;




<strong>
Mark:
</strong>


{{ $question->mark }}



</p>



</div>


</div>



@endforeach



@else



<div class="alert alert-info">

<i class="fa fa-info-circle"></i>

No Question Added Yet.

</div>



@endif



</div>


</div>





</section>


@endsection