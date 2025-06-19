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
@endphp

@extends('student')
@section('title', 'Course')
@section('stylesheets')
<meta name="description" content="{{$batch->subtitle}}"/>
<meta name="keywords" content="{{$batch->name}}"/>
<meta name="robots" content="index, follow, nocache"/>
<meta name="googlebot" content="index, follow, max-video-preview:-1, max-image-preview:large, max-snippet:-1"/>
<meta name="category" content="education"/>

<meta property="fb:app_id" content="1360109555168118" />
<meta property="og:type" content="video.other">
<meta property="og:url" content="{{route('home.course.show', $batch->id)}}" />
<meta property="og:title" content="{{$batch->name}}" />
<meta property="og:description" content="{{$batch->subtitle}}" />

@if($batch->video)

<meta property="og:image" content="https://img.youtube.com/vi/{{$value->video}}/maxresdefault.jpg">
<meta property="og:video" content="https://www.youtube.com/embed/{{$value->video}}">
<meta property="og:video:secure_url" content="https://www.youtube.com/embed/{{$value->video}}">
<meta property="og:video:type" content="text/html">
<meta property="og:video:width" content="1280">
<meta property="og:video:height" content="720">

@else

<meta property="og:logo" content="{{route('homepage').$batch->banner}}" />
<meta property="og:image" content="{{route('homepage').$batch->banner}}" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="{{$batch->name}}"/>
{{-- <meta property="og:site_name" content="Live Education BD" /> --}}

@endif

<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:site" content="@liveeducationbd"/>
<meta name="twitter:creator" content="@liveeducationbd"/>
<meta name="twitter:title" content="{{$batch->name}}"/>
<meta name="twitter:description" content="{{$batch->subtitle}}"/>
<meta name="twitter:image" content="{{route('homepage').$batch->banner}}"/>
<meta name="twitter:image:width" content="1200"/>
<meta name="twitter:image:height" content="630"/>
<meta name="twitter:image:alt" content="{{$batch->name}}"/>

<title>{{$batch->name}}</title>

@endsection
@section('content')
<style>
  .checkbox{padding-left: 25px}
  .card ::-webkit-scrollbar{width: 5px;}
  ::-webkit-scrollbar-thumb{background-color: #ddd}
  .course-image{width:100%}
  .teacher-title p{margin:0}
  .box-title {display: block;width:100%}
  .list{list-style: none}
  .list li{padding: 10px 0}
  .videoWrapper {
    position: relative;
    padding-bottom: 56.25%;
    /* 16:9 */
    padding-top: 25px;
    height: 0;
  }

  .videoWrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  }
