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
<meta name="description" content="{{$batch->short}}"/>
<meta name="keywords" content="{{$batch->name}}"/>
<meta name="robots" content="index, follow, nocache"/>
<meta name="googlebot" content="index, follow, max-video-preview:-1, max-image-preview:large, max-snippet:-1"/>
<meta name="category" content="education"/>

<meta property="fb:app_id" content="1360109555168118" />
<meta property="og:type" content="video.other">
<meta property="og:url" content="{{route('home.course.show', $batch->id)}}" />
<meta property="og:title" content="{{$batch->name}}" />
<meta property="og:description" content="{{substr($batch->short, 3, -4)}}" />
<meta property="og:logo" content="{{route('homepage').$batch->banner}}" />
{{-- <meta property="og:image" content="{{route('homepage').$batch->banner}}" /> --}}
<meta property="og:image" content="https://img.youtube.com/vi/{{$value->video}}/maxresdefault.jpg">
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="{{$batch->name}}"/>
<meta property="og:site_name" content="Live Education BD" />

<meta property="og:video" content="https://www.youtube.com/embed/{{$value->video}}">
{{-- <meta property="og:video" content="https://www.youtube.com/watch?v={{$value->video}}"> --}}
<meta property="og:video:secure_url" content="https://www.youtube.com/embed/{{$value->video}}">
<meta property="og:video:type" content="text/html">
<meta property="og:video:width" content="1280">
<meta property="og:video:height" content="720">

<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:site" content="@liveeducationbd"/>
<meta name="twitter:creator" content="@liveeducationbd"/>
<meta name="twitter:title" content="{{$batch->name}}"/>
<meta name="twitter:description" content="{{$batch->short}}"/>
<meta name="twitter:image" content="{{route('homepage').$batch->banner}}"/>
<meta name="twitter:image:width" content="1200"/>
<meta name="twitter:image:height" content="630"/>
<meta name="twitter:image:alt" content="{{$batch->name}}"/>

<title>{{$batch->name}}</title>

