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
<!-- top bar  -->
<style>
  body{background: url(/img/bg.jpg) no-repeat right bottom;}
  .main-header{color: #000!important;margin-bottom:50px}
  .navbar-static-top{ background: #fff; position: fixed;top:0; left:0; right:0}
  .header_logo{width: 190px; padding-left: 50px;margin-top: -7px;margin-bottom: -10px}
  .header_a{float:none;}
  .header_menu{float: right;}
  .header_menu a{color: #000!important}
  .item{margin:9px; color:#eee!important;font-size:18px}
  .alert{margin: 10px auto;float: none;}
  .main-header .navbar-brand{color:#444; font-size: 16px}
  .navbar-toggle{color:#444}
  .active{background:#ddd}
</style>

<header class="main-header">
  <nav class="navbar navbar-static-top">
    <div class="container">
      <div class="navbar-header">
        <a href="{{route('homepage')}}" class="navbar-brand">
          <img src="/img/logo.png" alt="" style="max-width: 60px; display:inline; padding-right: 10px"/>
          <b class="brand-name">{{config('app.name')}}</b>
        </a>
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
          <i class="fa fa-bars"></i>
        </button>
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
        <ul class="nav navbar-nav">
          <li class="{{active('home').active('/')}}"><a href="{{route('homepage')}}">হোম <span class="sr-only">(current)</span></a></li>
          <li class="{{active('course')}}"><a href="{{route('home.course')}}">চলমান কোর্স সমূহ</a></li>
          <li class="{{active('my-course')}}"><a href="{{route('students.my-course')}}">আমার কোর্স সমূহ</a></li>
          <li class="{{active('my-syllabus')}}"><a href="{{route('students.my-syllabus')}}">আমার সিলেবাস সমূহ</a></li>
          <li class="{{ request()->is('lebd-student-exams*') ? 'active' : '' }}"><a href="{{ route('student.exam-section.department') }}">পরীক্ষা</a></li>
          <li class="{{active('complain')}}"><a href="{{route('students.complain')}}">অভিযোগ দিন</a></li>
          @if(empty($user))
          <li><a href="/signup">সাইন আপ</a></li>
          <li><a href="/students/login">লগইন</a></li>
          @endif
        </ul>
        {{-- <form class="navbar-form navbar-left" role="search">
          <div class="form-group">
            <input type="text" class="form-control" id="navbar-search-input" placeholder="Search">
          </div>
        </form> --}}
      </div>
      <!-- /.navbar-collapse -->
      <!-- Navbar Right Menu -->
      <div class=" navbar-custom-menu">
        <ul class="nav navbar-nav">
          
          @if(!empty($user))
          <!-- Messages: style can be found in dropdown.less-->
          {{-- <li class="dropdown messages-menu">
            <!-- Menu toggle button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-envelope-o"></i>
              <span class="label label-success">4</span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">You have 4 messages</li>
              <li>
                <!-- inner menu: contains the messages -->
                <ul class="menu">
                  <li><!-- start message -->
                    <a href="#">
                      <div class="pull-left">
                        <!-- User Image -->
                        <img src="../../dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                      </div>
                      <!-- Message title and timestamp -->
                      <h4>
                        Support Team
                        <small><i class="fa fa-clock-o"></i> 5 mins</small>
                      </h4>
                      <!-- The message -->
                      <p>Why not buy a new awesome theme?</p>
                    </a>
                  </li>
                  <!-- end message -->
                </ul>
                <!-- /.menu -->
              </li>
              <li class="footer"><a href="#">See All Messages</a></li>
            </ul>
          </li> --}}
          <!-- /.messages-menu -->


          <!-- Notifications Menu -->
          <li class="dropdown notifications-menu">
            <!-- Menu toggle button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell-o"></i>
              <span class="label label-warning"></span>
            </a>
            {{-- <ul class="dropdown-menu">
              <li class="header">You have 10 notifications</li>
              <li>
                <!-- Inner Menu: contains the notifications -->
                <ul class="menu">
                  <li><!-- start notification -->
                    <a href="#">
                      <i class="fa fa-users text-aqua"></i> 5 new members joined today
                    </a>
                  </li>
                  <!-- end notification -->
                </ul>
              </li>
              <li class="footer"><a href="#">View all</a></li>
            </ul> --}}
          </li>
          <!-- User Account Menu -->
          <li class="dropdown user user-menu">
            <!-- Menu Toggle Button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <!-- The user image in the navbar-->
              <img src="{{$user->image ? :'/img/avatar.png' }}" class="user-image" alt="User Image">
              <!-- hidden-xs hides the username on small devices so only the image appears. -->
              <span class="hidden-xs">{{$user->name}}</span>
            </a>
            <ul class="dropdown-menu">
              <!-- The user image in the menu -->
              <li class="user-header" style="height: 100%">
                <img src="{{$user->image ? :'/img/avatar.png' }} " class="img-circle" alt="User Image"/>
                <h4 style="color: #000">
                  {{$user->name}}</br>
                  <small>Reg. ID: {{$source->numset($user->id, 6)}}<br>
                  {{$user->email}}</br>
                  {{$user->contact}}</small>
                  </h4>
                <div class="clearfix"></div>
              </li>
              <!-- Menu Body -->
              {{-- <li class="user-body">
                <div class="row">
                  <div class="col-xs-4 text-center">
                    <a href="#">Followers</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="#">Sales</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="#">Friends</a>
                  </div>
                </div><!-- /.row -->
              </li> --}}
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="{{route('students.profile')}}" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <form action="{{route('students.logout')}}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to logout?')">Logout</button>
                  </form>
                </div>
              </li>
            </ul>
          </li>
          @endif
        </ul>
      </div> <!-- /.navbar-custom-menu -->
      <div class="clearfix"></div>
    </div> <!-- /.container-fluid -->
  </nav>
</header>

  @include('partials.messages')