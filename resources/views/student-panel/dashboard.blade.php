@php
use \App\Http\Controllers\SourceCtrl;
$source = new SourceCtrl;
$user = Auth::guard('student')->user();
@endphp
@extends('student')
@section('title', 'Dashboard')
@section('stylesheets')
<link rel="stylesheet" href="{{ asset('web/css/lebd-dashboard-circular-v61.css') }}?v=61">
<style>
/* Main site header is provided by the shared student layout. */
/* ==========================================================
   STUDENT DASHBOARD — CONSISTENT RESPONSIVE LAYOUT
   No browser-zoom hacks: works at Chrome 75%, 100% and 125%.
   ========================================================== */
.lebd-student-dashboard {
    width: 100%;
    margin: 0 auto;
    padding: 12px 12px 46px;
    background: #fff;
    color: #142b4d;
    overflow-x: clip;
}
.lebd-student-dashboard .container,
.lebd-student-dashboard .container-fluid {
    width: 100% !important;
    max-width: 1440px !important;
    padding-left: 12px !important;
    padding-right: 12px !important;
    margin-left: auto !important;
    margin-right: auto !important;
}
.lebd-student-dashboard .exam-section {
    width: calc(100% - 24px);
    max-width: 1416px;
    margin: 24px auto 0;
}
.lebd-student-dashboard .student-profile-card,
.lebd-student-dashboard .dashboard-box,
.lebd-student-dashboard .quick-card,
.lebd-student-dashboard .progress-card,
.lebd-student-dashboard .job-card,
.lebd-student-dashboard .referral-earn-card {
    box-sizing: border-box;
}
.lebd-student-dashboard .student-profile-card {
    min-height: 100px;
    height: auto;
}
.lebd-student-dashboard .profile-left,
.lebd-student-dashboard .profile-info { min-width: 0; }
.lebd-student-dashboard .profile-info h4 {
    margin: 0 0 6px;
    color: #fff;
    font-size: 17px;
    font-weight: 700;
    overflow-wrap: anywhere;
}
.lebd-student-dashboard .student-email {
    overflow-wrap: anywhere;
}
.lebd-student-dashboard .referral-link {
    display: inline-flex;
    text-decoration: none;
    flex-shrink: 0;
}
.lebd-student-dashboard .quick-grid,
.lebd-student-dashboard .exam-grid,
.lebd-student-dashboard .progress-grid,
.lebd-student-dashboard .exam-result-grid {
    min-width: 0;
}
.lebd-student-dashboard .quick-card,
.lebd-student-dashboard .exam-grid a,
.lebd-student-dashboard .progress-card,
.lebd-student-dashboard .dashboard-box { min-width: 0; }
.lebd-student-dashboard .quick-card {
    transition: transform .2s ease, box-shadow .2s ease;
}
.lebd-student-dashboard .quick-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(4,32,78,.12);
}
.lebd-student-dashboard .quick-card .quick-text { min-width: 0; }
.lebd-student-dashboard .exam-item,
.lebd-student-dashboard .result-item {
    min-width: 0;
}
.lebd-student-dashboard .exam-info { min-width: 0; }
.lebd-student-dashboard .job-slider {
    width: 100%;
    min-width: 0;
}
@media (max-width: 991px) {
    .lebd-student-dashboard .quick-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .lebd-student-dashboard .student-profile-card { padding: 14px; }
}
@media (max-width: 768px) {
    .lebd-student-dashboard { padding: 8px 0 32px; }
    .lebd-student-dashboard .container { padding-inline: 10px !important; }
    .lebd-student-dashboard .exam-section {
        width: calc(100% - 20px);
        margin-top: 16px;
    }
    .lebd-student-dashboard .student-profile-card { gap: 10px; }
    .lebd-student-dashboard .profile-info h4 { font-size: 14px; }
    .lebd-student-dashboard .quick-grid {
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 9px;
    }
    .lebd-student-dashboard .quick-card { min-width: 0; }
    .lebd-student-dashboard .exam-grid {
        grid-template-columns: repeat(2, minmax(0,1fr));
    }
    .lebd-student-dashboard .exam-result-grid {
        grid-template-columns: minmax(0,1fr);
    }
    .lebd-student-dashboard .referral-earn-card { gap: 10px; }
}
@media (max-width: 380px) {
    .lebd-student-dashboard .quick-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .lebd-student-dashboard .referral-card { width: 104px; }
}
</style>
@endsection
@section('content')
{{-- Header is rendered once by resources/views/student.blade.php. --}}
<div class="lebd-student-dashboard" id="lebd-student-dashboard">
<!-- ================= STUDENT PROFILE CARD ================= -->
<section class="student-dashboard">
<div class="container">
<div class="student-profile-card">
    <!-- PROFILE LEFT -->
    <div class="profile-left">
        <div class="profile-avatar">
            <i class="fa fa-user"></i>
        </div>
        <div class="profile-info">
            <h4>
                স্বাগতম {{ $user->name }}
            </h4>
             <p>
                Your ID:
             <span>
              {{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}
             </span>
            </p>
            <p class="student-email">
        {{ $user->email }}
          </p>
        </div>
    </div>
   <!-- Referral Earn Card code-->
<a href="{{ route('students.referral') }}" class="referral-link">
    <div class="referral-card">
        <div class="referral-content">
            <h4>
                Available Balance
            </h4>
            @php
                // Match StudentReferralController::index() exactly:
                // Available Balance = student's wallet balance - pending withdrawal requests.
                // This is not gross referral earnings and must not include pending withdrawals.
                $lebdPendingWithdraw = \App\Models\WithdrawRequest::query()
                    ->where('student_id', $user->id)
                    ->where('status', 'pending')
                    ->sum('amount');
                $lebdAvailableBalance = (float) ($user->referral_balance ?? 0) - (float) $lebdPendingWithdraw;
            @endphp
            <h2>৳ {{ number_format($lebdAvailableBalance, 2) }}</h2>
            <span class="withdraw-btn">
                Withdraw →
            </span>
        </div>
    </div>
</a>
</div> {{-- /.student-profile-card --}}
</div> {{-- /.container --}}
</section>
<style>
/* ================= PROFILE CARD ================= */
.student-profile-card{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:linear-gradient(120deg,#0875e1,#00b37e);
    border-radius:22px;
    padding:15px 20px;
    height:100px;
    width:100%;
}
/* LEFT PROFILE */
.profile-left{
    display:flex;
    align-items:center;
    gap:15px;
}
.profile-avatar{
    width:65px;
    height:65px;
    background:white;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}
.profile-avatar i{
    font-size:42px;
    color:#0875e1;
}
.profile-info h3{
    color:white;
    font-size:16px;
    font-weight:700;
    margin:0 0 5px 0;
}
.profile-info p{
    color:white;
    font-size:12px;
    margin:3px 0;
}
.profile-info span{
    background:#00c878;
    padding:3px 10px;
    border-radius:20px;
}
.profile-info small{
    color:white;
    font-size:10px;
}
/* ================= REFERRAL CARD ================= */
.referral-card{
    background:white;
    border-radius:20px;
    height:80px;
    width:260px;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:10px;
}
.referral-content{
    text-align:center;
}
.referral-content h4{
    margin:0;
    font-size:15px;
    color:#12345b;
}
.referral-content h2{
    margin:3px 0;
    font-size:24px;
    color:#00a878;
}
.withdraw-btn{
    background:#0875d1;
    color:white;
    padding:5px 18px;
    border-radius:20px;
    font-size:12px;
    text-decoration:none;
}
/* ================= PROFILE & REFERRAL ALIGN FIX ================= */
.student-profile-card{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}
/* Left side fixed */
.profile-left{
    flex:1;
    min-width:0;
    display:flex;
    align-items:center;
    gap:10px;
}
.profile-info{
    overflow:hidden;
}
.profile-info h3{
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
/* Referral small box */
.referral-card{
    width:120px;
    height:65px;
    flex-shrink:0;
    padding:6px;
    border-radius:16px;
    display:flex;
    justify-content:center;
    align-items:center;
}
/* Referral text */
.referral-content{
    width:100%;
    text-align:center;
}
.referral-content h4{
    font-size:10px;
    margin:0;
    white-space:nowrap;
}
.referral-content h2{
    font-size:10px;
    margin:1px 0;
}
/* Withdraw right side */
.withdraw-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    margin-left:auto;
    padding:3px 10px;
    font-size:9px;
    white-space:nowrap;
}
/* Mobile */
@media(max-width:768px){
.student-profile-card{
    padding:10px;
}
.profile-avatar{
    width:52px;
    height:52px;
}
.profile-avatar i{
    font-size:28px;
}
.profile-info h3{
    font-size:14px;
}
.referral-card{
    width:115px;
    height:60px;
}
}
/* ================= TODAY EXAM CARD ================= */
.today-exam-card{
background:#eef8ff;
border-radius:18px;
padding:15px;
display:flex;
align-items:center;
justify-content:space-between;
gap:15px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}
/* LEFT */
.exam-left{
display:flex;
align-items:center;
gap:15px;
}
.exam-icon{
width:65px;
height:65px;
background:white;
border-radius:15px;
display:flex;
align-items:center;
justify-content:center;
}
.exam-icon i{
font-size:35px;
color:#0875e1;
}
.exam-details h3{
margin:0;
font-size:22px;
font-weight:700;
color:#17345d;
}
.exam-details h5{
margin:5px 0;
font-size:18px;
}
.exam-details p{
margin:0;
font-size:14px;
color:#777;
}
/* TIMER */
.exam-timer{
display:flex;
gap:8px;
}
.time-box{
background:white;
width:55px;
height:65px;
border-radius:10px;
display:flex;
flex-direction:column;
justify-content:center;
align-items:center;
box-shadow:0 3px 8px rgba(0,0,0,.08);
}
.time-box strong{
font-size:22px;
color:#0875e1;
}
.time-box span{
font-size:11px;
color:#777;
}
/* BUTTON */
.start-exam-btn{
background:#00ad78;
color:white;
padding:12px 22px;
border-radius:12px;
font-weight:700;
text-decoration:none;
white-space:nowrap;
}
.start-exam-btn i{
margin-right:5px;
}
/* ================= MOBILE ================= */
@media(max-width:768px){
.today-exam-card{
padding:12px;
gap:8px;
}
.exam-left{
gap:8px;
}
.exam-icon{
width:45px;
height:45px;
}
.exam-icon i{
font-size:22px;
}
.exam-details h3{
font-size:16px;
}
.exam-details h5{
font-size:14px;
margin:2px 0;
}
.exam-details p{
font-size:10px;
}
.exam-timer{
gap:5px;
}
.time-box{
width:38px;
height:45px;
}
.time-box strong{
font-size:14px;
}
.time-box span{
font-size:8px;
}
.start-exam-btn{
font-size:11px;
padding:8px 12px;
}
}
/* QUICK ACCESS */
.quick-grid{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:18px;
}
.quick-card{
background:#fff;
padding:18px;
border-radius:18px;
display:flex;
align-items:center;
gap:12px;
text-decoration:none;
box-shadow:0 5px 15px rgba(0,0,0,.08);
color:#222;
}
.quick-icon{
width:45px;
height:45px;
background:#eaf2ff;
border-radius:12px;
display:flex;
align-items:center;
justify-content:center;
}
.quick-icon i{
font-size:22px;
color:#0866d5;
}
.quick-text span{
display:block;
font-size:16px;
font-weight:700;
}
.quick-text small{
font-size:12px;
color:#777;
}
@media(max-width:768px){
.quick-grid{
grid-template-columns:repeat(3,1fr);
gap:8px;
}
.quick-card{
padding:10px;
display:flex;
flex-direction:column;
height:60px;
text-align:center;
}
.quick-icon{
width:35px;
height:20px;
}
.quick-icon i{
font-size:16px;
}
.quick-text span{
font-size:9px;
}
.quick-text small{
display:none;
}
}
/* ================= EXAM SECTION ================= */
.exam-section{
    margin-top:25px;
    background:#fff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 3px 10px rgba(0,0,0,.10);
}
.exam-section-title{
    background:#4b4bf5;
    color:white;
    text-align:center;
    font-size:20px;
    font-weight:700;
    padding:8px;
}
.exam-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:8px;
    padding:10px;
}
.exam-grid a{
    position:relative;
    background:white;
    border-radius:12px;
    padding:12px 8px;
    text-decoration:none;
    color:#333;
    font-size:14px;
    box-shadow:0 2px 8px rgba(0,0,0,.12);
    display:flex;
    align-items:center;
}
.exam-grid a::after{
    content:"";
    width:8px;
    height:8px;
    background:#ff1744;
    border-radius:50%;
    position:absolute;
    right:10px;
    top:5px;
}
/* Mobile */
@media(max-width:768px){
.exam-section{
    margin-top:18px;
}
.exam-section-title{
    font-size:16px;
}
.exam-grid a{
    font-size:11px;
    padding:10px 6px;
}
}
/* ===== Learning Progress Position Fix ===== */
.progress-grid{
    display:grid !important;
    grid-template-columns:repeat(2,1fr) !important;
    gap:8px !important;
}
.progress-card{
    display:flex !important;
    flex-direction:row !important;
    align-items:center !important;
    justify-content:flex-start !important;
    height:75px !important;
    padding:8px !important;
}
.progress-circle{
    flex-shrink:0 !important;
    margin-right:8px !important;
}
.progress-info{
    display:flex !important;
    flex-direction:column !important;
    justify-content:center !important;
    margin-left:0 !important;
}
.progress-info h4{
    margin:0 !important;
    font-size:12px !important;
}
.progress-info p{
    margin:2px 0 !important;
    font-size:8px !important;
}
.section-title{
    display:flex !important;
    justify-content:space-between !important;
    align-items:center !important;
}
.section-title h3{
    font-size:16px !important;
    margin:0 !important;
}
.section-title a{
    font-size:11px !important;
}
/* ===== Progress Card Design Fix ===== */
.progress-card{
    background:#ffffff !important;
    border-radius:15px !important;
    height:75px !important;
    padding:10px !important;
    display:flex !important;
    align-items:center !important;
    box-shadow:0 3px 12px rgba(0,0,0,0.08) !important;
    border:1px solid #f1f1f1;
}
/* Circle */
.progress-circle{
    width:38px !important;
    height:38px !important;
    min-width:38px !important;
    border-radius:50% !important;
}
/* Text */
.progress-info{
    margin-left:10px !important;
}
.progress-info h4{
    font-size:13px !important;
    font-weight:700 !important;
    color:#222;
}
.progress-info p{
    font-size:8px !important;
    color:#777;
}
/* Mobile */
@media(max-width:768px){
.progress-card{
    height:70px !important;
    border-radius:14px !important;
    padding:8px !important;
}
.progress-circle{
    width:32px !important;
    height:32px !important;
    min-width:32px !important;
}
.section-title h3{
    font-size:15px !important;
}
.section-title a{
    font-size:10px;
}
.progress-circle{
    width:32px !important;
    height:32px !important;
    min-width:32px !important;
}
}
/* ================= EXAM RESULT ================= */
.exam-result-grid{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:18px;
}
.dashboard-box{
background:white;
border-radius:18px;
padding:18px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}
.box-title{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:15px;
}
.box-title h3{
font-size:20px;
margin:0;
}
.box-title a{
color:#0875e1;
text-decoration:none;
}
.exam-item{
display:flex;
gap:15px;
padding:12px 0;
border-bottom:1px solid #eee;
}
.date-box{
width:55px;
height:65px;
background:#eaf2ff;
border-radius:12px;
text-align:center;
padding-top:8px;
}
.date-box strong{
display:block;
font-size:22px;
color:#0875e1;
}
.date-box span{
font-size:12px;
}
.exam-info h4{
margin:0;
font-size:15px;
}
.exam-info p{
font-size:12px;
margin:5px 0;
}
.exam-info small{
color:#777;
}
.result-item{
position:relative;
padding:12px 0;
border-bottom:1px solid #eee;
}
.result-item h4{
font-size:15px;
margin:0;
}
.result-item p{
font-size:12px;
}
.result-item small{
color:#777;
}
.grade{
position:absolute;
right:10px;
top:20px;
padding:6px 10px;
border-radius:50%;
color:white;
font-weight:bold;
}
.green{
background:#00a878;
}
.blue{
background:#0875e1;
}
.orange{
background:#ff9800;
}
@media(max-width:768px){
.exam-result-grid{
grid-template-columns:1fr;
gap:10px;
}
.dashboard-box{
padding:12px;
}
.box-title h3{
font-size:15px;
}
.exam-item{
gap:8px;
}
.exam-info h4{
font-size:12px;
}
.result-item h4{
font-size:12px;
}
}
/* ================= JOB SLIDER ================= */
.job-slider{
display:flex;
gap:15px;
overflow:hidden;
}
.job-card{
min-width:280px;
background:white;
border-radius:18px;
padding:15px;
display:flex;
align-items:center;
gap:15px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
animation:slide 12s infinite;
}
.job-icon{
width:50px;
height:50px;
background:#eaf2ff;
border-radius:15px;
display:flex;
align-items:center;
justify-content:center;
}
.job-icon i{
font-size:25px;
color:#0875e1;
}
.job-card h4{
margin:0;
font-size:16px;
}
.job-card p{
margin:5px 0;
font-size:13px;
color:#666;
}
.job-card span{
font-size:12px;
color:#00a878;
font-weight:bold;
}
@keyframes slide{
0%{
transform:translateX(0);
}
33%{
transform:translateX(-20px);
}
66%{
transform:translateX(-40px);
}
100%{
transform:translateX(0);
}
}
@media(max-width:768px){
.job-slider{
overflow-x:auto;
}
.job-card{
min-width:220px;
padding:10px;
}
.job-card h4{
font-size:13px;
}
.job-card p{
font-size:11px;
}
}
/* JOB DETAILS BUTTON */
.job-details-btn{
display:inline-flex;
align-items:center;
gap:5px;
margin-top:10px;
background:#0875e1;
color:white;
padding:6px 14px;
border-radius:20px;
font-size:12px;
text-decoration:none;
font-weight:600;
}
.job-details-btn:hover{
background:#0056b3;
color:white;
}
@media(max-width:768px){
.job-details-btn{
font-size:10px;
padding:5px 10px;
}
}
/* ================= REFERRAL EARN ================= */
.referral-earn-card{
background:linear-gradient(120deg,#0875e1,#00b37e);
border-radius:22px;
padding:20px 30px;
display:flex;
justify-content:space-between;
align-items:center;
color:white;
}
.referral-left{
display:flex;
align-items:center;
gap:15px;
}
.referral-wallet{
width:65px;
height:65px;
background:white;
border-radius:18px;
display:flex;
align-items:center;
justify-content:center;
}
.referral-wallet i{
font-size:32px;
color:#0875e1;
}
.referral-left h3{
margin:0;
font-size:22px;
}
.referral-left p{
margin:5px 0 0;
font-size:14px;
}
.referral-right{
text-align:center;
background:white;
padding:15px 30px;
border-radius:20px;
}
.referral-right h2{
margin:0 0 10px;
color:#00a878;
font-size:35px;
}
.referral-right .withdraw-btn{
background:#0875e1;
color:white;
padding:8px 25px;
border-radius:25px;
text-decoration:none;
}
@media(max-width:768px){
.referral-earn-card{
padding:12px;
border-radius:18px;
}
.referral-wallet{
width:45px;
height:45px;
}
.referral-wallet i{
font-size:22px;
}
.referral-left h3{
font-size:14px;
}
.referral-left p{
font-size:10px;
}
.referral-right{
padding:8px 12px;
}
.referral-right h2{
font-size:22px;
}
.referral-right .withdraw-btn{
padding:5px 12px;
font-size:10px;
}
}
/* ===== MOBILE OVERFLOW FINAL FIX — only dashboard ===== */
@media(max-width:768px){
.lebd-student-dashboard *{
    max-width:100%;
}
.lebd-student-dashboard{
    width:100%;
    overflow-x:hidden !important;
}
.lebd-student-dashboard .container,
.lebd-student-dashboard .container-fluid{
    width:100% !important;
    max-width:100% !important;
    padding-left:8px !important;
    padding-right:8px !important;
}
.lebd-student-dashboard section,
.lebd-student-dashboard div{
    max-width:100%;
}
/* Any fixed width remove */
.quick-card,
.progress-card,
.subject-card,
.exam-card,
.result-card{
    max-width:100% !important;
}
/* Referral */
.referral-card{
    overflow:hidden;
}
}
</style>
<!-- ========= LEBD PREMIUM DASHBOARD SECTIONS: SCOPED ========= -->
<style>
.lebd-student-dashboard {
    background:linear-gradient(180deg,#f1f6fd 0%,#f8fbff 46%,#f5f8fd 100%);
    min-height:calc(100vh - 80px);
    padding-block:22px 55px;
    overflow-x:hidden;
}
.lebd-student-dashboard .container,
.lebd-student-dashboard .container-fluid {
    max-width:1240px!important;
    width:calc(100% - 32px)!important;
    margin-inline:auto!important;
    padding-inline:16px!important;
}
.lebd-student-dashboard .student-dashboard .container {padding-inline:0!important;}
.lebd-student-dashboard .student-profile-card {
    min-height:125px;
    border-radius:22px;
    background:linear-gradient(111deg,#07377a 0%,#066cd0 56%,#0ab896 100%);
    padding:20px 26px;
    box-shadow:0 15px 32px rgba(10,46,105,.17);
}
.lebd-student-dashboard .lebd-section-panel {
    background:#fff;
    border:1px solid #dce8f7;
    border-radius:22px;
    box-shadow:0 10px 28px rgba(23,60,111,.065);
    padding:20px;
    margin-top:22px!important;
    overflow:hidden;
}
.lebd-student-dashboard .lebd-section-head {
    display:flex;
    align-items:center;
    gap:12px;
    padding:0 2px 15px;
    margin-bottom:15px;
    border-bottom:1px solid #e4edf8;
}
.lebd-student-dashboard .lebd-head-icon {
    width:45px;
    height:45px;
    flex:0 0 45px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:14px;
    color:#fff;
    background:linear-gradient(145deg,#0d8bff,#064bb8);
    font-size:21px;
}
.lebd-student-dashboard .lebd-head-text { min-width:0; flex:1; }
.lebd-student-dashboard .lebd-head-text h3 {
    font-size:20px;
    line-height:1.35;
    font-weight:800;
    color:#0b2b62;
    margin:0 0 3px;
}
.lebd-student-dashboard .lebd-head-text p {
    color:#6882a4;
    font-size:12px;
    line-height:1.4;
    margin:0;
}
.lebd-student-dashboard .lebd-head-tag {
    color:#1757a4;
    font-size:10px;
    font-weight:800;
    background:#e7f2ff;
    border:1px solid #cfe3fe;
    border-radius:30px;
    padding:6px 11px;
    white-space:nowrap;
}
.lebd-student-dashboard .lebd-quick-panel {
    background:linear-gradient(150deg,#fff,#f5f9ff);
}
.lebd-student-dashboard .lebd-quick-panel .quick-grid {
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:13px;
}
.lebd-student-dashboard .lebd-quick-panel .quick-card {
    border:1px solid #e2ecf9;
    border-radius:14px;
    box-shadow:0 3px 11px rgba(10,52,110,.045);
    height:auto;
    min-height:75px;
    padding:13px;
}
.lebd-student-dashboard .lebd-quick-panel .quick-icon {
    border-radius:11px;
    background:#eaf3ff;
    flex-shrink:0;
}
.lebd-student-dashboard .lebd-exam-panel {
    width:calc(100% - 32px)!important;
    max-width:1240px!important;
    background:linear-gradient(160deg,#0b2456,#14285f);
    padding:20px;
    box-shadow:0 17px 30px rgba(5,26,75,.13);
}
.lebd-student-dashboard .lebd-exam-panel .lebd-section-head {
    border-color:rgba(255,255,255,.15);
}
.lebd-student-dashboard .lebd-exam-panel .lebd-head-icon {
    background:#334fed;
}
.lebd-student-dashboard .lebd-exam-panel .lebd-head-text h3 { color:#fff; }
.lebd-student-dashboard .lebd-exam-panel .lebd-head-text p { color:#b4c8ee; }
.lebd-student-dashboard .lebd-exam-panel .lebd-head-tag {background:#2d4077;color:#e6efff;border-color:#576995;}
.lebd-student-dashboard .lebd-exam-panel .exam-grid {
    padding:0;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:11px;
}
.lebd-student-dashboard .lebd-exam-panel .exam-grid a {
    color:#18375f;
    border:1px solid #e7effb;
    box-shadow:none;
    border-radius:11px;
    padding:13px 15px;
    font-size:13px;
    font-weight:700;
}
.lebd-student-dashboard .lebd-progress-panel {
    background:linear-gradient(155deg,#f7fdff,#fff 55%);
}
.lebd-student-dashboard .lebd-progress-panel .section-title {
    padding:0 3px 15px;
    margin:0 0 14px;
    border-bottom:1px solid #e3edf7;
}
.lebd-student-dashboard .lebd-progress-panel .section-title h3 {
    color:#11376d;
    font-weight:800;
    font-size:19px!important;
}
.lebd-student-dashboard .lebd-progress-panel .section-title h3 i {color:#0c85e9;margin-right:7px;}
.lebd-student-dashboard .lebd-progress-panel .progress-grid {
    display:grid!important;
    grid-template-columns:repeat(3,minmax(0,1fr))!important;
    gap:12px!important;
}
.lebd-student-dashboard .lebd-progress-panel .progress-card {
    min-height:77px;
    height:auto!important;
    border:1px solid #e7eff8;
    box-shadow:0 3px 11px rgba(17,65,117,.045)!important;
}
.lebd-student-dashboard .lebd-results-panel { background:linear-gradient(155deg,#fff,#f9fcff); }
.lebd-student-dashboard .lebd-results-panel .exam-result-grid {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr))!important;
    gap:15px;
}
.lebd-student-dashboard .lebd-results-panel .dashboard-box {
    border-radius:15px;
    border:1px solid #e4ecfa;
    box-shadow:0 3px 12px rgba(10,49,101,.05);
    padding:17px;
    height:100%;
}
.lebd-student-dashboard .lebd-results-panel .box-title {
    border-bottom:1px solid #e7eef9;
    padding:0 0 13px;
    margin-bottom:8px;
    gap:6px;
}
.lebd-student-dashboard .lebd-results-panel .box-title h3 {
    font-size:17px;
    font-weight:800;
    color:#0d3971;
}
.lebd-student-dashboard .lebd-results-panel .box-title a {
    white-space:nowrap;
    font-size:12px;
}
.lebd-student-dashboard .lebd-job-panel .section-title {
    margin-bottom:14px;
    padding-bottom:14px;
    border-bottom:1px solid #e4ecf8;
}
.lebd-student-dashboard .lebd-job-panel .section-title h3 {
    color:#0a3772;
    font-size:19px!important;
    font-weight:800;
}
.lebd-student-dashboard .lebd-job-panel .job-slider {padding-block:3px 8px;}
.lebd-student-dashboard .lebd-job-panel .job-card {border:1px solid #e1eaf6;box-shadow:0 5px 14px rgba(20,57,103,.07);}
.lebd-student-dashboard .lebd-daily-panel {
    background:linear-gradient(155deg,#092554,#0c3675);
    border-color:#143f84;
}
.lebd-student-dashboard .lebd-daily-panel .lebd-section-head {border-color:rgba(255,255,255,.19);}
.lebd-student-dashboard .lebd-daily-panel .lebd-head-text h3 {color:#fff;}
.lebd-student-dashboard .lebd-daily-panel .lebd-head-text p {color:#c2d6ef;}
.lebd-student-dashboard .lebd-daily-panel .lebd-head-icon {background:linear-gradient(145deg,#00ab93,#0088c5);}
.lebd-student-dashboard .lebd-daily-grid {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:13px;
}
.lebd-student-dashboard .lebd-daily-card {
    background:#fff;
    border:1px solid #dfeafb;
    border-radius:16px;
    padding:15px 16px;
    min-width:0;
}
.lebd-student-dashboard .lebd-daily-top {display:flex;justify-content:space-between;align-items:center;gap:8px;}
.lebd-student-dashboard .lebd-daily-date {color:#3879b6;font-size:12px;font-weight:700;overflow-wrap:anywhere;}
.lebd-student-dashboard .lebd-daily-status {padding:5px 9px;border-radius:40px;background:#e5f1ff;color:#1761ad;font-weight:800;font-size:11px;white-space:nowrap;}
.lebd-student-dashboard .lebd-daily-card[data-state="live"] .lebd-daily-status {color:#057949;background:#d9f9e8;}
.lebd-student-dashboard .lebd-daily-card[data-state="ended"] .lebd-daily-status {color:#66748a;background:#e9eef6;}
.lebd-student-dashboard .lebd-daily-card h4 {font-weight:800;color:#102e58;font-size:17px;margin:10px 0 7px;}
.lebd-student-dashboard .lebd-daily-meta {font-size:12px;color:#6f829b;line-height:1.55;margin:0 0 14px;}
.lebd-student-dashboard .lebd-daily-meta span {margin-left:5px;}
.lebd-student-dashboard .lebd-daily-bottom {display:flex;gap:12px;align-items:center;justify-content:space-between;border-top:1px solid #eaf0f7;padding-top:12px;}
.lebd-student-dashboard .lebd-daily-countdown small {display:block;font-size:10px;color:#7a8ca6;}
.lebd-student-dashboard .lebd-daily-countdown strong {font-size:19px;color:#0755b1;font-variant-numeric:tabular-nums;letter-spacing:.5px;}
.lebd-student-dashboard .lebd-daily-link {
    background:#0b72e4;color:white;text-decoration:none;border-radius:10px;
    padding:10px 12px;white-space:nowrap;font-size:12px;font-weight:800;
}
.lebd-student-dashboard .lebd-daily-pending {color:#7086a1;font-size:11px;}
.lebd-student-dashboard .lebd-daily-empty {display:flex;align-items:center;gap:16px;padding:22px;border:1px dashed #809cc7;border-radius:15px;background:rgba(255,255,255,.08);}
.lebd-student-dashboard .lebd-daily-empty i {font-size:25px;color:#d2e6ff;}
.lebd-student-dashboard .lebd-daily-empty strong {color:#fff;font-size:14px;}
.lebd-student-dashboard .lebd-daily-empty p {color:#c4d8f0;font-size:12px;margin:4px 0 0;}
@media(max-width:991px){
    .lebd-student-dashboard .lebd-quick-panel .quick-grid {grid-template-columns:repeat(3,minmax(0,1fr));}
    .lebd-student-dashboard .lebd-progress-panel .progress-grid {grid-template-columns:repeat(2,minmax(0,1fr))!important;}
    .lebd-student-dashboard .lebd-exam-panel .exam-grid {grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media(max-width:768px){
    .lebd-student-dashboard {padding-block:12px 34px;}
    .lebd-student-dashboard .container,
    .lebd-student-dashboard .container-fluid {
        width:calc(100% - 18px)!important;
        padding-inline:11px!important;
    }
    .lebd-student-dashboard .lebd-section-panel {
        border-radius:17px;padding:12px;margin-top:12px!important;
    }
    .lebd-student-dashboard .student-profile-card {border-radius:16px;padding:12px;min-height:100px;}
    .lebd-student-dashboard .lebd-section-head {gap:8px;padding-bottom:10px;margin-bottom:10px;}
    .lebd-student-dashboard .lebd-head-icon {width:35px;height:35px;flex-basis:35px;font-size:15px;border-radius:10px;}
    .lebd-student-dashboard .lebd-head-text h3 {font-size:14px;}
    .lebd-student-dashboard .lebd-head-text p {font-size:9px;}
    .lebd-student-dashboard .lebd-head-tag {font-size:8px;padding:4px 6px;}
    .lebd-student-dashboard .lebd-quick-panel .quick-grid {grid-template-columns:repeat(3,minmax(0,1fr));gap:7px;}
    .lebd-student-dashboard .lebd-quick-panel .quick-card {padding:9px 5px;min-height:67px;gap:5px;border-radius:11px;flex-direction:column;justify-content:center;}
    .lebd-student-dashboard .lebd-quick-panel .quick-icon {width:29px;height:25px;}
    .lebd-student-dashboard .lebd-quick-panel .quick-icon i {font-size:15px;}
    .lebd-student-dashboard .lebd-quick-panel .quick-text span {font-size:10px;line-height:1.18;}
    .lebd-student-dashboard .lebd-exam-panel {width:calc(100% - 18px)!important;}
    .lebd-student-dashboard .lebd-exam-panel .exam-grid {grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;}
    .lebd-student-dashboard .lebd-exam-panel .exam-grid a {padding:10px 8px;font-size:10px;overflow-wrap:anywhere;}
    .lebd-student-dashboard .lebd-progress-panel .progress-grid {grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:8px!important;}
    /* Important: upcoming and recent result stay side by side on phone. */
    .lebd-student-dashboard .lebd-results-panel .exam-result-grid {grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:8px;}
    .lebd-student-dashboard .lebd-results-panel .dashboard-box {padding:9px;border-radius:12px;}
    .lebd-student-dashboard .lebd-results-panel .box-title {align-items:flex-start;gap:4px;flex-wrap:wrap;}
    .lebd-student-dashboard .lebd-results-panel .box-title h3 {font-size:11px;line-height:1.4;}
    .lebd-student-dashboard .lebd-results-panel .box-title a {font-size:9px;}
    .lebd-student-dashboard .lebd-results-panel .exam-item {gap:5px;padding:10px 0;align-items:flex-start;}
    .lebd-student-dashboard .lebd-results-panel .date-box {width:30px;min-width:30px;height:45px;border-radius:7px;padding:5px 0;}
    .lebd-student-dashboard .lebd-results-panel .date-box strong {font-size:12px;}
    .lebd-student-dashboard .lebd-results-panel .date-box span {font-size:9px;}
    .lebd-student-dashboard .lebd-results-panel .exam-info h4,
    .lebd-student-dashboard .lebd-results-panel .result-item h4 {font-size:10px;line-height:1.4;overflow-wrap:anywhere;}
    .lebd-student-dashboard .lebd-results-panel .exam-info p,
    .lebd-student-dashboard .lebd-results-panel .result-item p {font-size:9px;line-height:1.4;overflow-wrap:anywhere;}
    .lebd-student-dashboard .lebd-results-panel .exam-info small,
    .lebd-student-dashboard .lebd-results-panel .result-item small {font-size:9px;}
    .lebd-student-dashboard .lebd-results-panel .grade {position:static;display:inline-flex;align-items:center;justify-content:center;margin:3px 0 6px;border-radius:40px;font-size:10px;padding:4px 6px;}
    .lebd-student-dashboard .lebd-results-panel .result-item {padding:9px 0;}
    .lebd-student-dashboard .lebd-daily-grid {grid-template-columns:1fr;gap:9px;}
    .lebd-student-dashboard .lebd-daily-card {padding:12px;}
    .lebd-student-dashboard .lebd-daily-card h4 {font-size:14px;}
    .lebd-student-dashboard .lebd-daily-countdown strong {font-size:16px;}
}
@media(max-width:370px){
    .lebd-student-dashboard .lebd-results-panel .dashboard-box {padding:7px;}
    .lebd-student-dashboard .lebd-results-panel .date-box {width:27px;min-width:27px;}
    .lebd-student-dashboard .lebd-results-panel .box-title h3 {font-size:10px;}
}
</style>
<!-- ================= QUICK ACCESS ================= -->
<div class="container mt-4 lebd-section-panel lebd-quick-panel">
    <div class="lebd-section-head">
        <span class="lebd-head-icon"><i class="fa fa-compass"></i></span>
        <div class="lebd-head-text">
            <h3>আমার কেনা কোর্স সমূহ</h3>
            <p>আপনে যে সকল , ব্যাচ, ভিডিও ক্লাস, ই-বুক , MCS স্টাডি, Exam Section, কিনেছে সেগুলো সকল কিছু এখানে পাবেন।</p>
        </div>
    </div>
<div class="quick-grid">
<a href="{{ route('students.referral') }}" class="quick-card">
<div class="quick-icon">
<i class="fa fa-book"></i>
</div>
<div class="quick-text">
<span>All Referral link</span>
<small></small>
</div>
</a>
<a href="{{ route('students.my-batch.exam-index') }}" class="quick-card">
<div class="quick-icon">
<i class="fa fa-file"></i>
</div>
<div class="quick-text">
<span>My Batch</span>
<small></small>
</div>
</a>
<a href="{{route('category.result.option')}}" class="quick-card">
<div class="quick-icon">
<i class="fa fa-chart-line"></i>
</div>
<div class="quick-text">
<span>Exam Results</span>
<small></small>
</div>
</a>
<a href="{{route('category.batches',['Exam PDF','Dashboard'])}}" class="quick-card">
<div class="quick-icon">
<i class="fa fa-file-pdf"></i>
</div>
<div class="quick-text">
<span>Exam PDF</span>
<small></small>
</div>
</a>
<a href="{{ route('students.premium.video.classes') }}" class="quick-card">
<div class="quick-icon">
<i class="fa fa-video"></i>
</div>
<div class="quick-text">
<span>Video Class</span>
<small></small>
</div>
</a>
<a href="{{ url('/mcq-study/my-purchased') }}" class="quick-card">
<div class="quick-icon">
<i class="fa fa-clipboard-question"></i>
</div>
<div class="quick-text">
<span>MCQ Study</span>
<small></small>
</div>
</a>
<a href="{{route('category.batches',['Exam Routine','Dashboard'])}}" class="quick-card">
<div class="quick-icon">
<i class="fa fa-calendar"></i>
</div>
<div class="quick-text">
<span>Exam Section</span>
<small></small>
</div>
</a>
<a href="{{ \Illuminate\Support\Facades\Route::has('student.ebook.dashboard') ? route('student.ebook.dashboard') : route('student.ebook.categories', ['kind'=>'department']) }}" class="quick-card">
<div class="quick-icon">
<i class="fa fa-book-open"></i>
</div>
<div class="quick-text">
<span>E-Book</span>
<small></small>
</div>
</a>
<a href="#" class="quick-card">
<div class="quick-icon">
<i class="fa fa-pen-to-square"></i>
</div>
<div class="quick-text">
<span>Free Exam</span>
<small></small>
</div>
</a>
</div>
</div>
<!-- ================= EXAM SECTION: CATEGORY / SUB-CATEGORY ================= -->
{{-- LEBD Fix211: Keep the Fix210 Available Balance, restore the linked category cards. --}}
@include('student.partials.exam-section-category-cards')
<!-- ================= LEARNING PROGRESS — LEBD Fix240 ================= -->
@include('student.partials.lebd-learning-progress')
<!-- ================= EXAM & RESULT (LEBD Fix233) ================= -->
@include('student.partials.lebd-exam-results-7day')
<style>
/* Fix213: Batch-style full-width Circular image area; layout never collapses when image is missing. */
#lebd-dash-circulars .job-slider-card.lebd-circular-card {
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
    overflow: hidden !important;
    padding: 0 !important;
    text-align: left !important;
    min-width: 0;
    box-sizing: border-box;
}
#lebd-dash-circulars .lebd-circular-media {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex: none !important;
    width: 100% !important;
    height: 138px !important;
    margin: 0 !important;
    padding: 0 !important;
    border-radius: 0 !important;
    overflow: hidden !important;
    background: #e9f3fd;
}
#lebd-dash-circulars .lebd-circular-media img {
    display: block;
    width: 100%; height: 100%;
    object-fit: contain;
    object-position: center;
    border-radius: 0;
}
#lebd-dash-circulars .lebd-circular-media .lebd-circular-placeholder {
    display: flex;
    width: 100%; height: 100%;
    align-items: center; justify-content: center;
    background: linear-gradient(145deg,#eaf3ff,#f4f8ff);
    color: #1971df;
    font-size: 37px;
}
#lebd-dash-circulars .lebd-circular-media [hidden] { display: none !important; }
#lebd-dash-circulars .lebd-circular-card .lebd-circular-card-content {
    padding: 13px 14px 17px;
    min-width: 0;
    flex: 1;
}
#lebd-dash-circulars .lebd-circular-card .lebd-circular-card-content h3 {
    margin: 0 0 9px !important;
    overflow-wrap: anywhere;
}
#lebd-dash-circulars .lebd-circular-card .lebd-circular-card-content p {
    margin: 5px 0 !important;
    overflow-wrap: anywhere;
}
@media(max-width:600px) {
    #lebd-dash-circulars .lebd-circular-media { height: 125px !important; }
}
</style>
<!-- ================= HOMEPAGE-MATCHED LIVE CIRCULAR SLIDER ================= -->
@php
    // Same live source/filter/order as the homepage Circular slider.
    $lebdCircularToday = \Carbon\Carbon::today('Asia/Dhaka')->toDateString();
    $lebdSliderCirculars = \App\Models\Circular::query()
        ->whereIn('status', ['Active', 'Published'])
        ->whereNotNull('start_at')->whereNotNull('end_at')
        ->whereDate('start_at', '<=', $lebdCircularToday)
        ->whereDate('end_at', '>=', $lebdCircularToday)
        ->orderByDesc('updated_at')->orderByDesc('id')
        ->limit(10)->get();
@endphp
<section class="container mt-4 lebd-section-panel lebd-job-panel lebd-dash-job-v61" id="lebd-dash-circulars">
    <div class="job-slider-section">
        <div class="job-slider-title"><div></div><h2><span></span> নিয়োগ বিজ্ঞপ্তি স্লাইডার</h2><div></div></div>
        <div class="lebd-dash-circular-top"><span>বর্তমানে আবেদন চলমান নিয়োগ বিজ্ঞপ্তি</span><a href="{{ route('home.circular') }}">সব দেখুন →</a></div>
        <div class="job-slider-container">
            <button class="job-slider-arrow job-prev" type="button" aria-label="আগের বিজ্ঞপ্তি" data-circular-step="-1"><i class="fas fa-chevron-left"></i></button>
            <div class="job-slider-track" id="lebdDashCircularTrack">
                @forelse($lebdSliderCirculars as $lebdCircular)
                <a href="{{ route('home.circular.show', $lebdCircular->id) }}" class="job-slider-card lebd-circular-card {{ ['slider-pink','slider-blue','slider-green','slider-orange','slider-purple','slider-yellow'][$loop->index % 6] }}">
                    @php
                        // Circular image is the Admin-uploaded image field (single path or JSON image list).
                        $lebdImages = json_decode($lebdCircular->image ?? '', true);
                        $lebdImage = is_array($lebdImages) ? ($lebdImages[0] ?? null) : $lebdCircular->image;
                        $lebdImageUrl = $lebdImage ? (preg_match('~^https?://~i', $lebdImage) ? $lebdImage : asset(ltrim($lebdImage, '/'))) : null;
                    @endphp
                    <div class="lebd-circular-media">
                        @if($lebdImageUrl)
                            <img src="{{ $lebdImageUrl }}" alt="{{ $lebdCircular->title }}" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false;" />
                            <span class="lebd-circular-placeholder" hidden aria-hidden="true"><i class="fas fa-briefcase"></i></span>
                        @else
                            <span class="lebd-circular-placeholder" aria-hidden="true"><i class="fas fa-briefcase"></i></span>
                        @endif
                    </div>
                    <div class="lebd-circular-card-content">
                        <h3>{{ $lebdCircular->title }}</h3>
                        <p><i class="far fa-calendar-alt"></i> শেষ: {{ \Carbon\Carbon::parse($lebdCircular->end_at)->format('d/m/Y') }}</p>
                        @if($lebdCircular->position)<p><i class="far fa-file-alt"></i> {{ $lebdCircular->position }}</p>@endif
                    </div>
                </a>
                @empty
                    <div class="lebd-dash-circular-empty">এই মুহূর্তে আবেদন চলমান কোনো নিয়োগ বিজ্ঞপ্তি নেই।</div>
                @endforelse
            </div>
            <button class="job-slider-arrow job-next" type="button" aria-label="পরের বিজ্ঞপ্তি" data-circular-step="1"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="job-slider-dots" id="lebdDashCircularDots"></div>
    </div>
</section>
<!-- ================= TODAY'S LIVE / UPCOMING EXAMS ================= -->
@php
    // Published routine & assigned papers; access verified in the service.
    $lebdTodayExams = \App\Services\StudentDashboardTodayExams::forStudent($user);
    $lebdServerNow = \Carbon\Carbon::now('Asia/Dhaka')->getTimestamp() * 1000;
@endphp
<section class="container mt-4 lebd-section-panel lebd-daily-panel" id="lebd-today-exams">
    <div class="lebd-section-head">
        <span class="lebd-head-icon"><i class="fa fa-calendar-check"></i></span>
        <div class="lebd-head-text">
            <h3>আজকের পরীক্ষাসমূহ</h3>
            <p>{{ \Carbon\Carbon::now('Asia/Dhaka')->locale('bn')->translatedFormat('d F Y') }} · রুটিন অনুযায়ী সময় ও অবস্থা</p>
        </div>
        <span class="lebd-head-tag">TODAY</span>
    </div>
    @if(count($lebdTodayExams))
    <div class="lebd-daily-grid" data-server-now="{{ $lebdServerNow }}">
        @foreach($lebdTodayExams as $lebdExam)
        <article class="lebd-daily-card"
            data-start="{{ $lebdExam['start_ms'] }}"
            data-end="{{ $lebdExam['end_ms'] ?? '' }}"
            data-openable="{{ $lebdExam['can_open'] ? 1 : 0 }}">
            <div class="lebd-daily-top">
                <span class="lebd-daily-date"><i class="fa fa-book-open"></i> {{ $lebdExam['batch'] }}</span>
                <span class="lebd-daily-status" data-exam-status>{{ $lebdExam['status_label'] }}</span>
            </div>
            <h4>{{ $lebdExam['name'] }}</h4>
            <p class="lebd-daily-meta"><i class="fa-regular fa-clock"></i>
                {{ $lebdExam['start_label'] }} — {{ $lebdExam['end_label'] }}
                @if($lebdExam['question_count'] !== null)
                    <span>· {{ $lebdExam['question_count'] }} প্রশ্ন</span>
                @endif
            </p>
            <div class="lebd-daily-bottom">
                <div class="lebd-daily-countdown">
                    <small data-exam-timer-label>{{ $lebdExam['timer_label'] }}</small>
                    <strong data-exam-timer>{{ $lebdExam['timer_value'] }}</strong>
                </div>
                @if($lebdExam['can_open'] && $lebdExam['info_url'])
                    <a class="lebd-daily-link" data-exam-link href="{{ $lebdExam['info_url'] }}">
                        {{ $lebdExam['status'] === 'live' ? 'পরীক্ষায় যান' : 'বিস্তারিত' }}
                        <i class="fa fa-arrow-right"></i>
                    </a>
                @elseif($lebdExam['result_url'])
                    <a class="lebd-daily-link" data-exam-link href="{{ $lebdExam['result_url'] }}">
                        ফলাফল দেখুন <i class="fa fa-arrow-right"></i>
                    </a>
                @else
                    <span class="lebd-daily-pending" data-exam-link>তথ্য দেখুন</span>
                @endif
            </div>
        </article>
        @endforeach
    </div>
    @else
        <div class="lebd-daily-empty">
            <i class="fa fa-calendar-xmark"></i>
            <div>
                <strong>আজ আপনার ব্যাচে কোনো নির্ধারিত পরীক্ষা পাওয়া যায়নি।</strong>
                <p>নতুন রুটিন প্রকাশিত হলে এখানে স্বয়ংক্রিয়ভাবে দেখা যাবে।</p>
            </div>
        </div>
    @endif
</section>
</div> {{-- /.lebd-student-dashboard --}}
@endsection
@section('scripts')
<script defer src="{{ asset('web/js/lebd-dashboard-circular-v61.js') }}?v=61"></script>
<script>
// Client timer uses server time baseline, preventing local clock timezone drift.
(function () {
    'use strict';
    const grid = document.querySelector('#lebd-today-exams .lebd-daily-grid');
    if (!grid) return;
    const serverAtLoad = Number(grid.getAttribute('data-server-now'));
    const clientAtLoad = Date.now();
    const pad = (n) => String(n).padStart(2, '0');
    const displayTime = (milliseconds) => {
        let s = Math.max(0, Math.floor(milliseconds / 1000));
        const d = Math.floor(s / 86400); s %= 86400;
        const h = Math.floor(s / 3600); s %= 3600;
        const m = Math.floor(s / 60); s %= 60;
        return (d ? d + ' দিন ' : '') + pad(h) + ':' + pad(m) + ':' + pad(s);
    };
    function updateTimers() {
        const now = serverAtLoad + (Date.now() - clientAtLoad);
        for (const card of grid.querySelectorAll('.lebd-daily-card')) {
            const start = Number(card.getAttribute('data-start'));
            const end = Number(card.getAttribute('data-end'));
            const hasEnd = card.getAttribute('data-end') !== '' && Number.isFinite(end) && end > start;
            const openable = card.getAttribute('data-openable') === '1';
            const state = now < start ? 'upcoming' : (hasEnd && now < end ? 'live' : 'ended');
            card.dataset.state = state;
            const label = card.querySelector('[data-exam-timer-label]');
            const timer = card.querySelector('[data-exam-timer]');
            const badge = card.querySelector('[data-exam-status]');
            const link = card.querySelector('[data-exam-link]');
            if (state === 'upcoming') {
                if (label) label.textContent = 'শুরু হতে বাকি';
                if (timer) timer.textContent = displayTime(start - now);
                if (badge) badge.textContent = 'আসন্ন';
            } else if (state === 'live') {
                if (label) label.textContent = 'সময় বাকি';
                if (timer) timer.textContent = displayTime(end - now);
                if (badge) badge.textContent = openable ? 'লাইভ চলছে' : 'সময় চলছে';
            } else {
                if (label) label.textContent = 'অবস্থা';
                if (timer) timer.textContent = hasEnd ? 'সময় শেষ' : 'শেষ সময় নেই';
                if (badge) badge.textContent = hasEnd ? 'সম্পন্ন' : 'সময় অনির্ধারিত';
            }
            if (link && link.tagName === 'A' && link.getAttribute('href')) {
                // The exam-info route is read-only; never point to exam.show here.
                const href = link.getAttribute('href');
                if (href.indexOf('/exam-info/') !== -1) {
                    if (state === 'live' && openable) {
                        link.firstChild.textContent = 'পরীক্ষায় যান ';
                    }
                    if (state === 'ended') {
                        link.setAttribute('aria-disabled', 'true');
                        link.style.pointerEvents = 'none';
                        link.style.opacity = '.45';
                    }
                }
            }
        }
    }
    updateTimers();
    window.setInterval(updateTimers, 1000);
})();
</script>
@endsection