</style>

  <!-- Main content -->
  <section class="our_courses">
    <div class="container py-3">
      <div class="row">
        <div class="title-area text-center">
          <h1>{{$value->course ? $value->course->name:''}}</h1>
        </div>
      </div>
      <div class="row">

        <div class="col col-md-4 order-md-last py-5 pt-0">
          <!-- Apply to the course -->
          <form action="{{route('students.course.apply')}}" method="post">
            @csrf
            <input type="hidden" name="batch_id" value="{{$value->id}}">
            <div class="card card-default">
              <div class="penel-heading no-padding" style="text-align: center;">
                @if($value->video)
                <div class="videoWrapper">
                  <iframe class="responsive-iframe" style="width: 100%; height:100%; border:5px solid #fff" src="https://www.youtube.com/embed/{{$value->video}}?autoplay=1&mute=1" allowfullscreen ></iframe>
                </div>
                @else
                <img class="course-image" src="{{ $value->banner? $value->banner : '/img/course.png'}}" alt="" />
                @endif
              </div>
              <div class="card-heading p-3">
                <h3>
                  @if($value->discount && $value->offer_end_at && $value->offer_end_at > date('Y-m-d H:i:s')) 
                  <del> &#2547;{{$source->point0($value->price)}}</del> &nbsp; <span class="label label-warning"> &#2547;{{$source->point0($value->discount)}} ছাড়</span>
                  @endif
                  <b> &nbsp; &#2547;{{$source->point0($value->net_price)}}</b>
                  </h3>
                  @if($value->offer_end_at && $value->offer_end_at > date('Y-m-d H:i:s'))
                  <p style="color: red">ডিস্কাউন্ট অফারের মেয়াদ <b>{{$source->dtformat($value->offer_end_at)}}</b></p>
                  @endif
                <p>{{$value->subtitle}}</p>
              </div>
              <div class="card-footer">
                @if(!empty($user->id) && $value->students()->where('id', $user->id)->first())
                <button class="btn btn-success btn-block" disabled>আপনি কোর্সটি কিনেছেন</button>
                @else
                  @if($value->reg_end_at && date('Y-m-d H:i:s') > $value->reg_end_at )
                  <p class="text-danger">রেজিস্ট্রেশানের মেয়াদ শেষ</p>
                  @else
                  
                  <p style="text-align: left">
                    <input type="checkbox" required id="agree"> I agree to the <a href="{{route('home.page', 'terms-condition')}}">Terms & Condition</a>, <a href="{{route('home.page', 'privacy-policy')}}">Privacy Policy</a> and  <a href="{{route('home.page', 'return-policy')}}">Fund Return Policy</a>.
                  </p>
                  <br>
                  <button type="button" onclick="checkLogin()" href="{{route('students.course.checkout', $batch->id)}}" class="btn btn-success btn-block btn-lg">কোর্সটি কিনুন</button>
                  @endif
                @endif
                <div class="clearfix"></div>
              </div>
              @if($value->what_is)
              <div class="card-body">
                <h3>এই কোর্সে যা থাকছে</h3>
                {!! $value->what_is !!}
              </div>
              @endif
            </div>
          </form>
        </div> <!--/.col-left-->
        
        <div class="col col-md-8 order-md-first">
          <div class="card card-info mb-5 p-3">
            <div class="card-heading">
              <h3>{{$value->name}}</h3>
              @if($value->reg_end_at && $value->reg_end_at > date('Y-m-d H:i:s'))
              <p style="text-align: center; color:red">
                রেজিস্ট্রেশনের মেয়াদ আছে:  <b><span id="timer"></span></b>
              </p>
              @endif
            </div>
            <div class="car-body" style="min-height: 200px">
              {!!$value->short!!}
            </div>
          </div>
          
          @if($value->routine)
          <div class="card card-default p-3">
            <div class="card-heading"><h4>কোর্স সিলেবাস</h4></div>
            <div class="card-body">{!! $value->routine !!}</div>
          </div>
          @endif

          @if($routine)
          <div class="card card-default mt-5 p-3">
            <div class="card-heading">
              <h4>এক্সাম/ক্লাস রুটিন</h4>
            </div>
            <div class="main-accordion">
              <p>{!! $routine->header !!}</p>
              <div class="car/d">
                <form action="#">
                  <div class="form-group">
                    <select class="form-control" name="department_id" id="department_id" onchange="getRoutine(this)">
                      <option value="">Select Department</option>
                      @foreach($departments as $department)
                      <option value="{{$department->id}}">{{$department->name}}</option>
                      @endforeach
                    </select>
                  </div>
                </form>
              </div>
              <br>

                <div class="table-responsive">
                  <table class="table table-bordered" id="routine">
                    
                  
                  </table>
                </div>
                
            </div>
          </div>
          @endif
          
          @php
          $course_syllabuses = [];
          if($value->syllabus)
          {
            $course_syllabuses = $value->syllabuses()->where('department_id', NULL)->where('course_id', $value->course->id)->get();
          }
          @endphp

          @if($value->departments || $course_syllabuses)
          <div class="card card-default mt-5 p-3">
            <div class="card-heading">
              <h4>বিস্তারিত এক্সাম/ক্লাস PDF</h4>
            </div>
            <div class="main-accordian">
              <div class="accordion">

                @if($course_syllabuses)
                <div class="accordion-item">
                  <div class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#batch_syllabus" aria-expanded="true" aria-controls="batch_syllabus">
                      ব্যাচ এর সিলেবাস সমূহ
                    </button>
                  </div>
                  <div id="batch_syllabus" class="accordion-collapse collapse show">
                    <div class="table-responsive">
                      <table class="table">
                        @foreach($course_syllabuses as $key => $syllabus)
                        <tr>
                          <th>{{$syllabus->name}}</th>
                          <td>
                            @if($student && $batch->students()->where('id', $student->id)->first())
                            <a class="btn btn-default" href="{{route('student.syllabus', $syllabus->id)}}"><i class="fa fa-eye"></i> View</a>
                              
                            <a href="{{route('students.syllabus.pdf', $syllabus->id)}}" class="btn btn-info"><i class="fa fa-download"></i> ডাউনলোড</a>
                            @else
                            Premium
                            @endif
                          </td>
                        </tr>
                        @endforeach
                      </table>
                    </div>
                  </div>
                </div>
                @endif

                @foreach($value->departments as $key => $department)
                <div class="accordion-item mt-3">
                  <div class="accordion">
                    <div class="accordion-header">
                      <button class="accordion-button" type="button"  data-bs-toggle="collapse" data-bs-target="#dept{{$key}}" aria-expanded="true" aria-controls="batch_syllabus">
                        {{$department->name}}
                      </button>
                    </div>
                    <div id="dept{{$key}}" class="accordion-collapse collapse show">
                      <div class="card-body">
                        @php
                        $syllabuses = $department->syllabus()->where('batch_id', $value->id)->get();
                        @endphp

                        @if($syllabuses)
                        <table class="table">
                          @foreach($syllabuses as $syllabus)
                          <tr>
                            <th>{{$syllabus->name}}</th>
                            <td>
                              @if(!empty($student) && $syllabus->batch->students()->where('student_id', $user->id)->first())
                                <a class="btn btn-default" href="{{route('student.syllabus', $syllabus->id)}}"><i class="fa fa-eye"></i> View</a>
                                
                                <a href="{{route('students.syllabus.pdf', $syllabus->id)}}" class="btn btn-info"><i class="fa fa-download"></i> ডাউনলোড</a>

                              @else
                              <button class="btn btn-warning" onclick="alert('This is premium version. Buy the course first.')">ডাউনলোড Full PDF</button>

                              @endif
                            </td>
                            <td>
                              @if($syllabus->sample_pdf)
                              <a href="{{$syllabus->sample_pdf}}" class="btn btn-info"><i class="fa fa-download"></i> স্যাম্পল PDF</a>
                              @endif
                            </td>
                          </tr>
                          @endforeach
                        </table>
                        @else
                        <p>এই ডিপার্টমেন্টের জন্যে এখনো কোন সিলেবাস প্রকাশিত হয় নি</p>
                        @endif
                      </div>
                    </div>
                  
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
          @endif
          
          <div class="card card-warning p-3 mt-5">
            <div class="card-heading">
              <h4>কোর্সটির মেয়াদ ও অন্যান্য</h4>
            </div>
            <div class="card-body table-responsive">
              <table class="table table-bordered">
                @if($value->end_at)
                <tr>
                  <td>কোর্সটি চলবে</td>
                  <th>{!! $source->reminder($value->end_at) !!}</th>
                </tr>
                @endif
                @if($value->reg_end_at && $value->reg_end_at > date('Y-m-d H:i:s'))
                <tr>
                  <td>রেজিস্ট্রেশনের মেয়াদ শেষ হবে</td>
                  <th>{{$source->dtformat($value->reg_end_at)}}</th>
                </tr>
                @endif
                @if( $value->offer_end_at && $value->offer_end_at > date('Y-m-d H:i:s') )
                <tr>
                  <td>বর্তমান অফারের মেয়াদ</td>
                  <th>{{$source->dtformat($value->offer_end_at)}}</th>
                </tr>
                @endif
              </table>
            </div>
          </div>
          
          @if($value->teachers)
          <div class="card card-default p-3 mt-5">
            <div class="card-heading">
              <h4>কোর্স ইন্সট্রাক্টর</h4>
            </div>
            <div class="card-body">
              <div class="row">
                  @foreach($value->teachers as $val)
                  <div class="col-md-6">
                    <div class="image">
                      <img src="{{$val->image? $val->image:'/img/teacher.png'}}" alt="" style="max-width: 80px; padding:5px; float:left; padding-right: 15px">
                    </div>
                    <div class="teacher-title">
                      <b style="font-size:16px">{{$val->name}}</b>
                      {!! $val->designation !!}
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  @endforeach
              </div>
            </div>
          </div>
          @endif
          @if($value->learn)
          <div class="card card-default p-3 mt-5">
            <div class="card-heading"><h4>কোর্সটি করে যা শিখবেন</h4></div>
            <div class="card-body">{!! $value->learn !!}</div>
          </div>
          @endif

          @if($value->details)
          <div class="card card-default p-3 mt-5">
            <div class="card-heading"><h4>কোর্স সম্পর্কে বিস্তারিত</h4></div>
            <div class="card-body">{!! $value->details !!}</div>
          </div>
          @endif
          @if($value->details)
          <div class="card card-default p-3 mt-5">
            <div class="card-heading"><h4>সাধারণ জিজ্ঞাসা সমূহ</h4></div>
            <div class="card-body">{!! $value->faq !!}</div>
          </div>
          @endif
          @if($value->refund)
          <div class="card card-danger p-3 mt-5 p-3">
            <div class="card-heading"><h4>রিফান্ড পলিসি</h4></div>
            <div class="card-body">{!! $value->refund !!}</div>
          </div>
          @endif
          <a href="{{route('students.complain')}}" class="btn btn-info mt-3">আপনার মতামত/অভিযোগ দিন</a>
        </div> <!-- /.column -->
      </div>
    </div>
  </section> <!-- /.content -->

