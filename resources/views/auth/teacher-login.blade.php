@extends('student')
@section('title', 'Login')
@section('content')
{{-- <script src='https://www.google.com/recaptcha/api.js' async defer></script> --}}
<style>
  .checkbox{padding-left: 25px}
</style>

<section class="our_courses">
  <div class="container">
    <div class="row">
      <div class="col-md-4 offset-md-4">
        <div class="card p-3">
          <div class="login-box text-center" style="margin-top:10px">
            <div class="login-logo">
              <h2>{{config('app.name', 'App Name')}}</h2>
            </div>
            <!-- /.login-logo -->
            <div class="login-box-body m-3">
              <p class="login-box-msg">
                <img src="/img/logo.png?v=3008" alt="" width="100">
                <br>
                <br>
                Teacher Login
              </p>
    
              <div style="text-align:center">
                <a class="btn-block" href="/auth/google">
                  <img style="max-width: 80%" src="/img/sign-with-google.jpg" alt="">
                </a>
    
                {{-- <hr> --}}
      
                <h3 style="text-align: center">or</h3>
    
                <button class="btn btn-primary btn-block" onclick="showLoginForm()">Continue with Email Address</button>
                <br>
              </div>
    
              <form id="form" style="display: none" action="{{ route('teachers.login.post') }}" method="POST" style="margin-bottom:15px" class="py-3">
                @csrf
                <div class="form-group pd-3">
                  <label for="email" style="text-align: left; width:100%">Email Address</label>
                  <div class="input-group">
                    <input type='email' name="email" class='form-control' required placeholder="" />
                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                  </div>
                </div>
                <div class="form-group my-3">
                  <label for="password" style="text-align: left; width:100%">Password</label>
                  <div class="input-group">
                    <input type="password" name="password" class="form-control" required>
                    <span class="input-group-text" onclick="showPassword(this)"><i class="fa fa-eye-slash"></i></span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-xs-12">
                    {{-- @include('/partials.google_recaptcha') --}}
                    {{-- <br> --}}
                  </div>
                  {{-- <div class="col-xs-8">
                    <div class="checkbox icheck">
                      <label class="">
                        <input type="checkbox" class="checkbox"> Remember Me
                      </label>
                      <div class="clearfix"></div>
                    </div>
                  </div> --}}
                  <!-- /.col -->
                  <div class="col-xs-12 d-grid">
                    <button type="submit" class="btn btn-info btn-submit btn-large">Login</button>
                  </div>
                  <!-- /.col -->
                </div>
                <br>
                <p><a href="{{route('password.request')}}" class="text-primary">I forgot my password</a></p>
              </form>
              <!-- /.social-auth-links -->
              <br>
              <p>
                <a href="{{route('register')}}" class="text-success">Create New Account</a>
              </p>
            </div><!-- /.login-box-body -->
          </div><!-- /.login-box -->
        </div>
        
      </div>
    </div>
  </div>
</section>

<script>
  function showLoginForm()
  {
    document.getElementById('form').style.display = 'block';
  }
  function showPassword(e)
  {
    let elm = e.previousElementSibling;
    if(elm.type == 'password')
    {
      elm.setAttribute('type', 'text');
      e.firstChild.classList.add('fa-eye');
      e.firstChild.classList.remove('fa-eye-slash');
    }
    else 
    {
      elm.setAttribute('type', 'password');
      e.firstChild.classList.add('fa-eye-slash');
      e.firstChild.classList.remove('fa-eye');
    }
  }
</script>
@endsection