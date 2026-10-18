<?php

use \App\Http\Controllers\SourceCtrl;

$source = new SourceCtrl;

$user = [];

if (Auth::guard('student')->user()) {
    $user = Auth::guard('student')->user();
}

?>

<header id="Header">

    <nav class="navbar">

        <div class="container">

            <!-- =====================================================
                 ROW 1
                 LOGO + DASHBOARD + NOTIFICATION + USER
            ====================================================== -->

            <div class="header-top-row">

                <!-- LOGO -->

                <div class="main-logo">

                    <a href="{{ route('homepage') }}">

                        <img
                            src="/web/img/logo.png?v=1.1.2"
                            alt="LiveEducationBD"
                            class="logo-img"
                        >

                    </a>

                </div>


                <!-- RIGHT SIDE -->

                <div class="header-actions">

                    <!-- STUDENT DASHBOARD -->

                    @if(!empty($user))

                        <div class="student-dashboard-btn">

                            <a href="{{ route('students.dashboard') }}">

                                <i class="fa-solid fa-gauge-high"></i>

                                <span>Dashboard</span>

                            </a>

                        </div>

                    @endif


                    <!-- NOTIFICATION -->

                    <div class="dropdown notification-area">

                        <a
                            href="javascript:void(0)"
                            class="dropdown-toggle"
                            id="Bell"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >

                            <i class="fa fa-bell"></i>

                        </a>


                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>

                                <a class="dropdown-item" href="#">
                                    কোনো নতুন নোটিফিকেশন নেই
                                </a>

                            </li>

                        </ul>

                    </div>


                    <!-- USER / LOGIN -->

                    @if(empty($user))

                        <div class="login-button">

                            <a
                                href="{{ route('students.login') }}"
                                class="login-btn"
                            >
                                Login
                            </a>

                        </div>

                    @else

                        <div class="dropdown user user-menu">

                            <div
                                class="dropdown-toggle user-toggle"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >

                                <img
                                    src="{{ $user->image ?: '/img/avatar.png' }}"
                                    class="mobile-user-image"
                                    alt="Student"
                                >

                            </div>


                            <ul class="dropdown-menu dropdown-menu-end">

                                <li>

                                    <a
                                        class="dropdown-item"
                                        href="{{ route('students.dashboard') }}"
                                    >
                                        ড্যাশবোর্ড
                                    </a>

                                </li>


                                <li>

                                    <a
                                        class="dropdown-item"
                                        href="{{ route('students.my-course') }}"
                                    >
                                        আমার কোর্স
                                    </a>

                                </li>


                                <li>

                                    <form
                                        action="{{ route('students.logout') }}"
                                        method="post"
                                    >

                                        @csrf

                                        <button
                                            type="submit"
                                            class="dropdown-item"
                                        >
                                            লগআউট
                                        </button>

                                    </form>

                                </li>

                            </ul>

                        </div>

                    @endif

                </div>

            </div>


            <!-- =====================================================
                 ROW 2
                 NAVIGATION
            ====================================================== -->

            <div class="header-menu-row">

                <ul class="navbar-nav">

                    <li class="nav-item">

                        <a
                            class="nav-link active"
                            href="{{ route('homepage') }}"
                        >
                            হোম
                        </a>

                    </li>


                    <li class="nav-item">

                        <a class="nav-button" href="{{ route('homepage.courses') }}">
                        কোর্স
                        </a>


                    </li>


                    <li class="nav-item">

                        <a class="nav-button" href="{{ route('homepage.running.batches') }}">
                        ব্যাচ
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="/#our-success"
                        >
                       পরীক্ষা
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="/#circular"
                        >
                            নিয়োগ
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="/#our-services"
                        >
                            সেবাসমূহ
                        </a>

                    </li>

                </ul>

            </div>

        </div>

    </nav>

</header>


<style>

/* =========================================================
   LIVE EDUCATION BD HEADER
   CLEAN PROFESSIONAL VERSION
========================================================= */


/* =========================================================
   COMMON
========================================================= */

#Header,
#Header * {
    box-sizing: border-box;
}

