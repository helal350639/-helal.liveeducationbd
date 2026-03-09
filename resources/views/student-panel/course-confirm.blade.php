@php
use \App\Http\Controllers\SourceCtrl;
$source = new SourceCtrl;
$user = Auth::guard('student')->user();
$student = [];
if($user)
{
  $student = \App\Models\Student::find($user->id);
}
$value = $batch;
//price calculations
$price = $discount = $reference_discount = $selling_price = 0;
if($value->discount && $value->offer_end_at && $value->offer_end_at > now()) {
  $discount = $value->discount;
}

if($value->reference_discount && Session::get('batch-referrer') && Session::get('batch-referrer')['batch'] == $batch->id){
  $reference_discount = $value->reference_discount;
  $discount = $discount + $value->reference_discount;
}

$price = $value->price + $value->fee;
$selling_price = $price - $discount;
//price calculation end
@endphp

@extends('student')
@section('title', 'Course')
@section('content')
<style>
  .checkbox{padding-left: 25px}
  .panel ::-webkit-scrollbar{width: 5px;}
  ::-webkit-scrollbar-thumb{background-color: #ddd}
  .course-image{width:100%}
  .teacher-title p{margin:0}
  .box-title {display: block;width:100%}
</style>

    <!-- Main content -->
    <section class="container p-md-5">
      <div class="row">
        <!-- Content Header (Page header) -->
        <div class="content-header text-center">
          <h1>Course Confirm</h1>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 offset-md-3 pricing pd-3">
          <!-- Apply to the course -->
          <form action="{{route('payment.proceed')}}" method="post">
            @csrf
            @if(Session::get('_confirm'))
            <input type="hidden" name="batch_id" value="{{$value->id}}">
            
            <input type="hidden" name="name" value="{{$student->name}}" />
            <input type="hidden" name="student_id" value="{{$student->id}}" />
            <input type="hidden" name="department_id" value="{{$department->id}}" />
            <input type="hidden" name="email" value="{{$student->email}}" />
            <input type="hidden" name="phone" value="{{$student->contact}}" />
            <input type="hidden" name="address1" value="" />
            <input type="hidden" name="total" value="{{$price}}" />
            <div class="card card-default p-3">
              <div class="penel-heading no-padding" style="text-align: center;padding:15px">
                {{-- <img class="course-image" src="{{ $value->banner? $value->banner : '/img/course.png'}}" alt="" /> --}}
              </div>
              <div class="card-heading">
                <h3>
                  @if($discount) 
                  <del> &#2547;{{$source->point0($price)}}</del> &nbsp; <span class="label label-warning"> &#2547;{{$source->point0($discount)}} ছাড়</span>
                  @endif
                  <b> &nbsp; &#2547;{{$source->point0($selling_price)}}</b>
                  </h3>
                  @if($value->offer_end_at)
                  <p style="color: red">ডিস্কাউন্ট অফারের মেয়াদ <b>{{$source->dtformat($value->offer_end_at)}}</b></p>
                  @endif
                <p>{{$value->subtitle}}</p>
              </div>
              @if(!empty($user->id) && !$value->students()->where('id', $user->id)->first())
              <div class="card-body table-responsive">
                <table class="table">
                  <tr>
                    <td>কোর্স এর নাম</td>
                    <th>{{$batch->course ? $batch->course->name:''}}</th>
                  </tr>
                  <tr>
                    <td>ব্যাচ এর নাম</td>
                    <th>{{$batch->name}}</th>
                  </tr>
                  <tr>
                    <td>আপনার নির্বাচিত ডিপার্টমেন্ট</td>
                    <th>{{$department->name}}</th>
                  </tr>
                  <tr>
                    <td>কোর্সটির মূল্য </td>
                    <th>&#2547; {{$price-$value->fee}}</th>
                  </tr>
                  <tr>
                    <td>অন্যান্য ফী </td>
                    <th>
                      &#2547; {{$source->point0($value->fee)}}
                      <div class="btn btn-info" data-bs-toggle="tooltip" data-bs-title="{{$value->extra_fee_note}}">?</div>
                    </th>
                  </tr>
                  <tr>
                    <td>ডিস্কাউন্ট </td>
                    <th>&#2547; {{$source->point0($value->discount)}}</th>
                  </tr>
                  @if($reference_discount)
                  <tr>
                    <td>রিফারেন্স ডিস্কাউন্ট </td>
                    <th>&#2547; {{$source->point0($reference_discount)}}</th>
                  </tr>
                  @endif
                  <tr>
                    <th>মোট =</th>
                    <th>&#2547; {{$selling_price}}</th>
                  </tr>
                </table>
              </div>
              @endif
              <div class="card-footer">
                  <button class="btn btn-warning btn-block btn-lg" onsubmit="return confirm('Double check you provided information.')">&#2547; {{$selling_price}} পেমেন্ট কনফার্ম করুন</button>
                <div class="clearfix"></div>
              </div>
            </div>
            @endif
          </form>
        </div>
      </div>
    </section> <!-- /.content -->
@endsection
@section('scripts')
<script>
  // Set the date we're counting down to
  // var countDownDate = new Date("Oct 27, 2024 12:47:25").getTime();
  var countDownDate = new Date("{{$source->dtcformat($value->reg_end_at)}}").getTime();
  // var countDownDate = new Date().getTime();
  
  // Update the count down every 1 second
  var x = setInterval(function()
  {
    let timer = document.getElementById("timer");
  
    // Get today's date and time
    var now = new Date().getTime();
    // var now = new Date("{{$source->dtcformat($value->end_time)}}").getTime();
      
    // Find the distance between now and the count down date
    var distance = countDownDate - now;
      
    // Time calculations for days, hours, minutes and seconds
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
      
    // Output the result in an element with id="demo"
    let d = h = m = s = 0;
    if(days != 0)
    {
      d = days+" ";
    }
    if(hours != 0)
    {
      h = hours+" ";
    }
    if(minutes != 0)
    {
      m = minutes+" ";
    }
    if(seconds != 0)
    {
      s = seconds+" ";
    }
    // timer.innerHTML = hours + "h "
    // + minutes + "m " + seconds + "s ";
    if(m != null || s != null)
    {
    timer.innerHTML = d + "দিন " + h + "ঘণ্টা "
    + m + "মিনিট " + s + "সেকেন্ড ";
    }
    else
    {
      timer.innerHTML = '00:00:00';
    }

    // console.log(distance);
      
    // If the count down is over, write some text 
    if (distance < 60000 && distance > 59000)
    {
      timer.style.color='red';

    }

    if (distance < 0) {
      clearInterval(x);
      timer.innerHTML = '<span style="color:#d00">সময় শেষ</span>';
      // document.getElementById('questions_panel').innerHTML = '<div class="box box-warning"><a class="btn btn-warning" href="{{route("students.exam")}}">Back</div>';
      // submit the form automatically
      // submitExam();
    }
  }, 1000);
</script>
@endsection