<div class="modal fade" id="paymentConfirm" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{route('students.course.apply')}}" method="post">
        @csrf
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span></button>
        <h4 class="modal-title">{{$value->name}}</h4>
      </div>
      <div class="modal-body">
          <input type="hidden" name="student_id" value="{{$user ? $user->id : null}}">
          <input type="hidden" name="batch_id" value="{{$value->id}}">
          
          <input type="hidden" name="name" value="{{$student? $student->name:''}}" />
          <input type="hidden" name="email" value="{{$student? $student->email:''}}" />
          <input type="hidden" name="phone" value="{{$student? $student->contact:''}}" />
          <input type="hidden" name="address1" value="" />
          <input type="hidden" name="total" value="{{$value->net_price}}" />

            <div class="penel-heading no-padding" style="text-align: center;padding:15px">
              <img class="course-image" src="{{ $value->banner? $value->banner : '/img/course.jpg'}}" alt="" />
            </div>
              <h3>
                @if($value->discount) 
                <del> &#2547;{{$source->point0($value->price)}}</del> &nbsp; <span class="label label-warning"> &#2547;{{$source->point0($value->discount)}} ছাড়</span>
                @endif
                <b> &nbsp; &#2547;{{$source->point0($value->net_price)}}</b>
                </h3>
                @if($value->offer_end_at)
                <p style="color: red">ডিস্কাউন্ট অফারের মেয়াদ <b>{{$source->dtformat($value->offer_end_at)}}</b></p>
                @endif
              <p>{{$value->subtitle}}</p>
              <br>
            @if(!empty($user->id) && !$value->students()->where('id', $user->id)->first())
            <div class="table-responsive">
              <table class="table">
                <tr>
                  <td>ডিপার্টমেন্ট নির্বাচন করুন </td>
                  <th>
                    @if(!empty($user->id) && !$value->students()->where('id', $user->id)->first())
                      @if($departments)
                        <select id="department_id" name="department_id" class="form-control" required>
                          <option value="">Select One</option>
                          @foreach($departments as $val)
                          <option value="{{$val->id}}">{{$val->name}}</option>
                          @endforeach
                        </select>
                      @else
                      No Departments for The course
                      @endif
                    @endif</th>
                </tr>
                <tr>
                  <td>কোর্সটির মূল্য </td>
                  <th>&#2547; {{$source->point0($value->net_price)}}</th>
                </tr>
                <tr>
                  <td>অন্যান্য ফী </td>
                  <th>&#2547; 0</th>
                </tr>
                <tr style="font-size:18px">
                  <th>মোট =</th>
                  <th>&#2547; {{$source->point0($value->net_price)}}</th>
                </tr>
                <tr>
                  <th>
                  </th>
                </tr>
              </table>
            @endif
          </div>
        <div class="modal-footer">

          {{-- <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button> --}}
          
          <button class="btn btn-primary btn-block btn-lg" onsubmit="return confirm('Double check you provided information.')">পেমেন্ট এর জন্য এগিয়ে যান</button>
          <div class="clearfix"></div>
        </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>

