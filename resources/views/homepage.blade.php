<?php

use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;

$user = [];
if(Auth::guard('student')->user())
{
  $user = Auth::guard('student')->user();
}

function active($name)
{
  $uri = explode('/', $_SERVER['REQUEST_URI']);
  if(isset($uri[2]) && $uri[2] == $name || $_SERVER['REQUEST_URI'] == $name)
  {
    return 'active';
  }
}
?>

<!DOCTYPE html>
<html lang="en">
  <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
    <title>LiveEuducationBD</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="icon" href="/img/logo.png" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="index" />
    <meta property="description" content="" />
    <meta property="keywords" content="" />
    @yield('stylesheets')
    <!-- Preconnect & Preload Resources -->
    <link href="{{'/web/assets/fonts.googleapis.com/css24d2f.css?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet'}}">
    <link href="{{'/web/assets/fonts.googleapis.com/css26dcd.css?family=Noto+Sans+Bengali:wght@100..900&amp;display=swap" rel="stylesheet'}}" />

    <!-- External CSS Libraries -->
    <link rel="stylesheet" href="{{'/web/assets/ajax/libs/font-awesome/6.6.0/css/all.min.css'}}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{'/web/assets/npm/sweetalert2%4011.7.3/dist/sweetalert2.min.css'}}"  />
    <link rel="stylesheet" href="{{'/web/assets-old/css/owl.theme.default.min.css'}}" />
    <link rel="stylesheet" href="{{'/web/assets/css/bootstrap.min.css'}}" />
    <link rel="stylesheet" href="{{'/web/assets/vendors/slick/slick.css'}}" />
    <link rel="stylesheet" href="{{'/web/assets/vendors/slick/slick-theme.css'}}"/>
    <link rel="stylesheet" href="{{'/web/assets-old/css/owl.carousel.min.css'}}" />
    <!-- Custom CSS (With Cache Busting) -->
    <link rel="stylesheet" href="{{'/web/assets/css/style2b24.css?v=3.8.25'}}" />
    <link rel="stylesheet" href="{{'/web/assets/css/responsive2b24.css?v=3523'}}" />
    <link rel="stylesheet" href="{{'/web/css/style.css?v=1.3.3'}}" />
    <style>
      .user-image{
        max-width: 40px;
        border-radius: 25px
      }
      .mobile-user-image{
        max-width: 30px;
        border-radius: 50%
      }
      .img-circle{
        border-radius: 50%;
        max-width: 100px;
        margin:auto
      }
      .user-header{
        text-align: center
      }
      @media(min-width:769px){
        .logo-img{
          max-width: 60px!important;
        }
      }
      @media(max-width:768px){
        .logo-img{
          max-width: 40px!important;
        }
        #Home_add{margin-top: 15px}
      }
    </style>
