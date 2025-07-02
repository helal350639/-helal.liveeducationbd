@extends('dashboard')
@section('title', 'Send SMS')
@section('content')

<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
<section class="content-header">
  <h1>Send Email</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Email</a></li>
    <li class="active">Send Email</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-10"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Email Details</h3>
        </div>
        <form action="{{route('email.store')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="box-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="title">Title/Subject</label>
                        <input type="text" class="form-control" name="title" id="title" required />
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Batch Name</label>
                        <select class="form-control select2" name="batch_id[]" id="batch_id" multiple onchange="getStudents(this)">
                            <option value=""></option>
                            @foreach($batches as $batch)
                            <option value="{{$batch->id}}">{{$batch->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Student Name</label>
                        <select class="form-control select2" name="student_id[]" id="student_id" multiple>
                            
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">Email Details</label>
                        <textarea class="form-control editor" name="details" id="details" rows="10" required></textarea>
                    </div>
                </div>
                
              <div class="col-md-6">
                <div class="form-group">
                    <label>Status:</label><br>
                    <select name="status" id="status" class="form-control" required onchange="checkStatus(this)">
                      <option value="">Select One</option>
                      <option value="Scheduled">Scheduled</option>
                      <option value="Sent">Sent</option>
                      <option value="Unsent">Unsent</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="">Data & Time:</label>
                <input type="datetime-local" name="datetime" class="form-control" id="datetime">
              </div>
            </div>
            </div> <!-- /.box body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right" id="sentBtn"><i class="fa fa-send"></i> Send</button>
            </div>
        </form>
    </div> <!-- /.box -->
    </div> <!--/.col (left) -->
</div> <!-- /.row -->
</section> <!-- /.content -->
@endsection
@section('scripts')

<script src="/assets/summernote/summernote.min.js"></script>
<script>
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
        let ids = Array.from(e.selectedOptions).map(({value}) => value);

        console.log(ids);

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
                        options += '<option value="'+val.student_id+'">'+val.student_name+'</option>';
                    });

                    if(options != ""){
                        $("#student_id").html(options)
                    }else{
                        $("#student_id").html('')
                    }
                },
                error: function(data) {
                  console.error('data error');
                }
            });
        }

        
    }

    //select 2
    $(function (){ $('.select2').select2() });
    //this script for text editor
    $(document).ready(function() {
        $('.editor').summernote({
            height: 200
        });
    });
</script>
@endsection