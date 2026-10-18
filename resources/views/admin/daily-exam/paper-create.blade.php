@extends('dashboard')


@section('title','Create Question Paper')



@section('content')


<section class="content-header">

<h1>
Create Question Paper
</h1>

</section>





<section class="content">


<div class="box box-primary">


<div class="box-header with-border">


<h3 class="box-title">

Exam:
{{ $dailyExam->exam_title }}

</h3>


</div>





<div class="box-body">



<form method="POST"
action="{{ route('admin.daily-exam.paper.store') }}">


@csrf



<input type="hidden"
name="daily_exam_id"
value="{{ $dailyExam->id }}">





<div class="form-group">


<label>
Question Paper Name
</label>


<input type="text"
name="paper_name"
class="form-control"
placeholder="Example: Daily Exam Paper-01">


</div>






<div class="form-group">


<label>
Description
</label>


<textarea name="description"
class="form-control"
rows="4"
placeholder="Paper Description"></textarea>


</div>






<div class="form-group">


<label>
Status
</label>


<select name="status"
class="form-control">


<option value="active">
Active
</option>


<option value="inactive">
Inactive
</option>


</select>


</div>







<button type="submit"
class="btn btn-success">


<i class="fa fa-save"></i>

Save Paper


</button>





<a href="{{ route('admin.daily-exam.show',$dailyExam->id) }}"
class="btn btn-danger">


<i class="fa fa-arrow-left"></i>

Back


</a>





</form>



</div>


</div>


</section>


@endsection