@endsection
@section('scripts')
<script>
  //eheck login
  function checkLogin()
  {
    // let login = '{{$user ? $user->id : ''}}';
    let agree = document.getElementById('agree');

    if(agree.checked == false)
    {
      alert('Please confirm as per our agreement.');
      return;
    }
    else
    {
      window.location.href = '{{route("students.course.checkout", "$value->id")}}';
    }
  }
  // Set the date we're counting down to
  // var countDownDate = new Date("Oct 27, 2024 12:47:25").getTime();
  var countDownDate = new Date("{{$source->dtcformat($value->reg_end_at)}}").getTime();
  // var countDownDate = new Date().getTime();
  
  let timer = document.getElementById("timer");
  
  if(timer && countDownDate)
  {
    // Update the count down every 1 second
    var x = setInterval(function()
    {
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
      }
    
      
    }, 1000);
  }

  function dateTime(date) {
    const d = new Date(date);

    const day = String(d.getDate()).padStart(2, '0');
    const month = d.toLocaleString('en-US', { month: 'short' }); // "Jun"
    const year = d.getFullYear();

    let hours = d.getHours();
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';

    hours = hours % 12;
    hours = hours ? hours : 12; // 0 → 12
    const hourFormatted = String(hours).padStart(2, '0');

    return `${day} ${month} ${year}, ${hourFormatted}:${minutes} ${ampm}`;
  }


  function dateCheck(date)
  {
    let currentDate = new Date();
    let targetDate = new Date(date.replace(' ', 'T'));

    // Compare timestamps (in milliseconds)
    if (currentDate.getTime() >= targetDate.getTime()) {
      return 'Next';
    } else if(currentDate.getTime() < targetDate.getTime()) {
      return 'Pending';
    } else {
      return 'Completed';
    }

  }

  // routine filter
  function getRoutine(e)
  {
    let routine = document.getElementById('routine');
    let departmentId = e.options[e.options.selectedIndex].value;
    let tableData = '', subjects = '', departments = '', routineRows = '', subjectRow = '', departmentRow = '';

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.ajax({
      type: 'GET',
      url: '{{route("home.routine.filter", "")}}/' + '{{$routine->id}}' + '/' + departmentId,
      success: function (data) {

        var obj = JSON.parse(JSON.stringify(data));

        console.log(obj);
        
        obj.subjects.forEach(subject => {
          subjects += '<th>'+subject.name+'</th>';
        });

        obj.departments.forEach(department => {
          departments += '<th>'+department.name+'</th>';
        });


        obj.routine_rows.forEach((routineRow, n) => {

          obj.routine_rows[n].subjects.forEach(subjectData => {
            subjectRow += '<td>'+subjectData.chapter+'</td>';
          });

          obj.routine_rows[n].departments.forEach(departmentData => {
            departmentRow += '<td>'+departmentData.details+'</td>';
          });

          routineRows +=''+
          '<tr>'+
            '<td>'+ routineRow.paper_name +'</td>'+
        
            subjectRow +
        
            departmentRow +
        
            '<td>'+ dateTime(obj.routine_rows[n].exam_at) +'</td>'+
            '<td>'+
              dateCheck(obj.routine_rows[n].exam_at)+
            '</td>'+
          '</tr>';

          subjectRow = '', departmentRow = '';
        });

        //item->exam_at >= date("Y-m-d") ? "Next" || item->exam_at < date("Y-m-d") ? "Completed" : "Pending"

        // finally make the table
        tableData += ''+
        '<tr>'+
          '<th>Exam No</th>'+
      
          subjects +
      
          departments +

          '<th>Date & Time</th>'+
          '<th>Status</th>'+
        '</tr>'+

        routineRows;

        routine.innerHTML = tableData;
      },
      error: function(data) { 
        console.log('data error');
      }
    });
  }

  getRoutine(document.getElementById('department_id'));
</script>
@endsection