@endsection
@section('content')
{{-- <script src='https://www.google.com/recaptcha/api.js' async defer></script> --}}
<style>
  .checkbox{padding-left: 25px}
  .panel ::-webkit-scrollbar{width: 5px;}
  ::-webkit-scrollbar-thumb{background-color: #ddd}
  .course-image{width:100%}
  .teacher-title p{margin:0}
  .box-title {display: block;width:100%}
  @media(min-width: 769px){
    .pricing{float: right;}
    .info{}
  }
  @media(max-width: 768px){
    .pricing{}
    .info{}
  }
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

{{-- <div class="content-wrapper">
  <div class="container no-padding"> --}}
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1> Course {{-- <small>Courseple 2.0</small> --}} </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Course</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-4 pricing">
          <!-- Apply to the course -->
          <form action="{{route('students.course.apply')}}" method="post">
            @csrf
            <input type="hidden" name="batch_id" value="{{$value->id}}">
            <div class="panel panel-default">
              <div class="penel-heading no-padding" style="text-align: center;">
                @if($value->video)
                <div class="videoWrapper">
                  <iframe class="responsive-iframe" style="width: 100%; height:100%; border:5px solid #fff" src="https://www.youtube.com/embed/{{$value->video}}?autoplay=1&mute=1" allowfullscreen ></iframe>
                </div>
                @else
                <img class="course-image" src="{{ $value->banner? $value->banner : '/img/course.png'}}" alt="" />
                @endif
              </div>
              <div class="panel-heading">
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
              <div class="panel-footer">
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
              <div class="panel-body">
                <h3>এই কোর্সে যা থাকছে</h3>
                {!! $value->what_is !!}
              </div>
              @endif
            </div>
          </form>
          <div class="clearfix"></div>
        </div> <!--/.col-left-->
        
        <div class="col-md-8 info">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3>{{$value->name}} <br><small>Course: <b>{{$value->course ? $value->course->name:''}}</b></small></h3>
              @if($value->reg_end_at && $value->reg_end_at > date('Y-m-d H:i:s'))
              <p style="text-align: center; color:red">রেজিস্ট্রেশনের মেয়াদ আছে: <b><span id="timer"></span></b>
              </p>
              @endif
            </div>
            <div class="panel-body" style="min-height: 200px">
              {!!$value->short!!}
            </div>
          </div>
          
          @if($value->routine)
          <div class="panel panel-default">
            <div class="panel-heading"><h4>কোর্স সিলেবাস</h4></div>
            <div class="panel-body">{!! $value->routine !!}</div>
          </div>
          @endif

          @php
          $course_routines = [];
          if($value->syllabus)
          {
            $course_routines = $value->routines()->where('department_id', NULL)->where('course_id', $value->course->id)->get();
          }
          @endphp

          @if($value->departments || $course_routines)
          <div class="panel panel-default">
            <div class="panel-heading"><h4>এক্সাম/ক্লাস রুটিন</h4></div>
            <div class="box-group" id="accordion">

              @if($course_routines)
              <div class="panel box box-warning">
                <div class="box-header with-border">
                  <div class="box-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#batch_routine">
                      ব্যাচ এর রুটিন
                      <span class="pull-right-container">
                        <i class="fa fa-chevron-down pull-right"></i>
                      </span>
                    </a>
                  </div>
                </div>

                @php
                  $batch_routine = $value->routines()->where('department_id', null)->first();
                @endphp
                
                <table class="table">

                @if($batch_routine)
                  <tr>
                    <th>{{$batch_routine->name}}</th>
                    <td> 
                      @if($batch_routine->pdf)
                      <a class="btn btn-warning btn-sm" target="_blank" href="{{$batch_routine->pdf}}"><i class="fa fa-download"></i> ডাউনলোড</a>
                      @endif
                    </td>
                  </tr>
                @endif
                </table>
              </div>
              @endif

              @foreach($value->departments as $key => $department)
              <div class="panel box box-primary">
                <div class="box-header with-border">
                  <div class="box-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#dept_routine{{$key}}">
                      {{$department->name}}
                      <span class="pull-right-container">
                        <i class="fa fa-chevron-down pull-right"></i>
                      </span>
                    </a>
                  </div>
                  
                  @php
                  $routines = $department->routine()->where('course_id', $value->course->id)->where('batch_id', $batch->id)->orderBy('id', 'DESC')->get();
                  @endphp
                  @if($routines)
                  <table class="table">
                    @foreach($routines as $routine)
                    <tr>
                      <th>{{$routine->name}} </th>
                      <td> 
                        @if($routine->pdf)
                        <a class="btn btn-warning btn-sm" target="_blank" href="{{$routine->pdf}}"><i class="fa fa-download"></i> ডাউনলোড</a>
                        @endif
                      </td>
                    </tr>
                    @endforeach
                  </table>
                  @endif
                
              </div>
              </div>
              @endforeach
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
          <div class="panel panel-default">
            <div class="panel-heading"><h4>বিস্তারিত এক্সাম/ক্লাস PDF</h4></div>
            <div class="box-group" id="accordion">

              @if($course_syllabuses)
              <div class="panel box box-warning">
                <div class="box-header with-border">
                  <div class="box-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#batch_syllabus">
                      ব্যাচ এর সিলেবাস সমূহ
                      <span class="pull-right-container">
                        <i class="fa fa-chevron-down pull-right"></i>
                      </span>
                    </a>
                  </div>
                </div>
                <div id="batch_syllabus" class="panel-collapse collapse in">
                  <div class="box/-body">
                    <table class="table">
                      @foreach($course_syllabuses as $key => $syllabus)
                      <tr>
                        <th>{{$syllabus->name}}</th>
                        <td>
                          <a class="btn btn-default" href="{{route('student.syllabus', $syllabus->id)}}"><i class="fa fa-eye"></i> View</a>
                            
                          <a href="{{route('students.syllabus.pdf', $syllabus->id)}}" class="btn btn-info"><i class="fa fa-download"></i> ডাউনলোড</a>
                        </td>
                      </tr>
                      @endforeach
                    </table>
                  </div>
                </div>
              </div>
              @endif

              @foreach($value->departments as $key => $department)
              <div class="panel box box-primary">
                <div class="box-header with-border">
                  <div class="box-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#dept{{$key}}">
                      {{$department->name}}
                      <span class="pull-right-container">
                        <i class="fa fa-chevron-down pull-right"></i>
                      </span>
                    </a>
                  </div>
                <div id="dept{{$key}}" class="panel-collapse collapse in">
                  <div class="bo/x-body">
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
          @endif
          
          <div class="panel panel-warning">
            <div class="panel-heading">
              <h4>কোর্সটির মেয়াদ ও অন্যান্য</h4>
            </div>
            <div class="panel-body table-responsive">
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
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4>কোর্স ইন্সট্রাক্টর</h4>
            </div>
            <div class="panel-body">
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
          <div class="panel panel-default">
            <div class="panel-heading"><h4>কোর্সটি করে যা শিখবেন</h4></div>
            <div class="panel-body">{!! $value->learn !!}</div>
          </div>
          @endif

          @if($value->details)
          <div class="panel panel-default">
            <div class="panel-heading"><h4>কোর্স সম্পর্কে বিস্তারিত</h4></div>
            <div class="panel-body">{!! $value->details !!}</div>
          </div>
          @endif
          @if($value->details)
          <div class="panel panel-default">
            <div class="panel-heading"><h4>সাধারণ জিজ্ঞাসা সমূহ</h4></div>
            <div class="panel-body">{!! $value->faq !!}</div>
          </div>
          @endif
          @if($value->refund)
          <div class="panel panel-danger">
            <div class="panel-heading"><h4>রিফান্ড পলিসি</h4></div>
            <div class="panel-body">{!! $value->refund !!}</div>
          </div>
          @endif
          <a href="{{route('students.complain')}}" class="btn btn-info">আপনার মতামত/অভিযোগ দিন</a>
        </div><!-- column -->
      </div>
    </section> <!-- /.content -->
  {{-- </div> <!-- /.container -->
</div> --}}

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
                      @if($departments->count())
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
</script>
@endsection