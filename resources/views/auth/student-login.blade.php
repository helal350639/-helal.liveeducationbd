@extends('student')

@section('title', 'Login')

@section('content')

<style>

.login-card{
    max-width:420px;
    margin:50px auto;
    border-radius:20px;
    box-shadow:0 10px 40px rgba(0,0,0,.12);
    padding:30px;
    background:#fff;
}

.login-logo img{
    width:100px;
}

.login-title{
    font-size:28px;
    font-weight:700;
    color:#123b82;
}

.login-subtitle{
    font-size:18px;
    margin-top:15px;
    color:#444;
}


.form-control{
    height:48px;
    border-radius:10px;
}


.input-group-text{
    cursor:pointer;
}



.login-btn{

    width:100%;
    height:50px;
    border-radius:12px;
    font-size:18px;
    font-weight:600;

}


.google-btn img{

    width:80%;
    max-width:280px;

}


</style>


<section class="our_courses">


<div class="container">


<div class="login-card text-center">


    <div class="login-logo">

        <h2 class="login-title">
            {{ config('app.name','Live Education BD') }}
        </h2>


        <img src="/img/logo.png?v=3008">


        <div class="login-subtitle">
            Student Login
        </div>


    </div>




    <br>



    @if($errors->any())

        <div class="alert alert-danger">

            {{ $errors->first() }}

        </div>

    @endif





    <!-- Google Login -->

    <a href="/auth/google" class="google-btn">

        <img src="/img/sign-with-google.jpg">

    </a>




    <h3>
        OR
    </h3>




<form action="{{ route('students.login.post') }}" 
      method="POST">

@csrf



<div class="form-group text-start mb-3">

<label>
Email Address
</label>


<div class="input-group">


<input type="email"
       name="email"
       class="form-control"
       placeholder="Enter your email"
       required>


<span class="input-group-text">

<i class="fa fa-envelope"></i>

</span>


</div>

</div>





<div class="form-group text-start mb-3">


<label>
Password
</label>


<div class="input-group">


<input type="password"
       name="password"
       id="password"
       class="form-control"
       placeholder="Enter password"
       required>


<span class="input-group-text"
      onclick="showPassword()">

<i class="fa fa-eye-slash"
   id="eye"></i>

</span>


</div>


</div>





<button type="submit"
        class="btn btn-primary login-btn">


Login


</button>



<br><br>



<a href="{{ route('password.request') }}"
   class="text-primary">

Forgot Password?

</a>



<br><br>



<a href="{{ route('register') }}"
   class="text-success">

Create New Account

</a>



</form>



</div>


</div>


</section>





<script>


function showPassword(){

    let password = document.getElementById('password');

    let eye = document.getElementById('eye');


    if(password.type === "password"){

        password.type="text";

        eye.classList.remove('fa-eye-slash');

        eye.classList.add('fa-eye');


    }else{


        password.type="password";

        eye.classList.remove('fa-eye');

        eye.classList.add('fa-eye-slash');


    }

}


</script>



@endsection