#Header {
    width: 100%;
    background: #ffffff;
    position: relative;
    z-index: 1000;
    border-bottom: 1px solid #e5e7eb;
}

#Header .navbar {
    width: 100%;
    padding: 0 !important;
    margin: 0 !important;
    background: #ffffff !important;
}


/* =========================================================
   MAIN CONTAINER
========================================================= */

#Header .navbar > .container {
    width: 100%;
    max-width: 1250px;

    margin: 0 auto;
    padding: 0 !important;

    display: block;
}


/* =========================================================
   ROW 1
========================================================= */

#Header .header-top-row {
    width: 100%;
    height: 78px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 8px 0;

    background: #ffffff;
}


/* =========================================================
   LOGO
========================================================= */

#Header .main-logo {
    display: flex;
    align-items: center;

    flex-shrink: 0;
}

#Header .main-logo a {
    display: flex;
    align-items: center;

    text-decoration: none;
}

#Header .logo-img {
    width: 155px;
    height: auto;

    display: block;
}


/* =========================================================
   RIGHT SIDE
   Dashboard + Notification + User
========================================================= */

#Header .header-actions {
    display: flex;
    align-items: center;

    gap: 9px;

    margin-left: auto;
}


/* =========================================================
   DASHBOARD BUTTON
========================================================= */

#Header .student-dashboard-btn {
    display: flex;
    align-items: center;
}

#Header .student-dashboard-btn a {
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: 0 14px;

    color: #ffffff;

    background: linear-gradient(
        180deg,
        #3d91ff 0%,
        #1677ff 55%,
        #0864df 100%
    );

    border: 1px solid #075dcc;

    border-radius: 9px;

    font-size: 13px;
    font-weight: 600;

    text-decoration: none;

    white-space: nowrap;

    box-shadow:
        0 3px 0 #064da8,
        0 5px 10px rgba(22,119,255,.20);

    transition: .15s ease;
}

#Header .student-dashboard-btn a:hover {
    transform: translateY(-1px);

    box-shadow:
        0 4px 0 #064da8,
        0 7px 14px rgba(22,119,255,.25);
}

#Header .student-dashboard-btn a:active {
    transform: translateY(2px);

    box-shadow:
        0 1px 0 #064da8,
        0 2px 5px rgba(22,119,255,.15);
}

#Header .student-dashboard-btn i {
    font-size: 14px;
}


/* =========================================================
   NOTIFICATION
========================================================= */

#Header .notification-area {
    position: relative;
}

#Header #Bell {
    width: 40px;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0;
    margin: 0;

    color: #f5a900;

    background: #fff8e6;

    border-radius: 50%;

    text-decoration: none;

    transition: .2s ease;
}

#Header #Bell:hover {
    background: #fff0c2;
    transform: translateY(-1px);
}

#Header #Bell i {
    font-size: 18px;
}


/* =========================================================
   DROPDOWN
========================================================= */

#Header .dropdown-menu {
    min-width: 180px;

    padding: 6px;

    margin-top: 7px !important;

    border: 1px solid #e5e7eb;

    border-radius: 11px;

    background: #ffffff;

    box-shadow: 0 8px 25px rgba(0,0,0,.12);

    z-index: 99999;
}

#Header .dropdown-item {
    padding: 9px 12px;

    border-radius: 7px;

    font-size: 14px;

    color: #243b53;

    white-space: nowrap;
}

#Header .dropdown-item:hover {
    background: #f1f6ff;
    color: #1677ff;
}


/* =========================================================
   USER PROFILE
========================================================= */

#Header .user-menu {
    width: 46px;
    height: 46px;

    display: flex;
    align-items: center;
    justify-content: center;

    position: relative;
}

#Header .user-toggle {
    width: 46px;
    height: 46px;

    padding: 2px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #f4f7fb;

    cursor: pointer;
}

#Header .mobile-user-image {
    width: 42px;
    height: 42px;

    min-width: 42px;
    min-height: 42px;

    max-width: 42px;
    max-height: 42px;

    display: block;

    object-fit: cover;

    border-radius: 50%;

    border: 2px solid #e5e7eb;
}

