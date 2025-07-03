@php
$batch_srt = json_decode($sms->batch_id);
$batch_ids = array_map('intval', $batch_srt);

function check_batch($id, $batch)
{
  return in_array($id, $batch);
}

$student_srt = json_decode($sms->student_id);
$student_ids = array_map('intval', $student_srt);

function check_student($id, $student)
{
  return in_array($id, $student);
}
@endphp

@extends('dashboard')
@section('title', 'Edit Sms')
@section('content')
<section class="content-header">
  <h1>Edit Sms</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Smses</a></li>
    <li class="active">Edit Sms</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-8"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Sms Details</h3>
        </div>
        <form action="{{route('sms.update', $sms)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="box-body">
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="title">Title</label>
                      <input type="text" class="form-control" name="title" id="title" value="{{$sms->title}}"/>
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="name">Batch Name</label>
                      <select class="form-control select2" name="batch_id[]" id="batch_id" multiple onchange="getStudents(this)">
                          <option value=""></option>
                          @foreach($batches as $batch)
                          <option value="{{$batch->id}}" {{check_batch($batch->id, $batch_ids) ? 'selected' : ''}}>{{$batch->name}}</option>
                          @endforeach
                      </select>
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="name">Student Name</label>
                      <select class="form-control select2" name="student_id[]" id="student_id" multiple>

                      @foreach($students as $student)
                        <option value="{{$student->id}}" {{check_student($student->id, $student_ids) ? 'selected' : ''}}>{{$student->name}}</option>
                      @endforeach

                      </select>
                  </div>
              </div>
              <div class="clearfix"></div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="details">Suggested Text</label>
                      <select id="suggested_text" class="form-control" onchange="suggested(this)">
                        <option value="">Select One</option>
                        <option value="This is first selected text">This is first selected text</option>
                        <option value="This is second selected text">This is second selected text</option>
                        <option value="This is third selected text">This is third selected text</option>
                      </select>
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="details">SMS Details</label>
                      <textarea class="form-control" name="details" id="details" rows="5" required onkeyup="charCount(this)">{{$sms->details}}</textarea>
                      <p style="color: red">160 Characters = 1 SMS | <b>Character Count: <span id="char_count">160</span></b> | SMS Count: <span id="sms_count">1</span></p>
                  </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                    <label>Status:</label><br>
                    <select name="status" id="status" class="form-control" required onchange="checkStatus(this)">
                      <option value="">Select One</option>
                      <option value="Scheduled" {{$sms->status == 'Scheduled' ? 'selected' : ''}}>Scheduled</option>
                      <option value="Sent" {{$sms->status == 'Sent' ? 'selected' : ''}}>Sent</option>
                      <option value="Unsent" {{$sms->status == 'Unsent' ? 'selected' : ''}}>Unsent</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="">Date & Time:</label>
                <input type="datetime-local" name="datetime" class="form-control" id="datetime" value="{{$sms->datetime}}">
              </div>
            </div>
          </div> <!-- /.box body -->
          <div class="box-footer">
              <button type="submit" class="btn btn-primary pull-right" id="sentBtn"><i class="fa fa-save"></i> Save</button>
          </div>
        </form>
    </div> <!-- /.box -->
    </div> <!--/.col (left) -->
</div> <!-- /.row -->
</section> <!-- /.content -->
@endsection
@section('scripts')
<script>
  function suggested(e)
  {
    const details = document.getElementById('details');
    // console.log(details);
    details.innerHTML = e.options[e.selectedIndex].value;
  }
  
  function charCount(e)
  {
      let char_count = document.getElementById('char_count');
      let sms_count = document.getElementById('sms_count');
      
      char_count.innerHTML = e.value.length;

      smsCount = Math.ceil(e.value.length / 160);
      sms_count.innerHTML = smsCount;
  }
    
  const datatime = document.getElementById('datetime')
  const sentBtn = document.getElementById('sentBtn')
  function checkStatus(e)
  {
    const option = e.options[e.selectedIndex];
    if(option.value == 'Sent')
    {
      sentBtn.innerHTML = '<i class="fa fa-send"></i> Send';
    }
    else
    {
      sentBtn.innerHTML = '<i class="fa fa-save"></i> Save';
    }

    if(option.value == 'Scheduled')
    {
      datetime.setAttribute('required', 'required');
    }
    else
    {
      datetime.removeAttribute('required');
    }
  }

    function getStudents(e)
    {
      const selectedIds = '{{implode(",", $student_ids)}}';
      const selectedIdsArray = selectedIds.split(',');
      function check(e)
      {
        if(selectedIdsArray.includes(e.toString()))
        {
            return 'selected';
        }
        return '';
      }

        let ids = Array.from(e.selectedOptions).map(({value}) => value);

        if(ids.length > 0)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: 'GET', //THIS NEEDS TO BE GET
                url: '{{route("sms.batch.student", "")}}/' + ids,
                success: function (data) {

                    var obj = JSON.parse(JSON.stringify(data));
                    var options = '';

                    $.each(obj['students'], function (key, val) {
                        options += '<option value="'+val.student_id+'"'+check(val.id)+'>'+val.student_name+'</option>';
                    });

                    if(options != ""){
                        $("#student_id").html(options)
                    }else{
                        $("#student_id").html('')
                    }
                },
                error: function(data) { 
                        console.log('data error');
                }
            });
        }

        
    }

    //select 2
    $(function (){ $('.select2').select2() });
</script>
@endsection