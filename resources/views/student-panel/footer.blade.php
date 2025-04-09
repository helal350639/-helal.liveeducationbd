<!-- Footer -->
<style>
  footer{margin-top:30px; background:#000; color:#ddd;}
  .footer-menu{}
  .footer-menu, .social-icons {list-style: none;padding-left: 0}
  .footer-menu li{padding:5px}
  .footer-logo{max-width: 150px; width:100%;}
  .social-icons li a{font-size: 18px}
</style>

{{-- contact verification --}}
<div class="modal fade in" id="modal-contact">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="showModal()">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Continue to add your contact number</h4>
      </div>
      <div class="modal-body" id="contact-body">
        <div class="form-group">
          <label for="">Contact Number: </label>
          <input type="number" name="contact" id="contact-number" class="form-control" placeholder="01XXXXXXXXX" required/>
          <p style="color:red">Please double check your contact number</p>
        </div>
      </div>
      <div class="modal-footer" id="contact-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal" onclick="showModal()">Close</button>
        <button type="button" class="btn btn-info" onclick="submitForm()">Continue</button>
      </div>
    </div> <!-- /.modal-content -->
  </div> <!-- /.modal-dialog -->
</div> <!-- /.modal -->
{{-- contact verification end --}}

<footer id="footer" class="footer">
  <div class="ro/w">
    <div class="col-md-12">
      <div class="col-md-4">
        <br>
        <img src="/img/logo.png" alt="" class="footer-logo"><br><br>
        <ul class="social-icons">
          <!-- <li><a href="#" class="icon fa-twitter"><span class="label">Twitter</span></a></li> -->
          <li>
            <a target="_blank" href="https://www.facebook.com/saeonlineexamgroup?mibextid=ZbWKwL">
              <span class="label" style="border:1px solid #ddd; border-radius:10px"> <i class="fa fa-facebook"></i> </span> &nbsp;  Facebook
            </a>
          </li>
          <!-- <li><a href="#" class="icon fa-instagram"><span class="label">Instagram</span></a></li> -->
          <!-- <li><a href="#" class="icon fa-snapchat"><span class="label">Snapchat</span></a></li> -->
        </ul>
      </div>
      <div class="col-md-4">
        <br>
        <ul class="footer-menu">
          <li>
            <a href="{{route('home.page', 'about-us')}}">About Us</a></li>
          <li>
            <a href="{{route('home.page', 'terms-condition')}}">Terms & Condition</a>
          </li>
          <li>
            <a href="{{route('home.page', 'privacy-policy')}}">Privacy Policy</a>
          </li>
          <li><a href="{{route('home.page', 'return-policy')}}">Fund Return Policy</a></li>
          <li><a href="{{route('students.complain')}}">Complain Us</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h3>Contact Us</h3>
        <address>
          Mobile: 01718778184<br>
          Email: info@liveeducationbd.com<br>
          Address: Jogendranagar, Sabgari-6450, Gurudaspur, Natore<br>
          Trade License No. 69141130786
        </address>
      </div>
        <div class="copyright">
          <p style="text-align: center">
            <img src="/img/pay-buttons.png" alt="" style="width:100%">
          </p>
          <p style="text-align: center;">Copyright &copy; {{date('Y')}} {{config('app.name')}}. All rights reserved.</p>
        </div>
      </div>
  </div>
<div class="clearfix"></div>
</footer>

<script>
  function showModal()
  {
    let modalId = document.getElementById('modal-contact');
    if(modalId.style.display == 'block')
    {
      modalId.style.display = 'none';
    }
    else
    {
      modalId.style.display = 'block';
    }
    
  }

  @if(Auth::guard('student')->user() && Auth::guard('student')->user()->contact == null)
  showModal();
  @endif
  
  function submitForm()
  {
    let body = document.getElementById('contact-body');
    let number = document.getElementById('contact-number');
    let footer = document.getElementById('contact-footer');

    if(number.value.length == 0)
    {
      return confirm('Please input your 11 digit mobile number');
    }

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var formData = new FormData();
    formData.append('contact', number.value);

    $.ajax({
      url: '{{route("contact-check")}}',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(data){
        if(data.success == true)
        {
          body.innerHTML = '<div class="form-group">'+
            '<label for="">OTP: </label>'+
            '<input type="number" name="otp" id="otp-number" class="form-control" placeholder="xxxx"/>'+
            '<p style="color:red">Please check your phone. 4 digit number has been sent by SMS.</p>'+
          '</div>';

          footer.innerHTML = '<button type="button" class="btn btn-info" onclick="confirm()">Confirm</button>';
        }

        // console.log(data);
      },
      error: function(data){
        console.log(data);
      },
    });
  }

  function confirm()
  {
    let modalId = document.getElementById('modal-contact');
    let otp = document.getElementById('otp-number');
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var formData = new FormData();
    formData.append('otp', otp.value);

    $.ajax({
      url: '{{route("otp-check")}}',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(data){
        if(data.success == true)
        {
          modalId.style.display = 'none';
          alert('Contact verification successfull');
        }
        else
        {
          alert(data.message);
        }
      },
      error: function(data){
        console.log(data);
      },
    });
  }
</script>