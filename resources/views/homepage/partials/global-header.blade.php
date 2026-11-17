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

            @if(!empty($user))
            <button type="button" class="lebd-notice-toggle lebd-mobile-notice-toggle" aria-label="নোটিশ দেখুন" aria-expanded="false">
              <i class="fa fa-bell" aria-hidden="true"></i>
              <span class="lebd-notice-count" aria-live="polite"></span>
            </button>
            @endif

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
                <li class="nav-item">
                  <a class="nav-link {{ request()->is('lebd-student-exams*') ? 'active' : '' }}"
                     href="{{ route('student.exam-section.department') }}" style="color: #1CAB55;">পরীক্ষা</a>
                </li>
                <!-- More Dropdown -->
                <li class="nav-item dropdown more">
                  <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="mobileMoreDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #1CAB55;">
                    আরও
                  </a>
                  <ul class="dropdown-menu lebd-more-menu" aria-labelledby="mobileMoreDropdown">
                    <li><a class="dropdown-item" href="{{ route('premium.recordings.packages') }}"><i class="fa-solid fa-video"></i> Video Section</a></li>
                    <li><a class="dropdown-item" href="{{ route('student.ebook.categories', ['kind' => 'department']) }}"><i class="fa-solid fa-book-open"></i> E-Book</a></li>
                    <li><a class="dropdown-item" href="{{ route('student.jobprep.categories', ['kind' => 'department']) }}"><i class="fa-solid fa-circle-question"></i> MCQ Study</a></li>
                    <li><a class="dropdown-item" href="{{ route('student.advice.index') }}"><i class="fa-solid fa-user-graduate"></i> সফল শিক্ষার্থীর পরামর্শ</a></li>
                    <li><a class="dropdown-item" href="{{ route('home.circular') }}"><i class="fa-solid fa-briefcase"></i> নিয়োগ বিজ্ঞপ্তি</a></li>
                    <li><button type="button" class="dropdown-item lebd-more-expand" data-more-extra-toggle aria-expanded="false">আরও দেখুন <i class="fa-solid fa-chevron-down"></i></button></li>
                    <li class="lebd-more-extra" hidden><a class="dropdown-item" href="{{ route('students.my-syllabus') }}">সিলেবাস</a></li>
                    <li class="lebd-more-extra" hidden><a class="dropdown-item" href="/#our-success">আমাদের সাফল্য</a></li>
                    <li class="lebd-more-extra" hidden><a class="dropdown-item" href="/#our-services">সেবা সমূহ</a></li>
                    <li class="lebd-more-extra" hidden><a class="dropdown-item" href="/#Student_review">শিক্ষার্থীদের মতামত</a></li>
                    <li class="lebd-more-extra" hidden><a class="dropdown-item" href="{{ route('students.complain') }}">অভিযোগ দিন</a></li>
                    <li class="lebd-more-extra" hidden><a class="dropdown-item" href="/#book-storage">বুক স্টোরেজ</a></li>
                    <li class="lebd-more-extra" hidden><a class="dropdown-item" href="/#our_teacher">শিক্ষক মণ্ডলী</a></li>
                    <li class="lebd-more-extra" hidden><a class="dropdown-item" href="{{ route('students.my-course') }}">আমার কোর্স সমূহ</a></li>
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
            <li class="nav-item">
              <a class="nav-link {{ request()->is('lebd-student-exams*') ? 'active' : '' }}"
                 href="{{ route('student.exam-section.department') }}"
                 {{ request()->is('lebd-student-exams*') ? 'aria-current=page' : '' }}>
                <i class="fa-regular fa-file-lines" aria-hidden="true"></i> পরীক্ষা
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/#our-success"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> আমাদের সাফল্য</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('home.circular*') || request()->routeIs('home.circular.active') ? 'active' : '' }}" href="{{ route('home.circular') }}"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> নিয়োগ বিজ্ঞপ্তি</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="moreDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-circle-plus" aria-hidden="true"></i> আরও
              </a>
              <ul class="dropdown-menu lebd-more-menu" aria-labelledby="moreDropdown">
                <li><a class="dropdown-item" href="{{ route('premium.recordings.packages') }}"><i class="fa-solid fa-video"></i> Video Section</a></li>
                <li><a class="dropdown-item" href="{{ route('student.ebook.categories', ['kind' => 'department']) }}"><i class="fa-solid fa-book-open"></i> E-Book</a></li>
                <li><a class="dropdown-item" href="{{ route('student.jobprep.categories', ['kind' => 'department']) }}"><i class="fa-solid fa-circle-question"></i> MCQ Study</a></li>
                <li><a class="dropdown-item" href="{{ route('student.advice.index') }}"><i class="fa-solid fa-user-graduate"></i> সফল শিক্ষার্থীর পরামর্শ</a></li>
                <li><button type="button" class="dropdown-item lebd-more-expand" data-more-extra-toggle aria-expanded="false">আরও দেখুন <i class="fa-solid fa-chevron-down"></i></button></li>
                <li class="lebd-more-extra" hidden><a class="dropdown-item" href="{{ route('students.my-syllabus') }}">সিলেবাস</a></li>
                <li class="lebd-more-extra" hidden><a class="dropdown-item" href="/#our-success">আমাদের সাফল্য</a></li>
                <li class="lebd-more-extra" hidden><a class="dropdown-item" href="/#our-services">সেবা সমূহ</a></li>
                <li class="lebd-more-extra" hidden><a class="dropdown-item" href="/#Student_review">শিক্ষার্থীদের মতামত</a></li>
                <li class="lebd-more-extra" hidden><a class="dropdown-item" href="{{ route('students.complain') }}">অভিযোগ দিন</a></li>
                <li class="lebd-more-extra" hidden><a class="dropdown-item" href="/#book-storage">বুক স্টোরেজ</a></li>
                <li class="lebd-more-extra" hidden><a class="dropdown-item" href="/#our_teacher">শিক্ষক মণ্ডলী</a></li>
                <li class="lebd-more-extra" hidden><a class="dropdown-item" href="{{ route('students.my-course') }}">আমার কোর্স সমূহ</a></li>
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
          <button type="button" class="lebd-nav-bell lebd-notice-toggle d-none d-lg-inline-flex" aria-label="নোটিশ দেখুন" title="নোটিশ" aria-expanded="false">
            <i class="fa-regular fa-bell" aria-hidden="true"></i><span class="lebd-notice-count" aria-live="polite"></span>
          </button>
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

  {{-- Live, per-student notification panel. --}}
  @if(!empty($user))
  <section class="lebd-notification-panel" id="lebdNotificationPanel"
      data-feed-url="{{ route('students.header-notifications') }}"
      data-seen-url="{{ route('students.header-notifications.seen') }}"
      data-seen-all-url="{{ route('students.header-notifications.seen-all') }}"
      data-all-url="{{ route('students.notifications.page') }}"
      data-csrf="{{ csrf_token() }}" aria-label="Notifications" hidden>
    <div class="lebd-notification-card">
      <div class="lebd-notification-header">
        <div>
          <h3>Notifications</h3>
          <button type="button" class="lebd-mark-all-read" id="lebdMarkAllReadBtn"><i class="fa-solid fa-check"></i> Mark as read</button>
        </div>
        <div class="lebd-notification-header-actions">
          <div class="lebd-notification-unread-pill"><span id="lebdUnreadCountText">0</span> unread</div>
          <button type="button" class="lebd-notification-close" id="lebdCloseNotificationsBtn" aria-label="Close notifications" title="Close">&times;</button>
        </div>
      </div>
      <div class="lebd-notification-tabs" role="tablist" aria-label="Notification tabs">
        <button type="button" class="lebd-tab-btn active" data-filter="all" aria-pressed="true">All</button>
        <button type="button" class="lebd-tab-btn" data-filter="unread" aria-pressed="false">Unread</button>
        <button type="button" class="lebd-tab-btn" data-filter="seen" aria-pressed="false">Seen</button>
      </div>
      <div class="lebd-notification-list" id="lebdNotificationList" aria-live="polite">
        <div class="lebd-notification-loading">নোটিফিকেশন লোড হচ্ছে...</div>
      </div>
      <div class="lebd-notification-footer">
        <button type="button" class="lebd-notification-footer-link" id="lebdSeeAllNotificationsBtn">See all notifications <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>
  </section>
  @endif

