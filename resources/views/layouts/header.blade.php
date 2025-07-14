<?php
$user = Auth::user();
$notifications = [];
$complains = \App\Models\Complain::where('status', 'New')->limit(15)->get();
if($complains)
{
  $notifications = $complains;
}
?>
  <header class="main-header">
    <!-- Logo -->
    <a href="/home" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src="{{asset('/img/logo.png?v=3008')}}" width="50"></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img src="{{asset('/img/logo.png?v=3008')}}" class="img-responsive" style="width: 100px;margin:auto"></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
          <li class="dropdown messages-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-envelope-o"></i>
              <span class="label label-success">{{-- 4 --}}</span>
            </a>
            
          </li>
          @if($notifications->count())
          <!-- Notifications: style can be found in dropdown.less -->
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell-o"></i>
              <span class="label label-danger">{{$notifications->count()}}</span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">You have notifications</li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                  @foreach($notifications as $k => $val)
                  <li>
                    <a href="{{route('complain.show', $val->id)}}">
                      <i class="fa fa-circle-o text-aqua"></i>
                      {{substr($val->details, 0, 25)}}
                    </a>
                  </li>
                  @endforeach
                </ul>
              </li>
              <li class="footer"><a href="{{route('complain.index')}}">View all</a></li>
            </ul>
          </li>
          <!-- Tasks: style can be found in dropdown.less -->
          @endif
         
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="{{$user->image ? $user->image:'avatar.png'}}" class="user-image" alt="User Image">
              <span class="hidden-xs">{{ $user->name }}</span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="{{$user->image ? $user->image:'avatar.png'}}" class="img-circle" alt="User Image">
              </li>
              
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="{{route('profile')}}" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="{{ route('logout') }}" class="btn btn-default btn-flat" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                </div>
              </li>
            </ul>
          </li>
          
        </ul>
      </div>
    </nav>
  </header>

  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{{$user->image ? $user->image:'avatar.png'}}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info" style="text-align:right">
          <p>{{ $user->name }}</p>
          <a href="#"><i class="fa fa-circle text-success"></i>
            
            {{ Auth::user()->authRole()->name }}

          </a><br>
        </div>
      </div>
      <!-- search form -->
      
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <br>
      <ul class="sidebar-menu" data-widget="tree">
        {{-- <li class="header">MAIN NAVIGATION</li> --}}
        <li class="">
          <a href="/home">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
          </a>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-at"></i>
            <span>Emails</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('email.create')}}"><i class="fa fa-pencil"></i> Compose Email</a></li>
            <li><a href="{{route('email.index')}}"><i class="fa fa-list"></i> View Emails</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-envelope"></i>
            <span>SMS</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('sms.create')}}"><i class="fa fa-send"></i> Send SMS</a></li>
            <li><a href="{{route('sms.index')}}"><i class="fa fa-list"></i> View SMS</a></li>
          </ul>
        </li>


        <li class="treeview">
          <a href="#">
            <i class="fa fa-pencil"></i>
            <span>Exams</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('exam.live')}}"><i class="fa fa-bullseye"></i>Live Exams</a></li>
            {{-- <li><a href="{{route('exam.view')}}"><i class="fa fa-th-large"></i>View Exams</a></li> --}}
            <li><a href="{{route('exam.index')}}"><i class="fa fa-pencil"></i>View All Exams</a></li>
            {{-- <li><a href="#"><i class="fa fa-server"></i> MCQ</a></li>
            <li><a href="#"><i class="fa fa-pencil"></i> Written</a></li>
            <li><a href="#"><i class="fa fa-play"></i> Video</a></li> --}}
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-question"></i>
            <span>Questions</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('question.create')}}"><i class="fa fa-plus"></i>Add Question</a></li>
            <li><a href="{{route('question.view')}}"><i class="fa fa-th-large"></i>View Questions</a></li>
            <li><a href="{{route('question.index')}}"><i class="fa fa-list"></i>Question List</a></li>
            {{-- <li><a href="#"><i class="fa fa-server"></i> MCQ</a></li>
            <li><a href="#"><i class="fa fa-pencil"></i> Written</a></li>
            <li><a href="#"><i class="fa fa-play"></i> Video</a></li> --}}
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-file-o"></i>
            <span>Question Papers</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('paper.create')}}"><i class="fa fa-pencil"></i> Create Question Paper</a></li>
            <li><a href="{{route('paper.index')}}"><i class="fa fa-list"></i> View Question Papers</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-files-o"></i>
            <span>Syllabuses</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('syllabus.create')}}"><i class="fa fa-pencil"></i> Create Syllabus</a></li>
            <li><a href="{{route('syllabus.index')}}"><i class="fa fa-file-text"></i> View Syllabus</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-table"></i>
            <span>Routines</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('routine.create')}}"><i class="fa fa-pencil"></i> Create Routine</a></li>
            <li><a href="{{route('routine.index')}}"><i class="fa fa-file-text"></i> View Routines</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-th"></i>
            <span>Batches</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('batch.create')}}"><i class="fa fa-pencil"></i> Create Batch</a></li>
            
            <li><a href="{{route('batch.index')}}"><i class="fa fa-th"></i> View Batches</a></li>
          </ul>
        </li>

        {{-- <li class="treeview">
          <a href="#">
            <i class="fa fa-object-group"></i>
            <span>Groups</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('group.create')}}"><i class="fa fa-pencil"></i> Create Group</a></li>
            <li><a href="{{route('group.index')}}"><i class="fa fa-object-group"></i> View Groups</a></li>
          </ul>
        </li> --}}
       
        <li class="treeview">
          <a href="#">
            <i class="fa fa-object-ungroup"></i>
            <span>Categories</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('course.index')}}"><i class="fa fa-file-text"></i> View Courses</a></li>
            <li><a href="{{route('department.index')}}"><i class="fa fa-sitemap"></i> View Departments</a></li>
            <li><a href="{{route('semester.index')}}"><i class="fa fa-tree"></i> View Semesters</a></li>
            <li><a href="{{route('subject.index')}}"><i class="fa fa-book"></i> View Subjects</a></li>
            <li><a href="{{route('chapter.index')}}"><i class="fa fa-bookmark"></i> View Chapters</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-users"></i>
            <span>Students</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('student.create')}}"><i class="fa fa-circle-o"></i> Add Student</a></li>
            <li><a href="{{route('student.index')}}"><i class="fa fa-circle-o"></i> View Students</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-graduation-cap"></i>
            <span>Teachers</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{route('teacher.create')}}"><i class="fa fa-plus"></i> Add Teacher</a></li>
            <li><a href="{{route('teacher.index')}}"><i class="fa fa-circle-o"></i> View Teachers</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-sliders"></i> <span>Filters</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('filter.index') }}"><i class="fa fa-filter"></i> View Filters</a></li>
            <li><a href="{{ route('sub-filter.index') }}"><i class="fa fa-sliders"></i> View Sub-Filters</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-commenting"></i> <span>Complains</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('complain.index') }}"><i class="fa fa-circle-o"></i> View Complains</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-shopping-cart"></i> <span>Orders</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('order.index') }}"><i class="fa fa-circle-o"></i> View Orders</a></li>
          </ul>
        </li>

        @if(Auth::user()->authorizeRoles(['SuperAdmin']))

        <li class="treeview">
          <a href="#">
            <i class="fa fa-user-secret"></i> <span>Admins</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('user.create') }}"><i class="fa fa-user-plus"></i> Create User</a></li>
            <li><a href="{{ route('user.index') }}"><i class="fa fa-users"></i> View Users</a></li>
          </ul>
        </li>

        {{-- <li>
          <a href="{{ route('database.download') }}" class="btn btn-primary">
          Download Database Backup </a>
        </li> --}}

        @endif

        <li class="treeview">
          <a href="#">
            <i class="fa fa-gear"></i> <span>Homepage Settings</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li>
              <a href="/page"><i class="fa fa-copy"></i> Pages</a>
            </li>
            <li>
              <a href="/about-us"><i class="fa fa-copy"></i> About Us</a>
            </li>
            <li>
              <a href="/circular-type"><i class="fa fa-copy"></i> Circular Types</a>
            </li>
            <li>
              <a href="/circular"><i class="fa fa-copy"></i> Circulars</a>
            </li>
            <li>
              <a href="#"><i class="fa fa-youtube"></i> Videos</a>
            </li>
          </ul>
        </li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-gear"></i> <span>Settings</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('profile') }}"><i class="fa fa-user"></i> Profile</a></li>
            <li><a href="/change_password"><i class="fa fa-lock"></i> Change Password</a></li>
          </ul>
        </li>
        
      </ul>
    </section> <!-- /.sidebar -->
  </aside>

  <div class="alert-section" style="">
    <div class="clearfix"></div>
    @include('partials.messages')
   
    <div class="clearfix"></div>
  </div>