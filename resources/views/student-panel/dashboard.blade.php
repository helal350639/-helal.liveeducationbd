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


  <section class="dashboard p-2 py-5" style="background: #eee">
    <div class="container">
      <div class="row">
         <div class="col-md-12">
            <div class="section-title text-center mb-4">
               <h2> ড্যাশবোর্ড </h2>
               <hr class="w-25 mx-auto bg-danger" />
               {{-- <p style="font-size: 24px; font-weight: 700; color: #89181A;">- BB188165</p> --}}
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
                        <h3>2</h3>
                     </div>
                     <div class="corce_exam-icon">
                        <i class="fa fa-wallet"></i>
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
                        <h3>5</h3>
                     </div>
                     <div class="corce_exam-icon">
                        <i class="fa fa-wallet"></i>
                     </div>
                  </div>
               </a>
            </div>
         </div>
         <div class="col-6 col-lg-3">
            <div class="card card-body shadow-sm">
               <a href="{{route('category.batches', 'Premium Exams')}}">
                  <div class="corce_exam_mobile_res">
                     <div>
                        <span>প্রিমিয়াম পরীক্ষা</span>
                        <h3>3</h3>
                     </div>
                     <div class="corce_exam-icon">
                        <i class="fa fa-wallet"></i>
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
                        <span>রেজাল্ট</span>
                        <h3>2</h3>
                     </div>
                     <div class="corce_exam-icon">
                        <i class="fa fa-wallet"></i>
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
                        <span>এক্সাম/ ক্লাস রুটিন</span>
                        <h3>3</h3>
                     </div>
                     <div class="corce_exam-icon">
                        <i class="fa fa-wallet"></i>
                     </div>
                  </div>
               </a>
            </div>
         </div>
         <div class="col-6 col-lg-3">
            <div class="card card-body shadow-sm">
               <a href="{{route('category.batches', 'Exam PDF')}}">
                  <div class="corce_exam_mobile_res">
                     <div>
                        <span>এক্সাম PDF</span>
                        <h3>6</h3>
                     </div>
                     <div class="corce_exam-icon">
                        <i class="fa fa-wallet"></i>
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
                        <h3>2</h3>
                     </div>
                     <div class="corce_exam-icon">
                        <i class="fa fa-wallet"></i>
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
                        <span>নিয়োগ বিজ্ঞপ্তি</span>
                        <h3>5</h3>
                     </div>
                     <div class="corce_exam-icon">
                        <i class="fa fa-wallet"></i>
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