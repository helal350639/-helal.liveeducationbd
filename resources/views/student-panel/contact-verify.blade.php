<div class="modal fade" id="modal-contact" style="margin-top:60px">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <h4 class="modal-title">Continue to add your contact number</h4>
      </div>
      <div class="modal-body" id="contact-body">
        <div class="form-group">
          <label for="">Contact Number: </label>
          <input type="number" name="contact" id="contact-number" class="form-control" placeholder="01XXXXXXXXX" required/>
          <p style="color:red" id="errorMsg"></p>
        </div>
      </div>
      <div class="modal-footer" id="contact-footer">
        <button type="button" class="btn btn-default pull-left" data-bs-dismiss="modal" >Close</button>
        <button type="button" class="btn btn-info" onclick="submitForm()">Continue</button>
      </div>
    </div> <!-- /.modal-content -->
  </div> <!-- /.modal-dialog -->
</div> <!-- /.modal -->
{{-- contact verification end --}}

<script>

  @if(Auth::guard('student')->user() && Auth::guard('student')->user()->contact == null)
  
  window.addEventListener('DOMContentLoaded', function () {
    const myModal = new bootstrap.Modal(document.getElementById('modal-contact'));
    myModal.show();
  });

  @endif
  
  let contactBody = document.getElementById('contact-body');
  let number = document.getElementById('contact-number');
  let contactFooter = document.getElementById('contact-footer');

  number.addEventListener('keyup', function()
  {
    $('#errorMsg').text('');
  });

  function submitForm()
  {
    if(number.value == '' || number.value.length > 11)
    {
      $('#errorMsg').text('Your mobile number must be exactly 11 digits.');
    }
    else
    {
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
          if(data.success === true)
          {
            contactBody.innerHTML = '<div class="form-group">'+
              '<label for="">OTP: </label>'+
              '<input type="number" name="otp" id="otp-number" class="form-control" placeholder="xxxx"/>'+
              '<p style="color:red">Please check your phone. 4 digit number has been sent by SMS.</p>'+
            '</div>';

            contactFooter.innerHTML = '<button type="button" class="btn btn-info" onclick="confirm()">Confirm</button>';
          }
        },
        error: function(xhr){
          if (xhr.status === 422) {
              const errors = xhr.responseJSON.errors;
              $.each(errors, function (field, messages) {
                  $('#errorMsg').text(messages[0]);
              });
          } else {
              console.error('Unexpected error', xhr.responseText);
          }
        },
      });
    }
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