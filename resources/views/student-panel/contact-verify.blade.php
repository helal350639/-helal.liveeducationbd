<style>
  .error{color:red}
</style>
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
  function checkStudy(e)
  {
    if(e.options[e.selectedIndex].value == 'Others')
    {
      let elm = document.createElement('div');
      elm.setAttribute('class', 'form-group');
      elm.innerHTML = '<label>Write that you are studying</label>'+
      '<input name="other" id="other" class="form-control" required>'+
      '<p class="error"></p>';
      e.parentNode.parentNode.append(elm);
      e.parentNode.nextElementSibling.lastChild.focus();
    }
    else if(e.parentNode.nextElementSibling)
    {
      e.parentNode.nextElementSibling.remove();
    }
  }

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
              '<label for="">OTP: <span class="error">4 digit number has been sent by SMS.</span> </label>'+
              '<input type="number" name="otp" id="otp-number" class="form-control" placeholder="xxxx"/>'+
              '<p style="color:red">Check your mobile phone SMS. </p>'+
            '</div>'+
            '<div class="form-group">'+
              '<label for="">What are you studying? </label>'+
              '<select name="study" id="study" class="form-control" onchange="checkStudy(this)" required >'+
                '<option value="">Select One</option>'+
                '<option value="জব প্রস্তুতি (ডিপ্লোমা-ইন-ইঞ্জিনিয়ারিং)">জব প্রস্তুতি (ডিপ্লোমা-ইন-ইঞ্জিনিয়ারিং)</option>'+
                '<option value="পলিটেকনিক স্টুডেন্ট">পলিটেকনিক স্টুডেন্ট</option>'+
                '<option value="জেনারেল স্টুডেন্ট">জেনারেল স্টুডেন্ট</option>'+
                '<option value="Others">Others</option>'+
              '</select>'+
              '<p class="error"></p>'+
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
    let submit = false;
    let studyOptions = '';
    const modalId = document.getElementById('modal-contact');
    const otp = document.getElementById('otp-number');
    const study = document.getElementById('study');
    const other = document.getElementById('other');
    if(otp.value == '')
    {
      otp.style.borderColor = 'red';
      submit = false;
    }
    else
    {
      submit = true;
    }

    if(study.options[study.selectedIndex].value == '')
    {
      study.style.borderColor = 'red';
      study.nextElementSibling.innerHTML = 'Choose a option';
      submit = false;
    }
    else
    {
      submit = true;
    }

    if(study.options[study.selectedIndex].value == 'Others' &&  other.value == '')
    {
      submit = false;
      other.style.borderColor = 'red';
      other.focus();

      other.nextElementSibling.innerHTML = 'Fill out this field.';
    }
    else
    {
      submit = true;
    }

    if(study.options[study.selectedIndex].value == 'Others')
    {
      studyOptions = other.value;
    }
    else
    {
      studyOptions = study.options[study.selectedIndex].value;
    }

    if(submit == true)
    {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      var formData = new FormData();
      formData.append('otp', otp.value);
      formData.append('study', studyOptions);

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
  }
</script>