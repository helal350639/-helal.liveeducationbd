@php
$batch_srt = json_decode($email->batch_id);
$batch_ids = array_map('intval', $batch_srt);

function check_batch($id, $batch)
{
  return in_array($id, $batch);
}

$student_srt = json_decode($email->student_id);
$student_ids = array_map('intval', $student_srt);

function check_student($id, $student)
{
  return in_array($id, $student);
}
@endphp

@extends('dashboard')
@section('title', 'Edit Email')
@section('content')
<section class="content-header">
  <h1>Edit Email</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Emailes</a></li>
    <li class="active">Edit Email</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
@if($email->email_workflow === 'mcq' && $email->email_type === 'Exam Alert')
  <div class="box box-primary" style="border-top:3px solid #2474d2;border-radius:6px">
    <div class="box-header with-border" style="display:flex;align-items:center;justify-content:space-between;gap:12px">
      <h3 class="box-title" style="color:#800">Edit Exam Alert Email</h3>
      <a href="{{ route('email.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
    <form action="{{ route('email.update', $email->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="box-body">
        @if($errors->any())
          <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>Email Title</label>
              <input type="text" name="title" class="form-control" maxlength="255" required value="{{ old('title', $email->title) }}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group"><label>Batch</label><input type="text" class="form-control" readonly value="{{ implode(', ', array_map(function($id){ return optional(\App\Models\Batch::find($id))->name; }, json_decode($email->batch_id, true) ?: [])) }}"></div>
          </div>
          <div class="col-md-6">
            <div class="form-group"><label>Routine / Exam</label><input type="text" class="form-control" readonly value="{{ ($examInfo->routine_name ?? 'Routine') . ' — ' . ($examInfo->paper_name ?? 'Exam') }}"></div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label>Email Details</label>
              <textarea name="details" id="examDraftDetails" class="form-control" rows="6" maxlength="10000" required oninput="updateExamDraftCount()">{{ old('details', $email->details) }}</textarea>
              <p class="text-danger">Email body | Character Count: <b><span id="examDraftCount">0</span>/150</b></p>
            </div>
          </div>
          <div class="col-md-12">
            <div class="alert alert-info" style="margin-bottom:10px">
              <i class="fa fa-clock-o"></i>
              @if($email->status === 'Draft')
                এটি Draft হিসেবে সংরক্ষিত। Confirm & Schedule করলে Routine-এর Exam Time থেকে ১২ ঘণ্টা বিয়োগ করে Email Schedule হবে।
              @else
                বর্তমান অবস্থা: <strong>{{ $email->status }}</strong>। Routine-এর সময় অনুযায়ী পুনরায় Schedule করতে পারবেন।
              @endif
              @if($examInfo && $examInfo->exam_at)
                <br>Exam Time: <strong>{{ \Carbon\Carbon::parse($examInfo->exam_at)->timezone('Asia/Dhaka')->format('d-m-Y h:i A') }}</strong>
              @endif
            </div>
          </div>
          <div class="col-md-12">
            <input type="hidden" name="is_active" value="0">
            <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $email->is_active) ? 'checked' : '' }}> Active রাখুন</label>
          </div>
        </div>
      </div>
      <div class="box-footer" style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap">
        <button type="submit" name="exam_action" value="save_draft" class="btn btn-default"><i class="fa fa-floppy-o"></i> Save Changes as Draft</button>
        <button type="submit" name="exam_action" value="confirm_schedule" class="btn btn-success" onclick="return confirm('এই Exam Alert আপডেট করে Routine-এর সময় অনুযায়ী ১২ ঘণ্টা আগে Schedule করবেন?')"><i class="fa fa-calendar-check-o"></i> Confirm &amp; Schedule</button>
      </div>
    </form>
  </div>
  <script>
    function updateExamDraftCount(){
      var field=document.getElementById('examDraftDetails');
      document.getElementById('examDraftCount').textContent=Array.from(field.value).length;
    }
    updateExamDraftCount();
  </script>
@else
  <div class="row"> <!-- left column -->
    <div class="col-md-8"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Email Details</h3>
        </div>
        <form action="{{route('email.update', $email)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="box-body">
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="title">Title</label>
                      <input type="text" class="form-control" name="title" id="title" value="{{$email->title}}"/>
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
                      <label for="details">Email Details</label>
                      <textarea class="form-control" name="details" id="details" rows="5" required onkeyup="charCount(this)">{{$email->details}}</textarea>
                      <p style="color: red">160 Characters = 1 Email | <b>Character Count: <span id="char_count">160</span></b> | Email Count: <span id="email_count">1</span></p>
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="form-group">
                      <label for="start_date">Start Date</label>
                      <input type="date" class="form-control" name="start_date" id="start_date" value="{{ old('start_date', $email->start_date ? \Carbon\Carbon::parse($email->start_date)->format('Y-m-d') : '') }}">
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="form-group">
                      <label for="end_date">End Date</label>
                      <input type="date" class="form-control" name="end_date" id="end_date" value="{{ old('end_date', $email->end_date ? \Carbon\Carbon::parse($email->end_date)->format('Y-m-d') : '') }}">
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <input type="hidden" name="is_active" value="0">
                      <label style="display:flex;align-items:center;gap:9px">
                          <input type="checkbox" name="is_active" value="1" {{ old('is_active', $email->is_active) ? 'checked' : '' }}>
                          Active রাখুন
                      </label>
                  </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                    <label>Status:</label><br>
                    <select name="status" id="status" class="form-control" required onchange="checkStatus(this)">
                      <option value="">Select One</option>
                      <option value="Scheduled" {{$email->status == 'Scheduled' ? 'selected' : ''}}>Scheduled</option>
                      <option value="Sent" {{$email->status == 'Sent' ? 'selected' : ''}}>Sent</option>
                      <option value="Unsent" {{$email->status == 'Unsent' ? 'selected' : ''}}>Unsent</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="">Date & Time:</label>
                <input type="datetime-local" name="datetime" class="form-control" id="datetime" value="{{$email->datetime}}">
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
@endif
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
      let email_count = document.getElementById('email_count');
      
      char_count.innerHTML = e.value.length;

      emailCount = Math.ceil(e.value.length / 160);
      email_count.innerHTML = emailCount;
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
                url: '{{route("email.batch.student", "")}}/' + ids,
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