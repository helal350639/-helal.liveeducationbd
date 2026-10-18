@extends('homepage')


@section('title','Course Routine')


@section('content')


<section class="py-5">

<div class="container">


<a href="javascript:history.back()" 
class="btn btn-outline-success mb-4">
    ← Back
</a>



<div class="card p-4 text-center mb-4">


<h2>
{{ $batch->name }}
</h2>


<h5 class="text-muted">
Class / Exam Routine
</h5>


</div>





<div class="card p-4 mb-4">


<h4 class="mb-3">
Department Select করুন
</h4>



<div class="d-flex flex-wrap gap-2">


@if($routine)


@foreach($departments as $department)


<button 
type="button"
class="btn btn-outline-success department-btn"
data-id="{{ $department->id }}">

{{ $department->name }}

</button>


@endforeach



@else


<p class="text-danger">
এই ব্যাচের জন্য এখনো Routine Published হয়নি।
</p>


@endif



</div>


</div>







<div class="card p-4" id="routineBox" style="display:none;">

<h4>
Routine
</h4>



<div class="table-responsive">


<table class="table table-bordered" id="routine">


<tr>

<td class="text-center">

Department নির্বাচন করুন

</td>

</tr>


</table>



</div>


</div>



</div>


</section>




<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<script>

$(document).ready(function(){


    console.log("Routine page loaded");



    $(document).on('click','.department-btn',function(){

         $("#routineBox").slideDown();

        let departmentId = $(this).data('id');


console.log("Department ID:", departmentId);



$.ajax({

    url: "{{ route('home.routine.filter',$routine->id) }}/" + departmentId,

    type: "GET",

success:function(response){ 

    console.log("FULL RESPONSE", response);

    console.log("ROW FIRST DATA", response.routine_rows[0]);

    let html = "";


    if(response.routine_rows.length > 0){


        html += `

        <tr>
            <th>Exam No</th>
            <th>Subject 1</th>
            <th>Subject 2</th>
            <th>Subject 3</th>
            <th>Department</th>
            <th>Date & Time</th>
            <th>Status</th>
        </tr>

        `;



        response.routine_rows.forEach(function(row){


            let subject1 = response.subjects[0]?.subject?.name ?? '';
            let subject2 = response.subjects[1]?.subject?.name ?? '';
            let subject3 = response.subjects[2]?.subject?.name ?? '';

            let department = response.departments[0]?.department?.name ?? '';




            html += `

<tr>

<td>
${row.paper_name ?? ''}
</td>


<td>
${subject1}
</td>


<td>
${subject2}
</td>


<td>
${subject3}
</td>


<td>
${department}
</td>

<td>

<div>
${new Date(row.exam_at).toLocaleDateString('en-GB',{
    day:'2-digit',
    month:'short',
    year:'numeric'
})}
</div>

<div class="text-muted">

${new Date(row.exam_at).toLocaleTimeString('en-US',{
    hour:'2-digit',
    minute:'2-digit',
    hour12:true
})}

</div>

</td>

<td>
<span class="text-info">
Next
</span>
</td>


</tr>

`;


        });


    }
    else{


        html = `

        <tr>

        <td colspan="6" class="text-center">

        Routine পাওয়া যায়নি

        </td>

        </tr>

        `;

    }



    $("#routine").html(html);



}


});


    });



});


</script>

<style>

#routine{
    width:100%;
    border-collapse: collapse;
}


#routine th,
#routine td{

    border:1px solid #ddd;
    padding:10px;
    text-align:center;
    vertical-align:middle;

    white-space:normal;
    word-break:break-word;
    overflow-wrap:break-word;

}



/* Mobile View */

@media(max-width:576px){


    #routine th,
    #routine td{

        padding:5px;
        font-size:10px;

        white-space:normal;
        word-break:break-word;
        overflow-wrap:anywhere;

        height:auto;

    }


}

</style>


@endsection