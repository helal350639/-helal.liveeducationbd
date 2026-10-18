@extends('dashboard')


@section('title','Edit Question Paper')



@section('content')


<section class="content-header">

<h1>
Edit Question Paper
</h1>

</section>




<section class="content">


<div class="box box-primary">


<div class="box-header with-border">

<h3 class="box-title">

Update Paper

</h3>

</div>




<div class="box-body">


<form method="POST"
action="{{ route('admin.daily-exam.paper.update',$paper->id) }}">


@csrf

@method('PUT')




<div class="form-group">

<label>
Question Paper Name
</label>


<input type="text"
name="paper_name"
class="form-control"
value="{{ $paper->paper_name }}">


</div>





<div class="form-group">


<label>
Description
</label>


<textarea name="description"
class="form-control"
rows="4">{{ $paper->description }}</textarea>


</div>





<div class="form-group">


<label>
Status
</label>


<select name="status"
class="form-control">


<option value="active"
@if($paper->status=='active') selected @endif>

Active

</option>


<option value="inactive"
@if($paper->status=='inactive') selected @endif>

Inactive

</option>


</select>


</div>





<button type="submit"
class="btn btn-success">

<i class="fa fa-save"></i>

Update

</button>




<a href="{{ route('admin.daily-exam.show',$paper->daily_exam_id) }}"
class="btn btn-danger">

Back

</a>



</form>


</div>


</div>


</section>


@endsection