{{-- Mobile header navigation controller (kept independent from Bootstrap dropdown versions). --}}
<script>
(function(){
  'use strict';
  if(window.__lebdMobileHeaderV75) return;
  window.__lebdMobileHeaderV75=true;
  function setup(){
    var header=document.getElementById('Header'); if(!header)return;
    var toggle=header.querySelector('.lebd-mobile-nav-toggle'), menu=header.querySelector('#navbarSupportedContent');
    var profile=header.querySelector('.lebd-mobile-profile'), profileToggle=header.querySelector('.lebd-mobile-profile-toggle'), profileMenu=header.querySelector('#lebdMobileProfileMenu');
    function closeNav(){if(!toggle||!menu)return;menu.classList.remove('show');toggle.classList.add('collapsed');toggle.setAttribute('aria-expanded','false');}
    function closeProfile(){if(!profileToggle||!profileMenu)return;profileMenu.classList.remove('show');profileToggle.setAttribute('aria-expanded','false');if(profile)profile.classList.remove('show');}
    if(toggle&&menu)toggle.addEventListener('click',function(e){if(window.innerWidth>991)return;e.preventDefault();e.stopPropagation();var open=!menu.classList.contains('show');closeProfile();menu.classList.toggle('show',open);toggle.classList.toggle('collapsed',!open);toggle.setAttribute('aria-expanded',String(open));});
    if(profileToggle&&profileMenu)profileToggle.addEventListener('click',function(e){if(window.innerWidth>991)return;e.preventDefault();e.stopPropagation();var open=!profileMenu.classList.contains('show');closeNav();profileMenu.classList.toggle('show',open);profileToggle.setAttribute('aria-expanded',String(open));if(profile)profile.classList.toggle('show',open);});
    document.addEventListener('click',function(e){if(window.innerWidth>991)return;if(menu&&toggle&&!menu.contains(e.target)&&!toggle.contains(e.target))closeNav();if(profile&&!profile.contains(e.target))closeProfile();});
    document.addEventListener('keydown',function(e){if(e.key==='Escape'){closeNav();closeProfile();}});
    window.addEventListener('resize',function(){if(window.innerWidth>991){closeNav();closeProfile();}});
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',setup);else setup();
})();
</script>

<style>
/* LEBD live notification panel */
#Header .lebd-notice-toggle { position:relative; border:0; background:transparent; color:#1b365c; cursor:pointer; align-items:center; justify-content:center; }
#Header .lebd-mobile-notice-toggle { display:inline-flex; width:34px; height:36px; font-size:18px; }
#Header .lebd-nav-bell { display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; margin-left:8px; border-radius:50%; border:1px solid #dbe7f7; background:#fff; font-size:19px; box-shadow:0 3px 12px rgba(30,64,124,.08); }
#Header .lebd-notice-count { display:none; position:absolute; top:-3px; right:-2px; min-width:18px; height:18px; padding:0 5px; border-radius:20px; background:#ef4444; color:#fff; font:700 10px/18px Arial,sans-serif; text-align:center; box-shadow:0 2px 8px rgba(239,68,68,.3); }
#Header .lebd-notice-count.has-unread { display:block; }
#Header .lebd-more-menu { min-width:245px; max-height:min(70vh,520px); overflow-y:auto; padding:8px; }
#Header .lebd-more-menu .dropdown-item { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:8px; color:#18365e; font-size:14px; white-space:normal; }
#Header .lebd-more-menu .dropdown-item:hover { background:#eef5ff; color:#0869cf; }
#Header .lebd-more-menu .dropdown-item i { width:18px; color:#0877df; }
#Header .lebd-more-expand { width:100%; border:0; background:#f0f6ff; justify-content:space-between; font-weight:700; }
.lebd-notification-panel { position:fixed; z-index:100500; top:var(--lebd-header-bottom,86px); right:max(12px,calc((100vw - 1320px)/2 + 10px)); width:min(390px,calc(100vw - 24px)); }
.lebd-notification-panel[hidden] { display:none !important; }
.lebd-notification-card { background:#fff; border:1px solid #d8e5fb; border-radius:22px; box-shadow:0 20px 60px rgba(12,38,78,.22); overflow:hidden; }
.lebd-notification-header { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; padding:18px 18px 10px; }
.lebd-notification-header h3 { margin:0 0 8px; font-size:28px; line-height:1.1; font-weight:800; color:#1d2942; }
.lebd-mark-all-read { border:0; background:transparent; color:#2563eb; font-size:14px; font-weight:700; padding:0; }
.lebd-notification-header-actions { display:flex; align-items:center; gap:8px; flex:0 0 auto; }
.lebd-notification-unread-pill { white-space:nowrap; align-self:flex-start; background:#fff1f1; color:#e44949; border:1px solid #ffd6d6; border-radius:999px; padding:6px 12px; font-size:12px; font-weight:700; }
.lebd-notification-close { display:inline-flex; align-items:center; justify-content:center; flex:0 0 30px; width:30px; height:30px; padding:0; border:1px solid #e1e8f2; border-radius:50%; background:#f5f8fd; color:#52647d; font:700 21px/1 Arial,sans-serif; cursor:pointer; transition:background .15s ease,color .15s ease,border-color .15s ease; }
.lebd-notification-close:hover,.lebd-notification-close:focus-visible { background:#feecec; border-color:#ffcaca; color:#dc2626; outline:none; }
.lebd-notification-tabs { display:flex; gap:8px; padding:0 18px 14px; align-items:center; }
.lebd-tab-btn { border:0; background:#f2f6ff; color:#5a6e8f; padding:7px 14px; border-radius:999px; font-size:13px; font-weight:700; }
.lebd-tab-btn.active { background:#e8f0ff; color:#2563eb; }
.lebd-notification-list { max-height:min(60vh,520px); overflow-y:auto; padding:0 10px 8px; }
.lebd-notification-item { display:flex; gap:12px; margin:0 8px 12px; padding:14px; border-radius:18px; background:#fff9ea; border:1px solid #f1e5bb; }
.lebd-notification-item.is-seen { background:#f7f9fc; border-color:#e6ebf4; }
.lebd-notification-icon { flex:0 0 48px; width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:19px; }
.lebd-icon-result { background:#fff1cf; color:#e0a12f; } .lebd-icon-notice { background:#e8f1ff; color:#4d7df0; }
.lebd-notification-body { flex:1 1 auto; min-width:0; }
.lebd-notification-row-top { display:flex; gap:10px; justify-content:space-between; align-items:flex-start; }
.lebd-notification-title { font-size:15px; font-weight:800; color:#1f2d3d; line-height:1.35; }
.lebd-notification-status-badge { flex:0 0 auto; border-radius:999px; padding:4px 10px; font-size:11px; font-weight:800; background:#ff5a5f; color:#fff; }
.lebd-notification-item.is-seen .lebd-notification-status-badge { background:#eef2f7; color:#6b7a90; }
.lebd-notification-message { margin-top:6px; font-size:13px; line-height:1.55; color:#51637f; }
.lebd-notification-row-bottom { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:10px; }
.lebd-notification-row-bottom small { color:#6e809b; font-size:11px; display:inline-flex; gap:6px; align-items:center; }
.lebd-notification-view-btn,.lebd-notification-seen-btn,.lebd-notification-footer-link { border:1px solid #a8c3ff; background:#f8fbff; color:#2563eb; border-radius:10px; padding:6px 14px; font-size:12px; font-weight:800; text-decoration:none; }
.lebd-notification-view-btn:hover,.lebd-notification-seen-btn:hover,.lebd-notification-footer-link:hover { background:#edf4ff; }
.lebd-notification-seen-btn:disabled { opacity:.65; cursor:default; }
.lebd-notification-footer { padding:6px 18px 18px; text-align:center; }
.lebd-notification-footer-link { display:block; width:100%; background:#f2f6ff; border-color:#dbe7ff; color:#2563eb; }
.lebd-notification-empty,.lebd-notification-loading { display:flex; gap:12px; align-items:center; justify-content:center; margin:8px; padding:22px 16px; border:1px dashed #d6dfef; border-radius:16px; color:#6c819c; font-size:13px; }
@media(max-width:991px) { .lebd-notification-panel { right:10px; top:var(--lebd-header-bottom,112px); width:min(390px,calc(100vw - 20px)); } .lebd-notification-header h3 { font-size:23px; } .lebd-notification-item { padding:12px; border-radius:16px; } .lebd-notification-icon { flex-basis:44px; width:44px; height:44px; font-size:17px; } }
</style>
<script>
(function(){
  'use strict';
  if(window.__lebdLiveHeaderNotificationsV3) return;
  window.__lebdLiveHeaderNotificationsV3 = true;
  function setup(){
    var header=document.getElementById('Header'), panel=document.getElementById('lebdNotificationPanel');
    if(!header || !panel) return;
    var toggles=Array.prototype.slice.call(header.querySelectorAll('.lebd-notice-toggle'));
    var list=document.getElementById('lebdNotificationList'), unreadText=document.getElementById('lebdUnreadCountText');
    var filter='all', latest=[];
    var feedUrl=panel.dataset.feedUrl, seenUrl=panel.dataset.seenUrl, seenAllUrl=panel.dataset.seenAllUrl, csrf=panel.dataset.csrf;
    function placePanel(){ var r=header.getBoundingClientRect(); panel.style.setProperty('--lebd-header-bottom',Math.ceil(r.bottom+10)+'px'); }
    function openPanel(){ placePanel(); panel.hidden=false; toggles.forEach(function(t){t.setAttribute('aria-expanded','true');}); refresh(); }
    function closePanel(){ panel.hidden=true; toggles.forEach(function(t){t.setAttribute('aria-expanded','false');}); }
    var closeButton=document.getElementById('lebdCloseNotificationsBtn');
    if(closeButton)closeButton.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();closePanel();});
    toggles.forEach(function(t){ t.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();panel.hidden?openPanel():closePanel();}); });
    document.addEventListener('click',function(e){if(!panel.hidden && !panel.contains(e.target) && !toggles.some(function(t){return t.contains(e.target);}))closePanel();});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')closePanel();});
    window.addEventListener('resize',function(){if(!panel.hidden)placePanel();},{passive:true});
    window.addEventListener('scroll',function(){if(!panel.hidden)placePanel();},{passive:true});

    function updateBadges(unread){
      if(unreadText) unreadText.textContent=unread;
      toggles.forEach(function(t){var b=t.querySelector('.lebd-notice-count');if(!b)return;b.textContent=unread>99?'99+':String(unread);b.classList.toggle('has-unread',unread>0);});
    }
    function post(url,body){
      return fetch(url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(body||{})}).then(function(r){if(!r.ok)throw new Error('Request failed');return r.json();});
    }
    function make(tag,cls,txt){var el=document.createElement(tag);if(cls)el.className=cls;if(txt!==undefined)el.textContent=txt;return el;}
    function render(){
      list.replaceChildren();
      // Seen notifications leave the default popup list immediately. The Seen tab
      // remains available as an explicit history view; newest items are already
      // supplied first by the notification feed.
      var shown=latest.filter(function(n){return filter==='seen'?!!n.seen:!n.seen;});
      if(!shown.length){list.appendChild(make('div','lebd-notification-empty','এখানে কোনো নোটিফিকেশন নেই।'));return;}
      shown.forEach(function(n){
        var card=make('article','lebd-notification-item'+(n.seen?' is-seen':''));
        var icon=make('div','lebd-notification-icon lebd-icon-'+(n.tone||'notice'));
        var iconI=make('i','fa-solid '+(n.icon||'fa-bell'));icon.appendChild(iconI);
        var body=make('div','lebd-notification-body'), top=make('div','lebd-notification-row-top');
        top.appendChild(make('div','lebd-notification-title',n.title));
        top.appendChild(make('span','lebd-notification-status-badge',n.seen?'Seen':'New'));
        body.appendChild(top);body.appendChild(make('div','lebd-notification-message',n.message));
        var bottom=make('div','lebd-notification-row-bottom');
        bottom.appendChild(make('small','',n.time||'Recently'));
        var actions=make('div','lebd-notification-actions');
        if(n.url){var view=make('a','lebd-notification-view-btn','View');view.href=n.url;view.addEventListener('click',function(){if(!n.seen)post(seenUrl,{key:n.key}).catch(function(){});});actions.appendChild(view);}
        var seen=make('button','lebd-notification-seen-btn',n.seen?'Seen ✓':'Seen');seen.type='button';seen.disabled=!!n.seen;
        seen.addEventListener('click',function(){post(seenUrl,{key:n.key}).then(refresh).catch(function(){alert('Seen status save করা যায়নি। আবার চেষ্টা করুন।');});});
        actions.appendChild(seen);bottom.appendChild(actions);body.appendChild(bottom);
        if(n.image){var img=make('img','lebd-notification-detail-image');img.src=n.image;img.alt=n.title;body.appendChild(img);}
        card.appendChild(icon);card.appendChild(body);list.appendChild(card);
      });
    }
    function refresh(){
      fetch(feedUrl,{credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){if(!r.ok)throw new Error('Feed unavailable');return r.json();})
        .then(function(data){latest=data.notifications||[];updateBadges(Number(data.unread||0));render();})
        .catch(function(){if(!latest.length)list.textContent='নোটিফিকেশন লোড করা যাচ্ছে না।';});
    }
    panel.querySelectorAll('.lebd-tab-btn').forEach(function(btn){btn.addEventListener('click',function(){filter=btn.dataset.filter||'all';panel.querySelectorAll('.lebd-tab-btn').forEach(function(b){var a=b===btn;b.classList.toggle('active',a);b.setAttribute('aria-pressed',a?'true':'false');});render();});});
    var markAll=document.getElementById('lebdMarkAllReadBtn');
    if(markAll)markAll.addEventListener('click',function(){post(seenAllUrl,{}).then(refresh).catch(function(){alert('Seen status save করা যায়নি।');});});
    var seeAll=document.getElementById('lebdSeeAllNotificationsBtn');
    if(seeAll)seeAll.addEventListener('click',function(){
      var allUrl=panel.dataset.allUrl;
      if(allUrl) window.location.href=allUrl;
    });
    header.querySelectorAll('[data-more-extra-toggle]').forEach(function(button){button.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();var menu=button.closest('.lebd-more-menu');if(!menu)return;var expanded=button.getAttribute('aria-expanded')==='true';menu.querySelectorAll('.lebd-more-extra').forEach(function(item){item.hidden=expanded;});button.setAttribute('aria-expanded',String(!expanded));button.innerHTML=expanded?'আরও দেখুন <i class="fa-solid fa-chevron-down"></i>':'কম দেখুন <i class="fa-solid fa-chevron-up"></i>';});});
    refresh();
    window.setInterval(refresh,30000); // New exam/result notifications appear without reloading the page.
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',setup);else setup();
})();
</script>
