<!-- ================= DASHBOARD MENU ================= -->

<div class="dashboard-menu-row">


    <a href="#" class="active">
        <span>Homepage</span>
    </a>


    <a href="#">
        <span>Course</span>
    </a>


    <a href="#">
        <span>Book</span>
    </a>


    <a href="#">
        <span>MCQ</span>
    </a>


</div>


<!-- ================= LOGOUT ================= -->

<div class="logout-wrapper">

<form action="{{ route('students.logout') }}" method="POST">

    @csrf

    <button type="submit" class="logout-btn">

        <i class="fa-solid fa-right-from-bracket"></i>

        Logout

    </button>

</form>

</div>



<style>

/* ================= Dashboard Menu 3D ================= */


.dashboard-menu-row{

    display:flex;

    gap:8px;

    width:100%;

    padding-left:10px;

    padding-right:10px;

    margin-top:15px;

    margin-bottom:10px;

}



.dashboard-menu-row a{

    flex:1;

    min-height:34px;

    padding:7px 10px;

    border-radius:18px;

    display:flex;

    align-items:center;

    justify-content:center;

    background:linear-gradient(145deg,#f4f8ff,#dfeaff);

    color:#06408c;

    text-decoration:none;

    font-size:11px;

    font-weight:700;


    box-shadow:

    0 4px 8px rgba(0,0,0,.12),

    inset 0 1px 2px rgba(255,255,255,.8);

}



.dashboard-menu-row a.active{

    background:linear-gradient(145deg,#087de5,#005bb8);

    color:white;

}



/* Logout */

.logout-wrapper{

    text-align:right;

    padding:0 10px;

    margin-bottom:15px;

}


.logout-btn{

    border:none;

    background:#ff4757;

    color:white;

    border-radius:18px;

    padding:6px 18px;

    font-size:11px;

    font-weight:700;

    box-shadow:0 4px 8px rgba(255,0,0,.25);

}



.logout-btn i{

    margin-right:5px;

}



/* Mobile */

@media(max-width:768px){


.dashboard-menu-row{

    gap:6px;

}


.dashboard-menu-row a{

    min-height:32px;

    padding:6px 8px;

    font-size:10px;

}



.logout-wrapper{

    margin-bottom:12px;

}


.logout-btn{

    font-size:10px;

    padding:5px 15px;

}


}

</style>