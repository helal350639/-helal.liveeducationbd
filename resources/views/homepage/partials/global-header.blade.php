{{-- LEBD-3 v76: four-item mobile quick navigation. --}}
<link rel="stylesheet" href="{{ asset('web/css/lebd-shared-header-v72.css') }}?v=76">
<link rel="stylesheet" href="{{ asset('web/css/lebd-layout-fix-v78.css') }}?v=78">
{{-- LEBD-3 v69: single shared navigation markup for homepage and student layouts. --}}
  <header id="Header">
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container">
        {{-- Desktop logo: separate from the mobile-only wrapper (v72 hid the only logo). --}}
        <div class="lebd-desktop-brand main-logo">
          <a href="{{ route('homepage') }}" aria-label="Live Education BD হোম">
            <img src="{{ asset('web/img/logo.png') }}" alt="Live Education BD" class="logo-img">
          </a>
        </div>
        <div class="mobile-menu d-flex justify-content-between align-items-center">
          <div class="main-logo">
            <a href="{{route('homepage')}}">
              <img src="/web/img/logo.png?v=1.1.2" alt="Logo" class="logo-img">
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
              <div class="dropdown user user-menu lebd-mobile-profile">
                <button class="dropdown-toggle lebd-mobile-profile-toggle" type="button" aria-expanded="false" aria-controls="lebdMobileProfileMenu">
                  <!-- The user image in the navbar-->
                  <img src="{{$user->image ? : '/img/avatar.png' }}" class="mobile-user-image" alt="প্রোফাইল">
                </button>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow dropdown-center lebd-mobile-profile-menu" id="lebdMobileProfileMenu">
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
                    <form action="{{route('students.logout')}}" method="post" class="onclick">
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
              <button class="navbar-toggler collapsed lebd-mobile-nav-toggle" type="button" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="মেনু খুলুন">
                <span class="navbar-toggler-icon"></span>
              </button>
            </div>
          </div>
        </div>


        {{-- Mobile: exactly four persistent quick navigation items. Other links remain in hamburger. --}}
        <nav class="lebd-mobile-quicknav" aria-label="মোবাইল প্রধান মেনু">
          <a href="{{ route('homepage') }}" class="lebd-mobile-quicknav-item {{ request()->is('/') ? 'active' : '' }}" {{ request()->is('/') ? 'aria-current="page"' : '' }}>
            <i class="fa-solid fa-house" aria-hidden="true"></i><span>হোম</span>
          </a>
          <a href="{{ route('students.dashboard') }}" class="lebd-mobile-quicknav-item {{ request()->is('students') ? 'active' : '' }}" {{ request()->is('students') ? 'aria-current="page"' : '' }}>
            <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i><span>ড্যাশবোর্ড</span>
          </a>
          <a href="{{ route('homepage.courses') }}" class="lebd-mobile-quicknav-item {{ request()->is('homepage/courses') || request()->is('home/course/*') ? 'active' : '' }}" {{ request()->is('homepage/courses') || request()->is('home/course/*') ? 'aria-current="page"' : '' }}>
            <i class="fa-solid fa-book-open" aria-hidden="true"></i><span>কোর্স সমূহ</span>
          </a>
          <a href="{{ route('homepage.running.batches') }}" class="lebd-mobile-quicknav-item {{ request()->is('homepage/running-batches') || request()->is('homepage/batch/*') ? 'active' : '' }}" {{ request()->is('homepage/running-batches') || request()->is('homepage/batch/*') ? 'aria-current="page"' : '' }}>
            <i class="fa-solid fa-users" aria-hidden="true"></i><span>ব্যাচ</span>
          </a>
        </nav>

        <nav class="navbar-2 navbar-expand navbar-light" id="extra-mobile-nav">
          <div class="container">
            <div class="navbar-collapse">
              <ul class="navbar-nav justify-content-center w-100">
                <li class="nav-item">
                  <a class="nav-link" href="{{route('students.dashboard')}}"  style="color: #1CAB55; background:#eee; border-radius:10px">ড্যাশবোর্ড</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('homepage.courses') }}" style="color: #1CAB55;">
                    কোর্স
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('homepage.running.batches') }}" style="color: #1CAB55;">ব্যাচ</a>
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
                      <a class="nav-link" href="/#our-success"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> আমাদের সাফল্য</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="/#circular"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> নিয়োগ বিজ্ঞপ্তি</a>
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
                @csrf                            
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
              <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{route('homepage')}}"><i class="fa-solid fa-house" aria-hidden="true"></i> হোম</a>
            </li>
            @if(!empty($user))
            <li class="nav-item lebd-nav-dashboard">
              <a class="nav-link {{ request()->is('students*') && request()->is('students') ? 'active' : '' }}" href="{{ route('students.dashboard') }}"><i class="fa-solid fa-table-cells-large" aria-hidden="true"></i> ড্যাশবোর্ড</a>
            </li>
            @endif
            <li class="nav-item">
              <a class="nav-link {{ request()->is('homepage/courses') || request()->is('home/course/*') ? 'active' : '' }}" href="{{ route('homepage.courses') }}">
                <i class="fa-solid fa-book-open" aria-hidden="true"></i> কোর্স সমূহ
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('homepage/running-batches') || request()->is('homepage/batch/*') ? 'active' : '' }}" href="{{ route('homepage.running.batches') }}"><i class="fa-solid fa-users" aria-hidden="true"></i> ব্যাচ</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle {{ request()->is('category/*') ? 'active' : '' }}" data-bs-toggle="dropdown" href="/#exam-section"><i class="fa-regular fa-file-lines" aria-hidden="true"></i> পরীক্ষা</a>
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
              <a class="nav-link" href="/#our-success"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> আমাদের সাফল্য</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/#circular"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> নিয়োগ বিজ্ঞপ্তি</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="moreDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-circle-plus" aria-hidden="true"></i> আরও
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
              @csrf                        
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
          <a class="lebd-nav-bell d-none d-lg-inline-flex" href="{{ route('students.dashboard') }}" aria-label="ড্যাশবোর্ড ও নোটিফিকেশন দেখুন" title="নোটিফিকেশন"><i class="fa-regular fa-bell" aria-hidden="true"></i></a>
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
  </header>

