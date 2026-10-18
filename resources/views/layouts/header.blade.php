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
      <span class="logo-mini">
        <img src="{{asset('/img/favicon.png?v=3008')}}" width="50">
      </span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg">
        <img src="{{asset('/img/logo.png?v=3008')}}" class="img-responsive" style="width: 160px; margin:auto">
      </span>
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
        {{-- LEBD - Admin Dashboard submenu --}}
        @php
            $dashboardMenuActive = request()->routeIs('home', 'admin.exam.activity', 'admin.dashboard.old', 'admin.recent.activity*')
                || request()->is('home', 'home/old', 'exam-activity-dashboard', 'admin/recent-activity*');
        @endphp
        <li class="treeview {{ $dashboardMenuActive ? 'active menu-open' : '' }}">
            <a href="#">
                <i class="fa fa-dashboard"></i>
                <span>Dashboard</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu" style="{{ $dashboardMenuActive ? 'display: block;' : 'display: none;' }}">
                <li class="{{ request()->routeIs('admin.exam.activity') || request()->is('exam-activity-dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.exam.activity') }}">
                        <i class="fa fa-line-chart"></i> Exam Activity Dashboard
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.dashboard.old') || request()->is('home/old') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard.old') }}">
                        <i class="fa fa-desktop"></i> Old Dashboard
                    </a>
                </li>
                {{-- Recent Activity Dashboard --}}
                <li class="{{ request()->routeIs('admin.recent.activity*') || request()->is('admin/recent-activity*') ? 'active' : '' }}">
                    <a href="{{ route('admin.recent.activity') }}">
                        <i class="fa fa-history"></i> Recent Activity
                    </a>
                </li>
            </ul>
        </li>
        {{-- LEBD-3 MCQ Job Preparation --}}
        @php $jpActive = request()->routeIs('admin.job-preparation.*', 'admin.mcq-subscription.*') || request()->is('admin/mcq-subscription*'); @endphp
        <li class="treeview {{ $jpActive ? 'active menu-open' : '' }}">
          <a href="#"><i class="fa fa-book"></i> <span>MCQ Job Preparation</span>
            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
          </a>
          <ul class="treeview-menu" style="{{ $jpActive ? 'display:block;' : '' }}">
            <li class="{{ request()->routeIs('admin.job-preparation.categories') && request('kind') === 'department' ? 'active' : '' }}">
              <a href="{{ route('admin.job-preparation.categories', ['kind' => 'department']) }}"><i class="fa fa-building"></i> Department</a>
            </li>
            <li class="{{ request()->routeIs('admin.job-preparation.categories') && request('kind') === 'non-department' ? 'active' : '' }}">
              <a href="{{ route('admin.job-preparation.categories', ['kind' => 'non-department']) }}"><i class="fa fa-folder-open"></i> Non-Department</a>
            </li>
            {{-- MCQ Subscription Package Management --}}
            @if(\Illuminate\Support\Facades\Route::has('admin.mcq-subscription.index'))
            <li class="{{ request()->routeIs('admin.mcq-subscription.index', 'admin.mcq-subscription.create', 'admin.mcq-subscription.edit') ? 'active' : '' }}">
              <a href="{{ route('admin.mcq-subscription.index') }}"><i class="fa fa-cubes"></i> Subscription Packages</a>
            </li>
            @endif
          </ul>
        </li>
        {{-- LEBD-3 E-Book: Department / Non-Department --}}
        @php
          $ebookMenuActive = request()->routeIs('admin.ebook.*') || request()->is('admin/e-book/*');
          $ebookDepartmentActive = $ebookMenuActive && request()->segment(3) === 'department';
          $ebookNonDepartmentActive = $ebookMenuActive && request()->segment(3) === 'non-department';
        @endphp
        @if(\Illuminate\Support\Facades\Route::has('admin.ebook.categories'))
        <li class="treeview {{ $ebookMenuActive ? 'active menu-open' : '' }}">
          <a href="#"><i class="fa fa-book"></i> <span>E-Book</span>
            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
          </a>
          <ul class="treeview-menu" style="{{ $ebookMenuActive ? 'display:block;' : '' }}">
            <li class="{{ $ebookDepartmentActive ? 'active' : '' }}">
              <a href="{{ route('admin.ebook.categories', ['kind' => 'department']) }}"><i class="fa fa-building"></i> Department</a>
            </li>
            <li class="{{ $ebookNonDepartmentActive ? 'active' : '' }}">
              <a href="{{ route('admin.ebook.categories', ['kind' => 'non-department']) }}"><i class="fa fa-folder-open"></i> Non-Department</a>
            </li>
            {{-- E-Book Premium Package Management (v25) --}}
            @if(\Illuminate\Support\Facades\Route::has('admin.ebook-packages.index'))
            <li class="{{ request()->routeIs('admin.ebook-packages.*') ? 'active' : '' }}">
              <a href="{{ route('admin.ebook-packages.index') }}"><i class="fa fa-cubes"></i> Subscription Packages</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
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
        <li>