#Header .user-menu .dropdown-menu {
    position: absolute;

    top: calc(100% + 5px);

    right: 0;
    left: auto;
}


/* =========================================================
   LOGIN
========================================================= */

#Header .login-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    height: 38px;

    padding: 0 16px;

    background: #16a34a;

    color: #ffffff;

    border-radius: 8px;

    text-decoration: none;

    font-size: 13px;
    font-weight: 600;
}


/* =========================================================
   ROW 2 - NAVIGATION
========================================================= */

#Header .header-menu-row {
    width: 100%;
    height: 52px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #ffffff;

    border-top: 1px solid #f0f2f5;
    border-bottom: 1px solid #e5e7eb;

    overflow-x: auto;
    overflow-y: hidden;

    scrollbar-width: none;
}

#Header .header-menu-row::-webkit-scrollbar {
    display: none;
}


/* NAV LIST */

#Header .header-menu-row .navbar-nav {
    width: max-content;
    height: 52px;

    display: flex;

    flex-direction: row;

    align-items: center;
    justify-content: center;

    gap: 2px;

    margin: 0;
    padding: 0;

    list-style: none;
}


/* =========================================================
   3D PROFESSIONAL NAVIGATION
========================================================= */

#Header .header-menu-row .nav-link {

    height: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0 17px;

    margin: 0 3px;

    color: #183b66;

    background: linear-gradient(
        180deg,
        #ffffff 0%,
        #f4f8ff 55%,
        #e5edf9 100%
    );

    border: 1px solid #d5dfed;

    border-radius: 9px;

    font-size: 15px;
    font-weight: 600;

    line-height: 1;

    text-decoration: none;

    white-space: nowrap;

    /* 3D depth */
    box-shadow:
        0 3px 0 #c4d0df,
        0 5px 9px rgba(30, 70, 110, 0.12);

    transition:
        transform .15s ease,
        box-shadow .15s ease,
        background .15s ease,
        color .15s ease;

}


/* HOVER */

#Header .header-menu-row .nav-link:hover {

    color: #0864df;

    background: linear-gradient(
        180deg,
        #ffffff 0%,
        #edf5ff 50%,
        #dceaff 100%
    );

    transform: translateY(-2px);

    box-shadow:
        0 4px 0 #aebfd4,
        0 7px 13px rgba(22,119,255,.18);

}


/* CLICK / ACTIVE */

#Header .header-menu-row .nav-link:active {

    transform: translateY(2px);

    box-shadow:
        0 1px 0 #b6c3d3,
        0 2px 5px rgba(0,0,0,.10);

}


/* ACTIVE PAGE */

#Header .header-menu-row .nav-link.active {

    color: #ffffff;

    background: linear-gradient(
        180deg,
        #4b9cff 0%,
        #1677ff 55%,
        #075dcc 100%
    );

    border-color: #075dcc;

    box-shadow:
        0 3px 0 #064da8,
        0 5px 10px rgba(22,119,255,.25);

}


/* ACTIVE HOVER */

#Header .header-menu-row .nav-link.active:hover {

    color: #ffffff;

    background: linear-gradient(
        180deg,
        #5ba5ff 0%,
        #1677ff 55%,
        #075dcc 100%
    );

}


/* =========================================================
   NAVIGATION LIST
========================================================= */

#Header .header-menu-row .navbar-nav {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 5px;

    margin: 0;

    padding: 5px 0;

}


/* =========================================================
   NAV ITEM
========================================================= */

#Header .header-menu-row .nav-item {

    height: 42px;

    display: flex;

    align-items: center;

    flex: 0 0 auto;

}


/* =========================================================
   MOBILE 3D NAV
========================================================= */

@media (max-width: 768px) {

    #Header .header-menu-row .navbar-nav {

        gap: 3px;

        padding: 4px 6px;

    }


    #Header .header-menu-row .nav-link {

        height: 34px;

        padding: 0 10px;

        margin: 0 1px;

        border-radius: 7px;

        font-size: 11px;

        font-weight: 600;

        box-shadow:
            0 2px 0 #c4d0df,
            0 3px 6px rgba(30,70,110,.10);

    }


    #Header .header-menu-row .nav-link:hover {

        transform: translateY(-1px);

        box-shadow:
            0 3px 0 #aebfd4,
            0 5px 8px rgba(22,119,255,.15);

    }


    #Header .header-menu-row .nav-link:active {

        transform: translateY(1px);

        box-shadow:
            0 1px 0 #b6c3d3,
            0 2px 4px rgba(0,0,0,.10);

    }

}



