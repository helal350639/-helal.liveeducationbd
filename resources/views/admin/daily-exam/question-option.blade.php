@extends('dashboard')

@section('title','Add Question')


@section('content')


<section class="content-header">

<h1>
Add Question
</h1>

</section>



<section class="content">


<div class="row">


<div class="col-md-12">


<div class="box box-primary">


<div class="box-header with-border">

<h3 class="box-title">

Add Question For:
{{ $dailyExam->exam_title }}

</h3>


</div>




<div class="box-body">


<div class="row">



<div class="col-md-6">


<a href="{{ route('admin.daily-exam.question.manual',$dailyExam->id) }}"
class="btn btn-success btn-lg btn-block">

<i class="fa fa-pencil"></i>

<br>

Add Manually

</a>


</div>





<div class="col-md-6">


<a href="#"
class="btn btn-info btn-lg btn-block">

<i class="fa fa-database"></i>

<br>

Add From Database

</a>


</div>




</div>


</div>


</div>


</div>


</div>


</section>


@endsection