<a href="{{ route('admin.daily-exam.index') }}">
        <i class="fa fa-file-text"></i>
        <span>Daily Exam</span>
    </a>
</li>
        {{-- LEBD — Daily Update Admin Management --}}
        @php
            $dailyUpdateMenuActive = request()->routeIs('admin.daily-update.*')
                || request()->is('admin/daily-update*');
        @endphp
        <li class="treeview {{ $dailyUpdateMenuActive ? 'active menu-open' : '' }}">
          <a href="#">
            <i class="fa fa-newspaper-o"></i>
            <span>Daily Update</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" style="{{ $dailyUpdateMenuActive ? 'display: block;' : 'display: none;' }}">
            <li class="{{ request()->routeIs('admin.daily-update.index') ? 'active' : '' }}">
              <a href="{{ route('admin.daily-update.index') }}"><i class="fa fa-dashboard"></i> Dashboard</a>
            </li>
            <li class="{{ request()->routeIs('admin.daily-update.all') ? 'active' : '' }}">
              <a href="{{ route('admin.daily-update.all') }}"><i class="fa fa-list"></i> All Updates</a>
            </li>
            <li class="{{ request()->routeIs('admin.daily-update.create') ? 'active' : '' }}">
              <a href="{{ route('admin.daily-update.create') }}"><i class="fa fa-plus-square"></i> Add New Update</a>
            </li>
            <li class="{{ request()->routeIs('admin.daily-update.categories') ? 'active' : '' }}">
              <a href="{{ route('admin.daily-update.categories') }}"><i class="fa fa-folder-open"></i> Categories</a>
            </li>
            <li class="{{ request()->routeIs('admin.daily-update.comments') ? 'active' : '' }}">
              <a href="{{ route('admin.daily-update.comments') }}"><i class="fa fa-comments"></i> Comments</a>
            </li>
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
            <i class="fa fa-book"></i>
            <span>Books</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li>
              <a href="{{route('book-category.index')}}">
                <i class="fa fa-file-text"></i> View Book Categories
              </a>
            </li>
            <li>
              <a href="{{route('book.index')}}">
                <i class="fa fa-book"></i> View Books
              </a>
            </li>
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
            <li><a href="{{ route('order.referrer-order') }}"><i class="fa fa-circle-o"></i> View Referrers</a></li>
          </ul>
        </li>
        {{-- @if(Auth::user()->authorizeRoles(['Teacher']))
        <li class="treeview">
          <a href="#">
            <i class="fa fa-users"></i> <span>Referrels</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('order.index') }}"><i class="fa fa-circle-o"></i> View Referrels</a></li>
          </ul>
        </li>
        @endif --}}
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
      <!-- Withdraw -->
<li class="treeview">
    <a href="#">
        <i class="fa fa-money"></i>
        <span>Withdraw</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
    </a>
    <ul class="treeview-menu">
        <li>
            <a href="{{ route('admin.withdraw.index') }}">
                <i class="fa fa-list"></i>
                Withdraw Requests
            </a>
        </li>
    </ul>
</li>
<!-- Homepage Settings -->
        @php $homeSettingsActive = request()->routeIs('admin.home-success.*'); @endphp
        <li class="treeview {{ $homeSettingsActive ? 'active menu-open' : '' }}">
          <a href="#">
            <i class="fa fa-gear"></i> <span>Homepage Settings</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu" style="{{ $homeSettingsActive ? 'display:block;' : '' }}">
            <li>
              <a href="{{route('ad.index')}}"><i class="fa fa-copy"></i> Ads</a>
            </li>
            <li>
              <a href="{{route('video.index')}}"><i class="fa fa-youtube"></i> Videos</a>
            </li>
            <li>
              <a href="{{route('service.index')}}"><i class="fa fa-copy"></i> Services</a>
            </li>
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
              <a href="/notice-edit"><i class="fa fa-edit"></i> Notice</a>
            </li>
            {{-- LEBD-3 Our Success Management: only visible when v35 routes are registered --}}
            @if(\Illuminate\Support\Facades\Route::has('admin.home-success.index'))
            <li class="{{ request()->routeIs('admin.home-success.*') ? 'active' : '' }}">
              <a href="{{ route('admin.home-success.index') }}"><i class="fa fa-trophy"></i> Our Success / আমাদের সাফল্য</a>
            </li>
            @endif
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
            <li><a href="{{ route('profile') }}"><i class="fa fa-user"></i> User Roles</a></li>
            <li><a href="{{ route('profile') }}"><i class="fa fa-user"></i> Profile</a></li>
            <li><a href="/change_password"><i class="fa fa-lock"></i> Change Password</a></li>
            <li>
              <a href="{{route('database.download')}}" onclick="return confirm('Are you sure, you want to download database file?')">
                <i class="fa fa-database"></i> Download Database
              </a>
            </li>
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