/* =========================================================
   DESKTOP
========================================================= */

@media (min-width: 769px) {

    #Header .navbar > .container {
        max-width: 1250px;
    }

    #Header .header-top-row {
        height: 78px;
    }

    #Header .header-menu-row {
        height: 52px;
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 768px) {

    #Header {
        width: 100%;
        overflow: visible;
    }

    #Header .navbar > .container {
        width: 100%;
        max-width: 100%;

        padding: 0;

        margin: 0;
    }


    /* -----------------------------
       MOBILE ROW 1
    ------------------------------ */

    #Header .header-top-row {

        width: 100%;

        height: 60px;
        min-height: 60px;

        padding: 7px 10px;

        display: flex;

        align-items: center;

        justify-content: space-between;
    }


    /* MOBILE LOGO */

    #Header .logo-img {

        width: 120px;
        max-width: 120px;

        height: auto;
    }


    /* MOBILE ACTIONS */

    #Header .header-actions {

        gap: 6px;

        margin-left: auto;
    }


    /* DASHBOARD ICON ONLY */

    #Header .student-dashboard-btn a {

        width: 35px;
        height: 34px;

        padding: 0;

        border-radius: 8px;

        box-shadow:
            0 2px 0 #064da8,
            0 3px 7px rgba(22,119,255,.18);
    }

    #Header .student-dashboard-btn span {
        display: none;
    }

    #Header .student-dashboard-btn i {
        font-size: 14px;
    }


    /* MOBILE BELL */

    #Header #Bell {

        width: 34px;
        height: 34px;

        font-size: 14px;
    }

    #Header #Bell i {
        font-size: 15px;
    }


    /* MOBILE USER */

    #Header .user-menu {

        width: 37px;
        height: 37px;
    }

    #Header .user-toggle {

        width: 37px;
        height: 37px;

        padding: 1px;
    }

    #Header .mobile-user-image {

        width: 35px;
        height: 35px;

        min-width: 35px;
        min-height: 35px;

        max-width: 35px;
        max-height: 35px;

        border-width: 1px;
    }


    /* -----------------------------
       MOBILE NAVIGATION
    ------------------------------ */

    #Header .header-menu-row {

        width: 100%;

        height: 42px;
        min-height: 42px;

        display: flex;

        align-items: center;

        justify-content: flex-start;

        overflow-x: auto;
        overflow-y: hidden;

        scrollbar-width: none;
    }

    #Header .header-menu-row::-webkit-scrollbar {
        display: none;
    }


    #Header .header-menu-row .navbar-nav {

        width: max-content;
        min-width: max-content;

        height: 42px;

        display: flex;

        flex-direction: row;

        align-items: center;

        justify-content: flex-start;

        gap: 0;

        padding: 0 4px;
    }


    #Header .header-menu-row .nav-item {

        height: 42px;

        flex: 0 0 auto;
    }


    #Header .header-menu-row .nav-link {

        height: 42px;

        padding: 0 9px;

        font-size: 12px;

        white-space: nowrap;

        border-bottom: 2px solid transparent;
    }


    /* MOBILE DROPDOWNS */

    #Header .dropdown-menu {

        min-width: 155px;

        padding: 5px;

        margin-top: 5px !important;
    }

    #Header .dropdown-item {

        padding: 8px 10px;

        font-size: 13px;
    }

}


/* =========================================================
   VERY SMALL MOBILE
========================================================= */

@media (max-width: 380px) {

    #Header .logo-img {
        width: 105px;
    }

    #Header .header-actions {
        gap: 4px;
    }

    #Header .header-menu-row .nav-link {
        padding: 0 7px;
        font-size: 11px;
    }

}


</style>