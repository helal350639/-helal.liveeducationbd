@php
use \App\Http\Controllers\SourceCtrl;
$source = new SourceCtrl;
$user = Auth::guard('student')->user();
@endphp

@extends('student')
@section('title', 'Dashboard')
@section('content')

<style>

  .img-16 {
      height: 16px;
      width: 16px;
  }

  .box-shadow {
      box-shadow: 1px 1px 10px 0px rgba(0, 0, 0, 0.75);
      -webkit-box-shadow: 1px 1px 10px 0px rgba(0, 0, 0, 0.75);
      -moz-box-shadow: 1px 1px 10px 0px rgba(0, 0, 0, 0.75);
  }
  .student-panel-menu {
      background-color: #ffffff;
      border: 1px solid #EBEBEB;
      border-radius: 5px;
      padding: 10px;
  }
  .student-panel-menu li a {
      color: #1d1d1d;
      font-size: 16px;
      font-weight: 500;
      border-radius: 5px;
  }

  .student-panel-menu li:hover a {
      background-color: #B0D5D9;
      color: #4f4f4f  !important;
  }

  .st-menu-active {
      background-color: #033B3F;
      color: #ffffff !important;
  }

  .content-shadow {
      box-shadow: 0px 0px 25px #D6D6D6;
  }

  /* width */
  ::-webkit-scrollbar {
      width: 2px;
  }

  /* Track */
  ::-webkit-scrollbar-track {
      background: #f1f1f1;
  }

  /* Handle */
  ::-webkit-scrollbar-thumb {
      background: #aaa;
  }

  /* Handle on hover */
  ::-webkit-scrollbar-thumb:hover {
      background: black;
  }

    /* ------------------------------------ */
  .go-messenger {
      position: fixed;
      top: 50%;
      left: 3%;
      opacity: 1;
      cursor: pointer;
      text-decoration: none;
      color: var(--whiteColor);
      font-size: 40px;
      font-weight: 700;
      text-align: center;
      background: #281367;
      border-radius: 50px;
      width: 60px;
      height: 60px;
      line-height: 43px;
      z-index: 100;
      -webkit-transition: .5s;
      transition: .5s;
      padding-top: 6px;
  }

  .go-messenger i {
      -webkit-transition: .5s;
      transition: .5s;
      vertical-align: middle
  }

  .go-messenger:hover {
      background-color: var(--titleColor)
  }

  .go-messenger.active {
      top: 95%;
      -webkit-transform: translateY(-95%);
      transform: translateY(-95%);
      opacity: 1;
      visibility: visible
  }

  /* ------------------------------------------- */
</style>
<style>
  @media (min-width: 1024px) and (max-width: 1440px) {
    .row {
      /* --bs-gutter-x: -0.1rem !important; */
    }
  }
  
:root {
--progress-bar-width: 80px;
--progress-bar-height: 80px;
--font-size: 2rem;
}
.circular-progress {
width: var(--progress-bar-width);
height: var(--progress-bar-height);
border-radius: 50%;
display: flex;
justify-content: center;
align-items: center;
}
.inner-circle {
position: absolute;
width: calc(var(--progress-bar-width) - 25px);
height: calc(var(--progress-bar-height) - 25px);
border-radius: 50%;
background-color: lightgrey;
}

.percentage {
position: relative;
font-size: 20px;
color: rgb(0, 0, 0, 0.8);
margin-top: 1rem;
}
@media screen and (max-width: 800px) {
:root {
--progress-bar-width: 80px;
--progress-bar-height: 80px;
--font-size: 1.3rem;
}
}