{{-- v75: self-contained mobile controls; resilient across Bootstrap versions. --}}
<script>
(function(){
  'use strict';
  if(window.__lebdMobileHeaderV75) return;
  window.__lebdMobileHeaderV75=true;
  function setup(){
    var header=document.getElementById('Header');
    if(!header) return;
    var toggle=header.querySelector('.lebd-mobile-nav-toggle');
    var menu=header.querySelector('#navbarSupportedContent');
    var profile=header.querySelector('.lebd-mobile-profile');
    var profileToggle=header.querySelector('.lebd-mobile-profile-toggle');
    var profileMenu=header.querySelector('#lebdMobileProfileMenu');
    function closeNav(){if(!toggle||!menu)return;menu.classList.remove('show');toggle.classList.add('collapsed');toggle.setAttribute('aria-expanded','false');toggle.setAttribute('aria-label','মেনু খুলুন');}
    function closeProfile(){if(!profileToggle||!profileMenu)return;profileMenu.classList.remove('show');profileToggle.setAttribute('aria-expanded','false');if(profile) profile.classList.remove('show');}
    if(toggle&&menu){toggle.addEventListener('click',function(e){if(window.innerWidth>991)return;e.preventDefault();e.stopPropagation();var willOpen=!menu.classList.contains('show');closeProfile();menu.classList.toggle('show',willOpen);toggle.classList.toggle('collapsed',!willOpen);toggle.setAttribute('aria-expanded',String(willOpen));toggle.setAttribute('aria-label',willOpen?'মেনু বন্ধ করুন':'মেনু খুলুন');});}
    if(profileToggle&&profileMenu){profileToggle.addEventListener('click',function(e){if(window.innerWidth>991)return;e.preventDefault();e.stopPropagation();var willOpen=!profileMenu.classList.contains('show');closeNav();profileMenu.classList.toggle('show',willOpen);profileToggle.setAttribute('aria-expanded',String(willOpen));if(profile)profile.classList.toggle('show',willOpen);});}
    document.addEventListener('click',function(e){if(window.innerWidth>991)return;if(menu&&toggle&&!menu.contains(e.target)&&!toggle.contains(e.target))closeNav();if(profile&& !profile.contains(e.target))closeProfile();});
    document.addEventListener('keydown',function(e){if(e.key==='Escape'){closeNav();closeProfile();}});
    window.addEventListener('resize',function(){if(window.innerWidth>991){closeNav();closeProfile();}});
    // If Bootstrap dropdown handlers are also installed, keep this mobile menu controlled here.
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',setup);else setup();
})();
</script>