</head>
<body>
  <header id="Header">
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container">
        <div class="mobile-menu d-flex justify-content-between align-items-center">
          <div class="main-logo">
            <a href="{{route('homepage')}}">
              <img src="/web/img/logo.png" alt="Logo" class="logo-img">
            </a>
          </div>
          <div class="moblie-icon">

            <div class="dropdown">
              <a href="javascript:void(0)" class="dropdown-/toggle text-warning" id="Bell" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: black;">
                <i class="fa fa-bell"></i>
              </a>
              <ul class="dropdown-menu" aria-labelledby="Bell">
                <li class="nav-item">
                  <a class="nav-link" href="javascript:void(0)">...</a>
                </li>
              </ul>
            </div>

            @if(!empty($user))
              <div class="dropdown user user-menu">
                <div class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <!-- The user image in the navbar-->
                  <img src="{{$user->image ? : '/img/avatar.png' }}" class="mobile-user-image" alt="">
                </div>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow dropdown-center">
                  <!-- The user image in the menu -->
                  <div class="user-header">
                    <img src="{{$user->image ? :'/img/avatar.png' }} " class="img-circle md-3" alt="Photo"/>

                    <h4 style="color: #000; font-size:16px" class="mt-3">
                      <b>{{$user->name}}</b>
                      </br>
                      <small>Reg. ID: {{$source->numset($user->id, 6)}}<br>
                      {{$user->email}}</br>
                      {{$user->contact}}</small>
                      </h4>
                    <div class="clearfix"></div>
                  </div>

                  <li><a class="dropdown-item" href="{{route('students.dashboard')}}">ড্যাশবোর্ড</a></li>
                  <li>
                    <a class="dropdown-item" href="{{route('students.my-syllabus')}}">সিলেবাস সমূহ</a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{route('students.my-course')}}">আমার কোর্স সমূহ</a>
                  </li>
                  <li>
                    <a href="{{route('students.profile')}}" class="dropdown-item ">প্রোফাইল</a>
                  </li>
                  <li>
                    <form action="{{route('students.logout')}}" method="post" class="btn">
                      @csrf
                      <button type="submit" class="dropdown-item btn btn-warning btn-fluid" onclick="return confirm('Are you sure you want to logout?')">লগআউট</button>
                    </form>
                  </li>
                </div>
              </div>
            @else
              <div class="nav-item login-button">
                <a href="{{route('students.login')}}" type="button" class="btn btn-success btn-sm">Login</a>
              </div>
            @endif

            <div>
              <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" onclick="extMenuShowHide(this)">
                <span class="navbar-toggler-icon"></span>
              </button>
            </div>
          </div>
        </div>


        <nav class="navbar-2 navbar-expand navbar-light" id="extra-mobile-nav">
          <div class="container">
            <div class="navbar-collapse">
              <ul class="navbar-nav justify-content-center w-100">
                <li class="nav-item">
                  <a class="nav-link" href="{{route('students.dashboard')}}"  style="color: #1CAB55; background:#eee; border-radius:10px">ড্যাশবোর্ড</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/#our_courses" style="color: #1CAB55;">
                    কোর্স
                  </a>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="/#exam-section" style="color: #1CAB55;">পরীক্ষা </a>
                  <ul class="dropdown-menu" aria-labelledby="moreDropdown">
                    <li class="nav-item">
                      <a class="nav-link" href="{{route('category.batches', 'Free Exams')}}">ফ্রি পরীক্ষা সমূহ</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="{{route('category.batches', 'Premium Exams')}}">প্রিমিয়াম পরীক্ষা সমূহ</a>
                    </li>
                  </ul>
                </li>
                <!-- More Dropdown -->
                <li class="nav-item dropdown more">
                  <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="moreDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #1CAB55;">
                  আরও
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="moreDropdown">
                    <li class="nav-item">
                      <a class="nav-link" href="{{route('students.my-syllabus')}}" style="color: #1CAB55;">
                       সিলেবাস
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="/#our-success">আমাদের সাফল্য</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="/#circular">নিয়োগ বিজ্ঞপ্তি</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="/#our-services">সেবা সমূহ</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="/#Student_review">শিক্ষার্থীদের মতামত</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="{{route('students.complain')}}">অভিযোগ দিন</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="/#book-storage">বুক স্টোরেজ</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="/#our_teacher">শিক্ষক মণ্ডলী</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="{{route('students.my-course')}}">আমার কোর্স সমূহ</a>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
          </div>
        </nav>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <div class="search_button">
              <form class="search_button_area" action="#" method="POST" onclick="event.preventDefault()">
                <input type="hidden" name="_token" value="WlWD0OcHgoiFVuFIs7j0oumKvc7rASkRL84qLvMq" autocomplete="off">                            
                <div class="input-group">
                  <span class="input-group-text border-0">
                  <button class="" type="submit">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  </button>
                  </span>
                  <input type="text" class="form-control border-0 shadow-none"
                    placeholder="Search courses" name="search" />
                </div>
              </form>
            </div>
            <li class="nav-item">
              <a class="nav-link active" aria-current="page"
                href="{{route('homepage')}}">হোম</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/#our_courses">
                কোর্স সমূহ
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="/#exam-section">পরীক্ষা</a>
              <ul class="dropdown-menu" aria-labelledby="moreDropdown">
                <li class="nav-item">
                  <a class="nav-link" href="{{route('category.batches', 'Free Exams')}}">ফ্রি পরীক্ষা সমূহ</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{route('category.batches', 'Premium Exams')}}">প্রিমিয়াম পরীক্ষা সমূহ</a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/#our-success">আমাদের সাফল্য</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/#circular">নিয়োগ বিজ্ঞপ্তি</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="moreDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                আরও
              </a>
              <ul class="dropdown-menu" aria-labelledby="moreDropdown">
                <li class="nav-item">
                  <a class="nav-link" href="/#our-services">সেবা সমূহ</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/#Student_review">শিক্ষার্থীদের মতামত</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{route('students.complain')}}">অভিযোগ দিন</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/#book-storage">বুক স্টোরেজ</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/#our_teacher">শিক্ষক মণ্ডলী</a>
                </li>
              </ul>
            </li>
            <form class="search_button_area mt-lg-1 desk-top-search d-none d-lg-block" action="#" method="POST" onclick="event.preventDefault()">
              <input type="hidden" name="_token" value="WlWD0OcHgoiFVuFIs7j0oumKvc7rASkRL84qLvMq" autocomplete="off">                        
              <div class="input-group">
                <span class="input-group-text border-0">
                <button class="" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                </span>
                <input type="text" class="form-control border-0 shadow-none" placeholder="Search courses"
                  name="search_content" value="" />
              </div>
            </form>
          </ul>

          @if(!empty($user))
          <div class="dropdown user user-menu d-none d-lg-block">
            <div class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <!-- The user image in the navbar-->
              <img src="{{$user->image ? :'/img/avatar.png' }}" class="user-image" alt="">
              <span class="hidden-xs">{{$user->name}}</span>
            </div>
            <ul class="dropdown-menu">
              <!-- The user image in the menu -->
              <div class="user-header">
                <img src="{{$user->image ? :'/img/avatar.png' }} " class="img-circle md-3" alt="User Image"/>

                <h4 style="color: #000; font-size:16px" class="mt-3">
                  <b>{{$user->name}}</b>
                  </br>
                  <small>Reg. ID: {{$source->numset($user->id, 6)}}<br>
                  {{$user->email}}</br>
                  {{$user->contact}}</small>
                </h4>
              </div>

                <li><a class="dropdown-item" href="{{route('students.dashboard')}}">ড্যাশবোর্ড</a></li>
                <li>
                  <a class="dropdown-item" href="{{route('students.my-syllabus')}}">সিলেবাস সমূহ</a>
                </li>
                <li>
                  <a class="dropdown-item" href="{{route('students.my-course')}}">আমার কোর্স সমূহ</a>
                </li>
                <li>
                  <a href="{{route('students.profile')}}" class="dropdown-item ">প্রোফাইল</a>
                </li>
                <li>
                  <form action="{{route('students.logout')}}" method="post" class="btn">
                    @csrf
                    <button type="submit" class="dropdown-item btn btn-warning btn-fluid" onclick="return confirm('Are you sure you want to logout?')">লগআউট</button>
                  </form>
                </li>

            </ul>
          </div>
          @else
          <div class="desk-top-login">
            <div class="nav-item login-button">
              <a href="{{route('students.login')}}" type="button" class="btn btn_warning btn-sm">Login</a>
            </div>
          </div>
          @endif
        </div>
        
      </div> <!-- /.container -->
    </nav>
    <br><br>
  </header>

    
    <!-- Preloader -->

    <main>
      @yield('content')
    </main>
    <!-- Modal -->
        <!-- <div class="modal fade" id="popupModal" data-bs-backdrop="static" data-modal-parent="courseContentModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="modal-body p-0">
                        <div class="card card-body p-0">
                            <img src="/web/../expert-it-bucket.s3.ap-south-1.amazonaws.com/backend/assets/uploaded-files/additional-features-management/popup-notifications/pop-noti-1745129731-691390573370621.webp"
                            alt="popup-img" class="popup-img" style="height: 60%;" loading="lazy" srcset="">
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        
    <!-- Bootstrap Modal Structure -->
    <div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="embed-responsive embed-responsive-16by9">
              <iframe id="modalVideo" class="embed-responsive-item" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position: absolute;"></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Premium StudyRoom Bootstrap 5 Modal -->
    <div class="modal fade" id="homeServicesModalOne" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-4">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title mx-auto" id="modalTitle">📚 BCS & JOBS</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center p-4" id="modalContent">
            <div id="bcs-jobs" class="modal-static-content">
              <p></p>
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary flex-fill me-2" data-bs-dismiss="modal">Back</button>
            <button type="button" class="btn btn-success flex-fill ms-2" id="goToNextButton">Go to Next</button>
          </div>
        </div>
      </div>
    </div>

  <footer id="Footer">
    <div class="container">
      <div class="row g-4">
          <div class="col-sm-12 col-md-4">
              <div class="footer-logo-content mb-lg-5">
                  <div class="footer-logo">
                      <div class="footer-logo-image">
                          <img src="/web/img/logo.png" alt="" srcset="">
                      </div>
                  </div>
                  <div class="footer-logo-text">
                      <p>For countless students across the country,<br>
                        LiveEducationBD is the leading online education platform — a symbol of excellence and inspiration.
                        Begin your journey with confidence and experience dedicated support at every step.</p>
                  </div>
                  <div class="footer-social-media-area">
                      <div class="social-media-icon">
                          <div class="facebook-icon">
                              <a href="javascript:void(0)" target="_blank">
                                <i class="fa-brands fa-facebook"></i>
                              </a>
                          </div>
                          <div class="youtube-icon">
                            <a href="javascript:void(0)" target="_blank">
                              <i class="fa-brands fa-youtube"></i>
                            </a>
                          </div>
                          <div class="instagram-icon">
                            <a href="javascript:void(0)" target="_blank">
                              <i class="fa-brands fa-instagram"></i>
                            </a>
                          </div>
                          <div class="linkedin-icon">
                            <a href="javascript:void(0)" target="_blank">
                              <i class="fa-brands fa-linkedin"></i>
                            </a>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
          <div class="col-md-8">
              <div class="footer-others-div">
                  <div class="row g-4">
                      <div class="col-6 col-md-4">
                          <div class="about-us">
                              <h3>About Us</h3>
                              <ul>
                                <li>
                                  <a href="javascript:void(0)">Our Success</a>
                                </li>
                                <li>
                                  <a href="javascript:void(0)">Instructors</a>
                                </li>
                                <li>
                                  <a href="javascript:void(0)">Our Services</a>
                                </li>
                                <li>
                                  <a href="javascript:void(0)">Book Storage</a>
                                </li>
                                <li>
                                  <a href="javascript:void(0)">Circular</a>
                                </li>
                              </ul>
                          </div>
                      </div>
                      <div class="col-6 col-md-4">
                          <div class="resources">
                            <h3>Resources</h3>
                            <ul>
                              <li><a href="javascript:void(0)">Courses</a></li>
                              <li><a href="javascript:void(0)">Exams</a></li>
                              <li><a href="javascript:void(0)">Terms & Conditions</a></li>
                              <li><a href="javascript:void(0)">Privacy Policy</a></li>
                              <li><a href="javascript:void(0)">Return Policy</a></li>
                            </ul>
                          </div>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <div class="official-info">
                          <h3>Official Info</h3>
                          <ul>
                            <li>
                              <i class="fa-regular fa-envelope"></i>
                              <a href="mailto:liveeducationbd24@gmail.com">liveeducationbd24@gmail.com</a>
                            </li>
                            <li>
                                <i class="fa-solid fa-phone"></i>
                                <a href="tel:+01718778184">01718778184</a>
                            </li>
                            <li>
                                <i class="fa-solid fa-location-dot"></i>
                                <a href="javascript:void(0)">Address: Jogendranagar, Sabgari-6450, Gurudaspur, Natore, Bangladesh<br>
                                  Trade License No. 69141130786</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <div class="col-sm-12 col-md-12">
        <img class="img-fluid" src="/web/img/pay-buttons.png" alt="SSL Image" srcset="" style="width: 100%;">
      </div>
      <br>
    </div>
    <div class="copy-right-section">
      <div class="container">
        <p>Copyright © 2025 <span><a href="/">LiveEuducationBD</a></span> All rights reserved</p>
      </div>
    </div>
  </footer>
  
  <div class="objection btn">
    <a href="https://wa.me/8801718778184" target="blank">
      <i class="fa-brands fa-whatsapp"></i> WhatsApp
    </a>
    |
    <a href="https://m.me/saeonlineexamgroup" target="blank">
      <i class="fab fa-facebook-messenger"></i> Messenger
    </a>
    
  </div>
  
  <!-- JavaScript Libraries (footer) -->
  <script src="{{'/web/assets/js/jquery-3.6.0.min.js'}}"></script>
  <script src="{{'/web/assets/js/bootstrap.bundle.min.js'}}" defer></script>
  <script src="{{'/web/assets/vendors/slick/slick.min.js'}}" defer></script>
  <script src="{{'/web/assets-old/js/owl.carousel.min.js'}}" type="text/javascript" defer></script>
  <script src="{{'/web/assets/js/main.js'}}" defer></script>

  <!-- Toastr JS -->
  <link rel="stylesheet" href="{{'/web/assets/ajax/libs/toastr.js/latest/toastr.min.css'}}" />
  <script src="{{'/web/assets/ajax/libs/toastr.js/latest/toastr.min.js'}}"></script>

  <!-- Sweet Alert -->
  <script async src="{{'/web/assets/npm/sweetalert2%4011.7.3/dist/sweetalert2.all.min.js'}}"></script>

  <!-- Google Tag Manager (noscript) -->
  <!-- <noscript>
      <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T642VTXN" height="0" width="0" style="display:none;visibility:hidden"></iframe>
  </noscript> -->
  <!-- End Google Tag Manager (noscript) -->
    <script>
      $(function() {
        $('#popupModal').modal('show');
      });
    </script>

    <script>
      let extraMenu = document.querySelector('extra-mobile-nav');
      function extMenuShowHide(e){
        console.log(extraMenu);
      }
    </script>
    
   
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const preloader = document.querySelector('.preloader');

        // Start flush animation immediately
        preloader.style.animation = 'flush 1s ease-in-out forwards';

        // Remove preloader after the animation ends
        preloader.addEventListener('animationend', function() {
          preloader.remove();
        });
      });

    </script>
    
    <script>
      // Function to show video modal and set video source
      function showVideoModal(videoSrc) {
        document.getElementById('modalVideo').src = videoSrc; // Autoplay the video
        $('#videoModal').modal('show');
      }
      // When the modal is hidden, remove the src to stop the video
      $('#videoModal').on('click', '.btn-close', function() {
        document.getElementById('modalVideo').src = ''; // This will stop the video
        $('#videoModal').modal('hide');
      });
    </script>
    <script>
    setTimeout(function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
          new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }, 1000); // Delay of 1 second
    $(document).ready(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

</script>

@yield('scripts')

</body>
</html>