@media screen and (max-width: 500px) {
:root {
--progress-bar-width: 60px;
--progress-bar-height: 60px;
--font-size: 1rem;
}
.percentage {
font-size: 16px;
}
}
</style>
<style>
  .blink-text {
      animation: blinker 1.5s linear infinite;
      color: red; /* Example color */
      font-family: sans-serif; /* Example font */
  }
  
  @keyframes blinker {
      50% {
          opacity: 0;
      }
  }
  .btn-exam{
    background:#fff;
    padding:25px;
    box-shadow: 2px 5px 3px #ddd;
  }
  </style>

  <section class="dashboard p-2 py-5" style="background: #eee">
    <div class="container">
      <div class="row">
         <div class="col-md-12">
            <div class="section-title text-center mb-4">
               <h2> স্টুডেন্ট ড্যাশবোর্ড </h2>
               <p style="font-size: 24px;"> Registration ID: <span style=" font-weight: 700; color: #080;">{{$source->numset($user->id, 6)}}</span></p>
               <p style="color: white;">
                <a class="blink-text btn btn-info" href="{{route('students.my-course')}}" >Your Reference Link: Click Now</a>
              </p>

            <div class="all-courses-area">
              <div class="row justify-content-center g-3">

              <div class="col-lg-8 col-md-10 col-sm-12" title="{{$source->dtformat($paper->close)}}">
                <div class="border accordion-body-content d-flex justify-content-between align-items-center flex-wrap p-3 py-2" style="background: #fff">

                  <div class="text d-flex align-items-center list-content">
                    <div class="course_exam-icon">
                      <div class="circular-progress" title="Provided Answer" data-inner-circle-color="#def3ff" data-percentage="6" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)" style="background: #fff; font-size:25px; border:1px solid #ddd; color:#1CAB55">
                        @if($paper->open <= now() && $paper->close > now())
                        <i class="fa fa-unlock"></i>
                        @else
                        <i class="fa fa-lock" style="color:red"></i>
                        @endif
                      </div>
                    </div>
                    <p class="exam-title ps-1">
                      <span>{{ $paper->name }}</span>
                      <span></span>
                      <br>
                      <span style="font-size: 12px; font-weight: 700; color: #1CAB55;"> (প্রশ্ন -{{$paper->questions()->count()}}টি, সময়-{{$paper->time}}মিনিট) </span>
                    </p>
                  </div>
                  <div class="v-button d-flex flex-wrap gap-2">
                    <a href="{{route('students.instruction', $paper->id)}}">
                      Today Your Exam
                    </a>
                  </div>
                </div>
              </div>
              </div>

            </div>
         </div>
      </div>
      <div class="row g-4">
         <div class="col-6 col-lg-3">
            <div class="card card-body shadow-sm">
               <a href="{{route('students.my-course')}}">
                  <div class="corce_exam_mobile_res">
                     <div>
                        <span>কোর্স সমূহ</span>
                        <h3>{{$data['course']}}</h3>
                     </div>
                  </div>
               </a>
            </div>
         </div>
         <div class="col-6 col-lg-3">
            <div class="card card-body shadow-sm">
               <a href="{{route('category.batches', 'Free Exams')}}">
                  <div class="corce_exam_mobile_res">
                     <div>
                        <span>ফ্রি পরীক্ষা</span>
                        <h3>{{$data['free_exam']}}</h3>
                     </div>
                  </div>
               </a>
            </div>
         </div>
         <div class="col-6 col-lg-3">
            <div class="card card-body shadow-sm">
               <a href="{{route('category.batches', ['Premium Exams', 'Dashboard'])}}">
                  <div class="corce_exam_mobile_res">
                     <div>
                        <span>প্রিমিয়াম পরীক্ষা</span>
                        <h3>{{$data['premium_exam']}}</h3>
                     </div>
                  </div>
               </a>
            </div>
         </div>
         <div class="col-6 col-lg-3">
            <div class="card card-body shadow-sm">
               <a href="{{route('category.result.option')}}">
                  <div class="corce_exam_mobile_res">
                     <div>
                        <span>রেজাল্ট/সলুশন</span>
                        <h3>{{$data['result']}}</h3>
                     </div>
                  </div>
               </a>
            </div>
         </div>
         <div class="col-6 col-lg-3">
            <div class="card card-body shadow-sm">
               <a href="{{route('category.batches', ['Exam Routine', 'Dashboard'])}}">
                  <div class="corce_exam_mobile_res">
                     <div>
                        <span>এক্সাম/ ক্লাস রুটিন</span>
                        <h3>{{$data['routine']}}</h3>
                     </div>
                  </div>
               </a>
            </div>
         </div>
         <div class="col-6 col-lg-3">
            <div class="card card-body shadow-sm">
               <a href="{{route('category.batches', ['Exam PDF', 'Dashboard'])}}">
                  <div class="corce_exam_mobile_res">
                     <div>
                        <span>এক্সাম PDF</span>
                        <h3>{{$data['exam_pdf']}}</h3>
                     </div>
                  </div>
               </a>
            </div>
         </div>
         <div class="col-6 col-lg-3">
            <div class="card card-body shadow-sm">
               <a href="#">
                  <div class="corce_exam_mobile_res">
                     <div>
                        <span>ই-বুক সমূহ</span>
                        <h3>{{$data['e-book']}}</h3>
                     </div>
                  </div>
               </a>
            </div>
         </div>
         <div class="col-6 col-lg-3">
            <div class="card card-body shadow-sm">
               <a href="{{route('homepage')}}#circular">
                  <div class="corce_exam_mobile_res">
                     <div>
                        <span>নিয়োগ বিজ্ঞপ্তি</span>
                        <h3>{{$data['circular']}}</h3>
                     </div>
                  </div>
               </a>
            </div>
         </div>
         <div class="col-6 col-lg-4">
            <div class="card card-body shadow-sm">
               <div class="corce_exam_mobile_res">
                  <div>
                     <span>আপনার কোর্স প্রোগ্রেস &nbsp; 0 OF 0 </span>
                  </div>
                  <div class="course_exam-icon">
                     <div class="circular-progress" data-inner-circle-color="#def3ff" data-percentage="10" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)">
                        <div class="inner-circle"></div>
                        <p class="percentage">0%</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-6 col-lg-4">
            <div class="card card-body shadow-sm">
               <div class="corce_exam_mobile_res">
                  <div>
                     <span>আপনার এক্সাম প্রোগ্রেস &nbsp; 0 OF 0 </span>
                  </div>
                  <div class="course_exam-icon">
                     <div class="circular-progress" data-inner-circle-color="#def3ff" data-percentage="0" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)">
                        <div class="inner-circle"></div>
                        <p class="percentage">0%</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-6 col-lg-4">
            <div class="card card-body shadow-sm">
               <div class="corce_exam_mobile_res">
                  <div>
                     <span>আপনার প্রোগ্রেস &nbsp; 0 OF 0 </span>
                  </div>
                  <div class="course_exam-icon">
                     <div class="circular-progress"
                        data-inner-circle-color="#def3ff"
                        data-percentage="0"
                        data-progress-color="rgb(137 24 26)"
                        data-bg-color="rgb(137 24 26 / 70%)">
                        <div class="inner-circle"></div>
                        <p class="percentage">0%</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="mt-3 row">
         <div class="col-md-12">
            <div class="card  course_exam_mobile_res">
               <div class="card-header">
                  <h3 class="text-center f-s-32  pt-2 pd-0">Your Reference Earnings</h3>
               </div>
               <div class="card-body overflow-scroll">
                  <table class="table table-striped f-s-22">
                     <thead>
                        <tr>
                           <th>Student Name</th>
                           <th>Batch</th>
                           <th>Bonus(Tk.)</th>
                        </tr>
                     </thead>
                     <tbody>
                      @php
                      $total_reference_bonus = 0;
                      @endphp

                      @foreach($references as $value)
                      <tr>
                        <td>{{$value->student ? $value->student->name : ''}}</td>
                        <td>{{$value->batch ? $value->batch->name : ''}}</td>
                        <td>{{$value->referrer_bonus}}</td>
                      </tr>
                      @php
                      $total_reference_bonus += $value->referrer_bonus;
                      @endphp
                      @endforeach
                      <tr>
                        <th colspan="2"></th>
                        <th>{{$total_reference_bonus}} Tk.</th>
                      </tr>

                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <div class="mt-3 row">
         <div class="col-md-12">
            <div class="card  course_exam_mobile_res">
               <div class="card-header">
                  <h3 class="text-center f-s-32  pt-2 pd-0">Your Order & Payments</h3>
               </div>
               <div class="card-body overflow-scroll">
                  <table class="table table-striped f-s-22">
                     <thead>
                        <tr>
                           <th>Name</th>
                           <th>Type</th>
                           <th>Price</th>
                           <th>Paid Amount</th>
                           <th>Due</th>
                           <th>Status</th>
                        </tr>
                     </thead>
                     <tbody>

                      @foreach($orders as $order)
                      <tr>
                        <td>{{$order->name}}</td>
                        <td>{{$order->id}}</td>
                        <td>{{$order->amount}}</td>
                        <td>{{$order->status == 'Processing' ? $order->amount : 0}}</td>
                        <td>{{$order->status == 'Pending' ? $order->amount : 0}}</td>
                        <td>{{$order->status}}</td>
                      </tr>
                      @endforeach

                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
    </div>
  </section>
@endsection