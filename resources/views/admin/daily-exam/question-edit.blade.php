@extends('dashboard')


@section('title','Edit Question')


@section('content')


<section class="content-header">

<h1>
<i class="fa fa-edit"></i>
Edit Question
</h1>

</section>





<section class="content">


<div class="box box-primary">


<div class="box-header with-border">


<h3 class="box-title">

Update Question

</h3>



<a href="{{ route('admin.daily-exam.show',$dailyExam->id) }}"
class="btn btn-default pull-right">

<i class="fa fa-arrow-left"></i>

Back

</a>


</div>





<div class="box-body">



<form method="POST"
action="{{ route('admin.daily-exam.question.update',$question->id) }}">


@csrf

@method('PUT')



<input type="hidden"
name="daily_exam_id"
value="{{ $dailyExam->id }}">







<!-- Paper Select -->


<div class="form-group">


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


<option value="{{ $paper->id }}"
@if($question->paper_id == $paper->id)
selected
@endif
>

{{ $paper->paper_name }}

</option>


@endforeach



</select>


</div>









<!-- Question -->


<div class="form-group">


<label>
Question
</label>



<textarea name="question"
class="form-control"
rows="5"
required>{{ $question->question }}</textarea>


</div>








<!-- Options -->


<div class="row">


<div class="col-md-6">


<label>
Option A
</label>


<input type="text"
name="option_a"
class="form-control"
value="{{ $question->option_a }}"
required>


</div>



<div class="col-md-6">


<label>
Option B
</label>


<input type="text"
name="option_b"
class="form-control"
value="{{ $question->option_b }}"
required>


</div>



</div>





<br>





<div class="row">


<div class="col-md-6">


<label>
Option C
</label>


<input type="text"
name="option_c"
class="form-control"
value="{{ $question->option_c }}"
required>


</div>



<div class="col-md-6">


<label>
Option D
</label>


<input type="text"
name="option_d"
class="form-control"
value="{{ $question->option_d }}"
required>


</div>



</div>









<br>







<!-- Correct Answer -->


<div class="form-group">


<label>
Correct Answer
</label>



<select name="correct_answer"
class="form-control"
required>



<option value="A"
@if($question->correct_answer == 'A')
selected
@endif
>
Option A
</option>



<option value="B"
@if($question->correct_answer == 'B')
selected
@endif
>
Option B
</option>



<option value="C"
@if($question->correct_answer == 'C')
selected
@endif
>
Option C
</option>



<option value="D"
@if($question->correct_answer == 'D')
selected
@endif
>
Option D
</option>



</select>


</div>









<!-- Mark -->


<div class="form-group">


<label>
Mark
</label>


<input type="number"
name="mark"
class="form-control"
value="{{ $question->mark }}"
min="1"
required>


</div>









<button type="submit"
class="btn btn-success">


<i class="fa fa-save"></i>

Update Question


</button>





<a href="{{ route('admin.daily-exam.show',$dailyExam->id) }}"
class="btn btn-danger">

Cancel

</a>




</form>


</div>


</div>


</section>



@endsection